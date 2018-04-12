<?php
/**
 * Plugin sub class.
 *
 * @package     wpdtrt_gallery
 * @version 	0.0.1
 * @since       0.7.0
 */

/**
 * Plugin sub class.
 *
 * Extends the base class to inherit boilerplate functionality.
 * Adds application-specific methods.
 *
 * @version 	0.0.1
 * @since       0.7.0
 */
class WPDTRT_Gallery_Plugin extends DoTheRightThing\WPPlugin\Plugin {

    /**
     * Hook the plugin in to WordPress
     * This constructor automatically initialises the object's properties
     * when it is instantiated,
     * using new WPDTRT_Weather_Plugin
     *
     * @param     array $settings Plugin options
     *
	 * @version 	0.0.1
     * @since       0.7.0
     */
    function __construct( $settings ) {

    	// add any initialisation specific to wpdtrt-gallery here

		// Instantiate the parent object
		parent::__construct( $settings );
    }

    //// START WORDPRESS INTEGRATION \\\\

    /**
     * Initialise plugin options ONCE.
     *
     * @param array $default_options
     *
     * @version     0.0.1
     * @since       0.7.0
     */
    protected function wp_setup() {

        parent::set_plugin_dependency(
            array(
                'name'      => 'DTRT EXIF',
                'slug'      => 'wpdtrt-exif',
                'required'  => true
            ),
            array(
                'name'      => 'Regenerate Thumbnails',
                'slug'      => 'regenerate-thumbnails',
                'required'  => false
            )
        );

    	parent::wp_setup();

		// add actions and filters here
        add_filter( 'shortcode_atts_gallery', 'filter_gallery_attributes', 10, 3 );
        add_filter( 'wp_read_image_metadata', 'filter_save_image_geodata', '', 3 );
        add_filter( 'wp_get_attachment_link', 'filter_thumbnail_queryparams', 1, 4 );
        add_filter( 'the_content', 'filter_shortcode_heading', 10 );

        $this->helper_add_image_sizes();
    }

    /**
     * Add project-specific frontend scripts
     *
     * @version     0.0.1
     * @since       0.7.1
     *
     * @see wpdtrt-plugin/src/Plugin.php
     */
    protected function render_js_frontend() {
        $attach_to_footer = true;

        wp_register_script( 'uri',
            $this->get_url() . 'vendor/urijs/src/URI.min.js',
            array(),
            '1.18.12',
            $attach_to_footer
        );

        // inview lazy loading
        wp_register_script( 'jquery_waypoints',
            $this->get_url() . 'vendor/waypoints/lib/jquery.waypoints.min.js',
            array(
                // load these registered dependencies first:
                'jquery',
            ),
            '4.0.0',
            $attach_to_footer
        );

        // inview lazy loading
        wp_register_script( 'waypoints_inview',
            $this->get_url() . 'vendor/waypoints/lib/shortcuts/inview.min.js',
            array(
                // load these registered dependencies first:
                'jquery_waypoints'
            ),
            '4.0.0',
            $attach_to_footer
        );

        // init
        // from Plugin.php + extra dependencies
        wp_enqueue_script( $this->get_prefix(),
            $this->get_url() . 'js/frontend.js',
            array(
                // load these registered dependencies first:
                'jquery',
                'uri',
                'waypoints_inview',
            ),
            $this->get_version(),
            $attach_to_footer
        );

        // from Plugin.php
        wp_localize_script( $this->get_prefix(),
            $this->get_prefix() . '_config',
            array(
                // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
                // but we need to explicitly expose it to frontend pages
                'ajaxurl' => admin_url( 'admin-ajax.php' ), // wpdtrt_foobar_config.ajaxurl
                'options' => $this->get_options() // wpdtrt_foobar_config.options
            )
        );

        // Replace rather than extend, in order to specify dependencies:
        // parent::render_js_frontend();
    }

    //// END WORDPRESS INTEGRATION \\\\

    //// START SETTERS AND GETTERS \\\\
    //// END SETTERS AND GETTERS \\\\

    //// START RENDERERS \\\\
    //// END RENDERERS \\\\

    //// START FILTERS \\\\

