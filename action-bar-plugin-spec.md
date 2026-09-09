# Build spec — a mobile action bar plugin for WordPress.org

A fixed bar across the bottom of the screen on phones and tablets, holding
whatever the site owner puts in it: a phone number, WhatsApp, an announcement,
a social profile, a link to anywhere. Every item is added, ordered and
configured from the admin — nothing is hard-coded.

This spec is written to be handed to an AI or a developer as the whole brief.
It has three parts: what WordPress.org will reject you for, how the thing is
built, and the design rules — which are measured numbers from a working
implementation, not opinions.

---

## Part 0 — Read this before you name it

These are rejection reasons, not style notes. Each one has sunk real submissions.

### The slug cannot contain someone else's trademark

You may **not** call it `whatsapp-bar`, `apple-style-bar`, `insta-bar`, or
anything beginning with a trademark you do not own. WhatsApp and Instagram are
Meta trademarks; Apple is Apple's. The Plugin Review team rejects on the slug
before reading a line of code, and the slug is permanent once approved.

- **Wrong:** `whatsapp-action-bar`, `apple-mobile-bar`
- **Right:** `tapbar-mobile-action-bar`, `handybar`, `thumbbar`

You *may* say "works with WhatsApp" in the description. You may not lead with
it, and you may not imply endorsement. Do not ship Apple's icons or claim an
"Apple design" — describe it as what it is: a bottom action bar.

Check the slug is free at `wordpress.org/plugins/<slug>/` before you write
anything. The slug is taken from the plugin **name** in your main file header
on first submission.

### Everything gets a prefix

Four characters minimum, unique to you, on **everything**: functions, classes,
constants, option names, hook names, global JS, CSS classes, transients, meta
keys, image sizes. A generic `render_bar()` or `.bar__btn` in the global
namespace is an automatic rejection.

Pick one prefix and never deviate. This spec uses `tbar` / `TBAR_` / `.tbar-`.

### The licence

GPLv2 or later, and everything you bundle must be compatible. State it in the
plugin header **and** in `readme.txt`. Any font, icon set or library you ship
needs a compatible licence and an attribution line.

---

## Part 1 — WordPress.org requirements

### The main plugin file header

```php
<?php
/**
 * Plugin Name:       TapBar — Mobile Action Bar
 * Plugin URI:        https://example.com/tapbar
 * Description:       A bottom bar on phones and tablets holding whatever you put in it — call, message, links, an announcement.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      7.4
 * Author:            Your Name
 * Author URI:        https://example.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       tapbar-mobile-action-bar
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
```

The **Text Domain must equal the slug**, exactly. `Version` must match
`Stable tag` in readme.txt or the wrong code ships.

### readme.txt

The format is strict and machine-parsed. Validate it at
`wordpress.org/plugins/developers/readme-validator/` before submitting.

```
=== TapBar — Mobile Action Bar ===
Contributors: yourwporgusername
Tags: mobile, call button, sticky bar, click to call, floating bar
Requires at least: 6.4
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A bottom bar on phones and tablets holding whatever you put in it.

== Description ==
...

== Installation ==
== Frequently Asked Questions ==
== Screenshots ==
== Changelog ==
== Upgrade Notice ==
```

Rules that catch people out:

- **Maximum 5 tags.** More are ignored and it looks like keyword stuffing.
- **Short description is the line under the header** — 150 characters, plain
  text, no markup.
- **`Stable tag` is the version that ships.** If it says `1.0.0`, the `/tags/1.0.0/`
  directory is served, not trunk. Getting this wrong ships the wrong code.
- `Tested up to` must be a real, current WordPress version.

### Security — the four rules with no exceptions

**1. Sanitise on the way in.** Every `$_POST`, `$_GET`, `$_REQUEST`, every value
read from anywhere you do not control.

```php
$label = isset( $_POST['tbar_label'] ) ? sanitize_text_field( wp_unslash( $_POST['tbar_label'] ) ) : '';
$url   = isset( $_POST['tbar_url'] ) ? esc_url_raw( wp_unslash( $_POST['tbar_url'] ) ) : '';
```

`wp_unslash()` before sanitising, always — WordPress adds slashes to superglobals.

**2. Escape on the way out.** Late, and matched to context.

| Context | Function |
|---|---|
| HTML text | `esc_html()` |
| Attribute | `esc_attr()` |
| URL in `href`/`src` | `esc_url()` |
| `tel:` / `mailto:` | `esc_attr()` (`esc_url` strips these unless whitelisted) |
| Inline JS value | `wp_json_encode()` |
| Translated string | `esc_html__()` / `esc_attr__()` |

