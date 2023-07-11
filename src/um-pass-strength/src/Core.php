<?php
/**
 * Core class
 *
 * @package UM_Extended_Password_Strength_Meter\Core
 */

namespace UM_Extended_Password_Strength_Meter;

/**
 * Class Core to handle all plugin initialization.
 *
 * @since 1.0.0
 */
class Core {

	/**
	 * Init
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Get Plugin URL
	 */
	public function plugin_url() {

		if ( defined( 'UM_EXTENDED_PLUGIN_URL' ) && \UM_EXTENDED_PLUGIN_URL ) {
			return \UM_EXTENDED_PLUGIN_URL . '/src/um-pass-strength/';
		}

		return UM_EXTENDED_PSWD_STRENGTH_METER_PLUGIN_URL;
	}

	/**
	 * Enqueue Assets
	 */
	public function enqueue_assets() {
		wp_enqueue_script( 'um-pass-strength', $this->plugin_url() . 'assets/js/um-pass-strength.js', array( 'um_scripts', 'jquery' ), '1.0.0', true );
		wp_enqueue_script( 'zxcvbn', $this->plugin_url() . 'assets/js/zxcvbn.min.js', array( 'um-pass-strength' ), '4.2.0', true );
		wp_enqueue_style( 'um-pass-strength', $this->plugin_url() . 'assets/css/um-pass-strength.css', array(), '1.0.0' );

		wp_localize_script(
			'um-pass-strength',
			'um_pass_strength',
			array(
				'show_score'       => apply_filters( 'um_pass_strength_show_score', true ),
				'show_warning'     => apply_filters( 'um_pass_strength_show_warning', true ),
				'show_suggestions' => apply_filters( 'um_pass_strength_show_suggestions', true ),
			)
		);
	}
}
