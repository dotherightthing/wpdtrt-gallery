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
class WPDTRT_Gallery_Plugin extends DoTheRightThing\WPDTRT_Plugin_Boilerplate\r_1_7_5\Plugin {

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
		add_filter( 'wp_get_attachment_link', array( $this, 'filter_thumbnail_queryparams' ), 1, 4 );
		add_filter( 'wp_get_attachment_image_attributes', array( $this, 'filter_thumbnail_attributes' ), 1, 4 ); // 10,2
		add_filter( 'the_content', array( $this, 'filter_content_galleries' ), 10 );
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
				'tabclass'                  => '',
				'tabtag'                    => '',
				'tablinerclass'             => '',
				'tablinertag'               => '',
				'tablistclass'              => '',
				'tablistlabel'              => '',
				'tabpanelcaptionclass'      => '',
				'tabpanelcaptionlinerclass' => '',
				'tabpanelcaptiontag'        => $html5 ? 'figcaption' : 'dd',
				'tabpanelclass'             => '',
				'tabpanelimageclass'        => '',
				'tabpanelimagesize'         => '',
				'tabpanelimagetag'          => $html5 ? 'div' : 'dt',
				'tabpanelitemclass'         => '',
				'tabpanelitemtag'           => $html5 ? 'figure' : 'dl',
				'tabpanelsheaderclass'      => '',
				'tabpanelslinerclass'       => '',
				'tabpanelswrapperclass'     => '',
				'usecaptiontag'             => 'true',
				'useicontag'                => 'true',
				'useitemtag'                => 'true',
				'usetabspattern'            => 'false',
			),
			$attr,
			'gallery'
		);

		$id = intval( $atts['id'] );

		if ( ! empty( $atts['include'] ) ) {
			$_attachments = get_posts(
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
			$attachments = get_children(
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
			$attachments = get_children(
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

		// tabpanels wrapper - wraps all tabpanel items.
		$tabpanelswrapperclass = $this->helper_sanitize_html_classes( $atts['tabpanelswrapperclass'] );

		// tabpanels header - child of tabpanels wrapper.
		$tabpanelsheaderclass = $this->helper_sanitize_html_classes( $atts['tabpanelsheaderclass'] );

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
		$tablistclass = $this->helper_sanitize_html_classes( $atts['tablistclass'] );
		$tablistlabel = esc_attr( $atts['tablistlabel'] );

		// tab - wraps thumbnail/icon.
		$tabclass = $this->helper_sanitize_html_classes( $atts['tabclass'] );
		$tabtag   = tag_escape( $atts['tabtag'] );

		// tab liner - child of tab.
		$tablinerclass = $this->helper_sanitize_html_classes( $atts['tablinerclass'] );
		$tablinertag   = tag_escape( $atts['tablinertag'] );

		// gallery - output booleans.
		$usecaptiontag = filter_var( $atts['usecaptiontag'], FILTER_VALIDATE_BOOLEAN );
		$useicontag    = filter_var( $atts['useicontag'], FILTER_VALIDATE_BOOLEAN );
		$useitemtag    = filter_var( $atts['useitemtag'], FILTER_VALIDATE_BOOLEAN );

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
		 * START TABPANELS WRAPPER
		 */

		if ( $usetabspattern ) {

			$tabpanelswrapper_attrs = '';
			$tabpanelsliner_attrs   = '';
			$tabpanelsheader_attrs  = '';

			if ( '' !== $tabpanelswrapperclass ) {
				$tabpanelswrapper_attrs .= " class='{$tabpanelswrapperclass}'";
				$tabpanelswrapper_attrs .= " data-enabled='false'";
				$tabpanelswrapper_attrs .= " data-expanded='false'";
				$tabpanelswrapper_attrs .= " data-wpdtrt-anchorlinks-controls='highlighting'";
			}

			$output .= "<div${tabpanelswrapper_attrs}>";

			/**
			 * START TABPANEL LINER
			 */

			if ( '' !== $tabpanelsheaderclass ) {
				$tabpanelsheader_attrs .= " class='{$tabpanelsheaderclass}'";
			}

			if ( '' !== $tabpanelslinerclass ) {
				$tabpanelsliner_attrs .= " class='{$tabpanelslinerclass}'";
			}

			$output .= "<div{$tabpanelsheader_attrs}>";
			$output .= '<h2>TITLE</h2>';
			$output .= '</div>';

			$output .= "<div{$tabpanelsliner_attrs}>";

			$count     = 0;
			$i         = 0;
			$parent_id = $id;

			foreach ( $attachments as $id => $attachment ) {

				++$count;

				$image_output = wp_get_attachment_image( $id, $atts['tabpanelimagesize'], false, $attr );
				$image_meta   = wp_get_attachment_metadata( $id );
				// $orientation  = '';
				// if ( isset( $image_meta['height'], $image_meta['width'] ) ) {
				// $orientation = ( $image_meta['height'] > $image_meta['width'] ) ? 'portrait' : 'landscape';
				// }
				if ( $usetabspattern ) {
					/**
					 * START TABPANEL
					 */

					$tabpanel_attrs  = " role='tabpanel'";
					$tabpanel_attrs .= " id='galleryid-{$id}-tabpanel-{$count}'";
					$tabpanel_attrs .= " aria-labelledby='galleryid-{$id}-tab-{$count}'";
					$tabpanel_attrs .= " tabindex='0'";

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

					if ( '' !== $tabpanelitemclass ) {
						$tabpanelitem_attrs .= " class='{$tabpanelitemclass}'";
					}

					$output .= "<{$tabpanelitemtag}{$tabpanelitem_attrs}>";

					/**
					 * START TABPANEL IMAGE
					 */

					$tabpanelimage_attrs = '';

					if ( '' !== $tabpanelimageclass ) {
						$tabpanelimage_attrs .= " class='{$tabpanelimageclass}'";
					}

					$output .= "<{$tabpanelimagetag}{$tabpanelimage_attrs}>";
					$output .= $image_output;
					$output .= "</{$tabpanelimagetag}>";

					// TODO.
					$output .= "<div class='wpdtrt-gallery-viewer__embed'>";
					$output .= "<iframe aria-hidden='true' title='Gallery media viewer.'></iframe>";
					$output .= '</div>';

					if ( '' !== $captiontag && trim( $attachment->post_excerpt ) ) {
						$enlargementcaption_attrs      = '';
						$enlargementcaptionliner_attrs = '';

						if ( '' !== $tabpanelcaptionclass ) {
							$enlargementcaption_attrs .= " class='{$tabpanelcaptionclass}'";
						}

						if ( '' !== $tabpanelcaptionlinerclass ) {
							$enlargementcaptionliner_attrs .= " class='{$tabpanelcaptionlinerclass}'";
						}

						$output .= "<{$tabpanelcaptiontag}{$enlargementcaption_attrs}>";
						$output .= "<div{$enlargementcaptionliner_attrs}>";
						$output .= wptexturize( $attachment->post_excerpt );
						$output .= '</div>';
						$output .= "</{$tabpanelcaptiontag}>";
					}

					/**
					 * END TABPANEL IMAGE
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

		/**
		 * START GALLERY DIV
		 */

		$gallery_div = "<div id='$selector' class='gallery galleryid-{$id} gallery-columns-{$columns} gallery-size-{$size_class}'>";

		if ( $usetabspattern ) {
			$gallery_attrs = " role='tablist'";

			if ( '' !== $tablistlabel ) {
				$gallery_attrs .= " aria-label='{$tablistlabel}'";
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

		$count = 0;
		$i     = 0;

		foreach ( $attachments as $id => $attachment ) {

			++$count;

			$attr = ( $usecaptiontag && trim( $attachment->post_excerpt ) ) ? array( 'aria-describedby' => "$selector-$id" ) : '';

			if ( ! empty( $atts['link'] ) && 'file' === $atts['link'] ) {
				$image_output = wp_get_attachment_link( $id, $atts['size'], false, false, false, $attr );
			} elseif ( $usetabspattern && ! empty( $atts['link'] ) && 'none' === $atts['link'] ) {
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
				$linklinerattrs = '';

				if ( '' !== $tablinerclass ) {
					$linklinerattrs .= " class='{$tablinerclass}'";
				}

				$image_output = "<{$tabtag}{$tab_attrs}>";

				if ( '' !== $tablinertag ) {
					$image_output .= "<{$tablinertag} {$linklinerattrs}>";
				}

				$image_output .= wp_get_attachment_image( $id, $atts['size'], false, $attr );

				if ( '' !== $tablinertag ) {
					$image_output .= "</{$tablinertag}>";
				}

				$image_output .= "</{$tabtag}>";
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

			if ( $itemtag && $useitemtag ) {
				$output .= "<{$itemtag} class='gallery-item'>";
			}

			if ( $icontag && $useicontag ) {
				$output .= "<{$icontag} class='gallery-icon {$orientation}'>";
			}

			$output .= $image_output;

			if ( $icontag && $useicontag ) {
				$output .= "</{$icontag}>";
			}

			if ( $captiontag && $usecaptiontag && trim( $attachment->post_excerpt ) ) {
				$output .= "
					<{$captiontag} class='wp-caption-text gallery-caption' id='$selector-$id'>
					" . wptexturize( $attachment->post_excerpt ) . "
					</{$captiontag}>";
			}

			if ( $itemtag && $useitemtag ) {
				$output .= "</{$itemtag}>";
			}

			if ( ! $html5 && $columns > 0 && 0 === ++$i % $columns ) {
				$output .= '<br style="clear: both" />';
			}
		}

		$output .= '</div>';

		/**
		 * END GALLERY DIV
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
		$result['tabclass']                  = '';
		$result['tabtag']                    = 'button';
		$result['tablinerclass']             = 'a';
		$result['tablinertag']               = 'span';
		$result['tablistclass']              = 'wpdtrt-gallery-gallery';
		$result['tablistlabel']              = 'Choose photo to display';
		$result['tabpanelclass']             = '';
		$result['tabpanelcaptionclass']      = 'wpdtrt-gallery-viewer__footer';
		$result['tabpanelcaptionlinerclass'] = 'wpdtrt-gallery-viewer__caption';
		$result['tabpanelimageclass']        = 'wpdtrt-gallery-viewer__img-wrapper';
		$result['tabpanelimagesize']         = 'wpdtrt-gallery-desktop';
		$result['tabpanelitemclass']         = 'wpdtrt-gallery-viewer__liner';
		$result['tabpanelsheaderclass']      = 'wpdtrt-gallery-viewer__header';
		$result['tabpanelslinerclass']       = 'wpdtrt-gallery-viewer__wrapper';
		$result['tabpanelswrapperclass']     = 'wpdtrt-gallery-viewer';
		$result['usecaptiontag']             = 'false';
		$result['useicontag']                = 'false';
		$result['useitemtag']                = 'false';
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
	 * Method: filter_content_galleries
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
	public function filter_content_galleries( string $content ) : string {
		// Prevent DOMDocument from raising warnings about invalid HTML.
		libxml_use_internal_errors( true );

		$dom = new DOMDocument();
		$dom->loadHTML( mb_convert_encoding( $content, 'HTML-ENTITIES', 'UTF-8' ) );

		// phpcs:disable WordPress.NamingConventions

		// Clear errors, so they aren't kept in memory.
		libxml_clear_errors();

		// DOMDocument doesn't support HTML5
		// so we use a div rather than section element.
		// For better or worse,
		// these divs were initially added by wpdtrt-contentsections
		// but are now added by wpdtrt-anchorlinks,
		// which requires 'tall' sectioning blocks
		// containing gallery/text
		// rather than traditional 'short' headings anchors
		// in order to correctly highlight the 'active' anchor list link.
		$sections             = $dom->getElementsByTagName( 'div' );
		$content_replacements = [];

		foreach ( $sections as $section ) {
			$gallery            = null;
			$gallery_shortcode  = '';
			$heading_html       = '';
			$section_class      = 'wpdtrt-gallery__section';
			$section_html       = '';
			$section_id         = '';
			$section_inner_html = $this->render_html( $section, false );
			$section_tabindex   = '';

			// class is added to h2 by wpdtrt-anchorlinks->filter_content_anchors().
			preg_match( '/wpdtrt-anchorlinks__anchor/', $section->getAttribute( 'class' ), $anchor_matches );

			// only process existing sectioned content.
			if ( count( $anchor_matches ) > 0 ) {

				$headings = $section->getElementsByTagName( 'h2' );

				if ( $headings->length > 0 ) {
					// only h2s are supported.
					$heading = $section->getElementsByTagName( 'h2' )[0];

					if ( $heading->nextSibling ) {
						// Check node type
						// XML_TEXT_NODE = DOMText.
						// XML_ELEMENT_NODE = DOMElement.
						// See https://www.php.net/manual/en/class.domtext.php.
						// See https://www.php.net/manual/en/dom.constants.php.
						if ( XML_TEXT_NODE === $heading->nextSibling->nodeType ) {
							$siblingText = $heading->nextSibling->wholeText;

							// substr would also work
							// but I'm concerned about leading whitespace.
							preg_match( '/\[gallery link="file" ids=/', $siblingText, $gallery_matches );

							if ( count( $gallery_matches ) > 0 ) {
								$gallery = $heading->nextSibling;
							}
						}
					}

					$heading_html     = $this->render_html( $heading, true );
					$new_heading_html = '[wpdtrt_gallery_shortcode_heading]' . $heading_html . '[/wpdtrt_gallery_shortcode_heading]';
					$section_class    = $section->getAttribute( 'class' ) . ' wpdtrt-gallery__section';
					$section_id       = $section->getAttribute( 'id' );
					$section_tabindex = $section->getAttribute( 'tabindex' );
				}
			}

			// rebuild the sectioning element to add our own class.
			$section_html .= '<div class="' . $section_class . '"';

			if ( strlen( $section_id ) > 0 ) {
				$section_html .= ' id="' . $section_id . '"';
			}

			if ( strlen( $section_tabindex ) > 0 ) {
				$section_html .= ' tabindex="' . $section_tabindex . '"';
			}

			$section_html .= '>';

			if ( ! is_null( $gallery ) ) {
				$gallery_shortcode = $this->render_html( $gallery, true );

				if ( strlen( $gallery_shortcode ) > 0 ) {
					$section_inner_html = str_replace( $gallery_shortcode, '', $section_inner_html );
				}
			}

			// wrap heading in gallery viewer shortcode.
			if ( strlen( $heading_html ) > 0 ) {
				// headings are wrapped regardless of whether they precede galleries
				// to apply the gallery heading styling.
				$section_inner_html = str_replace( $heading_html, $new_heading_html, $section_inner_html );
			}

			if ( isset( $gallery ) ) {
				preg_match( '/\[gallery link="file" ids=/', $gallery->nodeValue, $gallery_matches );

				if ( count( $gallery_matches ) > 0 ) {
					// insert gallery shortcode before content.
					// $section_html .= '<div class="wpdtrt-gallery">';
					// $section_html .= '<h3 class="wpdtrt-gallery-gallery__header">Photos</h3>';
					// .
					$section_html .= $gallery_shortcode; // this is the raw shortcode.
					// $section_html .= '</div>';
					// .
				}
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
	public function filter_thumbnail_attributes( array $atts, WP_Post $attachment ) : array {

		$id = $attachment->ID;

		// Fix missing alt text.
		if ( '' === $atts['alt'] ) {
			$caption = wp_get_attachment_caption( $id );

			if ( '' !== $caption ) {
				$atts['alt'] = $caption;
			}
		}

		if ( '' !== $atts['alt'] ) {
			$atts['alt'] = 'Photo N'; // TODO get order number somehow.
		}

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
