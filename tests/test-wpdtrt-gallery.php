<?php
/**
 * Unit tests, using PHPUnit, wp-cli, WP_UnitTestCase
 *
 * The plugin is 'active' within a WP test environment
 *  so the plugin class has already been instantiated
 *  with the options set in wpdtrt-gallery.php
 *
 * Only function names prepended with test_ are run.
 * $debug logs are output with the test output in Terminal
 * A failed assertion may obscure other failed assertions in the same test.
 *
 * @package WPDTRT_Gallery
 * @version 0.0.1
 * @since   0.7.0
 * @see http://kb.dotherightthing.dan/php/wordpress/php-unit-testing-revisited/ - Links
 * @see https://phpunit.readthedocs.io/en/7.1/configuration.html
 * @see http://richardsweeney.com/testing-integrations/
 * @see https://gist.github.com/benlk/d1ac0240ec7c44abd393 - Collection of notes on WP_UnitTestCase
 * @see https://core.trac.wordpress.org/browser/trunk/tests/phpunit/includes/factory.php
 * @see https://core.trac.wordpress.org/browser/trunk/tests/phpunit/includes//factory/
 * @see https://stackoverflow.com/questions/35442512/how-to-use-wp-unittestcase-go-to-to-simulate-current-pageclass-wp-unittest-factory-for-term.php
 * @see https://codesymphony.co/writing-wordpress-plugin-unit-tests/#object-factories
 * @see https://miya0001.github.io/wp-unit-docs/
 * @see https://codesymphony.co/creating-your-own-wordpress-unit-test-factories/
 * @see https://stackoverflow.com/a/27151309 - Stubs vs Mocks
 */

/**
 * WP_UnitTestCase unit tests for GalleryTest
 */
class GalleryTest extends WP_UnitTestCase {

	/**
	 * Compare two HTML fragments.
	 *
	 * @param string $expected Expected value
	 * @param string $actual Actual value
	 * @param string $error_message Message to show when strings don't match
	 * @uses https://stackoverflow.com/a/26727310/6850747
	 */
	protected function assertEqualHtml( $expected, $actual, $error_message ) {
		$from = [ '/\>[^\S ]+/s', '/[^\S ]+\</s', '/(\s)+/s', '/> </s' ];
		$to   = [ '>', '<', '\\1', '><' ];
		$this->assertEquals(
			preg_replace( $from, $to, $expected ),
			preg_replace( $from, $to, $actual ),
			$error_message
		);
	}

	/**
	 * Lint page state in Tenon.io
	 *
	 * @param string $url_or_src Page URL or post-JS DOM source
	 * @return array Tenon resultSet
	 * @todo Waiting on Tenon Tunnel to expose WPUnit environment to Tenon API
	 * @see https://blog.tenon.io/tenon-io-end-of-year-startup-experience-at-9-months-in-product-updates-and-more/ Roadmap at 12/2015
	 * @see https://github.com/joedolson/access-monitor/blob/master/src/access-monitor.php
	 * @see https://tenon.io/documentation/understanding-request-parameters.php Optional parameters/$args
	 */
	protected function tenon( $url_or_src ) {

		$args = array(
			'method'  => 'POST',
			'body' => array(
				// Required parameter #1 is passed in by Travis CI
				'key' => getenv('TENON_AUTH'),
				// Optional parameters:
				'level' => 'AA',
				'priority' => 0,
				'certainty' => 100,
			),
			'headers' => '',
			'timeout' => 60,
		);

		// Required parameter #2
		if ( preg_match( "/^http/", $url_or_src ) ) {
			$args['body']['url'] = $url_or_src;
		} else {
			$args['body']['src'] = $url_or_src;
		}

		$result = wp_remote_post( 'https://tenon.io/api/', $args );

		if ( is_wp_error( $result ) ) {
			$response = $result->errors;
		} else {
			// the test results.
			$response = $result; // $result['resultSet'];
		}

		var_dump($response);

		return $response;
	}

	/**
	 * Delete image sizes generated by WP
	 * @see https://gistpages.com/posts/php_delete_files_with_unlink
	 */
	public function delete_sized_images() {
		array_map( 'unlink', glob( 'images/test1-*.jpg' ) );
	}

