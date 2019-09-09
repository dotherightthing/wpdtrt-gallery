<?php
/**
 * File: src/class-wpdtrt-gallery-plugin.php
 *
 * Plugin sub class.
 *
 * Since:
 *   0.8.13 - DTRT WordPress Plugin Boilerplate Generator
 */

/**
 * Class: WPDTRT_Gallery_Plugin
 *
 * Extends the base class to inherit boilerplate functionality,
 * adds application-specific methods.
 *
 * Since:
 *   0.8.13 - DTRT WordPress Plugin Boilerplate Generator
 */
class WPDTRT_Gallery_Plugin extends DoTheRightThing\WPDTRT_Plugin_Boilerplate\r_1_6_8\Plugin {

	/**
	 * Constructor: __construct
	 *
	 * Supplement plugin initialisation.
	 *
	 * Parameters:
	 *   $options - Plugin options
	 *
	 * Since:
	 *   0.8.13 - DTRT WordPress Plugin Boilerplate Generator
	 */
	public function __construct( $options ) {

		// edit here.
		parent::__construct( $options );
	}

	/**
	 * Group: WordPress Integration
	 * _____________________________________
	 */

	/**
	 * Function: wp_setup
	 *
	 * Supplement plugin's WordPress setup.
	 *
	 * Note:
	 * - Default priority is 10. A higher priority runs later.
	 *
	 * See:
	 * - <Action order: https://codex.wordpress.org/Plugin_API/Action_Reference>
	 *
	 * Since:
	 *   0.8.13 - DTRT WordPress Plugin Boilerplate Generator
	 */
	protected function wp_setup() {

		parent::wp_setup();

		// About: add actions and filters here.
		//
		// for filter_save_image_geodata.
		include_once ABSPATH . 'wp-admin/includes/image.php';

		// add actions and filters here.
		add_filter( 'shortcode_atts_gallery', array( $this, 'filter_gallery_attributes' ), 10, 3 );
		add_filter( 'wp_read_image_metadata', array( $this, 'filter_save_image_geodata' ), '', 3 );
		add_filter( 'wp_get_attachment_link', array( $this, 'filter_thumbnail_queryparams' ), 1, 4 );
		add_filter( 'wp_get_attachment_image_attributes', array( $this, 'filter_thumbnail_attributes' ), 1, 4 ); // 10,2
		add_filter( 'the_content', array( $this, 'filter_shortcode_heading' ), 10 );
		add_filter( 'jpeg_quality', array( $this, 'filter_image_quality' ) );
		add_filter( 'wp_editor_set_quality', array( $this, 'filter_image_quality' ) );
		// add_filter( 'ilab_s3_can_calculate_srcset', false, 10, 4 ); // https://github.com/Interfacelab/ilab-media-tools/issues/55.
		//
		$this->helper_add_image_sizes();
	}

	/**
	 * Group: Getters and Setters
	 * _____________________________________
	 */

	/**
	 * Group: Renderers
	 * _____________________________________
	 */

	/**
	 * Method: render_js_frontend
	 *
	 * Add project-specific frontend scripts.
	 *
	 * See:
	 * - wpdtrt-plugin-boilerplate/src/Plugin.php
	 *
	 * Since:
	 *   0.7.1 - Added
	 */
	public function render_js_frontend() {
		$attach_to_footer = true;

		wp_register_script( 'uri',
			$this->get_url() . 'node_modules/urijs/src/URI.min.js',
			array(),
			'1.18.12',
			$attach_to_footer
		);

		// inview lazy loading.
		wp_register_script( 'jquery_waypoints',
			$this->get_url() . 'node_modules/waypoints/lib/jquery.waypoints.min.js',
			array(
				// load these registered dependencies first:.
				'jquery',
			),
			'4.0.0',
			$attach_to_footer
		);

		// inview lazy loading.
		wp_register_script( 'waypoints_inview',
			$this->get_url() . 'node_modules/waypoints/lib/shortcuts/inview.min.js',
			array(
				// load these registered dependencies first:.
				'jquery_waypoints',
			),
			'4.0.0',
			$attach_to_footer
		);

		// init
		// from Plugin.php + extra dependencies.
		wp_enqueue_script( $this->get_prefix(),
			$this->get_url() . 'js/frontend-es5.js',
			array(
				// load these registered dependencies first:.
				'jquery',
				'uri',
				'waypoints_inview',
			),
			$this->get_version(),
			$attach_to_footer
		);

		// from Plugin.php.
		wp_localize_script( $this->get_prefix(),
			$this->get_prefix() . '_config',
			array(
				// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
				// but we need to explicitly expose it to frontend pages.
				'ajaxurl' => admin_url( 'admin-ajax.php' ), // wpdtrt_foobar_config.ajaxurl.
				'options' => $this->get_options(),
			)
		);

		// Replace rather than extend, in order to specify dependencies:
		// parent::render_js_frontend();.
	}

