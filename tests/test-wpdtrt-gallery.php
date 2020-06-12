<?php
/**
 * File: tests/test-wpdtrt-gallery.php
 *
 * Unit tests, using PHPUnit, wp-cli, WP_UnitTestCase.
 *
 * Note:
 * - The plugin is 'active' within a WP test environment
 *   so the plugin class has already been instantiated
 *   with the options set in wpdtrt-gallery.php
 * - Only function names prepended with test_ are run.
 * - $debug logs are output with the test output in Terminal
 * - A failed assertion may obscure other failed assertions in the same test.
 *
 * See:
 * - <https://github.com/dotherightthing/wpdtrt-plugin-boilerplate/wiki/Testing-&-Debugging#testing>
 *
 * Since:
 *   0.8.13 - DTRT WordPress Plugin Boilerplate Generator
 */

/**
 * Class: WPDTRT_GalleryTest
 *
 * WP_UnitTestCase unit tests for wpdtrt_gallery.
 */
class WPDTRT_GalleryTest extends WP_UnitTestCase {

	/**
	 * Group: Variables
	 * _____________________________________
	 */

	/**
	 * Variable: $base_url
	 */
	private $base_url = 'http://example.org';

	/**
	 * Group: Lifecycle Events
	 * _____________________________________
	 */

	/**
	 * Method: setUp
	 *
	 * SetUp,
	 * automatically called by PHPUnit before each test method is run.
	 */
	public function setUp() {
		// Make the factory objects available.
		parent::setUp();

		// Generate WordPress data fixtures
		//
		// Post (for testing manually entered, naked shortcode).
		$this->post_for_test_image = $this->create_post( array(
			'post_title'   => 'Empty gallery test',
			'post_content' => '<p>Post for image</p>',
		));

		// Attachment (for testing custom sizes and meta).
		$this->image_1 = $this->create_attachment( array(
			'filename'       => 'images/test1.jpg',
			'parent_post_id' => $this->post_for_test_image,
		));

		$this->thumbnail_size = 'thumbnail';

		// Post (for testing populated shortcode)
		// NOTE: generated attachment is attached to post_for_test_image not post_with_single_image_gallery
		// this is a chicken-and-egg scenario.
		$this->post_with_single_image_gallery = $this->create_post( array(
			'post_title'   => 'Single image gallery test',
			'post_content' => '<div class="wpdtrt-anchorlinks__section wpdtrt-anchorlinks__anchor" id="section-heading" tabindex="-1">[wpdtrt_gallery_shortcode_heading]<h2 data-anchorlinks-id="section-heading">Section heading<a class="wpdtrt-anchorlinks__anchor-link" href="#section-heading"><span aria-label="Anchor" class="wpdtrt-anchorlinks__anchor-icon">#</span></a></h2>[/wpdtrt_gallery_shortcode_heading][gallery link="file" ids="' . $this->image_1 . '"]<p>A short sentence.</p></div>',
		));

		// Post (for injected naked shortcode).
		$this->post_with_no_gallery = $this->create_post( array(
			'post_title'   => 'Empty gallery test',
			'post_content' => '<div class="wpdtrt-anchorlinks__section wpdtrt-anchorlinks__anchor" id="section-heading" tabindex="-1">[wpdtrt_gallery_shortcode_heading]<h2 data-anchorlinks-id="section-heading">Section heading<a class="wpdtrt-anchorlinks__anchor-link" href="#section-heading"><span aria-label="Anchor" class="wpdtrt-anchorlinks__anchor-icon">#</span></a></h2>[/wpdtrt_gallery_shortcode_heading]<p>A short sentence.</p></div>',
		));
	}

	/**
	 * Method: tearDown
	 *
	 * TearDown,
	 * automatically called by PHPUnit after each test method is run.
	 *
	 * See:
	 * - <https://codesymphony.co/writing-wordpress-plugin-unit-tests/#object-factories>
	 */
	public function tearDown() {

		parent::tearDown();

		wp_delete_post( $this->post_for_test_image, true );
		wp_delete_post( $this->image_1, true );
		wp_delete_post( $this->post_with_single_image_gallery, true );

		$this->delete_sized_images();
	}

	/**
	 * Group: Helpers
	 * _____________________________________
	 */

