
=== DTRT Gallery ===
Contributors: dotherightthingnz
Donate link: http://dotherightthing.co.nz
Tags: gallery, video, audio, map
Requires at least: 4.9.5
Tested up to: 4.9.5
Requires PHP: 7.2.20
Stable tag: 1.8.8
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Gallery viewer which supports images, panoramas, maps, SoundCloud and Vimeo.

== Description ==

Gallery viewer which supports images, panoramas, maps, SoundCloud and Vimeo.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/wpdtrt-gallery` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Settings->DTRT Gallery screen to configure the plugin

== Frequently Asked Questions ==

The plugin shortcode is automatically injected into the content and wraps all `<h2>` headings.

To manually use the shortcode:

```
<!-- in WYSIWYG -->
[wpdtrt_gallery_shortcode_heading]<h2>Heading to wrap</h2>[/wpdtrt_gallery_shortcode_heading]

// in PHP
$heading = 'Heading to wrap';
echo do_shortcode('[wpdtrt_gallery_shortcode_heading]<h2>' . $heading . '</h2>[/wpdtrt_gallery_shortcode_heading]');
```

= Caveats =

* A WordPress gallery must immediately follow the `<h2>` heading

== Changelog ==

= 1.8.8 =
* Update wpdtrt-plugin-boilerplate, from 1.6.17 to 1.6.19

= 1.8.7 =
* Accessibility fixes (SortSite):
  * Inject gallery viewer image, to fix invalid markup prior to lazy loading (#62)
  * Apply width, height and allowfullscreen to iframe using JavaScript (#61)
  * Remove iframe border & hide overflow using CSS rather than HTML (#61)
  * Add dynamic title to gallery viewer iframe, remove JS populated src (#64)
  * Remove JS populated id from viewer (#60)
  * Hide items accessibly
  * Replace `data-viewing` with `aria-expanded`
* Boilerplate / generator migration:
  * Update wpdtrt-plugin-boilerplate from 1.5.3 to 1.6.17
  * Sync with generator-wpdtrt-plugin-boilerplate 0.8.12 (migration)
  * Remove boilerplate code and file
  * Add missing attachment fields
  * Add missing icon, image, content template files
  * Add fallback array if no options sent to output template
  * Fix type declaration for integer
  * Fix type declaration for post object
  * Use correct variable types (fixes #77)
* CI:
  * Tell Travis to start MySQL as this no longer happens automatically
  * Move database credentials into Travis settings
  * Match PHP version to version used by Sitehost container (`7.2.20`)
* Dependencies:
  * Fix casing of Composer dependency
  * Fix name of `extra` key in `composer.json`
  * Update Composer dependencies
  * Update Yarn dependencies
* Documentation:
  * Migrate PHPDoc to Natural Docs
  * Exclude PHPDoc sniffs
  * Don't install Mono on CI on dev branch (https://github.com/dotherightthing/wpdtrt-plugin-boilerplate/issues/173)
* Linting:
  * Configure ESLint, suppress ESLint error jQuery noConflict iife
  * Remove unsupported CSS property
  * When validating an HTML fragment via Tenon, pass the relevant option (does not resolve #56)
  * Clean up `tenonAnalyzeHtml` output
* Misc:
  * Fix removeAttr typo
  * Fix gallery viewer selector
  * Show pointer cursor over gallery expand button
  * Optimise `attr` and `removeAttr` for jQuery 1.7+
  * Remove redundant attributes
  * Only update the viewer if a different thumbnail was selected
* SEO:
  * Only track clicks when these were initiated by the user, not by the setup script
* Unit testing:
  * Add Cypress, add UI tests & several Gulp unit tests
  * Increase Cypress' allowed response time, to allow loading of local WordPress pages via MAMP Pro (https://github.com/dotherightthing/generator-wpdtrt-plugin-boilerplate/issues/92)
  * PHPUnit - update expected image dimensions, as images are slightly narrower on live, for some reason
  * PHPUnit - Fix name of thumbnail size
  * Add PHPUnit XML config file for generated plugin, as only the boilerplate tests were being run
  * PHPUnit - Thumbnail links now store options as data attributes rather than URL params
  * PHPUnit - Update expected gallery markup
  * PHPUnit - Disable test without assertions
  * PHPUnit - Format fixtures, move bulky fixture out of test, rename fixtures for readability
  * PHPUnit - Ignore test images
  * Cypress - Reinstate failing test (see #67)

= 1.8.6 =
* Travis: Tell Travis to start MySQL as this no longer happens automatically
* Composer: Fix casing of Composer dependency
* Linting: Configure ESLint, suppress ESLint error jQuery noConflict iife
* Travis: Move database credentials into Travis settings
* Docs: Migrate PHPDoc to Natural Docs
* Composer: Fix name of `extra` key in `composer.json`
* Boilerplate: Update wpdtrt-plugin-boilerplate from 1.5.3 to 1.6.12
* Boilerplate: Sync with generator-wpdtrt-plugin-boilerplate 0.8.12 (migration)
* Composer: Fix name of `extra` key in `composer.json`
* Composer/Yarn: Update dependencies
* Travis: Match PHP version to version used by Sitehost container (`7.2.20`)
* Boilerplate migration: Remove boilerplate code and file
* Boilerplate migration: Add missing attachment fields
* Boilerplate migration: Add missing icon, image, content template files
* Fix removeAttr typo
* Fix gallery viewer selector
* Linting: Remove unsupported CSS property
* Show pointer cursor over gallery expand button
* SortSite: Inject gallery viewer image, to fix invalid markup prior to lazy loading (#62)
* SortSite: Apply width, height and allowfullscreen to iframe using JavaScript (#61)
* SortSite: Remove iframe border & hide overflow using CSS rather than HTML (#61)
* SortSite: Add dynamic title to gallery viewer iframe, remove JS populated src (#64)
* SortSite: Remove JS populated id from viewer (#60)
* GTM: Only track clicks when these were initiated by the user, not by the setup script.
* Optimise `attr` and `removeAttr` for jQuery 1.7+
* Tenon: When validating an HTML fragment, pass the relevant option (does not resolve #56)
* Cypress: Increase the allowed response time, to allow loading of local WordPress pages via MAMP Pro (https://github.com/dotherightthing/generator-wpdtrt-plugin-boilerplate/issues/92)
* Tenon: Clean up `tenonAnalyzeHtml` output
* Cypress: Add Cypress, add UI tests & several Gulp unit tests
* Accessibility: Hide items accessibly
* Remove redundant attributes
* Accessibility: Replace data-viewing with aria-expanded
* Only update the viewer if a different thumbnail was selected
* Gulp: Add fallback array if no options sent to output template

= 1.8.5 =
* Update wpdtrt-plugin-boilerplate to 1.5.4
* Fix incorrect icons on non-image thumbnails
* Refactor thumbnail URL params to data attributes, to prevent clash with Imgix URL params
* Crop image in collapsed viewer, replace background position offset with crop (via Media Cloud plugin)
* Use the default WordPress (square) thumbnail size, to remove redundant thumbnail option from Media Cloud crop modal
* Ensure that panoramas use the full viewer height
* Copy data- attributes from the thumbnail image to the surrounding link (fixes #51)
* Update dependencies
* Fix linting error

= 1.8.4 =
* Fix incorrect icons on non-image thumbnails
* Fix Soundcloud embed URL (ES6 conversion error)

= 1.8.3 =
* ES6 fixes (event.target vs this)
* Add missing JS initialisation message

= 1.8.2 =
* Update wpdtrt-plugin-boilerplate to 1.5.3
* Sync with generator-wpdtrt-plugin-boilerplate 0.8.2

= 1.8.1 =
* Update wpdtrt-contentsections dependency
* Update wpdtrt-exif dependency

= 1.8.0 =
* Update wpdtrt-plugin-boilerplate to 1.5.0
* Sync with generator-wpdtrt-plugin-boilerplate 0.8.0

= 1.7.18 =
* Update wpdtrt-plugin-boilerplate to 1.4.39
* Sync with generator-wpdtrt-plugin-boilerplate 0.7.27

= 1.7.17 =
* Update wpdtrt-plugin-boilerplate to 1.4.38
* Sync with generator-wpdtrt-plugin-boilerplate 0.7.25

= 1.7.16 =
* Update TGMPA dependency version for wpdtrt-contentsections
* Update TGMPA dependency version for wpdtrt-exif

= 1.7.15 =
* Update wpdtrt-plugin-boilerplate to 1.4.25
* Sync with generator-wpdtrt-plugin-boilerplate 0.7.20
* Added Tenon.io Proof of Concept

= 1.7.14 =
* Update wpdtrt-contentsections to 0.1.4 in TGMPA config
* Update wpdtrt-exif to 0.1.8 in TGMPA config

= 1.7.13 =
* Update wpdtrt-plugin to wpdtrt-plugin-boilerplate
* Update wpdtrt-plugin-boilerplate to 1.4.24
* Fix TGMPA dependency fields for wpdtrt-exif, specify dependencies in config
* Update lock file
* Remove redundant functionality
* Enable required functionality
* Fixes for PHPCS
* Fix plugin references in changelog
* Update dependencies
* Prefer stable versions but allow dev versions
* Update wpdtrt-contentsections to 0.1.4

= 1.7.12 =
* Update wpdtrt-contentsections dependency

= 1.7.11 =
* Update wpdtrt-plugin to 1.4.15

= 1.7.10 =
* Clean composer files

= 1.7.9 =
* Update wpdtrt-contentsections

= 1.7.8 =
* Update wpdtrt-contentsections

= 1.7.7 =
* Update wpdtrt-plugin to 1.4.14

= 1.7.6 =
* Update wpdtrt-contentsections dependency
* Fix path to autoloader when loaded as a test dependency
* Pass flag to fix path to autoloader when loading test dependency

= 1.7.5 =
* Include release number in wpdtrt-plugin namespaces
* Update wpdtrt-plugin to 1.4.6

= 1.7.4 =
* Update wpdtrt-plugin to 1.3.6

= 1.7.3 =
* Migrate image quality settings from wpdtrt
* Migrate common wpdtrt-gallery styles into wpdtrt-plugin
* Migrate Bower & NPM to Yarn
* Update Node from 6.11.2 to 8.11.1
* Add messages required by shortcode demo
* Add SCSS partials for project-specific extends and variables
* Change tag badge to release badge
* Fix default .pot file
* Update wpdtrt-plugin to 1.3.1

= 1.7.2 =
* Scale media embeds using embedresponsively
* Migrate icons from wpdtrt-dbth theme
* Fix media embed icon position
* Rename data attributes
* Document dependencies
* Use Environmental Variables in build
* Update wpdtrt-plugin

= 1.7.1 =
* Use Private Packagist
* Test shortcode
* Fix build badge

= 1.7.0 =
* Adapt plugin to use the new wpdtrt-plugin system

= 1.6.10 =
* Add tests for 1.6.9

= 1.6.9 =
* Fix custom post meta not being saved with new posts

= 1.6.8 =
* match build/config to [wpdtrt-plugin](https://github.com/dotherightthing/wpdtrt-plugin) (which this is an older standalone version of)
* add unique API key for generating releases via Travis

= 1.6.0 =
* Run PHPUnit tests from Travis

= 1.5.0 =
* Display correct icons on SoundCloud & Vimeo thumbnails
* Display media icon behind loading SoundCloud & Vimeo embeds
* Limit height of SoundCloud & Vimeo embeds

= 1.4.8 =
* Added Continuous Integration via Travis
* A compiled release zip is generated by Travis

= 1.4.1 =
* Hide thumbnail when it is the only one in a gallery
* Update media query syntax

= 1.4.0 =
* Merge in styles from wpdtrt-dbth
* Finesse number of thumbnail columns on smaller breakpoints
* Add aria-expanded to viewer expand button

= 1.3.2 =
* Fixed focus bugs caused by updated panorama scripting

= 1.3.1 =
* Reduced dimensions of thumbnail image
* Set attachment defaults
* Simplify expand state management and favour JS over CSS
* Fixed panorama caption overlap

= 1.2.0 =
* Replaced buggy Paver panorama scroll with simpler solution

= 1.1.1 =
* Improve presentation of attachment fields

= 1.0.0 =
* Released with wpdtrt-dbth

= 0.4.0 =
* Fixes to core functionality

= 0.3.0 =
* Fixes to core functionality
* Improvements to display of fields in attachment modal

= 0.2.0 =
* Fixes to core functionality

= 0.1.0 =
* Initial version

== Upgrade Notice ==

= 0.1.0 =
* Initial release