There is no such thing as a value that is safe because you sanitised it
earlier. Escape at the point of output, every time.

**3. Nonce plus capability on every write.**

```php
if ( ! isset( $_POST['tbar_nonce'] ) ||
     ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['tbar_nonce'] ) ), 'tbar_save' ) ) {
	wp_die( esc_html__( 'That form has expired. Please try again.', 'tapbar-mobile-action-bar' ) );
}

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'You do not have permission to change these settings.', 'tapbar-mobile-action-bar' ) );
}
```

A nonce is not an authorisation check and a capability check is not a CSRF
check. You need both, in that order.

**4. Never trust a URL scheme.** An item's URL comes from an admin, but admins
get compromised. Whitelist the schemes you allow and reject the rest —
`javascript:` and `data:` in an `href` are stored XSS.

```php
$allowed = array( 'http', 'https', 'tel', 'mailto', 'sms', 'whatsapp' );
$url     = esc_url_raw( $raw, $allowed );
```

### Things that get you removed

- **Calling home.** No analytics, no version pings, no fetching anything from
  your server, unless the user has explicitly opted in *and* you say what is
  sent. Silent telemetry is the most common cause of removal.
- **Loading code remotely.** Everything executable ships in the plugin. No
  fetching JS from a CDN at runtime, no `eval`, no obfuscation, no
  `base64_decode` on anything that becomes code.
- **Minified files without source.** If you ship `tbar.min.js`, ship
  `tbar.js` too, or a build config that produces it. Plugin Check flags this.
- **Admin nagging.** No dashboard notices on pages that are not yours. No
  "upgrade to Pro" banners scattered through wp-admin. One dismissible notice
  on your own settings page is the ceiling.
- **Writing outside uploads.** Do not touch `wp-content` directly, do not write
  to the plugin directory, do not create files at all if you can avoid it.
- **Undeclared third-party services.** Any external domain the plugin contacts
  must be named in readme.txt with a link to its terms and privacy policy.

### Internationalisation

Load the text domain, translate every visible string, and never put a variable
inside a translation function.

```php
// WRONG — the string extractor cannot read this
__( $label, 'tapbar-mobile-action-bar' );

// RIGHT
sprintf(
	/* translators: %s is the item label. */
	esc_html__( 'Delete “%s”', 'tapbar-mobile-action-bar' ),
	esc_html( $label )
);
```

Ship a `/languages/` directory and a `.pot` file. Since WordPress 4.6 the
`load_plugin_textdomain()` call is optional for .org-hosted plugins, but
harmless and clearer for anyone reading.

### Uninstall

Remove what you created. An `uninstall.php` in the plugin root, which
WordPress runs on delete:

```php
<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'tbar_settings' );
delete_option( 'tbar_version' );

// Multisite: every site, not just the current one.
if ( is_multisite() ) {
	foreach ( get_sites( array( 'fields' => 'ids' ) ) as $site_id ) {
		switch_to_blog( $site_id );
		delete_option( 'tbar_settings' );
		delete_option( 'tbar_version' );
		restore_current_blog();
	}
}
```

Offer a "keep my settings when I delete this" checkbox and honour it. People
deactivate to test conflicts and are furious to lose their configuration.

### Plugin Check

Install the official **Plugin Check (PCP)** plugin and run it until it is
clean. It runs the same automated checks the review queue does. A submission
that fails it will be bounced with a form letter and you will wait weeks.

---

## Part 2 — Architecture

### File layout

Small files, one job each.

```
tapbar-mobile-action-bar/
├── tapbar-mobile-action-bar.php   bootstrap: header, constants, requires
├── uninstall.php
├── readme.txt
├── includes/
│   ├── class-tbar-settings.php    defaults, get, sanitise
│   ├── class-tbar-items.php       item types, their fields, validation
│   ├── class-tbar-render.php      the front-end markup
│   ├── class-tbar-visibility.php  should this item show on this request
│   └── class-tbar-assets.php      conditional enqueue
├── admin/
│   ├── class-tbar-admin.php       menu, page, save handler
│   ├── views/                     templates only, no logic
│   └── assets/                    admin css + js (repeater, drag order)
├── assets/
│   ├── css/tbar.css               source
│   ├── css/tbar.min.css           built
│   ├── js/tbar.js                 source
│   ├── js/tbar.min.js             built
│   └── icons/                     inline SVG sprite
└── languages/
```

Keep files under 400 lines. Classes do one thing. Rendering never queries;
querying never echoes.