	/**
	 * Group: Filters
	 * _____________________________________
	 */

	/**
	 * Method: filter_gallery_attributes
	 *
	 * Set attachment defaults.
	 *
	 * Note:
	 * - This affects the front-end, overriding the selections in wp-admin.
	 *
	 * Parameters:
	 *   $result - The shortcode_atts() merging of $defaults and $atts
	 *   $defaults - The default attributes defined for the shortcode
	 *   $atts - The attributes supplied by the user within the shortcode
	 *
	 * See:
	 * - <https://gist.github.com/mjsdiaz/7204576>
	 */
	public function filter_gallery_attributes( array $result, array $defaults, array $atts ) {

		$result['columns'] = '3';
		$result['link']    = 'file';

		return $result;
	}

	/**
	 * Method: filter_image_quality
	 *
	 * Filters the image quality for thumbnails to be at the highest ratio possible.
	 *
	 * Note:
	 * - Supports the new 'wp_editor_set_quality' filter added in WP 3.5.
	 *
	 * Parameters:
	 *   $quality - The default quality (90).
	 *
	 * Returns:
	 *   $quality - Amended quality (100)
	 *
	 * See:
	 * - <https://thomasgriffin.io/how-to-change-the-quality-of-wordpress-thumbnails/>
	 */
	public function filter_image_quality( int $quality ) : int {
		return 100;
	}

	/**
	 * Method: filter_save_image_geodata
	 *
	 * Add Geolocation EXIF to the attachment metadata stored in the WP database.
	 *
	 * Note:
	 * - Added false values to prevent this function running over and over,
	 *   if the image was taken with a non-geotagging camera
	 *
	 * Parameters:
	 *   $meta - Image meta data
	 *   $file - Path to image file
	 *   $source_image_type - Type of image
	 *
	 * Returns:
	 *   $meta - Image meta data including geolocation data
	 *
	 * Requires:
	 *   wp-admin/includes/image.php
	 *
	 * Uses:
	 * - <http://kristarella.blog/2009/04/add-image-exif-metadata-to-wordpress/>
	 *
	 * Example:
	 * --- php
	 * include_once( ABSPATH . 'wp-admin/includes/image.php' ); // access wp_read_image_metadata
	 * add_filter('wp_read_image_metadata', 'filter_save_image_geodata','',3);
	 * ---
	 */
	public function filter_save_image_geodata( array $meta, string $file, int $source_image_type ) : array {

		$exif = @exif_read_data( $file );

		if ( ! empty( $exif['GPSLatitude'] ) ) {
			$meta['latitude'] = $exif['GPSLatitude'];
		} else {
			$meta['latitude'] = false;
		}

		if ( ! empty( $exif['GPSLatitudeRef'] ) ) {
			$meta['latitude_ref'] = trim( $exif['GPSLatitudeRef'] );
		} else {
			$meta['latitude_ref'] = false;
		}

		if ( ! empty( $exif['GPSLongitude'] ) ) {
			$meta['longitude'] = $exif['GPSLongitude'];
		} else {
			$meta['longitude'] = false;
		}

		if ( ! empty( $exif['GPSLongitudeRef'] ) ) {
			$meta['longitude_ref'] = trim( $exif['GPSLongitudeRef'] );
		} else {
			$meta['longitude_ref'] = false;
		}

		return $meta;
	}

