/**
 * Image merge: photo + frame template.
 * Draws photo into frame, returns merged data URL.
 */

export function mergePhotoWithFrame(photoDataUrl, frameImageUrl, options = {}) {
  const { photoArea } = options;
  const defaultArea = {
    x: 0.1,
    y: 0.15,
    width: 0.8,
    height: 0.7,
  };
  const area = photoArea || defaultArea;

  return new Promise((resolve, reject) => {
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    if (!ctx) {
      reject(new Error('No canvas context'));
      return;
    }

    const templateImg = new Image();
    templateImg.crossOrigin = 'anonymous';

    templateImg.onload = () => {
      canvas.width = templateImg.width;
      canvas.height = templateImg.height;

      const x = Math.round(area.x * canvas.width);
      const y = Math.round(area.y * canvas.height);
      const w = Math.round(area.width * canvas.width);
      const h = Math.round(area.height * canvas.height);

      const photoImg = new Image();
      photoImg.crossOrigin = 'anonymous';

      photoImg.onload = () => {
        const photoAspect = photoImg.width / photoImg.height;
        const areaAspect = w / h;
        let drawW = w,
          drawH = h;
        if (photoAspect > areaAspect) {
          drawH = w / photoAspect;
        } else {
          drawW = h * photoAspect;
        }
        const drawX = x + (w - drawW) / 2;
        const drawY = y + (h - drawH) / 2;

        ctx.drawImage(photoImg, drawX, drawY, drawW, drawH);
        ctx.drawImage(templateImg, 0, 0);

        resolve(canvas.toDataURL('image/png'));
      };

      photoImg.onerror = () => reject(new Error('Failed to load photo'));
      photoImg.src = photoDataUrl;
    };

    templateImg.onerror = () => reject(new Error('Failed to load frame'));
    templateImg.src = frameImageUrl;
  });
}
