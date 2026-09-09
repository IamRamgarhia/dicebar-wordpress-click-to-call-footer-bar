"""Replace the icon dropdown with a picker that shows icons.

Also makes the icon follow the item's type. Choosing "Social profile" while a
telephone stayed selected is the report that prompted this: the icon field
never reset, so the previous type's glyph persisted and the network control
below it was ignored.
"""

import io


def patch(path, pairs):
    text = io.open(path, encoding="utf-8").read()
    for old, new in pairs:
        if old not in text:
            raise SystemExit("Not found in %s:\n%s" % (path, old[:200]))
        text = text.replace(old, new, 1)
    io.open(path, "w", encoding="utf-8", newline="\n").write(text)
    print("patched", path)


# --- The view renders a grid of glyphs instead of a select -----------------

patch(
    "admin/views/settings-page.php",
    [
        (
            '\t\t\t\t\t\t\t\t\t<label class="fbarui__field">\n'
            "\t\t\t\t\t\t\t\t\t\t<span><?php esc_html_e( 'Icon', 'footer-bar-mobile-action-bar' ); ?></span>\n"
            "\t\t\t\t\t\t\t\t\t\t<?php FBar_Admin::select( 'fbar[items][' . $fbar_index . '][icon]', $fbar_icon_choices, $fbar_item['icon'] ); ?>\n"
            "\t\t\t\t\t\t\t\t\t</label>",
            '\t\t\t\t\t\t\t\t\t<div class="fbarui__field fbarui__field--icons" data-role="iconfield">\n'
            "\t\t\t\t\t\t\t\t\t\t<span><?php esc_html_e( 'Icon', 'footer-bar-mobile-action-bar' ); ?></span>\n"
            '\t\t\t\t\t\t\t\t\t\t<div class="fbarui__iconpicker" role="radiogroup" aria-label="<?php esc_attr_e( \'Icon\', \'footer-bar-mobile-action-bar\' ); ?>">\n'
            "\t\t\t\t\t\t\t\t\t\t\t<?php foreach ( $icons as $fbar_choice ) : ?>\n"
            '\t\t\t\t\t\t\t\t\t\t\t\t<label class="fbarui__iconopt" title="<?php echo esc_attr( $fbar_choice ); ?>">\n'
            '\t\t\t\t\t\t\t\t\t\t\t\t\t<input type="radio" name="fbar[items][<?php echo esc_attr( $fbar_index ); ?>][icon]" value="<?php echo esc_attr( $fbar_choice ); ?>" <?php checked( $fbar_choice, $fbar_item[\'icon\'] ); ?>>\n'
            '\t\t\t\t\t\t\t\t\t\t\t\t\t<svg class="fbarui__iconglyph" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><use href="#fbar-i-<?php echo esc_attr( $fbar_choice ); ?>"></use></svg>\n'
            '\t\t\t\t\t\t\t\t\t\t\t\t\t<span class="screen-reader-text"><?php echo esc_html( $fbar_choice ); ?></span>\n'
            "\t\t\t\t\t\t\t\t\t\t\t\t</label>\n"
            "\t\t\t\t\t\t\t\t\t\t\t<?php endforeach; ?>\n"
            "\t\t\t\t\t\t\t\t\t\t</div>\n"
            "\t\t\t\t\t\t\t\t\t</div>",
        )
    ],
)

# --- The script builds the same picker, and resets it on a type change -----

