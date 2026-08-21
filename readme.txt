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

== Frequently Asked Questions ==

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
