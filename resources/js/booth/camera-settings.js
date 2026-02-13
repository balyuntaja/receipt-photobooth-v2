/**
 * Camera Settings Module
 * ======================
 * Handles camera selection, preview, and localStorage persistence.
 * Based on booth example CameraSettingModal.jsx, ported to plain JS.
 */

import { enumerateCameras, isAndroid } from './camera-utils.js';

const STORAGE_KEY = 'photobooth_selectedCameraId';

// ===========================
// DOM HELPERS
// ===========================

function getModalEl() {
  return document.getElementById('camera-settings-modal');
}

function getSelectEl() {
  return document.getElementById('camera-settings-select');
}

function getPreviewEl() {
  return document.getElementById('camera-settings-preview');
}

function getPrinterStatusEl() {
  return document.getElementById('camera-settings-printer-status');
}

// ===========================
// PREVIEW STREAM
// ===========================

let previewStream = null;

function stopPreviewStream() {
  if (previewStream) {
    previewStream.getTracks().forEach((t) => t.stop());
    previewStream = null;
  }
  const video = getPreviewEl();
  if (video) {
    video.srcObject = null;
    video.src = '';
  }
}

function startPreview(deviceId) {
  stopPreviewStream();
  const video = getPreviewEl();
  if (!video) return;

  const constraints =
    isAndroid() || deviceId === 'android-default'
      ? {
          video: { facingMode: 'environment', aspectRatio: 4 / 3 },
          audio: false,
        }
      : {
          video: {
            deviceId: deviceId ? { ideal: deviceId } : true,
            aspectRatio: 4 / 3,
          },
          audio: false,
        };

  navigator.mediaDevices
    .getUserMedia(constraints)
    .then((stream) => {
      previewStream = stream;
      video.srcObject = stream;
      video.src = '';
      video.play().catch((err) => console.warn('[camera-settings] video play failed:', err));
    })
    .catch((err) => console.error('[camera-settings] preview failed:', err));
}

// ===========================
// CAMERA ENUMERATION
// ===========================

function populateSelect(devices, savedDeviceId) {
  const select = getSelectEl();
  if (!select) return null;

  select.innerHTML = '';
  let selectedId = savedDeviceId;

  if (isAndroid()) {
    const optAndroid = document.createElement('option');
    optAndroid.value = 'android-default';
    optAndroid.textContent = 'Android Camera (Auto - facingMode)';
    select.appendChild(optAndroid);
    selectedId = 'android-default';
  } else {
    if (devices.length === 0) {
      const optNone = document.createElement('option');
      optNone.value = '';
      optNone.textContent = 'No cameras found';
      select.appendChild(optNone);
      return null;
    }

    devices.forEach((cam, i) => {
      const opt = document.createElement('option');
      opt.value = cam.deviceId;
      opt.textContent = cam.label || `Camera ${i + 1}`;
      select.appendChild(opt);
    });

    if (selectedId && !devices.find((d) => d.deviceId === selectedId)) {
      selectedId = devices[0]?.deviceId || null;
    }
    if (!selectedId && devices.length) {
      selectedId = devices[0].deviceId;
    }
  }

  select.value = selectedId || '';
  return select.value;
}

function loadCameras() {
  return enumerateCameras().then((devices) => {
    const saved = localStorage.getItem(STORAGE_KEY);
    const selectedId = populateSelect(devices, saved);
    
    if (selectedId && selectedId !== 'android-default') {
      localStorage.setItem(STORAGE_KEY, selectedId);
    } else if (selectedId === 'android-default') {
      localStorage.removeItem(STORAGE_KEY);
    }
    
    return selectedId;
  });
}

// ===========================
// MODAL OPEN/CLOSE
// ===========================

function onSelectChange() {
  const select = getSelectEl();
  if (!select) return;
  
  const newId = select.value;
  if (newId !== 'android-default') {
    localStorage.setItem(STORAGE_KEY, newId);
  } else {
    localStorage.removeItem(STORAGE_KEY);
  }
  startPreview(newId);
}

function openModal() {
  const modal = getModalEl();
  if (!modal) return;

  modal.classList.remove('hidden');
  modal.classList.add('flex');
  modal.setAttribute('aria-hidden', 'false');

  loadCameras().then((selectedId) => {
    if (selectedId) {
      startPreview(selectedId);
    }
  });

  const select = getSelectEl();
  if (select) {
    select.addEventListener('change', onSelectChange);
  }
}

function closeModal() {
  const modal = getModalEl();
  if (!modal) return;

  modal.classList.add('hidden');
  modal.classList.remove('flex');
  modal.setAttribute('aria-hidden', 'true');

  stopPreviewStream();

  const select = getSelectEl();
  if (select) {
    select.removeEventListener('change', onSelectChange);
  }
}

// ===========================
// PRINTER (STUB)
// ===========================

function handleConnectPrinter() {
  // WebUSB printer connection is not implemented in Laravel kiosk.
  // Use browser's native print dialog instead.
  alert('Printer akan menggunakan dialog print bawaan browser.\nFitur WebUSB tidak tersedia.');
}

// ===========================
// INITIALIZATION
// ===========================

/**
 * Initialize camera settings modal.
 * Call this from kiosk.js on DOMContentLoaded.
 */
export function initCameraSettings() {
  const btnOpen = document.getElementById('btn-camera-settings');
  const btnClose = document.getElementById('camera-settings-close');
  const btnSave = document.getElementById('camera-settings-save');
  const btnConnectPrinter = document.getElementById('camera-settings-connect-printer');
  const modal = getModalEl();

  // Open modal
  btnOpen?.addEventListener('click', () => openModal());

  // Close modal
  btnClose?.addEventListener('click', () => closeModal());

  // Save and close
  btnSave?.addEventListener('click', () => {
    const select = getSelectEl();
    if (select?.value && select.value !== 'android-default') {
      localStorage.setItem(STORAGE_KEY, select.value);
    }
    closeModal();
  });

  // Click outside to close
  modal?.addEventListener('click', (e) => {
    if (e.target === modal) {
      closeModal();
    }
  });

  // ESC key to close
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !modal?.classList.contains('hidden')) {
      closeModal();
    }
  });

  // Printer connection (stub)
  btnConnectPrinter?.addEventListener('click', handleConnectPrinter);
}

/**
 * Get the currently selected camera ID from localStorage.
 * @returns {string|null}
 */
export function getSelectedCameraId() {
  return localStorage.getItem(STORAGE_KEY);
}