	/**
	 * SetUp
	 * Automatically called by PHPUnit before each test method is run
	 */
	public function setUp() {
		// Make the factory objects available.
		parent::setUp();

		// Generate WordPress data fixtures

		// Post (for testing manually entered, naked shortcode)
		$this->post_id_1 = $this->create_post( array(
			'post_title'   => 'Empty gallery test',
			'post_content' => '[wpdtrt_gallery_shortcode_heading]<h2>Post 1 heading</h2>[/wpdtrt_gallery_shortcode_heading]',
		));

		// Attachment (for testing custom sizes and meta)
		$this->attachment_id_1 = $this->create_attachment( array(
			'filename'       => 'images/test1.jpg',
			'parent_post_id' => $this->post_id_1,
		));

		$this->thumbnail_size = 'wpdtrt-gallery-thumbnail';

		// Post (for testing populated shortcode)
		// NOTE: generated attachment is attached to post_id_1 not post_id_2
		// this is a chicken-and-egg scenario
		$this->post_id_2 = $this->create_post( array(
			'post_title'   => 'Single image gallery test',
			'post_content' => '[wpdtrt_gallery_shortcode_heading]<h2>Post 2 heading</h2>[gallery link="file" ids="' . $this->attachment_id_1 . '"][/wpdtrt_gallery_shortcode_heading]',
		));

		// Post (for injected naked shortcode)
		$this->post_id_3 = $this->create_post( array(
			'post_title'   => 'Empty gallery test',
			'post_content' => '<h2>Post 3 heading</h2>',
		));
	}

	/**
	 * TearDown
	 * Automatically called by PHPUnit after each test method is run
	 *
	 * @see https://codesymphony.co/writing-wordpress-plugin-unit-tests/#object-factories
	 */
	public function tearDown() {

		parent::tearDown();

		wp_delete_post( $this->post_id_1, true );
		wp_delete_post( $this->attachment_id_1, true );
		wp_delete_post( $this->post_id_2, true );

		$this->delete_sized_images();
	}

	/**
	 * Create post
	 *
	 * @param string $post_title Post title
	 * @param string $post_date Post date
	 * @param array $term_ids Taxonomy term IDs
	 * @return number $post_id
	 * @see https://developer.wordpress.org/reference/functions/wp_insert_post/
	 * @see https://wordpress.stackexchange.com/questions/37163/proper-formatting-of-post-date-for-wp-insert-post
	 * @see https://codex.wordpress.org/Function_Reference/wp_update_post
	 */
	public function create_post( $options ) {

		$post_title   = null;
		$post_date    = null;
		$post_content = null;

		extract( $options, EXTR_IF_EXISTS );

		$post_id = $this->factory->post->create([
			'post_title'   => $post_title,
			'post_date'    => $post_date,
			'post_content' => $post_content,
			'post_type'    => 'post',
			'post_status'  => 'publish',
		]);

		return $post_id;
	}

	/**
	 * Create attachment, upload media file, generate sizes
	 * @see http://develop.svn.wordpress.org/trunk/tests/phpunit/includes/factory/class-wp-unittest-factory-for-attachment.php
	 * @see https://core.trac.wordpress.org/ticket/42990 - Awaiting Review
	 * @todo Factory method not available - see create_attachment(), below
	 */
	public function create_attachment_simple( $options ) {

		$filename       = null;
		$parent_post_id = null;

		extract( $options, EXTR_IF_EXISTS );

		$attachment_id = $this->factory->attachment->create_upload_object([
			'file'   => $filename,
			'parent' => $parent_post_id,
		]);
	}

	/**
	 * Create attachment and attach it to a post
	 *
	 * @param string $filename Filename
	 * @param number $parent_post_id The ID of the post this attachment is for
	 * @return number $attachment_id
	 * @see https://developer.wordpress.org/reference/functions/wp_insert_attachment/
	 * @see http://develop.svn.wordpress.org/trunk/tests/phpunit/includes/factory/class-wp-unittest-factory-for-attachment.php
	 */
	public function create_attachment( $options ) {

		$filename       = null;
		$parent_post_id = null;

		extract( $options, EXTR_IF_EXISTS );

		// Check the type of file. We'll use this as the 'post_mime_type'
		$filetype = wp_check_filetype( basename( $filename ), null );

		// Get the path to the upload directory
		$wp_upload_dir = wp_upload_dir();

		// Create the attachment from an array of post data
		$attachment_id = $this->factory->attachment->create([
			'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ),
			'post_mime_type' => $filetype['type'],
			'post_parent'    => $parent_post_id, // test factory only
			'file'           => $filename, // test factory only
		]);

		// generate image sizes
		// @see https://wordpress.stackexchange.com/a/134252
		$attach_data = wp_generate_attachment_metadata( $attachment_id, $filename );
		wp_update_attachment_metadata( $attachment_id, $attach_data );

		return $attachment_id;
	}

