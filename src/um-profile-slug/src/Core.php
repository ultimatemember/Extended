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
	}

	/**
	 * Rewrite rules
	 */
	public function rewrite_rules() {
		add_filter( 'page_link', array( $this, 'profile_page_link' ), 1, 2 );

		$profile_page_slug = get_post_field( 'post_name', UM()->config()->permalinks['user'] );

		global $wp;
		$wp->add_query_var( 'um_uid' );
		$wp->add_query_var( 'um_profile_tab' );
		add_rewrite_rule( $profile_page_slug . '/([~a-z0%-9-]+)/([~a-z0%-9-]+)/?', 'index.php?pagename=' . $profile_page_slug . '&um_user=$matches[1]&profiletab=$matches[2]', 'top' );

		$tabs = UM()->profile()->tabs_active();

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

		if ( UM()->config()->permalinks['user'] === $post_id ) {
			$profile_page_slug = get_post_field( 'post_name', UM()->config()->permalinks['user'] );
			$arr               = explode( '/', home_url( $profile_page_slug . '/' . get_query_var( 'um_user' ) ) );
			$permalink         = implode( '/', array_unique( $arr ) );
		}

		return $permalink;
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
