
import { zxcvbn, zxcvbnOptions } from '@zxcvbn-ts/core'
import * as zxcvbnCommonPackage from '@zxcvbn-ts/language-common'
import * as zxcvbnEnPackage from '@zxcvbn-ts/language-en'
const { __ } = wp.i18n;

const options = {
  translations: um_pass_strength.translations,
  graphs: zxcvbnCommonPackage.adjacencyGraphs,
  dictionary: {
    ...zxcvbnCommonPackage.dictionary,
    ...zxcvbnEnPackage.dictionary,
  },
}

zxcvbnOptions.setOptions(options)

var strength = {
	0: __( "Worst ☹", "um-pass-strength" ),
	1: __( "Bad ☹", "um-pass-strength" ),
	2: __( "Weak ☹", "um-pass-strength" ),
	3: __(  "Good ☺", "um-pass-strength" ),
	4: __( "Strong 💪", "um-pass-strength" ),
};

var $change_pass_dom = jQuery("#um_field_password_user_password, #um_field_0_user_password");
var $register_pass_dom = jQuery(".um-register div[data-key='user_password'] .um-field-area, .um-password div[data-key='user_password'] .um-field-area");

var $meter_content = "<meter id='um-sp-password-strength-meter' max='4'><div></div></meter><div id='um-sp-password-strength-text'></div>";

$change_pass_dom.append($meter_content);
$register_pass_dom.append($meter_content);

jQuery("#um_field_password_user_password input[name='user_password']").on("keyup", function() {
	var val = jQuery(this).val();
	um_pass_strength_validate(val);
});

jQuery("#um_field_0_user_password input[name='user_password']").on("keyup", function() {
var val = jQuery(this).val();
um_pass_strength_validate(val);
});

jQuery(".um-register input[data-key='user_password'], .um-password input[data-key='user_password']").on("keyup", function() {
	var val = jQuery(this).val();
	um_pass_strength_validate(val);
});

function um_pass_strength_validate(val) {
	var result = zxcvbn(val);
	var meter = document.getElementById("um-sp-password-strength-meter");
	var text = document.getElementById("um-sp-password-strength-text");

	// Update the password strength meter
	meter.value = result.score;
	if (result.feedback.warning !== "" && um_pass_strength.show_warning && result.feedback.warning !== null ) {
	   result.feedback.warning = "<div class='warning'><span class='um-faicon-warning'></span> " + result.feedback.warning + "</div>";
	} else {
	   result.feedback.warning = "";
	}

	if (result.feedback.suggestions.length > 0 && um_pass_strength.show_suggestions) {
		result.feedback.suggestions = "<div class='suggestions'><span class='um-faicon-info-circle'></span> " + result.feedback.suggestions.join('<br/><span class=\'um-faicon-info-circle\'></span> ') + "</div>";
	} else {
	   result.feedback.suggestions = "";
	}

	var $strength_text = "";
	if (um_pass_strength.show_score) {
	   $strength_text = __( "Strength", "um-pass-strength" ) + ": <strong>" + strength[result.score] + "</strong>";
	} else {
	   strength[result.score] = "";
	}

	// Update the text indicator
	if (val !== "" && result.feedback.warning !== null ) {
	   text.innerHTML =
		  "<div id='um-pass-strength-text'>" +
		  $strength_text +
		  "</div>" +
		  "<div class='feedback'>" +
		  result.feedback.warning +
		  ( result.feedback.suggestions !== null ? result.feedback.suggestions : '' ) +
		  "</div>";
	} else {
	   text.innerHTML = "";
	}
 }
