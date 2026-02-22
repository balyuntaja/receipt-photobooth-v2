/**
 * Thermal Printer Utilities - WebUSB and Web Bluetooth (BLE)
 * For ESC/POS compatible thermal printers (58mm)
 */

const PRINTER_STATE = {
  type: null,  // 'usb' | 'bluetooth'
  device: null,
  characteristic: null,  // BLE write characteristic
  server: null,          // BLE GATT server
};

/** Common BLE service UUIDs for thermal printers */
const BLE_SERVICES = [
  '6e400001-b5a3-f393-e0a9-e50e24dcca9e', // Nordic UART
  '49535343-fe7d-4ae5-8fa9-9fafd205e455', // HM-10 / Serial
  'e7810a71-73ae-499d-8c15-faa9aef0c3f2', // Custom thermal (XP-D4601B, etc)
  '0000ffe0-0000-1000-8000-00805f9b34fb', // Common BLE serial
];

/** BLE chunk size: 20 = default MTU (aman), 185 = setelah negosiasi (lebih cepat) */
const BLE_CHUNK_SIZE = 20;
const BLE_CHUNK_DELAY_MS = 15; // Delay antar chunk agar printer tidak kewalahan

/** Nordic UART TX - karakteristik untuk menulis ke BLE serial */
const NORDIC_UART_TX = '6e400002-b5a3-f393-e0a9-e50e24dcca9e';

/**
 * Connect to thermal printer via WebUSB
 * @param {number} vendorId - USB vendor ID (default: 0x0418)
 */
export async function connectPrinterUSB(vendorId = 0x0418) {
  if (!navigator.usb) {
    throw new Error('WebUSB tidak didukung. Gunakan Chrome atau Edge.');
  }
  await disconnectPrinter();

  const device = await navigator.usb.requestDevice({ filters: [{ vendorId }] });
  await device.open();
  await device.selectConfiguration(1);
  await device.claimInterface(0);

  PRINTER_STATE.type = 'usb';
  PRINTER_STATE.device = device;
  PRINTER_STATE.characteristic = null;
  PRINTER_STATE.server = null;
  console.log('[Printer] USB connected');
}

/**
 * Connect to thermal printer via Web Bluetooth (BLE)
 */
export async function connectPrinterBLE() {
  if (!navigator.bluetooth) {
    throw new Error('Web Bluetooth tidak didukung. Gunakan Chrome atau Edge.');
  }
  await disconnectPrinter();

  const device = await navigator.bluetooth.requestDevice({
    acceptAllDevices: true,
    optionalServices: BLE_SERVICES,
  });

  const server = await device.gatt.connect();
  const services = await server.getPrimaryServices();

  let writeChar = null;
  let nordicTx = null;

  for (const svc of services) {
    const chars = await svc.getCharacteristics();
    for (const c of chars) {
      if (!c.properties.write && !c.properties.writeWithoutResponse) continue;
      if (c.uuid.toLowerCase() === NORDIC_UART_TX) {
        nordicTx = c;
        break;
      }
      if (!writeChar) writeChar = c;
    }
  }

  const chosen = nordicTx || writeChar;
  if (!chosen) {
    await server.disconnect();
    throw new Error('Tidak ditemukan karakteristik untuk menulis data. Pastikan printer BLE mendukung ESC/POS.');
  }

  PRINTER_STATE.type = 'bluetooth';
  PRINTER_STATE.device = device;
  PRINTER_STATE.characteristic = chosen;
  PRINTER_STATE.server = server;
  console.log('[Printer] BLE connected:', device.name, 'char:', chosen.uuid);
}

/**
 * Connect via USB (alias for backward compatibility)
 */
export async function connectPrinter(vendorId = 0x0418) {
  return connectPrinterUSB(vendorId);
}

/**
 * Check if printer is connected
 */
export function isPrinterConnected() {
  return PRINTER_STATE.device !== null;
}

/**
 * Get current printer connection type
 * @returns {'usb'|'bluetooth'|null}
 */
export function getPrinterType() {
  return PRINTER_STATE.type;
}

/**
 * Check if WebUSB is available
 */
export function isWebUSBAvailable() {
  return !!navigator.usb;
}

/**
 * Check if Web Bluetooth is available
 */
export function isWebBluetoothAvailable() {
  return !!navigator.bluetooth;
}

/**
 * Disconnect printer
 */
