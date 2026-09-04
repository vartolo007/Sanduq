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

  /* categories: one sheet serves both adding and editing.
     sqNewCategory resets it to a blank POST; sqEditCategory fills it from the
     card's data-* attributes and switches it to a PUT on that category. */
  function catForm() { return document.getElementById('sq-cat-form'); }

  window.sqNewCategory = function () {
    var sheet = document.getElementById('sq-cat-sheet');
    var form = catForm();
    if (!form) return;
    form.action = form.dataset.storeUrl;
    form.querySelector('#sq-cat-method').value = '';
    form.querySelector('[name="editing_id"]').value = '';
    form.querySelector('[name="name_ar"]').value = '';
    form.querySelector('[name="name_en"]').value = '';
    var inc = form.querySelector('input[name="type"][value="income"]');
    if (inc) inc.checked = true;
    document.getElementById('sq-cat-title').textContent = sheet.dataset.newLabel;
    open(sheet);
  };

  window.sqEditCategory = function (btn) {
    var sheet = document.getElementById('sq-cat-sheet');
    var form = catForm();
    if (!form) return;
    var d = btn.dataset;
    form.action = d.updateUrl;
    form.querySelector('#sq-cat-method').value = 'PUT';
    form.querySelector('[name="editing_id"]').value = d.categoryId;
    form.querySelector('[name="name_ar"]').value = d.nameAr;
    form.querySelector('[name="name_en"]').value = d.nameEn;
    var t = form.querySelector('input[name="type"][value="' + d.type + '"]');
    if (t) t.checked = true;
    document.getElementById('sq-cat-title').textContent = sheet.dataset.editLabel;
    open(sheet);
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

  /* transaction form: the "+ new category" entry in the category list.
     Leaves for the categories page and comes back with the new category chosen.
     What the user already typed travels in the query string, so the form is
     never wiped just because a category was missing. */
  window.sqCategoryChanged = function (select) {
    if (select.value !== '__new__') return;

    var form = select.form;
    var typed = new URLSearchParams();

    ['type', 'amount', 'date', 'note'].forEach(function (name) {
      var field = form.querySelector('[name="' + name + '"]');
      if (field && field.value) { typed.set(name, field.value); }
    });

    var back = window.location.pathname + (typed.toString() ? '?' + typed.toString() : '');

    // نمرّر نوع الحركة أيضًا لتفتح نافذة التصنيف على النوع نفسه، فلا يضطر
    // المستخدم إلى تبديله يدويًا وهو قادم من حركة مصروف.
    window.location.href = select.dataset.newUrl
      + '?return=' + encodeURIComponent(back)
      + '&type=' + encodeURIComponent(typed.get('type') || '');
  };
})();
