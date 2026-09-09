/**
 * TapBar settings screen.
 *
 * Adding, removing and reordering items. Everything else is a plain form.
 */

( function () {
	'use strict';

	var list = document.getElementById( 'tbar-items' );
	var add = document.getElementById( 'tbar-add-item' );
	var hint = document.getElementById( 'tbar-add-hint' );

	if ( ! list || ! add ) {
		return;
	}

	var config = window.tbarAdmin || { maxItems: 4, maxNotice: '', confirmText: 'Remove this item?' };

	/**
	 * Renumber every field so the array indexes stay contiguous after a
	 * removal or a reorder. PHP reads the order it receives.
	 */
	function reindex() {
		var rows = list.querySelectorAll( '.tbar-item' );

		Array.prototype.forEach.call( rows, function ( row, index ) {
			row.setAttribute( 'data-index', index );

			var fields = row.querySelectorAll( '[name^="tbar[items]"]' );

			Array.prototype.forEach.call( fields, function ( field ) {
				field.name = field.name.replace( /tbar\[items\]\[\d+\]/, 'tbar[items][' + index + ']' );
			} );
		} );

		updateAddState();
	}

	/**
	 * Say why the button is disabled rather than silently refusing.
	 */
	function updateAddState() {
		var count = list.querySelectorAll( '.tbar-item' ).length;
		var full = count >= config.maxItems;

		add.disabled = full;
		hint.textContent = full ? config.maxNotice : '';
	}

	list.addEventListener( 'click', function ( event ) {
		if ( ! event.target.classList.contains( 'tbar-item__remove' ) ) {
			return;
		}

		if ( ! window.confirm( config.confirmText ) ) {
			return;
		}

		event.target.closest( '.tbar-item' ).remove();
		reindex();
	} );

	add.addEventListener( 'click', function () {
		var rows = list.querySelectorAll( '.tbar-item' );

		if ( rows.length >= config.maxItems ) {
			return;
		}

		if ( ! rows.length ) {
			// Nothing to clone from, so reload with one blank row appended by
			// the server rather than building markup twice in two languages.
			window.location.search += ( window.location.search ? '&' : '?' ) + 'tbar_add=1';
			return;
		}

		var copy = rows[ rows.length - 1 ].cloneNode( true );

		Array.prototype.forEach.call( copy.querySelectorAll( 'input[type="text"]' ), function ( field ) {
			field.value = '';
		} );

		Array.prototype.forEach.call( copy.querySelectorAll( 'input[type="checkbox"]' ), function ( field ) {
			field.checked = false;
		} );

		// A cloned id would collide, so clear it and let the server mint one.
		var id = copy.querySelector( 'input[name$="[id]"]' );

		if ( id ) {
			id.value = '';
		}

		var badge = copy.querySelector( '.tbar-item__id' );

		if ( badge ) {
			badge.textContent = '';
		}

		list.appendChild( copy );
		reindex();
	} );

	// Drag to reorder, using the browser's own drag and drop.
	var dragged = null;

	Array.prototype.forEach.call( list.querySelectorAll( '.tbar-item' ), makeDraggable );

	function makeDraggable( row ) {
		row.draggable = true;

		row.addEventListener( 'dragstart', function () {
			dragged = row;
			row.classList.add( 'is-dragging' );
		} );

		row.addEventListener( 'dragend', function () {
			row.classList.remove( 'is-dragging' );
			dragged = null;
			reindex();
		} );

		row.addEventListener( 'dragover', function ( event ) {
			event.preventDefault();

			if ( ! dragged || dragged === row ) {
				return;
			}

			var box = row.getBoundingClientRect();
			var after = event.clientY > box.top + box.height / 2;

			list.insertBefore( dragged, after ? row.nextSibling : row );
		} );
	}

	updateAddState();
} )();
