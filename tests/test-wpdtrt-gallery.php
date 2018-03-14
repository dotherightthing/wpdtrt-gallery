<?php
/**
 * Unit tests, using PHPUnit, wp-cli, WP_UnitTestCase
 *
 * The plugin is 'active' within a WP test environment
 * 	so the plugin class has already been instantiated
 * 	with the options set in wpdtrt-gallery.php
 *
 * Only function names prepended with test_ are run.
 * $debug logs are output with the test output in Terminal
 * A failed assertion may obscure other failed assertions in the same test.
 *
 * @package GalleryTest
 * @see http://kb.dotherightthing.dan/php/wordpress/php-unit-testing-revisited/ - Links
 * @see http://richardsweeney.com/testing-integrations/
 * @see https://gist.github.com/benlk/d1ac0240ec7c44abd393 - Collection of notes on WP_UnitTestCase
 * @see https://core.trac.wordpress.org/browser/trunk/tests/phpunit/includes/factory.php
 * @see https://core.trac.wordpress.org/browser/trunk/tests/phpunit/includes//factory/
 * @see https://stackoverflow.com/questions/35442512/how-to-use-wp-unittestcase-go-to-to-simulate-current-pageclass-wp-unittest-factory-for-term.php
 * @see https://codesymphony.co/writing-wordpress-plugin-unit-tests/#object-factories
 */

/**
 * WP_UnitTestCase unit tests for GalleryTest
 */
class GalleryTest extends WP_UnitTestCase {

    /**
     * SetUp
     * Automatically called by PHPUnit before each test method is run
     */
    public function setUp() {
  		// Make the factory objects available.
        parent::setUp();

	    $this->post_id_1 = $this->create_post( array(
	    	'post_title' => 'Gallery test',
	    	'post_date' => '2018-03-14 19:00:00',
	    	'post_content' => '[wpdtrt-gallery-h2]<h2>Heading</h2>[/wpdtrt-gallery-h2]'
	    ) );
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
    }

    /**
     * Create post
     *
     * @param string $post_title Post title
     * @param string $post_date Post date
     * @param array $term_ids Taxonomy term IDs
     * @return number $post_id
     *
     * @see https://developer.wordpress.org/reference/functions/wp_insert_post/
     * @see https://wordpress.stackexchange.com/questions/37163/proper-formatting-of-post-date-for-wp-insert-post
     * @see https://codex.wordpress.org/Function_Reference/wp_update_post
     */
    public function create_post( $options ) {

    	$post_title = null;
    	$post_date = null;
        $post_content = null;

    	extract( $options, EXTR_IF_EXISTS );

 		$post_id = $this->factory->post->create([
           'post_title' => $post_title,
           'post_date' => $post_date,
           'post_content' => $post_content,
           'post_type' => 'post',
           'post_status' => 'publish'
        ]);

        //global $debug;
        //$debug->log('Created post ' . $post_title . ' with id of ' . $post_id);

        return $post_id;
    }

    // ########## TEST ########## //

	/**
	 * Test shortcodes
	 * 	trim() removes line break added by WordPress
	 */
	public function test_shortcodes() {

		$this->go_to(
			get_post_permalink( $this->post_id_1 )
		);

		$this->assertXmlStringEqualsXmlString(
			trim( do_shortcode( get_the_content() ) ),
			'<div class="stack stack_link_viewer gallery-viewer h2-viewer" id="[]-viewer" data-has-image="false" data-expanded="false">
				<div class="gallery-viewer--header">
			    	<h2>Heading</h2>
			   	</div>
			  	<div class="stack--wrapper" style="">
			    	<figure class="stack--liner">
			      		<div class="img-wrapper">
			        		<img src="" alt="">
			      		</div>
			      		<iframe width="100%" height="100%" src="" frameborder="0" allowfullscreen="true" scrolling="no" aria-hidden="true"></iframe>
			      		<figcaption class="gallery-viewer--footer">
			        		<div class="gallery-viewer--caption"></div>
			      		</figcaption>
			    	</figure>
			  	</div>
			</div>',
			'wpdtrt_gallery_shortcode does not return the correct HTML'
		);
	}
}
