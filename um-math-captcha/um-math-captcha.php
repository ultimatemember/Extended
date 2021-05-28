<?php 
/**
 * Plugin Name: Ultimate Member - Math Captcha in Register form
 * Description:  This plugin adds math challenge in the Register forms
 * Version: 1.0.1
 * Author: Ultimate Member
 * Author URI: http://ultimatemember.com/
 * Text Domain: um-math-captcha
 * UM version: 2.1.20
 */
if ( function_exists( 'UM' ) && !class_exists( 'UM_Profile_Forms' ) ) {

    if( ! class_exists('MathCaptcha') ){
        require_once "includes/class-math-captcha.php";
    }
    $cpa = new MathCaptcha();
     
    add_action("um_submit_form_register","um_math_captcha_validate",1);
    function um_math_captcha_validate( $post_form ){
        global $cpa;
        if( isset( $_REQUEST['um_math_challenge'] ) ){
            $captcha_val = $_REQUEST['um_math_challenge'];
          
            if( ! $cpa->validate($captcha_val) ){
               UM()->form()->add_error('um_math_challenge', __( 'Incorrect answer. Please try again.', 'ultimate-member' ) );
		    }
        }
    }

    add_action("um_after_register_fields","um_math_captcha_field");
    function um_math_captcha_field(){
        global $cpa;
        $cpa->reset_captcha();

        $captcha_text = __('Solve this simple Math: ','um-math-captcha') . $cpa->get_captcha_text() . " = ?";
        echo $cpa->get_captcha_text($captcha_text);
        echo "<input  type='text' placeholder='".__("Your answer...","um-math-captcha")."' name='um_math_challenge' value=''/>";
        echo UM()->fields()->field_error( UM()->fields()->show_error( 'um_math_challenge' ) );
        
    }

}