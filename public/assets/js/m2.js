(function () {
  'use strict';

  /* ---------- Search page ---------- */
  var swap = document.getElementById('swap');
  if (swap) {
    swap.addEventListener('click', function () {
      var f = document.getElementById('from'), t = document.getElementById('to');
      var tmp = f.value; f.value = t.value; t.value = tmp;
    });
  }

  var maxFare = document.getElementById('max_fare');
  var maxOut = document.getElementById('max_fare_out');
  if (maxFare && maxOut) {
    maxFare.addEventListener('input', function () { maxOut.textContent = maxFare.value; });
  }

  // Filters re-run the search straight away (the Apply button still works without JS)
  document.querySelectorAll('[data-autosubmit]').forEach(function (el) {
    el.addEventListener('change', function () { el.form.submit(); });
  });

  /* ---------- Seat selection page ---------- */
  var form = document.getElementById('seatform');
  if (!form) { return; }

  var max = parseInt(form.dataset.max, 10) || 1;
  var boxes = form.querySelectorAll('input[name="seats[]"]');
  var counter = document.getElementById('picked');
  var warn = document.getElementById('warn');
  var cont = document.getElementById('cont');
  var list = document.getElementById('picked_list');

  function update() {
    var checked = form.querySelectorAll('input[name="seats[]"]:checked');
    counter.textContent = checked.length;
    cont.disabled = checked.length !== max;
    var names = [];
    checked.forEach(function (c) { names.push(c.dataset.label); });
    list.textContent = names.length ? names.join(', ') : 'None yet';
  }

  boxes.forEach(function (box) {
    box.addEventListener('change', function () {
      var checked = form.querySelectorAll('input[name="seats[]"]:checked').length;
      if (checked > max) {          // too many: undo and warn
        box.checked = false;
        warn.hidden = false;
      } else {
        warn.hidden = true;
      }
      update();
    });
  });

  // Coach tabs (all coaches are on the page; selections are kept when switching)
  var tabs = form.querySelectorAll('[data-coach-tab]');
  var panels = form.querySelectorAll('[data-coach-panel]');
  tabs.forEach(function (tab) {
    tab.addEventListener('click', function () {
      tabs.forEach(function (t) { t.setAttribute('aria-pressed', t === tab ? 'true' : 'false'); t.classList.toggle('on', t === tab); });
      panels.forEach(function (p) { p.hidden = p.dataset.coachPanel !== tab.dataset.coachTab; });
    });
  });

  update();
})();
