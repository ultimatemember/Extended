<?php
/**
 * Core class
 *
 * @package UM_Extended_Fields_Counter\Core
 */

namespace UM_Extended_Fields_Counter;

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
		add_filter( 'um_field_extra_atts', array( $this, 'field_length_limit' ), 10, 3 );
		add_action( 'init', array( $this, 'assets' ) );
	}

	/**
	 * Get Plugin URL
	 */
	public function plugin_url() {

		if ( defined( 'UM_EXTENDED_PLUGIN_URL' ) && \UM_EXTENDED_PLUGIN_URL ) {
			return \UM_EXTENDED_PLUGIN_URL . '/src/um-fields-counter/';
		}

		return UM_EXTENDED_FIELDS_COUNTER_PLUGIN_URL;
	}

	/**
	 * Add field attributes
	 *
	 * @param array  $field_atts Field attributes.
	 * @param string $key Field key / meta key.
	 * @param array  $data Field data.
	 */
	public function field_length_limit( $field_atts, $key, $data ) {

		if ( isset( $data['max_chars'] ) ) {
			$field_atts['data-max_chars'] = array( $data['max_chars'] );
		}

		if ( isset( $data['max_words'] ) ) {
			$field_atts['data-max_words'] = array( $data['max_words'] );
		}

		return $field_atts;
	}

	/**
	 * Register Assets
	 */
	public function assets() {
		wp_register_script( 'um-fields-counter', $this->plugin_url() . 'assets/js/um-fields-counter.js', array( 'jquery' ), '1.0.0', true );
		wp_enqueue_script( 'um-fields-counter' );
	}
}
