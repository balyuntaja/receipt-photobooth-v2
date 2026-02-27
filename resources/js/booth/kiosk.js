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
  const validateVoucherUrl = document.body.dataset.validateVoucherUrl || '';
  const applyVoucherUrl = document.body.dataset.applyVoucherUrl || '';
  const confirmFreeUrl = document.body.dataset.confirmFreeUrl || '';
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

  function getReviewOrderCopyLimits() {
    const sorted = Object.keys(copyPriceOptions).map(Number).sort((a, b) => a - b);
    return { min: Math.min(...sorted), max: Math.max(...sorted) };
  }

  function updateReviewOrderDisplay() {
    const copyValueEl = document.getElementById('review-copy-value');
    const copyPriceEl = document.getElementById('review-copy-price');
    const subtotalEl = document.getElementById('review-subtotal');
    const totalEl = document.getElementById('review-total');
    const promoHintEl = document.getElementById('review-copy-promo');
    const { min: minCopy } = getReviewOrderCopyLimits();
    const price = copyPriceOptions[selectedCopyCount] ?? 0;
    const formatRp = (n) => 'Rp ' + (n || 0).toLocaleString('id-ID');
    if (copyValueEl) copyValueEl.textContent = selectedCopyCount;
    if (copyPriceEl) copyPriceEl.textContent = formatRp(price);
    if (subtotalEl) subtotalEl.textContent = formatRp(price);
    if (totalEl) totalEl.textContent = formatRp(price);
    if (promoHintEl) {
      promoHintEl.classList.toggle('hidden', selectedCopyCount < 2);
      promoHintEl.textContent = selectedCopyCount >= 2 ? `Dapat ${selectedCopyCount} strip!` : '';
    }
    const minusBtn = document.getElementById('review-copy-minus');
    if (minusBtn) minusBtn.disabled = selectedCopyCount <= minCopy;
  }

  function initReviewOrderScreen() {
    const { min: minCopy, max: maxCopy } = getReviewOrderCopyLimits();
    if (selectedCopyCount < minCopy || selectedCopyCount > maxCopy) {
      selectedCopyCount = minCopy;
    }
    updateReviewOrderDisplay();
    document.getElementById('review-payment-error')?.classList.add('hidden');
    document.getElementById('review-payment-error')?.replaceChildren();
    // Reset tombol "Lanjutkan ke Pembayaran" saat masuk ke layar (mis. kembali dari payment)
    reviewToPaymentInProgress = false;
    const reviewBtn = document.getElementById('btn-review-to-payment');
    if (reviewBtn) {
      reviewBtn.disabled = false;
      reviewBtn.removeAttribute('aria-busy');
      reviewBtn.textContent = reviewBtn.dataset.defaultLabel ?? 'Lanjutkan ke Pembayaran';
    }
  }

  const initialState = document.body.dataset.initialState || 'IDLE';
  const stateMachine = createStateMachine({
    IDLE: () => {},
    REVIEW_ORDER: (state, prev) => {
      initReviewOrderScreen();
    },
    PROMO_CODE: (state, prev) => {
      const input = document.getElementById('promo-code-input');
      const errEl = document.getElementById('promo-code-error');
      if (errEl) {
        errEl.classList.add('hidden');
        errEl.textContent = '';
      }
      if (input) {
        input.value = '';
        input.focus();
      }
      const btn = document.getElementById('btn-promo-apply');
      if (btn) {
        btn.disabled = false;
        btn.textContent = btn.dataset.defaultLabel ?? 'Terapkan';
      }
      promoApplyInProgress = false;
    },
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
  }, initialState);

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

  // Start → Tinjau Pesanan (REVIEW_ORDER), lalu lanjut ke Payment
  function goToReviewOrder() {
    stateMachine.setState(stateMachine.STATES.REVIEW_ORDER);
  }
  const welcomeScreen = document.getElementById('screen-welcome');
  welcomeScreen?.addEventListener('click', (e) => {
    if (e.target.closest('.welcome-start-btn') || e.target.closest('.welcome-component--button')) {
      e.preventDefault();
      goToReviewOrder();
    }
  });
  document.querySelectorAll('.welcome-start-btn').forEach((btn) => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      goToReviewOrder();
    });
  });
  const btnStartById = document.getElementById('btn-start');
  if (btnStartById && !btnStartById.classList.contains('welcome-start-btn')) {
    btnStartById.addEventListener('click', goToReviewOrder);
  }

  // Tinjau Pesanan: back, quantity +/- , lanjut ke pembayaran, kode promo
  document.getElementById('btn-review-back')?.addEventListener('click', () => {
    stateMachine.setState(stateMachine.STATES.IDLE);
  });
  const { min: reviewMinCopy, max: reviewMaxCopy } = (() => {
    const s = Object.keys(copyPriceOptions).map(Number).sort((a, b) => a - b);
    return { min: Math.min(...s), max: Math.max(...s) };
  })();
  document.getElementById('review-copy-minus')?.addEventListener('click', () => {
    if (selectedCopyCount <= reviewMinCopy) return;
    selectedCopyCount = Math.max(reviewMinCopy, selectedCopyCount - 1);
    updateReviewOrderDisplay();
  });
  document.getElementById('review-copy-plus')?.addEventListener('click', () => {
    if (selectedCopyCount >= reviewMaxCopy) return;
    selectedCopyCount = Math.min(reviewMaxCopy, selectedCopyCount + 1);
    updateReviewOrderDisplay();
  });
  let reviewToPaymentInProgress = false;
  document.getElementById('btn-review-to-payment')?.addEventListener('click', async () => {
    if (reviewToPaymentInProgress) return;
    reviewToPaymentInProgress = true;
    const btn = document.getElementById('btn-review-to-payment');
    const defaultLabel = btn?.dataset.defaultLabel ?? 'Lanjutkan ke Pembayaran';
    const loadingLabel = btn?.dataset.loadingLabel ?? 'Memproses...';
    if (btn) {
      btn.disabled = true;
      btn.setAttribute('aria-busy', 'true');
      btn.textContent = loadingLabel;
    }
    function resetButton() {
      reviewToPaymentInProgress = false;
      if (btn) {
        btn.disabled = false;
        btn.removeAttribute('aria-busy');
        btn.textContent = defaultLabel;
      }
    }
    const price = copyPriceOptions[selectedCopyCount] ?? 0;
    const reviewErrorEl = document.getElementById('review-payment-error');
    function showReviewError(msg) {
      if (reviewErrorEl) {
        reviewErrorEl.textContent = msg || 'Gagal memproses pembayaran. Coba lagi.';
        reviewErrorEl.classList.remove('hidden');
      }
    }
    function hideReviewError() {
      if (reviewErrorEl) {
        reviewErrorEl.textContent = '';
        reviewErrorEl.classList.add('hidden');
      }
    }
    hideReviewError();
    try {
      if (price > 0) {
        if (!createPaymentUrl) {
          resetButton();
          return;
        }
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
        if (data.snap_token) {
          console.error('Backend returned snap_token; deploy latest (Core API only).');
          showReviewError('Backend masih mengembalikan Snap. Pastikan deploy terbaru.');
          resetButton();
          return;
        }
        const redirectUrl = data.redirect_url;
        if (!redirectUrl) {
          const msg = data.message || (res.ok ? '' : `Error ${res.status}`);
          console.error('No redirect_url', msg);
          showReviewError(msg || 'Tidak ada redirect. Coba mulai sesi baru.');
          resetButton();
          return;
        }
        if (!res.ok) {
          showReviewError(data.message || `Error ${res.status}`);
          resetButton();
          return;
        }
        window.location.href = redirectUrl;
      } else {
        if (!confirmFreeUrl) {
          resetButton();
          return;
        }
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
          resetButton();
        }
      }
    } catch (err) {
      console.error('Payment error', err);
      showReviewError(err.message || 'Koneksi gagal. Periksa jaringan dan coba lagi.');
      resetButton();
    }
  });
  document.getElementById('btn-review-promo')?.addEventListener('click', () => {
    stateMachine.setState(stateMachine.STATES.PROMO_CODE);
  });

  // Promo code screen: Batal → REVIEW_ORDER; Terapkan → validate → 100% apply-voucher+FRAME, <100% createPayment (Core API) + redirect
  document.getElementById('btn-promo-cancel')?.addEventListener('click', () => {
    stateMachine.setState(stateMachine.STATES.REVIEW_ORDER);
  });
  let promoApplyInProgress = false;
  document.getElementById('btn-promo-apply')?.addEventListener('click', async () => {
    if (promoApplyInProgress) return;
    const input = document.getElementById('promo-code-input');
    const errEl = document.getElementById('promo-code-error');
    const btn = document.getElementById('btn-promo-apply');
    const code = input?.value?.trim();
    if (!code) {
      if (errEl) {
        errEl.textContent = 'Masukkan kode promo';
        errEl.classList.remove('hidden');
      }
      return;
    }
    if (!validateVoucherUrl) return;
    promoApplyInProgress = true;
    if (btn) {
      btn.disabled = true;
      btn.textContent = btn.dataset.loadingLabel ?? 'Memproses...';
    }
    if (errEl) {
      errEl.classList.add('hidden');
      errEl.textContent = '';
    }
    try {
      const validateRes = await fetch(validateVoucherUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ code, copy_count: selectedCopyCount }),
      });
      const validateData = await validateRes.json().catch(() => ({}));
      if (!validateRes.ok || !validateData.valid) {
        if (errEl) {
          errEl.textContent = validateData.message || 'Voucher tidak valid';
          errEl.classList.remove('hidden');
        }
        if (btn) {
          btn.disabled = false;
          btn.textContent = btn.dataset.defaultLabel ?? 'Terapkan';
        }
        promoApplyInProgress = false;
        return;
      }
      const amountAfterDiscount = validateData.amount_after_discount ?? 0;
      if (amountAfterDiscount <= 0) {
        const applyRes = await fetch(applyVoucherUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
          },
          body: JSON.stringify({ code, copy_count: selectedCopyCount }),
        });
        const applyData = await applyRes.json().catch(() => ({}));
        if (applyRes.ok && applyData.success) {
          stateMachine.setState(stateMachine.STATES.FRAME);
        } else {
          if (errEl) {
            errEl.textContent = applyData.message || 'Gagal menerapkan voucher';
            errEl.classList.remove('hidden');
          }
          if (btn) {
            btn.disabled = false;
            btn.textContent = btn.dataset.defaultLabel ?? 'Terapkan';
          }
        }
        promoApplyInProgress = false;
        return;
      }
      const payRes = await fetch(createPaymentUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ copy_count: selectedCopyCount, voucher_code: code }),
      });
      const payData = await payRes.json().catch(() => ({}));
      if (payData.snap_token) {
        console.error('Backend returned snap_token; deploy latest (Core API only).');
        if (btn) { btn.disabled = false; btn.textContent = btn.dataset.defaultLabel ?? 'Terapkan'; }
        promoApplyInProgress = false;
        return;
      }
      const redirectUrl = payData.redirect_url;
      if (!redirectUrl) {
        if (errEl) {
          errEl.textContent = payData.message || 'Gagal membuat pembayaran';
          errEl.classList.remove('hidden');
        }
        if (btn) {
          btn.disabled = false;
          btn.textContent = btn.dataset.defaultLabel ?? 'Terapkan';
        }
        promoApplyInProgress = false;
        return;
      }
      window.location.href = redirectUrl;
    } catch (err) {
      console.error('Promo apply error', err);
      if (errEl) {
        errEl.textContent = 'Gagal memproses. Coba lagi.';
        errEl.classList.remove('hidden');
      }
      if (btn) {
        btn.disabled = false;
        btn.textContent = btn.dataset.defaultLabel ?? 'Terapkan';
      }
      promoApplyInProgress = false;
    }
  });
  document.getElementById('promo-code-input')?.addEventListener('input', () => {
    const errEl = document.getElementById('promo-code-error');
    if (errEl) {
      errEl.classList.add('hidden');
      errEl.textContent = '';
    }
  });

  document.getElementById('btn-payment-back')?.addEventListener('click', () => {
    stateMachine.setState(stateMachine.STATES.REVIEW_ORDER);
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

  document.getElementById('btn-payment-qris')?.addEventListener('click', async () => {
    if (!createPaymentUrl) return;
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
      if (data.snap_token) {
        console.error('Backend returned snap_token; deploy latest (Core API only).');
        if (btn) btn.disabled = false;
        return;
      }
      const redirectUrl = data.redirect_url;
      if (!redirectUrl) {
        console.error('No redirect_url', data.message);
        if (btn) btn.disabled = false;
        return;
      }
      window.location.href = redirectUrl;
    } catch (err) {
      console.error('Create payment error', err);
      if (btn) btn.disabled = false;
    }
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

  stateMachine.setState(initialState);
});
