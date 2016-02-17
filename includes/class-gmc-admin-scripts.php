<?php
/**
 * Load admin scripts
 *
 * @package     GMB-Core
 * @subpackage  Functions
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

class Google_Maps_Builder_Core_Admin_Scripts extends Google_Maps_Builder_Core_Scripts {

	protected function hooks() {
		add_action( 'admin_head', array( $this, 'icon_style' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

	}

	/**
	 * Admin Dashicon
	 *
	 * @description Displays a cute lil map dashicon on our CPT
	 */
	function icon_style() {
		?>
		<style rel="stylesheet" media="screen">
			#adminmenu #menu-posts-google_maps div.wp-menu-image:before {
				font-family: 'dashicons' !important;
				content: '\f231';
			}
		</style>
		<?php return;
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * Return early if no settings page is registered.
	 * @since     2.0
	 *
	 * @param $hook
	 *
	 * @return    null
	 */
	function enqueue_admin_styles( $hook ) {

		global $post;
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		//Only enqueue scripts for CPT on post type screen
		if ( ( $hook == 'post-new.php' || $hook == 'post.php' ) && 'google_maps' === $post->post_type || $hook == 'google_maps_page_gmb_settings' || $hook == 'google_maps_page_gmb_import_export' ) {

			wp_register_style( $this->plugin_slug . '-admin-styles', GMB_CORE_URL . 'assets/css/gmb-admin' . $suffix . '.css', array(), GMB_VERSION );
			wp_enqueue_style( $this->plugin_slug . '-admin-styles' );

			wp_register_style( $this->plugin_slug . '-map-icons', GMB_CORE_URL . 'includes/libraries/map-icons/css/map-icons.css', array(), GMB_VERSION );
			wp_enqueue_style( $this->plugin_slug . '-map-icons' );

		}

	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since    2.0
	 *
	 * @param $hook
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	function enqueue_admin_scripts( $hook ) {
		global $post;
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$js_dir     = $this->admin_js_dir();
		$js_plugins = $this->admin_js_url();

		//Builder Google Maps API URL
		$signed_in_option    = gmb_get_option( 'gmb_signed_in' );
		$google_maps_api_url = $this->google_maps_url( $signed_in_option );

		//Only enqueue scripts for CPT on post type screen
		if ( ( $hook == 'post-new.php' || $hook == 'post.php' ) && 'google_maps' === $post->post_type ) {

			$this->admin_scripts( $js_plugins, $suffix, $google_maps_api_url, $js_dir, $post, $signed_in_option );
		}

		//Setting Scripts
		if ( $hook == 'google_maps_page_gmb_settings' ) {
			wp_register_script( $this->plugin_slug . '-admin-settings', $js_dir . 'admin-settings' . $suffix . '.js', array( 'jquery' ), GMB_VERSION );
			wp_enqueue_script( $this->plugin_slug . '-admin-settings' );

		}
		wp_enqueue_style( 'dashicons' );


	}

	/**
	 * Load admin scripts
	 *
	 * @since 0.1.0
	 *
	 * @param string $js_plugins
	 * @param string $suffix
	 * @param string $google_maps_api_url
	 * @param string $js_dir
	 * @param WP_Post $post
	 * @param bool|string  $signed_in_option
	 */
	protected function admin_scripts( $js_plugins, $suffix, $google_maps_api_url, $js_dir, $post, $signed_in_option ) {
		wp_enqueue_style( 'wp-color-picker' );

		wp_register_script( $this->plugin_slug . '-admin-magnific-popup', $js_plugins . 'gmb-magnific' . $suffix . '.js', array( 'jquery' ), GMB_VERSION );
		wp_enqueue_script( $this->plugin_slug . '-admin-magnific-popup' );

		wp_register_script( $this->plugin_slug . '-admin-gmaps', $google_maps_api_url, array( 'jquery' ) );
		wp_enqueue_script( $this->plugin_slug . '-admin-gmaps' );

		wp_register_script( $this->plugin_slug . '-map-icons', GMB_CORE_URL . 'includes/libraries/map-icons/js/map-icons.js', array( 'jquery' ) );
		wp_enqueue_script( $this->plugin_slug . '-map-icons' );

		wp_register_script( $this->plugin_slug . '-admin-qtip', $js_plugins . 'jquery.qtip' . $suffix . '.js', array( 'jquery' ), GMB_VERSION, true );
		wp_enqueue_script( $this->plugin_slug . '-admin-qtip' );

		//Map base
		wp_register_script( $this->plugin_slug . '-admin-map-builder', $js_dir . 'admin-google-map' . $suffix . '.js', array(
			'jquery',
			'wp-color-picker'
		), GMB_VERSION );
		wp_enqueue_script( $this->plugin_slug . '-admin-map-builder' );

		//Modal magnific
		wp_register_script( $this->plugin_slug . '-admin-magnific-builder', $js_dir . 'admin-maps-magnific' . $suffix . '.js', array(
			'jquery',
			'wp-color-picker'
		), GMB_VERSION );
		wp_enqueue_script( $this->plugin_slug . '-admin-magnific-builder' );




		$api_key     = gmb_get_option( 'gmb_maps_api_key' );
		$geolocate   = gmb_get_option( 'gmb_lat_lng' );
		$post_status = get_post_status( $post->ID );

		$maps_data = array(
			'api_key'           => $api_key,
			'geolocate_setting' => isset( $geolocate[ 'geolocate_map' ] ) ? $geolocate[ 'geolocate_map' ] : 'yes',
			'default_lat'       => isset( $geolocate[ 'latitude' ] ) ? $geolocate[ 'latitude' ] : '32.715738',
			'default_lng'       => isset( $geolocate[ 'longitude' ] ) ? $geolocate[ 'longitude' ] : '-117.16108380000003',
			'plugin_url'        => GMB_PLUGIN_URL,
			'default_marker'    => apply_filters( 'gmb_default_marker', GMB_PLUGIN_URL . 'assets/img/spotlight-poi.png' ),
			'ajax_loader'       => set_url_scheme( apply_filters( 'gmb_ajax_preloader_img', GMB_PLUGIN_URL . 'assets/images/spinner.gif' ), 'relative' ),
			'snazzy'            => GMB_PLUGIN_URL . 'assets/js/admin/snazzy.json',
			'modal_default'     => gmb_get_option( 'gmb_open_builder' ),
			'post_status'       => $post_status,
			'signed_in_option'  => $signed_in_option,
			'site_name'         => get_bloginfo( 'name' ),
			'site_url'          => get_bloginfo( 'url' ),
			'i18n'              => array(
				'update_map'               => $post_status == 'publish' ? __( 'Update Map', $this->plugin_slug ) : __( 'Publish Map', $this->plugin_slug ),
				'set_place_types'          => __( 'Update Map', $this->plugin_slug ),
				'places_selection_changed' => __( 'Place selections have changed.', $this->plugin_slug ),
				'multiple_places'          => __( 'Hmm, it looks like there are multiple places in this area. Please confirm which place you would like this marker to display:', $this->plugin_slug ),
				'btn_drop_marker'          => '<span class="dashicons dashicons-location"></span>' . __( 'Drop a Marker', $this->plugin_slug ),
				'btn_drop_marker_click'    => __( 'Click on the Map', $this->plugin_slug ),
				'btn_edit_marker'          => __( 'Edit Marker', $this->plugin_slug ),
				'btn_delete_marker'        => __( 'Delete Marker', $this->plugin_slug ),
				'visit_website'            => __( 'Visit Website', $this->plugin_slug ),
				'get_directions'           => __( 'Get Directions', $this->plugin_slug )
			),
		);
		wp_localize_script( $this->plugin_slug . '-admin-map-builder', 'gmb_data', $maps_data );
	}




}