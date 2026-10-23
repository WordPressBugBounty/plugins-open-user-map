// Enhance the existing settings submit button without changing form submission.
(function() {
  function initSaveBar() {
    const bar = document.querySelector('.oum-settings-save-bar');
    const form = bar && bar.closest('form');
    const status = bar && bar.querySelector('.oum-settings-save-status');
    if (!form || !status) return;

    const root = document.documentElement;
    const area = bar.parentElement;
    root.classList.add('oum-settings-save-bar-page');
    bar.classList.add('is-pinned');

    function updateLayout() {
      // Stay inside the visible settings content, even when wide fields scroll
      // horizontally or the WordPress sidebar changes the content width.
      const bounds = area.getBoundingClientRect();
      const left = Math.max(0, bounds.left);
      const right = Math.min(document.documentElement.clientWidth, bounds.right);
      bar.style.left = left + 'px';
      bar.style.width = Math.max(0, right - left) + 'px';
      const height = bar.getBoundingClientRect().height;
      area.style.height = height + 'px';
      root.style.setProperty('--oum-settings-save-bar-height', (height + 12) + 'px');
    }
    updateLayout();
    let layoutFrame = null;
    function scheduleLayout() {
      if (layoutFrame !== null) return;
      layoutFrame = window.requestAnimationFrame(function() {
        layoutFrame = null;
        updateLayout();
      });
    }
    if (window.ResizeObserver) {
      const observer = new ResizeObserver(scheduleLayout);
      observer.observe(bar);
      observer.observe(area);
    }
    window.addEventListener('resize', scheduleLayout);
    window.addEventListener('scroll', scheduleLayout, {passive: true});

    function readSettings() {
      // Use native submission semantics so new, nested, and dynamic settings
      // participate automatically. Preserve order for repeatable field groups.
      const values = [];
      new FormData(form).forEach(function(value, name) {
        // Changing tabs only changes the post-save destination, not a setting.
        if (name === 'oum_active_tab') return;
        // Leaflet recalculates the center on tab changes. Ignore floating-point
        // rounding noise in the comparison; the actual submitted values stay intact.
        if (typeof value === 'string' && (name === 'oum_start_lat' || name === 'oum_start_lng') && value.trim() !== '' && Number.isFinite(Number(value))) {
          value = Number(value).toFixed(7);
        }
        values.push([name, typeof value === 'string' ? value : [value.name, value.size, value.type, value.name ? value.lastModified : 0]]);
      });
      return JSON.stringify(values);
    }

    const initialSettings = readSettings();
    let wasDirty = false;
    function updateStatus() {
      if (document.hidden) return;
      const isDirty = readSettings() !== initialSettings;
      if (isDirty === wasDirty) return;
      wasDirty = isDirty;
      status.textContent = isDirty ? status.dataset.unsavedLabel : '';
      // Also reserve the new height when the text wraps without ResizeObserver.
      scheduleLayout();
    }

    form.addEventListener('input', updateStatus);
    form.addEventListener('change', updateStatus);
    form.addEventListener('reset', function() { window.setTimeout(updateStatus, 0); });
    document.addEventListener('visibilitychange', updateStatus);

    // Maps, media pickers, and jQuery controls also set values without native events.
    // A lightweight comparison catches these and added/removed/reordered fields.
    window.setInterval(updateStatus, 1000);
  }

  // Capture the baseline after the existing load handlers normalize their controls.
  function afterLoad() { window.setTimeout(initSaveBar, 0); }
  if (document.readyState === 'complete') {
    afterLoad();
  } else {
    window.addEventListener('load', afterLoad, {once: true});
  }
})();