patch(
    "admin/assets/admin.js",
    [
        (
            "\t/**\n"
            "\t * Icon names mapped to themselves, with a default entry first.\n"
            "\t *\n"
            "\t * @return {Object} Choices for an icon select.\n"
            "\t */\n"
            "\tfunction iconChoices() {\n"
            "\t\tvar choices = { '': strings.defaultIcon };\n"
            "\n"
            "\t\tconfig.icons.forEach( function ( name ) {\n"
            "\t\t\tchoices[ name ] = name;\n"
            "\t\t} );\n"
            "\n"
            "\t\treturn choices;\n"
            "\t}",
            "\t/**\n"
            "\t * A grid of icons to choose from, drawn from the page's sprite.\n"
            "\t *\n"
            "\t * @param {string} name    Field name.\n"
            "\t * @param {string} current Selected icon.\n"
            "\t * @return {Element} The picker.\n"
            "\t */\n"
            "\tfunction iconPicker( name, current ) {\n"
            "\t\tvar grid = make( 'div', { class: 'fbarui__iconpicker', role: 'radiogroup' } );\n"
            "\n"
            "\t\tconfig.icons.forEach( function ( icon ) {\n"
            "\t\t\tvar input = make( 'input', { type: 'radio', name: name, value: icon } );\n"
            "\n"
            "\t\t\tif ( icon === current ) {\n"
            "\t\t\t\tinput.checked = true;\n"
            "\t\t\t}\n"
            "\n"
            "\t\t\tvar svg = document.createElementNS( 'http://www.w3.org/2000/svg', 'svg' );\n"
            "\t\t\tvar use = document.createElementNS( 'http://www.w3.org/2000/svg', 'use' );\n"
            "\n"
            "\t\t\tsvg.setAttribute( 'class', 'fbarui__iconglyph' );\n"
            "\t\t\tsvg.setAttribute( 'viewBox', '0 0 24 24' );\n"
            "\t\t\tsvg.setAttribute( 'aria-hidden', 'true' );\n"
            "\t\t\tuse.setAttribute( 'href', '#fbar-i-' + icon );\n"
            "\t\t\tsvg.appendChild( use );\n"
            "\n"
            "\t\t\tgrid.appendChild(\n"
            "\t\t\t\tmake( 'label', { class: 'fbarui__iconopt', title: icon }, [\n"
            "\t\t\t\t\tinput,\n"
            "\t\t\t\t\tsvg,\n"
            "\t\t\t\t\tmake( 'span', { class: 'screen-reader-text', text: icon } ),\n"
            "\t\t\t\t] )\n"
            "\t\t\t);\n"
            "\t\t} );\n"
            "\n"
            "\t\treturn grid;\n"
            "\t}",
        ),
        (
            "\t\t\tfield( strings.icon, select( base + '[icon]', iconChoices(), '' ) ),",
            "\t\t\tmake( 'div', { class: 'fbarui__field fbarui__field--icons', 'data-role': 'iconfield' }, [\n"
            "\t\t\t\tmake( 'span', { text: strings.icon } ),\n"
            "\t\t\t\ticonPicker( base + '[icon]', type.icon ),\n"
            "\t\t\t] ),",
        ),
        # Selecting a type now moves the icon with it, and hides the picker for
        # a social item whose network already decides the glyph.
        (
            "\t\t\t\tbuildExtra( row, type, index );\n"
            "\t\t\t}\n"
            "\t\t}\n"
            "\n"
            "\t\trefreshTitle( row );",
            "\t\t\t\tbuildExtra( row, type, index );\n"
            "\t\t\t\tselectIcon( row, type.icon );\n"
            "\t\t\t\tsyncIconField( row, type );\n"
            "\t\t\t}\n"
            "\t\t}\n"
            "\n"
            "\t\t// A social item's glyph is its network, so choosing one has to\n"
            "\t\t// move the icon too or the two controls disagree.\n"
            "\t\tif ( event.target.name && event.target.name.indexOf( '[extra][network]' ) !== -1 ) {\n"
            "\t\t\tselectIcon( row, event.target.value );\n"
            "\t\t}\n"
            "\n"
            "\t\trefreshTitle( row );",
        ),
        (
            "\t/**\n"
            "\t * Renumber every field so the indexes stay contiguous.",
            "\t/**\n"
            "\t * Choose an icon in a row's picker.\n"
            "\t *\n"
            "\t * @param {Element} row  The item row.\n"
            "\t * @param {string}  icon Icon name.\n"
            "\t */\n"
            "\tfunction selectIcon( row, icon ) {\n"
            "\t\tvar option = row.querySelector( '[name$=\"[icon]\"][value=\"' + icon + '\"]' );\n"
            "\n"
            "\t\tif ( option ) {\n"
            "\t\t\toption.checked = true;\n"
            "\t\t}\n"
            "\t}\n"
            "\n"
            "\t/**\n"
            "\t * Hide the icon picker for types that decide their own glyph.\n"
            "\t *\n"
            "\t * @param {Element} row  The item row.\n"
            "\t * @param {Object}  type The item type.\n"
            "\t */\n"
            "\tfunction syncIconField( row, type ) {\n"
            "\t\tvar picker = row.querySelector( '[data-role=\"iconfield\"]' );\n"
            "\n"
            "\t\tif ( picker ) {\n"
            "\t\t\tpicker.hidden = type.slug === 'social';\n"
            "\t\t}\n"
            "\t}\n"
            "\n"
            "\t/**\n"
            "\t * Renumber every field so the indexes stay contiguous.",
        ),
        # Rows rendered by PHP need the same treatment on load.
        (
            "\tArray.prototype.forEach.call( list.querySelectorAll( '.fbarui__item' ), makeDraggable );",
            "\tArray.prototype.forEach.call( list.querySelectorAll( '.fbarui__item' ), function ( row ) {\n"
            "\t\tmakeDraggable( row );\n"
            "\n"
            "\t\tvar typeControl = row.querySelector( '[name$=\"[type]\"]' );\n"
            "\t\tvar type = typeControl ? typeBySlug( typeControl.value ) : null;\n"
            "\n"
            "\t\tif ( type ) {\n"
            "\t\t\tsyncIconField( row, type );\n"
            "\t\t}\n"
            "\t} );",
        ),
    ],
)

# The picker replaces the select, so its "default for this type" entry is gone.
patch(
    "admin/views/settings-page.php",
    [
        (
            "$fbar_icon_choices = array( '' => __( 'Default for this type', 'footer-bar-mobile-action-bar' ) );\n"
            "\n"
            "foreach ( $icons as $fbar_icon ) {\n"
            "\t$fbar_icon_choices[ $fbar_icon ] = $fbar_icon;\n"
            "}\n",
            "",
        )
    ],
)

print("done")
