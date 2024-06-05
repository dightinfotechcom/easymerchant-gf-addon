/** @format */

jQuery(document).ready(function () {
	// Save API
	jQuery(document).on("click", "#api-submit", function (e) {
		e.preventDefault();
		var apiKey = jQuery("#api-key").val();
		var apiSecret = jQuery("#api-secret-key").val();
		var mode = jQuery(".mode:checked").val();
		jQuery.ajax({
			url: "<?php echo admin_url('admin-ajax.php'); ?>",
			type: "POST",
			data: {
				action: "save_api_in_database",
				api_key: apiKey,
				api_secret_key: apiSecret,
				api_mode: mode,
			},
			success: function (response) {
				jQuery(".wp-save-notice").html(
					"Api Key and Secret Key save successfully"
				);
			},
		});
	});

	// Save Form
	jQuery(document).on("change", "#gravityformlists", function () {
		var formId = jQuery(this).val();
		console.log(formId);
		jQuery.ajax({
			url: "<?php echo admin_url('admin-ajax.php'); ?>",
			type: "POST",
			data: {
				action: "save_form_in_database",
				gravityformlist: formId,
			},
			success: function (response) {
				window.location.reload();
				jQuery(".wp-save-notice").html("Form save successfully");
			},
		});
	});

	// Save field
	jQuery(document).on("click", "#submit", function (e) {
		e.preventDefault();
		var firstNameField = jQuery("#firstnamefield").val();
		var lastNameField = jQuery("#lastnamefield").val();
		var emailField = jQuery("#emailfield").val();
		var phoneField = jQuery("#phonefield").val();
		var formId = jQuery('input[name="formid"]').val();
		jQuery.ajax({
			url: "<?php echo admin_url('admin-ajax.php'); ?>",
			type: "POST",
			data: {
				action: "save_field_id_in_database",
				firstnamefield: firstNameField,
				lastnamefield: lastNameField,
				emailfield: emailField,
				phonefield: phoneField,
				formid: formId,
			},
			success: function (response) {
				jQuery(".wp-save-notice").html("Form save successfully");
			},
		});
	});
});