export async function disconnectPrinter() {
  if (!PRINTER_STATE.device) return;
  try {
    if (PRINTER_STATE.type === 'bluetooth' && PRINTER_STATE.server?.connected) {
      PRINTER_STATE.server.disconnect();
    } else if (PRINTER_STATE.type === 'usb' && PRINTER_STATE.device?.opened) {
      await PRINTER_STATE.device.close();
    }
  } catch (err) {
    console.warn('[Printer] Disconnect error:', err);
  }
  PRINTER_STATE.type = null;
  PRINTER_STATE.device = null;
  PRINTER_STATE.characteristic = null;
  PRINTER_STATE.server = null;
  console.log('[Printer] Disconnected');
}

/**
 * Floyd-Steinberg Dithering
 * @param {Uint8ClampedArray} data - ImageData RGBA array
 * @param {number} width - Image width
 * @param {number} height - Image height
 */
function floydSteinbergDithering(data, width, height) {
  const getIndex = (x, y) => (width * y + x) * 4;

  for (let y = 0; y < height; y++) {
    for (let x = 0; x < width; x++) {
      const idx = getIndex(x, y);
      const oldPixel = data[idx];
      const newPixel = oldPixel < 128 ? 0 : 255;
      const error = oldPixel - newPixel;

      // Set RGB channels to new pixel value
      data[idx] = data[idx + 1] = data[idx + 2] = newPixel;

      // Helper function to add error to neighboring pixels
      const add = (px, py, factor) => {
        if (px >= 0 && px < width && py >= 0 && py < height) {
          const i = getIndex(px, py);
          const val = data[i] + error * factor;
          const clampedVal = Math.max(0, Math.min(255, val));
          data[i] = data[i + 1] = data[i + 2] = clampedVal;
        }
      };

      // Distribute error to neighboring pixels
      add(x + 1, y, 7 / 16);
      add(x - 1, y + 1, 3 / 16);
      add(x, y + 1, 5 / 16);
      add(x + 1, y + 1, 1 / 16);
    }
  }
}

/**
 * Convert image to ESC/POS format (1-bit bitmap with dithering)
 * @param {string} imageDataUrl - Data URL of the image
 * @param {number} width - Target width in pixels (default: 680 for 58mm printer)
 * @returns {Promise<Uint8Array>} ESC/POS command bytes
 */
async function convertToESCPos(imageDataUrl, width = 680) {
  return new Promise((resolve, reject) => {
    const img = new Image();
    img.onload = () => {
      try {
        // Create canvas to resize and convert image
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        if (!ctx) {
          reject(new Error('Could not get canvas context'));
          return;
        }

        // Calculate height maintaining aspect ratio
        const aspectRatio = img.height / img.width;
        const height = Math.round(width * aspectRatio);

        canvas.width = width;
        canvas.height = height;

        // Draw and resize image
        ctx.drawImage(img, 0, 0, width, height);

        // Get image data
        const imageData = ctx.getImageData(0, 0, width, height);
        const data = imageData.data;

        // Convert to grayscale
        for (let i = 0; i < data.length; i += 4) {
          const r = data[i];
          const g = data[i + 1];
          const b = data[i + 2];
          // Convert to grayscale using standard formula
          const gray = Math.round(0.299 * r + 0.587 * g + 0.114 * b);
          // Set all RGB channels to grayscale value
          data[i] = data[i + 1] = data[i + 2] = gray;
        }

        // Apply Floyd-Steinberg dithering
        floydSteinbergDithering(data, width, height);

        // Convert to 1-bit bitmap (8 pixels per byte)
        const bytesPerRow = Math.ceil(width / 8);
        const bitmapData = new Uint8Array(bytesPerRow * height);
        
        for (let y = 0; y < height; y++) {
          for (let x = 0; x < width; x++) {
            const idx = (y * width + x) * 4;
            const pixelValue = data[idx]; // Get grayscale value from R channel
            const bit = pixelValue < 128 ? 1 : 0; // Inverted for thermal printer (black = 1)
            const byteIdx = Math.floor(x / 8);
            const bitPos = 7 - (x % 8);
            bitmapData[y * bytesPerRow + byteIdx] |= (bit << bitPos);
          }
        }

        // Build ESC/POS command
        const commands = [];
        
        // Initialize printer
        commands.push(0x1B, 0x40); // ESC @ (Initialize)
        
        // Set center alignment
        commands.push(0x1B, 0x61, 0x01); // ESC a 1 (Center align)
        
        // Set image mode
        commands.push(0x1D, 0x76, 0x30, 0x00); // GS v 0 (Print raster image)
        
        // Image width (LSB, MSB)
        const widthLow = bytesPerRow & 0xFF;
        const widthHigh = (bytesPerRow >> 8) & 0xFF;
        commands.push(widthLow, widthHigh);
        
        // Image height (LSB, MSB)
        const heightLow = height & 0xFF;
        const heightHigh = (height >> 8) & 0xFF;
        commands.push(heightLow, heightHigh);
        
        // Append bitmap data
        commands.push(...Array.from(bitmapData));
        
        // Feed paper and cut (if supported)
        commands.push(0x1D, 0x56, 0x00); // GS V 0 (Cut paper)
        commands.push(0x0A); // Line feed
        
        // Reset alignment to left (optional, for next print)
        commands.push(0x1B, 0x61, 0x00); // ESC a 0 (Left align)

        resolve(new Uint8Array(commands));
      } catch (error) {
        reject(error);
      }
    };

    img.onerror = () => {
      reject(new Error('Failed to load image'));
    };

    img.src = imageDataUrl;
  });
}

