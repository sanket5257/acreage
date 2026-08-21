/**
 * Acreage — front-end behaviour.
 *
 * Vanilla JavaScript, no jQuery, no build step. Everything is wrapped in one
 * IIFE so nothing leaks into the global namespace where a plugin could collide
 * with it, and every feature checks that its element exists before touching it —
 * a page built in Elementor may legitimately contain none of them.
 */
(function () {
	'use strict';

	/**
	 * Mobile navigation toggle.
	 *
	 * The button carries aria-expanded so a screen reader announces the state,
	 * and Escape closes the menu and returns focus to the button — otherwise a
	 * keyboard user who opens the menu has no way back out of it.
	 */
	function initNav() {
		var burger = document.querySelector('.acreage-hd__burger');
		var nav = document.getElementById('acreage-nav');

		if (!burger || !nav) {
			return;
		}

		function setOpen(open) {
			nav.classList.toggle('is-open', open);
			burger.setAttribute('aria-expanded', open ? 'true' : 'false');
		}

		burger.addEventListener('click', function () {
			setOpen(!nav.classList.contains('is-open'));
		});

		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && nav.classList.contains('is-open')) {
				setOpen(false);
				burger.focus();
			}
		});
	}

	/**
	 * Scroll reveal.
	 *
	 * The resting state is fully visible: entering the viewport adds .is-in,
	 * which plays a one-shot animation. If IntersectionObserver is missing or
	 * never fires, the cost is the animation, not the content. Content that only
	 * appears once JavaScript runs is content that search engines and readers
	 * with scripts disabled never see.
	 */
	function initReveal() {
		var targets = document.querySelectorAll('[data-reveal]');

		if (!targets.length || !('IntersectionObserver' in window)) {
			return;
		}

		var observer = new IntersectionObserver(function (entries) {
			entries.forEach(function (entry) {
				if (entry.isIntersecting) {
					entry.target.classList.add('is-in');
					observer.unobserve(entry.target);
				}
			});
		}, { rootMargin: '0px 0px -10% 0px' });

		targets.forEach(function (t) {
			observer.observe(t);
		});
	}

	function init() {
		initNav();
		initReveal();
	}

	init();

	/*
	 * Elementor swaps widget markup in and out of the page while the editor is
	 * open without reloading it, so anything bound at load is bound to elements
	 * that no longer exist. Re-running init() after a widget renders is what
	 * keeps the menu and the reveal working inside the editor preview.
	 */
	if (window.elementorFrontend && window.elementorFrontend.hooks) {
		window.elementorFrontend.hooks.addAction('frontend/element_ready/global', init);
	}
})();
