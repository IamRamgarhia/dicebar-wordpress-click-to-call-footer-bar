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
	var empty = document.querySelector( '.fbarui__empty' );
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
		return make( 'label', { class: 'fbarui__field', 'data-role': role || null }, [
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
	 * A grid of icons to choose from, drawn from the page's own sprite.
	 *
	 * A list of icon names is not an icon picker. Using the same sprite the
	 * preview uses means what is chosen here is exactly what appears there.
	 *
	 * @param {string} name    Field name.
	 * @param {string} current Selected icon.
	 * @return {Element} The picker.
	 */
	function iconPicker( name, current ) {
		var grid = make( 'div', { class: 'fbarui__iconpicker', role: 'radiogroup' } );

		config.icons.forEach( function ( icon ) {
			var input = make( 'input', { type: 'radio', name: name, value: icon } );

			if ( icon === current ) {
				input.checked = true;
			}

			var svg = document.createElementNS( 'http://www.w3.org/2000/svg', 'svg' );
			var use = document.createElementNS( 'http://www.w3.org/2000/svg', 'use' );

			svg.setAttribute( 'class', 'fbarui__iconglyph' );
			svg.setAttribute( 'viewBox', '0 0 24 24' );
			svg.setAttribute( 'aria-hidden', 'true' );
			use.setAttribute( 'href', '#fbar-i-' + icon );
			svg.appendChild( use );

			grid.appendChild(
				make( 'label', { class: 'fbarui__iconopt', title: icon }, [
					input,
					svg,
					make( 'span', { class: 'screen-reader-text', text: icon } ),
				] )
			);
		} );

		return grid;
	}

	/**
	 * Choose an icon in a row's picker.
	 *
	 * @param {Element} row  The item row.
	 * @param {string}  icon Icon name.
	 */
	function selectIcon( row, icon ) {
		var option = row.querySelector( '[name$="[icon]"][value="' + icon + '"]' );

		if ( option ) {
			option.checked = true;
		}
	}

	/**
	 * Hide the icon picker for a type that decides its own glyph.
	 *
	 * A social item takes its icon from its network, so offering a second
	 * control that can disagree with the first is worse than offering none.
	 *
	 * @param {Element} row  The item row.
	 * @param {Object}  type The item type.
	 */
	function syncIconField( row, type ) {
		var picker = row.querySelector( '[data-role="iconfield"]' );

		if ( picker ) {
			picker.hidden = type.slug === 'social';
		}
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

		var head = make( 'div', { class: 'fbarui__item-head' }, [
			make( 'span', { class: 'fbarui__grip', 'aria-hidden': 'true' } ),
			make( 'strong', { class: 'fbarui__item-title', text: strings.untitled } ),
			make( 'code', { class: 'fbarui__item-id', text: strings.newId } ),
			make( 'button', {
				type: 'button',
				class: 'fbarui__remove',
				text: strings.remove,
			} ),
		] );

		var fields = make( 'div', { class: 'fbarui__fields' }, [
			field( strings.type, select( base + '[type]', typeChoices(), type.slug ) ),
			field( strings.label, make( 'input', { type: 'text', name: base + '[label]', placeholder: type.label } ) ),
			field( type.valueLabel, make( 'input', { type: 'text', name: base + '[value]' } ), 'value' ),
			make( 'div', { class: 'fbarui__field fbarui__field--icons', 'data-role': 'iconfield' }, [
				make( 'span', { text: strings.icon } ),
				iconPicker( base + '[icon]', type.icon ),
			] ),
			make( 'div', { class: 'fbarui__extra', 'data-role': 'extra' } ),
			make( 'label', { class: 'fbarui__field fbarui__field--check' }, [
				make( 'input', { type: 'checkbox', name: base + '[primary]', value: '1' } ),
				make( 'span', { text: strings.primary } ),
			] ),
			field( strings.devices, select( base + '[show][devices]', config.choices.devices, 'inherit' ) ),
			field( strings.users, select( base + '[show][users]', config.choices.users, 'inherit' ) ),
		] );

		var row = make( 'div', { class: 'fbarui__item', draggable: 'true' }, [
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
					make( 'label', { class: 'fbarui__field fbarui__field--check' }, [
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
		var rows = list.querySelectorAll( '.fbarui__item' );

		Array.prototype.forEach.call( rows, function ( row, index ) {
			row.setAttribute( 'data-index', index );

			Array.prototype.forEach.call( row.querySelectorAll( '[name^="fbar[items]"]' ), function ( input ) {
				input.name = input.name.replace( /fbar\[items\]\[\d+\]/, 'fbar[items][' + index + ']' );
			} );
		} );

		updateState();

		if ( typeof window.fbarRefreshPreview === 'function' ) {
			window.fbarRefreshPreview();
		}
	}

	/**
	 * Reflect the item count in the button, the hint and the empty state.
	 */
	function updateState() {
		var count = list.querySelectorAll( '.fbarui__item' ).length;
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
		var title = row.querySelector( '.fbarui__item-title' );
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
		var count = list.querySelectorAll( '.fbarui__item' ).length;

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
		if ( ! event.target.classList.contains( 'fbarui__remove' ) ) {
			return;
		}

		if ( ! window.confirm( strings.confirm ) ) {
			return;
		}

		event.target.closest( '.fbarui__item' ).remove();
		reindex();
	} );

	list.addEventListener( 'change', function ( event ) {
		var row = event.target.closest( '.fbarui__item' );

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
				selectIcon( row, type.icon );
				syncIconField( row, type );
			}
		}

		// A social item's glyph is its network, so choosing a network has
		// to move the icon with it or the two controls disagree.
		if ( event.target.name && event.target.name.indexOf( '[extra][network]' ) !== -1 ) {
			selectIcon( row, event.target.value );
		}

		refreshTitle( row );
	} );

	list.addEventListener( 'input', function ( event ) {
		if ( event.target.name && event.target.name.indexOf( '[label]' ) !== -1 ) {
			refreshTitle( event.target.closest( '.fbarui__item' ) );
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

	Array.prototype.forEach.call( list.querySelectorAll( '.fbarui__item' ), function ( row ) {
		makeDraggable( row );

		var typeControl = row.querySelector( '[name$="[type]"]' );
		var loaded = typeControl ? typeBySlug( typeControl.value ) : null;

		if ( loaded ) {
			syncIconField( row, loaded );
		}
	} );

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

	Array.prototype.forEach.call( document.querySelectorAll( '.fbarui__kit' ), function ( button ) {
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
			if ( list.querySelector( '.fbarui__item' ) && ! window.confirm( strings.replace ) ) {
				return;
			}

			applyKit( kit );
		} );
	} );

	/* ---- Advanced options ------------------------------------------------ */

	Array.prototype.forEach.call( document.querySelectorAll( '[data-advanced]' ), function ( group ) {
		var rows = group.querySelectorAll( '.fbarui__row' );

		if ( ! rows.length ) {
			return;
		}

		var toggle = document.createElement( 'button' );

		toggle.type = 'button';
		toggle.className = 'fbarui__advanced-toggle';
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
	var tabs = document.querySelectorAll( '.fbarui__tab' );
	var panels = document.querySelectorAll( '.fbarui__panel' );

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

	/* ---- Live preview ---------------------------------------------------- */

	var preview = document.getElementById( 'fbar-preview' );
	var previewInner = document.getElementById( 'fbar-preview-inner' );
	var previewScreen = document.getElementById( 'fbar-preview-screen' );
	var previewNote = document.getElementById( 'fbar-preview-note' );
	var previewStage = 'photo';
	var form = document.querySelector( '.fbarui__form' );

	/**
	 * The value of a named control in the form.
	 *
	 * @param {string} name    Control name.
	 * @param {string} missing Value to use when the control is absent.
	 * @return {string} The value.
	 */
	function value( name, missing ) {
		var control = form.querySelector( '[name="' + name + '"]' );

		if ( ! control ) {
			return missing;
		}

		if ( control.type === 'checkbox' ) {
			return control.checked ? control.value : '';
		}

		return control.value;
	}

	/**
	 * The chosen value of a radio group.
	 *
	 * @param {string} name    Group name.
	 * @param {string} missing Value to use when nothing is chosen.
	 * @return {string} The value.
	 */
	function chosen( name, missing ) {
		var control = form.querySelector( '[name="' + name + '"]:checked' );

		return control ? control.value : missing;
	}

	/**
	 * Redraw the preview from whatever the form currently says.
	 *
	 * Reads the live controls rather than the saved settings, so the effect of
	 * a change is visible before anything is saved. That is the whole point:
	 * choosing a look from a written description is guesswork.
	 */
	function refreshPreview() {
		if ( ! preview || ! previewInner ) {
			return;
		}

		var mode = chosen( 'fbar[style][label][mode]', 'icon_label' );
		var look = chosen( 'fbar[style][preset]', 'glass' );

		preview.className = [
			'fbar',
			'fbar--preview',
			previewStage === 'dark' ? 'fbar--scheme-dark' : 'fbar--scheme-light',
			'fbar--preset-' + look,
			'fbar--item-' + value( 'fbar[style][item][shape]', 'plain' ),
			'fbar--shadow-' + value( 'fbar[style][shadow]', 'soft' ),
			'fbar--show-' + mode.replace( '_', '-' ),
			'fbar--divider-' + ( value( 'fbar[style][divider]', '' ) ? 'hairline' : 'none' ),
			'fbar--case-upper',
			value( 'fbar[style][blur]', '' ) ? 'fbar--blur' : 'fbar--no-blur',
		].join( ' ' );

		var palette = previewStage === 'dark' ? 'dark' : 'light';

		var tokens = {
			'--fbar-bar-bg': value( 'fbar[style][' + palette + '][bar_bg]', previewStage === 'dark' ? '#1c1c1e' : '#ffffff' ),
			'--fbar-text': value( 'fbar[style][' + palette + '][text]', '#1c1c1e' ),
			'--fbar-icon': value( 'fbar[style][' + palette + '][icon]', '#1c1c1e' ),
			'--fbar-accent': value( 'fbar[style][' + palette + '][accent]', '#0a84ff' ),
			'--fbar-hover-bg': value( 'fbar[style][' + palette + '][hover_bg]', '#0a84ff' ),
			'--fbar-hover-text': value( 'fbar[style][' + palette + '][hover_text]', '#ffffff' ),
			'--fbar-opacity': value( 'fbar[style][opacity]', '78' ) + '%',
			'--fbar-glass': value( 'fbar[style][glass]', '22' ) + 'px',
			'--fbar-radius': value( 'fbar[style][radius]', '18' ) + 'px',
		};

		Object.keys( tokens ).forEach( function ( token ) {
			preview.style.setProperty( token, tokens[ token ] );
		} );

		while ( previewInner.firstChild ) {
			previewInner.removeChild( previewInner.firstChild );
		}

		var rows = list.querySelectorAll( '.fbarui__item' );

		if ( ! rows.length ) {
			var empty = document.createElement( 'p' );

			empty.className = 'fbarui__preview-empty';
			empty.textContent = strings.previewEmpty;
			previewInner.appendChild( empty );
			previewNote.textContent = '';
			return;
		}

		var longest = 0;

		Array.prototype.forEach.call( rows, function ( row ) {
			var typeControl = row.querySelector( '[name$="[type]"]' );
			var type = typeControl ? typeBySlug( typeControl.value ) : null;
			var labelInput = row.querySelector( '[name$="[label]"]' );
			// The icon field is a radio group, so this has to ask for the
			// checked one. Without :checked it always answered with the
			// first icon in the grid and the preview never moved.
			var iconControl = row.querySelector( '[name$="[icon]"]:checked' );
			var network = row.querySelector( '[name$="[extra][network]"]' );
			var primary = row.querySelector( '[name$="[primary]"]' );

			var label = ( labelInput && labelInput.value.trim() ) || ( type ? type.label : '' );
			var icon = ( network && network.value ) || ( iconControl && iconControl.value ) || ( type ? type.icon : '' );

			longest = Math.max( longest, label.length );

			var button = document.createElement( 'span' );

			button.className = 'fbar__item' + ( primary && primary.checked ? ' fbar__item--primary' : '' );

			if ( mode !== 'label' && icon ) {
				var svg = document.createElementNS( 'http://www.w3.org/2000/svg', 'svg' );
				var use = document.createElementNS( 'http://www.w3.org/2000/svg', 'use' );

				svg.setAttribute( 'class', 'fbar__icon' );
				svg.setAttribute( 'viewBox', '0 0 24 24' );
				svg.setAttribute( 'aria-hidden', 'true' );
				use.setAttribute( 'href', '#fbar-i-' + icon );
				svg.appendChild( use );
				button.appendChild( svg );
			}

			if ( mode !== 'icon' ) {
				var text = document.createElement( 'span' );

				text.className = 'fbar__label';
				text.textContent = label;
				button.appendChild( text );
			}

			previewInner.appendChild( button );
		} );

		// The four-item cap exists because a fifth clips its label at 320. The
		// same arithmetic warns when one long word will clip in a smaller row.
		var perItem = Math.floor( ( 320 - 28 - 12 - ( rows.length - 1 ) * 11 ) / rows.length );

		previewNote.textContent = mode !== 'icon' && longest * 7 > perItem
			? strings.previewTight
			: '';
	}

	if ( preview && form ) {
		form.addEventListener( 'input', refreshPreview );
		form.addEventListener( 'change', refreshPreview );

		Array.prototype.forEach.call( document.querySelectorAll( '.fbarui__preview-stages button' ), function ( button ) {
			button.addEventListener( 'click', function () {
				Array.prototype.forEach.call( button.parentNode.children, function ( other ) {
					other.classList.toggle( 'is-current', other === button );
				} );

				previewStage = button.getAttribute( 'data-stage' );
				previewScreen.setAttribute( 'data-stage', previewStage );
				refreshPreview();
			} );
		} );

		Array.prototype.forEach.call( document.querySelectorAll( '.fbarui__preview-widths button' ), function ( button ) {
			button.addEventListener( 'click', function () {
				Array.prototype.forEach.call( button.parentNode.children, function ( other ) {
					other.classList.toggle( 'is-current', other === button );
				} );

				previewScreen.style.width = button.getAttribute( 'data-width' ) + 'px';
				refreshPreview();
			} );
		} );

		window.fbarRefreshPreview = refreshPreview;
		refreshPreview();
	}

	updateState();
} )();
