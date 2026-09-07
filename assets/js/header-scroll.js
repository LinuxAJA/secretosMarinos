/**
 * ============================================================================
 * header-scroll.js — Toggle .scrolled en el header fijo
 * ============================================================================
 * - Umbral ~40px de scroll vertical.
 * - Si el header tiene data-force-scrolled="1", permanece en glass
 *   (páginas sin hero fotográfico).
 * ============================================================================
 */

(function initHeaderScroll() {
  const header = document.querySelector("[data-site-header]");
  if (!header) {
    return;
  }

  // Páginas sin hero: ya nace scrolled; no hace falta listener
  if (header.getAttribute("data-force-scrolled") === "1") {
    header.classList.add("scrolled");
    return;
  }

  const THRESHOLD = 40;

  const sync = () => {
    if (window.scrollY > THRESHOLD) {
      header.classList.add("scrolled");
    } else {
      header.classList.remove("scrolled");
    }
  };

  sync();
  window.addEventListener("scroll", sync, { passive: true });
})();
