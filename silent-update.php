<?php
/**
 * SilentUpdate WordPress Plugin
 *
 * @package   SilentUpdate
 * @author    Tomasz Dobrzyński
 * @link      http://tomasz-dobrzynski.com
 * @copyright Copyright © 2015 Tomasz Dobrzyński
 *
 * Plugin Name: Silent Update
 * Plugin URI: http://tomasz-dobrzynski.com/wordpress-silent-update
 * Description: Control whether you want to update modification date when update post.
 * Version: 1.0.0
 * Author: Tomasz Dobrzyński
 * Author URI: http://tomasz-dobrzynski.com
 * Domain Path: /languages/
 * Tested up to: 4.2.2
 */

function silent_update_call()
{
	new SilentUpdate();
}

/**
 * Loads only on page where you can edit posts
 */
if ( is_admin() ) {
	add_action( 'load-post.php', 'silent_update_call' );
	add_action( 'load-post-new.php', 'silent_update_call' );
}

class SilentUpdate
{
	function __construct()
	{
		load_plugin_textdomain( 'silent-update', false, basename( dirname( __FILE__ ) ) . '/languages' );

		add_action( 'add_meta_boxes', array( &$this, 'meta_box' ), 10, 2 );
		add_filter( 'wp_insert_post_data', array( &$this, 'filter_handler' ), '99', 2 );
	}

	/**
	 * Adds the meta box container.
	 */
	public function meta_box( $post_type )
	{
		add_meta_box( 'silent_update', __( 'Modification date', 'silent-update' ), array( $this, 'render_meta_box_content' ), $post_type, 'side', 'high' );
	}

	/**
	 * Render Meta Box content.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_meta_box_content( $post )
	{
		wp_nonce_field( 'silent_update_custom_box', 'silent_update_custom_box_nonce' );

		$datef = __( 'M j, Y @ G:i' );
		$date = date_i18n( $datef, the_modified_date( 'U', false, false, false ) );

		$out = '<div class="misc-pub-section">
			<input type="checkbox" id="modification_date" name="modification_date" />
			<label for="modification_date">'.__( 'Do not update modification date', 'silent-update' ).'</label>
		</div>
		<div class="misc-pub-section">'.__( 'Modified on:', 'silent-update' ).' <b>'.$date.'</b></div>';

		print $out;
	}

	/**
	 * Omit modification date if checkbox selected
	 */
	public function filter_handler( $data, $postarr )
	{
		if ( isset( $_POST['modification_date'] ) && isset( $postarr['post_modified'] ) && isset( $postarr['post_modified_gmt'] ) ) {
			$data['post_modified'] = $postarr['post_modified'];
			$data['post_modified_gmt'] = $postarr['post_modified_gmt'];
		}

		return $data;
	}

}
