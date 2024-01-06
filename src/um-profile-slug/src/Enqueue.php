<?php
/**
 * Enqueue class
 *
 * @package UM_Extended_Profile_slug
 */

namespace UM_Extended_Profile_slug;

/**
 * Class Enqueue to handle all plugin initialization.
 *
 * @since 1.0.0
 */
class Enqueue extends Core {

	/**
	 * Init
	 */
	public function __construct() {
		// Frontend.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
		// Admin.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ) );
	}

	/**
	 * Frontend Enqueue scripts & styles.
	 */
	public function enqueue() {
		wp_enqueue_style( 'um-extended-profile-slug', $this->plugin_url() . '/assets/frontend/css/profile-slug.css', array(), '1.0.0', 'all' );
		wp_enqueue_script( 'um-extended-profile-slug', $this->plugin_url() . '/assets/frontend/js/profile-slug.js', array(), '1.0.0', true );
	}

	/**
	 * Admin Enqueue scripts & styles.
	 */
	public function admin_enqueue() {
		wp_enqueue_style( 'um-extended-profile-slug_admin', $this->plugin_url() . '/assets/admin/css/profile-slug.css', array(), '1.0.0', 'all' );
		wp_enqueue_script( 'um-extended-profile-slug_admin', $this->plugin_url() . '/assets/admin/js/profile-slug.js', array(), '1.0.0', true );
	}
}
