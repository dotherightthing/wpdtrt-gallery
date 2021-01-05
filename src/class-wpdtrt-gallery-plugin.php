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
class WPDTRT_Gallery_Plugin extends DoTheRightThing\WPDTRT_Plugin_Boilerplate\r_1_7_6\Plugin {

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
				'iconclasstabhint'          => '',
				'iconclassrvimeo'           => '',
				'tabspatternclass'          => '',
				'tabclass'                  => '',
				'tabtag'                    => '',
				'tabhintclass'              => '',
				'tabhintlinerclass'         => '',
				'tabhintttext'              => '',
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
		$iconclassimage      = $this->helper_sanitize_html_classes( $atts['iconclassimage'] );
		$iconclasspanorama   = $this->helper_sanitize_html_classes( $atts['iconclasspanorama'] );
		$iconclassrwgps      = $this->helper_sanitize_html_classes( $atts['iconclassrwgps'] );
		$iconclasssoundcloud = $this->helper_sanitize_html_classes( $atts['iconclasssoundcloud'] );
		$iconclasstabhint    = $this->helper_sanitize_html_classes( $atts['iconclasstabhint'] );
		$iconclassvimeo      = $this->helper_sanitize_html_classes( $atts['iconclassrvimeo'] );

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
		$tabpanelimageclass = $this->helper_sanitize_html_classes( $atts['tabpanelimageclass'] );
		$tabpanelimagesize  = esc_html( $atts['tabpanelimagesize'] );
		$tabpanelimagetag   = tag_escape( $atts['tabpanelimagetag'] );

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

		$tabhintclass      = $this->helper_sanitize_html_classes( $atts['tabhintclass'] );
		$tabhintlinerclass = $this->helper_sanitize_html_classes( $atts['tabhintlinerclass'] );
		$tabhinttext       = esc_html( $atts['tabhinttext'] );

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
			$component_attrs = '';

			if ( '' !== $tabspatternclass ) {
				$component_attrs .= " class='{$tabspatternclass}'";
				$component_attrs .= " data-enabled='true'";
				$component_attrs .= " data-expanded='false'";
				$component_attrs .= " data-expanded-locked='false'";
				$component_attrs .= " data-expanded-user='false'";
			}

