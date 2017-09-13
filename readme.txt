
=== DTRT Gallery ===
Contributors: dotherightthingnz
Donate link: http://dotherightthing.co.nz
Tags: gallery
Requires at least: 4.8.1
Tested up to: 4.8.1
Stable tag: 0.3.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Gallery viewer which supports images, panoramas, maps, SoundCloud and Vimeo

== Description ==

Gallery viewer which supports images, panoramas, maps, SoundCloud and Vimeo

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/wpdtrt-gallery` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress

== Frequently Asked Questions ==

= How do I use the plugin? =

The plugin shortcode is automatically injected into the content and wraps all `<h2>` headings.

To manually use the shortcode:

```
<!-- in WYSIWYG -->
[wpdtrt-gallery-h2]<h2>Heading to wrap</h2>[/wpdtrt-gallery-h2]

// in PHP
$heading = 'Heading to wrap';
echo do_shortcode('[wpdtrt-gallery-h2]<h2>' . $heading . '</h2>[/wpdtrt-gallery-h2]');
```

Note:

* A WordPress gallery must immediately follow the `<h2>` heading.
* Gallery settings must be: *Link To: Media File*, *Columns: 3*, *Size: Thumbnail*

== Changelog ==

= 0.3 =
* Fixes to core functionality
* Improvements to display of fields in attachment modal

= 0.2 =
* Fixes to core functionality

= 0.1 =
* Initial version

== Upgrade Notice ==

= 0.1 =
* Initial release
