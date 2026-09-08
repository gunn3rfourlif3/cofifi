/**
 * Cofifi theme scripts.
 *
 * Small, dependency-free, progressive: everything still works with JS off,
 * these just improve it.
 */
(function () {
	'use strict';

	/* ----------------------------------------------------------------------
	 * Mobile navigation drawer
	 * -------------------------------------------------------------------- */
	var drawer = document.getElementById('nav-drawer');
	var openers = document.querySelectorAll('.nav-toggle:not(.nav-toggle--close)');
	var closers = document.querySelectorAll('.nav-toggle--close');
	var lastFocused = null;

	function openDrawer() {
		if (!drawer) { return; }
		lastFocused = document.activeElement;
		drawer.hidden = false;
		// Next frame, so the transition runs.
		requestAnimationFrame(function () {
			drawer.classList.add('is-open');
		});
		document.body.classList.add('nav-open');
		openers.forEach(function (btn) { btn.setAttribute('aria-expanded', 'true'); });
		var first = drawer.querySelector('a, button');
		if (first) { first.focus(); }
	}

	function closeDrawer() {
		if (!drawer) { return; }
		drawer.classList.remove('is-open');
		document.body.classList.remove('nav-open');
		openers.forEach(function (btn) { btn.setAttribute('aria-expanded', 'false'); });
		window.setTimeout(function () { drawer.hidden = true; }, 400);
		if (lastFocused) { lastFocused.focus(); }
	}

	openers.forEach(function (btn) { btn.addEventListener('click', openDrawer); });
	closers.forEach(function (btn) { btn.addEventListener('click', closeDrawer); });

	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape' && drawer && drawer.classList.contains('is-open')) {
			closeDrawer();
		}
	});

	/* ----------------------------------------------------------------------
	 * Accordions (product page detail rows)
	 * -------------------------------------------------------------------- */
	document.querySelectorAll('.accordion__btn').forEach(function (btn) {
		var item = btn.closest('.accordion__item');
		var panel = item ? item.querySelector('.accordion__panel') : null;
		if (!item || !panel) { return; }

		if (!panel.id) {
			panel.id = 'acc-' + Math.random().toString(36).slice(2, 9);
		}
		btn.setAttribute('aria-expanded', item.classList.contains('is-open') ? 'true' : 'false');
		btn.setAttribute('aria-controls', panel.id);

		btn.addEventListener('click', function () {
			var open = item.classList.toggle('is-open');
			btn.setAttribute('aria-expanded', open ? 'true' : 'false');
		});
	});

	/* ----------------------------------------------------------------------
	 * Product gallery thumbnails
	 * -------------------------------------------------------------------- */
	var frame = document.querySelector('.pdp__frame img');
	document.querySelectorAll('.pdp__thumb').forEach(function (thumb) {
		thumb.addEventListener('click', function (e) {
			e.preventDefault();
			var img = thumb.querySelector('img');
			if (!frame || !img) { return; }
			frame.src = thumb.getAttribute('data-full') || img.src;
			frame.srcset = '';
			frame.alt = img.alt || '';
			document.querySelectorAll('.pdp__thumb').forEach(function (t) {
				t.classList.remove('is-active');
			});
			thumb.classList.add('is-active');
		});
	});

	/* ----------------------------------------------------------------------
	 * Quantity stepper
	 * -------------------------------------------------------------------- */
	document.querySelectorAll('.qty-field').forEach(function (field) {
		var input = field.querySelector('input');
		if (!input) { return; }

		field.querySelectorAll('[data-step]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var step = parseInt(btn.getAttribute('data-step'), 10) || 1;
				var min = parseInt(input.getAttribute('min'), 10);
				var max = parseInt(input.getAttribute('max'), 10);
				var next = (parseInt(input.value, 10) || 1) + step;

				if (!isNaN(min) && next < min) { next = min; }
				if (!isNaN(max) && next > max) { next = max; }

				input.value = next;
				input.dispatchEvent(new Event('change', { bubbles: true }));
			});
		});
	});

	/* ----------------------------------------------------------------------
	 * Purchase mode (one-time vs standing order)
	 * -------------------------------------------------------------------- */
	document.querySelectorAll('.purchase-option').forEach(function (option) {
		option.addEventListener('click', function () {
			var group = option.closest('[data-purchase-group]');
			if (!group) { return; }
			group.querySelectorAll('.purchase-option').forEach(function (o) {
				o.classList.remove('is-selected');
			});
			option.classList.add('is-selected');
			var radio = option.querySelector('input[type="radio"]');
			if (radio) { radio.checked = true; }
		});
	});
}());
