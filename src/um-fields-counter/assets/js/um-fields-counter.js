jQuery(document).on("ready", function() {
   jQuery(".um-profile.um-editing .um-field[data-max_chars], .um-register .um-field[data-max_chars]").each(function() {
      var me = jQuery(this);
      var field_wrap = me.find(".um-field-area");
      var max_chars = me.data("max_chars");
      var max_words = me.data("max_words");
      if (max_words > 0) {
         // Words limit
         if (me.find("input,textarea").val() == "") {
            var field_value_length = 0;
         } else {
            var field_value_length = me
               .find("input,textarea")
               .val()
               .split(" ").length;
         }

         max_chars = max_words;
      } else {
         // Character limit
         var field_value_length = me.find("input,textarea").val().length;
      }

      if (max_chars <= 0) return;
      field_wrap.append(
				'<small class="um-right um-field-counter-wrap">' +
				'<span class="um-field-text-counter">' +
				field_value_length +
				'</span>' +
				'/' +
				'<span class="um-field-max-chars">' +
				max_chars +
				'</span>' +
				'</small>' +
				'<div class="um-clear"></div>'
      );
   });

   jQuery(".um-profile.um-editing .um-field[data-max_chars], .um-register .um-field[data-max_chars]")
      .find("input,textarea")
      .on("keyup", function(e) {
         var field_max_chars = jQuery(this)
            .parent(".um-field-area")
            .find(".um-field-max-chars")
            .text();
         if (field_max_chars <= 0) return;

         var max_words = jQuery(this)
            .parent(".um-field-area")
            .parent(".um-field")
            .data("max_words");
         if (max_words > 0) {
            // Words limit
            if (jQuery(this).val() == "") {
               var field_length = 0;
            } else {
               var field_length = jQuery(this)
                  .val()
                  .split(" ").length;
            }
            field_max_chars = max_words;
         } else {
            // Character limit
            var field_length = jQuery(this).val().length;
         }

         var field_counter = jQuery(this)
            .parent(".um-field-area")
            .find(".um-field-text-counter");

         var field_counter_wrap = jQuery(this)
            .parent(".um-field-area")
            .find(".um-field-counter-wrap");

         if (field_length <= field_max_chars) {
            jQuery(this)
               .parent(".um-field-area")
               .parent(".um-field")
               .find(".um-field-error")
               .remove();
         }

         if (field_length > field_max_chars) {
            field_counter_wrap.css("color", "red");
            e.preventDefault();
         } else {
            field_counter_wrap.css("color", "");
         }
         field_counter.text(field_length);
      });
});