	/**
	 * Method: filter_shortcode_heading
	 *
	 * Automatically inject plugin shortcodes into the content.
	 *
	 * Note:
	 * - do_shortcode() is registered as a default filter on 'the_content' with a priority of 11.
	 *
	 * Parameters:
	 *   $content - Content
	 *
	 * Returns:
	 *   $content - Content
	 *
	 * See:
	 * <https://codex.wordpress.org/Shortcode_API#Function_reference>
	 *
	 * Manual alternatives:
	 * --- php
	 * [wpdtrt_gallery]H2 heading text[/wpdtrt_gallery]
	 * ---
	 * --- php
	 * do_shortcode( '[wpdtrt_gallery]H2 heading text[/wpdtrt_gallery]' );
	 * ---
	 */
	public function filter_shortcode_heading( string $content ) : string {

		$content = preg_replace( '/(<h2>.+<\/h2>)/', '[wpdtrt_gallery_shortcode_heading]$1[/wpdtrt_gallery_shortcode_heading]', $content );
		$content = preg_replace( "/<div id='gallery'>/", '<h3>Gallery</h3>$1', $content );

		return $content;
	}

	/**
	 * Method: filter_thumbnail_queryparams
	 *
	 * Add attributes to gallery thumbnail links,
	 * which are transformed to data attributes by the plugin JS
	 *
	 * Parameters:
	 *   $html - HTML
	 *   $id - ID
	 *   $size - Size
	 *   $permalink - Permalink
	 *
	 * Returns:
	 *   $html - HTML with attributes
	 *
	 * TODO
	 * - Change urlencode to rawurlencode
	 */
	public function filter_thumbnail_queryparams( string $html, int $id, string $size, bool $permalink ) : string {

		if ( false !== $permalink ) {
			return $html;
		}

		/**
		 * Filter the gallery thumbnail links to point to custom image sizes, rather than the 'full' image size.
		 *
		 * @see http://johnciacia.com/2012/12/31/filter-wordpress-gallery-image-link/
		 * list() is used to assign a list of variables in one operation.
		 */

		// see wpdtrt-gallery-enlargement.php.
		$image_size_desktop  = 'wpdtrt-gallery-desktop';
		$image_size_panorama = 'wpdtrt-gallery-panorama';

		$panorama = get_post_meta( $id, 'wpdtrt_gallery_attachment_panorama', true ); // used for JS dragging.

		$image_size_large = ( '1' === $panorama ) ? $image_size_panorama : $image_size_desktop;

		// set $link to values from the image_src array.
		list( $link, , ) = wp_get_attachment_image_src( $id, $image_size_large );

		// Update gallery link.
		return preg_replace( "/href='([^']+)'/", "href='$link'", $html );
	}

