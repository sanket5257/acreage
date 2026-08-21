=== Acreage ===

Contributors: sanket
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
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

6. Make it yours: Appearance > Theme Options for the telephone number, email,
   disclaimer and page wording. Colours and fonts are in Elementor's Site
   Settings — see the FAQ below.

== Frequently Asked Questions ==

= Where do I change the phone number, email and homepage wording? =

Appearance > Theme Options. It has four tabs:

  Site details    telephone, fax, email and the footer disclaimer. These show
                  on every page, so this is the tab most people need.
  Homepage copy   the hero, the statistics band, the featured and province
                  bands, the about and sell-your-farm sections.
  Page headings   the masthead on Farms for Sale, Articles and Contact.
  Colours & fonts where to change them, which is Elementor Site Settings.

Clear any field and it falls back to the theme default, so there is no way to
end up with a blank header.

= I changed a homepage field and the page did not change. Why? =

Because that page is built in Elementor, and Elementor stores its content in the
page itself. The Homepage copy fields seed the page when the demo importer
builds it, and they drive the theme's own homepage when Elementor is not
running — they are not a live feed into a page that already exists.

Edit a built page the normal way: open it with Elementor and change the text
there. The Site details tab is different — the telephone number and email are
read fresh on every page load, so those take effect immediately.

= Where do I change the colours and fonts? =

Edit any page with Elementor, open the hamburger menu at the top left, then
Site Settings > Global Colors and Site Settings > Global Fonts.

The theme's palette and its two typefaces are mapped onto those, so one change
there updates every heading, button, border and link across the whole site,
including pages the demo built. Appearance > Theme Options > Colours & fonts
lists the palette and the CSS variable behind each colour, for child themes.

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

= 1.0.0 =
* Initial release.

== Copyright ==

Acreage bundles no third-party code.

Acreage is distributed under GPL-2.0-or-later. See LICENSE for the full text.

Demo photographs are supplied under separate licence and are listed individually
in assets/demo/licences.txt. They are provided for demonstration and may be
replaced.

Screenshot image: licensed for distribution with this theme.
