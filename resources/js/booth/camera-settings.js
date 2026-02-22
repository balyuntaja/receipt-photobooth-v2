/**
 * Camera Settings Module (Pengaturan Perangkat)
 * Camera, printer (WebUSB).
 */

import { enumerateCameras, isAndroid } from './camera-utils.js';
import {
  connectPrinterUSB,
  connectPrinterBLE,
  disconnectPrinter,
  isPrinterConnected,
  isWebUSBAvailable,
  isWebBluetoothAvailable,
} from './printer.js';

const STORAGE_KEY = 'photobooth_selectedCameraId';
const PRINTER_TYPE_KEY = 'photobooth_printerType';

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

function getPrinterTypeSelect() {
  return document.getElementById('camera-settings-printer-type');
}

// ===========================
// PREVIEW STREAM
// ===========================

let previewStream = null;

function getPlaceholderEl() {
  return document.getElementById('camera-settings-placeholder');
}

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
  const placeholder = getPlaceholderEl();
  if (placeholder) placeholder.style.display = '';
}

/** Nilai facingMode dari storage/select untuk Android. */
function getFacingModeFromValue(value) {
  if (value === 'android-user') return 'user';
  if (value === 'android-environment') return 'environment';
  return 'environment'; // default
}

function startPreview(deviceId) {
  stopPreviewStream();
  const video = getPreviewEl();
  if (!video) return;

  const useFacingMode = isAndroid() || deviceId === 'android-default' || deviceId === 'android-user' || deviceId === 'android-environment';
  const constraints = useFacingMode
    ? {
        video: { facingMode: getFacingModeFromValue(deviceId), aspectRatio: 4 / 3 },
        audio: false,
      }
    : {
        video: {
          deviceId: deviceId ? { ideal: deviceId } : true,
          aspectRatio: 4 / 3,
        },
        audio: false,
      };

  const placeholder = getPlaceholderEl();
  if (placeholder) placeholder.style.display = '';

  navigator.mediaDevices
    .getUserMedia(constraints)
    .then((stream) => {
      previewStream = stream;
      video.srcObject = stream;
      video.src = '';
      if (placeholder) placeholder.style.display = 'none';
      video.play().catch((err) => console.warn('[camera-settings] video play failed:', err));
    })
    .catch((err) => {
      console.error('[camera-settings] preview failed:', err);
      if (placeholder) placeholder.style.display = '';
    });
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
    const optBack = document.createElement('option');
    optBack.value = 'android-environment';
    optBack.textContent = 'Kamera Belakang';
    select.appendChild(optBack);
    const optFront = document.createElement('option');
    optFront.value = 'android-user';
    optFront.textContent = 'Kamera Depan';
    select.appendChild(optFront);
    // Default atau pilihan tersimpan
    if (savedDeviceId === 'android-user' || savedDeviceId === 'android-environment') {
      selectedId = savedDeviceId;
    } else {
      selectedId = 'android-environment';
    }
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
    
    if (selectedId) {
      localStorage.setItem(STORAGE_KEY, selectedId);
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
  if (newId) {
    localStorage.setItem(STORAGE_KEY, newId);
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

  updatePrinterTypeSelect();
  updatePrinterStatus();

  const select = getSelectEl();
  if (select) {
    select.addEventListener('change', onSelectChange);
  }
}

function updatePrinterTypeSelect() {
  const sel = getPrinterTypeSelect();
  if (!sel) return;

  const usbAvailable = isWebUSBAvailable();
  const btAvailable = isWebBluetoothAvailable();
  const saved = localStorage.getItem(PRINTER_TYPE_KEY) || 'bluetooth';

  sel.innerHTML = '';
  if (btAvailable) {
    const optBt = document.createElement('option');
    optBt.value = 'bluetooth';
    optBt.textContent = 'Bluetooth (BLE)';
    sel.appendChild(optBt);
  }
  if (usbAvailable) {
    const optUsb = document.createElement('option');
    optUsb.value = 'usb';
    optUsb.textContent = 'USB';
    sel.appendChild(optUsb);
  }
  if (!btAvailable && !usbAvailable) {
    const optNone = document.createElement('option');
    optNone.value = '';
    optNone.textContent = 'Tidak didukung';
    sel.appendChild(optNone);
    const btnConnect = document.getElementById('camera-settings-connect-printer');
    if (btnConnect) btnConnect.disabled = true;
    return;
  }

  const validSaved = (saved === 'bluetooth' && btAvailable) || (saved === 'usb' && usbAvailable);
  sel.value = validSaved ? saved : (btAvailable ? 'bluetooth' : 'usb');
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
// PRINTER (WebUSB)
// ===========================

function updatePrinterStatus() {
  const statusEl = getPrinterStatusEl();
  const btnEl = document.getElementById('camera-settings-connect-printer');
  if (!statusEl || !btnEl) return;

  if (isPrinterConnected()) {
    statusEl.textContent = 'Terhubung';
    statusEl.classList.remove('text-red-600');
    statusEl.classList.add('text-green-600');
    btnEl.textContent = 'Putuskan Printer';
    btnEl.classList.remove('bg-blue-600', 'hover:bg-blue-700');
    btnEl.classList.add('bg-gray-500', 'hover:bg-gray-600');
    btnEl.dataset.action = 'disconnect';
  } else {
    statusEl.textContent = 'Tidak Terhubung';
    statusEl.classList.remove('text-green-600');
    statusEl.classList.add('text-red-600');
    btnEl.textContent = 'Hubungkan Printer';
    btnEl.classList.remove('bg-gray-500', 'hover:bg-gray-600');
    btnEl.classList.add('bg-blue-600', 'hover:bg-blue-700');
    btnEl.dataset.action = 'connect';
  }
}

async function handleConnectPrinter() {
  const btnEl = document.getElementById('camera-settings-connect-printer');
  const action = btnEl?.dataset.action || 'connect';

  if (action === 'disconnect') {
    try {
      btnEl.disabled = true;
      await disconnectPrinter();
      updatePrinterStatus();
    } catch (err) {
      console.error('Disconnect printer error:', err);
      alert('Gagal memutuskan printer: ' + (err.message || 'Unknown error'));
    } finally {
      btnEl.disabled = false;
    }
    return;
  }

  const printerType = getPrinterTypeSelect()?.value || 'bluetooth';
  localStorage.setItem(PRINTER_TYPE_KEY, printerType);

  try {
    btnEl.disabled = true;
    if (printerType === 'usb') {
      if (!isWebUSBAvailable()) {
        alert('WebUSB tidak didukung. Gunakan Chrome atau Edge.');
        return;
      }
      await connectPrinterUSB(0x0418);
    } else {
      if (!isWebBluetoothAvailable()) {
        alert('Web Bluetooth tidak didukung. Gunakan Chrome atau Edge.');
        return;
      }
      await connectPrinterBLE();
    }
    updatePrinterStatus();
  } catch (err) {
    if (err.name === 'NotFoundError') {
      alert('Tidak ada printer yang dipilih atau printer tidak ditemukan.');
    } else if (err.name === 'SecurityError') {
      alert('Akses ditolak. Pastikan izin untuk USB/Bluetooth diberikan.');
    } else {
      console.error('Connect printer error:', err);
      alert('Gagal menghubungkan printer: ' + (err.message || 'Unknown error'));
    }
  } finally {
    btnEl.disabled = false;
  }
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
    if (select?.value) {
      localStorage.setItem(STORAGE_KEY, select.value);
    }
    const printerTypeSel = getPrinterTypeSelect();
    if (printerTypeSel?.value) {
      localStorage.setItem(PRINTER_TYPE_KEY, printerTypeSel.value);
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
