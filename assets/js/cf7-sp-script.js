/**
 * Handle JS Events
 *
 * @package cf7-summary-and-print
 */

/**
 * Trigger when CF7 submit and sent an email notif ication
 *
 * @since 1.0
 * @version 1.2
 */

document.addEventListener(
	'wpcf7mailsent',
	function ( event ) {
		
		if( cf7_sp.summay_enabled_for.includes( event['detail']['contactFormId'] ) == true ) {			

			var summary 	= '';
			var input_label = '';
			var input_value = ''

			// Make sure title is not empty.
			if ( cf7_sp.summay_title != '' ) {
				summary += '<h3>' + cf7_sp.summay_title + '</h3>';
			}

			if ( cf7_sp.summay_message_check == '1' ) {

				// Use regex to get tags.
				var regex 		   = /\[(.*?)\]/g;
				var summay_message = cf7_sp.summay_message;

				var replace_tags = '';
				while ( matches = summay_message.match( regex ) ) {

					if ( matches[0] == '' || typeof matches[0] == 'undefined' ) {
						break;
					}

					var match = matches[0];
					match     = match.replace( '[','' );
					match     = match.replace( ']','' );

					var fields_value = jQuery( 'input[name="' + match + '"],select[name="' + match + '"],textarea[name="' + match + '"]' ).val();

					if ( typeof fields_value != 'undefined' ) {
						summay_message = cf7_sp_replace_tag( summay_message, '[' + match + ']', fields_value );
						summay_message = summay_message.replace( /\n/g,'<br>' );
					} else {
						var multi_select = '';
						jQuery( 'input[name="' + match + '[]"]:checked' ).each(
							function () {
								multi_select += jQuery( this ).val() + ' ';
							}
						);
						summay_message = cf7_sp_replace_tag( summay_message, '[' + match + ']', multi_select );
						summay_message = summay_message.replace( /\n/g,'<br>' );
					}
				}
				summary += summay_message;
			} else {
				// Getting all the form data.
				jQuery( 'form.wpcf7-form label' ).each(
					function () {

						input_value = '';
						input_value = '';

						if ( jQuery( this ).find( 'input[type=checkbox],input[type=radio]' ) && jQuery( this ).find( 'input[type=checkbox],input[type=radio]' ).is( ':checked' ) ) {
							jQuery( this ).find( 'input[type="checkbox"]:checked,input[type=radio]:checked' ).each(
								function () {
									var elem     = jQuery( this );
									input_label  = elem.attr( 'name' );
									input_value += elem.val() + '<br>';
								}
							);
						} else if ( jQuery( this ).find( 'input,select,textarea' ) ) {

							var elem = jQuery( this ).find( 'input,select,textarea' );

							input_label = elem.attr( 'name' ); // Getting field label.
							input_value = elem.val(); // Getting field value.
						}

						// dump all the form data in a variable to print them after form submission.
						// Skip all unwanted fields.
						if ( typeof input_label != 'undefined' && typeof input_value != 'undefined' ) {
							summary += '<div class="cf7-row"><strong>' + cf7_sp_label_formating( input_label ) + '</strong> <p>' + input_value + '</p></div>';
						}
					}
				);
			}

			// Add additional text at the bottom of the summary page.
			//if ( cf7_sp.summary_additional_text != '' && cf7_sp.summary_additional_text != null ) {
			//	summary += cf7_sp.summary_additional_text;
			//}

			// Print Button.
			summary += cf7_sp_print_btn();

			// Show the summary.
			jQuery( '.wpcf7' ).html( summary );
		
		}
	},
	false
);

/**
 * Clean Field's label
 *
 * @param cf7_label
 *
 * @since 1.0
 * @version 1.0
 */
function cf7_sp_label_formating( cf7_label ) {
	var cf7_label = cf7_label.replace( /-/g, ' ' );
	cf7_label     = cf7_label.replace( /\[/g, ' ' );
	cf7_label     = cf7_label.replace( /]/g, ' ' );
	cf7_label     = cf7_label.toLowerCase().replace(
		/\b[a-z]/g,
		function ( letter ) {
			return letter.toUpperCase();
		}
	);

	return cf7_label;
}

/**
 * Print Button
 *
 * @since 1.0
 * @version 1.0
 */
function cf7_sp_print_btn() {

	var html = '';
	if ( cf7_sp.summay_print_btn_text != "" ) {
		var html = '<div><input type="button" id="cf7-print-btn" value="' + cf7_sp.summay_print_btn_text + '" onclick="cf7_sp_print_form()" /></div>';
	}

	return html;
}

/**
 * Print only form area
 *
 * @since 1.0
 * @version 1.0
 */
function cf7_sp_print_form() {

	jQuery( '#cf7-print-btn' ).hide();
	var whole_content = jQuery( 'body' ).html();
	var print_form = jQuery( 'div.wpcf7' ).html();

	jQuery( 'body' ).html( print_form );
	window.print();

	setTimeout(
		function () {
			jQuery( 'body' ).html( whole_content );
			jQuery( '#cf7-print-btn' ).show();
		},
		10
	);
}

/**
 * Replace bracket tags to Field's value
 *
 * @since 1.1
 * @version 1.0
 */
function cf7_sp_replace_tag( str,replaceWhat,replaceTo ) {
	replaceWhat = replaceWhat.replace( /[-\/\\^$* + ?.()|[\]{}]/g, '\\$&' );
	var re      = new RegExp( replaceWhat, 'g' );
	return str.replace( re,replaceTo );
}
