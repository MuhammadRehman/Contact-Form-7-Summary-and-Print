<?php
/**
 * Handlev view sumary & print button
 *
 * @package cf7-summary-and-print
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class to Show Summary after form submission with print button
 *
 * @since 1.0
 * @version 1.1
 */
class CF7_SP_Summary_Print {

	/**
	 * Construct function of a class
	 *
	 * @since 1.0
	 * @version 1.0
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'register_sp_script' ) );
		add_action('admin_notices', array( $this, 'notice_for_settings' ) );
	}
	
	public function notice_for_settings(){
		if ( empty( get_option( 'cf7_sp_notice' ) ) ) {
			 echo '<div class="notice notice-warning is-dismissible cf7-summary-print-notice">
			     <p><strong>Contact Form 7 Summary & Print</strong> settings have moved on separate page. Go to <a href="'.admin_url().'admin.php?page=cf7-summary-Print">settings page</a>.</p>
			 </div>';
		}
	}


	/**
	 * Register Script
	 *
	 * @since 1.0
	 * @version 1.1
	 */
	public function register_sp_script() {
		global $post;

		$summary = array();
		
		$cf7_form_data = get_option( 'cf7_summary_print', true );

		if( !empty( $cf7_form_data ) ) {
			$summary['title'] = $cf7_form_data['cf7-summary-title'];
			$summary['print_btn_txt'] = $cf7_form_data['cf7-summary-btn'];
			$summary['message_check'] = $cf7_form_data['cf7-summary-msg-enabled'];
			$summary['message'] = $cf7_form_data['cf7-summary-msg'];
			$summary['cf7-enabled'] = $cf7_form_data['cf7-enabled'];
			$summary['cf7-enabled-for'] = $cf7_form_data['cf7-enabled-for'];
		} else {
			// Loop to get all the shortcodes that running on curret page.
			$cf7_form = $this->get_cf7_form();
			if ( ! is_array( $cf7_form ) ) {
				return;
			}

			foreach ( $cf7_form as $form ) {
				// If this is contant form 7 shortcode.
				if ( isset( $form['id'] ) && 'wpcf7_contact_form' === get_post_type( $form['id'] ) ) {
					// Getting Summary Settings.
					$summary = get_post_meta( $form['id'], '_cf7_sp_summary', true );
					break; // exit from loop.
				}
			}

		}

		if ( empty( $summary ) ) {
			return;
		}					

		// Register the script.
		wp_register_script( 'cf7_sp_js', plugins_url( 'assets/js/cf7-sp-script.js', CF7_SP_THIS ), array( 'jquery' ), CF7_SP_VERSION, true );

		// Modify Summary Settings data or add Additional text on print window.
		$summary = apply_filters( 'cf7_sp_summary_settings', $summary );

		// Localize the script.
		$localize_data = array(
			'summay_title'            => $summary['title'],
			'summay_print_btn_text'   => $summary['print_btn_txt'],
			'summay_message_check'    => $summary['message_check'],
			'summay_message'          => $summary['message'],
			'summay_enabled_for'         => $summary['cf7-enabled-for'],
			//'summary_additional_text' => $summary['additional_text'],
		);
	
		
		wp_localize_script( 'cf7_sp_js', 'cf7_sp', $localize_data );

		// Enqueued script with localized data.
		if ( '1' === $summary['cf7-enabled'] ) {
			wp_enqueue_script( 'cf7_sp_js' );
		}
	}

	/**
	 * Getting Contact Form 7 shortcode Attributes
	 *
	 * @since 1.0
	 * @version 1.0
	 */
	public function get_cf7_form() {
		global $post;
		$result = array();

		// get shortcode regex pattern WordPress function.
		$pattern = get_shortcode_regex();

		if ( preg_match_all( '/' . $pattern . '/s', $post->post_content, $matches ) ) {
			$keys   = array();
			$result = array();
			foreach ( $matches[0] as $key => $value ) {

				// $matches[3] return the shortcode attribute as string. Replace space with '&' for parse_str() function.
				$get = str_replace( ' ', '&', $matches[3][ $key ] );
				parse_str( $get, $output );

				// get all shortcode attribute keys.
				$keys     = array_unique( array_merge( $keys, array_keys( $output ) ) );
				$result[] = $output;

			}

			if ( $keys && $result ) {

				// Loop the result array and add the missing shortcode attribute key.
				foreach ( $result as $key => $value ) {

					// Loop the shortcode attribute key.
					foreach ( $keys as $attr_key ) {
						$result[ $key ][ $attr_key ] = isset( $result[ $key ][ $attr_key ] ) ? str_replace( '"', '', $result[ $key ][ $attr_key ] ) : null;
					}

					// sort the array key.
					ksort( $result[ $key ] );
				}
			}

			// display the result.
			return $result;
		}
	}
}

$cf7_sp_summary_print = new CF7_SP_Summary_Print();
