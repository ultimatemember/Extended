<?php
/**
 * Core class
 *
 * @package UM_Extended_Profile_Slug
 */

namespace UM_Extended_Profile_Slug;

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

		add_action( 'init', array( $this, 'rewrite_rules' ), 1 );
		add_action( 'template_redirect', array( $this, 'redirect' ) );
		add_action( 'template_redirect', array( $this, 'modify_nav_links' ) );
	}

	/**
	 * Rewrite rules
	 */
	public function rewrite_rules() {
		add_filter( 'page_link', array( $this, 'profile_page_link' ), 1, 2 );

		$profile_page_slug = get_post_field( 'post_name', um_get_predefined_page_id( 'user' ) );

		global $wp;
		$wp->add_query_var( 'um_uid' );
		$wp->add_query_var( 'um_profile_tab' );
		$wp->add_query_var( 'subnav' );
		add_rewrite_rule( $profile_page_slug . '/([~a-z0%-9-]+)/([~a-z0%-9-]+)/([~a-z0%-9-]+)?', 'index.php?pagename=' . $profile_page_slug . '&um_user=$matches[1]&profiletab=$matches[2]&subnav=$matches[3]', 'top' );
		add_rewrite_rule( $profile_page_slug . '/([~a-z0%-9-]+)/([~a-z0%-9-]+)/?', 'index.php?pagename=' . $profile_page_slug . '&um_user=$matches[1]&profiletab=$matches[2]', 'top' );
	}

	/**
	 * Redirect to formatted Tab Slug
	 */
	public function redirect() {
		global $wp;
		if ( isset( $_REQUEST['profiletab'] ) || isset( $_REQUEST['subnav'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$profiletab        = ! empty( $_REQUEST['profiletab'] ) ? sanitize_key( $_REQUEST['profiletab'] ) : get_query_var( 'profiletab' ); // phpcs:ignore WordPress.Security.NonceVerification
			$subnav            = ! empty( $_REQUEST['subnav'] ) ? sanitize_key( $_REQUEST['subnav'] ) : get_query_var( 'subnav' ); // phpcs:ignore WordPress.Security.NonceVerification
			$profile_page_slug = get_post_field( 'post_name', um_get_predefined_page_id( 'user' ) );
			$arr               = explode( '/', home_url( $profile_page_slug . '/' . get_query_var( 'um_user' ) ) );
			$permalink         = implode( '/', array_unique( $arr ) ) . '/' . $profiletab;

			if ( ! empty( $subnav ) ) {
				$permalink = $permalink . '/' . $subnav;
			}

			$parse_request = array_map( 'esc_attr', $_REQUEST ); // phpcs:ignore WordPress.Security.NonceVerification
			unset( $parse_request['profiletab'] );
			unset( $parse_request['subnav'] );

			$permalink = add_query_arg( $parse_request, $permalink );

			wp_safe_redirect( $permalink );

		}
	}

	/**
	 * Modify Profile URLs
	 *
	 * @param string  $permalink Permalink.
	 * @param integer $post_id Post ID.
	 */
	public function profile_page_link( $permalink, $post_id ) {
		if ( ! class_exists( 'UM' ) || did_action( 'um_profile_header' ) || ! doing_action( 'wp_head' ) ) {
			return $permalink;
		}

		if ( absint( um_get_predefined_page_id( 'user' ) ) === absint( $post_id ) ) {
			$profile_page_slug = get_post_field( 'post_name', um_get_predefined_page_id( 'user' ) );
			$arr               = explode( '/', home_url( $profile_page_slug . '/' . get_query_var( 'um_user' ) ) );
			$permalink         = implode( '/', array_unique( $arr ) );
		}

		return $permalink;
	}

	/**
	 * Modify Nav Links in Profile forms
	 */
	public function modify_nav_links() {

		$tabs       = UM()->profile()->tabs_active();
		$active_tab = UM()->profile()->active_tab();

		foreach ( $tabs as $tab_id => $tab_data ) {
			add_filter(
				'um_profile_menu_link_' . $tab_id,
				function () use ( $tab_id ) {
					if ( 'main' === $tab_id ) {
						return um_user_profile_url();
					}
					return um_user_profile_url() . $tab_id;
				}
			);
			if ( isset( $tab_data['subnav'] ) ) {
				foreach ( $tab_data['subnav'] as $id_s => $subtab ) {
					if ( get_query_var( 'profiletab' ) !== $tab_id ) {
						continue;
					}
					add_filter(
						'um_user_profile_subnav_link',
						function ( $subnav_link, $id_s, $subtab ) use ( $tab_id ) {
							$subnav_link;
							$subtab;
							return um_user_profile_url() . $tab_id . '/' . $id_s;
						},
						10,
						3
					);
				}
			}
		}
	}
	/**
	 * Get Plugin URL
	 */
	public function plugin_url() {

		if ( defined( 'UM_EXTENDED_PLUGIN_URL' ) && \UM_EXTENDED_PLUGIN_URL ) {
			return \UM_EXTENDED_PLUGIN_URL . '/src/um-profile-slug/';
		}

		return UM_EXTENDED_PROFILE_SLUG_PLUGIN_URL;
	}
}
