=== Acreage ===

Contributors: sanket
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.1
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Tags: custom-menu, featured-images, translation-ready, right-sidebar, block-styles, wide-blocks, rtl-language-support, threaded-comments, accessibility-ready

An Elementor-first theme for land, farm and rural property listings.

== Description ==

Acreage is a WordPress theme for agencies and brokers selling land rather than
houses: farms, smallholdings, hunting and grazing land, and rural estates.

Listings are not stored in the theme. They live in the Acreage Core companion
plugin, which means switching theme later leaves every property, photograph and
filter term exactly where it was. A theme that owns your data is a theme you can
never leave; this one is deliberately not that.

The theme is built around Elementor and works completely on the free version.
Elementor Pro is supported and adds Theme Builder locations, but it is never
required.

== Installation ==

Install in this order. The demo import needs Acreage Core and Elementor already
running, because it creates the sample farms and builds the pages with them.

1. Appearance > Themes > Add New > Upload Theme, choose acreage.zip, Install,
   Activate.
2. Plugins > Add New > Upload Plugin, choose acreage-core.zip, Install,
   Activate. This plugin owns the Farms post type — without it there are no
   listings and the demo import will refuse to run.
3. Install Elementor from Plugins > Add New and activate it. The free version is
   enough.
4. Optional but recommended: upload acreage-child.zip the same way as step 1 and
   activate it instead of the parent, so your changes survive updates.
5. Import the demo: Appearance > Demo Content, then "Import demo content". It
   creates the pages, the menus, the sample articles and twelve sample farms
   with their photographs, prices, extents, provinces and species, and sets the
   homepage. Allow up to a minute — it copies around sixty images into the media
   library.

The same screen has a "Remove demo content" button that deletes everything the
import created, and nothing else.

6. Make it yours: Appearance > Theme Options — colours, typefaces, buttons,
   layout and your contact details. Page wording is edited on the page itself
   in Elementor.

== Frequently Asked Questions ==

= What can I change in Appearance > Theme Options? =

Five tabs:

  Colours      all nine palette colours, each a colour picker. The whole
               stylesheet is written against them, so one change reaches every
               heading, button, border, link and card on the site.
  Typography   heading and body typeface from sixteen faces, body text size,
               line height and a heading scale.
  Buttons      solid or outline, background, text and hover colours, corner
               radius, padding, uppercase or as-typed, and letter spacing.
  Layout       content width, farms per row, farm photograph shape and corner
               radius.
  Site details telephone, fax, email and the footer disclaimer.

There is a "Reset all settings" button at the foot of every tab. It puts the
controls back to the design default and does not touch your content, pages or
farms.

Every control ships at the design default, and a site that changes none of them
renders byte-for-byte identically to one without the screen.

= Do the Google fonts slow the site down? =

Only if you choose one. The two default faces — Georgia for headings, Helvetica
for body — are already on every machine and load instantly, so a default install
makes no third-party font request at all. Pick one of the fourteen Google
families and a single stylesheet is requested for both faces together, with
display=swap so text stays visible while it loads.

= I set a colour in Elementor and one in Theme Options. Which wins? =

Theme Options. Its CSS is added after Elementor's, deliberately, because it is
the screen you will look for first and a control that silently loses to
something else is worse than no control.

Elementor's Site Settings still drive Elementor's own widgets, so both remain
usable — just pick one place to set a given colour and stay there.

= Why is there no field for the homepage wording? =

Because it would not work. Pages are built in Elementor and Elementor stores
each page's content in the page itself, so a "Hero heading" field in a settings
screen could not change a page that had already been built — it would sit there
looking editable and do nothing.

Page wording is edited where the page is: open it with Elementor and change the
text. Theme Options is for the things that apply across the whole site.

Site details are the exception. The telephone number, email and disclaimer are
printed by the theme's own header and footer rather than by Elementor, and are
read fresh on every page load, so those take effect immediately.

= Do I need Elementor Pro? =

No. Every layout in the demo is built with Elementor Free and the widgets that
ship in Acreage Core. If you do have Pro, the theme registers Theme Builder
locations so your Pro headers, footers and archive templates are used instead.

= What happens if I deactivate Elementor? =

The site keeps working. The theme falls back to its own header, footer and
templates. Pages built in Elementor will show their plain content until you
reactivate it.

= Will I lose my listings if I change theme? =

No. They belong to the Acreage Core plugin, not the theme.

= Where do I see the enquiries people have sent? =

Farms > Enquiries. Every enquiry sent through the built-in form is saved there
as well as emailed, with the sender's name, email, telephone, message and the
farm they were asking about. Unread ones show a count beside the menu item, and
there is an "Export all as CSV" button for a mail-merge or a CRM import.

The reason it saves as well as sends is that email is not reliable. A host with
no SMTP configured, a missing SPF record, a spam filter or a shared inbox nobody
watches will all lose an enquiry silently, and on a listings site that is the
whole return on the advert that produced it. Any enquiry whose notification
email failed is marked "Email failed — reply by hand" in the list, so you find
out rather than never knowing.

Enquiries are stored privately: no front-end URL, excluded from search, and not
exposed over the REST API.

= Do enquiries from Contact Form 7 appear there too? =

No. A form plugin keeps its own records — Contact Form 7 stores nothing by
default, and its companion plugin Flamingo is what saves submissions there.
Farms > Enquiries lists what the theme's own built-in form receives.

= Can I use Contact Form 7 instead of the built-in form? =

Yes, and any other form plugin too. Edit the page in Elementor, select the
Enquiry Form widget, and pick your form from the Form dropdown. Contact Form 7
forms are listed there by name. For WPForms, Gravity Forms, Fluent Forms,
Forminator or anything else, choose "Another form plugin" and paste that
plugin's shortcode.

The built-in form is the default so a fresh install can be contacted before any
plugin is installed. Nothing is lost by switching: if the form plugin is later
deactivated, the widget quietly serves the built-in form again rather than
leaving the page with no way to make contact.

Whichever plugin you use, its fields are restyled to match the theme.

= Will the enquiry say which farm it is about? =

Yes. On a farm page the theme hands the farm to your form automatically.

In Contact Form 7 the farm arrives as three mail tags you can use anywhere in
the Mail tab:

  [acreage-farm]      the farm's name
  [acreage-farm-id]   its numeric ID
  [acreage-farm-url]  a link straight to the listing

A useful subject line is: Enquiry - [acreage-farm]

For other form plugins, put [acreage_farm_name] or [acreage_farm_url] in a
field's default value. Both are empty away from a farm page, so a general
contact form stays clean.

= Can I edit the theme's files? =

You can, but use the child theme instead — a parent update overwrites direct
edits. See documentation/child-themes.

== Changelog ==

= 1.0.1 =
* Footer social profile links, set under Appearance > Theme Options.
* Layout: consistent gutters between paired columns, a readable measure on
  headings, and tightened archive spacing.
* No action needed on existing sites.

= 1.0.0 =
* Initial release.

== Copyright ==

Acreage bundles no third-party code.

Acreage is distributed under GPL-2.0-or-later. See LICENSE for the full text.

Demo photographs are supplied under separate licence and are listed individually
in assets/demo/licences.txt. They are provided for demonstration and may be
replaced.

Screenshot image: licensed for distribution with this theme.
