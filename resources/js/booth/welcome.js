/**
 * Welcome Screen JS
 * =================
 * Handles the welcome screen rendering and interactions.
 * Components are rendered from server-side data passed via data attributes.
 */

/**
 * Initialize the welcome screen.
 * Called from kiosk.js on DOMContentLoaded.
 */
export function initWelcomeScreen() {
  const welcomeScreen = document.getElementById('screen-welcome');
  if (!welcomeScreen) {
    console.warn('[welcome.js] #screen-welcome not found');
    return;
  }

  // Components are rendered server-side via Blade
  // This JS handles any client-side interactivity

  // Lazy load images for performance
  lazyLoadImages(welcomeScreen);

  // Add click animation to start button
  const startBtn = welcomeScreen.querySelector('.welcome-start-btn');
  if (startBtn) {
    startBtn.addEventListener('click', handleStartClick);
  }
}

/**
 * Lazy load images in welcome screen.
 * @param {HTMLElement} container
 */
function lazyLoadImages(container) {
  const images = container.querySelectorAll('img[data-src]');
  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          const img = entry.target;
          img.src = img.dataset.src;
          img.removeAttribute('data-src');
          observer.unobserve(img);
        }
      });
    });
    images.forEach((img) => observer.observe(img));
  } else {
    // Fallback: load all immediately
    images.forEach((img) => {
      img.src = img.dataset.src;
      img.removeAttribute('data-src');
    });
  }
}

/**
 * Handle start button click with visual feedback.
 * @param {Event} event
 */
function handleStartClick(event) {
  const btn = event.currentTarget;
  btn.classList.add('animate-pulse');
  setTimeout(() => {
    btn.classList.remove('animate-pulse');
  }, 300);
}

/**
 * Get the background image URL from welcome screen data.
 * @returns {string|null}
 */
export function getWelcomeBackground() {
  const bgEl = document.querySelector('.welcome-background');
  return bgEl?.src || null;
}

/**
 * Check if welcome screen has custom components.
 * @returns {boolean}
 */
export function hasWelcomeComponents() {
  const welcomeScreen = document.getElementById('screen-welcome');
  if (!welcomeScreen) return false;
  
  const components = welcomeScreen.querySelectorAll('.welcome-component');
  return components.length > 0;
}
