/**
 * Kiosk state machine.
 * States: IDLE | REVIEW_ORDER | PAYMENT | FRAME | CAPTURE | PREVIEW | PRINT | RESULT | RESET
 */

const STATES = {
  IDLE: 'IDLE',
  REVIEW_ORDER: 'REVIEW_ORDER',
  PROMO_CODE: 'PROMO_CODE',
  PAYMENT: 'PAYMENT',
  FRAME: 'FRAME',
  CAPTURE: 'CAPTURE',
  PREVIEW: 'PREVIEW',
  PRINT: 'PRINT',
  RESULT: 'RESULT',
  DONE: 'DONE',
  RESET: 'RESET',
};

export function createStateMachine(handlers = {}, initial = 'IDLE') {
  const allowedInitial = STATES[initial] ? initial : 'IDLE';
  let state = allowedInitial;
  const listeners = new Set();

  function getState() {
    return state;
  }

  function setState(newState) {
    // Dari Welcome (IDLE) tidak boleh loncat langsung ke FRAME/PAYMENT — harus lewat REVIEW_ORDER.
    // (Halaman continue setelah bayar diload dengan initialState=FRAME sehingga state sudah FRAME.)
    if (state === STATES.IDLE && newState === STATES.FRAME) {
      newState = STATES.REVIEW_ORDER;
    }
    if (state === STATES.IDLE && newState === STATES.PAYMENT) {
      newState = STATES.REVIEW_ORDER;
    }
    if (state === newState) return;
    const prev = state;
    state = newState;
    console.log('[Kiosk] State:', prev, '->', newState);
    listeners.forEach((fn) => fn(newState, prev));
    if (handlers[newState]) {
      handlers[newState](newState, prev);
    }
  }

  function subscribe(fn) {
    listeners.add(fn);
    return () => listeners.delete(fn);
  }

  function render() {
    const screens = document.querySelectorAll('.booth-screen');
    screens.forEach((el) => {
      const screenState = el.getAttribute('data-state');
      const isActive = screenState === state;
      el.classList.toggle('hidden', !isActive);
      el.style.display = isActive ? 'flex' : 'none';
    });
  }

  subscribe(render);

  if (state !== STATES.IDLE) {
    render();
    if (handlers[state]) handlers[state](state, STATES.IDLE);
  }

  return {
    getState,
    setState,
    subscribe,
    STATES,
  };
}
