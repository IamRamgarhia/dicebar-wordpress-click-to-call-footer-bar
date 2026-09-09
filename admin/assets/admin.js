/**
 * Footer Bar settings screen.
 *
 * Three jobs: switch tabs without losing unsaved changes, build item rows from
 * the type definitions, and reorder by dragging.
 *
 * Rows are built here rather than cloned from an existing one, because cloning
 * cannot produce the first row when none exists yet, and because changing an
 * item's type has to rebuild its fields anyway.
 */

( function () {
	'use strict';

	var config = window.fbarAdmin;

	if ( ! config ) {
		return;
	}

	var strings = config.strings;
	var list = document.getElementById( 'fbar-items' );
	var addButton = document.getElementById( 'fbar-add-item' );
	var hint = document.getElementById( 'fbar-add-hint' );
	var empty = document.querySelector( '.fbar__empty' );
	var activeTab = document.getElementById( 'fbar-active-tab' );

	/**
	 * One item type by slug.
	 *
	 * @param {string} slug Type slug.
	 * @return {Object|null} The type, or null.
	 */
	function typeBySlug( slug ) {
		for ( var i = 0; i < config.types.length; i++ ) {
			if ( config.types[ i ].slug === slug ) {
				return config.types[ i ];
			}
		}

		return null;
	}

	/**
	 * Build an element.
	 *
	 * @param {string} tag        Tag name.
	 * @param {Object} attributes Attributes to set.
	 * @param {Array}  children   Child nodes or strings.
	 * @return {Element} The element.
	 */
	function make( tag, attributes, children ) {
		var node = document.createElement( tag );

		Object.keys( attributes || {} ).forEach( function ( name ) {
			if ( name === 'class' ) {
				node.className = attributes[ name ];
			} else if ( name === 'text' ) {
				node.textContent = attributes[ name ];
			} else if ( attributes[ name ] !== null && attributes[ name ] !== false ) {
				node.setAttribute( name, attributes[ name ] );
			}
		} );

		( children || [] ).forEach( function ( child ) {
			node.appendChild( typeof child === 'string' ? document.createTextNode( child ) : child );
		} );

		return node;
	}

	/**
	 * A labelled field wrapping a control.
	 *
	 * @param {string}  label   Field label.
	 * @param {Element} control The control.
	 * @param {string}  role    Optional data-role marker.
	 * @return {Element} The field.
	 */
	function field( label, control, role ) {
		return make( 'label', { class: 'fbar__field', 'data-role': role || null }, [
			make( 'span', { text: label } ),
			control,
		] );
	}

	/**
	 * A select control.
	 *
	 * @param {string} name    Field name.
	 * @param {Object} choices Value to label map.
	 * @param {string} current Selected value.
	 * @return {Element} The select.
	 */
	function select( name, choices, current ) {
		var node = make( 'select', { name: name } );

		Object.keys( choices ).forEach( function ( value ) {
			var option = make( 'option', { value: value, text: choices[ value ] } );

			if ( value === current ) {
				option.selected = true;
			}

			node.appendChild( option );
		} );

		return node;
	}

	/**
	 * Type slugs mapped to their labels.
	 *
	 * @return {Object} Choices for a type select.
	 */
	function typeChoices() {
		var choices = {};

		config.types.forEach( function ( type ) {
			choices[ type.slug ] = type.label;
		} );

		return choices;
	}

	/**
	 * Icon names mapped to themselves, with a default entry first.
	 *
	 * @return {Object} Choices for an icon select.
	 */
	function iconChoices() {
		var choices = { '': strings.defaultIcon };

		config.icons.forEach( function ( name ) {
			choices[ name ] = name;
		} );

		return choices;
	}

	/**
	 * Build a blank item row.
	 *
	 * @param {number} index Position in the list.
	 * @return {Element} The row.
	 */
	function buildRow( index ) {
		var type = config.types[ 0 ];
		var base = 'fbar[items][' + index + ']';

		var head = make( 'div', { class: 'fbar__item-head' }, [
			make( 'span', { class: 'fbar__grip', 'aria-hidden': 'true' } ),
			make( 'strong', { class: 'fbar__item-title', text: strings.untitled } ),
			make( 'code', { class: 'fbar__item-id', text: strings.newId } ),
			make( 'button', {
				type: 'button',
				class: 'fbar__remove',
				text: strings.remove,
			} ),
		] );

		var fields = make( 'div', { class: 'fbar__fields' }, [
			field( strings.type, select( base + '[type]', typeChoices(), type.slug ) ),
			field( strings.label, make( 'input', { type: 'text', name: base + '[label]', placeholder: type.label } ) ),
			field( type.valueLabel, make( 'input', { type: 'text', name: base + '[value]' } ), 'value' ),
			field( strings.icon, select( base + '[icon]', iconChoices(), '' ) ),
			make( 'div', { class: 'fbar__extra', 'data-role': 'extra' } ),
			make( 'label', { class: 'fbar__field fbar__field--check' }, [
				make( 'input', { type: 'checkbox', name: base + '[primary]', value: '1' } ),
				make( 'span', { text: strings.primary } ),
			] ),
			field( strings.devices, select( base + '[show][devices]', config.choices.devices, 'inherit' ) ),
			field( strings.users, select( base + '[show][users]', config.choices.users, 'inherit' ) ),
		] );

		var row = make( 'div', { class: 'fbar__item', draggable: 'true' }, [
			head,
			make( 'input', { type: 'hidden', name: base + '[id]', value: '' } ),
			fields,
		] );

		buildExtra( row, type, index );
		makeDraggable( row );

		return row;
	}

	/**
	 * Replace a row's secondary fields with those its type declares.
	 *
	 * @param {Element} row   The item row.
	 * @param {Object}  type  The item type.
	 * @param {number}  index Position in the list.
	 */
	function buildExtra( row, type, index ) {
		var holder = row.querySelector( '[data-role="extra"]' );

		if ( ! holder ) {
			return;
		}

		// Emptied by removing children rather than by assigning innerHTML, so
		// nothing in this file can ever parse a string as markup.
		while ( holder.firstChild ) {
			holder.removeChild( holder.firstChild );
		}

		type.extra.forEach( function ( extra ) {
			var name = 'fbar[items][' + index + '][extra][' + extra.key + ']';

			if ( extra.kind === 'boolean' ) {
				holder.appendChild(
					make( 'label', { class: 'fbar__field fbar__field--check' }, [
						make( 'input', { type: 'checkbox', name: name, value: '1' } ),
						make( 'span', { text: extra.label } ),
					] )
				);
				return;
			}

			holder.appendChild( field( extra.label, make( 'input', { type: 'text', name: name } ) ) );
		} );
	}

	/**
	 * Renumber every field so the indexes stay contiguous.
	 *
	 * PHP reads the order it receives, so this is what makes a drag or a
	 * removal actually stick.
	 */
	function reindex() {
		var rows = list.querySelectorAll( '.fbar__item' );

		Array.prototype.forEach.call( rows, function ( row, index ) {
			row.setAttribute( 'data-index', index );

			Array.prototype.forEach.call( row.querySelectorAll( '[name^="fbar[items]"]' ), function ( input ) {
				input.name = input.name.replace( /fbar\[items\]\[\d+\]/, 'fbar[items][' + index + ']' );
			} );
		} );

		updateState();
	}

	/**
	 * Reflect the item count in the button, the hint and the empty state.
	 */
	function updateState() {
		var count = list.querySelectorAll( '.fbar__item' ).length;
		var full = count >= config.maxItems;

		addButton.disabled = full;
		hint.textContent = full ? strings.full : '';

		if ( empty ) {
			empty.hidden = count > 0;
		}
	}

	/**
	 * Keep a row's heading in step with its label.
	 *
	 * @param {Element} row The item row.
	 */
	function refreshTitle( row ) {
		var title = row.querySelector( '.fbar__item-title' );
		var label = row.querySelector( '[name$="[label]"]' );
		var type = row.querySelector( '[name$="[type]"]' );

		if ( ! title ) {
			return;
		}

		if ( label && label.value.trim() ) {
			title.textContent = label.value.trim();
			return;
		}

		var definition = type ? typeBySlug( type.value ) : null;

		title.textContent = definition ? definition.label : strings.untitled;
	}

	/* ---- Adding, removing, editing ------------------------------------- */

	addButton.addEventListener( 'click', function () {
		var count = list.querySelectorAll( '.fbar__item' ).length;

		if ( count >= config.maxItems ) {
			return;
		}

		var row = buildRow( count );

		list.appendChild( row );
		reindex();

		var first = row.querySelector( 'input[type="text"]' );

		if ( first ) {
			first.focus();
		}
	} );

	list.addEventListener( 'click', function ( event ) {
		if ( ! event.target.classList.contains( 'fbar__remove' ) ) {
			return;
		}

		if ( ! window.confirm( strings.confirm ) ) {
			return;
		}

		event.target.closest( '.fbar__item' ).remove();
		reindex();
	} );

	list.addEventListener( 'change', function ( event ) {
		var row = event.target.closest( '.fbar__item' );

		if ( ! row ) {
			return;
		}

		if ( event.target.name && event.target.name.indexOf( '[type]' ) !== -1 ) {
			var type = typeBySlug( event.target.value );

			if ( type ) {
				var index = parseInt( row.getAttribute( 'data-index' ), 10 ) || 0;
				var valueField = row.querySelector( '[data-role="value"] span' );
				var labelInput = row.querySelector( '[name$="[label]"]' );

				if ( valueField ) {
					valueField.textContent = type.valueLabel;
				}

				if ( labelInput ) {
					labelInput.placeholder = type.label;
				}

				buildExtra( row, type, index );
			}
		}

		refreshTitle( row );
	} );

	list.addEventListener( 'input', function ( event ) {
		if ( event.target.name && event.target.name.indexOf( '[label]' ) !== -1 ) {
			refreshTitle( event.target.closest( '.fbar__item' ) );
		}
	} );

	/* ---- Reordering ----------------------------------------------------- */

	var dragging = null;

	/**
	 * Let a row be dragged into a new position.
	 *
	 * @param {Element} row The item row.
	 */
	function makeDraggable( row ) {
		row.addEventListener( 'dragstart', function () {
			dragging = row;
			row.classList.add( 'is-dragging' );
		} );

		row.addEventListener( 'dragend', function () {
			row.classList.remove( 'is-dragging' );
			dragging = null;
			reindex();
		} );

		row.addEventListener( 'dragover', function ( event ) {
			event.preventDefault();

			if ( ! dragging || dragging === row ) {
				return;
			}

			var box = row.getBoundingClientRect();
			var below = event.clientY > box.top + box.height / 2;

			list.insertBefore( dragging, below ? row.nextSibling : row );
		} );
	}

	Array.prototype.forEach.call( list.querySelectorAll( '.fbar__item' ), makeDraggable );

	/* ---- Starter kits ---------------------------------------------------- */

	/**
	 * Fill a row's controls from a saved item.
	 *
	 * @param {Element} row  The item row.
	 * @param {Object}  item The item to write in.
	 */
	function fillRow( row, item ) {
		var type = row.querySelector( '[name$="[type]"]' );

		if ( type ) {
			type.value = item.type;
			type.dispatchEvent( new Event( 'change', { bubbles: true } ) );
		}

		var label = row.querySelector( '[name$="[label]"]' );

		if ( label ) {
			label.value = item.label || '';
		}

		var value = row.querySelector( '[name$="[value]"]' );

		if ( value ) {
			value.value = item.value || '';
		}

		var icon = row.querySelector( '[name$="[icon]"]' );

		if ( icon && item.icon ) {
			icon.value = item.icon;
		}

		var primary = row.querySelector( '[name$="[primary]"]' );

		if ( primary ) {
			primary.checked = !! item.primary;
		}

		Object.keys( item.extra || {} ).forEach( function ( key ) {
			var control = row.querySelector( '[name$="[extra][' + key + ']"]' );

			if ( ! control ) {
				return;
			}

			if ( control.type === 'checkbox' ) {
				control.checked = !! item.extra[ key ];
			} else {
				control.value = item.extra[ key ];
			}
		} );

		refreshTitle( row );
	}

	/**
	 * Replace the item list with a starter kit.
	 *
	 * @param {Object} kit The kit.
	 */
	function applyKit( kit ) {
		while ( list.firstChild ) {
			list.removeChild( list.firstChild );
		}

		kit.items.forEach( function ( item, index ) {
			var row = buildRow( index );

			list.appendChild( row );
			fillRow( row, item );
		} );

		reindex();

		if ( hint ) {
			hint.textContent = strings.applied;
		}

		var firstEmpty = list.querySelector( '[name$="[value]"]' );

		if ( firstEmpty ) {
			firstEmpty.focus();
		}
	}

	Array.prototype.forEach.call( document.querySelectorAll( '.fbar__kit' ), function ( button ) {
		button.addEventListener( 'click', function () {
			var id = button.getAttribute( 'data-kit' );
			var kit = null;

			for ( var i = 0; i < config.presets.length; i++ ) {
				if ( config.presets[ i ].id === id ) {
					kit = config.presets[ i ];
					break;
				}
			}

			if ( ! kit ) {
				return;
			}

			// Replacing what is already there is destructive, so it asks.
			if ( list.querySelector( '.fbar__item' ) && ! window.confirm( strings.replace ) ) {
				return;
			}

			applyKit( kit );
		} );
	} );

	/* ---- Advanced options ------------------------------------------------ */

	Array.prototype.forEach.call( document.querySelectorAll( '[data-advanced]' ), function ( group ) {
		var rows = group.querySelectorAll( '.fbar__row' );

		if ( ! rows.length ) {
			return;
		}

		var toggle = document.createElement( 'button' );

		toggle.type = 'button';
		toggle.className = 'fbar__advanced-toggle';
		toggle.setAttribute( 'aria-expanded', 'false' );
		toggle.textContent = strings.more;

		group.parentNode.insertBefore( toggle, group );
		group.hidden = true;

		toggle.addEventListener( 'click', function () {
			var open = group.hidden;

			group.hidden = ! open;
			toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			toggle.textContent = open ? strings.less : strings.more;
		} );
	} );

	/* ---- Tabs ----------------------------------------------------------- */

	// Switching in the page rather than following the link, so a change made on
	// one tab is still there when the form is submitted from another.
	var tabs = document.querySelectorAll( '.fbar__tab' );
	var panels = document.querySelectorAll( '.fbar__panel' );

	function showTab( name ) {
		Array.prototype.forEach.call( tabs, function ( tab ) {
			var current = tab.getAttribute( 'data-tab' ) === name;

			tab.classList.toggle( 'is-current', current );
			tab.setAttribute( 'aria-selected', current ? 'true' : 'false' );
		} );

		Array.prototype.forEach.call( panels, function ( panel ) {
			panel.hidden = panel.id !== 'fbar-panel-' + name;
		} );

		if ( activeTab ) {
			activeTab.value = name;
		}

		if ( window.history && window.history.replaceState ) {
			var url = new URL( window.location.href );

			url.searchParams.set( 'tab', name );
			window.history.replaceState( {}, '', url );
		}
	}

	Array.prototype.forEach.call( tabs, function ( tab ) {
		tab.addEventListener( 'click', function ( event ) {
			event.preventDefault();
			showTab( tab.getAttribute( 'data-tab' ) );
		} );
	} );

	showTab( activeTab ? activeTab.value : 'items' );

	updateState();
} )();
