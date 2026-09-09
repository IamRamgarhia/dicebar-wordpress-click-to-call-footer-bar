/**
 * Footer Bar front-end behaviour.
 *
 * Deliberately small and dependency free. Everything here is either something
 * CSS genuinely cannot do, or something that must not be baked into a cached
 * page because it varies between two visitors looking at the same URL.
 */

( function () {
	'use strict';

	var bar = document.querySelector( '.fbar--fixed' );

	if ( ! bar ) {
		return;
	}

	var inner = bar.querySelector( '.fbar__inner' );

	/**
	 * Whether the visitor is signed in.
	 *
	 * A page cache serves one copy to everybody, so this cannot be decided on
	 * the server. The session cookie is HTTP-only and unreadable here, but
	 * WordPress also sets wp-settings-time- for signed-in users and does not
	 * mark that one, which makes it the usual cache-safe substitute.
	 *
	 * @return {boolean} True when a signed-in visitor is likely.
	 */
	function isLoggedIn() {
		return document.cookie.indexOf( 'wp-settings-time-' ) !== -1;
	}

	/**
	 * Remove items whose audience rule the visitor does not match.
	 */
	function applyUserRules() {
		var loggedIn = isLoggedIn();
		var barRule = bar.getAttribute( 'data-fbar-users' ) || 'all';

		if ( ( barRule === 'in' && ! loggedIn ) || ( barRule === 'out' && loggedIn ) ) {
			bar.parentNode.removeChild( bar );
			return false;
		}

		var items = bar.querySelectorAll( '[data-fbar-users]' );

		Array.prototype.forEach.call( items, function ( item ) {
			var rule = item.getAttribute( 'data-fbar-users' );

			if ( ( rule === 'in' && ! loggedIn ) || ( rule === 'out' && loggedIn ) ) {
				item.parentNode.removeChild( item );
			}
		} );

		return true;
	}

	/**
	 * Whether any item is still both present and not hidden by CSS.
	 *
	 * Device rules are media queries and audience rules are script, so neither
	 * mechanism alone knows whether the row ended up empty. A bar drawing its
	 * frame around nothing is worse than no bar.
	 *
	 * @return {boolean} True when at least one item is visible.
	 */
	function hasVisibleItems() {
		var items = bar.querySelectorAll( '.fbar__item' );
		var visible = 0;

		Array.prototype.forEach.call( items, function ( item ) {
			if ( item.offsetParent !== null || item.getClientRects().length ) {
				visible++;
			}
		} );

		return visible > 0;
	}

	/**
	 * Publish the bar's real height so the page can be given that space back.
	 *
	 * A fixed bar covers the last stretch of every page: a footer's final
	 * line, a form's submit button, the last row of a table. The height is
	 * measured rather than assumed because it changes with the label setting
	 * and with the safe-area inset.
	 */
	function publishHeight() {
		if ( ! bar.isConnected ) {
			return;
		}

		var height = Math.ceil( bar.getBoundingClientRect().height );
		var side = bar.classList.contains( 'fbar--top' ) ? 'Top' : 'Bottom';

		document.documentElement.style.setProperty( '--fbar-height', height + 'px' );
		document.body.style[ 'padding' + side ] = height + 'px';
	}

	/**
	 * Hide the bar while a named element is on screen.
	 *
	 * A Call item is noise while the contact section carrying the same number
	 * is visible.
	 */
	function watchHideTarget() {
		var selector = bar.getAttribute( 'data-fbar-hide-near' );

		if ( ! selector || typeof window.IntersectionObserver !== 'function' ) {
			return;
		}

		var target;

		try {
			target = document.querySelector( selector );
		} catch ( error ) {
			return;
		}

		if ( ! target ) {
			return;
		}

		new window.IntersectionObserver( function ( entries ) {
			bar.classList.toggle( 'is-away', entries[ 0 ].isIntersecting );
		} ).observe( target );
	}

	/**
	 * Hide on scroll down, show on scroll up.
	 */
	function watchScrollDirection() {
		if ( bar.getAttribute( 'data-fbar-appear' ) !== 'scroll_up' ) {
			return;
		}

		var last = window.pageYOffset;
		var ticking = false;

		window.addEventListener(
			'scroll',
			function () {
				if ( ticking ) {
					return;
				}

				ticking = true;

				window.requestAnimationFrame( function () {
					var now = window.pageYOffset;

					if ( Math.abs( now - last ) > 6 ) {
						bar.classList.toggle( 'is-away', now > last && now > 80 );
						last = now;
					}

					ticking = false;
				} );
			},
			{ passive: true }
		);
	}

	/**
	 * Handle the items that act rather than navigate.
	 */
	function wireActions() {
		bar.addEventListener( 'click', function ( event ) {
			var item = event.target.closest( '[data-fbar-item]' );

			if ( ! item ) {
				return;
			}

			// Announce the tap so the site's own tag manager can see it. The
			// plugin sends nothing anywhere; what happens next is the owner's
			// business.
			document.dispatchEvent(
				new CustomEvent( 'fbar:click', {
					detail: {
						id: item.getAttribute( 'data-fbar-item' ),
						type: item.getAttribute( 'data-fbar-type' ),
					},
				} )
			);

			var action = item.getAttribute( 'data-fbar-action' );

			if ( ! action ) {
				return;
			}

			if ( action === 'top' ) {
				event.preventDefault();
				window.scrollTo( { top: 0, behavior: 'smooth' } );
				return;
			}

			if ( action === 'anchor' ) {
				var hash = item.getAttribute( 'href' );
				var target = hash && hash.charAt( 0 ) === '#' ? document.querySelector( hash ) : null;

				if ( target ) {
					event.preventDefault();
					target.scrollIntoView( { behavior: 'smooth', block: 'start' } );
				}

				return;
			}

			if ( action === 'share' ) {
				event.preventDefault();
				share();
			}
		} );
	}

	/**
	 * Share the current page, falling back to copying its address.
	 */
	function share() {
		var data = { title: document.title, url: window.location.href };

		if ( navigator.share ) {
			navigator.share( data ).catch( function () {} );
			return;
		}

		if ( navigator.clipboard ) {
			navigator.clipboard.writeText( data.url ).catch( function () {} );
		}
	}

	if ( ! applyUserRules() ) {
		return;
	}

	if ( ! hasVisibleItems() ) {
		bar.parentNode.removeChild( bar );
		return;
	}

	publishHeight();
	watchHideTarget();
	watchScrollDirection();
	wireActions();

	if ( typeof window.ResizeObserver === 'function' && inner ) {
		new window.ResizeObserver( publishHeight ).observe( inner );
	}

	window.addEventListener( 'orientationchange', publishHeight );
	window.addEventListener( 'resize', publishHeight, { passive: true } );

	// Device rules are media queries, so a rotation can empty the row without
	// any script running. Re-check when the breakpoint is crossed.
	if ( window.matchMedia ) {
		window.matchMedia( '(max-width: 1023px)' ).addEventListener( 'change', function () {
			if ( bar.isConnected && ! hasVisibleItems() ) {
				bar.parentNode.removeChild( bar );
				document.body.style.paddingBottom = '';
				document.body.style.paddingTop = '';
			}
		} );
	}
} )();