	/**
	 * ===== Tests =====
	 */

	/**
	 * Test that the custom field keys and values are output as HTML queryparams.
	 * @see https://codex.wordpress.org/Function_Reference/wp_get_attachment_link
	 * @see https://github.com/dotherightthing/wpdtrt-gallery/issues/35
	 */
	public function test_attachment_fields() {

		// location - only used for Media Library searches

		// panorama

		update_post_meta( $this->attachment_id_1, 'wpdtrt_gallery_attachment_panorama', '1' );

		$this->assertContains(
			'panorama=1',
			wp_get_attachment_link( $this->attachment_id_1 ),
			'Thumbnail link HTML missing query param for panorama'
		);

		// position_y

		$this->assertContains(
			'position_y=50',
			wp_get_attachment_link( $this->attachment_id_1 ),
			'Thumbnail link HTML missing query param for position_y (default)'
		);

		// position_y (custom)

		update_post_meta( $this->attachment_id_1, 'wpdtrt_gallery_attachment_position_y', '0' );

		$this->assertContains(
			'position_y=0',
			wp_get_attachment_link( $this->attachment_id_1 ),
			'Thumbnail link HTML missing query param for position_y (top)'
		);

		// position_y (custom)

		update_post_meta( $this->attachment_id_1, 'wpdtrt_gallery_attachment_position_y', '100' );

		$this->assertContains(
			'position_y=100',
			wp_get_attachment_link( $this->attachment_id_1 ),
			'Thumbnail link HTML missing query param for position_y (bottom)'
		);

		// ride with gps map embed

		update_post_meta( $this->attachment_id_1, 'wpdtrt_gallery_attachment_rwgps_pageid', '123456789' );

		$this->assertContains(
			'rwgps_pageid=123456789',
			wp_get_attachment_link( $this->attachment_id_1 ),
			'Thumbnail link HTML missing query param for rwgps_pageid'
		);

		// soundcloud player embed - both keys must have values

		update_post_meta( $this->attachment_id_1, 'wpdtrt_gallery_attachment_soundcloud_pageid', 'test-page' );
		update_post_meta( $this->attachment_id_1, 'wpdtrt_gallery_attachment_soundcloud_trackid', '123456789' );

		$this->assertContains(
			'soundcloud_pageid=test-page',
			wp_get_attachment_link( $this->attachment_id_1 ),
			'Thumbnail link HTML missing query param for soundcloud_pageid'
		);

		$this->assertContains(
			'soundcloud_trackid=123456789',
			wp_get_attachment_link( $this->attachment_id_1 ),
			'Thumbnail link HTML missing query param for soundcloud_trackid'
		);

		// vimeo

		update_post_meta( $this->attachment_id_1, 'wpdtrt_gallery_attachment_vimeo_pageid', '123456789' );

		$this->assertContains(
			'vimeo_pageid=123456789',
			wp_get_attachment_link( $this->attachment_id_1 ),
			'Thumbnail link HTML missing query param for vimeo_pageid'
		);
	}

	/**
	 * Test that the gallery thumbnail image exists and is the correct size
	 *  The test image path is appended to http://example.org/wp-content/uploads/.
	 *
	 * @see http://develop.svn.wordpress.org/trunk/tests/phpunit/includes/factory/class-wp-unittest-factory-for-attachment.php
	 */
	public function test_image_sizes() {

		$this->assertGreaterThan(
			0,
			$this->attachment_id_1,
			'Attachment image not created'
		);

		$this->assertEquals(
			'http://example.org/wp-content/uploads/images/test1-150x150.jpg',
			wp_get_attachment_image_src( $this->attachment_id_1, $this->thumbnail_size )[0],
			'Thumbnail image not created'
		);

		$this->assertEquals(
			150,
			wp_get_attachment_image_src( $this->attachment_id_1, $this->thumbnail_size )[1],
			'Thumbnail image file has incorrect width'
		);

		$this->assertEquals(
			150,
			wp_get_attachment_image_src( $this->attachment_id_1, $this->thumbnail_size )[2],
			'Thumbnail image file has incorrect height'
		);

		$this->assertContains(
			'width="150" height="150"',
			wp_get_attachment_image( $this->attachment_id_1, $this->thumbnail_size ),
			'Thumbnail image src has incorrect dimensions'
		);
	}

