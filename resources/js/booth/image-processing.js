/**
 * Image merge: photo + frame template.
 * Draws photo into frame, returns merged data URL.
 */

export function mergePhotoWithFrame(photoDataUrl, frameImageUrl, options = {}) {
  const { photoArea, mirror = false } = options;
  const defaultArea = {
    x: 0.1,
    y: 0.15,
    width: 0.8,
    height: 0.7,
  };

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

      // Konversi photo slot dari canvas coordinates (945x1299) ke template image coordinates
      let x, y, w, h;
      
      if (photoArea && photoArea.canvasWidth && photoArea.canvasHeight) {
        // Photo slot disimpan dalam pixel coordinates relatif terhadap canvas (945x1299)
        // x, y adalah CENTER point (karena di Filament menggunakan transform:translate(-50%, -50%))
        // Konversi ke ratio berdasarkan canvas
        const ratioCenterX = photoArea.x / photoArea.canvasWidth;
        const ratioCenterY = photoArea.y / photoArea.canvasHeight;
        const ratioW = photoArea.width / photoArea.canvasWidth;
        const ratioH = photoArea.height / photoArea.canvasHeight;
        
        // Konversi ke pixel di template image yang sebenarnya
        const centerX = ratioCenterX * canvas.width;
        const centerY = ratioCenterY * canvas.height;
        w = Math.round(ratioW * canvas.width);
        h = Math.round(ratioH * canvas.height);
        
        // Konversi dari center point ke top-left corner untuk canvas drawing
        x = Math.round(centerX - w / 2);
        y = Math.round(centerY - h / 2);
        
        console.log('Photo slot conversion:', {
          canvasSize: { width: photoArea.canvasWidth, height: photoArea.canvasHeight },
          slotCanvasPixels: { centerX: photoArea.x, centerY: photoArea.y, width: photoArea.width, height: photoArea.height },
          slotRatio: { centerX: ratioCenterX, centerY: ratioCenterY, width: ratioW, height: ratioH },
          templateSize: { width: canvas.width, height: canvas.height },
          slotTemplatePixels: { x, y, w, h, centerX: centerX, centerY: centerY }
        });
      } else if (photoArea) {
        // Sudah dalam format ratio (0-1)
        x = Math.round(photoArea.x * canvas.width);
        y = Math.round(photoArea.y * canvas.height);
        w = Math.round(photoArea.width * canvas.width);
        h = Math.round(photoArea.height * canvas.height);
      } else {
        // Gunakan default area
        x = Math.round(defaultArea.x * canvas.width);
        y = Math.round(defaultArea.y * canvas.height);
        w = Math.round(defaultArea.width * canvas.width);
        h = Math.round(defaultArea.height * canvas.height);
      }

      // Validasi slot coordinates
      if (w <= 0 || h <= 0) {
        console.warn('Invalid slot dimensions:', { x, y, w, h });
        reject(new Error('Invalid photo slot dimensions'));
        return;
      }

      const photoImg = new Image();
      photoImg.crossOrigin = 'anonymous';

      photoImg.onload = () => {
        // Draw template first (background layer)
        ctx.drawImage(templateImg, 0, 0);
        
        // Pastikan slot berada dalam bounds template (x, y sudah top-left corner)
        const slotX = Math.max(0, Math.min(x, canvas.width - 1));
        const slotY = Math.max(0, Math.min(y, canvas.height - 1));
        const slotW = Math.max(1, Math.min(w, canvas.width - slotX));
        const slotH = Math.max(1, Math.min(h, canvas.height - slotY));
        
        console.log('Slot bounds:', {
          original: { x, y, w, h },
          clamped: { slotX, slotY, slotW, slotH },
          canvasSize: { width: canvas.width, height: canvas.height }
        });
        
        // Then draw photo into the slot area using cover mode (fill slot completely)
        const photoAspect = photoImg.width / photoImg.height;
        const areaAspect = slotW / slotH;
        let drawW = slotW,
          drawH = slotH;
        
        // Cover mode: foto mengisi slot sepenuhnya, crop jika perlu
        if (photoAspect > areaAspect) {
          // Foto lebih lebar dari slot - fit by height, crop width
          drawH = slotH;
          drawW = slotH * photoAspect;
        } else {
          // Foto lebih tinggi dari slot - fit by width, crop height
          drawW = slotW;
          drawH = slotW / photoAspect;
        }
        
        // Center foto di dalam slot
        const drawX = slotX + (slotW - drawW) / 2;
        const drawY = slotY + (slotH - drawH) / 2;

        // Clip to photo slot area before drawing - foto hanya terlihat di dalam slot
        ctx.save();
        ctx.beginPath();
        ctx.rect(slotX, slotY, slotW, slotH);
        ctx.clip();
        // Mirror: hanya foto dibalik horizontal, template tetap
        if (mirror) {
          ctx.drawImage(photoImg, 0, 0, photoImg.width, photoImg.height, drawX + drawW, drawY, -drawW, drawH);
        } else {
          ctx.drawImage(photoImg, drawX, drawY, drawW, drawH);
        }
        ctx.restore();

        resolve(canvas.toDataURL('image/png'));
      };

      photoImg.onerror = () => reject(new Error('Failed to load photo'));
      photoImg.src = photoDataUrl;
    };

    templateImg.onerror = () => reject(new Error('Failed to load frame'));
    templateImg.src = frameImageUrl;
  });
}
