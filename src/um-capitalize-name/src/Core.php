<?php
/**
 * Core class
 *
 * @package UM_Extended_Capitalize_Names\Core
 */

namespace UM_Extended_Capitalize_Names;

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
		add_action( 'init', array( $this, 'hooks' ) );
	}

	/**
	 * Apply Capitalization
	 */
	public function hooks() {
		/**
		 * Force capitalization
		 *
		 * By default, this is set to TRUE. This may affect performance of the server.
		 * If set to FALSE, display names will capitlized on form submissions instead.
		 */
		$is_forced = apply_filters( 'um_extended_capitalize_name_forced', true );
		if ( $is_forced ) {
			add_filter( 'um_user_display_name_filter', array( $this, 'apply_capitalization' ), 10, 1 );
			add_filter( 'um_user_first_name_case', array( $this, 'apply_capitalization' ), 10, 1 );  // based on this hook 'um_user_{$data}_case'.
			add_filter( 'um_user_last_name_case', array( $this, 'apply_capitalization' ), 10, 1 ); // based on this hook 'um_user_{$data}_case'.
			add_filter( 'um_profile_first_name__filter', array( $this, 'apply_capitalization' ), 10, 1 );
			add_filter( 'um_profile_last_name__filter', array( $this, 'apply_capitalization' ), 10, 1 );
		} else {
			add_filter( 'pre_user_display_name', array( $this, 'apply_capitalization' ), 10, 1 );
			add_filter( 'pre_user_first_name', array( $this, 'apply_capitalization' ), 10, 1 );
			add_filter( 'pre_user_last_name', array( $this, 'apply_capitalization' ), 10, 1 );
		}

		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_filter( 'manage_users_custom_column', array( $this, 'column_content' ), 10, 3 );
		add_filter( 'manage_users_columns', array( $this, 'display_name_column' ), 1 );

	}

	/**
	 * Apply capitlization to the Display Name globally
	 *
	 * @since 1.0.0
	 *
	 * @param string $name Name of a User.
	 *
	 * @return string`
	 */
	public function apply_capitalization( $name ) {

		if ( ! extension_loaded( 'mbstring' ) ) {
			return $name;
		}

		return $this->validate_names( $name );

	}

	/**
	 * Validate Names with different cases
	 *
	 * @since 1.0.0
	 *
	 * @param string $name Name.
	 * @param string $encoding String encoding.
	 *
	 * @source https://github.com/eumanito/php-capitalize-names
	 *
	 * @return string
	 */
	public function validate_names( $name, $encoding = 'UTF-8' ) {

		$word_splitters = apply_filters(
			'um_extended_capitlize_name__word_splitters',
			array( ' ', '-', 'O\’', 'L\’', 'D’', 'St.', 'Mc', 'Dall\'', 'l’', 'd’', 'a’', 'o’' )
		);

		$lowercase_exceptions = apply_filters(
			'um_extended_capitlize_name__lowercase_exceptions',
			array( 'the', 'van', 'den', 'von', 'und', 'der', 'da', 'of', 'and', 'd’', 'das', 'do', 'dos', 'e', 'el' )
		);

		$uppercase_exceptions = apply_filters(
			'um_extended_capitlize_name__uppercase_exceptions',
			array( 'III', 'IV', 'VI', 'VII', 'VIII', 'IX', 'ME', 'EIRELI', 'EPP', 'S/A', 'S.A', 'LTDA' )
		);

		$string = mb_strtolower( $name, $encoding );
		$string = str_replace( '\'', '’', $string );

		foreach ( $word_splitters as $delimiter ) {
			$words    = explode( $delimiter, $string );
			$newwords = array();

			foreach ( $words as $word ) {
				if ( in_array( mb_strtoupper( $word, $encoding ), $uppercase_exceptions, true ) ) {
					$word = mb_strtoupper( $word, $encoding );
				} else {
					if ( ! in_array( $word, $lowercase_exceptions, true ) ) {
						$word = mb_strtoupper( mb_substr( $word, 0, 1 ), $encoding ) . mb_substr( $word, 1 );
					}
				}

				$newwords[] = $word;
			}

			if ( in_array( mb_strtolower( $delimiter, $encoding ), $lowercase_exceptions, true ) ) {
				$delimiter = mb_strtolower( $delimiter, $encoding );
			}

			$string = join( $delimiter, $newwords );
		}

		return $string;
	}

	/**
	 * Display required server module
	 */
	public function admin_notices() {
		if ( ! extension_loaded( 'mbstring' ) ) {
			echo '<div class="notice notice-warning"><p>';
			echo wp_kses( 'Ultimate Member - Capitalize Name: PHP <strong>mbstring</strong> extension is not installed on your server.', 'um-extended' );
			echo '</p></div>';
		}
	}

	/**
	 * Adds Display Name in the column
	 *
	 * @param string  $value Column value.
	 * @param string  $column_name Column name.
	 * @param integer $user_id the user ID in the loop.
	 *
	 * @since 1.0.0
	 * @return string $value modified value.
	 */
	public function column_content( $value, $column_name, $user_id ) {

		if ( 'um_display_name' === $column_name ) {
			um_fetch_user( $user_id );
			return um_user( 'display_name' );
		}
		um_reset_user();

		return $value;
	}

	/**
	 * Adds Custom Column To Users List Table
	 *
	 * @param array $columns User table columns.
	 */
	public function display_name_column( $columns ) {
		$columns['um_display_name'] = 'UM Display Name';
		return $columns;
	}
}