### Storage — one option, one array

One row in `wp_options`, not thirty. Autoloaded, because the bar renders on
every front-end request and a second query per page load is a real cost.

```php
array(
	'enabled'     => true,
	'breakpoint'  => 1023,          // px; bar hides above this
	'position'    => 'bottom',
	'style'       => array(
		'background' => '#ffffff',
		'text'       => '#1c1c1e',
		'accent'     => '#0a84ff',
		'radius'     => 18,
		'blur'       => true,
		'labels'     => true,        // all items labelled, or none — see design rules
	),
	'announcement' => array(
		'enabled'    => false,
		'text'       => '',
		'url'        => '',
		'dismissible'=> true,
	),
	'items'       => array(
		array(
			'id'    => 'itm_a1b2c3',
			'type'  => 'call',
			'label' => 'Call',
			'value' => '+1 516 428 5020',
			'icon'  => 'phone',
			'show'  => array( 'devices' => 'phone_tablet', 'pages' => 'all' ),
		),
		// ...
	),
)
```

Every item carries a generated `id` so the admin can reorder and delete
without index collisions.

### Item types — this is the freedom

Each type declares what fields it needs and how it turns into a link. Adding a
type is adding one entry to a registry, not editing the renderer.

| Type | Field asked for | Renders as |
|---|---|---|
| `call` | Phone number | `tel:` — digits only, `+` kept |
| `whatsapp` | Number with country code | `https://wa.me/<digits>` |
| `sms` | Number, optional body | `sms:` |
| `email` | Address, optional subject | `mailto:` |
| `link` | Any URL | `<a href>`, optional new tab |
| `anchor` | CSS selector or `#id` | Smooth scroll on the current page |
| `page` | Page picker (dropdown) | Internal permalink, survives a slug change |
| `social` | Network + profile URL | `<a href>` with that network's icon |
| `directions` | Address or map URL | Map link, new tab |
| `text` | Words only | Non-interactive label |
| `share` | — | Web Share API, falls back to copy-link |
| `top` | — | Scroll to top |
| `cart` | — | WooCommerce cart with a live count *(only if Woo is active)* |
| `custom` | Label, URL, icon choice | Anything else |

**Registry shape:**

```php
function tbar_item_types() {
	return apply_filters( 'tbar_item_types', array(
		'call' => array(
			'label'  => __( 'Call', 'tapbar-mobile-action-bar' ),
			'icon'   => 'phone',
			'fields' => array( 'value' => array( 'type' => 'tel', 'label' => __( 'Phone number', 'tapbar-mobile-action-bar' ) ) ),
			'href'   => 'tbar_href_tel',
		),
		// ...
	) );
}
```

The filter is the extension point. Do not build a paid add-on system into the
free plugin; build the filter and let anyone use it.

### Visibility rules

Per item, evaluated server-side so nothing flashes then disappears:

- **Devices** — phone only / phone and tablet / everywhere
- **Pages** — everywhere, front page only, specific post types, specific IDs,
  or an exclude list
- **Users** — everyone / logged in / logged out
- **Schedule** — a start and end time, in the site's timezone. This is what
  makes "Call" show during opening hours and "WhatsApp" show outside them, and
  it is the feature people will actually pay attention to.

Use `wp_timezone()`, never `date_default_timezone_set()`.

### Conditional asset loading

Work out whether the bar will render **before** enqueuing. A plugin that adds
14KB of CSS to a page that shows nothing is the reason people disable plugins.

```php
add_action( 'wp_enqueue_scripts', function () {
	if ( ! tbar_will_render() ) {
		return;
	}
	wp_enqueue_style( 'tbar', TBAR_URL . 'assets/css/tbar.min.css', array(), TBAR_VERSION );
	wp_enqueue_script( 'tbar', TBAR_URL . 'assets/js/tbar.min.js', array(), TBAR_VERSION, true );
} );
```

No jQuery. The whole thing is well under 5KB of vanilla JS.

### Rendering

Hook `wp_footer` at a late priority. Output nothing at all when there are no
visible items — a bar drawing its frame around an empty list is worse than no
bar. Escape every value at the point of output.

---

## Part 3 — Design rules

These are measured from a working implementation. Where a number appears, it
came from a real measurement, not a guess.

### The geometry

