<?php
/**
 * Init the extension.
 *
 * @package um_ext\um_math_captcha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class UM_Math_Captcha
 */
class UM_Math_Captcha {

	/**
	 * PHP-based Simple Math Captcha
	 *
	 * @var \MathCaptcha
	 */
	public $cpa;


	/**
	 * Math Captcha field's name
	 *
	 * @var string
	 */
	public $key = 'um_math_challenge';


	/**
	 * An instance of the class.
	 *
	 * @var UM_Math_Captcha
	 */
	private static $instance;


	/**
	 * Creates an instance of the class.
	 *
	 * @return UM_Math_Captcha
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 * UM_Math_Captch constructor.
	 */
	public function __construct() {
		if ( ! class_exists( 'MathCaptcha' ) ) {
			require_once 'class-math-captcha.php';
		}
		$this->cpa = new MathCaptcha();

		add_action( 'um_after_register_fields', array( $this, 'add_field' ), 10, 1 );
		add_action( 'um_submit_form_errors_hook__registration', array( $this, 'validate' ), 10, 2 );
	}


	/**
	 * Add Math Captcha field to the registration form.
	 *
	 * @param array $args UM form data.
	 */
	public function add_field( $args ) {
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
				<?php echo UM()->fields()->field_label( $data['label'], $this->key, $data ); ?>
				<div class="um-field-area">
					<input type="text" name="<?php echo esc_attr( $data['name'] ); ?>" value="" placeholder="<?php echo esc_attr( $data['placeholder'] ); ?>" />
				</div>
			</div>
		<?php

		if ( UM()->fields()->is_error( $this->key ) ) {
			$text = UM()->fields()->show_error( $this->key );
			echo UM()->fields()->field_error( $text, $this->key );
		}
	}


	/**
	 * Validate Math Captcha field when the registration form has been submitted.
	 *
	 * @param array $post_form Submission array.
	 * @param array $form_data UM form data.
	 */
	public function validate( $post_form, $form_data ) {
		if ( empty( $post_form[ $this->key ] ) ) {
			UM()->form()->add_error( $this->key, __( 'Math Captcha is required.', 'um-math-captcha' ) );
		} elseif ( ! $this->cpa->validate( $post_form[ $this->key ] ) ) {
			UM()->form()->add_error( $this->key, __( 'Incorrect answer. Please try again.', 'um-math-captcha' ) );
		}
	}

}