<?php
/**
 * Load front-end scripts
 *
 * @package     GMB-Core
 * @subpackage  Functions
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */
class Google_Maps_Builder_Core_Front_End_Scripts extends Google_Maps_Builder_Core_Scripts {

	protected function hooks(){
		add_action( 'wp_enqueue_scripts', array( $this, 'load_frontend_scripts' ), 11 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_styles' ) );

	}

	/**
	 * Load Frontend Scripts
	 *
	 * Enqueues the required scripts to display maps on the frontend only.
	 *
	 * @since 0.1.0
	 */
	function load_frontend_scripts() {

		$libraries = 'places';
		$signed_in_option = false;
		if ( ! empty( $this->plugin_settings['gmb_signed_in'] ) && $this->plugin_settings['gmb_signed_in'] == 'enabled' ) {
			$signed_in_option = true;
		}

		$google_maps_api_url = $this->google_maps_url( $signed_in_option, $libraries );

		wp_register_script( 'google-maps-builder-gmaps', $google_maps_api_url, array( 'jquery' ) );
		wp_enqueue_script( 'google-maps-builder-gmaps' );

		$js_dir     = $this->front_end_js_dir();
		$js_plugins = $this->front_end_js_url();
		$suffix     = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		// Use minified libraries if SCRIPT_DEBUG is turned off
		wp_register_script( 'google-maps-builder-plugin-script', $js_dir . 'google-maps-builder' . $suffix . '.js', array( 'jquery' ), GMB_VERSION, true );
		wp_enqueue_script( 'google-maps-builder-plugin-script' );

		wp_register_script( 'google-maps-builder-maps-icons', GMB_CORE_URL . 'includes/libraries/map-icons/js/map-icons.js', array( 'jquery' ), GMB_VERSION, true );
		wp_enqueue_script( 'google-maps-builder-maps-icons' );


		wp_register_script( 'google-maps-builder-infobubble', $js_plugins . 'infobubble' . $suffix . '.js', array( 'jquery' ), GMB_VERSION, true );
		wp_enqueue_script( 'google-maps-builder-infobubble' );

		wp_localize_script( $this->plugin_slug . '-plugin-script', 'gmb_data', array() );

	}


	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    2.0
	 */
	function enqueue_frontend_styles() {

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_style( 'google-maps-builder-plugin-styles', GMB_PLUGIN_URL . 'assets/css/google-maps-builder' . $suffix . '.css', array(), GMB_VERSION );
		wp_enqueue_style( 'google-maps-builder-plugin-styles' );

		wp_register_style( 'google-maps-builder-map-icons', GMB_CORE_URL . 'includes/libraries/map-icons/css/map-icons.css', array(), GMB_VERSION );
		wp_enqueue_style( 'google-maps-builder-map-icons' );

	}

}