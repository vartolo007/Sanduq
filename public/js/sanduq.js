/* Sanduq — the only JavaScript the UI needs. No framework. */
(function () {
  var root = document.documentElement;

  /* theme: remembered locally, applied before paint via the inline script in the layout */
  window.sqToggleTheme = function () {
    var next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    root.setAttribute('data-theme', next);
    try { localStorage.setItem('sq-theme', next); } catch (e) {}
    document.querySelectorAll('[data-theme-target]').forEach(function (el) {
      el.setAttribute('data-theme', next);
    });
  };

  /* sheets and dialogs */
  function open(el) { if (!el) return; el.hidden = false; document.body.style.overflow = 'hidden';
    var f = el.querySelector('input,select,textarea,button'); if (f) f.focus({ preventScroll: true }); }
  function close(el) { if (!el) return; el.hidden = true; document.body.style.overflow = ''; }

  window.sqOpen = function (id) { open(document.getElementById(id)); };
  window.sqClose = function (id) { close(document.getElementById(id)); };

  document.addEventListener('click', function (e) {
    var o = e.target.closest('[data-sq-open]');
    if (o) { e.preventDefault(); open(document.getElementById(o.getAttribute('data-sq-open'))); return; }
    var c = e.target.closest('[data-sq-close]');
    if (c) { e.preventDefault(); close(c.closest('.sq-sheet, .dialog-backdrop')); return; }
    if (e.target.classList && (e.target.classList.contains('sq-sheet') || e.target.classList.contains('dialog-backdrop'))) close(e.target);
  });
  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') return;
    document.querySelectorAll('.sq-sheet:not([hidden]), .dialog-backdrop:not([hidden])').forEach(close);
  });

  /* delete confirmation: fills the shared dialog, then posts the matching form */
  window.sqConfirmDelete = function (formId, label) {
    var dlg = document.getElementById('sq-confirm');
    if (!dlg) return;
    var target = dlg.querySelector('[data-confirm-target]');
    if (label) { var l = dlg.querySelector('[data-confirm-label]'); if (l) l.textContent = label; }
    target.onclick = function () { document.getElementById(formId).submit(); };
    open(dlg);
  };

  /* toast auto-dismiss */
  document.querySelectorAll('.sq-toast-wrap').forEach(function (t) {
    setTimeout(function () { t.remove(); }, 3200);
  });

  /* transaction form: category list follows the selected type */
  window.sqSyncCategories = function (type) {
    document.querySelectorAll('.sq-type-opt').forEach(function (b) {
      var on = b.value === type;
      b.classList.toggle('is-on-in', on && type === 'income');
      b.classList.toggle('is-on-out', on && type === 'expense');
      b.setAttribute('aria-pressed', on ? 'true' : 'false');
    });
    var input = document.getElementById('sq-type-value');
    if (input) input.value = type;
    document.querySelectorAll('#sq-category option[data-type]').forEach(function (o) {
      o.hidden = o.getAttribute('data-type') !== type;
      if (o.hidden && o.selected) { o.selected = false; }
    });
  };
})();