	/**
	 * Test shortcode with a heading only
	 *  trim() removes line break added by WordPress
	 *
	 * @todo https://github.com/dotherightthing/wpdtrt-gallery/issues/2
	 */
	public function test_shortcode_with_heading() {

		$this->go_to(
			get_post_permalink( $this->post_id_1 )
		);

		$content = get_post_field( 'post_content', $this->post_id_1 );

		$this->assertEqualHtml(
			'<div class="wpdtrt-gallery stack stack_link_viewer gallery-viewer h2-viewer" id="[]-viewer" data-has-gallery="false" data-expanded="false">
				<div class="gallery-viewer--header">
					<h2>Post 1 heading</h2>
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
			</div>',
			trim( do_shortcode( $content ) ),
			'wpdtrt_gallery_shortcode does not return the correct HTML'
		);
	}

	/**
	 * Test shortcode with a heading and gallery containing a single image
	 *  Note that test theme does not appear to output HTML5 markup for gallery.
	 */
	public function test_shortcode_with_heading_and_gallery() {

		$this->go_to(
			get_post_permalink( $this->post_id_2 )
		);

		// https://stackoverflow.com/a/22270259/6850747
		$content = apply_filters( 'the_content', get_post_field( 'post_content', $this->post_id_2 ) );

		$this->assertContains(
			'<h2>Post 2 heading</h2>',
			trim( do_shortcode( trim( do_shortcode( $content ) ) ) ),
			'wpdtrt_gallery_shortcode does not output heading text'
		);
	}

	/**
	 * Test shortcode with a heading and gallery containing a single image
	 *  Note that test theme does not appear to output HTML5 markup for gallery.
	 */
	public function test_injected_shortcode_with_heading() {

		$this->go_to(
			get_post_permalink( $this->post_id_3 )
		);

		// https://stackoverflow.com/a/22270259/6850747
		$content = apply_filters( 'the_content', get_post_field( 'post_content', $this->post_id_3 ) );

		$this->assertContains(
			'<h2>Post 3 heading</h2>',
			trim( do_shortcode( trim( do_shortcode( $content ) ) ) ),
			'wpdtrt_gallery_shortcode not injected'
		);
	}

	public function test_shortcode_a11y() {

		$permalink = get_post_permalink( $this->post_id_3 );

		$this->go_to( $permalink );

		$this->assertEquals(
			array(),
			$this->tenon( '<a href="#anchor"></a>' )
			//$this->tenon( $permalink )
		);
	}

	/**
	 * Test theme support as this affects gallery markup
	 *  dtrt parent theme sets add_theme_support( 'html5', array( 'gallery', 'caption' ) ); (3.9+)
	 *  This uses "<figure> and <figcaption> elements, instead of the generic definition list markup."
	 *  "galleries will not include inline styles anymore when in HTML5 mode. This caters to the trend of disabling default gallery styles through the use_default_gallery_style filter, a filter that even the last two default themes used. With that, theme developers can always start with a clean slate when creating their own set of gallery styles."
	 *
	 * @see https://make.wordpress.org/core/2014/04/15/html5-galleries-captions-in-wordpress-3-9/
	 * @see https://wordpress.stackexchange.com/questions/23839/using-add-theme-support-inside-a-plugin
	 */
	public function __test_theme_support() {

		$this->assertTrue(
			get_theme_support( 'html5' ),
			'Current theme does not support HTML5 markup'
		);

		$this->assertTrue(
			in_array( 'gallery', get_theme_support( 'html5' )[0], true ),
			'Current theme does not support HTML5 gallery markup'
		);

		$this->assertTrue(
			in_array( 'caption', get_theme_support( 'html5' )[0], true ),
			'Current theme does not support HTML5 (fig)caption markup'
		);
	}
}
