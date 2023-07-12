<?php
/**
 * Core class
 *
 * @package UM_Extended_User_Shortcodes\Core
 */

namespace UM_Extended_User_Shortcodes;

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
		add_shortcode( 'um_user', array( $this, 'shortcode' ) );
	}

	/**
	 * Returns a user meta value
	 * Usage [um_user user_id="" meta_key="" ] // leave user_id empty if you want to retrive the current user's meta value.
	 * meta_key is the field name that you've set in the UM form builder
	 * You can modify the return meta_value with filter hook 'um_user_shortcode_filter__{$meta_key}'
	 *
	 * @param array $atts shortcode attributes.
	 */
	public function shortcode( $atts ) {

		$atts = shortcode_atts(
			array(
				'user_id'  => um_profile_id(),
				'meta_key' => '', //phpcs:ignore
			),
			$atts
		);

		if ( empty( $atts['meta_key'] ) ) {
			return;
		}

		$meta_value = get_user_meta( $atts['user_id'], $atts['meta_key'], true );
		if ( is_serialized( $meta_value ) ) {
			$meta_value = maybe_unserialize( $meta_value );
		}

		if ( is_array( $meta_value ) ) {
			$meta_value = implode( ',', $meta_value );
		}

		return apply_filters( 'um_user_shortcode_filter__' . $atts['meta_key'], $meta_value );

	}

}