| Property | Value | Why |
|---|---|---|
| Item minimum height | **52px** | Above the 44px floor with room for icon and label |
| Tap target minimum | **44 × 44** | The floor. Anything less is not reliably hittable |
| Icon | **18 × 18** | Reads at arm's length without crowding the label |
| Gap, icon to label | **3px** | |
| Label | **10px**, uppercase, `letter-spacing: .1em` | |
| Bar padding | **6px** inside, **10px** outside | |
| Corner radius | **18px** outer, **13px** on items | |
| Gap between items | **11px**, hairline centred in it | |
| Divider | **1px × 26px** | |

### Equal cells, or it looks broken

**Every item gets an equal share of the width.** Sizing items to their content
produced cells of 138, 138 and 44 pixels — the dividers landed in the wrong
places and the last item read as stranded against the edge rather than as the
third of three.

```css
.tbar__item { flex: 1 1 0; min-width: 0; }
```

Measured after the fix: 106px each at 375px viewport, 88px each at 320px,
glyph centres 118 and 117 apart.

### Label every item, or label none

An unlabelled icon centres itself in the row. A labelled one lifts its glyph to
make room for the word underneath. Mix them and the unlabelled mark sits
**10px lower** than its neighbours and the row looks wrong without it being
obvious why.

Make it one setting for the whole bar, not per item.

### Four items is the ceiling

At 320px the bar has 288px of usable width. Four labelled items is 72px each,
which fits `WHATSAPP` at 10px and nothing longer. Five does not.

Cap it in the admin, and say why in the hint rather than silently truncating.

### The background

```css
.tbar__inner {
	background: #fff;                                  /* fallback */
	border-radius: 18px;
	box-shadow: 0 4px 14px -8px rgba(0,0,0,.22),
	            0 22px 44px -28px rgba(0,0,0,.34);
}

@supports ((backdrop-filter: blur(1px)) or (-webkit-backdrop-filter: blur(1px))) {
	.tbar__inner {
		background: color-mix(in srgb, #fff 90%, transparent);
		-webkit-backdrop-filter: blur(22px) saturate(180%);
		backdrop-filter: blur(22px) saturate(180%);
	}
}
```

**90%, not 66%.** The bar sits over photographs more often than anything else
on a page, and at two-thirds opacity the labels swim. And put the translucency
*inside* the `@supports` block — without a blur behind it, a see-through bar
over a photo is the one case where it is genuinely unreadable.

### The home indicator

```css
padding-bottom: calc(10px + env(safe-area-inset-bottom, 0px));
```

Without this the bar sits under the iPhone home bar and the bottom row of
targets is unhittable.

### Give the page its space back

```css
body { padding-bottom: 84px; }
```

A fixed bar covers the last 84px of every page. The footer's final line, a
form's submit button, the last row of a table — all hidden, on every page,
until someone complains. Add the padding when the bar renders and only then.

### Getting out of the way

The bar should hide when it is redundant or in the way:

- **A menu or modal is open** — translate it out and drop `pointer-events`
- **The thing it points at is on screen** — if there is a "Call" item and the
  contact section with the same number is in the viewport, the bar is noise.
  Use `IntersectionObserver` against a selector the user can set.

```css
.tbar.is-away { transform: translateY(140%); opacity: 0; pointer-events: none; }
```

`140%`, not `100%` — the shadow extends past the box and 100% leaves a smudge
of it visible along the bottom edge.

### Buttons need three states

Rest, hover and active. The press-down is what makes it feel like a control
rather than a picture of one.

```css
.tbar__item        { box-shadow: 0 6px 14px -8px rgba(0,0,0,.45); }
.tbar__item:hover  { box-shadow: 0 12px 22px -10px rgba(0,0,0,.55); transform: translateY(-1px); }
.tbar__item:active { transform: none; box-shadow: 0 6px 14px -8px rgba(0,0,0,.45); }
```

Use a **tighter** shadow than you would on a card. A card-sized shadow under a
small control reads as floating rather than resting.

Every item hovers to the same colour. Items that fill on hover fill with the
accent; toggles take the accent on their border and text instead, because a
fill there reads as the selected state.

### Themeable without editing CSS

Expose everything as custom properties on the wrapper and let the admin colours
write them inline. Any theme can then override them, and you never ship a
`!important`.

```css
.tbar { --tbar-bg: #fff; --tbar-text: #1c1c1e; --tbar-accent: #0a84ff; --tbar-radius: 18px; }
```

### Dark mode

Three states, not two. An explicit choice stamps an attribute; the default
"system" setting stamps nothing and only `prefers-color-scheme` separates
light from dark. Define the light palette on the bare selector, redefine the
tokens under the media query, and again under the explicit dark attribute. A
colour whose only definition sits inside a media query never applies in the
un-stamped state.

### Reduced motion

