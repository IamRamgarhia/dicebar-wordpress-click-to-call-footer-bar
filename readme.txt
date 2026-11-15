=== DiceBar — Mobile Bottom Bar, Click to Call & Chat ===
Contributors: dicecodes
Tags: click to call, footer bar, mobile menu, bottom navigation, sticky bar
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.11.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Sticky footer bar for phones with a click to call button, chat button, directions and social icons. Works with any theme.

== Description ==

DiceBar adds a **mobile bottom bar** to your site, also called a sticky footer
bar, holding the buttons your visitors actually need: a **click to call button**,
a **WhatsApp chat button**, directions, email, social media icons, or a link to
anywhere on your site.

You can add a **call now button** without touching a theme file, and it works
with every theme and every page builder.

Most people arrive on a phone, where a number in the footer is four scrolls away
and a contact form is a wall. A bottom bar puts the action under the thumb on
every page.

= What you can put in it =

* **Click to call button** — taps straight into the dialler
* **WhatsApp button** — opens a chat, with a message already written if you want
* **Text message button** — SMS, with a prefilled body
* **Email button** — with a prefilled subject
* **Directions button** — opens the map app with your address
* **Social profile buttons** — thirty networks, in their own colours
* **Link, page, or scroll to a section**, **share**, and **back to top**

Up to **eight buttons**, in whatever order you want them. Drag a row to move it
or pick its number from the list beside it — the number works on a touch screen
and from the keyboard, where there is nothing to drag. Past four on a screen the
labels step aside so nothing is cut short.

= Nine starter kits =

Deciding what belongs in the bar is harder than configuring it, so there is a
one-click starting point for a restaurant, a clinic or salon, a trade call-out,
an online shop, a blog, a portfolio, and a row of social profiles. Everything
stays editable afterwards.

= Four looks, including a frosted glass dock =

Glass, the default, is a frosted panel floating clear of the screen edges, much
like a phone's own dock, with sliders for how see-through it is
and how strongly it blurs. Solid drops the translucency for busy photography,
Minimal removes the panel, and Bold fills the bar in a colour you choose.

Light and dark colour sets are set separately, so dark mode is what you decided
rather than an automatic inversion of your brand colour.

= See it before you save =

The settings screen shows a live preview of the real bar, drawn with the real
stylesheet, standing on a photograph, a light page or a dark page.

Switch it between **Phone, Tablet and Desktop** to see what each one gets: only
the buttons that screen shows, counted for you, with the labels dropped where
that screen has too many for them, exactly as the site will. Phone widths of
320, 375 and 414 are a click apart. It warns you when a label is too long for
the number of buttons.

= Works with any theme, and with page builders =

The bar needs no placement work: it appears by itself on every page you allow
it on, in any theme.

To also drop the same row of buttons inside your content, there is a **DiceBar
block** in the block editor: insert it and the real bar appears in the editor,
drawn by the same code that draws it on the site. It can show every button or
only the ones you name.

Everywhere else, the shortcode `[dicebar]` does the same job — in a widget, in a
theme template, and in Divi, Beaver Builder, Bricks and Oxygen. Elementor users
get a DiceBar widget in the panel.

= One bar, every screen =

Each button can be set to the screens it belongs on, so the bar grows with the
window instead of being a phone-only compromise: a phone gets call, WhatsApp and
directions, a desktop gets those plus your social icons. Past four buttons on a
screen the labels step aside and the icons carry it, so nothing is cut in half.

* Phones only, phones and tablets, tablets only, tablets and desktops, desktops
  only, or every screen — with the widths that divide them settable
* Everywhere, only on chosen pages, or everywhere except chosen pages
* Everyone, signed in visitors only, or signed out visitors only
* Per item as well as per bar, so one button can appear where another does not

= Built to behave =

* **Works with page caching.** Page-dependent rules are decided on the server,
  visitor-dependent ones in the browser, so a cached page is never wrong.
* **Loads nothing when it shows nothing.** No stylesheet, no script.
* **No tracking of any kind.** No analytics, no phoning home, no external
  requests. A tap fires an event your own tag manager can listen for.
* **Accessible.** Real links and buttons, a navigation landmark, visible focus,
  an accessible name on every icon-only button, and reduced motion respected.
* **Gets out of the way.** Optionally hides while scrolling down, steps aside
  for a section you name, leaves room for a cookie banner, and never prints.
