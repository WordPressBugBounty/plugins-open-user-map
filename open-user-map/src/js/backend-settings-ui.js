// Settings presentation only. Never change field values, names, or disabled states.
(function () {
  'use strict';

  window.addEventListener('load', function () {
    const content = document.querySelector('.oum-tab-content');
    if (!content) return;

    const regions = content.querySelector('#oum_enable_regions');
    const regionLayout = content.querySelector('[data-oum-region-layout]');
    function updateRegions() {
      if (regions && regionLayout) regionLayout.hidden = !regions.checked;
    }
    if (regions) regions.addEventListener('change', updateRegions);
    updateRegions();

    // Respect sections hidden by existing feature switches, without hiding links
    // merely because their tab is currently inactive.
    function updateQuickLinks() {
      content.querySelectorAll('.community-quick-links').forEach(function (nav) {
        const links = Array.from(nav.querySelectorAll('a[href^="#"]'));
        links.forEach(function (link) {
          let section = document.getElementById(link.hash.slice(1));
          let hidden = !section;
          while (section && !section.classList.contains('oum-tab-pane')) {
            hidden = hidden || section.hidden || section.style.display === 'none';
            section = section.parentElement;
          }
          link.hidden = hidden;
        });
        nav.hidden = links.every(link => link.hidden);
      });
    }
    updateQuickLinks();
    content.addEventListener('change', function () {
      // Existing jQuery handlers toggle dependent sections during this event.
      window.setTimeout(updateQuickLinks, 0);
    });
    content.addEventListener('click', function (event) {
      const link = event.target.closest('.community-quick-links a[href^="#"]');
      if (!link || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
      const target = document.getElementById(link.hash.slice(1));
      if (!target) return;
      event.preventDefault();
      target.setAttribute('tabindex', '-1');
      target.focus({preventScroll: true});
      target.scrollIntoView({
        behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
        block: 'start'
      });
    });

    // Legacy rows often show their label in a table heading or a nearby strong
    // element. Reuse that translated text for controls lacking a native label.
    // Explicit labels/ARIA always win, including those on newly added settings.
    function labelText(element) {
      if (!element) return '';
      const copy = element.cloneNode(true);
      copy.querySelectorAll('input, select, textarea, button, .oum-tooltip, .oum-pro, .oum-pro-feature-actions, .description').forEach(node => node.remove());
      return copy.textContent.replace(/\s+/g, ' ').trim();
    }
    function labelControls(root) {
      root.querySelectorAll('input:not([type="hidden"]):not([type="submit"]):not([type="button"]), select, textarea').forEach(function (control) {
        if (control.hasAttribute('aria-label') || control.hasAttribute('aria-labelledby')) return;
        if (Array.from(control.labels || []).some(label => labelText(label))) return;
        const cell = control.closest('td');
        if (!cell) return;
        const customFields = control.closest('.oum_custom_fields_wrapper');
        let label;
        if (customFields) {
          label = labelText(customFields.querySelectorAll('thead th')[cell.cellIndex]);
        } else {
          const next = control.nextElementSibling;
          if (next && next.tagName === 'LABEL') label = labelText(next);
          let previous = control.previousElementSibling;
          while (!label && previous && previous.tagName === 'BR') previous = previous.previousElementSibling;
          if (!label && previous && /^(STRONG|LABEL)$/.test(previous.tagName)) label = labelText(previous);
          if (!label) label = labelText(cell.closest('tr').querySelector(':scope > th'));
        }
        if (label) control.setAttribute('aria-label', label);
      });
      root.querySelectorAll('.oum_custom_fields_wrapper .actions button').forEach(function (button) {
        const wrapper = button.closest('.oum_custom_fields_wrapper');
        const key = button.classList.contains('up') ? 'moveUpLabel' : button.classList.contains('down') ? 'moveDownLabel' : 'removeLabel';
        button.setAttribute('aria-label', wrapper.dataset[key]);
        button.title = wrapper.dataset[key];
      });
    }
    labelControls(content);
    // Custom fields and advanced-filter sections can be added after page load.
    // Observe insertions only: labels never depend on or mutate form values.
    new MutationObserver(function (records) {
      const controlsAdded = records.some(record => Array.from(record.addedNodes).some(node =>
        node.nodeType === 1 && (node.matches('input, select, textarea, .actions button') || node.querySelector('input, select, textarea, .actions button'))
      ));
      if (controlsAdded) labelControls(content);
    }).observe(content, {childList: true, subtree: true});
  }, {once: true});
}());