```css
@media (prefers-reduced-motion: reduce) {
	.tbar, .tbar__item { transition-duration: .01ms !important; }
}
```

The states still apply — they just arrive at once, which is what someone who
asked for less motion wants.

### Accessibility

- `<nav aria-label="…">` around the bar, not a bare `<div>`
- Real `<a>` and `<button>` elements — never a `<div>` with a click handler
- An icon-only item needs `aria-label`; a labelled one does not (and must not
  have both, or a screen reader reads it twice)
- Decorative SVG gets `aria-hidden="true"` and `focusable="false"`
- Visible focus ring on every item, and never `outline: none` without a
  replacement
- A dismissible announcement needs a real close button with a label, and the
  dismissal remembered in `localStorage` wrapped in try/catch — it throws in
  private mode

---

## Part 4 — Faults to check for before you ship

Each of these shipped in a working implementation and had to be found by
measuring. They are cheap to check and expensive to discover in reviews.

**A ragged row.** Items sized to content instead of equal thirds. Measure the
left edge of each label and confirm they are identical.

**A stranded icon.** One unlabelled item among labelled ones sits 10px low.
Measure glyph `top` for every item; they must match.

**Clipped labels.** `WHATSAPP` at 10px needs 57px. Confirm
`span.scrollWidth <= span.clientWidth` for every item at 320px.

**Sideways scroll.** Check `document.documentElement.scrollWidth` against
`innerWidth` at 320, 375 and 768. A flex or grid child will not shrink below
its content unless you give it `min-width: 0`.

**The bar over its own dialog.** If the plugin opens anything modal, the bar's
`z-index` must be below it. Test by opening a modal and calling
`document.elementFromPoint()` on the bar's coordinates.

**Content hidden behind it.** Scroll to the very bottom of a long page and
confirm the last line is readable.

**A card inside a card.** If the bar is ever rendered inside another panel,
strip its background, border and shadow there. A shadow pooling against a
panel edge is the tell.

**Announcement dismissal that throws.** `localStorage` throws outright in some
contexts, not just returns empty. Wrap every read and write.

---

## Part 5 — Testing before submission

| Check | How |
|---|---|
| Plugin Check | Install PCP, run it, get it clean |
| Default themes | Twenty Twenty-Four, Twenty Twenty-Five — a plugin must not depend on a theme |
| Popular themes | Astra, Kadence, GeneratePress, Blocksy |
| PHP versions | 7.4 and 8.3 minimum |
| Multisite | Network activate, then per-site |
| WooCommerce | Active and inactive — the cart item must vanish cleanly, not fatal |
| Widths | 320, 375, 414, 768 |
| Themes | Light and dark, plus system-default |
| Screen reader | Tab through it; confirm each item announces once |
| No JS | Bar still renders and links still work |
| Uninstall | Delete the plugin, confirm no rows left in `wp_options` |

### Assets for the listing

| Asset | Size |
|---|---|
| Icon | 128×128 and 256×256 |
| Banner | 772×250 and 1544×500 |
| Screenshots | Numbered `screenshot-1.png`, described in readme.txt |

These live in an `/assets/` directory in SVN at the repository root — **not**
inside the plugin folder, or they ship to every user for nothing.

---

## Part 6 — Order of work

1. **Slug and licence.** Check availability, confirm no trademark, write the
   header and readme.txt skeleton. Do this first; everything else assumes it.
2. **Settings storage.** One option, defaults, sanitiser. Write the sanitiser
   test now: pass every default through it and assert nothing changes. That one
   test catches a whole class of bug where a default is silently rewritten.
3. **The item registry.** Types, their fields, their `href` builders.
4. **The admin screen.** Repeater with drag ordering, nonce, capability check.
5. **Visibility.** Device, page, user, schedule.
6. **Rendering.** Conditional enqueue, `wp_footer`, escape everything.
7. **The design pass.** Every number in Part 3, measured at 320/375/768.
8. **The fault list.** Every item in Part 4, checked by measuring.
9. **Plugin Check**, then the testing matrix in Part 5.
10. **Submit**, and expect to wait. Reviews take weeks and come back with a
    list. Fix everything they name, including the things you disagree with.

---

## Notes on what not to build

- **No page builder integration** in v1. It doubles the surface area and every
  builder breaks it differently.
- **No analytics**, not even "how many taps". It is the fastest route to
  removal, and it is not what people install this for.
- **No upsell in wp-admin.** One line on your own settings page is the limit.
- **No custom HTML field.** It looks like freedom and it is stored XSS with
  extra steps. Label, URL, icon covers what people actually need.
