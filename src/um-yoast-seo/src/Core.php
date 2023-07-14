<?php
/**
 * Core class
 *
 * @package UM_Extended_Yoast_Seo\Core
 */

namespace UM_Extended_Yoast_Seo;

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

		add_action( 'wp_head', array( $this, 'profile_dynamic_meta_desc' ), 2 );

		// Remove existing UM hardcoded dynamic meta tags.
		remove_action( 'wp_head', 'um_profile_dynamic_meta_desc', 9999999 );

		add_filter( 'wpseo_sitemap_exclude_author', array( $this, 'sitemap_exclude_author' ), 10, 1 );

		add_filter( 'author_link', array( $this, 'author_link' ), 10, 3 );

		add_filter( 'wpseo_canonical', array( $this, 'user_yoast_canonical' ) );

		add_filter( 'wpseo_opengraph_type', array( $this, 'change_opengraph_type' ), 10, 1 );

		add_filter( 'wpseo_twitter_image', array( $this, 'change_opengraph_image_url' ), 10, 1 );

		add_filter( 'wpseo_opengraph_image', array( $this, 'change_opengraph_image_url' ), 10, 1 );

		add_filter( 'wpseo_opengraph_url', array( $this, 'opengraph_url' ) );

		add_filter( 'wpseo_twitter_site', array( $this, 'opengraph_site' ), 10, 1 );

		add_filter( 'wpseo_twitter_creator_account', array( $this, 'opengraph_site' ), 10, 1 );

		add_filter( 'wpseo_opengraph_title', array( $this, 'opengraph_title' ), 10, 1 );

		add_filter( 'wpseo_twitter_title', array( $this, 'opengraph_title' ), 10, 1 );

	}

	/**
	 * Add all UM Profiles to Author archive sitemap
	 *
	 * @param array $users Users.
	 */
	public function sitemap_exclude_author( $users ) {

		// WP_User_Query arguments.
		$args = array(
			'fields' => array( 'ID' ),
		);

		$args['meta_query'] = array( //phpcs:ignore db slow query ok.
			'relation' => 'OR',
			array(
				'key'     => 'wpseo_noindex_author',
				'value'   => '',
				'compare' => '=',
			),
			array(
				'key'     => 'wpseo_noindex_author',
				'compare' => 'NOT EXISTS',
			),
		);

		$args = apply_filters( 'um_yoast_seo_get_users', $args );

		$users = get_users( $args );

		return $users;
	}

	/**
	 * Change author URLs to UM Profile URLs in Author archive sitemap
	 *
	 * @param string  $link Author Link.
	 * @param integer $author_id Author ID.
	 * @param string  $author_nicename Author Name.
	 */
	public function author_link( $link, $author_id, $author_nicename ) {

		if ( function_exists( 'um_is_core_page' ) ) {

			$link = um_user_profile_url( $author_id );
		}

		return $link;
	}

	/**
	 * Change canonical URL to UM Profile URL
	 *
	 * @param string $url Canonical URL.
	 *
	 * @return string $url
	 */
	public function user_yoast_canonical( $url ) {

		if ( function_exists( 'um_is_core_page' ) ) {
			if ( um_is_core_page( 'user' ) && um_get_requested_user() ) {
					return um_user_profile_url( um_get_requested_user() );
			}
		}

		return $url;
	}

	/**
	 * Change Open Graph Type to Profile
	 *
	 * @param string $type Open Graph Type.
	 */
	public function change_opengraph_type( $type ) {

		if ( function_exists( 'um_is_core_page' ) ) {
			$type = 'profile';
		}
		return $type;
	}

	/**
	 * Change Twitter/Open Graph Image URL
	 *
	 * @param string $url Open Graph Image URL.
	 */
	public function change_opengraph_image_url( $url ) {

		if ( function_exists( 'um_is_core_page' ) ) {
			if ( um_is_core_page( 'user' ) && um_get_requested_user() ) {
				$url = um_get_user_avatar_url( um_get_requested_user() );
			}
		}
		return $url;
	}

	/**
	 * Change Open Graph URL to UM Profile URL
	 *
	 * @param string $url Open Graph URL.
	 */
	public function opengraph_url( $url ) {

		if ( function_exists( 'um_is_core_page' ) ) {
			if ( um_is_core_page( 'user' ) && um_get_requested_user() ) {
					return um_user_profile_url( um_get_requested_user() );
			}
		}

		return $url;
	}

	/**
	 * Change OpenGraph/Twitter Title to UM Profile Title.
	 *
	 * Go to UM > Settings > Misc > see "User Profile Title" & "User Profile Dynamic Meta Description"
	 *
	 * @param string $name Open graph title.
	 */
	public function opengraph_title( $name ) {

		if ( um_is_core_page( 'user' ) && um_get_requested_user() ) {

			$profile_title = UM()->options()->get( 'profile_title' );
			um_fetch_user( um_get_requested_user() );
			$profile_title = um_convert_tags( $profile_title );
			um_reset_user();

			return $profile_title;

		}

		return $name;
	}

	/**
	 * Change Twitter site/creator account to UM field twitter value
	 *
	 * @param string $site Site creator/account.
	 */
	public function um_custom_wpseo_opengraph_site( $site ) {

		if ( um_is_core_page( 'user' ) && um_get_requested_user() ) {

			um_fetch_user( um_get_requested_user() );

			$twitter_username = um_user( 'twitter' );
			if ( ! empty( $twitter_username ) ) {
					return um_custom_wpseo_get_twitter_id( $twitter_username );
			} else {
				return '';
			}
		}

		return $site;
	}

	/**
	 * Get twitter ID from string URL
	 *
	 * @param  string $id Twitter ID.
	 * @return string
	 */
	public function wpseo_get_twitter_id( $id ) {
		if ( preg_match( '`([A-Za-z0-9_]{1,25})$`', $id, $match ) ) {
				return $match[1];
		}
	}


	/**
	 * Add extra meta tags not available from Yoast SEO to the User Profile Page
	 */
	public function profile_dynamic_meta_desc() {
		if ( um_is_core_page( 'user' ) && um_get_requested_user() ) {

			um_fetch_user( um_get_requested_user() );

			$content = um_convert_tags( UM()->options()->get( 'profile_desc' ) );
			$user_id = um_user( 'ID' );

			$avatar = um_get_user_avatar_url( $user_id, 'original' );

			um_reset_user(); ?>
			<!-- UM:Yoast SEO Generated -->
			<meta name="description" content="<?php echo esc_attr( $content ); ?>">
			<meta property="og:image" content="<?php echo esc_url( $avatar ); ?>"/>
			<meta property="og:description" content="<?php echo esc_attr( $content ); ?>"/>
			<!--/UM:Yoast SEO Generated  -->
			<?php
		}
	}


}
