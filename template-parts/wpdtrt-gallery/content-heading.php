<?php
/**
 * File: template-parts/wpdtrt-gallery/content-heading.php
 *
 * Template to display plugin output in shortcodes and widgets.
 *
 * Since:
 *   0.8.13 - DTRT WordPress Plugin Boilerplate Generator
 */

// Predeclare variables
//
// Internal WordPress arguments available to widgets
// This allows us to use the same template for shortcodes and front-end widgets.
$before_widget = null; // register_sidebar.
$before_title  = null; // register_sidebar.
$title         = null;
$after_title   = null; // register_sidebar.
$after_widget  = null; // register_sidebar.

// shortcode options
// $foo = null;
//
// access to plugin.
$plugin = null;

// Options: display $args + widget $instance settings + access to plugin.
$options = get_query_var( 'options', array() );

// Overwrite variables from array values
// @link http://kb.network.dan/php/wordpress/extract/.
extract( $options, EXTR_IF_EXISTS );

// content between shortcode tags.
if ( isset( $context ) ) {
	$content = $context->content;
} else {
	$content = '';
}

// WordPress widget options (not output with shortcode).
echo $before_widget;
echo $before_title . $title . $after_title;
?>

<div class="wpdtrt-gallery-viewer" data-wpdtrt-anchorlinks-controls="highlighting" data-enabled="false" data-expanded="false">
	<div class="wpdtrt-gallery-viewer__header">
	<?php
		echo $content;
	?>
	</div>
	<div class="wpdtrt-gallery-viewer__wrapper">
		<figure class="wpdtrt-gallery-viewer__liner">
			<div class="wpdtrt-gallery-viewer__img-wrapper"></div>
			<div class="wpdtrt-gallery-viewer__embed">
				<iframe aria-hidden="true" title="Gallery media viewer."></iframe>
			</div>
			<figcaption class="wpdtrt-gallery-viewer__footer">
				<div class="wpdtrt-gallery-viewer__caption"></div>
			</figcaption>
		</figure>
	</div>
</div>

<?php
// output widget customisations (not output with shortcode).
echo $after_widget;