* **Safe area aware**, so it sits above the home indicator instead of under it.

= Free, all of it =

Every feature here is in the free plugin. No locked tier, no dashboard adverts.

**Full documentation:** [dicecodes.com/mobile-bottom-bar-wordpress-plugin](https://dicecodes.com/mobile-bottom-bar-wordpress-plugin/)

Built and maintained by [Dice Codes](https://dicecodes.com/).

== External Services ==

DiceBar connects to nothing. It makes no request to any server, ours included:
no analytics, no fonts, no icon service, no update check of its own, no licence
call. The stylesheet, the script and every icon are files inside the plugin, so
the bar works on a site with no outbound internet access at all.

Two of the buttons you can add are links to someone else's site, and a link is
only followed when a visitor taps it:

* **WhatsApp chat** builds a `https://wa.me/` link from the number you enter,
  with your pre-written message in the query string if you set one. Tapping it
  opens WhatsApp, which is operated by Meta: [terms](https://www.whatsapp.com/legal/terms-of-service) and [privacy policy](https://www.whatsapp.com/legal/privacy-policy). Both pages are served to people but refuse automated readers with a 400, so a link checker reports a failure for pages that are genuinely there.
* **Directions** builds a `https://www.google.com/maps/search/` link from the
  address or place you enter. Tapping it opens Google Maps: [terms](https://policies.google.com/terms) and [privacy policy](https://policies.google.com/privacy).

Nothing is sent to either service until a visitor taps the button, and what is
sent is the address in the link — the number or place you typed into the
settings. No visitor data is added to it, and the plugin stores nothing about
who tapped what.

The **Call**, **SMS** and **Email** buttons use `tel:`, `sms:` and `mailto:`
links, which open an app on the visitor's own device and reach no third party.

== Installation ==

1. Install and activate the plugin.
2. Open **DiceBar** in your dashboard menu.
3. Pick a starter kit, or add items yourself.
4. Fill in your phone number and links, then save.
5. Look at your site on a phone.

== Frequently Asked Questions ==

= Does it work with my theme? =

Yes. The bar attaches to the footer hook, which every theme WordPress accepts
has to call. If something in your theme covers the bar, or the bar covers
something, change the stacking order on the Behaviour tab.

= Does it work with Elementor? =

Yes, in two ways. The bar itself appears on Elementor pages like any other,
with no setup. To place the same row of buttons inside a page, Elementor users
get a DiceBar widget in the panel, and everyone can use the
`[dicebar]` shortcode.

= Does it work with Divi, Beaver Builder, Bricks or Oxygen? =

Yes. All of them render shortcodes, so `[dicebar]` works in each.

= Does it work with a caching plugin? =

Yes, and this was designed for rather than bolted on. Rules that depend on which
page you are looking at run on the server and cache correctly. Rules that differ
between two visitors on the same page, such as whether they are signed in, run
in the browser instead, so a cached page is never wrong for the person reading
it.

= Will it slow my site down? =

It loads nothing on any page where it does not appear. Where it does appear, it
adds one small stylesheet and one small script, with no jQuery and no external
requests.

= Can I show the bar only on some pages? =

Yes. Choose everywhere, only on the pages you list, or everywhere except the
pages you list. Individual buttons can also have their own rules.

= Can I show more buttons on a desktop than on a phone? =

Yes, and that is the intended way to use a bar with a lot in it. Set each button
to the screens it belongs on: the first three to **Whatever the bar does**, a
fourth to **Tablets and desktops**, your social icons to **Desktops only**. A
phone then gets three buttons, a tablet four and a desktop six, from one bar.

Labels are judged per screen, so the phone keeps its words while the desktop
shows icons only — whichever screen has more than four buttons loses its labels,
and the others keep theirs. The preview's Phone, Tablet and Desktop buttons show
you each one before you save.

= Can I show only icons, without words? =

Yes. Icons and words, icons only, or words only, applied to the whole bar. It is
a whole-bar choice on purpose: an unlabelled icon centres itself while a
labelled one lifts to make room, so mixing them leaves one mark sitting low.

= Can I make an Apple-style glass dock at the bottom? =

Yes, that is the Glass look on the Design tab, and it is the default. It is a
frosted translucent panel with a blur behind it, floating clear of the screen
edges. Transparency and blur strength are both sliders, so you can go from
barely-there glass to nearly solid. The live preview stands it on a photograph,
which is where a translucent bar is hardest to read.

= Does it track my visitors? =

No. Nothing is collected and nothing is sent anywhere. A tap fires an event in
the page that your own analytics can listen for if you want it to, and that is
entirely your own code's business.

== Screenshots ==

1. The bar on a phone, with call, WhatsApp and directions.
2. The settings screen, with the live preview.
3. Four looks, including the frosted glass bar.
4. Starter kits for common kinds of business.
5. Choosing where the bar appears.

== Changelog ==

= 1.11.0 =
* Items can now be set to show on tablets and desktops, not only on phones. The old choices all narrowed downwards — phones, phones and tablets, everywhere — so there was no way to say "and one more once there is room". There are three new ones: Tablets only, Tablets and desktops, Desktops only.
* That means one bar can carry three buttons on a phone, four on a tablet and six on a desktop, instead of needing a second bar or a shortcode per template.
* Labels are now judged per screen rather than on the total. A bar with three phone buttons and six desktop ones keeps its labels on the phone, where they fit, and shows icons only on the desktop, where they do not. Items whose label is hidden at some widths carry an aria-label, so they never lose their name to a screen reader.
* The preview has Phone, Tablet and Desktop buttons. It shows the items that screen actually gets, applies the same label rule, says how many buttons are on it, and scales a desktop preview down to fit rather than pretending a desktop bar is phone-width.
* The bar as a whole can also be set to tablets or desktops only.

= 1.10.0 =
* Up to eight items, not four. Past four the bar shows icons only, so no label is cut short — which was the whole reason for the old limit. Enough for a row of social icons.
* Every item now carries a position number you can pick from a list, as well as the drag handle. Dragging needs a mouse; the number works on a touch screen, from the keyboard, and with JavaScript switched off.
* A DiceBar block for the block editor. It draws the real bar in the editor through the same code that renders it on the site, so what you see while editing is the bar itself. The shortcode and the Elementor widget are unchanged.
* The items screen now says why nothing shows on the site yet, and points at the starter kits above it.
* Added an External Services section to this readme. The plugin still connects to nothing — the section says so, and explains that the WhatsApp and Directions buttons are links a visitor taps rather than requests the plugin makes.

= 1.9.1 =
* The plugins screen now links to the plugin's directory page, support forum and reviews.
* Description tidied.

= 1.9.0 =
* The description explains the Glass look in more detail.
* Two FAQ entries answering how to get a frosted glass dock at the bottom.
* Tags adjusted.

= 1.8.0 =
* Removed the custom CSS field. Every value it reached is a named control on the Design tab, and a theme can still override any of them through the custom properties on the bar's wrapper.
* Reworded the description and corrected the menu name in the install steps.

= 1.7.0 =
* The colour scheme now defaults to light rather than following the visitor's device. Most themes are light only, so a visitor with dark mode on was getting a dark bar on a light page. Following the device is still an option for themes that have their own dark mode.
* Fixed: the documentation page was being included inside the plugin archive.
* The translation template now carries all 229 strings; it was previously an empty stub.

= 1.6.0 =
* Renamed to DiceBar.
* Social icons can now use each network's own colour.
* Both light and dark colour sets have their own controls.
* Bold has its own fill colour instead of borrowing the standout item's.
* Fixed: changing an item's icon did not change the preview.
* Fixed: the preview's Photo, Light and Dark buttons rendered as bare text.
* Minimum WordPress lowered to 6.0.

= 1.4.0 =
* The icon field is a grid of real icons instead of a list of names.
* The preview offers light and dark grounds as well as a photograph.
* Fixed: every look previewed identically.
* Fixed: choosing a social profile left the previous type's icon selected.
* Fixed: the settings screen shared a class name with the bar itself.

= 1.3.0 =
* A live preview beside the settings.
* Transparency and blur strength are sliders.

= 1.2.0 =
* Social profile items with thirty brand marks.
* Four ready-made looks.
* Icon only, word only, or both.
* Nine starter kits.

= 1.1.0 =
* Its own top-level menu with tabbed sections.
* Fixed: with no items saved, the Add button could not create the first one.

= 1.0.0 =
* First release.

== Upgrade Notice ==

= 1.6.0 =
Renamed to DiceBar, with brand-coloured social icons and separate dark colours.