	/**
	 * Method: filter_thumbnail_attributes
	 *
	 * Add data- attributes to gallery thumbnails for use by the plugin JS.
	 *
	 * Note:
	 * - Refactored due to clashes with Imgix using previous solution of link URL params.
	 *
	 * Parameters:
	 *   $atts - Gallery image tag attributes
	 *   $attachment - WP_Post object for the attachment
	 *
	 * Returns:
	 *   $atts - (maybe) filtered gallery image tag attributes
	 *
	 * See:
	 * - <https://developer.wordpress.org/reference/hooks/wp_get_attachment_image_attributes/>
	 */
	public function filter_thumbnail_attributes( array $atts, object $attachment ) : array {

		$id = $attachment->ID;

		$atts['data-id'] = $id;

		// Vimeo.
		$vimeo_pageid = get_post_meta( $id, 'wpdtrt_gallery_attachment_vimeo_pageid', true ); // used for embed.

		if ( $vimeo_pageid ) {
			$atts['data-vimeo-pageid'] = $vimeo_pageid;
		}

		// SoundCloud.
		$soundcloud_pageid  = get_post_meta( $id, 'wpdtrt_gallery_attachment_soundcloud_pageid', true ); // used for SEO.
		$soundcloud_trackid = get_post_meta( $id, 'wpdtrt_gallery_attachment_soundcloud_trackid', true ); // used for embed, see also http://stackoverflow.com/a/28182284.

		if ( $soundcloud_pageid && $soundcloud_trackid ) {
			$atts['data-soundcloud-pageid']  = rawurlencode( $soundcloud_pageid );
			$atts['data-soundcloud-trackid'] = $soundcloud_trackid;
		}

		// Ride With GPS.
		$rwgps_pageid = get_post_meta( $id, 'wpdtrt_gallery_attachment_rwgps_pageid', true );

		if ( $rwgps_pageid ) {
			$atts['data-rwgps-pageid'] = rawurlencode( $rwgps_pageid );
		}

		// Select onload.
		$default = get_post_meta( $id, 'wpdtrt_gallery_attachment_default', true );

		if ( '1' === $default ) {
			$atts['data-initial'] = $default;
		}

		// Panorama.
		$panorama = get_post_meta( $id, 'wpdtrt_gallery_attachment_panorama', true ); // used for JS dragging.

		if ( '1' === $panorama ) {
			$atts['data-panorama'] = $panorama;
		}

		// Geolocation
		// Could this be replaced by simply looking up the custom field?
		if ( function_exists( 'wpdtrt_exif_get_attachment_metadata' ) ) {
			$attachment_metadata     = wpdtrt_exif_get_attachment_metadata( $id );
			$attachment_metadata_gps = wpdtrt_exif_get_attachment_metadata_gps( $attachment_metadata, 'number' );
			$atts['data-latitude']   = $attachment_metadata_gps['latitude'];
			$atts['data-longitude']  = $attachment_metadata_gps['longitude'];
		}

		// store the other enlarged sources in data attributes.
		$image_size_desktop_expanded       = 'wpdtrt-gallery-desktop-expanded';
		$atts['data-src-desktop-expanded'] = wp_get_attachment_image_src( $id, $image_size_desktop_expanded )[0];
		// $image_size_mobile       = 'wpdtrt-gallery-mobile'; // TODO not implemented, needs enquire.js.
		// $atts['data-src-mobile'] = wp_get_attachment_image_src( $id, $image_size_mobile )[0].
		return $atts;
	}

	/**
	 * Group: Helpers
	 * _____________________________________
	 */

	/**
	 * Method: helper_add_image_sizes
	 *
	 * Add image sizes, scaling width proportional to max height in UI.
	 *
	 * Note:
	 * - Scaling is always relative to the shorter axis
	 * - Crop option determines whether image will be assigned a tab in the Interfacelab Media Cloud crop modal
	 *
	 * See:
	 * - <https://stackoverflow.com/a/18159895/6850747> (used)
	 * - <https://wordpress.stackexchange.com/questions/212768/add-image-size-where-largest-possible-proportional-size-is-generated> (not used)
	 * - <https://www.smashingmagazine.com/2016/09/responsive-images-in-wordpress-with-art-direction/>
	 */
	public function helper_add_image_sizes() {
		$desktop_width            = 865;
		$desktop_height_collapsed = 368; // vertically crop to design.
		$desktop_height_expanded  = 9999; // auto.
		$desktop_crop_collapsed   = true;
		$desktop_crop_expanded    = false;

		add_image_size(
			'wpdtrt-gallery-desktop',
			$desktop_width,
			$desktop_height_collapsed,
			$desktop_crop_collapsed
		);

		add_image_size(
			'wpdtrt-gallery-desktop-expanded',
			$desktop_width,
			$desktop_height_expanded,
			$desktop_crop_expanded
		);

		// Landscape Panorama image.
		$panorama_width_collapsed  = 9999; // auto.
		$panorama_height_collapsed = 368; // both desktop and mobile.
		$panorama_crop_collapsed   = false;

		add_image_size(
			'wpdtrt-gallery-panorama',
			$panorama_width_collapsed,
			$panorama_height_collapsed,
			$panorama_crop_collapsed
		);
	}
}
