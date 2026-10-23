/**
 * Tavern 101 - premium interaction layer.
 *
 * Two jobs only, both progressive enhancements:
 *   1. Toggle a scrolled state on <html> so the header can go to glass.
 *   2. Reveal sections as they enter the viewport.
 *
 * The reveal class is applied by this script, so if it never runs the page
 * simply renders fully visible. No dependencies, no layout thrash.
 */
( function () {
	'use strict';

	var root = document.documentElement;

	var reduceMotion = window.matchMedia &&
		window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	/* ------------------------------------------------------------------
	 * 1. Scroll state
	 *
	 * One listener drives both the glass header and the sticky order bar.
	 * Two separate listeners each toggled a class on <html>, so a single
	 * scroll could invalidate styles for the whole document twice a frame.
	 * The classes are only written when they actually change.
	 * ------------------------------------------------------------------ */
	function initScrollState() {
		var HEADER_AT = 24;
		var hero = document.querySelector( '.vintageBanner' );
		var heroAt = hero ? hero.offsetHeight * 0.75 : 400;
		var ticking = false;
		var scrolled = null;
		var pastHero = null;

		function apply() {
			var y = window.pageYOffset || root.scrollTop;
			var isScrolled = y > HEADER_AT;
			var isPastHero = y > heroAt;

			if ( isScrolled !== scrolled ) {
				scrolled = isScrolled;
				root.classList.toggle( 't101-header-scrolled', isScrolled );
			}

			if ( isPastHero !== pastHero ) {
				pastHero = isPastHero;
				root.classList.toggle( 't101-past-hero', isPastHero );
			}

			ticking = false;
		}

		window.addEventListener( 'scroll', function () {
			if ( ! ticking ) {
				window.requestAnimationFrame( apply );
				ticking = true;
			}
		}, { passive: true } );

		// The hero height is only known once its image has laid out.
		window.addEventListener( 'resize', function () {
			heroAt = hero ? hero.offsetHeight * 0.75 : 400;
			apply();
		}, { passive: true } );

		apply();
	}

	/* ------------------------------------------------------------------
	 * 2. Scroll reveal
	 * ------------------------------------------------------------------ */
	function initReveal() {
		if ( reduceMotion || ! ( 'IntersectionObserver' in window ) ) {
			return;
		}

		// Top-level Elementor containers, minus the hero (already in view).
		var targets = document.querySelectorAll(
			'.elementor-location-single .e-con.e-parent,' +
			'.elementor-location-footer .e-con.e-parent,' +
			'main .e-con.e-parent'
		);

		if ( ! targets.length ) {
			return;
		}

		var observer = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( entry ) {
				if ( entry.isIntersecting ) {
					entry.target.classList.add( 'is-visible' );
					observer.unobserve( entry.target );
				}
			} );
		}, {
			rootMargin: '0px 0px -8% 0px',
			threshold: 0.06
		} );

		Array.prototype.forEach.call( targets, function ( el, index ) {
			// Never hide what is already on screen at load.
			if ( el.getBoundingClientRect().top < window.innerHeight * 0.9 ) {
				return;
			}

			el.classList.add( 't101-reveal' );
			el.setAttribute( 'data-t101-delay', String( index % 3 ) );
			observer.observe( el );
		} );
	}

	function init() {
		initScrollState();
		initReveal();
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
}() );
