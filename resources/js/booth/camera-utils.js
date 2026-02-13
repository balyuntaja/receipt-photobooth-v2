/**
 * Camera utilities – Android-first design.
 * Plain JS port from booth example lib/camera.ts
 */

export function isAndroid() {
  return /Android/i.test(navigator.userAgent);
}

/**
 * Enumerate cameras (DESKTOP ONLY – for camera selection UI).
 * On Android, enumeration is unreliable.
 */
export async function enumerateCameras() {
  if (isAndroid()) {
    return [];
  }
  try {
    const stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
    stream.getTracks().forEach((t) => t.stop());
    await new Promise((r) => setTimeout(r, 100));
    const devices = await navigator.mediaDevices.enumerateDevices();
    return devices.filter(
      (d) => d.kind === 'videoinput' && d.deviceId && d.deviceId !== ''
    );
  } catch (err) {
    console.error('[camera-utils] enumerateCameras failed:', err);
    return [];
  }
}