			$output .= "<div{$component_attrs}>";
		}

		/**
		 * START TITLE
		 */

		if ( $usetabspattern ) {
			if ( '' !== $titletag ) {
				$title_wrapper_attrs = '';
				$title_attrs         = '';

				if ( '' !== $titleclass ) {
					$title_wrapper_attrs .= " class='{$titleclass}'";
				}

				if ( '' !== $titleextraattrs ) {
					$title_attrs .= $titleextraattrs;
				}

				$output .= "<div{$title_wrapper_attrs}>";
				$output .= "<{$titletag}{$title_attrs}>";
				$output .= $title;

				if ( '' !== $titleextrahtml ) {
					$output .= $titleextrahtml;
				}

				$output .= "</{$titletag}>";
				$output .= '</div>';
			}
		}

		/**
		 * END TITLE
		 */

		/**
		 * START TABLIST
		 */

		$gallery_div = "<div id='$selector' class='gallery galleryid-{$id} gallery-columns-{$columns} gallery-size-{$size_class}'>";

		if ( $usetabspattern ) {
			$gallery_attrs = " role='tablist'";

			if ( '' !== $tablistlabel ) {
				$gallery_attrs .= " aria-label='{$tablistlabel}'";
			} elseif ( '' !== $tablisttitletag ) {
				$gallery_attrs .= " aria-labelledby='galleryid-{$id}-tablist-title'";
			}

			if ( '' !== $tabhinttext ) {
				$gallery_attrs .= " aria-describedby='galleryid-{$id}-tabhint'";
			}

			$gallery_div = str_replace( '>', $gallery_attrs . '>', $gallery_div );
			$gallery_div = str_replace( "class='gallery", "class='gallery " . $tablistclass, $gallery_div );
		}

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
		$output .= apply_filters( 'gallery_style', $gallery_style . $gallery_div );

		if ( $usetabspattern ) {
			if ( '' !== $tablisttitletag ) {
				$tablisttitle_attrs = " id='galleryid-{$id}-tablist-title'";

				if ( '' !== $tablisttitleclass ) {
					$tablisttitle_attrs .= " class='{$tablisttitleclass}'";
				}

				$output .= "<{$tablisttitletag}{$tablisttitle_attrs}>{$tablisttitle}</{$tablisttitletag}>";
			}
		}

		$count = 0;
		$i     = 0;

		foreach ( $attachments as $id => $attachment ) {

			++$count;

			$attr = ( trim( $attachment->post_excerpt ) && ! $usetabspattern ) ? array( 'aria-describedby' => "$selector-$id" ) : '';

			if ( ! empty( $atts['link'] ) && 'file' === $atts['link'] ) {
				$image_output = wp_get_attachment_link( $id, $atts['size'], false, false, false, $attr );
			} elseif ( ! empty( $atts['link'] ) && 'none' === $atts['link'] && $usetabspattern ) {

				/**
				 * START TAB
				 */

				$panorama           = get_post_meta( $id, 'wpdtrt_gallery_attachment_panorama', true ); // used for JS dragging.
				$rwgps_pageid       = get_post_meta( $id, 'wpdtrt_gallery_attachment_rwgps_pageid', true );
				$soundcloud_pageid  = get_post_meta( $id, 'wpdtrt_gallery_attachment_soundcloud_pageid', true ); // used for SEO.
				$soundcloud_trackid = get_post_meta( $id, 'wpdtrt_gallery_attachment_soundcloud_trackid', true ); // used for embed, see also http://stackoverflow.com/a/28182284.
				$vimeo_pageid       = get_post_meta( $id, 'wpdtrt_gallery_attachment_vimeo_pageid', true ); // used for embed.

				$tab_attrs  = " role='tab'";
				$tab_attrs .= " class='gallery-item {$tabclass}'";
				$tab_attrs .= " aria-controls='galleryid-{$id}-tabpanel-{$count}'";
				$tab_attrs .= " id='galleryid-{$id}-tab-{$count}'";
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
					if ( '1' === $panorama ) {
						$iconalt   = 'Panorama image';
						$iconclass = $iconclasspanorama;
					} elseif ( $rwgps_pageid ) {
						$iconalt   = 'Ride with GPS map';
						$iconclass = $iconclassrwgps;
					} elseif ( $soundcloud_pageid && $soundcloud_trackid ) {
						$iconalt   = 'Soundcloud audio';
						$iconclass = $iconclasssoundcloud;
					} elseif ( $vimeo_pageid ) {
						$iconalt   = 'Vimeo video';
						$iconclass = $iconclassvimeo;
					} else {
						$iconalt   = 'Image';
						$iconclass = $iconclassimage;
					}

					$tabicon_attrs .= " class='{$iconclass}'";
					$tabicon_attrs .= " aria-label='{$iconalt}'";

					$tabliner_attrs .= " class='{$tablinerclass}'";
				}

				$image_output = "<{$tabtag}{$tab_attrs}>";

				if ( '' !== $tablinertag ) {
					$image_output .= "<{$tablinertag} {$tabliner_attrs}>";
					$image_output .= "<span{$tabicon_attrs}></span>";
				}

				/**
				 * START TAB IMAGE
				 */

				$image_output .= wp_get_attachment_image( $id, $atts['size'], false, $attr );

				/**
				 * END TAB IMAGE
				 */

				if ( '' !== $tablinertag ) {
					$image_output .= "</{$tablinertag}>";
				}

				/**
				 * END TAB LINER
				 */

				$image_output .= "</{$tabtag}>";

				/**
				 * END TAB
				 */
			} elseif ( ! empty( $atts['link'] ) && 'none' === $atts['link'] ) {
				$image_output = wp_get_attachment_image( $id, $atts['size'], false, $attr );
			} else {
				$image_output = wp_get_attachment_link( $id, $atts['size'], true, false, false, $attr );
			}

			$image_meta = wp_get_attachment_metadata( $id );

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

			$output .= $image_output;

			if ( $icontag && ! $usetabspattern ) {
				$output .= "</{$icontag}>";
			}

			if ( $captiontag && trim( $attachment->post_excerpt ) && ! $usetabspattern ) {
				$output .= "
					<{$captiontag} class='wp-caption-text gallery-caption' id='$selector-$id'>
					" . wptexturize( $attachment->post_excerpt ) . "
					</{$captiontag}>";
			}

			if ( $itemtag && ! $usetabspattern ) {
				$output .= "</{$itemtag}>";
			}

			if ( ! $html5 && $columns > 0 && 0 === ++$i % $columns ) { // phpcs:ignore
				$output .= '<br style="clear: both" />';
			}
		}

		/**
		 * START TAB HINT
		 */

		if ( $usetabspattern ) {
			$tabhint_attrs       = '';
			$tabhint_liner_attrs = '';
			$tabhint_icon        = '';

			if ( '' !== $tabhintclass ) {
				$tabhint_attrs .= " class='{$tabhintclass}'";
				$tabhint_attrs .= " id='galleryid-{$id}-tabhint'";
			}

			if ( '' !== $tabhintlinerclass ) {
				$tabhint_liner_attrs .= " class='{$tabhintlinerclass}'";
			}

			if ( '' !== $iconclasstabhint ) {
				$tabhint_icon .= "<span class='{$iconclasstabhint}'></span>";
			}

			if ( '' !== $tabhinttext ) {
				$output .= "<p{$tabhint_attrs}><span{$tabhintliner_attrs}>{$tabhint_icon}{$tabhinttext}</span></p>";
			}
		}

		/**
		 * END TAB HINT
		 */

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

			$output .= "<div${tabpanelswrapper_attrs}>";

			/**
			 * START TABPANEL LINER
			 */

			if ( '' !== $tabpanelslinerclass ) {
				$tabpanelsliner_attrs .= " class='{$tabpanelslinerclass}'";
			}

			$output .= "<div{$tabpanelsliner_attrs}>";

			$count     = 0;
			$i         = 0;
			$parent_id = $id;

			foreach ( $attachments as $id => $attachment ) {
				/**
				 * START TABPANEL
				 */

				++$count;

				$image_output                = wp_get_attachment_image( $id, $atts['tabpanelimagesize'], false, $attr ); // TODO should this use a variable?.
				$image_meta                  = wp_get_attachment_metadata( $id );
				$image_size_desktop          = 'wpdtrt-gallery-desktop';
				$image_size_desktop_expanded = 'wpdtrt-gallery-desktop-expanded';
				$image_size_panorama         = 'wpdtrt-gallery-panorama';
				$panorama                    = get_post_meta( $id, 'wpdtrt_gallery_attachment_panorama', true ); // used for JS dragging.
				$rwgps_pageid                = get_post_meta( $id, 'wpdtrt_gallery_attachment_rwgps_pageid', true );
				$soundcloud_pageid           = get_post_meta( $id, 'wpdtrt_gallery_attachment_soundcloud_pageid', true ); // used for SEO.
				$soundcloud_trackid          = get_post_meta( $id, 'wpdtrt_gallery_attachment_soundcloud_trackid', true ); // used for embed, see also http://stackoverflow.com/a/28182284.
				$vimeo_pageid                = get_post_meta( $id, 'wpdtrt_gallery_attachment_vimeo_pageid', true ); // used for embed.
				// $image_size_mobile        = 'wpdtrt-gallery-mobile'; // TODO not implemented, needs enquire.js.
				//
				// $orientation  = '';
				// if ( isset( $image_meta['height'], $image_meta['width'] ) ) {
				// $orientation = ( $image_meta['height'] > $image_meta['width'] ) ? 'portrait' : 'landscape';
				// }
				// .

				$tabpanel_attrs  = " role='tabpanel'";
				$tabpanel_attrs .= " id='galleryid-{$id}-tabpanel-{$count}'";
				$tabpanel_attrs .= " aria-labelledby='galleryid-{$id}-tab-{$count}'";
				$tabpanel_attrs .= " tabindex='0'";
				$tabpanel_attrs .= " data-id='{$id}'";

				if ( $rwgps_pageid ) {
					$tabpanel_attrs .= " data-rwgps-pageid='true'";
				} elseif ( $soundcloud_pageid && $soundcloud_trackid ) {
					$tabpanel_attrs .= " data-soundcloud-pageid='true' data-soundcloud-trackid='true'";
				} elseif ( $vimeo_pageid ) {
					$tabpanel_attrs .= " data-vimeo-pageid='true'";
				} elseif ( '1' === $panorama ) {
					$img_src_panorama = wp_get_attachment_image_src( $id, $image_size_panorama )[0];
					$tabpanel_attrs  .= " data-src-panorama='{$img_src_panorama}'";
					$tabpanel_attrs  .= " data-panorama='true'";
				} else {
					$image_size_desktop          = wp_get_attachment_image_src( $id, $image_size_desktop )[0];
					$image_size_desktop_expanded = wp_get_attachment_image_src( $id, $image_size_desktop_expanded )[0];
					$tabpanel_attrs             .= " data-src-desktop='{$image_size_desktop}'";
					$tabpanel_attrs             .= " data-src-desktop-expanded='{$image_size_desktop_expanded}'";
					// $img_src_mobile   = wp_get_attachment_image_src( $id, $image_size_mobile )[0];
					// $tabpanel_attrs  .= " data-src-mobile='{$img_src_mobile}'";
				}

				// Geolocation
				// Could this be replaced by simply looking up the custom field?
				if ( function_exists( 'wpdtrt_exif_get_attachment_metadata' ) ) {
					$attachment_metadata     = wpdtrt_exif_get_attachment_metadata( $id );
					$attachment_metadata_gps = wpdtrt_exif_get_attachment_metadata_gps( $attachment_metadata, 'number' );
					$latitude                = $attachment_metadata_gps['latitude'];
					$longitude               = $attachment_metadata_gps['longitude'];
					$tabpanel_attrs         .= " data-latitude='{$latitude}'";
					$tabpanel_attrs         .= " data-longitude='{$longitude}'";
				}

				if ( $count > 1 ) {
					$tabpanel_attrs .= ' hidden';
				}

				if ( '' !== $tabpanelclass ) {
					$tabpanel_attrs .= " class='{$tabpanelclass}'";
				}

				$output .= "<div{$tabpanel_attrs}>";

				/**
				 * START TABPANEL ITEM
				 */

				$tabpanelitem_attrs = '';
				$iconclass          = '';

				if ( '' !== $tabpanelitemclass ) {
					$tabpanelitem_attrs .= " class='{$tabpanelitemclass}'";
				}

				if ( $rwgps_pageid || ( $soundcloud_pageid && $soundcloud_trackid ) || $vimeo_pageid ) {
					$tabpanelitemtag = 'div';
				}

				$output .= "<{$tabpanelitemtag}{$tabpanelitem_attrs}>";

				/**
				 * START TABPANEL IMAGE
				 */

				if ( ! $rwgps_pageid && ! $soundcloud_pageid && ! $soundcloud_trackid && ! $vimeo_pageid ) {
					$tabpanelimage_attrs = '';

					if ( '' !== $tabpanelimageclass ) {
						if ( '1' === $panorama ) {
							$iconclass = $iconclasspanorama;
						} elseif ( $rwgps_pageid ) {
							$iconclass = $iconclassrwgps;
						} elseif ( $soundcloud_pageid && $soundcloud_trackid ) {
							$iconclass = $iconclasssoundcloud;
						} elseif ( $vimeo_pageid ) {
							$iconclass = $iconclassvimeo;
						} else {
							$iconclass = $iconclassimage;
						}

						$tabpanelimage_attrs .= " class='{$tabpanelimageclass} {$iconclass}'";
					}

					$output .= "<{$tabpanelimagetag}{$tabpanelimage_attrs}>";
					$output .= $image_output;
					// $output .= preg_replace( '/src="[^"]*"/', 'src=""', $image_output ); // TODO: lazy loading.
					$output .= "</{$tabpanelimagetag}>";
				}

				/**
				 * END TABPANEL IMAGE
				 */

				/**
				 * START IFRAME EMBED
				 */

				if ( $rwgps_pageid || ( $soundcloud_pageid && $soundcloud_trackid ) || $vimeo_pageid ) {
					// ex-JS notes:
					// autoplay disabled as working but inconsistent
					// TODO update this to only apply on page load - i.e. if triggered (#31)
					// if first thumbnail in the gallery or
					// not first thumbnail but elected to display first
					// if ($galleryItem.is(':first-child') || isDefault) {
					// autoplay = false;
					// }.
					if ( '1' === $panorama ) {
						$iconclass = $iconclasspanorama;
					} elseif ( $rwgps_pageid ) {
						$iconclass = $iconclassrwgps;
					} elseif ( $soundcloud_pageid && $soundcloud_trackid ) {
						$iconclass = $iconclasssoundcloud;
					} elseif ( $vimeo_pageid ) {
						$iconclass = $iconclassvimeo;
					} else {
						$iconclass = $iconclassimage;
					}

					$autopause     = 'true'; // opposite of $autoplay.
					$autoplay      = 'false';
					$embed_attrs   = " class='wpdtrt-gallery-viewer__iframe-wrapper {$iconclass}'";
					$iframe_attrs  = " aria-describedby='galleryid-{$id}-tabpanel-{$count}-caption'";
					$iframe_attrs .= " class='wpdtrt-gallery-viewer__iframe'";

					if ( $rwgps_pageid ) {
						$iframe_attrs .= " src='//rwgps-embeds.com/routes/{$rwgps_pageid}/embed'";
						$iframe_attrs .= " title='Ride With GPS map viewer. '";
					} elseif ( $soundcloud_pageid && $soundcloud_trackid ) {
						// TODO: player on live site displays a waveform, player on local dev doesn't.
						$iframe_attrs .= " src='//w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/{$soundcloud_trackid}?auto_play={$autoplay}&hide_related=true&show_comments=false&show_user=false&show_reposts=false&visual=true'";
						$iframe_attrs .= " title='SoundCloud player. '";
					} elseif ( $vimeo_pageid ) {
						// adapted from https://appleple.github.io/modal-video/.
						$iframe_attrs .= " allowfullscreen='true'";
						$iframe_attrs .= " src='//player.vimeo.com/video/{$vimeo_pageid}?api=false&autopause={$autopause}&autoplay={$autoplay}&byline=false&loop=false&portrait=false&title=false&xhtml=false'";
						$iframe_attrs .= " title='Vimeo player. '";
					}

					$output .= "<div{$embed_attrs}>";
					$output .= "<iframe{$iframe_attrs}></iframe>";
					$output .= '</div>';
				}

				/**
				 * END IFRAME EMBED
				 */

				/**
				 * START TABPANEL CAPTION
				 */

				if ( '' !== $captiontag && trim( $attachment->post_excerpt ) ) {
					$enlargementcaption_attrs      = '';
					$enlargementcaptionliner_attrs = '';

					if ( '' !== $tabpanelcaptionclass ) {
						$enlargementcaption_attrs .= " class='{$tabpanelcaptionclass}'";
					}

					if ( $rwgps_pageid || ( $soundcloud_pageid && $soundcloud_trackid ) || $vimeo_pageid ) {
						$enlargementcaption_attrs .= " id='galleryid-{$id}-tabpanel-{$count}-caption'";
						$tabpanelcaptiontag        = 'div';
					}

					if ( '' !== $tabpanelcaptionlinerclass ) {
						if ( '1' === $panorama ) {
							$iconclass = $iconclasspanorama;
						} elseif ( $rwgps_pageid ) {
							$iconclass = $iconclassrwgps;
						} elseif ( $soundcloud_pageid && $soundcloud_trackid ) {
							$iconclass = $iconclasssoundcloud;
						} elseif ( $vimeo_pageid ) {
							$iconclass = $iconclassvimeo;
						} else {
							$iconclass = $iconclassimage;
						}

						$enlargementcaptionliner_attrs .= " class='{$tabpanelcaptionlinerclass}'";
					}

					$output .= "<{$tabpanelcaptiontag}{$enlargementcaption_attrs}>";
					$output .= "<div{$enlargementcaptionliner_attrs}>";

					if ( '' !== $iconclass ) {
						$output .= "<span class='{$iconclass}'></span>";
					}

					$output .= wptexturize( $attachment->post_excerpt );
					$output .= '</div>';
					$output .= "</{$tabpanelcaptiontag}>";
				}

				/**
				 * END TABPANEL CAPTION
				 */

				$output .= "</{$tabpanelitemtag}>";

				/**
				 * END TABPANEL ITEM
				 */

				$output .= '</div>';

				/**
				 * END TABPANEL
				 */
			}

			$output .= '</div>';

			// TODO.
			$output .= '</div>';

			/**
			 * END TABPANEL LINER
			 */
		}

		/**
		 * END TABPANELS WRAPPER
		 */

		$output .= '</div>';

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
		$result['iconclasstabhint']          = 'wpdtrt-gallery-icon-TODO';
		$result['iconclassrvimeo']           = 'wpdtrt-gallery-icon-vimeo';
		$result['tabclass']                  = 'wpdtrt-gallery-gallery__tab';
		$result['tabtag']                    = 'button';
		$result['tabhintclass']              = 'wpdtrt-gallery-gallery__tab-hint';
		$result['tabhintlinerclass']         = 'wpdtrt-gallery-gallery__tab-hint-liner';
		$result['tabhinttext']               = 'Use LEFT and RIGHT arrows to select an image, ENTER to load it.';
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
		$id                 = $attachment->ID;
		$caption            = wp_get_attachment_caption( $id );
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
