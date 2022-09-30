jQuery(document).on("ready", function() {
   var strength = {
      0: "Worst ☹",
      1: "Bad ☹",
      2: "Weak ☹",
      3: "Good ☺",
      4: "Strong 💪"
   };

   var $change_pass_dom = jQuery("#um_field_password_user_password");
   var $register_pass_dom = jQuery(".um-register div[data-key='user_password'] .um-field-area, .um-password div[data-key='user_password'] .um-field-area");

   var $meter_content = "<meter id='um-sp-password-strength-meter' max='4'><div></div></meter><div id='um-sp-password-strength-text'></div>";

   $change_pass_dom.append($meter_content);
   $register_pass_dom.append($meter_content);

   jQuery("#um_field_password_user_password input[name='user_password']").on("keyup", function() {
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
      if (result.feedback.warning !== "" && um_pass_strength.show_warning) {
         result.feedback.warning = "<div class='warning'><span class='um-faicon-warning'></span> " + result.feedback.warning + "</div>";
      } else {
         result.feedback.warning = "";
      }

      if (result.feedback.suggestions.length > 0 && um_pass_strength.show_suggestions) {
         result.feedback.suggestions = "<div class='suggestions'><span class='um-faicon-info-circle'></span> " + result.feedback.suggestions + "</div>";
      } else {
         result.feedback.suggestions = "";
      }

      var $strength_text = "";
      if (um_pass_strength.show_score) {
         $strength_text = "Strength: <strong>" + strength[result.score] + "</strong>";
      } else {
         strength[result.score] = "";
      }

      // Update the text indicator
      if (val !== "") {
         text.innerHTML =
            "<div id='um-pass-strength-text'>" +
            $strength_text +
            "</div>" +
            "<div class='feedback'>" +
            result.feedback.warning +
            result.feedback.suggestions +
            "</div>";
      } else {
         text.innerHTML = "";
      }
   }
});