    /**
     * Change size of gallery thumbnail images and set attachment defaults.
     *  This affects the front-end, overriding the selections in wp-admin.
     * @uses https://mekshq.com/change-image-thumbnail-size-in-wordpress-gallery/
     * @see https://gist.github.com/mjsdiaz/7204576
     */
    function filter_gallery_attributes( $output, $pairs, $atts ) {

        $output['columns'] = '3';
        $output['link'] = 'file';
        $output['size'] = 'wpdtrt-gallery-thumbnail';

        return $output;
    }

    /**
     * Add Geolocation EXIF to the attachment metadata stored in the WP database
     * Added false values to prevent this function running over and over
     * if the image was taken with a non-geotagging camera
     * @uses http://kristarella.blog/2009/04/add-image-exif-metadata-to-wordpress/
     * @requires wp-admin/includes/image.php
     *
     * @example
     *  include_once( ABSPATH . 'wp-admin/includes/image.php' ); // access wp_read_image_metadata
     *  add_filter('wp_read_image_metadata', 'filter_save_image_geodata','',3);
     */
    function filter_save_image_geodata( $meta, $file, $sourceImageType ) {

        include_once( ABSPATH . 'wp-admin/includes/image.php' );

        $exif = @exif_read_data( $file );

        if (!empty($exif['GPSLatitude'])) {
            $meta['latitude'] = $exif['GPSLatitude'] ;
        }
        else {
            $meta['latitude'] = false;
        }

        if (!empty($exif['GPSLatitudeRef'])) {
            $meta['latitude_ref'] = trim( $exif['GPSLatitudeRef'] );
        }
        else {
            $meta['latitude_ref'] = false;
        }

        if (!empty($exif['GPSLongitude'])) {
            $meta['longitude'] = $exif['GPSLongitude'] ;
        }
        else {
            $meta['longitude'] = false;
        }

        if (!empty($exif['GPSLongitudeRef'])) {
            $meta['longitude_ref'] = trim( $exif['GPSLongitudeRef'] );
        }
        else {
            $meta['longitude_ref'] = false;
        }

        return $meta;
    }

    /**
     * Add attributes to gallery thumbnail links
     * These are transformed to data attributes by the plugin JS
     *
     * @param $html
     * @param $id
     * @param $size
     * @param $permalink
     * @return string
     */
    function filter_thumbnail_queryparams($html, $id, $size, $permalink) {

        if ( false !== $permalink ) {
            return $html;
        }

        $link_options = array();

        // Vimeo

        $vimeo_pageid = get_post_meta( $id, 'wpdtrt_gallery_attachment_vimeo_pageid', true ); // used for embed

        if ( $vimeo_pageid ) {
            $link_options['vimeo_pageid'] = $vimeo_pageid;
        }

        // SoundCloud

        $soundcloud_pageid = get_post_meta( $id, 'wpdtrt_gallery_attachment_soundcloud_pageid', true ); // used for SEO
        $soundcloud_trackid = get_post_meta( $id, 'wpdtrt_gallery_attachment_soundcloud_trackid', true ); // used for embed, see also http://stackoverflow.com/a/28182284

        if ( $soundcloud_pageid && $soundcloud_trackid ) {
            $link_options['soundcloud_pageid'] = urlencode( $soundcloud_pageid );
            $link_options['soundcloud_trackid'] = $soundcloud_trackid;
        }

        // Ride With GPS

        $rwgps_pageid = get_post_meta( $id, 'wpdtrt_gallery_attachment_rwgps_pageid', true );

        if ( $rwgps_pageid ) {
            $link_options['rwgps_pageid'] = urlencode( $rwgps_pageid );
        }

        // Position Y

        $position_y = get_post_meta( $id, 'wpdtrt_gallery_attachment_position_y', true );
        $position_y_default = "50";

        if ( $position_y !== '' ) {
            $link_options['position_y'] = $position_y;
        }
        else {
            $link_options['position_y'] = $position_y_default;
        }

        // Select onload

        $default = get_post_meta( $id, 'wpdtrt_gallery_attachment_default', true );

        if ( $default == '1' ) {
            $link_options['default'] = $default;
        }

        // Panorama

        $panorama = get_post_meta( $id, 'wpdtrt_gallery_attachment_panorama', true ); // used for JS dragging

        if ( $panorama == '1' ) {
            $link_options['panorama'] = $panorama;
        }

        // Geolocation
        // Could this be replaced by simply looking up the custom field?

        if ( function_exists( 'wpdtrt_exif_get_attachment_metadata' ) ) {
            $attachment_metadata = wpdtrt_exif_get_attachment_metadata( $id );
            $attachment_metadata_gps = wpdtrt_exif_get_attachment_metadata_gps( $attachment_metadata, 'number' );

            $link_options['latitude'] = $attachment_metadata_gps['latitude'];
            $link_options['longitude'] = $attachment_metadata_gps['longitude'];
        }

        /**
        * Filter the gallery thumbnail links to point to custom image sizes, rather than the 'full' image size.
        * @see http://johnciacia.com/2012/12/31/filter-wordpress-gallery-image-link/
        * list() is used to assign a list of variables in one operation.
        */

        // see wpdtrt-gallery-enlargement.php
        $image_size_mobile = 'wpdtrt-gallery-mobile';
        $image_size_desktop = 'wpdtrt-gallery-desktop';
        $image_size_panorama = 'wpdtrt-gallery-panorama';

        $image_size_small = $image_size_mobile;
        $image_size_large = $panorama ? $image_size_panorama : $image_size_desktop;

        // set $link to values from the image_src array
        list( $link, , ) = wp_get_attachment_image_src( $id, $image_size_large );

        // store the other enlargement sizes in data attributes
        $link_options['src_mobile'] = wp_get_attachment_image_src( $id, $image_size_small )[0];

        // Encode options
        // http://stackoverflow.com/a/39370906

        $query = http_build_query($link_options, '', '&amp;');
        $link .= '?' . $query;

        // Update gallery link
        return preg_replace( "/href='([^']+)'/", "href='$link'", $html );
    }

