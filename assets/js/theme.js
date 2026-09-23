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

/* --------------------------------------------------------------------------
 * Gallery lightbox
 *
 * The tiles carry their own full-size source and caption, so the lightbox
 * needs no data island and works wherever cofifi_gallery_tile() is printed.
 * ----------------------------------------------------------------------- */
(function () {
	var box = document.getElementById('cofifi-lightbox');
	var tiles = Array.prototype.slice.call(document.querySelectorAll('[data-lightbox]'));

	if (!box || !tiles.length) { return; }

	var img = box.querySelector('.lb__img');
	var cap = box.querySelector('.lb__cap');
	var now = box.querySelector('[data-lb-index]');
	var all = box.querySelector('[data-lb-total]');
	var closeBtn = box.querySelector('.lb__btn--close');
	var index = 0;
	var opener = null;

	all.textContent = tiles.length;

	function frame(i) {
		var picture = tiles[i].querySelector('img');
		if (!picture) { return; }

		index = i;
		img.src = picture.getAttribute('data-full') || picture.src;
		img.alt = picture.alt || '';
		cap.textContent = picture.getAttribute('data-caption') || '';
		now.textContent = i + 1;
	}

	function step(by) {
		frame((index + by + tiles.length) % tiles.length);
	}

	function open(i, from) {
		opener = from || null;
		frame(i);
		box.hidden = false;
		document.body.classList.add('lb-open');
		closeBtn.focus();
	}

	function close() {
		box.hidden = true;
		document.body.classList.remove('lb-open');
		img.src = '';
		if (opener) { opener.focus(); }
		opener = null;
	}

	tiles.forEach(function (tile, i) {
		tile.addEventListener('click', function () { open(i, tile); });
	});

	box.querySelectorAll('[data-lb-close]').forEach(function (btn) {
		btn.addEventListener('click', close);
	});

	box.querySelectorAll('[data-lb-step]').forEach(function (btn) {
		btn.addEventListener('click', function () {
			step(parseInt(btn.getAttribute('data-lb-step'), 10) || 1);
		});
	});

	document.addEventListener('keydown', function (e) {
		if (box.hidden) { return; }
		if (e.key === 'Escape') { close(); }
		if (e.key === 'ArrowLeft') { step(-1); }
		if (e.key === 'ArrowRight') { step(1); }
	});
}());