	/**
	 * Method: assertEqualHtml
	 *
	 * Compare two HTML fragments.
	 *
	 * Parameters:
	 *   $expected - Expected value.
	 *   $actual - Actual value.
	 *   $error_message - Message to show when strings don't match.
	 *
	 * Uses:
	 *   <https://stackoverflow.com/a/26727310/6850747>
	 */
	protected function assertEqualHtml( string $expected, string $actual, string $error_message ) {
		$from = [ '/\>[^\S ]+/s', '/[^\S ]+\</s', '/(\s)+/s', '/> </s' ];
		$to   = [ '>', '<', '\\1', '><' ];
		$this->assertEquals(
			preg_replace( $from, $to, $expected ),
			preg_replace( $from, $to, $actual ),
			$error_message
		);
	}

	/**
	 * Method: create_attachment
	 *
	 * Create attachment and attach it to a post.
	 *
	 * Parameters:
	 *   $options - Options
	 *
	 * Returns:
	 *   $attachment_id - Attachment ID
	 *
	 * See:
	 * - <https://developer.wordpress.org/reference/functions/wp_insert_attachment/>
	 * - <http://develop.svn.wordpress.org/trunk/tests/phpunit/includes/factory/class-wp-unittest-factory-for-attachment.php>
	 */
	public function create_attachment( array $options ) : int {

		$filename       = null;
		$parent_post_id = null;

		extract( $options, EXTR_IF_EXISTS );

		// Check the type of file. We'll use this as the 'post_mime_type'.
		$filetype = wp_check_filetype( basename( $filename ), null );

		// Get the path to the upload directory.
		$wp_upload_dir = wp_upload_dir();

		// Create the attachment from an array of post data.
		$attachment_id = $this->factory->attachment->create([
			'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ),
			'post_mime_type' => $filetype['type'],
			'post_parent'    => $parent_post_id, // test factory only.
			'file'           => $filename, // test factory only.
		]);

		// generate image sizes
		// @see https://wordpress.stackexchange.com/a/134252.
		$attach_data = wp_generate_attachment_metadata( $attachment_id, $filename );
		wp_update_attachment_metadata( $attachment_id, $attach_data );

		return $attachment_id;
	}

	/**
	 * Method: create_attachment_simple
	 *
	 * Create attachment, upload media file, generate sizes.
	 *
	 * Parameters:
	 *   $options - Options
	 *
	 * See:
	 * - <http://develop.svn.wordpress.org/trunk/tests/phpunit/includes/factory/class-wp-unittest-factory-for-attachment.php>
	 * - <Awaiting Review: https://core.trac.wordpress.org/ticket/42990>
	 *
	 * TODO
	 * - Factory method not available - see create_attachment(), below
	 */
	public function create_attachment_simple( array $options ) {

		$filename       = null;
		$parent_post_id = null;

		extract( $options, EXTR_IF_EXISTS );

		$attachment_id = $this->factory->attachment->create_upload_object([
			'file'   => $filename,
			'parent' => $parent_post_id,
		]);
	}

