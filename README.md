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

Within the editor:

```txt
[wpdtrt_gallery_shortcode_heading]<h2>Heading to wrap</h2>[/wpdtrt_gallery_shortcode_heading]
```

In a PHP template, as a template tag:

```php
<?php
    $heading = 'Heading to wrap';
    echo do_shortcode('[wpdtrt_gallery_shortcode_heading]<h2>' . $heading . '</h2>[/wpdtrt_gallery_shortcode_heading]');
?>
```

### Styling

Core CSS properties may be overwritten by changing the variable values in your theme stylesheet.

See `scss/_variables.scss`.

## Dependencies

1. The WordPress content filter looks for galleries within the markup injected by [DTRT Anchorlinks](https://github.com/dotherightthing/wpdtrt-anchorlinks):

```html
<div class="wpdtrt-anchorlinks__anchor">
    <!-- h2, .gallery, etc -->
</div>
```
