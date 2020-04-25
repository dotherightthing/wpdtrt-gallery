# DTRT Gallery

[![GitHub release](https://img.shields.io/github/v/tag/dotherightthing/wpdtrt-gallery)](https://github.com/dotherightthing/wpdtrt-gallery/releases) [![Build](https://github.com/dotherightthing/wpdtrt-gallery/workflows/Build/badge.svg)](https://github.com/dotherightthing/wpdtrt-gallery/actions?query=workflow%3ABuild) [![GitHub issues](https://img.shields.io/github/issues/dotherightthing/wpdtrt-gallery.svg)](https://github.com/dotherightthing/wpdtrt-gallery/issues)

Gallery viewer which supports images, panoramas, maps, SoundCloud and Vimeo.

## Setup and Maintenance

Please read [DTRT WordPress Plugin Boilerplate: Workflows](https://github.com/dotherightthing/wpdtrt-plugin-boilerplate/wiki/Workflows).

## WordPress Installation and Usage

Please read the [WordPress readme.txt](readme.txt).

## Dependencies

1. The WordPress content filter looks for galleries within the markup injected by [DTRT Anchorlinks](https://github.com/dotherightthing/wpdtrt-anchorlinks):

```html
<div class="wpdtrt-anchorlinks__anchor">
    <!-- h2, .gallery, etc -->
</div>
```
