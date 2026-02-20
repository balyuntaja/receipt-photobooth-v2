/**
 * Kiosk state machine.
 * States: IDLE | PAYMENT | FRAME | CAPTURE | PREVIEW | PRINT | RESULT | RESET
 */

const STATES = {
  IDLE: 'IDLE',
  PAYMENT: 'PAYMENT',
  FRAME: 'FRAME',
  CAPTURE: 'CAPTURE',
  PREVIEW: 'PREVIEW',
  PRINT: 'PRINT',
  RESULT: 'RESULT',
  DONE: 'DONE',
  RESET: 'RESET',
};

export function createStateMachine(handlers = {}) {
  let state = STATES.IDLE;
  const listeners = new Set();

  function getState() {
    return state;
  }

  function setState(newState) {
    // Dari Welcome (IDLE) harus lewat PAYMENT dulu, tidak boleh langsung ke FRAME (backward compatibility / cache)
    if (state === STATES.IDLE && newState === STATES.FRAME) {
      newState = STATES.PAYMENT;
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

  return {
    getState,
    setState,
    subscribe,
    STATES,
  };
}
