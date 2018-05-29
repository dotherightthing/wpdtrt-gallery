<?php
/**
 * Template to display plugin output in shortcodes and widgets
 *
 * @package   DTRT Gallery
 * @version   0.0.1
 * @since     0.7.0 DTRT WordPress Plugin Boilerplate Generator
 */

// Predeclare variables

// Internal WordPress arguments available to widgets
// This allows us to use the same template for shortcodes and front-end widgets
$before_widget = null; // register_sidebar
$before_title  = null; // register_sidebar
$title         = null;
$after_title   = null; // register_sidebar
$after_widget  = null; // register_sidebar

// shortcode options
// $foo = null;

// access to plugin
$plugin = null;

// Options: display $args + widget $instance settings + access to plugin
$options = get_query_var( 'options' );

// Overwrite variables from array values
// @link http://kb.network.dan/php/wordpress/extract/
extract( $options, EXTR_IF_EXISTS );

// content between shortcode tags
if ( isset( $context ) ) {
	$content = $context->content;
} else {
	$content = '';
}

// WordPress widget options (not output with shortcode)
echo $before_widget;
echo $before_title . $title . $after_title;
?>

<div class="wpdtrt-gallery stack stack_link_viewer gallery-viewer h2-viewer" id="[]-viewer" data-has-gallery="false" data-expanded="false">
	<div class="gallery-viewer--header">
	<?php
		echo $content;
	?>
	</div>
	<div class="stack--wrapper" style="">
		<figure class="stack--liner">
			<div class="img-wrapper">
				<img src="" alt="">
			</div>
			<div class="gallery-viewer--embed">
				<iframe width="100%" height="100%" src="" frameborder="0" allowfullscreen="true" scrolling="no" aria-hidden="true"></iframe>
			</div>
			<figcaption class="gallery-viewer--footer">
				<div class="gallery-viewer--caption"></div>
			</figcaption>
		</figure>
	</div>
</div>

<?php
// output widget customisations (not output with shortcode)
echo $after_widget;
