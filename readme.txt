=== DiceBar — Mobile Bottom Bar, Click to Call & Chat ===
Contributors: dicecodes
Tags: click to call, footer bar, mobile menu, bottom navigation, sticky bar
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.9.1
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

Most people arrive on a phone. On a phone, a phone number in the footer is four
scrolls away, and a contact form is a wall. A bottom bar puts the action under
the thumb on every page, which is the whole idea.

= What you can put in it =

* **Click to call button** — taps straight into the dialler
* **WhatsApp button** — opens a chat, with a message already written if you want
* **Text message button** — SMS, with a prefilled body
* **Email button** — with a prefilled subject
* **Directions button** — opens the map app with your address
* **Social profile buttons** — thirty networks, drawn in their own colours
* **Link, page, or scroll to a section**
* **Share and back to top**

Four buttons is the most that fits on a narrow phone without labels being cut
short, so the plugin caps it there and tells you why rather than letting you
find out on a visitor's screen.

= Nine starter kits =

Deciding what belongs in the bar is harder than configuring it, so there is a
one-click starting point for a restaurant, a clinic or salon, a trade call-out,
an online shop, a blog, a portfolio, and a row of social profiles. Everything
stays editable afterwards.

= Four looks, including a frosted glass dock =

Glass, the default, is a frosted panel floating clear of the screen edges, much
like the dock at the bottom of a phone. Sliders set how see-through it is and
how strongly it blurs what sits behind it. Solid drops the translucency for busy
photography. Minimal removes the panel entirely. Bold fills the bar in a colour
you choose.

Light and dark colour sets are set separately, so dark mode is what you decided
rather than an automatic inversion of your brand colour.

= See it before you save =

The settings screen shows a live preview of the real bar, drawn with the real
stylesheet, standing on a photograph, a light page or a dark page, at 320, 375
and 414 pixels wide. It warns you when a label is too long for the number of
buttons.

= Works with any theme, and with page builders =

The bar needs no placement work: it appears by itself on every page you allow
it on, in any theme.

To also drop the same row of buttons inside your content, use the shortcode
`[dicebar]`. It works in the block editor, in a widget, in a theme
template, and in Elementor, Divi, Beaver Builder, Bricks and Oxygen. Elementor
users also get a DiceBar widget in the panel.

= Choose exactly where it appears =

* Phones only, phones and tablets, or every screen, with the widths settable
* Everywhere, only on chosen pages, or everywhere except chosen pages
* Everyone, signed in visitors only, or signed out visitors only
* Per item as well as per bar, so one button can appear where another does not

= Built to behave =

* **Works with page caching.** Rules that depend on the page are worked out on
  the server; rules that change between two visitors on the same page are worked
  out in the browser. A cached page is never wrong.
* **Loads nothing when it shows nothing.** No stylesheet, no script.
* **No tracking of any kind.** No analytics, no phoning home, no external
  requests. A tap fires an event your own tag manager can listen for, and
  nothing leaves the visitor's browser because of us.
* **Accessible.** Real links and buttons, a navigation landmark, visible focus,
  an accessible name on every icon-only button, and reduced motion respected.
* **Gets out of the way.** Optionally hides while scrolling down, steps aside
  when a section you name is on screen, leaves room for a cookie banner, and
  never prints.
* **Safe area aware**, so it sits above the home indicator instead of under it.

= Free, all of it =

Every feature described here is in the free plugin. There is no locked tier and
no adverts in your dashboard.

**Full documentation:** [dicecodes.com/mobile-bottom-bar-wordpress-plugin](https://dicecodes.com/mobile-bottom-bar-wordpress-plugin/)

Built and maintained by [Dice Codes](https://dicecodes.com/).

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