/**
 * Send ESC/POS data to printer (USB or BLE)
 * @param {Uint8Array} data
 */
async function sendToPrinter(data) {
  if (PRINTER_STATE.type === 'usb') {
    await PRINTER_STATE.device.transferOut(1, data);
    return;
  }
  if (PRINTER_STATE.type === 'bluetooth' && PRINTER_STATE.characteristic) {
    const char = PRINTER_STATE.characteristic;
    const useNoResponse = char.properties.writeWithoutResponse;

    const writeChunk = async (chunk) => {
      try {
        if (useNoResponse && chunk.byteLength <= 512) {
          await char.writeValueWithoutResponse(chunk);
        } else {
          await char.writeValue(chunk);
        }
      } catch (e) {
        if (useNoResponse && e.name !== 'NetworkError') {
          await char.writeValue(chunk);
        } else {
          throw e;
        }
      }
    };

    for (let i = 0; i < data.length; i += BLE_CHUNK_SIZE) {
      const chunk = data.slice(i, i + BLE_CHUNK_SIZE);
      await writeChunk(chunk);
      if (BLE_CHUNK_DELAY_MS > 0 && i + BLE_CHUNK_SIZE < data.length) {
        await new Promise((r) => setTimeout(r, BLE_CHUNK_DELAY_MS));
      }
    }
    return;
  }
  throw new Error('Printer tidak terhubung atau tidak mendukung pengiriman data.');
}

/**
 * Print photostrip to thermal printer
 * @param {string} imageDataUrl - Data URL of the photostrip image
 * @param {number} quantity - Number of copies to print
 * @returns {Promise<void>} Promise that resolves when print is complete
 */
export async function printPhotostrip(imageDataUrl, quantity = 1) {
  if (!PRINTER_STATE.device) {
    throw new Error('Printer belum terhubung. Silakan hubungkan printer terlebih dahulu.');
  }

  if (PRINTER_STATE.type === 'bluetooth' && PRINTER_STATE.server && !PRINTER_STATE.server.connected) {
    throw new Error('Koneksi printer terputus. Silakan hubungkan ulang printer di Pengaturan.');
  }

  const escposData = await convertToESCPos(imageDataUrl);

  for (let i = 0; i < quantity; i++) {
    await sendToPrinter(escposData);
    if (i < quantity - 1) {
      await new Promise((r) => setTimeout(r, 500));
    }
  }

  console.log(`[Printer] Printed ${quantity} copy/copies`);
}

/**
 * Print photostrip from File/Blob
 * @param {File|Blob} photoFile - File or Blob object of the photostrip
 * @param {number} quantity - Number of copies to print
 */
export async function printPhotostripFromFile(photoFile, quantity = 1) {
  // Convert File/Blob to data URL
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = async (e) => {
      try {
        const dataUrl = e.target?.result;
        await printPhotostrip(dataUrl, quantity);
        resolve();
      } catch (error) {
        reject(error);
      }
    };
    reader.onerror = () => reject(new Error('Failed to read file'));
    reader.readAsDataURL(photoFile);
  });
}
