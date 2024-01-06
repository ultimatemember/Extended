<?php
/**
 * Core class
 *
 * @package UM_Extended_Pass_Strength\Core
 */

namespace UM_Extended_Pass_Strength;

define( 'UM_EXTENDED_PSWD_STRENGTH_METER_TEXTDOMAIN', 'um-pass-strength' );

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
		add_action( 'plugins_loaded', array( $this, 'load_translations' ), 0 );
	}

	/**
	 * Add languages
	 */
	public function load_translations() {

		$locale = ( get_locale() !== '' ) ? get_locale() : 'en_US';
		load_textdomain( UM_EXTENDED_PSWD_STRENGTH_METER_TEXTDOMAIN, WP_LANG_DIR . '/plugins/' . UM_EXTENDED_PSWD_STRENGTH_METER_TEXTDOMAIN . '-' . $locale . '.mo' );
		load_plugin_textdomain( UM_EXTENDED_PSWD_STRENGTH_METER_TEXTDOMAIN, false, $this->plugin_dir() . '/languages/' );
	}

	/**
	 * Get Plugin URL
	 *
	 * @since 1.0.0
	 */
	public function plugin_url() {

		if ( defined( 'UM_EXTENDED_PLUGIN_URL' ) && \UM_EXTENDED_PLUGIN_URL ) {
			return \UM_EXTENDED_PLUGIN_URL . '/src/um-pass-strength/';
		}

		return UM_EXTENDED_PSWD_STRENGTH_METER_PLUGIN_URL;
	}

	/**
	 * Get Plugin Path
	 *
	 * @since 1.0.1
	 */
	public function plugin_dir() {

		if ( defined( 'UM_EXTENDED_PLUGIN_DIR' ) && \UM_EXTENDED_PLUGIN_DIR ) {
			return \UM_EXTENDED_PLUGIN_DIR . '/src/um-pass-strength/';
		}

		return UM_EXTENDED_PSWD_STRENGTH_METER_PLUGIN_DIR;
	}
	/**
	 * Enqueue Assets
	 */
	public function enqueue_assets() {
		wp_enqueue_script( 'um-pass-strength', $this->plugin_url() . 'assets/js/um-pass-strength.min.js', array( 'um_scripts', 'jquery' ), '1.0.0', true );
		wp_enqueue_style( 'um-pass-strength', $this->plugin_url() . 'assets/css/um-pass-strength.min.css', array(), '1.0.0' );

		$translations = array(
			'warnings'       => array(
				'straightRow'       => __( 'Straight rows of keys on your keyboard are easy to guess.', 'um-pass-strength' ),
				'keyPattern'        => __( 'Short keyboard patterns are easy to guess.', 'um-pass-strength' ),
				'simpleRepeat'      => __( 'Repeated characters like \'aaa\' are easy to guess.', 'um-pass-strength' ),
				'extendedRepeat'    => __( 'Repeated character patterns like \'abcabcabc\' are easy to guess.', 'um-pass-strength' ),
				'sequences'         => __( 'Common character sequences like \'abc\' are easy to guess.', 'um-pass-strength' ),
				'recentYears'       => __( 'Recent years are easy to guess.', 'um-pass-strength' ),
				'dates'             => __( 'Dates are easy to guess.', 'um-pass-strength' ),
				'topTen'            => __( 'This is a heavily used password.', 'um-pass-strength' ),
				'topHundred'        => __( 'This is a frequently used password.', 'um-pass-strength' ),
				'common'            => __( 'This is a commonly used password.', 'um-pass-strength' ),
				'similarToCommon'   => __( 'This is similar to a commonly used password.', 'um-pass-strength' ),
				'wordByItself'      => __( 'Single words are easy to guess.', 'um-pass-strength' ),
				'namesByThemselves' => __( 'Single names or surnames are easy to guess.', 'um-pass-strength' ),
				'commonNames'       => __( 'Common names and surnames are easy to guess.', 'um-pass-strength' ),
				'userInputs'        => __( 'There should not be any personal or page related data.', 'um-pass-strength' ),
				'pwned'             => __( 'Your password was exposed by a data breach on the Internet.', 'um-pass-strength' ),
			),
			'suggestions'    => array(
				'l33t'                  => __( 'Avoid predictable letter substitutions like \'@\' for \'a\'.', 'um-pass-strength' ),
				'reverseWords'          => __( 'Avoid reversed spellings of common words.', 'um-pass-strength' ),
				'allUppercase'          => __( 'Capitalize some, but not all letters.', 'um-pass-strength' ),
				'capitalization'        => __( 'Capitalize more than the first letter.', 'um-pass-strength' ),
				'dates'                 => __( 'Avoid dates and years that are associated with you.', 'um-pass-strength' ),
				'recentYears'           => __( 'Avoid recent years.', 'um-pass-strength' ),
				'associatedYears'       => __( 'Avoid years that are associated with you.', 'um-pass-strength' ),
				'sequences'             => __( 'Avoid common character sequences.', 'um-pass-strength' ),
				'repeated'              => __( 'Avoid repeated words and characters.', 'um-pass-strength' ),
				'longerKeyboardPattern' => __( 'Use longer keyboard patterns and change typing direction multiple times.', 'um-pass-strength' ),
				'anotherWord'           => __( 'Add more words that are less common.', 'um-pass-strength' ),
				'useWords'              => __( 'Use multiple words, but avoid common phrases.', 'um-pass-strength' ),
				'noNeed'                => __( 'You can create strong passwords without using symbols, numbers, or uppercase letters.', 'um-pass-strength' ),
				'pwned'                 => __( 'If you use this password elsewhere, you should change it.', 'um-pass-strength' ),
			),
			'timeEstimation' => array(
				'ltSecond'  => __( 'less than a second', 'um-pass-strength' ),
				'second'    => __( '{base} second', 'um-pass-strength' ),
				'seconds'   => __( '{base} seconds', 'um-pass-strength' ),
				'minute'    => __( '{base} minute', 'um-pass-strength' ),
				'minutes'   => __( '{base} minutes', 'um-pass-strength' ),
				'hour'      => __( '{base} hour', 'um-pass-strength' ),
				'hours'     => __( '{base} hours', 'um-pass-strength' ),
				'day'       => __( '{base} day', 'um-pass-strength' ),
				'days'      => __( '{base} days', 'um-pass-strength' ),
				'month'     => __( '{base} month', 'um-pass-strength' ),
				'months'    => __( '{base} months', 'um-pass-strength' ),
				'year'      => __( '{base} year', 'um-pass-strength' ),
				'years'     => __( '{base} years', 'um-pass-strength' ),
				'centuries' => __( 'centuries', 'um-pass-strength' ),
			),
		);

		wp_localize_script(
			'um-pass-strength',
			'um_pass_strength',
			array(
				'show_score'       => apply_filters( 'um_pass_strength_show_score', true ),
				'show_warning'     => apply_filters( 'um_pass_strength_show_warning', true ),
				'show_suggestions' => apply_filters( 'um_pass_strength_show_suggestions', true ),
				'translations'     => $translations,
			)
		);
	}
}