    /**
     * Automatically inject plugin shortcodes into the content
     *  Note: do_shortcode() is registered as a default filter on 'the_content' with a priority of 11.
     *
     * Manual alternatives:
     * @example [wpdtrt_gallery]H2 heading text[/wpdtrt_gallery]
     * @example do_shortcode( '[wpdtrt_gallery]H2 heading text[/wpdtrt_gallery]' );
     *
     * @return $content
     *
     * @see https://codex.wordpress.org/Shortcode_API#Function_reference
     */
    public function filter_shortcode_heading($content) {
        //if ( ! function_exists('wpdtrt_content_sections') ) {
        //  return;
        //}

        $content = preg_replace("/(<h2>.+<\/h2>)/", "[wpdtrt_gallery_shortcode_heading]$1[/wpdtrt_gallery_shortcode_heading]", $content);

        $content = preg_replace("/<div id='gallery'>/", "<h3>Gallery</h3>$1", $content);

        return $content;
    }

    //// END FILTERS \\\\

    //// START HELPERS \\\\

    /**
     * Add image sizes, scaling width proportional to max height in UI.
     *  Note: Scaling is always relative to the shorter axis.
     *
     * @see https://stackoverflow.com/a/18159895/6850747 (used)
     * @see https://wordpress.stackexchange.com/questions/212768/add-image-size-where-largest-possible-proportional-size-is-generated (not used)
     * @see https://www.smashingmagazine.com/2016/09/responsive-images-in-wordpress-with-art-direction/
     */
    public function helper_add_image_sizes() {

        // Thumbnail image

        $thumbnail_width = 150;
        $thumbnail_height = 150;
        $thumbnail_crop = true;

        add_image_size(
          'wpdtrt-gallery-thumbnail',
          $thumbnail_width,
          $thumbnail_height,
          $thumbnail_crop
        );

        // Portrait/Landscape image

        $mobile_width = 400; // allows for bigger phones
        $mobile_height = 9999; // auto
        $mobile_crop = false;

        add_image_size(
          'wpdtrt-gallery-mobile',
          $mobile_width,
          $mobile_height,
          $mobile_crop
        );

        $desktop_width = 972;
        $desktop_height = 9999; // auto
        $desktop_crop = false;

        add_image_size(
          'wpdtrt-gallery-desktop',
          $desktop_width,
          $desktop_height,
          $desktop_crop
        );

        // Landscape Panorama image

        $panorama_width = 9999; // auto
        $panorama_height = 368; // both desktop and mobile
        $panorama_crop = false;

        add_image_size(
          'wpdtrt-gallery-panorama',
          $panorama_width,
          $panorama_height,
          $panorama_crop
        );
    }

    //// END HELPERS \\\\
}

?>