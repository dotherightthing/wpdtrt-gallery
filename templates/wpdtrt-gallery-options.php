<?php
/**
 * Template partial for Admin Options page
 *    WP Admin > Settings > DTRT Gallery
 *
 * This file contains PHP, and HTML from the WordPress_Admin_Style plugin.
 *
 * @link        https://github.com/dotherightthing/wpdtrt-gallery
 * @link        /wp-admin/admin.php?page=WordPress_Admin_Style#twocolumnlayout2
 * @since       0.1.0
 *
 * @package     WPDTRT_Gallery
 * @subpackage  WPDTRT_Gallery/views
 */
?>

<div class="wrap">

  <div id="icon-options-general" class="icon32"></div>
  <h1><?php esc_attr_e( 'DTRT Gallery', 'wp_admin_style' ); ?>: Placeholder blocks</h1>

  <div id="poststuff">

    <div id="post-body" class="metabox-holder columns-2">

      <!-- main content -->
      <div id="post-body-content">

        <div class="meta-box-sortables ui-sortable">

          <?php
          /**
           * Start Scenario 1 - data selection form
           * If the user has not chosen a content type yet.
           * then $wpdtrt_gallery_datatype will be set to the default of ""
           * The user must make a selection so that we know which page to query,
           * so we show the selection box first.
           */
          if ( !isset( $wpdtrt_gallery_datatype ) || ( $wpdtrt_gallery_datatype === '') ) :
          ?>

          <div class="postbox">

            <h2>
              <span><?php esc_attr_e( 'What kind of blocks would you like?', 'wp_admin_style' ); ?></span>
            </h2>

            <div class="inside">

              <form name="wpdtrt_gallery_data_form" method="post" action="">

                <input type="hidden" name="wpdtrt_gallery_form_submitted" value="Y" />

                <table class="form-table">
                  <tr>
                    <th>
                      <label for="wpdtrt_gallery_datatype">Please select a block type:</label>
                    </th>
                    <td>
                      <select name="wpdtrt_gallery_datatype" id="wpdtrt_gallery_datatype">
                        <option value="">None</option>
                        <option value="photos">Coloured Blocks</option>
                        <option value="users">Map Blocks</option>
                      </select>
                    </td>
                  </tr>
                </table>

                <?php
                /**
                 * submit_button( string $text = null, string $type = 'primary', string $name = 'submit', bool $wrap = true, array|string $other_attributes = null )
                 */
                  submit_button(
                    $text = 'Save',
                    $type = 'primary',
                    $name = 'wpdtrt_gallery_submit',
                    $wrap = true,
                    $other_attributes = null
                  );
                ?>

              </form>
            </div>
            <!-- .inside -->

          </div>
          <!-- .postbox -->

          <?php
          /**
           * End Scenario 1 - data selection form
           */

          else:

          /**
           * Start Scenario 2 - data selected
           * If the user has already chosen a content type,
           * then $wpdtrt_gallery_data will contain the body of the resulting JSON.
           */

          /**
           * Start Scenario 2a - data sample
           * We display a sample of the data
           * so the user can verify that they have chosen the type
           * which meets their needs.
           */
          ?>

          <div class="postbox">

            <h2>
              <span><?php esc_attr_e( 'Sample blocks', 'wp_admin_style' ); ?></span>
            </h2>

            <div class="inside">

              <p>This data set contains <?php echo count( $wpdtrt_gallery_data ); ?> blocks.</p>

              <p>The first 6 are displayed below:</p>

              <div class="wpdtrt-gallery-blocks">
                <ul>

                <?php
                  $max_length = 6;
                  $count = 0;
                  $display_count = 1;

                  foreach( $wpdtrt_gallery_data as $key => $val ) {
                    echo "<li>" . wpdtrt_gallery_html_image( $key ) . "</li>\r\n";

                    $count++;
                    $display_count++;

                    // when we reach the end of the demo sample, stop looping
                    if ($count === $max_length) {
                      break;
                    }
                  }
                  // end foreach
                ?>
                </ul>
              </div>

              <?php
                echo wpdtrt_gallery_html_date();
              ?>

            </div>
            <!-- .inside -->

          </div>
          <!-- .postbox -->

          <?php
          /**
           * End Scenario 2a - data sample
           */

          /**
           * Start Scenario 2b - data viewer
           * For the purposes of debugging, we display the raw data.
           * var_dump is prefereable to print_r,
           * because it reveals the data types used,
           * so we can check whether the data is in the expected format.
           * @link http://kb.dotherightthing.co.nz/php/print_r-vs-var_dump/
           */
          ?>

          <div class="postbox">

            <h2>
              <span><?php esc_attr_e( 'Raw block data', 'wp_admin_style'); ?></span>
            </h2>

            <div class="inside">

              <p>The data used to generate the blocks above.</p>

              <div class="wpdtrt-gallery-data"><pre><code><?php echo "{\r\n";

                  $count = 0;
                  $max_length = 6;

                  foreach( $wpdtrt_gallery_data as $key => $val ) {
                    var_dump( $wpdtrt_gallery_data[$key] );

                    $count++;

                    // when we reach the end of the demo sample, stop looping
                    if ($count === $max_length) {
                      break;
                    }

                  }

                  echo "}\r\n"; ?></code></pre></div>

            </div> <!-- .inside -->

          </div>
          <!-- .postbox -->

          <?php
          /**
           * End Scenario 2b - data viewer
           */

          /**
           * End Scenario 2 - data selected
           */
          endif;
          ?>

        </div>
        <!-- .meta-box-sortables .ui-sortable -->

      </div>
      <!-- post-body-content -->

      <!-- sidebar -->
      <div id="postbox-container-1" class="postbox-container">

        <div class="meta-box-sortables">

          <?php
          /**
           * Start Scenario 2 - data selected
           */

          /**
           * Start Scenario 2c - data re-selection form
           * If the user has already chosen a content type
           * then we'll provide the selection form again,
           * so that they can choose a different content type.
           * But this time we'll give it secondary importance
           * by displaying it in a sidebar:
           */
            if ( isset( $wpdtrt_gallery_datatype ) && ( $wpdtrt_gallery_datatype !== '') ) :
          ?>

          <div class="postbox">

            <h2>
              <span><?php esc_attr_e( 'Update preferences', 'wp_admin_style'); ?></span>
            </h2>

            <div class="inside">

              <p>Sample data not what you were expecting?</p>
              <p>Change your selection here:</p>

              <form name="wpdtrt_gallery_data_form" method="post" action="">

                <input type="hidden" name="wpdtrt_gallery_form_submitted" value="Y" />

                <p>
                  <label for="wpdtrt_gallery_datatype">Please select a block type:</label>
                </p>
                <p>
                  <?php
                  /**
                   * selected
                   * Compares two given values (for example, a saved option vs. one chosen in a form) and,
                   * if the values are the same, adds the selected attribute to the current option tag.
                   * @link https://codex.wordpress.org/Function_Reference/selected
                   */
                  ?>
                  <select name="wpdtrt_gallery_datatype" id="wpdtrt_gallery_datatype">
                    <option value="">None</option>
                    <option value="photos" <?php selected( $wpdtrt_gallery_datatype, "photos" ); ?>>Coloured blocks</option>
                    <option value="users" <?php selected( $wpdtrt_gallery_datatype, "users" ); ?>>Maps</option>
                  </select>
                </p>
                <p>

                  <?php
                    submit_button(
                      $text = 'Save &amp; load new data',
                      $type = 'primary',
                      $name = 'wpdtrt_gallery_submit',
                      $wrap = false, // don't wrap in paragraph
                      $other_attributes = null
                    );
                  ?>
                </p>

              </form>

            </div> <!-- .inside -->

          </div>
          <!-- .postbox -->

          <?php
          /**
           * End Scenario 2c - data re-selection form
           */

          /**
           * End Scenario 2 - data selected
           */
          endif;
          ?>

        </div>
        <!-- .meta-box-sortables -->

      </div>
      <!-- #postbox-container-1 .postbox-container -->

    </div>
    <!-- #post-body .metabox-holder .columns-2 -->

    <br class="clear">
  </div>
  <!-- #poststuff -->

</div> <!-- .wrap -->
