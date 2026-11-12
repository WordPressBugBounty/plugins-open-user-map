(function () {
  'use strict';

  document.addEventListener('click', function (event) {
    const opener = event.target.closest('.oum-location-report-open');
    if (!opener) return;

    const template = opener.closest('.oum-location-report').querySelector('template');
    const dialog = template.content.firstElementChild.cloneNode(true);
    const fullscreen = document.fullscreenElement;
    const mount = fullscreen && fullscreen.contains(opener) ? fullscreen : document.body;
    mount.appendChild(dialog);

    dialog.querySelector('.oum-location-report-close').addEventListener('click', () => dialog.close());
    dialog.addEventListener('close', function () {
      dialog.remove();
      if (opener.isConnected) opener.focus({preventScroll: true});
    }, {once: true});

    const form = dialog.querySelector('.oum-location-report-form');
    if (form) {
      const reason = form.elements.reason;
      const submit = form.querySelector('[type="submit"]');
      const status = dialog.querySelector('.oum-location-report-status');
      reason.addEventListener('input', () => reason.setCustomValidity(''));
      form.addEventListener('submit', async function (event) {
        event.preventDefault();
        if (submit.disabled) return;

        const length = Array.from(reason.value.trim()).length;
        if (length < 1 || length > Number(reason.dataset.maxLength)) {
          reason.setCustomValidity(form.dataset.invalidReason);
          reason.reportValidity();
          return;
        }

        const body = new FormData(form);
        submit.disabled = true;
        reason.readOnly = true;
        status.textContent = '';
        try {
          const response = await fetch(form.getAttribute('action'), {method: 'POST', credentials: 'same-origin', body});
          const result = await response.json();
          status.textContent = result.data?.message || form.dataset.requestFailed;
          if (result.success === true) {
            form.hidden = true;
            dialog.querySelector('.oum-location-report-close').focus();
          }
        } catch (error) {
          status.textContent = form.dataset.requestFailed;
        } finally {
          submit.disabled = false;
          reason.readOnly = false;
        }
      });
    }

    dialog.showModal();
  });
}());
