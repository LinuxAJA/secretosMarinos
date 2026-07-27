/**
 * ============================================================================
 * main.js — JavaScript Vanilla
 * ============================================================================
 * Paso 1: menú móvil accesible
 * Paso 2: validación básica de formularios de auth (UX; el backend valida igual)
 * ============================================================================
 */

document.addEventListener('DOMContentLoaded', () => {
  initMobileNav();
  initAuthForms();
  initFocusForm();
  initCampaignCancelReason();
});

/**
 * Si hay errores de validación en un formulario del panel,
 * hace scroll hasta la tarjeta marcada con data-focus-form.
 */
function initFocusForm() {
  const target = document.querySelector('[data-focus-form]');
  if (target) {
    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
}

/**
 * Muestra u oculta el motivo de cancelación según el estado de la campaña.
 * La validación definitiva la hace el backend (CampaignService).
 */
function initCampaignCancelReason() {
  const form = document.querySelector('[data-campaign-form]');
  if (!form) {
    return;
  }

  const estado = form.querySelector('[data-campaign-estado]');
  const reasonBox = form.querySelector('[data-cancel-reason]');
  const reasonField = form.querySelector('#motivo_cancelacion');
  if (!estado || !reasonBox) {
    return;
  }

  const sync = () => {
    const cancelled = estado.value === 'cancelada';
    reasonBox.hidden = !cancelled;
    if (reasonField) {
      reasonField.required = cancelled;
    }
  };

  estado.addEventListener('change', sync);
  sync();
}

/**
 * Menú hamburguesa + Escape
 */
function initMobileNav() {
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
}

/**
 * Validación en cliente de login/registro.
 * No reemplaza la validación PHP: solo mejora la experiencia.
 */
function initAuthForms() {
  const forms = document.querySelectorAll('[data-auth-form]');

  forms.forEach((form) => {
    form.addEventListener('submit', (event) => {
      clearClientErrors(form);

      const password = form.querySelector('#password');
      const confirm = form.querySelector('#password_confirm');
      let valid = true;

      form.querySelectorAll('[required]').forEach((field) => {
        if (!String(field.value || '').trim()) {
          showClientError(field, 'Este campo es obligatorio.');
          valid = false;
        }
      });

      if (password && password.value.length > 0 && password.value.length < 8) {
        showClientError(password, 'La contraseña debe tener al menos 8 caracteres.');
        valid = false;
      }

      if (confirm && password && password.value !== confirm.value) {
        showClientError(confirm, 'Las contraseñas no coinciden.');
        valid = false;
      }

      if (!valid) {
        event.preventDefault();
      }
    });
  });
}

function showClientError(field, message) {
  field.setAttribute('aria-invalid', 'true');
  const error = document.createElement('p');
  error.className = 'form-error';
  error.dataset.clientError = '1';
  error.textContent = message;
  field.insertAdjacentElement('afterend', error);
}

function clearClientErrors(form) {
  form.querySelectorAll('[data-client-error]').forEach((el) => el.remove());
  form.querySelectorAll('[aria-invalid]').forEach((el) => {
    el.setAttribute('aria-invalid', 'false');
  });
}
