
=== DTRT Gallery ===
Contributors: dotherightthingnz
Donate link: http://dotherightthing.co.nz
Tags: gallery
Requires at least: 4.8.1
Tested up to: 4.8.1
Stable tag: 0.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Gallery which supports images, panoramas, maps, SoundCloud and Vimeo

== Description ==

Gallery which supports images, panoramas, maps, SoundCloud and Vimeo

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/wpdtrt-gallery` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Settings->Plugin Name screen to configure the plugin

== Frequently Asked Questions ==

= How do I use the widget? =

One or more widgets can be displayed within one or more sidebars:

1. Locate the widget: Appearance > Widgets > *DTRT Gallery Widget*
2. Drag and drop the widget into one of your sidebars
3. Add a *Title*
4. Specify *Number of blocks to display*
5. Toggle *Link to enlargement?*

= How do I use the shortcode? =

```
<!-- within the editor -->
[wpdtrt_gallery option="value"]

// in a PHP template, as a template tag
<?php echo do_shortcode( '[wpdtrt_gallery option="value"]' ); ?>
```

= Shortcode options =

1. `Number of blocks to display="4"` (default) - number of blocks to display
2. `enlargement="yes"` (default) - optionally link each block to a larger version

== Screenshots ==

1. The caption for ./assets/screenshot-1.(png|jpg|jpeg|gif)
2. The caption for ./assets/screenshot-2.(png|jpg|jpeg|gif)

== Changelog ==

= 0.1 =
* Initial version

== Upgrade Notice ==

= 0.1 =
* Initial release
