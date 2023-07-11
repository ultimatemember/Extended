<?php
/**
 * Core class
 *
 * @package UM_Extended_Math_Captcha\Core
 */

namespace UM_Extended_Math_Captcha;

use Kmlpandey77\MathCaptcha\Captcha;

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
		add_action( 'um_submit_form_register', array( $this, 'captcha_validate' ), 1 );
		add_action( 'um_after_register_fields', array( $this, 'captcha_field' ) );
	}

	/**
	 * Validate Captcha
	 *
	 * @param array $post_form Form data.
	 */
	public function captcha_validate( $post_form ) {
		if ( ! Captcha::check() ) {
			UM()->form()->add_error( 'um_math_challenge', __( 'Incorrect answer. Please try again.', 'ultimate-member' ) );
		}
	}

	/**
	 * Render Captcha Field
	 */
	public function captcha_field() {

		echo esc_attr__( 'Solve this simple Math: ', 'um-math-captcha' ) . esc_attr( new Captcha() ) . ' = ?';
		echo "<input  type='text' placeholder='" . esc_attr__( 'Your answer...', 'um-math-captcha' ) . "' name='captcha' value=''/>";
		if ( UM()->form()->has_error( 'um_math_challenge' ) ) {
			echo wp_kses( UM()->fields()->field_error( UM()->fields()->show_error( 'um_math_challenge' ) ), UM()->get_allowed_html( 'template' ) );
		}
	}
}
