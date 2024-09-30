<?php
/**
 * Core class
 *
 * @package UM_Extended_Math_Captcha\Core
 */

namespace UM_Extended_Math_Captcha;

use Kmlpandey77\MathCaptcha\Captcha as MathCaptcha;

/**
 * Class Core to handle all plugin initialization.
 *
 * @since 1.0.0
 */
class Core {

	/**
	 * PHP-based Simple Math Captcha
	 *
	 * @var \Kmlpandey77\MathCaptcha\Captcha
	 */
	public $cpa;


	/**
	 * Math Captcha field's name
	 *
	 * @var string
	 */
	public $key = 'um_math_challenge';

	/**
	 * Init
	 */
	public function __construct() {
		add_action( 'um_submit_form_register', array( $this, 'validate' ), 1 );
		add_action( 'um_after_register_fields', array( $this, 'add_field' ) );

		$this->cpa = new MathCaptcha();
	}

	/**
	 * Validate Captcha
	 *
	 * @param array $post_form Form data.
	 */
	public function validate( $post_form ) {
		if ( ! MathCaptcha::check() ) {
			UM()->form()->add_error( 'um_math_challenge', __( 'Incorrect answer. Please try again.', 'ultimate-member' ) );
		}
	}

	/**
	 * Render Captcha Field
	 */
	public function add_field() {

		$this->cpa->reset_captcha();

		$field_data = array(
			'type'        => 'text',
			'label'       => __( 'Solve this simple Math: ', 'um-math-captcha' ) . $this->cpa->get_captcha_text() . ' = ?',
			'name'        => $this->key,
			'help'        => '',
			'placeholder' => __( 'Your answer...', 'um-math-captcha' ),
			'icon'        => '',
			'default'     => null,
			'required'    => true,
			'editable'    => true,
		);

		$data = apply_filters( "um_get_field__{$this->key}", $field_data );

		?>
			<div class="um-field um-field-text">
				<?php echo wp_kses( UM()->fields()->field_label( $data['label'], $this->key, $data ), UM()->get_allowed_html() ); ?>
				<div class="um-field-area">
					<input type="text" name="<?php echo esc_attr( $data['name'] ); ?>" value="" placeholder="<?php echo esc_attr( $data['placeholder'] ); ?>" />
				</div>
			</div>
		<?php

		if ( UM()->fields()->is_error( $this->key ) ) {
			$text = UM()->fields()->show_error( $this->key );
			echo wp_kses( UM()->fields()->field_error( $text, $this->key ), UM()->get_allowed_html() );
		}
	}
}
