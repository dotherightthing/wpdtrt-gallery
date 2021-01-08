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
class WPDTRT_Gallery_Plugin extends DoTheRightThing\WPDTRT_Plugin_Boilerplate\r_1_7_7\Plugin {

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
	public function __construct( $options ) { // phpcs:ignore

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
		add_filter( 'post_gallery', array( $this, 'filter_gallery_html' ), 10, 3 );
		add_filter( 'shortcode_atts_gallery', array( $this, 'filter_gallery_attributes' ), 10, 3 );
		add_filter( 'use_default_gallery_style', '__return_false' );
		add_filter( 'wp_read_image_metadata', array( $this, 'filter_save_image_geodata' ), '', 3 );
		add_filter( 'wp_get_attachment_image_attributes', array( $this, 'filter_image_attributes' ), 1, 4 ); // 10,2
		add_filter( 'the_content', array( $this, 'filter_content_inject_shortcode' ), 10 ); // after wpdtrt-anchorlinks @ 9.
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
	 * Method: set_tabpanel_props
	 *
	 * Parameters:
	 *   $gallery_props - Array
	 *   $att_id - String?
	 *   $atts - Array
	 *   $count - Number
	 *   $iconclassimage - String
	 *   $iconclasspanorama - String
	 *   $iconclassrwgps - String
	 *   $iconclasssoundcloud - String
	 *   $iconclassvimeo - String
	 *   $tabpanelimagesize - String
	 *   $tabpanelimagesizeexpanded - String
	 *   $tabpanelimagesizepanorama - String
	 *   $post_excerpt - string
	 *
	 * Returns:
	 *   $array - $tabpanel_props
	 */
	public function set_tabpanel_props( Array $gallery_props, int $att_id, Array $atts, $count, string $iconclassimage, string $iconclasspanorama, string $iconclassrwgps, string $iconclasssoundcloud, string $iconclassvimeo, string $tabpanelimagesize, string $tabpanelimagesizeexpanded, string $tabpanelimagesizepanorama, string $post_excerpt ) : Array {
		$tabpanel_props = [];

		$tabpanel_props['att_id']             = $att_id;
		$tabpanel_props['autopause']          = 'true'; // opposite of $autoplay.
		$tabpanel_props['autoplay']           = 'false'; // ex-JS notes: autoplay disabled as working but inconsistent.
		$tabpanel_props['caption_id']         = "{$gallery_props['id']}-tabpanel-{$count}-caption";
		$tabpanel_props['iframe_fullscreen']  = '';
		$tabpanel_props['media_id']           = "{$gallery_props['id']}-tabpanel-{$count}-media";
		$tabpanel_props['panorama']           = get_post_meta( $att_id, 'wpdtrt_gallery_attachment_panorama', true ); // used for JS dragging.
		$tabpanel_props['post_excerpt']       = trim( $post_excerpt );
		$tabpanel_props['rwgps_pageid']       = get_post_meta( $att_id, 'wpdtrt_gallery_attachment_rwgps_pageid', true );
		$tabpanel_props['soundcloud_pageid']  = get_post_meta( $att_id, 'wpdtrt_gallery_attachment_soundcloud_pageid', true ); // used for SEO.
		$tabpanel_props['soundcloud_trackid'] = get_post_meta( $att_id, 'wpdtrt_gallery_attachment_soundcloud_trackid', true ); // used for embed, see also http://stackoverflow.com/a/28182284.
		$tabpanel_props['tab_id']             = "{$gallery_props['id']}-tab-{$count}";
		$tabpanel_props['tabpanel_id']        = "{$gallery_props['id']}-tabpanel-{$count}";
		$tabpanel_props['thumbnail']          = wp_get_attachment_image( $att_id, $atts['size'], false, '' );
		$tabpanel_props['vimeo_pageid']       = get_post_meta( $att_id, 'wpdtrt_gallery_attachment_vimeo_pageid', true ); // used for embed.

		// Map geolocation
		// Could this be replaced by simply looking up the custom field?
		if ( function_exists( 'wpdtrt_exif_get_attachment_metadata' ) ) {
			$attachment_metadata         = wpdtrt_exif_get_attachment_metadata( $att_id );
			$attachment_metadata_gps     = wpdtrt_exif_get_attachment_metadata_gps( $attachment_metadata, 'number' );
			$tabpanel_props['latitude']  = $attachment_metadata_gps['latitude'];
			$tabpanel_props['longitude'] = $attachment_metadata_gps['longitude'];
		} else {
			$tabpanel_props['latitude']  = '';
			$tabpanel_props['longitude'] = '';
		}

		// Media types.
		$tabpanel_props['iframe'] = $tabpanel_props['rwgps_pageid'] || ( $tabpanel_props['soundcloud_pageid'] && $tabpanel_props['soundcloud_trackid'] ) || $tabpanel_props['vimeo_pageid'];

		if ( '1' === $tabpanel_props['panorama'] ) {
			$tabpanel_props['iconalt']   = 'Panorama';
			$tabpanel_props['iconclass'] = $iconclasspanorama;
			$tabpanel_props['image']     = wp_get_attachment_image( $att_id, $tabpanelimagesizepanorama, false, '' );
			$tabpanel_props['image_src'] = wp_get_attachment_image_src( $att_id, $tabpanelimagesizepanorama )[0];
		} elseif ( $tabpanel_props['rwgps_pageid'] ) {
			$tabpanel_props['iconalt']      = 'Map';
			$tabpanel_props['iconclass']    = $iconclassrwgps;
			$tabpanel_props['iframe_src']   = "//rwgps-embeds.com/routes/{$tabpanel_props['rwgps_pageid']}/embed";
			$tabpanel_props['iframe_title'] = 'Ride With GPS map viewer';
		} elseif ( $tabpanel_props['soundcloud_pageid'] && $tabpanel_props['soundcloud_trackid'] ) {
			$tabpanel_props['iconalt']      = 'Audio';
			$tabpanel_props['iconclass']    = $iconclasssoundcloud;
			$tabpanel_props['iframe_src']   = "//w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/{$tabpanel_props['soundcloud_trackid']}?auto_play={$tabpanel_props['autoplay']}&hide_related=true&show_comments=false&show_user=false&show_reposts=false&visual=true";
			$tabpanel_props['iframe_title'] = 'SoundCloud player';
		} elseif ( $tabpanel_props['vimeo_pageid'] ) {
			$tabpanel_props['iconalt']           = 'Video';
			$tabpanel_props['iconclass']         = $iconclassvimeo;
			$tabpanel_props['iframe_src']        = "//player.vimeo.com/video/{$tabpanel_props['vimeo_pageid']}?api=false&autopause={$tabpanel_props['autopause']}&autoplay={$tabpanel_props['autoplay']}&byline=false&loop=false&portrait=false&title=false&xhtml=false";
			$tabpanel_props['iframe_title']      = 'Vimeo player';
			$tabpanel_props['iframe_fullscreen'] = 'true';
		} else {
			$tabpanel_props['iconalt']            = 'Image';
			$tabpanel_props['iconclass']          = $iconclassimage;
			$tabpanel_props['image']              = wp_get_attachment_image( $att_id, $tabpanelimagesize, false, '' );
			$tabpanel_props['image_src']          = wp_get_attachment_image_src( $att_id, $tabpanelimagesize )[0];
			$tabpanel_props['image_expanded_src'] = wp_get_attachment_image_src( $att_id, $tabpanelimagesizeexpanded )[0];
		}

		return $tabpanel_props;
	}

	/**
	 * Group: Renderers
	 * _____________________________________
	 */

	/**
	 * Method: render_html
	 *
	 * This is better than getting child nodes because WP shortcodes aren't HTML elements.
	 *
	 * Parameters:
	 *   $n - DOM node
	 *   $include_target_tag - whether to include the element tag in the output
	 *
	 * Returns:
	 *   $html - HTML
	 *
	 * See:
	 * <https://stackoverflow.com/a/53740544/6850747>
	 */
	public function render_html( DOMNode $n, $include_target_tag = false ) : string {
		$dom = new DOMDocument();
		$dom->appendChild( $dom->importNode( $n, true ) ); // $deep.
		$html = trim( $dom->saveHTML() );

		if ( $include_target_tag ) {
			return $html;
		}

		return preg_replace( '@^<' . $n->nodeName . '[^>]*>|</'. $n->nodeName . '>$@', '', $html ); // phpcs:ignore
	}

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

		// init
		// from Plugin.php + extra dependencies.
		wp_enqueue_script( $this->get_prefix(),
			$this->get_url() . 'js/frontend-es5.js',
			array(
				// load these registered dependencies first:.
				'jquery',
				'uri',
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
	 * Method: render_tablist_start
	 *
	 * Parameters:
	 *   $gallery_div - String
	 *   $gallery_props - Array
	 *   $tabkeyboardhinttextlines - Array
	 *   $tablistlabel - String
	 *   $tablisttitletag - String
	 *   $tablistclass - String
	 *
	 * Returns:
	 *   $html - HTML
	 */
	public function render_tablist_start( string $gallery_div, Array $gallery_props, Array $tabkeyboardhinttextlines, string $tablistlabel, string $tablisttitletag, string $tablistclass ) : string {
		$attrs   = " role='tablist'";
		$hint_id = '';

		if ( count( $tabkeyboardhinttextlines ) > 0 ) {
			$hint_id .= " {$gallery_props['tabhint_id']}";
		}

		if ( '' !== $tablistlabel ) {
			$attrs .= " aria-label='{$tablistlabel}'";
		} elseif ( '' !== $tablisttitletag ) {
			$attrs .= " aria-labelledby='{$gallery_props['id']}-tablist-title{$hint_id}'";
		}

		$gallery_div = str_replace( '>', $attrs . '>', $gallery_div );
		$gallery_div = str_replace( "class='gallery", "class='gallery " . $tablistclass, $gallery_div );

		return $gallery_div;
	}

	/**
	 * Method: render_tablist_title
	 *
	 * Parameters:
	 *   $gallery_props - Array
	 *   $tablisttitleclass - string
	 *   $iconclassmousehint - string
	 *   $tablisttitletag - string
	 *   $tablisttitle - string
	 *
	 * Returns:
	 *   $html - HTML
	 */
	public function render_tablist_title( Array $gallery_props, string $tablisttitleclass, string $iconclassmousehint, string $tablisttitletag, string $tablisttitle ) : string {
		$html = '';

		if ( '' !== $tablisttitletag ) {
			$tablisttitle_attrs = " id='{$gallery_props['tablisttitle_id']}'";
			$tabmousehint_icon  = '';

			if ( '' !== $tablisttitleclass ) {
				$tablisttitle_attrs .= " class='{$tablisttitleclass}'";
			}

			if ( '' !== $iconclassmousehint ) {
				$tabmousehint_icon .= "<span class='{$iconclassmousehint}' aria-hidden='true'></span>";
			}

			$html .= "<{$tablisttitletag}{$tablisttitle_attrs}>{$tablisttitle}{$tabmousehint_icon}</{$tablisttitletag}>";
		}

		return $html;
	}

	/**
	 * Method: render_tab
	 *
	 * Parameters:
	 *   $tabpanel_props - Array
	 *   $count - number
	 *   $tabclass - string
	 *   $tablinerclass - string
	 *   $tabtag - string
	 *   $tablinertag - string
	 *
	 * Returns:
	 *   $html - HTML
	 */
	public function render_tab( Array $tabpanel_props, int $count, string $tabclass, string $tablinerclass, string $tabtag, string $tablinertag ) : string {
		$html = '';

		$tab_attrs  = " role='tab'";
		$tab_attrs .= " class='gallery-item {$tabclass}'";
		$tab_attrs .= " aria-controls='{$tabpanel_props['tabpanel_id']}'";
		$tab_attrs .= " id='{$tabpanel_props['tab_id']}'";
		$tab_attrs .= " data-kh-proxy='selectFocussed'";
		$tab_attrs .= ' disabled';

		if ( 1 === $count ) {
			$tab_attrs .= " aria-selected='true'";
			$tab_attrs .= " tabindex='0'";
		} else {
			$tab_attrs .= " aria-selected='false'";
			$tab_attrs .= " tabindex='-1'";
		}

		/**
		 * START TAB LINER
		 */

		$tabliner_attrs = '';
		$tabicon_attrs  = '';

		if ( '' !== $tablinerclass ) {
			$tabicon_attrs .= " class='{$tabpanel_props['iconclass']}'";
			$tabicon_attrs .= " aria-label='{$tabpanel_props['iconalt']}. '";

			$tabliner_attrs .= " class='{$tablinerclass}'";
		}

		$html .= "<{$tabtag}{$tab_attrs}>";

		if ( '' !== $tablinertag ) {
			$html .= "<{$tablinertag} {$tabliner_attrs}>";
			$html .= "<span{$tabicon_attrs}></span>";
		}

		/**
		 * START TAB IMAGE
		 */

		$html .= $tabpanel_props['thumbnail'];

		/**
		 * END TAB IMAGE
		 */

		if ( '' !== $tablinertag ) {
			$html .= "</{$tablinertag}>";
		}

		/**
		 * END TAB LINER
		 */

		$html .= "</{$tabtag}>";

		return $html;
	}

	/**
	 * Method: render_tab_hint
	 *
	 * Parameters:
	 *   $gallery_props - Array
	 *   $tabpanel_props - Array
	 *   $tabkeyboardhintclass - string
	 *   $tabkeyboardhintlinerclass - string
	 *   $iconclasskeyboardhint - string
	 *   $tabkeyboardtitletag - string
	 *   $tabkeyboardtitleclass - string
	 *   $tabkeyboardtitletext - string
	 *   $tabkeyboardhinttextlines - Array
	 *
	 * Returns:
	 *   $html - HTML
	 */
	public function render_tab_hint( Array $gallery_props, Array $tabpanel_props, string $tabkeyboardhintclass, string $tabkeyboardhintlinerclass, string $iconclasskeyboardhint, string $tabkeyboardtitletag, string $tabkeyboardtitleclass, string $tabkeyboardtitletext, Array $tabkeyboardhinttextlines ) : string {
		$html = '';

		$tabkeyboardhint_attrs      = '';
		$tabkeyboardhintliner_attrs = '';
		$tabkeyboardhint_icon       = '';

		if ( '' !== $tabkeyboardhintclass ) {
			$tabkeyboardhint_attrs .= " class='{$tabkeyboardhintclass}'";
			$tabkeyboardhint_attrs .= " id='{$gallery_props['tabhint_id']}'";
		}

		if ( '' !== $tabkeyboardhintlinerclass ) {
			$tabkeyboardhintliner_attrs .= " class='{$tabkeyboardhintlinerclass}'";
		}

		if ( '' !== $iconclasskeyboardhint ) {
			$tabkeyboardhint_icon .= "<span class='{$iconclasskeyboardhint}' aria-hidden='true'></span>";
		}

		if ( count( $tabkeyboardhinttextlines ) > 0 ) {
			$html .= "<div{$tabkeyboardhint_attrs}>";
			$html .= "<div{$tabkeyboardhintliner_attrs}>";
			$html .= "<{$tabkeyboardtitletag} class='{$tabkeyboardtitleclass}'>{$tabkeyboardtitletext} {$tabkeyboardhint_icon}</{$tabkeyboardtitletag}>";
			$html .= '<ul>';

			foreach ( $tabkeyboardhinttextlines as $tabkeyboardhinttextline ) {
				$html .= "<li>{$tabkeyboardhinttextline}</li>";
			}

			$html .= '</ul>';
			$html .= '</div>';
			$html .= '</div>';
		}

		return $html;
	}

	/**
	 * Method: render_tabpanel_iframe
	 *
	 * Parameters:
	 *   $tabpanel_props - Array
	 *
	 * Returns:
	 *   $html - HTML
	 */
	public function render_tabpanel_iframe( Array $tabpanel_props ) : string {
		$html = '';

		// TODO replace with inline icon.
		$embed_attrs   = " class='wpdtrt-gallery-viewer__iframe-wrapper {$tabpanel_props['iconclass']}'";
		$iframe_attrs  = " aria-describedby='{$tabpanel_props['caption_id']}'";
		$iframe_attrs .= " class='wpdtrt-gallery-viewer__iframe'";
		$iframe_attrs .= " id='{$tabpanel_props['media_id']}'";

		if ( $tabpanel_props['iframe'] ) {
			$iframe_attrs .= " src='{$tabpanel_props['iframe_src']}'";
			$iframe_attrs .= " title='{$tabpanel_props['iframe_title']}. '";

			if ( '' !== $tabpanel_props['iframe_fullscreen'] ) {
				// adapted from https://appleple.github.io/modal-video/.
				$iframe_attrs .= ' allowfullscreen';
			}
		}

		$html .= "<div{$embed_attrs}>";
		$html .= "<iframe{$iframe_attrs}></iframe>";
		$html .= '</div>';

		return $html;
	}

	/**
	 * Method: render_tabpanel_caption
	 *
	 * Parameters:
	 *   $tabpanel_props - Array
	 *   $tabpanelcaptionclass
	 *   $tabpanelcaptionlinerclass
	 *   $tabpanelcaptiontag
	 *
	 * Returns:
	 *   $html - HTML
	 */
	public function render_tabpanel_caption( Array $tabpanel_props, string $tabpanelcaptionclass, string $tabpanelcaptionlinerclass, string $tabpanelcaptiontag ) : string {
		$html = '';

		$enlargementcaption_attrs      = '';
		$enlargementcaptionliner_attrs = '';

		if ( '' !== $tabpanelcaptionclass ) {
			$enlargementcaption_attrs .= " class='{$tabpanelcaptionclass}'";
		}

		if ( $tabpanel_props['iframe'] ) {
			$enlargementcaption_attrs .= " id='{$tabpanel_props['caption_id']}'";
			$tabpanelcaptiontag        = 'div';
		}

		if ( '' !== $tabpanelcaptionlinerclass ) {
			$enlargementcaptionliner_attrs .= " class='{$tabpanelcaptionlinerclass}'";
		}

		$html .= "<{$tabpanelcaptiontag}{$enlargementcaption_attrs}>";
		$html .= "<div{$enlargementcaptionliner_attrs}>";

		if ( '' !== $tabpanel_props['iconclass'] ) {
			$html .= "<span class='{$tabpanel_props['iconclass']}'></span>";
		}

		$html .= wptexturize( $tabpanel_props['post_excerpt'] );
		$html .= '</div>';
		$html .= "</{$tabpanelcaptiontag}>";

		return $html;
	}

	/**
	 * Method: render_tabpanel_image
	 *
	 * Parameters:
	 *   $tabpanel_props - Array
	 *   $tabpanelimageclass - string
	 *   $tabpanelimagetag - string
	 *
	 * Returns:
	 *   $html - HTML
	 */
	public function render_tabpanel_image( Array $tabpanel_props, string $tabpanelimageclass, string $tabpanelimagetag ) : string {
		$html        = '';
		$image       = $tabpanel_props['image'];
		$image_attrs = " id='{$tabpanel_props['media_id']}'";

		$tabpanelimage_attrs = '';

		if ( '' !== $tabpanelimageclass ) {
			// TODO replace with inline icon.
			$tabpanelimage_attrs .= " class='{$tabpanelimageclass} {$tabpanel_props['iconclass']}'";
		}

		$html .= "<{$tabpanelimagetag}{$tabpanelimage_attrs}>";

		// inject ID for aria-controls, as we don't know this when filter_image_attributes is applied.
		$html .= str_replace( '<img', "<img{$image_attrs}", $image );

		// $html .= preg_replace( '/src="[^"]*"/', 'src=""', $image ); // TODO: lazy loading // phpcs-disable
		$html .= "</{$tabpanelimagetag}>";

		return $html;
	}

	/**
	 * Method: render_tabpanel_title
	 *
	 * Parameters:
	 *   $titleclass - string
	 *   $titleextraattrs - string
	 *   $titletag - string
	 *   $title - string
	 *   $titleextrahtml - string
	 *
	 * Returns:
	 *   $html - HTML
	 */
	public function render_tabpanel_title( string $titleclass, string $titleextraattrs, string $titletag, string $title, string $titleextrahtml ) : string {
		$html = '';

		if ( '' !== $titletag ) {
			$title_wrapper_attrs = '';
			$title_attrs         = '';

			if ( '' !== $titleclass ) {
				$title_wrapper_attrs .= " class='{$titleclass}'";
			}

			if ( '' !== $titleextraattrs ) {
				$title_attrs .= $titleextraattrs;
			}

			$html .= "<div{$title_wrapper_attrs}>";
			$html .= "<{$titletag}{$title_attrs}>";
			$html .= $title;

			if ( '' !== $titleextrahtml ) {
				$html .= $titleextrahtml;
			}

			$html .= "</{$titletag}>";
			$html .= '</div>';
		}

		return $html;
	}

	/**
	 * Group: Filters
	 * _____________________________________
	 */

	/**
	 * Method: filter_gallery_html
	 *
	 * Set attachment defaults.
	 *
	 * Note:
	 * - Duplicated from wp-includes/media.php and enhanced
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
	public function filter_gallery_html( $output = '', $attr = null, $instance = null ) {
		$post = get_post();

		static $instance = 0;
		$instance++;

		if ( ! empty( $attr['ids'] ) ) {
			// 'ids' is explicitly ordered, unless you specify otherwise.
			if ( empty( $attr['orderby'] ) ) {
				$attr['orderby'] = 'post__in';
			}
			$attr['include'] = $attr['ids'];
		}

		/**
		 * Filters the default gallery shortcode output.
		 *
		 * This will be used instead of generating
		 * the default gallery template.
		 *
		 * @since 2.5.0
		 * @since 4.2.0 The `$instance` parameter was added.
		 *
		 * @see gallery_shortcode()
		 *
		 * @param string $output   The gallery output. Default empty.
		 * @param array  $attr     Attributes of the gallery shortcode.
		 * @param int    $instance Unique numeric ID of this gallery shortcode instance.
		 */
		$html5 = current_theme_supports( 'html5', 'gallery' );
		$atts  = shortcode_atts(
			array(
				'order'                     => 'ASC',
				'orderby'                   => 'menu_order ID',
				'id'                        => $post ? $post->ID : 0,
				'itemtag'                   => $html5 ? 'figure' : 'dl',
				'icontag'                   => $html5 ? 'div' : 'dt',
				'captiontag'                => $html5 ? 'figcaption' : 'dd',
				'columns'                   => 3,
				'size'                      => 'thumbnail',
				'include'                   => '',
				'exclude'                   => '',
				'link'                      => '',
				// additions:.
				'iconclassimage'            => '',
				'iconclasspanorama'         => '',
				'iconclassrwgps'            => '',
				'iconclasssoundcloud'       => '',
				'iconclasskeyboardhint'     => '',
				'iconclassmousehint'        => '',
				'iconclassrvimeo'           => '',
				'tabspatternclass'          => '',
				'tabclass'                  => '',
				'tabtag'                    => '',
				'tabkeyboardtitletag'       => '',
				'tabkeyboardtitleclass'     => '',
				'tabkeyboardtitletext'      => '',
				'tabkeyboardhintclass'      => '',
				'tabkeyboardhintlinerclass' => '',
				'tabkeyboardhinttextlines'  => [],
				'tablinerclass'             => '',
				'tablinertag'               => '',
				'tablistclass'              => '',
				'tablistlabel'              => '',
				'tablisttitle'              => '',
				'tablisttitleclass'         => '',
				'tablisttitletag'           => '',
				'tabpanelcaptionclass'      => '',
				'tabpanelcaptionlinerclass' => '',
				'tabpanelcaptiontag'        => $html5 ? 'figcaption' : 'dd',
				'tabpanelcontrolsclass'     => '',
				'tabpanelclass'             => '',
				'tabpanelimageclass'        => '',
				'tabpanelimagesize'         => '',
				'tabpanelimagesizeexpanded' => '',
				'tabpanelimagesizepanorama' => '',
				'tabpanelimagetag'          => $html5 ? 'div' : 'dt',
				'tabpanelitemclass'         => '',
				'tabpanelitemtag'           => $html5 ? 'figure' : 'dl',
				'tabpanelslinerclass'       => '',
				'tabpanelswrapperclass'     => '',
				'title'                     => '',
				'titleclass'                => '',
				'titleextraattrs'           => '',
				'titleextrahtml'            => '',
				'titletag'                  => 'h2',
				'usetabspattern'            => 'false',
			),
			$attr,
			'gallery'
		);

		$id = intval( $atts['id'] );

		if ( ! empty( $atts['include'] ) ) {
			$_attachments = get_posts( // phpcs:ignore
				array(
					'include'        => $atts['include'],
					'post_status'    => 'inherit',
					'post_type'      => 'attachment',
					'post_mime_type' => 'image',
					'order'          => $atts['order'],
					'orderby'        => $atts['orderby'],
				)
			);

			$attachments = array();
			foreach ( $_attachments as $key => $val ) {
				$attachments[ $val->ID ] = $_attachments[ $key ];
			}
		} elseif ( ! empty( $atts['exclude'] ) ) {
			$attachments = get_children( // phpcs:ignore
				array(
					'post_parent'    => $id,
					'exclude'        => $atts['exclude'],
					'post_status'    => 'inherit',
					'post_type'      => 'attachment',
					'post_mime_type' => 'image',
					'order'          => $atts['order'],
					'orderby'        => $atts['orderby'],
				)
			);
		} else {
			$attachments = get_children( // phpcs:ignore
				array(
					'post_parent'    => $id,
					'post_status'    => 'inherit',
					'post_type'      => 'attachment',
					'post_mime_type' => 'image',
					'order'          => $atts['order'],
					'orderby'        => $atts['orderby'],
				)
			);
		}

		if ( empty( $attachments ) ) {
			return '';
		}

		if ( is_feed() ) {
			$output = "\n";
			foreach ( $attachments as $att_id => $attachment ) {
				if ( ! empty( $atts['link'] ) ) {
					if ( 'none' === $atts['link'] ) {
						$output .= wp_get_attachment_image( $att_id, $atts['size'], false, $attr );
					} else {
						$output .= wp_get_attachment_link( $att_id, $atts['size'], false );
					}
				} else {
					$output .= wp_get_attachment_link( $att_id, $atts['size'], true );
				}
				$output .= "\n";
			}
			return $output;
		}

		$itemtag    = tag_escape( $atts['itemtag'] );
		$captiontag = tag_escape( $atts['captiontag'] );
		$icontag    = tag_escape( $atts['icontag'] );
		$valid_tags = wp_kses_allowed_html( 'post' );
		if ( ! isset( $valid_tags[ $itemtag ] ) ) {
			$itemtag = 'dl';
		}
		if ( ! isset( $valid_tags[ $captiontag ] ) ) {
			$captiontag = 'dd';
		}
		if ( ! isset( $valid_tags[ $icontag ] ) ) {
			$icontag = 'dt';
		}

		$columns   = intval( $atts['columns'] );
		$itemwidth = $columns > 0 ? floor( 100 / $columns ) : 100;
		$float     = is_rtl() ? 'right' : 'left';

		// switch for wai-aria tabs pattern.
		$usetabspattern = filter_var( $atts['usetabspattern'], FILTER_VALIDATE_BOOLEAN );

		// tabspattern - wraps tablist and tabpanels.
		$tabspatternclass = $this->helper_sanitize_html_classes( $atts['tabspatternclass'] );

		// tabpanels wrapper - wraps all tabpanel items.
		$tabpanelswrapperclass = $this->helper_sanitize_html_classes( $atts['tabpanelswrapperclass'] );

		// icon classes.
		$iconclassimage        = $this->helper_sanitize_html_classes( $atts['iconclassimage'] );
		$iconclasspanorama     = $this->helper_sanitize_html_classes( $atts['iconclasspanorama'] );
		$iconclassrwgps        = $this->helper_sanitize_html_classes( $atts['iconclassrwgps'] );
		$iconclasssoundcloud   = $this->helper_sanitize_html_classes( $atts['iconclasssoundcloud'] );
		$iconclasskeyboardhint = $this->helper_sanitize_html_classes( $atts['iconclasskeyboardhint'] );
		$iconclassmousehint    = $this->helper_sanitize_html_classes( $atts['iconclassmousehint'] );
		$iconclassvimeo        = $this->helper_sanitize_html_classes( $atts['iconclassrvimeo'] );

		// tabpanels title - child of tabpanels wrapper.
		$title           = esc_html( $atts['title'] );
		$titleclass      = $this->helper_sanitize_html_classes( $atts['titleclass'] );
		$titleextraattrs = wp_kses_post( $atts['titleextraattrs'] );
		$titleextrahtml  = wp_kses_post( $atts['titleextrahtml'] );
		$titletag        = tag_escape( $atts['titletag'] );

		// tabpanels liner - child of tabpanels wrapper.
		$tabpanelslinerclass = $this->helper_sanitize_html_classes( $atts['tabpanelslinerclass'] );

		// tabpanel - wraps the tabpanel image.
		$tabpanelclass = $this->helper_sanitize_html_classes( $atts['tabpanelclass'] );

		// tabpanel item.
		$tabpanelitemclass = $this->helper_sanitize_html_classes( $atts['tabpanelitemclass'] );
		$tabpanelitemtag   = tag_escape( $atts['tabpanelitemtag'] );

		if ( ! isset( $valid_tags[ $tabpanelitemtag ] ) ) {
			$tabpanelitemtag = 'dl';
		}

		// tabpanel image.
		$tabpanelimageclass        = $this->helper_sanitize_html_classes( $atts['tabpanelimageclass'] );
		$tabpanelimagesize         = esc_html( $atts['tabpanelimagesize'] );
		$tabpanelimagesizeexpanded = esc_html( $atts['tabpanelimagesizeexpanded'] );
		$tabpanelimagesizepanorama = esc_html( $atts['tabpanelimagesizepanorama'] );
		$tabpanelimagetag          = tag_escape( $atts['tabpanelimagetag'] );

		if ( ! isset( $valid_tags[ $tabpanelimagetag ] ) ) {
			$tabpanelimagetag = 'dt';
		}
		// tabpanel caption - output below large image.
		$tabpanelcaptionclass = $this->helper_sanitize_html_classes( $atts['tabpanelcaptionclass'] );
		$tabpanelcaptiontag   = tag_escape( $atts['tabpanelcaptiontag'] );

		if ( ! isset( $valid_tags[ $tabpanelcaptiontag ] ) ) {
			$tabpanelcaptiontag = 'dd';
		}

		// tabpanel caption liner - child of tabpanel caption.
		$tabpanelcaptionlinerclass = $this->helper_sanitize_html_classes( $atts['tabpanelcaptionlinerclass'] );

		// tablist - wraps all tabs.
		$tablistclass      = $this->helper_sanitize_html_classes( $atts['tablistclass'] );
		$tablistlabel      = esc_attr( $atts['tablistlabel'] );
		$tablisttitle      = esc_html( $atts['tablisttitle'] );
		$tablisttitleclass = $this->helper_sanitize_html_classes( $atts['tablisttitleclass'] );
		$tablisttitletag   = tag_escape( $atts['tablisttitletag'] );

		// tab - wraps thumbnail/icon.
		$tabclass = $this->helper_sanitize_html_classes( $atts['tabclass'] );
		$tabtag   = tag_escape( $atts['tabtag'] );

		$tabkeyboardtitletag   = tag_escape( $atts['tabkeyboardtitletag'] );
		$tabkeyboardtitleclass = $this->helper_sanitize_html_classes( $atts['tabkeyboardtitleclass'] );
		$tabkeyboardtitletext  = esc_html( $atts['tabkeyboardtitletext'] );

		$tabkeyboardhintclass      = $this->helper_sanitize_html_classes( $atts['tabkeyboardhintclass'] );
		$tabkeyboardhintlinerclass = $this->helper_sanitize_html_classes( $atts['tabkeyboardhintlinerclass'] );
		$tabkeyboardhinttextlines  = [];
		$loopindex                 = 0;

		foreach ( $atts['tabkeyboardhinttextlines'] as $tabkeyboardhinttextline ) {
			$tabkeyboardhinttextlines[ $loopindex ] = esc_html( $tabkeyboardhinttextline );

			$loopindex++;
		}

		// tab liner - child of tab.
		$tablinerclass = $this->helper_sanitize_html_classes( $atts['tablinerclass'] );
		$tablinertag   = tag_escape( $atts['tablinertag'] );

		$selector = "gallery-{$instance}";

		$gallery_style = '';

		/**
		 * Filters whether to print default gallery styles.
		 *
		 * @since 3.1.0
		 *
		 * @param bool $print Whether to print default gallery styles.
		 *                    Defaults to false if the theme supports HTML5 galleries.
		 *                    Otherwise, defaults to true.
		 */
		if ( apply_filters( 'use_default_gallery_style', ! $html5 ) ) {
			$type_attr = current_theme_supports( 'html5', 'style' ) ? '' : ' type="text/css"';

			$gallery_style = "
			<style{$type_attr}>
				#{$selector} {
					margin: auto;
				}
				#{$selector} .gallery-item {
					float: {$float};
					margin-top: 10px;
					text-align: center;
					width: {$itemwidth}%;
				}
				#{$selector} img {
					border: 2px solid #cfcfcf;
				}
				#{$selector} .gallery-caption {
					margin-left: 0;
				}
				/* see gallery_shortcode() in wp-includes/media.php */
			</style>\n\t\t";
		}

		$size_class = sanitize_html_class( $atts['size'] );
		$output     = '';

		/**
		 * START TABS PATTERN
		 */

		if ( $usetabspattern ) {
			$gallery_props                    = [];
			$gallery_props['id']              = "galleryid-{$instance}";
			$gallery_props['tabhint_id']      = "galleryid-{$instance}-tabhint";
			$gallery_props['tablisttitle_id'] = "galleryid-{$instance}-tablist-title";
			$count                            = 0;
			$tabpanels_props                  = [];

			foreach ( $attachments as $att_id => $attachment ) {
				++$count;
				$tabpanels_props[] = $this->set_tabpanel_props( $gallery_props, $att_id, $atts, $count, $iconclassimage, $iconclasspanorama, $iconclassrwgps, $iconclasssoundcloud, $iconclassvimeo, $tabpanelimagesize, $tabpanelimagesizeexpanded, $tabpanelimagesizepanorama, $attachment->post_excerpt );
			}

			$component_attrs = '';

			if ( '' !== $tabspatternclass ) {
				$component_attrs .= " class='{$tabspatternclass}'";
				$component_attrs .= " data-enabled='true'";
				$component_attrs .= " data-expanded='false'";
				$component_attrs .= " data-expanded-locked='false'";
				$component_attrs .= " data-expanded-user='false'";
			}

			$output .= "<div{$component_attrs}>\n";

			$output .= $this->render_tabpanel_title( $titleclass, $titleextraattrs, $titletag, $title, $titleextrahtml ) . "\n";
		}

		/**
		 * START TABLIST
		 */

		$gallery_div = "<div id='$selector' class='gallery gallery-columns-{$columns} gallery-size-{$size_class}'>";

		/**
		 * Filters the default gallery shortcode CSS styles.
		 *
		 * @since 2.5.0
		 *
		 * @param string $gallery_style Default CSS styles and opening HTML div container
		 *                              for the gallery shortcode output.
		 *
		 * Note:
		 * - Not used for wpdtrt-gallery enhancements as it discards the useful $id, $columns, $size_class attributes
		 */
		if ( $usetabspattern ) {
			$output .= $this->render_tablist_start( $gallery_div, $gallery_props, $tabkeyboardhinttextlines, $tablistlabel, $tablisttitletag, $tablistclass ) . "\n";
		} else {
			$output .= apply_filters( 'gallery_style', $gallery_style . $gallery_div ) . "\n";
		}

		$count = 0;
		$i     = 0;

		foreach ( $attachments as $att_id => $attachment ) {

			++$count;

			$attr = ( $attachment->post_excerpt && ! $usetabspattern ) ? array( 'aria-describedby' => "$selector-$att_id" ) : '';

			if ( ! empty( $atts['link'] ) && 'file' === $atts['link'] ) {
				$image_output = wp_get_attachment_link( $att_id, $atts['size'], false, false, false, $attr );
			} elseif ( ! empty( $atts['link'] ) && 'none' === $atts['link'] && $usetabspattern ) {
				$image_output = '';
			} elseif ( ! empty( $atts['link'] ) && 'none' === $atts['link'] ) {
				$image_output = wp_get_attachment_image( $att_id, $atts['size'], false, $attr );
			} else {
				$image_output = wp_get_attachment_link( $att_id, $atts['size'], true, false, false, $attr );
			}

			$image_meta = wp_get_attachment_metadata( $att_id );

			$orientation = '';

			if ( isset( $image_meta['height'], $image_meta['width'] ) ) {
				$orientation = ( $image_meta['height'] > $image_meta['width'] ) ? 'portrait' : 'landscape';
			}

			if ( $itemtag && ! $usetabspattern ) {
				$output .= "<{$itemtag} class='gallery-item'>";
			}

			if ( $icontag && ! $usetabspattern ) {
				$output .= "<{$icontag} class='gallery-icon {$orientation}'>";
			}

			if ( ! $usetabspattern ) {
				$output .= $image_output;
			}

			if ( $icontag && ! $usetabspattern ) {
				$output .= "</{$icontag}>";
			}

			if ( $captiontag && trim( $attachment->post_excerpt ) && ! $usetabspattern ) {
				$output .= "
					<{$captiontag} class='wp-caption-text gallery-caption' id='$selector-$att_id'>
					" . wptexturize( $attachment->post_excerpt ) . "
					</{$captiontag}>";
			}

			if ( $itemtag && ! $usetabspattern ) {
				$output .= "</{$itemtag}>";
			}

			if ( ! $html5 && $columns > 0 && 0 === ++$i % $columns && ! $usetabspattern ) { // phpcs:ignore
				$output .= '<br style="clear: both" />';
			}
		}

		if ( $usetabspattern ) {
			$output .= $this->render_tablist_title( $gallery_props, $tablisttitleclass, $iconclassmousehint, $tablisttitletag, $tablisttitle );
			$count   = 0;

			foreach ( $tabpanels_props as $tabpanel_props ) {
				++$count;
				$output .= $this->render_tab( $tabpanel_props, $count, $tabclass, $tablinerclass, $tabtag, $tablinertag );
			}

			$output .= $this->render_tab_hint( $gallery_props, $tabpanel_props, $tabkeyboardhintclass, $tabkeyboardhintlinerclass, $iconclasskeyboardhint, $tabkeyboardtitletag, $tabkeyboardtitleclass, $tabkeyboardtitletext, $tabkeyboardhinttextlines );
		}

		$output .= '</div>';

		/**
		 * END TABLIST
		 */

		/**
		 * START TABPANELS WRAPPER
		 */
		if ( $usetabspattern ) {

			$tabpanelswrapper_attrs = '';
			$tabpanelsliner_attrs   = '';

			if ( '' !== $tabpanelswrapperclass ) {
				$tabpanelswrapper_attrs .= " class='{$tabpanelswrapperclass}'";
			}

			$output .= "<div${tabpanelswrapper_attrs}>\n";

			/**
			 * START TABPANEL LINER
			 */

			if ( '' !== $tabpanelslinerclass ) {
				$tabpanelsliner_attrs .= " class='{$tabpanelslinerclass}'";
			}

			$output .= "<div{$tabpanelsliner_attrs}>\n";

			$count     = 0;
			$i         = 0;
			$parent_id = $id;

			foreach ( $tabpanels_props as $tabpanel_props ) {
				$att_id = $tabpanel_props['att_id'];

				/**
				 * START TABPANEL
				 */

				++$count;

				$image_meta = wp_get_attachment_metadata( $att_id );

				// $image_size_mobile        = 'wpdtrt-gallery-mobile'; // TODO not implemented, needs enquire.js.
				//
				// $orientation  = '';
				// if ( isset( $image_meta['height'], $image_meta['width'] ) ) {
				// $orientation = ( $image_meta['height'] > $image_meta['width'] ) ? 'portrait' : 'landscape';
				// }
				// .
				$tabpanel_attrs  = " role='tabpanel'";
				$tabpanel_attrs .= " id='{$tabpanel_props['tabpanel_id']}'";
				$tabpanel_attrs .= " aria-labelledby='{$tabpanel_props['tab_id']}'";
				$tabpanel_attrs .= " tabindex='0'";
				// $tabpanel_attrs .= " data-id='{$id}'";
				// .
				if ( $tabpanel_props['rwgps_pageid'] ) {
					$tabpanel_attrs .= " data-rwgps-pageid='true'";
				} elseif ( $tabpanel_props['soundcloud_pageid'] && $tabpanel_props['soundcloud_trackid'] ) {
					$tabpanel_attrs .= " data-soundcloud-pageid='true'";
					$tabpanel_attrs .= " data-soundcloud-trackid='true'";
				} elseif ( $tabpanel_props['vimeo_pageid'] ) {
					$tabpanel_attrs .= " data-vimeo-pageid='true'";
				} elseif ( '1' === $tabpanel_props['panorama'] ) {
					$tabpanel_attrs .= " data-src-panorama='{$tabpanel_props['image_src']}'";
					$tabpanel_attrs .= " data-panorama='true'";
				} else {
					$tabpanel_attrs .= " data-src-desktop='{$tabpanel_props['image_src']}'";
					$tabpanel_attrs .= " data-src-desktop-expanded='{$tabpanel_props['image_expanded_src']}'";
					// $img_src_mobile   = wp_get_attachment_image_src( $att_id, $image_size_mobile )[0];
					// $tabpanel_attrs  .= " data-src-mobile='{$img_src_mobile}'";
				}

				if ( '' !== $tabpanel_props['latitude'] ) {
					$tabpanel_attrs .= " data-latitude='{$tabpanel_props['latitude']}'";
				}

				if ( '' !== $tabpanel_props['longitude'] ) {
					$tabpanel_attrs .= " data-longitude='{$tabpanel_props['longitude']}'";
				}

				if ( $count > 1 ) {
					$tabpanel_attrs .= ' hidden';
				}

				if ( '' !== $tabpanelclass ) {
					$tabpanel_attrs .= " class='{$tabpanelclass}'";
				}

				$output .= "<div{$tabpanel_attrs}>\n";

				/**
				 * START TABPANEL ITEM
				 */

				$tabpanelitem_attrs = '';

				if ( '' !== $tabpanelitemclass ) {
					$tabpanelitem_attrs .= " class='{$tabpanelitemclass}'";
				}

				// override $atts.
				if ( $tabpanel_props['iframe'] ) {
					$tabpanelitemtag = 'div';
				}

				$output .= "<{$tabpanelitemtag}{$tabpanelitem_attrs}>\n";

				if ( $tabpanel_props['iframe'] ) {
					$output .= $this->render_tabpanel_iframe( $tabpanel_props ) . "\n";
				} else {
					$output .= $this->render_tabpanel_image( $tabpanel_props, $tabpanelimageclass, $tabpanelimagetag ) . "\n";
				}

				if ( '' !== $captiontag && $tabpanel_props['post_excerpt'] ) {
					$output .= $this->render_tabpanel_caption( $tabpanel_props, $tabpanelcaptionclass, $tabpanelcaptionlinerclass, $tabpanelcaptiontag ) . "\n";
				}

				$output .= "</{$tabpanelitemtag}>\n";

				/**
				 * END TABPANEL ITEM
				 */

				$output .= '</div>' . "\n";

				/**
				 * END TABPANEL
				 */
			}

			$output .= '</div>' . "\n";

			/**
			 * END TABPANEL LINER
			 */

			$output .= '</div>' . "\n";
		}

		/**
		 * END TABPANELS WRAPPER
		 */

		$output .= '</div>' . "\n";

		/**
		 * END TABS PATTERN
		 */

		return $output;
	}

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
		$result['link']    = 'none';

		// additions:.
		$result['iconclasssoundcloud']       = 'wpdtrt-gallery-icon-soundcloud';
		$result['iconclassimage']            = 'wpdtrt-gallery-icon-image';
		$result['iconclassrwgps']            = 'wpdtrt-gallery-icon-map';
		$result['iconclasspanorama']         = 'wpdtrt-gallery-icon-panorama';
		$result['iconclasskeyboardhint']     = 'wpdtrt-gallery-icon-keyboard-o';
		$result['iconclassmousehint']        = 'wpdtrt-gallery-icon-hand-pointer-o';
		$result['iconclassrvimeo']           = 'wpdtrt-gallery-icon-vimeo';
		$result['tabclass']                  = 'wpdtrt-gallery-gallery__tab';
		$result['tabtag']                    = 'button';
		$result['tabkeyboardtitletag']       = 'h4';
		$result['tabkeyboardtitleclass']     = 'wpdtrt-gallery-gallery__header';
		$result['tabkeyboardtitletext']      = 'Keyboard instructions';
		$result['tabkeyboardhintclass']      = 'wpdtrt-gallery-gallery__tab-hint';
		$result['tabkeyboardhintlinerclass'] = 'wpdtrt-gallery-gallery__tab-hint-liner';
		$result['tabkeyboardhinttextlines']  = [ 'Navigate with: LEFT + RIGHT arrows', 'Select with: ENTER', 'Enlarge with: TAB then ENTER' ];
		$result['tablinerclass']             = 'wpdtrt-gallery-gallery__tab-liner';
		$result['tablinertag']               = 'span';
		$result['tablistclass']              = 'wpdtrt-gallery-gallery';
		$result['tablisttitle']              = 'Select a photo to display';
		$result['tablisttitleclass']         = 'wpdtrt-gallery-gallery__header';
		$result['tablisttitletag']           = 'h3';
		$result['tabpanelclass']             = 'wpdtrt-gallery-viewer__tabpanel';
		$result['tabpanelcaptionclass']      = 'wpdtrt-gallery-viewer__caption-wrapper';
		$result['tabpanelcaptionlinerclass'] = 'wpdtrt-gallery-viewer__caption';
		$result['tabpanelcontrolsclass']     = 'wpdtrt-gallery-viewer__controls';
		$result['tabpanelimageclass']        = 'wpdtrt-gallery-viewer__img-wrapper';
		$result['tabpanelimagesize']         = 'wpdtrt-gallery-desktop';
		$result['tabpanelimagesizeexpanded'] = 'wpdtrt-gallery-desktop-expanded';
		$result['tabpanelimagesizepanorama'] = 'wpdtrt-gallery-panorama';
		$result['tabpanelitemclass']         = 'wpdtrt-gallery-viewer__liner';
		$result['tabpanelslinerclass']       = 'wpdtrt-gallery-viewer__wrapper';
		$result['tabpanelswrapperclass']     = 'wpdtrt-gallery-viewer';
		$result['tabspatternclass']          = 'wpdtrt-gallery';
		$result['titleclass']                = 'wpdtrt-gallery__header';
		$result['titletag']                  = 'h2';
		$result['usetabspattern']            = 'true';

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

		$exif = @exif_read_data( $file ); // phpcs:ignore

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
	 * Method: filter_content_inject_shortcode
	 *
	 * Automatically inject plugin shortcodes.
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
	 * <https://www.php.net/manual/en/class.domdocument.php>
	 * <https://davidwalsh.name/domdocument>
	 * <https://stackoverflow.com/questions/3577641/how-do-you-parse-and-process-html-xml-in-php>
	 * <https://stackoverflow.com/questions/7048080/ignore-specific-warnings-with-php-codesniffer>
	 * <https://stackoverflow.com/a/60673499/6850747>
	 * <https://github.com/Yoast/wordpress-seo/commit/6a6de3b07fd959cc53a102fd00b874e4d405a26d>
	 *
	 * Manual alternatives:
	 * --- php
	 * [wpdtrt_gallery]H2 heading text[/wpdtrt_gallery]
	 * ---
	 * --- php
	 * do_shortcode( '[wpdtrt_gallery]H2 heading text[/wpdtrt_gallery]' );
	 * ---
	 *
	 * TODO:
	 * - https://github.com/dotherightthing/wpdtrt-gallery/issues/81
	 */
	public function filter_content_inject_shortcode( string $content ) : string {
		// Prevent DOMDocument from raising warnings about invalid HTML.
		libxml_use_internal_errors( true );

		$dom = new DOMDocument();
		$dom->loadHTML( mb_convert_encoding( $content, 'HTML-ENTITIES', 'UTF-8' ) );

		// phpcs:disable WordPress.NamingConventions

		// Clear errors, so they aren't kept in memory.
		libxml_clear_errors();

		// DOMDocument doesn't support HTML5, so we use a div rather than section element.
		// These divs are now added by wpdtrt-anchorlinks.
		// They are the direct descendants of the WP 'content'.
		// They take the IDs required to highlight the 'active' anchor list link.
		$content_replacements = [];
		$headings             = $dom->getElementsByTagName( 'h2' );
		$sections             = [];

		foreach ( $headings as $heading ) {
			$div = $heading->parentNode;

			// class is added to h2 by wpdtrt-anchorlinks->filter_content_anchors().
			$is_section = 'wpdtrt-anchorlinks__section wpdtrt-anchorlinks__anchor' === $div->getAttribute( 'class' );

			if ( $is_section ) {
				$section                     = $div;
				$gallery                     = null;
				$gallery_shortcode           = '';
				$heading_data_anchorlinks_id = '';
				$heading_html                = '';
				$heading_text                = '';
				$section_attr                = ' data-wpdtrt-anchorlinks-controls="highlighting"';
				$section_class               = 'wpdtrt-gallery__section';
				$section_html                = '';
				$section_id                  = '';
				$section_inner_html          = $this->render_html( $section, false );
				$section_tabindex            = '';

				// check if $heading sibling is gallery shortcode.
				if ( null !== $heading->nextSibling ) {
					// Check node type
					// XML_TEXT_NODE = DOMText.
					// XML_ELEMENT_NODE = DOMElement.
					// See https://www.php.net/manual/en/class.domtext.php.
					// See https://www.php.net/manual/en/dom.constants.php.
					if ( XML_TEXT_NODE === $heading->nextSibling->nodeType ) {
						// unsure why additional sibling is required.
						$siblingText = $heading->nextSibling->wholeText;

						// substr would also work
						// but I'm concerned about leading whitespace.
						preg_match( '/\[gallery link="file" ids=/', $siblingText, $gallery_matches );

						if ( count( $gallery_matches ) > 0 ) {
							$gallery = $heading->nextSibling;
						}
					}
				}

				$heading_html                = $this->render_html( $heading, true );
				$heading_text                = $this->render_html( $heading, false );
				$heading_text                = explode( '<', $heading_text )[0];
				$heading_data_anchorlinks_id = $heading->getAttribute( 'data-anchorlinks-id' );
				$heading_anchor              = $heading->getElementsByTagName( 'a' );
				$anchor_html                 = '';

				if ( $heading_anchor->length > 0 ) {
					$heading_anchor_html = $this->render_html( $heading_anchor[0], true );
				}

				$section_class    = $section->getAttribute( 'class' ) . ' wpdtrt-gallery__section';
				$section_id       = $section->getAttribute( 'id' );
				$section_tabindex = $section->getAttribute( 'tabindex' );

				/**
				 * Rebuild the start of the sectioning element to add our own class and attributes.
				 */

				$section_attrs  = " class='{$section_class}'";
				$section_attrs .= $section_attr;

				if ( strlen( $section_id ) > 0 ) {
					$section_attrs .= ' id="' . $section_id . '"';
				}

				if ( strlen( $section_tabindex ) > 0 ) {
					$section_attrs .= ' tabindex="' . $section_tabindex . '"';
				}

				$section_html .= "<div{$section_attrs}>";

				if ( isset( $gallery ) ) {
					/**
					 * Remove existing gallery shortcode.
					 */

					$gallery_shortcode = $this->render_html( $gallery, true );

					if ( strlen( $gallery_shortcode ) > 0 ) {
						$section_inner_html = str_replace( $gallery_shortcode, '', $section_inner_html );
					}

					preg_match( '/\[gallery link="file" ids=/', $gallery->nodeValue, $gallery_matches );

					if ( count( $gallery_matches ) > 0 ) {
						/**
						 * Add custom attributes to gallery shortcode.
						 */

						$gallery_shortcode_attrs = '';

						if ( '' !== $heading_text ) {
							$gallery_shortcode_attrs .= " title='{$heading_text}'";
						}

						if ( '' !== $heading_data_anchorlinks_id ) {
							$title_extra_attrs        = ' data-anchorlinks-id="' . $heading_data_anchorlinks_id . '"';
							$title_extra_html         = $heading_anchor_html;
							$gallery_shortcode_attrs .= " titleextraattrs='{$title_extra_attrs}'";
							$gallery_shortcode_attrs .= " titleextrahtml='{$title_extra_html}'";
						}

						$gallery_shortcode = str_replace( ']', $gallery_shortcode_attrs . ']', $gallery_shortcode );

						/**
						 * Insert gallery shortcode before content.
						 */

						$section_html .= $gallery_shortcode; // this is the raw shortcode.

						// remove the old heading as it now appears in the gallery tabpanels area.
						$section_inner_html = str_replace( $heading_html, '', $section_inner_html );
					}
				} else {
					// inject heading shortcode
					// .
					// if there's no gallery, wrap heading in gallery heading shortcode.
					// headings are wrapped regardless of whether they precede galleries
					// to apply the gallery heading styling.
					$new_heading_html = '[wpdtrt_gallery_shortcode_heading]' . $heading_html . '[/wpdtrt_gallery_shortcode_heading]';

					$section_html      .= $new_heading_html;
					$section_inner_html = str_replace( $heading_html, '', $section_inner_html );
				}

				// wrap remaining content.
				$section_html .= '<div class="entry-content">';
				$section_html .= str_replace( '&nbsp;', ' ', $section_inner_html );
				$section_html .= '</div>';

				// end section.
				$section_html .= '</div>';

				// update output.
				$content_replacements[] = $section_html;
			}
		}

		// phpcs:enable WordPress.NamingConventions

		if ( count( $content_replacements ) > 0 ) {
			$content = '';

			foreach ( $content_replacements as $content_replacement ) {
				$content .= $content_replacement;
			}
		}

		return $content;
	}

	/**
	 * Method: filter_image_attributes
	 *
	 * Set image alt attribute.
	 *
	 * Parameters:
	 *   $atts - HTML image attributes
	 *   $attachment - WP_Post object for the attachment
	 *
	 * Returns:
	 *   $atts - (maybe) filtered HTML image attributes
	 *
	 * See:
	 * - <https://developer.wordpress.org/reference/hooks/wp_get_attachment_image_attributes/>
	 */
	public function filter_image_attributes( array $atts, WP_Post $attachment ) : array {
		$att_id             = $attachment->ID;
		$caption            = wp_get_attachment_caption( $att_id );
		$tab_img_class      = 'attachment-thumbnail size-thumbnail';
		$tabpanel_img_class = 'attachment-wpdtrt-gallery-desktop size-wpdtrt-gallery-desktop';

		/**
		 * Either tab or tabpanel image
		 */

		if ( $tab_img_class === $atts['class'] ) {
			$atts['alt'] = $caption;
		} elseif ( $tabpanel_img_class === $atts['class'] ) {
			// Fix for missing alt text.
			if ( '' === $atts['alt'] ) {
				if ( '' !== $caption ) {
					$atts['alt'] = $caption;
				}
			}
		}

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

	/**
	 * Method: helper_sanitize_html_classes
	 *
	 * Escape multiple HTML classes.
	 *
	 * Parameters:
	 *   $classes - Array|String, an array of classes or a string of them separated by a delimiter
	 *   $sep - String, class separator
	 *
	 * Returns:
	 *   $string - string
	 *
	 * See:
	 * - <https://developer.wordpress.org/reference/functions/sanitize_html_class/#comment-2084>
	 */
	public function helper_sanitize_html_classes( $classes, $sep = ' ' ) : string {
		$return = '';

		if ( ! is_array( $classes ) ) {
			$classes = explode( $sep, $classes );
		}

		if ( ! empty( $classes ) ) {
			foreach ( $classes as $class ) {
				$return .= sanitize_html_class( $class ) . ' ';
			}
		}

		// remove trailing space.
		$return = trim( $return );

		return $return;
	}
}