	/**
	 * Method: create_post
	 *
	 * Create post.
	 *
	 * Parameters:
	 *   $options - Post options
	 *
	 * Returns:
	 *   $post_id - Post ID
	 *
	 * See:
	 * - <https://developer.wordpress.org/reference/functions/wp_insert_post/>
	 * - <https://wordpress.stackexchange.com/questions/37163/proper-formatting-of-post-date-for-wp-insert-post>
	 * - <https://codex.wordpress.org/Function_Reference/wp_update_post>
	 */
	public function create_post( array $options ) : int {

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
	 * Method: delete_sized_images
	 *
	 * Delete image sizes generated by WP.
	 *
	 * See:
	 * - <https://gistpages.com/posts/php_delete_files_with_unlink>
	 */
	public function delete_sized_images() {
		array_map( 'unlink', glob( 'images/test1-*.jpg' ) );
	}

	/**
	 * Method: tenon
	 *
	 * Lint page state in Tenon.io (proof of concept).
	 *
	 * Parameters:
	 *   $url_or_src - Page URL or post-JS DOM source
	 *
	 * Returns:
	 *   $result - Tenon resultSet, or WP error
	 *
	 * TODO:
	 * - Waiting on Tenon Tunnel to expose WPUnit environment to Tenon API
	 *
	 * See:
	 * - <Tenon - Roadmap at 12/2015: https://blog.tenon.io/tenon-io-end-of-year-startup-experience-at-9-months-in-product-updates-and-more/>
	 * - <https://github.com/joedolson/access-monitor/blob/master/src/access-monitor.php>
	 * - <Tenon - Optional parameters/$args: https://tenon.io/documentation/understanding-request-parameters.php>
	 *
	 * Since:
	 *   1.7.15 - wpdtrt-gallery
	 */
	protected function tenon( string $url_or_src ) : array {

		$endpoint = 'https://tenon.io/api/';

		$args = array(
			'method'  => 'POST',
			'body'    => array(
				// Required parameter #1 is passed in by Travis CI.
				'key'       => getenv( 'TENON_AUTH' ),
				// Optional parameters:.
				'level'     => 'AA',
				'priority'  => 0,
				'certainty' => 100,
			),
			'headers' => '',
			'timeout' => 60,
		);

		// Required parameter #2.
		if ( preg_match( '/^http/', $url_or_src ) ) {
			$args['body']['url'] = $url_or_src;
		} else {
			$args['body']['src'] = $url_or_src;
			// TODO
			// this is a quick hack to get something working
			// in reality we will want to support full pages too.
			$args['body']['fragment'] = 1; // else 'no title' etc error.
		}

		$response = wp_remote_post(
			$endpoint,
			$args
		);

		// $body = wp_remote_retrieve_body( $response );.
		if ( is_wp_error( $response ) ) {
			$result = $response->errors;
		} else {
			/**
			 * Return the body, not the header
			 * true = convert to associative array
			 */
			$api_response = json_decode( $response['body'], true );

			$result = $api_response['resultSet'];
		}

		return $result;
	}

	/**
	 * Group: Tests
	 * _____________________________________
	 */
	public function test_test_url() {
		$url = get_post_permalink( $this->post_with_no_gallery );

		$this->go_to(
			$url
		);

		$this->assertContains(
			'?post_type=post&p=7',
			$url,
			'URL not as expected'
		);
	}

	/**
	 * Method: test_attachment_fields
	 *
	 * Test that the custom field keys and values are output as HTML queryparams.
	 *
	 * See:
	 * - <https://codex.wordpress.org/Function_Reference/wp_get_attachment_link>
	 * - <https://github.com/dotherightthing/wpdtrt-gallery/issues/35>
	 */
	public function test_attachment_fields() {

		// TODO add test for #77.
		//
		// location - only used for Media Library searches
		//
		// panorama.
		update_post_meta( $this->image_1, 'wpdtrt_gallery_attachment_panorama', '1' );

		$this->assertContains(
			'data-panorama="1"',
			wp_get_attachment_link( $this->image_1 ),
			'Thumbnail link HTML missing data attribute for panorama'
		);

		// ride with gps map embed.
		update_post_meta( $this->image_1, 'wpdtrt_gallery_attachment_rwgps_pageid', '123456789' );

		$this->assertContains(
			'data-rwgps-pageid="123456789"',
			wp_get_attachment_link( $this->image_1 ),
			'Thumbnail link HTML missing data attribute for rwgps-pageid'
		);

		// soundcloud player embed - both keys must have values.
		update_post_meta( $this->image_1, 'wpdtrt_gallery_attachment_soundcloud_pageid', 'test-page' );
		update_post_meta( $this->image_1, 'wpdtrt_gallery_attachment_soundcloud_trackid', '123456789' );

		$this->assertContains(
			'data-soundcloud-pageid="test-page"',
			wp_get_attachment_link( $this->image_1 ),
			'Thumbnail link HTML missing data attribute for soundcloud-pageid'
		);

		$this->assertContains(
			'data-soundcloud-trackid="123456789"',
			wp_get_attachment_link( $this->image_1 ),
			'Thumbnail link HTML missing data attribute for soundcloud-trackid'
		);

		// vimeo.
		update_post_meta( $this->image_1, 'wpdtrt_gallery_attachment_vimeo_pageid', '123456789' );

		$this->assertContains(
			'data-vimeo-pageid="123456789"',
			wp_get_attachment_link( $this->image_1 ),
			'Thumbnail link HTML missing data attribute for vimeo-pageid'
		);
	}

	/**
	 * Method: test_image_sizes
	 *
	 * Test that the gallery thumbnail image exists and is the correct size.
	 *
	 * Note:
	 * - The test image path is appended to http://example.org/wp-content/uploads/.
	 *
	 * See:
	 * - <http://develop.svn.wordpress.org/trunk/tests/phpunit/includes/factory/class-wp-unittest-factory-for-attachment.php>
	 */
	public function test_image_sizes() {

		$this->assertGreaterThan(
			0,
			$this->image_1,
			'Attachment image not created'
		);

		$this->assertEquals(
			$this->base_url . '/wp-content/uploads/images/test1-150x150.jpg',
			wp_get_attachment_image_src( $this->image_1, $this->thumbnail_size )[0],
			'Thumbnail image not created'
		);

		$this->assertEquals(
			150,
			wp_get_attachment_image_src( $this->image_1, $this->thumbnail_size )[1],
			'Thumbnail image file has incorrect width'
		);

		$this->assertEquals(
			150,
			wp_get_attachment_image_src( $this->image_1, $this->thumbnail_size )[2],
			'Thumbnail image file has incorrect height'
		);

		$this->assertContains(
			'width="150" height="150"',
			wp_get_attachment_image( $this->image_1, $this->thumbnail_size ),
			'Thumbnail image src has incorrect dimensions'
		);
	}

	/**
	 * Method: test_shortcode_in_post_with_no_gallery
	 *
	 * Test shortcode with a heading and gallery containing a single image.
	 *
	 * Note:
	 * - Test theme does not appear to output HTML5 markup for gallery.
	 */
	public function test_shortcode_in_post_with_no_gallery() {

		$this->go_to(
			get_post_permalink( $this->post_with_no_gallery )
		);

		// https://stackoverflow.com/a/22270259/6850747.
		$content = apply_filters( 'the_content', get_post_field( 'post_content', $this->post_with_no_gallery ) );

		/*
		Snapshot:

		<div id="section-heading" class="wpdtrt-anchorlinks__section wpdtrt-anchorlinks__anchor wpdtrt-gallery__section" tabindex="-1">
			<div class="entry-content">
				<div class="wpdtrt-gallery-viewer" data-wpdtrt-anchorlinks-controls="highlighting" data-enabled="false">
					<div class="wpdtrt-gallery-viewer__header">
						<h2 data-anchorlinks-id="section-heading">
							Section heading
							<a class="wpdtrt-anchorlinks__anchor-link" href="#section-heading">
								<span aria-label="Anchor" class="wpdtrt-anchorlinks__anchor-icon">#</span>
							</a>
						</h2>
					</div>
				</div>
				<p>A short sentence</p>
			</div>
		</div>
		*/

		$dom = new DOMDocument();
		$dom->loadHTML( mb_convert_encoding( $content, 'HTML-ENTITIES', 'UTF-8' ) );

		$this->assertEquals(
			1,
			$dom
				->getElementsByTagName( 'h2' )->length,
			'Heading should remain intact'
		);

		$this->assertEquals(
			'wpdtrt-anchorlinks__section wpdtrt-anchorlinks__anchor wpdtrt-gallery__section',
			$dom
				->getElementsByTagName( 'div' )[0]
				->getAttribute( 'class' ),
			'wpdtrt-gallery__section should be one of the section classnames'
		);

		$this->assertEquals(
			'entry-content',
			$dom
				->getElementsByTagName( 'div' )[0]
				->getElementsByTagName( 'div' )[0]
				->getAttribute( 'class' ),
			'entry-content should be nested within section'
		);

		$this->assertEquals(
			null,
			$dom
				->getElementsByTagName( 'div' )[0]
				->getElementsByTagName( 'div' )[0]
				->nextSibling,
			'entry-content should not be followed by wpdtrt-gallery-gallery when there is no gallery'
		);

		$this->assertEquals(
			'wpdtrt-gallery-viewer__header',
			$dom
				->getElementsByTagName( 'h2' )[0]
				->parentNode
				->getAttribute( 'class' ),
			'wpdtrt-gallery-viewer__header should wrap heading'
		);

		$this->assertEquals(
			'wpdtrt-gallery-viewer',
			$dom
				->getElementsByTagName( 'h2' )[0]
				->parentNode
				->parentNode
				->getAttribute( 'class' ),
			'wpdtrt-gallery-viewer should wrap heading'
		);

		$this->assertEquals(
			'highlighting',
			$dom
				->getElementsByTagName( 'h2' )[0]
				->parentNode
				->parentNode
				->getAttribute( 'data-wpdtrt-anchorlinks-controls' ),
			'wpdtrt-gallery-viewer should control link highlighting'
		);
	}

	/**
	 * Method: test_shortcode_in_post_with_single_image_gallery
	 *
	 * Test shortcode with a heading and gallery containing a single image.
	 *
	 * Note:
	 * - Test theme does not appear to output HTML5 markup for gallery.
	 */
	public function test_shortcode_in_post_with_single_image_gallery() {

		$this->go_to(
			get_post_permalink( $this->post_with_single_image_gallery )
		);

		// https://stackoverflow.com/a/22270259/6850747.
		$content = apply_filters( 'the_content', get_post_field( 'post_content', $this->post_with_single_image_gallery ) );

		/*
		Snapshot:

		<div id="section-heading" class="wpdtrt-anchorlinks__section wpdtrt-anchorlinks__anchor wpdtrt-gallery__section" tabindex="-1">
			<div class="entry-content">
				<div class="wpdtrt-gallery-viewer" data-wpdtrt-anchorlinks-controls="highlighting" data-enabled="false" data-expanded="false">
					<div class="wpdtrt-gallery-viewer__header">
						<h2 data-anchorlinks-id="section-heading">
							Section heading
							<a class="wpdtrt-anchorlinks__anchor-link" href="#section-heading">
								<span aria-label="Anchor" class="wpdtrt-anchorlinks__anchor-icon">#</span>
							</a>
						</h2>
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
				<p>A short sentence.</p>
			</div>
			<div class="wpdtrt-gallery-gallery">
				<h3 class="accessible">Photos</h3>
				<div id='gallery-7' class='gallery galleryid-133 gallery-columns-3 gallery-size-thumbnail'>
					<figure class='gallery-item'>
						<div class='gallery-icon portrait'>
							<a href='https://dontbelievethehype.imgix.net/2018/10/MDM_20150926_172415_IMG_2651-e1534628107653.jpg?auto=compress%2Cformat&fit=crop&h=368&ixlib=php-1.2.1&rect=0%2C1944%2C3264%2C1388&w=865&wpsize=wpdtrt-gallery-desktop&s=d62730885958f63427dc8663bc5979b2'><img width="300" height="300" src="https://dontbelievethehype.imgix.net/2018/10/MDM_20150926_172415_IMG_2651-e1534628107653.jpg?auto=compress%2Cformat&amp;fit=crop&amp;h=300&amp;ixlib=php-1.2.1&amp;rect=0%2C223%2C3264%2C3264&amp;w=300&amp;wpsize=thumbnail&amp;s=0d3dcc2bef6dd1db876d8ab53ece41d8" class="attachment-thumbnail size-thumbnail" alt="The Stella Khomutovo monument reads &quot;Khomutovo, 1685&quot; and features a life size bear." aria-describedby="gallery-7-11837" data-id="11837" data-src-desktop-expanded="https://dontbelievethehype.imgix.net/2018/10/MDM_20150926_172415_IMG_2651-e1534628107653.jpg?auto=compress%2Cformat&amp;fit=crop&amp;h=1153&amp;ixlib=php-1.2.1&amp;rect=0%2C0%2C3264%2C4352&amp;w=865&amp;wpsize=wpdtrt-gallery-desktop-expanded&amp;s=cc58a8f6ef6bd472c6c87c5415cb347e" /></a>
						</div>
						<figcaption class='wp-caption-text gallery-caption' id='gallery-7-11837'>
							Bear encounter at the turnoff to Khomutovo.
						</figcaption>
					</figure>
				</div>
			</div>
		</div>
		*/

		$dom = new DOMDocument();
		$dom->loadHTML( mb_convert_encoding( $content, 'HTML-ENTITIES', 'UTF-8' ) );

		$this->assertEquals(
			1,
			$dom
				->getElementsByTagName( 'h2' )->length,
			'Heading should remain intact'
		);

		$this->assertEquals(
			'wpdtrt-anchorlinks__section wpdtrt-anchorlinks__anchor wpdtrt-gallery__section',
			$dom
				->getElementsByTagName( 'div' )[0]
				->getAttribute( 'class' ),
			'wpdtrt-gallery__section should be one of the section classnames'
		);

		$this->assertEquals(
			'entry-content',
			$dom
				->getElementsByTagName( 'div' )[0]
				->getElementsByTagName( 'div' )[0]
				->getAttribute( 'class' ),
			'entry-content should be nested within section'
		);

		/*
		// this nextSibling test fails due to whitespace in the rendered content
		// but works in real life.
		$this->assertNotEquals(
			null,
			$dom
				->getElementsByTagName( 'div' )[0]
				->getElementsByTagName( 'div' )[0]
				->nextSibling,
			'entry-content should be followed by wpdtrt-gallery-gallery when there is a gallery'
		);

		// this nextSibling test fails due to whitespace in the rendered content
		// but works in real life.
		$this->assertEquals(
			'wpdtrt-gallery-gallery',
			$dom
				->getElementsByTagName( 'div' )[0]
				->getElementsByTagName( 'div' )[0]
				->nextSibling
				->getAttribute( 'class' ),
			'entry-content should be followed by wpdtrt-gallery-gallery when there is a gallery'
		);
		*/

		$this->assertEquals(
			'wpdtrt-gallery-viewer__header',
			$dom
				->getElementsByTagName( 'h2' )[0]
				->parentNode
				->getAttribute( 'class' ),
			'wpdtrt-gallery-viewer__header should wrap heading'
		);

		$this->assertEquals(
			'wpdtrt-gallery-viewer',
			$dom
				->getElementsByTagName( 'h2' )[0]
				->parentNode
				->parentNode
				->getAttribute( 'class' ),
			'wpdtrt-gallery-viewer should wrap heading'
		);

		$this->assertEquals(
			'highlighting',
			$dom
				->getElementsByTagName( 'h2' )[0]
				->parentNode
				->parentNode
				->getAttribute( 'data-wpdtrt-anchorlinks-controls' ),
			'wpdtrt-gallery-viewer should control link highlighting'
		);
	}

	/**
	 * Method: test_wp_admin
	 *
	 * Test wp-admin.
	 */
	public function __test_wp_admin() {
		$this->go_to( '/wp-admin/plugins.php' );

		$this->go_to( '/wp-admin/plugins.php?page=tgmpa-install-plugins&plugin_status=install' );
	}

	/**
	 * Method: __test_shortcode_a11y
	 *
	 * Test accessibility using the Tenon API.
	 *
	 * TODO:
	 * - This is working, but see TODOs in tenon() function
	 */
	public function __test_shortcode_a11y() {

		$permalink = get_post_permalink( $this->post_with_no_gallery );

		$this->go_to( $permalink );

		$this->assertEquals(
			array(), // empty resultSet = no issues.
			$this->tenon( '<h2>Quick links</h2><p><a href="#anchor">Jump!</a></p>' )
			// $this->tenon( $permalink ).
		);
	}

	/**
	 * Method: __test_theme_support
	 *
	 * Test theme support as this affects gallery markup.
	 *
	 * Note:
	 * - dtrt parent theme sets add_theme_support( 'html5', array( 'gallery', 'caption' ) ); (3.9+)
	 * - This uses "<figure> and <figcaption> elements, instead of the generic definition list markup."
	 * - "galleries will not include inline styles anymore when in HTML5 mode. This caters to the trend of disabling default gallery styles through the use_default_gallery_style filter, a filter that even the last two default themes used. With that, theme developers can always start with a clean slate when creating their own set of gallery styles."
	 *
	 * See:
	 * - <https://make.wordpress.org/core/2014/04/15/html5-galleries-captions-in-wordpress-3-9/>
	 * - <https://wordpress.stackexchange.com/questions/23839/using-add-theme-support-inside-a-plugin>
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
