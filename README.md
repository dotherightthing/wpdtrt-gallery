# DTRT Gallery

[![GitHub release](https://img.shields.io/github/release/dotherightthing/wpdtrt-gallery.svg)](https://github.com/dotherightthing/wpdtrt-gallery/releases) [![Build Status](https://travis-ci.org/dotherightthing/wpdtrt-gallery.svg?branch=master)](https://travis-ci.org/dotherightthing/wpdtrt-gallery) [![GitHub issues](https://img.shields.io/github/issues/dotherightthing/wpdtrt-gallery.svg)](https://github.com/dotherightthing/wpdtrt-gallery/issues)

Gallery viewer which supports images, panoramas, maps, SoundCloud and Vimeo.

## Setup and Maintenance

Please read [DTRT WordPress Plugin Boilerplate: Workflows](https://github.com/dotherightthing/wpdtrt-plugin-boilerplate/wiki/Workflows).

## WordPress Installation and Usage

Please read the [WordPress readme.txt](readme.txt).

## Cypress Configuration and Usage

### Configuration

#### Base URL

* Edit cypress.json
  ```
  "baseUrl": "https://dontbelievethehype.co.nz",
  ```

Note: Live website URLs are preferable (see [#53](https://github.com/dotherightthing/wpdtrt-gallery/issues/53)).

#### Tenon

* Get a new API key: <https://tenon.io/register.php>
* Get an existing API key: <https://tenon.io/apikey.php>
* Edit cypress.json
  ```
  "env": {
    "TENON_API_KEY": "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX"
  },
  ```

### Usage

```
yarn cypress:open
```

## Dependencies

1. The gallery viewer is initialised as [DTRT Content Sections](https://github.com/dotherightthing/wpdtrt-contentsections) are scrolled into view.
