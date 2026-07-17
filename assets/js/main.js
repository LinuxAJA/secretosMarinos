/**
 * ============================================================================
 * main.js — JavaScript Vanilla base
 * ============================================================================
 * Paso 1: solo menú móvil accesible.
 * Más adelante: validación de formularios, Fetch API, etc.
 * ============================================================================
 */

document.addEventListener('DOMContentLoaded', () => {
  const toggle = document.querySelector('[data-nav-toggle]');
  const nav = document.querySelector('[data-nav]');

  if (!toggle || !nav) {
    return;
  }

  /**
   * Abre/cierra el menú y actualiza aria-expanded
   * para lectores de pantalla.
   */
  toggle.addEventListener('click', () => {
    const isOpen = nav.classList.toggle('is-open');
    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
  });

  // Cierra el menú al pulsar Escape
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && nav.classList.contains('is-open')) {
      nav.classList.remove('is-open');
      toggle.setAttribute('aria-expanded', 'false');
      toggle.focus();
    }
  });
});
