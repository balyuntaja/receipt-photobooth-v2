/**
 * Result page (QR destination).
 * Shows photos for download.
 */

document.addEventListener('DOMContentLoaded', () => {
  const media = JSON.parse(document.body.dataset.media || '[]');
  const loading = document.getElementById('result-loading');
  const empty = document.getElementById('result-empty');
  const grid = document.getElementById('result-grid');
  const btnDownloadAll = document.getElementById('btn-download-all');

  loading?.classList.add('hidden');

  if (!media || media.length === 0) {
    empty?.classList.remove('hidden');
    btnDownloadAll.disabled = true;
    return;
  }

  empty?.classList.add('hidden');
  grid?.classList.remove('hidden');

  const files = media.filter((m) => m.url).map((m) => ({ url: m.url, type: m.type }));

  files.forEach((file, i) => {
    const div = document.createElement('div');
    div.className = 'relative rounded-lg overflow-hidden bg-gray-100 border border-gray-200 aspect-square';
    const img = document.createElement('img');
    img.src = file.url;
    img.alt = file.type === 'strip' ? 'Photostrip' : `Photo ${i + 1}`;
    img.className = 'w-full h-full object-cover';
    div.appendChild(img);
    const label = document.createElement('span');
    label.className = 'absolute left-2 top-2 px-2 py-1 rounded bg-black/70 text-white text-xs font-semibold';
    label.textContent = file.type === 'strip' ? 'Photostrip' : `Photo ${i + 1}`;
    div.appendChild(label);
    const dlBtn = document.createElement('button');
    dlBtn.type = 'button';
    dlBtn.className = 'absolute right-2 top-2 p-2 rounded-full bg-black/60 text-white hover:bg-black/80';
    dlBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>';
    dlBtn.onclick = () => downloadImage(file.url);
    div.appendChild(dlBtn);
    grid?.appendChild(div);
  });

  btnDownloadAll?.addEventListener('click', () => {
    files.forEach((f) => downloadImage(f.url));
  });

  async function downloadImage(url) {
    try {
      const res = await fetch(url, { mode: 'cors' });
      if (!res.ok) throw new Error(res.statusText);
      const blob = await res.blob();
      const ext = blob.type.includes('png') ? 'png' : 'jpg';
      const a = document.createElement('a');
      a.href = URL.createObjectURL(blob);
      a.download = `photobooth-${Date.now()}.${ext}`;
      a.click();
      setTimeout(() => URL.revokeObjectURL(a.href), 500);
    } catch (err) {
      console.error('Download error:', err);
      window.open(url, '_blank');
    }
  }
});
