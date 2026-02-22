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
import { printPhotostrip, isPrinterConnected } from './printer.js';

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

  const pricePerSession = parseFloat(document.body.dataset.pricePerSession || '0', 10);
  const copyPriceOptions = (() => {
    try {
      const raw = document.body.dataset.copyPriceOptions || '{}';
      const obj = JSON.parse(raw);
      const result = {};
      for (const [k, v] of Object.entries(obj)) {
        const n = parseInt(k, 10);
        if (n >= 1) result[n] = parseFloat(v, 10);
      }
      return Object.keys(result).length ? result : { 1: pricePerSession };
    } catch {
      return { 1: pricePerSession };
    }
  })();
  let selectedCopyCount = Math.min(...Object.keys(copyPriceOptions).map(Number));
  const createPaymentUrl = document.body.dataset.createPaymentUrl || '';
  const applyVoucherUrl = document.body.dataset.applyVoucherUrl || '';
  const confirmFreeUrl = document.body.dataset.confirmFreeUrl || '';
  const midtransClientKey = document.body.dataset.midtransClientKey || '';
  const midtransIsProduction = document.body.dataset.midtransIsProduction === '1';
  const csrfToken = document.body.dataset.csrf || '';

  function initPaymentCopySelector() {
    const container = document.getElementById('payment-copy-options');
    const priceEl = document.getElementById('payment-selected-price');
    const freeBtn = document.getElementById('btn-payment-free');
    if (!container) return;
    container.innerHTML = '';
    const sorted = Object.keys(copyPriceOptions).map(Number).sort((a, b) => a - b);
    sorted.forEach((n) => {
      const price = copyPriceOptions[n];
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.dataset.copies = n;
      btn.dataset.price = price;
      btn.className = `copy-option payment-copy-btn group rounded-2xl border p-4 text-center transition-all hover:border-gray-300 ${selectedCopyCount === n ? 'border-gray-900 bg-gray-900 text-white' : 'border-gray-200 bg-white'}`;
      btn.innerHTML = `
        <div class="text-sm ${selectedCopyCount === n ? 'text-gray-200' : 'text-gray-500'}">Print</div>
        <div class="text-2xl font-bold ${selectedCopyCount === n ? 'text-white' : 'text-gray-900'}">${n}x</div>
        <div class="mt-1 text-sm font-medium ${selectedCopyCount === n ? 'text-gray-200' : 'text-gray-600'}">${price > 0 ? '+ Rp' + price.toLocaleString('id-ID') : 'Gratis'}</div>
      `;
      btn.addEventListener('click', () => {
        selectedCopyCount = n;
        container.querySelectorAll('.payment-copy-btn').forEach((b) => {
          const isSel = parseInt(b.dataset.copies, 10) === n;
          b.classList.toggle('border-gray-900', isSel);
          b.classList.toggle('bg-gray-900', isSel);
          b.classList.toggle('border-gray-200', !isSel);
          b.classList.toggle('bg-white', !isSel);
          b.innerHTML = `
            <div class="text-sm ${isSel ? 'text-gray-200' : 'text-gray-500'}">Print</div>
            <div class="text-2xl font-bold ${isSel ? 'text-white' : 'text-gray-900'}">${b.dataset.copies}x</div>
            <div class="mt-1 text-sm font-medium ${isSel ? 'text-gray-200' : 'text-gray-600'}">${parseFloat(b.dataset.price) > 0 ? 'Rp' + parseFloat(b.dataset.price).toLocaleString('id-ID') : 'Gratis'}</div>
          `;
        });
        const selPrice = copyPriceOptions[n] ?? 0;
        if (priceEl) {
          priceEl.classList.remove('hidden');
          priceEl.textContent = selPrice > 0 ? `Total: Rp ${selPrice.toLocaleString('id-ID')}` : 'Total: Gratis';
        }
        if (freeBtn) {
          freeBtn.textContent = selPrice > 0 ? 'Pilih opsi gratis' : 'Lanjut (Gratis)';
          freeBtn.disabled = selPrice > 0;
        }
      });
      container.appendChild(btn);
    });
    const firstPrice = copyPriceOptions[selectedCopyCount] ?? 0;
    if (priceEl) {
      priceEl.classList.remove('hidden');
      priceEl.textContent = firstPrice > 0 ? `Total: Rp ${firstPrice.toLocaleString('id-ID')}` : 'Total: Gratis';
    }
    if (freeBtn) {
      freeBtn.textContent = firstPrice > 0 ? 'Pilih opsi gratis' : 'Lanjut (Gratis)';
      freeBtn.disabled = firstPrice > 0;
    }
  }

  const stateMachine = createStateMachine({
    IDLE: () => {},
    PAYMENT: (state, prev) => {
      initPaymentCopySelector();
      const cards = document.getElementById('payment-cards');
      const freeWrap = document.getElementById('payment-free-wrap');
      const voucherError = document.getElementById('payment-voucher-error');
      if (cards) cards.style.display = pricePerSession > 0 ? '' : 'none';
      if (freeWrap) {
        freeWrap.classList.toggle('hidden', pricePerSession > 0);
      }
      if (voucherError) {
        voucherError.classList.add('hidden');
        voucherError.textContent = '';
      }
    },
    FRAME: () => {},
    CAPTURE: (state, prev) => {
      if (prev === 'FRAME') {
        const countdownSeconds = setting.countdown_seconds ?? 3;
        camera = createCamera(session, { countdownSeconds });
        camera?.reset();
        applyCaptureSlotOverlay(framesData, getSelectedFrameId());
      }
    },
    PREVIEW: (state, prev) => {
      if (prev === 'CAPTURE' && camera) {
        const photos = camera.getPhotos();
        selectedPhotoIndex = 0;
        renderPreviewPhotos(photos);
        selectedFrameData = framesData.find((f) => f.id === getSelectedFrameId());
        // Tidak merge otomatis, tunggu user klik foto dulu
        mergedImageDataUrl = null;
        const merged = document.getElementById('preview-merged');
        if (merged && selectedFrameData) {
          // Tampilkan template dulu tanpa foto
          merged.innerHTML = '';
          const img = document.createElement('img');
          img.src = selectedFrameData.frame_file;
          img.alt = 'Template Preview';
          img.className = 'w-full h-full object-contain';
          merged.appendChild(img);
          img.onload = () => {
            const aspectRatio = img.naturalWidth / img.naturalHeight;
            merged.style.aspectRatio = `${aspectRatio}`;
          };
        } else if (merged) {
          merged.innerHTML = '<p class="text-gray-500 text-sm">Pilih foto untuk melihat preview</p>';
        }
        const printBtn = document.getElementById('btn-preview-print');
        if (printBtn) {
          printBtn.disabled = true;
          printBtn.classList.remove('bg-gray-900', 'text-white', 'cursor-pointer');
          printBtn.classList.add('bg-gray-200', 'text-gray-500', 'cursor-not-allowed');
        }
      }
    },
    PRINT: (state, prev) => {
      // PRINT state sekarang tidak digunakan langsung dari preview
      // Flow langsung ke RESULT setelah upload dan print attempt
      if (prev === 'PREVIEW' && mergedImageDataUrl) {
        // Fallback: jika masih ada yang masuk ke PRINT state
        doPrint();
      }
    },
    RESULT: (state, prev) => {
      // Panggil handleResultPage saat masuk RESULT (bisa dari PREVIEW atau PRINT)
      handleResultPage();
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

  /**
   * Overlay gelap 50% di luar slot foto. Slot dari template di-scale to fit preview 4:3:
   * memenuhi width atau height sambil jaga aspect ratio slot, lalu di-center.
   */
  function applyCaptureSlotOverlay(frames, selectedFrameId) {
    const overlay = document.getElementById('capture-slot-overlay');
    const slotTop = document.getElementById('slot-top');
    const slotLeft = document.getElementById('slot-left');
    const slotRight = document.getElementById('slot-right');
    const slotBottom = document.getElementById('slot-bottom');
    if (!overlay || !slotTop || !slotLeft || !slotRight || !slotBottom) return;

    const frame = frames.find((f) => f.id === selectedFrameId);
    const slots = frame?.photo_slots;
    const slot = Array.isArray(slots) && slots.length > 0 ? slots[0] : null;

    if (!slot || slot.width == null || slot.height == null || slot.width <= 0 || slot.height <= 0) {
      overlay.classList.add('hidden');
      return;
    }

    const cameraAspect = 4 / 3;
    const slotAspect = slot.width / slot.height;
    let leftPct, topPct, widthPct, heightPct;

    if (slotAspect >= cameraAspect) {
      // Slot lebih lebar/sama dengan 4:3 → penuh lebar, height dihitung, center vertikal
      widthPct = 100;
      heightPct = (slot.height / slot.width) * cameraAspect * 100;
      leftPct = 0;
      topPct = (100 - heightPct) / 2;
    } else {
      // Slot lebih tinggi → penuh tinggi, width dihitung, center horizontal
      heightPct = 100;
      widthPct = (slot.width / slot.height) / cameraAspect * 100;
      topPct = 0;
      leftPct = (100 - widthPct) / 2;
    }

    slotTop.style.height = `${topPct}%`;
    slotLeft.style.top = `${topPct}%`;
    slotLeft.style.left = '0';
    slotLeft.style.width = `${leftPct}%`;
    slotLeft.style.height = `${heightPct}%`;
    slotRight.style.top = `${topPct}%`;
    slotRight.style.left = `${leftPct + widthPct}%`;
    slotRight.style.width = `${100 - leftPct - widthPct}%`;
    slotRight.style.height = `${heightPct}%`;
    slotBottom.style.top = `${topPct + heightPct}%`;
    slotBottom.style.height = `${100 - topPct - heightPct}%`;

    overlay.classList.remove('hidden');
  }

  const frames = initFrames(stateMachine, session);

  // Start → Payment: delegation + binding ke setiap tombol (klik tombol atau wrapper-nya)
  function goToPayment() {
    stateMachine.setState(stateMachine.STATES.PAYMENT);
  }
  const welcomeScreen = document.getElementById('screen-welcome');
  welcomeScreen?.addEventListener('click', (e) => {
    if (e.target.closest('.welcome-start-btn') || e.target.closest('.welcome-component--button')) {
      e.preventDefault();
      goToPayment();
    }
  });
  document.querySelectorAll('.welcome-start-btn').forEach((btn) => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      goToPayment();
    });
  });
  const btnStartById = document.getElementById('btn-start');
  if (btnStartById && !btnStartById.classList.contains('welcome-start-btn')) {
    btnStartById.addEventListener('click', goToPayment);
  }

  document.getElementById('btn-payment-back')?.addEventListener('click', () => {
    stateMachine.setState(stateMachine.STATES.IDLE);
  });

  document.getElementById('btn-payment-free')?.addEventListener('click', async () => {
    if (!confirmFreeUrl) return;
    try {
      const res = await fetch(confirmFreeUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ copy_count: selectedCopyCount }),
      });
      const data = await res.json().catch(() => ({}));
      if (res.ok && data.success) {
        stateMachine.setState(stateMachine.STATES.FRAME);
      } else {
        console.error('Confirm free failed', data.message || res.statusText);
      }
    } catch (err) {
      console.error('Confirm free error', err);
    }
  });

  document.getElementById('btn-payment-voucher-apply')?.addEventListener('click', async () => {
    const input = document.getElementById('payment-voucher-input');
    const errEl = document.getElementById('payment-voucher-error');
    const code = input?.value?.trim();
    if (!code) {
      if (errEl) {
        errEl.textContent = 'Masukkan kode voucher';
        errEl.classList.remove('hidden');
      }
      return;
    }
    if (!applyVoucherUrl) return;
    try {
      const res = await fetch(applyVoucherUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ code, copy_count: selectedCopyCount }),
      });
      const data = await res.json().catch(() => ({}));
      if (res.ok && data.success) {
        if (errEl) {
          errEl.classList.add('hidden');
          errEl.textContent = '';
        }
        stateMachine.setState(stateMachine.STATES.FRAME);
      } else {
        if (errEl) {
          errEl.textContent = data.message || 'Voucher tidak valid';
          errEl.classList.remove('hidden');
        }
      }
    } catch (err) {
      console.error('Apply voucher error', err);
      if (errEl) {
        errEl.textContent = 'Gagal memproses voucher';
        errEl.classList.remove('hidden');
      }
    }
  });

  let snapPopupOpen = false; // Flag untuk mencegah multiple calls snap.pay

  document.getElementById('btn-payment-qris')?.addEventListener('click', async () => {
    if (!createPaymentUrl || snapPopupOpen) return; // Jangan jalankan jika popup sudah terbuka
    const btn = document.getElementById('btn-payment-qris');
    if (btn) btn.disabled = true;
    try {
      const res = await fetch(createPaymentUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ copy_count: selectedCopyCount }),
      });
      const data = await res.json().catch(() => ({}));
      const snapToken = data.snap_token;
      if (!snapToken) {
        console.error('No snap_token', data.message);
        if (btn) btn.disabled = false;
        return;
      }
      await loadMidtransSnap();
      if (typeof window.snap !== 'undefined' && window.snap.pay && !snapPopupOpen) {
        snapPopupOpen = true; // Set flag sebelum memanggil snap.pay
        window.snap.pay(snapToken, {
          onSuccess: () => {
            snapPopupOpen = false;
            stateMachine.setState(stateMachine.STATES.FRAME);
          },
          onPending: () => {
            // Popup masih terbuka, jangan reset flag
          },
          onError: (err) => {
            console.error('Midtrans error', err);
            snapPopupOpen = false;
            if (btn) btn.disabled = false;
          },
          onClose: () => {
            snapPopupOpen = false;
            if (btn) btn.disabled = false;
          },
        });
      } else {
        console.error('Midtrans Snap not loaded or popup already open');
        if (btn) btn.disabled = false;
      }
    } catch (err) {
      console.error('Create payment error', err);
      snapPopupOpen = false;
      if (btn) btn.disabled = false;
    }
  });

  function loadMidtransSnap() {
    if (window.snap && window.snap.pay) return Promise.resolve();
    const scriptId = 'midtrans-snap-script';
    if (document.getElementById(scriptId)) {
      return new Promise((resolve) => {
        const check = () => {
          if (window.snap && window.snap.pay) return resolve();
          setTimeout(check, 50);
        };
        check();
      });
    }
    return new Promise((resolve, reject) => {
      const script = document.createElement('script');
      script.id = scriptId;
      script.src = midtransIsProduction
        ? 'https://app.midtrans.com/snap/snap.js'
        : 'https://app.sandbox.midtrans.com/snap/snap.js';
      script.setAttribute('data-client-key', midtransClientKey);
      script.onload = () => resolve();
      script.onerror = () => reject(new Error('Failed to load Midtrans Snap'));
      document.body.appendChild(script);
    });
  }

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
      img.className = 'w-full aspect-4/3 object-cover';
      btn.appendChild(img);
      btn.addEventListener('click', () => {
        selectedPhotoIndex = i;
        grid.querySelectorAll('.preview-photo-btn').forEach((b) => b.classList.remove('border-gray-900'));
        btn.classList.add('border-gray-900');
        // Merge hanya saat foto diklik
        if (selectedFrameData && photos[i]) {
          renderPreviewLayout(photos[i], selectedFrameData.frame_file);
        }
      });
      grid.appendChild(btn);
    });
  }

  /**
   * Tampilkan preview: merge template + foto via canvas. Foto di dalam photo slots.
   * Mirror checkbox mengubah tampilan (re-merge). Merge final saat klik "Lanjut ke Print".
   */
  async function renderPreviewLayout(photoUrl, frameUrl) {
    const merged = document.getElementById('preview-merged');
    if (!merged) return;
    merged.innerHTML = '<p class="text-gray-500 text-sm">Merging...</p>';
    try {
      const photoArea = selectedFrameData?.photo_slots?.[0];
      const templateWidth = selectedFrameData?.template_width || 945;
      const templateHeight = selectedFrameData?.template_height || 1299;
      const mirrorChecked = document.getElementById('preview-mirror')?.checked ?? false;
      const options = photoArea ? {
        photoArea: {
          x: photoArea.x, y: photoArea.y, width: photoArea.width, height: photoArea.height,
          canvasWidth: 945, canvasHeight: 1299, templateWidth, templateHeight,
        },
        mirror: mirrorChecked,
      } : { mirror: mirrorChecked };

      const dataUrl = await mergePhotoWithFrame(photoUrl, frameUrl, options);
      merged.innerHTML = '';
      const img = document.createElement('img');
      img.src = dataUrl;
      img.alt = 'Preview';
      img.className = 'w-full h-full object-contain';
      merged.appendChild(img);
      img.onload = () => {
        merged.style.aspectRatio = `${img.naturalWidth / img.naturalHeight}`;
      };

      const printBtn = document.getElementById('btn-preview-print');
      if (printBtn) {
        printBtn.disabled = false;
        printBtn.classList.remove('bg-gray-200', 'text-gray-500', 'cursor-not-allowed');
        printBtn.classList.add('bg-gray-900', 'text-white', 'cursor-pointer');
      }
    } catch (err) {
      console.error('Merge error:', err);
      merged.innerHTML = '<p class="text-red-400 text-sm">Merge failed</p>';
    }
  }

  document.getElementById('preview-mirror')?.addEventListener('change', () => {
    const photos = camera?.getPhotos() || [];
    if (photos[selectedPhotoIndex] && selectedFrameData) {
      renderPreviewLayout(photos[selectedPhotoIndex], selectedFrameData.frame_file);
    }
  });

  /**
   * Merge foto + template ke canvas (dipanggil saat klik Lanjut ke Print).
   */
  async function mergeForPrint(photoUrl, frameUrl) {
    const photoArea = selectedFrameData?.photo_slots?.[0];
    const templateWidth = selectedFrameData?.template_width || 945;
    const templateHeight = selectedFrameData?.template_height || 1299;
    const mirrorChecked = document.getElementById('preview-mirror')?.checked ?? false;
    const options = photoArea ? {
      photoArea: {
        x: photoArea.x, y: photoArea.y, width: photoArea.width, height: photoArea.height,
        canvasWidth: 945, canvasHeight: 1299, templateWidth, templateHeight,
      },
      mirror: mirrorChecked,
    } : { mirror: mirrorChecked };
    return mergePhotoWithFrame(photoUrl, frameUrl, options);
  }

  document.getElementById('btn-preview-back')?.addEventListener('click', () => {
    stateMachine.setState(stateMachine.STATES.CAPTURE);
  });

  document.getElementById('btn-preview-print')?.addEventListener('click', async () => {
    const photos = camera?.getPhotos() || [];
    const photoUrl = photos[selectedPhotoIndex];
    if (!photoUrl || !selectedFrameData) return;

    const printBtn = document.getElementById('btn-preview-print');
    if (printBtn) { printBtn.disabled = true; printBtn.textContent = 'Memproses...'; }
    try {
      mergedImageDataUrl = await mergeForPrint(photoUrl, selectedFrameData.frame_file);
    } catch (err) {
      console.error('Merge error:', err);
      if (printBtn) { printBtn.disabled = false; printBtn.textContent = 'Lanjut ke Print'; }
      return;
    }
    if (printBtn) printBtn.textContent = 'Lanjut ke Print';

    // Upload foto ke database
    try {
      await session.saveMedia('strip', mergedImageDataUrl);
      for (let i = 0; i < photos.length; i++) {
        await session.saveMedia('image', photos[i], i + 1);
      }
    } catch (err) {
      console.error('Save media error:', err);
      // Tetap lanjutkan meskipun ada error upload
    }

    // Coba print ke receipt printer (non-blocking, tidak menghalangi flow)
    const copies = selectedCopyCount || setting.copies || 1;
    if (isPrinterConnected()) {
      try {
        await printPhotostrip(mergedImageDataUrl, copies);
        console.log(`Printed ${copies} copy/copies successfully`);
      } catch (e) {
        console.warn('Print failed:', e);
        // Tidak menghalangi flow meskipun print gagal
      }
    } else {
      console.warn('Printer tidak terhubung, skip printing');
      // Tidak menghalangi flow meskipun printer tidak terhubung
    }

    // Langsung ke RESULT state untuk menampilkan QR code
    stateMachine.setState(stateMachine.STATES.RESULT);
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

    const copies = selectedCopyCount || setting.copies || 1;
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
    stateMachine.setState(stateMachine.STATES.RESULT);
  });

  async function handleResultPage() {
    const uploadStatus = document.getElementById('result-upload-status');
    const qrSection = document.getElementById('result-qr-section');
    const resultError = document.getElementById('result-error');
    const resultActions = document.getElementById('result-actions');
    const qrContainer = document.getElementById('result-qr-code');
    const resultUrlEl = document.getElementById('result-url');
    
    if (!uploadStatus || !qrSection) return;

    // Pastikan foto sudah di-upload (jika belum di-upload di button click)
    const photos = camera?.getPhotos() || [];
    try {
      // Upload strip jika belum
      if (mergedImageDataUrl) {
        await session.saveMedia('strip', mergedImageDataUrl);
      }
      // Upload individual photos jika belum
      for (let i = 0; i < photos.length; i++) {
        await session.saveMedia('image', photos[i], i + 1);
      }
    } catch (err) {
      console.error('Upload error:', err);
      uploadStatus.classList.add('hidden');
      if (resultError) {
        resultError.classList.remove('hidden');
        resultError.textContent = `Gagal mengupload foto: ${err.message || 'Unknown error'}`;
      }
      return;
    }

    // Generate QR code menggunakan resultUrl dari data attribute atau fallback
    const sessionId = document.body.dataset.sessionId;
    const finalResultUrl = resultUrl || `${window.location.origin}/booth/result/${sessionId}`;
    
    uploadStatus.classList.add('hidden');
    qrSection.classList.remove('hidden');
    if (resultActions) {
      resultActions.classList.remove('hidden');
    }

    // Sesi selesai setelah QR muncul
    try {
      await session.updateSession({ status: 'completed' });
    } catch (err) {
      console.warn('Gagal update status session:', err);
    }

    // Generate QR code menggunakan library atau API
    if (qrContainer) {
      qrContainer.innerHTML = '';
      const qrImg = document.createElement('img');
      qrImg.src = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(finalResultUrl)}`;
      qrImg.alt = 'QR Code';
      qrImg.className = 'w-full max-w-xs';
      qrContainer.appendChild(qrImg);
    }

    if (resultUrlEl) {
      resultUrlEl.textContent = finalResultUrl;
    }
  }

  document.getElementById('btn-result-home')?.addEventListener('click', () => {
    const projectId = document.body.dataset.projectId;
    if (projectId) {
      window.location.href = `/booth/${projectId}`;
    } else {
      stateMachine.setState(stateMachine.STATES.RESET);
    }
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
    const projectId = document.body.dataset.projectId;
    if (projectId) {
      window.location.href = `/booth/${projectId}`;
    } else {
      window.location.reload();
    }
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
