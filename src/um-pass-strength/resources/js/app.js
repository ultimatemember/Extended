
import { zxcvbn, zxcvbnOptions } from '@zxcvbn-ts/core'
import * as zxcvbnCommonPackage from '@zxcvbn-ts/language-common'
import * as zxcvbnEnPackage from '@zxcvbn-ts/language-en'

  
  const requireUMExtendedMatcher = {
	Matching: class MatchUpperCase {
	
	  match({ password }) {
		const matches = [];
		if ( password.length > 0 && password == password.toLowerCase() ) {
		  matches.push({
			pattern: 'requireUpperCase',
			token: password,
			i: 0,
			j: password.length -1 ,
		  })
		}

		if ( password.length > 0 && password == password.toUpperCase() ) {
			matches.push({
			  pattern: 'requireLowerCase',
			  token: password,
			  i: 0,
			  j: password.length -1 ,
			})
		  }

		if (password.length > 0 && password.length <= this.minLength) {
			matches.push({
			  pattern: 'minLength',
			  token: password,
			  i: 0,
			  j: password.length - 1,
			})
		  }

		  if (password.search(/[0-9]/) < 0) {
			matches.push({
				pattern: 'requireDigit',
				token: password,
				i: 0,
				j: password.length - 1,
			 })
		}
		return matches
	  }
	},
	feedback(match, isSoleMatch) {

		if( match.pattern === 'requireLowerCase' ) {
			return {
				warning: um_pass_strength.translations.warnings.requireLowerCase,
				suggestions: new Array(),
			  }
		} else if( match.pattern === 'requireUpperCase' ) {
			return {
				warning: um_pass_strength.translations.warnings.requireUpperCase,
				suggestions: new Array(),
			  }
		} else if( match.pattern === 'minLength' ) {
			return {
				warning: um_pass_strength.translations.warnings.minLength,
				suggestions: new Array(),
				}
			} else if( match.pattern === 'requireDigit' ) {
				return {
					warning: um_pass_strength.translations.warnings.requireDigit,
					suggestions: new Array(),
					}
			}
	
	},
	scoring(match) {
	  // The length of the password is multiplied by 3 to create a higher score the more characters are added.
	  return match.token.length * 10
	},
  }


const options = {
  translations: um_pass_strength.translations,
  graphs: zxcvbnCommonPackage.adjacencyGraphs,
  dictionary: {
    ...zxcvbnCommonPackage.dictionary,
    ...zxcvbnEnPackage.dictionary,
  },
}

zxcvbnOptions.setOptions(options)
zxcvbnOptions.addMatcher('minLength', requireUMExtendedMatcher)
zxcvbnOptions.addMatcher('requireUpperCase', requireUMExtendedMatcher)
zxcvbnOptions.addMatcher('requireLowerCase', requireUMExtendedMatcher)
zxcvbnOptions.addMatcher('requireDigit', requireUMExtendedMatcher)

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

	var strength = um_pass_strength.translations.strength;

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
	   $strength_text = um_pass_strength.translations.strength_label + ": <strong>" + strength[result.score] + "</strong>";
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
