/**
 * Session persistence and API calls.
 */

export function createSession() {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || document.body.dataset.csrf;
  const saveFrameUrl = document.body.dataset.saveFrameUrl;
  const updateSessionUrl = document.body.dataset.updateSessionUrl;
  const saveMediaUrl = document.body.dataset.saveMediaUrl;

  function headers() {
    return {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrf,
      Accept: 'application/json',
    };
  }

  async function saveFrame(frameId) {
    if (!saveFrameUrl) return { success: false };
    const res = await fetch(saveFrameUrl, {
      method: 'POST',
      headers: headers(),
      body: JSON.stringify({ frame_id: parseInt(frameId, 10) }),
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(data.message || 'Failed to save frame');
    return data;
  }

  async function updateSession(payload) {
    if (!updateSessionUrl) return { success: false };
    const res = await fetch(updateSessionUrl, {
      method: 'PATCH',
      headers: headers(),
      body: JSON.stringify(payload),
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(data.message || 'Failed to update session');
    return data;
  }

  async function saveMedia(type, dataUrl, index = null) {
    if (!saveMediaUrl) return { success: false };
    const body = { type, data: dataUrl };
    if (index != null) body.index = index;
    const res = await fetch(saveMediaUrl, {
      method: 'POST',
      headers: headers(),
      body: JSON.stringify(body),
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(data.message || 'Failed to save media');
    return data;
  }

  return {
    saveFrame,
    updateSession,
    saveMedia,
  };
}

export function dataUrlToBlob(dataUrl) {
  const arr = dataUrl.split(',');
  const mime = arr[0].match(/:(.*?);/)?.[1] || 'image/png';
  const bstr = atob(arr[1]);
  const u8arr = new Uint8Array(bstr.length);
  for (let i = 0; i < bstr.length; i++) u8arr[i] = bstr.charCodeAt(i);
  return new Blob([u8arr], { type: mime });
}
