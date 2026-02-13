/**
 * Kiosk main entry.
 * Wires state machine, frames, camera, session.
 * No auto fullscreen, no auto session start – homescreen has Start, Lock, Camera Settings.
 */

import { createStateMachine } from './state.js';
import { initFrames } from './frames.js';
import { createCamera } from './camera.js';
import { createSession } from './session.js';
import { mergePhotoWithFrame } from './image-processing.js';
import { initCameraSettings } from './camera-settings.js';
import { initWelcomeScreen } from './welcome.js';

document.addEventListener('DOMContentLoaded', () => {
  // Initialize welcome screen
  initWelcomeScreen();

  const session = createSession();
  const framesData = JSON.parse(document.body.dataset.frames || '[]');
  const setting = JSON.parse(document.body.dataset.setting || '{}');
  const resultUrl = document.body.dataset.resultUrl || '';

  let camera = null;
  let selectedPhotoIndex = 0;
  let mergedImageDataUrl = null;
  let selectedFrameData = null;

  const stateMachine = createStateMachine({
    IDLE: () => {},
    FRAME: () => {},
    CAPTURE: (state, prev) => {
      if (prev === 'FRAME') {
        camera = createCamera(session);
        camera?.reset();
      }
    },
    PREVIEW: (state, prev) => {
      if (prev === 'CAPTURE' && camera) {
        const photos = camera.getPhotos();
        selectedPhotoIndex = 0;
        renderPreviewPhotos(photos);
        selectedFrameData = framesData.find((f) => f.id === getSelectedFrameId());
        if (selectedFrameData && photos[0]) {
          mergeAndShow(photos[0], selectedFrameData.frame_file);
        }
      }
    },
    PRINT: (state, prev) => {
      if (prev === 'PREVIEW' && mergedImageDataUrl) {
        doPrint();
      }
    },
    DONE: () => {},
    RESET: (state, prev) => {
      camera?.stop();
      camera = null;
      mergedImageDataUrl = null;
      selectedFrameData = null;
      stateMachine.setState(stateMachine.STATES.IDLE);
    },
  });

  function getSelectedFrameId() {
    const frameCard = document.querySelector('.frame-card.border-gray-900');
    return frameCard ? parseInt(frameCard.dataset.frameId, 10) : null;
  }

  const frames = initFrames(stateMachine, session);

  document.getElementById('btn-start')?.addEventListener('click', () => {
    stateMachine.setState(stateMachine.STATES.FRAME);
  });

  document.getElementById('btn-capture-back')?.addEventListener('click', () => {
    camera?.stop();
    stateMachine.setState(stateMachine.STATES.FRAME);
  });

  document.getElementById('btn-capture-next')?.addEventListener('click', () => {
    const photos = camera?.getPhotos() || [];
    if (photos.length < 1) return;
    stateMachine.setState(stateMachine.STATES.PREVIEW);
  });

  function renderPreviewPhotos(photos) {
    const grid = document.getElementById('preview-photo-grid');
    const merged = document.getElementById('preview-merged');
    if (!grid) return;
    grid.innerHTML = '';
    photos.forEach((dataUrl, i) => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = `preview-photo-btn rounded-lg overflow-hidden border-2 transition-all ${i === selectedPhotoIndex ? 'border-gray-900' : 'border-transparent'}`;
      btn.dataset.index = i;
      const img = document.createElement('img');
      img.src = dataUrl;
      img.alt = `Photo ${i + 1}`;
      img.className = 'w-full aspect-square object-cover';
      btn.appendChild(img);
      btn.addEventListener('click', () => {
        selectedPhotoIndex = i;
        grid.querySelectorAll('.preview-photo-btn').forEach((b) => b.classList.remove('border-gray-900'));
        btn.classList.add('border-gray-900');
        if (selectedFrameData && photos[i]) {
          mergeAndShow(photos[i], selectedFrameData.frame_file);
        }
      });
      grid.appendChild(btn);
    });
  }

  async function mergeAndShow(photoUrl, frameUrl) {
    const merged = document.getElementById('preview-merged');
    if (!merged) return;
    merged.innerHTML = '<p class="text-white/60">Merging...</p>';
    try {
      mergedImageDataUrl = await mergePhotoWithFrame(photoUrl, frameUrl);
      merged.innerHTML = '';
      const img = document.createElement('img');
      img.src = mergedImageDataUrl;
      img.alt = 'Preview';
      img.className = 'w-full h-full object-contain';
      merged.appendChild(img);
      const printBtn = document.getElementById('btn-preview-print');
      printBtn.disabled = false;
      printBtn.classList.remove('bg-gray-200', 'text-gray-500', 'cursor-not-allowed');
      printBtn.classList.add('bg-gray-900', 'text-white', 'cursor-pointer');
    } catch (err) {
      console.error('Merge error:', err);
      merged.innerHTML = '<p class="text-red-400">Merge failed</p>';
    }
  }

  document.getElementById('preview-mirror')?.addEventListener('change', () => {
    const photos = camera?.getPhotos() || [];
    if (photos[selectedPhotoIndex] && selectedFrameData) {
      mergeAndShow(photos[selectedPhotoIndex], selectedFrameData.frame_file);
    }
  });

  document.getElementById('btn-preview-back')?.addEventListener('click', () => {
    stateMachine.setState(stateMachine.STATES.CAPTURE);
  });

  async function doPrint() {
    const status = document.getElementById('print-status');
    const done = document.getElementById('print-done');
    if (!mergedImageDataUrl) return;

    const photos = camera?.getPhotos() || [];
    try {
      await session.saveMedia('strip', mergedImageDataUrl);
      for (let i = 0; i < photos.length; i++) {
        await session.saveMedia('image', photos[i], i + 1);
      }
    } catch (err) {
      console.error('Save media error:', err);
    }

    const copies = setting.copies || 1;
    try {
      const printWindow = window.open('', '_blank');
      if (printWindow) {
        printWindow.document.write(`
          <!DOCTYPE html><html><head><title>Print</title>
          <style>@page{size:8cm 11cm;margin:0}body{margin:0}img{width:8cm;height:11cm;object-fit:contain}</style>
          </head><body><img src="${mergedImageDataUrl}" alt="Photostrip"></body></html>
        `);
        printWindow.document.close();
        printWindow.onload = () => {
          printWindow.print();
          setTimeout(() => printWindow.close(), 500);
        };
      }
    } catch (e) {
      console.warn('Print failed:', e);
    }

    status?.classList.add('hidden');
    done?.classList.remove('hidden');
  }

  document.getElementById('btn-print-next')?.addEventListener('click', () => {
    stateMachine.setState(stateMachine.STATES.DONE);
  });

  stateMachine.subscribe((state) => {
    if (state === stateMachine.STATES.DONE && resultUrl) {
      const qrContainer = document.getElementById('qr-container');
      if (qrContainer && !qrContainer.querySelector('img')) {
        const img = document.createElement('img');
        img.src = `https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=${encodeURIComponent(resultUrl)}`;
        img.alt = 'QR Code';
        img.className = 'block';
        qrContainer.appendChild(img);
      }
    }
  });

  document.getElementById('btn-reset')?.addEventListener('click', () => {
    window.location.href = window.location.pathname;
  });

  // Lock / Fullscreen button (manual only – no auto fullscreen on page load)
  document.getElementById('btn-lock-fullscreen')?.addEventListener('click', () => {
    if (document.fullscreenElement || document.webkitFullscreenElement) {
      if (document.exitFullscreen) document.exitFullscreen();
      else if (document.webkitExitFullscreen) document.webkitExitFullscreen();
    } else {
      const el = document.documentElement;
      if (el.requestFullscreen) el.requestFullscreen().catch(() => {});
      else if (el.webkitRequestFullscreen) el.webkitRequestFullscreen();
    }
  });

  // Initialize camera settings modal
  initCameraSettings();

  const initialState = document.body.dataset.initialState || 'IDLE';
  stateMachine.setState(initialState);
});
