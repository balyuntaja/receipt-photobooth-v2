/**
 * Kiosk state machine.
 * States: IDLE | FRAME | CAPTURE | PREVIEW | PRINT | DONE | RESET
 */

const STATES = {
  IDLE: 'IDLE',
  FRAME: 'FRAME',
  CAPTURE: 'CAPTURE',
  PREVIEW: 'PREVIEW',
  PRINT: 'PRINT',
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
    document.querySelectorAll('.booth-screen').forEach((el) => {
      const screenState = el.dataset.state;
      if (screenState === state) {
        el.classList.remove('hidden');
        el.style.display = '';
      } else {
        el.classList.add('hidden');
        el.style.display = 'none';
      }
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
