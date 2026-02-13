/**
 * Frame selection logic.
 */

export function initFrames(stateMachine, session) {
  const frames = JSON.parse(document.body.dataset.frames || '[]');
  let selectedFrameId = null;

  const btnBack = document.getElementById('btn-frame-back');
  const btnNext = document.getElementById('btn-frame-next');
  const grid = document.getElementById('frame-grid');

  if (!grid) return;

  function selectFrame(frameId) {
    selectedFrameId = frameId;
    grid.querySelectorAll('.frame-card').forEach((btn) => {
      const id = String(btn.dataset.frameId);
      if (id === String(frameId)) {
        btn.classList.add('border-gray-900', 'bg-gray-200');
        btn.classList.remove('border-transparent');
      } else {
        btn.classList.remove('border-gray-900', 'bg-gray-200');
        btn.classList.add('border-transparent');
      }
    });
    btnNext.disabled = !selectedFrameId;
    btnNext.classList.toggle('cursor-not-allowed', !selectedFrameId);
    btnNext.classList.toggle('opacity-50', !selectedFrameId);
    if (selectedFrameId) {
      btnNext.classList.remove('bg-gray-200', 'text-gray-500');
      btnNext.classList.add('bg-gray-900', 'text-white', 'cursor-pointer');
    } else {
      btnNext.classList.add('bg-gray-200', 'text-gray-500');
      btnNext.classList.remove('bg-gray-900', 'text-white', 'cursor-pointer');
    }
  }

  grid.querySelectorAll('.frame-card').forEach((btn) => {
    btn.addEventListener('click', () => selectFrame(btn.dataset.frameId));
  });

  btnBack?.addEventListener('click', () => stateMachine.setState(stateMachine.STATES.IDLE));
  btnNext?.addEventListener('click', () => {
    if (!selectedFrameId) return;
    session.saveFrame(selectedFrameId).then(() => {
      stateMachine.setState(stateMachine.STATES.CAPTURE);
    });
  });

  return {
    getSelectedFrame: () => selectedFrameId,
    getFrames: () => frames,
  };
}
