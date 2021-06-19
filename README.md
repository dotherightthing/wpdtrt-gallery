# DTRT Gallery

[![GitHub release](https://img.shields.io/github/v/tag/dotherightthing/wpdtrt-gallery)](https://github.com/dotherightthing/wpdtrt-gallery/releases) [![Build Status](https://github.com/dotherightthing/wpdtrt-gallery/workflows/Build%20and%20release%20if%20tagged/badge.svg)](https://github.com/dotherightthing/wpdtrt-gallery/actions?query=workflow%3A%22Build+and+release+if+tagged%22) [![GitHub issues](https://img.shields.io/github/issues/dotherightthing/wpdtrt-gallery.svg)](https://github.com/dotherightthing/wpdtrt-gallery/issues)

Gallery viewer which supports images, panoramas, maps, SoundCloud and Vimeo.

## Setup and Maintenance

Please read [DTRT WordPress Plugin Boilerplate: Workflows](https://github.com/dotherightthing/wpdtrt-plugin-boilerplate/wiki/Workflows).

## WordPress Installation

Please read the [WordPress readme.txt](readme.txt).

## WordPress Usage

The plugin shortcode is automatically injected into the content and wraps all `<h2>` headings.

Note: A WordPress gallery must immediately follow the `<h2>` heading.

### Display the gallery viewer

#### Within the content area

When the plugin is enabled, DTRT Gallery replaces the existing WordPress `[gallery]` shortcode. For correct display please set `[gallery link="file"]`.

Galleries added via the WordPress WYSIWYG will automatically be enhanced. For correct display please set *Gallery Settings > Link To: Media File*.

If a heading has no adjacent gallery, it will automatically be wrapped in `[wpdtrt_gallery_shortcode_heading]`.

#### Outside the content area

Headings may be manually wrapped in `[wpdtrt_gallery_shortcode_heading]`, as a template tag:

```php
<?php
    $heading = 'Heading to wrap';
    echo do_shortcode('[wpdtrt_gallery_shortcode_heading]<h2>' . $heading . '</h2>[/wpdtrt_gallery_shortcode_heading]');
?>
```

### Styling

Core CSS properties may be overwritten by changing the variable values in your theme stylesheet.

See `scss/variables/_css.scss`.

## Dependencies

1. The WordPress content filter looks for galleries within the markup injected by [DTRT Anchorlinks](https://github.com/dotherightthing/wpdtrt-anchorlinks). It also uses the *Heading level* set in that plugin's admin screen.

```html
<div class="wpdtrt-anchorlinks__section">
    <!-- h2, .gallery, etc -->
</div>
```

## Demo pages

1. Gallery with audio: [Don't Believe The Hype - Day 13](https://dontbelievethehype.co.nz/tourdiaries/asia/east-asia/russia/13/exploring-irkutsk/#section-quirky-cuisine)
1. Gallery with panorama: [Don't Believe The Hype - Day 17](https://dontbelievethehype.co.nz/tourdiaries/asia/east-asia/russia/17/buguldeyka-to-yelantsy/#section-leaving-buguldeyka)
1. Gallery with video: [Don't Believe The Hype - Day 11](https://dontbelievethehype.co.nz/tourdiaries/asia/east-asia/russia/11/exploring-chita/#section-soviet-style)
