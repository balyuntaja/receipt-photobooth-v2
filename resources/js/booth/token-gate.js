/**
 * Token Gate – validasi token langganan sebelum kiosk bisa dipakai.
 * Token disimpan di localStorage per project. Satu token = satu device.
 */

const STORAGE_PREFIX = 'booth_token_';

export function getDeviceFingerprint() {
  const ua = navigator.userAgent || '';
  const lang = navigator.language || '';
  const sw = (screen?.width ?? '').toString();
  const sh = (screen?.height ?? '').toString();
  try {
    return btoa(ua + lang + sw + sh).slice(0, 64);
  } catch {
    return ua.slice(0, 64);
  }
}

export function createTokenGate(projectId, validateUrl) {
  const storageKey = `${STORAGE_PREFIX}${projectId}`;

  let cachedValid = false;
  let validatedToken = null;

  const modal = document.getElementById('credit-modal');
  const tokenInput = document.getElementById('credit-modal-token');
  const validateBtn = document.getElementById('credit-modal-validate');
  const tokenError = document.getElementById('credit-modal-token-error');
  const closeBtn = document.getElementById('credit-modal-close');

  function showModal() {
    if (modal) {
      modal.classList.remove('hidden');
      modal.setAttribute('aria-hidden', 'false');
      tokenError?.classList.add('hidden');
      tokenInput?.focus();
    }
  }

  function hideModal() {
    if (modal) {
      modal.classList.add('hidden');
      modal.setAttribute('aria-hidden', 'true');
    }
  }

  async function validateToken(token, bindDevice = false) {
    if (!token?.trim()) return false;
    const body = { token: token.trim() };
    if (bindDevice) body.device_fingerprint = getDeviceFingerprint();
    const res = await fetch(validateUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
      },
      body: JSON.stringify(body),
    });
    const data = await res.json().catch(() => ({}));
    return data.valid === true;
  }

  function setValidToken(token) {
    validatedToken = token;
    cachedValid = true;
    try {
      localStorage.setItem(storageKey, token);
    } catch (e) {
      console.warn('[token-gate] localStorage set failed', e);
    }
    hideModal();
  }

  async function checkStoredToken() {
    try {
      const stored = localStorage.getItem(storageKey);
      if (!stored) return false;
      const valid = await validateToken(stored, true);
      if (valid) {
        validatedToken = stored;
        cachedValid = true;
        return true;
      }
      localStorage.removeItem(storageKey);
      return false;
    } catch (e) {
      return false;
    }
  }

  async function hasValidToken() {
    if (cachedValid) return true;
    return checkStoredToken();
  }

  validateBtn?.addEventListener('click', async () => {
    const token = tokenInput?.value?.trim();
    if (!token) {
      tokenError.textContent = 'Masukkan token terlebih dahulu';
      tokenError.classList.remove('hidden');
      return;
    }
    tokenError.classList.add('hidden');
    validateBtn.disabled = true;
    validateBtn.textContent = 'Memvalidasi...';
    try {
      const valid = await validateToken(token, true);
      if (valid) {
        setValidToken(token);
      } else {
        tokenError.textContent = 'Token tidak valid atau langganan sudah habis';
        tokenError.classList.remove('hidden');
      }
    } catch (e) {
      tokenError.textContent = 'Gagal memvalidasi. Coba lagi.';
      tokenError.classList.remove('hidden');
    } finally {
      validateBtn.disabled = false;
      validateBtn.textContent = 'Validasi';
    }
  });

  closeBtn?.addEventListener('click', hideModal);

  modal?.addEventListener('click', (e) => {
    if (e.target === modal) hideModal();
  });

  return {
    hasValidToken,
    showModal,
    hideModal,
    /** Call sebelum izinkan Start. Jika belum valid, tampilkan modal dan return false. */
    async requireToken() {
      if (await hasValidToken()) return true;
      showModal();
      return false;
    },
  };
}
