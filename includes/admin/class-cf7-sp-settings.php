<?php
/**
 * Handle Settings Fields
 *
 * @package cf7-summary-and-print
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class to Add CF7 Setting Tab
 *
 * @since 1.0
 * @version 1.2
 */
class CF7_SP_Settings {

	/**
	 * Construct function of a class
	 *
	 * @since 1.0
	 * @version 1.1
	 */
	public function __construct() {
		add_filter( 'wpcf7_editor_panels', array( $this, 'add_summary_tab' ), 10, 1 );
		add_filter( 'wpcf7_default_template', array( $this, 'set_default_template' ), 10, 2 );
		add_filter( 'wpcf7_contact_form_properties', array( $this, 'add_new_property' ), 10, 2 );
		add_filter( 'wpcf7_save_contact_form', array( $this, 'save_summary_tab' ), 10, 1 );
		add_filter( 'admin_enqueue_scripts', array( $this, 'admin_script_style' ) );
		add_action( 'admin_menu', array( $this, 'register_sub_menu_cf7_summary' ) );
		add_action( 'wp_ajax_cf7_hide_summary_notice', array( $this, 'cf7_hide_summary_notice_func' ) );
	}
	

	function cf7_hide_summary_notice_func() {
		global $wpdb; // this is how you get access to the database

		if( isset( $_POST['hide_notice'] ) ) {
			update_option( 'cf7_sp_notice', true );
		}

		wp_die(); // this is required to terminate immediately and return a proper response
	}

	public function register_sub_menu_cf7_summary() {
		add_submenu_page( 'wpcf7', 'Summary & Print', 'Summary & Print', 'manage_options', 'cf7-summary-Print', array( $this, 'cf7_summary_print_callback' ) );
	}
	public function cf7_summary_print_callback() {
		
		$cf7_summary_form = $this->cf7_save_settings();

		$html = '';
		if( $cf7_summary_form !== false ) {
			$html .= $cf7_summary_form;
		}
		
		$cf7_form_data = get_option( 'cf7_summary_print', true );

		$cf7_form_list = get_posts( array( 'post_type' => 'wpcf7_contact_form' ) );
		
		$form_dropdown = '';		
		foreach( $cf7_form_list as $cf7_form ) {
			$form_dropdown .= '<option value="'.$cf7_form->ID.'" '.( isset( $cf7_form_data['cf7-enabled-for'] ) && in_array( $cf7_form->ID, $cf7_form_data['cf7-enabled-for'] ) ? 'selected' : '' ).' >'.$cf7_form->post_title.'</option>';
		}

		$html .= '<h1>Contact Form 7 Summary & Print</h1>
			<hr>
			<form action="#" method="post">
			<table class="form-table" role="presentation">

			<tbody>
			<tr>
			<th scope="row"><label for="cf7-enabled">Enable</label></th>
			<td><input name="cf7-enabled" type="checkbox" id="cf7-enabled" '. ( isset( $cf7_form_data['cf7-enabled'] ) ? 'checked' : '' ) .' value="1" class="regular-text">
			<p class="description" id="cf7-enabled-description">Enable CF7 Summary & Print</p></td>
			</tr>
			<tr>
			<th scope="row"><label for="cf7-enabled-for">Select CF7 Forms</label></th>
			<td><select name="cf7-enabled-for[]" class="cf7-form-list regular-text" multiple="multiple">'.$form_dropdown.'</select>
			<p class="description" id="cf7-enabled-description">Select forms which you would like to enable for summary</p></td>
			</tr>
			
			<th scope="row"><label for="cf7-summary-title">Summary Title</label></th>
			<td><input name="cf7-summary-title" type="text" id="cf7-summary-title" value="'. ( isset( $cf7_form_data['cf7-summary-title'] ) ? $cf7_form_data['cf7-summary-title'] : 'Summary' ) .'" class="regular-text">
			<p class="description" id="cf7-summary-title-description">Add summary title</p></td>
			</tr>

<tr>
			<th scope="row"><label for="cf7-summary-msg-enabled">Display message on summary</label></th>
			<td><input name="cf7-summary-msg-enabled" type="checkbox" id="cf7-summary-msg-enabled" '. ( isset( $cf7_form_data['cf7-summary-msg-enabled'] ) ? 'checked' : '' ) .' value="1" class="regular-text">
			<p class="description" id="cf7-summary-msg-description">When you enabled this option a message will apear after the form submit instead of form fields values.</p></td>
			</tr>
			
			<tr>
			<th scope="row"><label for="cf7-summary-msg">Message</label></th>
			<td><textarea id="cf7-summary-msg" name="cf7-summary-msg" rows="5" col="10">'. ( isset( $cf7_form_data['cf7-summary-msg'] ) ? $cf7_form_data['cf7-summary-msg'] : 'Hi [your-name], Thank you for contacting us we will contact you on your email [your-email].' ) .'</textarea>
			<p class="description" id="cf7-summary-msg-description">Enter message to show on summary page</p></td>
			</tr>
			
			<tr>
			<th scope="row"><label for="cf7-summary-msg">Print Button Text</label></th>
			<td><input name="cf7-summary-btn" type="text" id="cf7-summary-btn" value="'. ( isset( $cf7_form_data['cf7-summary-btn'] ) ? $cf7_form_data['cf7-summary-btn'] : 'Print this form' ) .'" class="regular-text">
			<p class="description" id="cf7-summary-msg-description">Print Button Text( Leave empty if you do not want to show print button )</p></td>
			</tr>
			
			<tr>
			<td colspan="2">
			<p style="color: gray;font-style: italic;">Note: If you\'r using any cache plugin, please purge all the cache after saving the settings.</p>
			</td>
			</tr>
			<tr>
			<td><input type="submit" name="cf7-summary-submit" id="submit" class="button button-primary" value="Save Changes"></td>
			</tr>
		</tbody></table>
			</form>';
		echo $html;
	}
	
	public function cf7_save_settings() {
		
		if( isset( $_POST['cf7-summary-submit'] ) ) {
			$form_data = array();
			foreach( $_POST as $index => $form_values ) {
				if( $index != 'cf7-summary-submit' ) {
					$form_data[ $index ] = $form_values;
				}
			}
			update_option( 'cf7_summary_print', $form_data );
			
			$html = '<div class="updated success fs-notice fs-sticky fs-has-title cf7_summary_saved">
					<div class="fs-notice-body">
						Settings Saved!
					</div>
				</div>';

		return $html;
		} else {
			return false;
		}
	}
    
	/**
	 * Handle CSS & JS for backend settings
	 *
	 * @since 1.2
	 * @version 1.0
	 */
	public function admin_script_style() {

		wp_enqueue_style( 'cf7-admin-css', plugins_url( 'assets/css/cf7-admin-sp-style.css', CF7_SP_THIS ), array(), CF7_SP_VERSION );
		wp_enqueue_script( 'cf7-admin-js', plugins_url( 'assets/js/cf7-admin-sp-script.js', CF7_SP_THIS ), array( 'jquery' ), CF7_SP_VERSION, true );
		wp_enqueue_style( 'cf7-sp-select2css', plugins_url( 'assets/css/select2-css.css', CF7_SP_THIS ), false, CF7_SP_VERSION, 'all' );
		wp_enqueue_script( 'cf7-sp-select2js', plugins_url( 'assets/js/select2-js.js', CF7_SP_THIS ), array( 'jquery' ), CF7_SP_VERSION, true );
	}

	/**
	 * Register Summary tab in Contact Form 7
	 *
	 * @param array $pannels cf7 tabs.
	 *
	 * @since 1.0
	 * @version 1.1
	 */
	public function add_summary_tab( $pannels ) {
		$pannels['cf7_sp_summary'] = array(
			'title'    => __( 'Summary & Print', 'CF7_SP' ),
			'callback' => array( $this, 'wpcf7_summary_form' ),
		);

		return $pannels;
	}

	/**
	 * HTML Fields to popuplate in Summary Tab
	 *
	 * @param array $post wp post.
	 *
	 * @since 1.0
	 * @version 1.1
	 */
	public function wpcf7_summary_form( $post ) {
		$allowed_html = array(
			'a'      => array(
				'href'  => array(),
				'title' => array(),
			),
			'br'     => array(),
			'em'     => array(),
			'strong' => array(),
			'div'    => array(
				'class' => array(),
				'id'    => array(),
			),
			'p'      => array(
				'class' => array(),
				'id'    => array(),
			),
		);

		wp_nonce_field( 'cf7_summary_print', 'cf7_summary_print_nonce' );
		?>
		<h3><?php echo esc_html( __( 'Form Summary with Print Button', 'CF7_SP' ) ); ?></h3>
		<fieldset>
		<legend><?php echo __( 'The settings will moved on separate page, go to <a href="'.admin_url().'admin.php?page=cf7-summary-Print">settings</a>.', 'CF7_SP' ); ?></legend>
		<?php

		do_action( 'cf7_sp_summary_setting', $post );
	}

	/**
	 * Set up Default values of setting fields
	 *
	 * @param string $template cf7 template.
	 * @param string $prop cf7 prop.
	 *
	 * @since 1.0
	 * @version 1.1
	 */
	public function set_default_template( $template, $prop ) {

		if ( 'cf7_sp_summary' === $prop ) {
			$template = $this->default_template();
		}

		return $template;
	}

	/**
	 * Set Default Values of the fields
	 *
	 * @since 1.0
	 * @version 1.1
	 */
	public function default_template() {

		$template = array(
			'cf7_sp_enable'         => __( '1', 'CF7_SP' ),
			'cf7_sp_title'          => __( 'Form Summary', 'CF7_SP' ),
			'cf7_sp_message_check'  => __( '0', 'CF7_SP' ),
			'cf7_sp_message'        => __( 'Enter Your Message', 'CF7_SP' ),
			'cf7_sp_print_btn_text' => __( 'Print This Form', 'CF7_SP' ),
		);

		return $template;
	}

	/**
	 * Add new property to CF7 to save summary tab fields
	 *
	 * @param parse_arg $properties array.
	 * @param object    $object cf7 object.
	 *
	 * @since 1.0
	 * @version 1.0
	 */
	public function add_new_property( $properties, $object ) {

		$properties = wp_parse_args(
			$properties,
			array(
				'cf7_sp_summary' => array(),
			)
		);

		return $properties;
	}

	/**
	 * Save Summary Tab Fields
	 *
	 * @param array $contact_form post values.
	 *
	 * @since 1.0
	 * @version 1.1
	 */
	public function save_summary_tab( $contact_form ) {

		if ( isset( $_POST['cf7_summary_print_nonce'] ) || wp_verify_nonce( sanitize_key( $_POST['cf7_summary_print_nonce'] ), 'cf7_summary_print' ) ) {
				$properties['cf7_sp_summary']['enable']          = ( isset( $_POST['cf7_sp_enable'] ) ? sanitize_text_field( wp_unslash( $_POST['cf7_sp_enable'] ) ) : '' );
				$properties['cf7_sp_summary']['title']           = ( isset( $_POST['cf7_sp_title'] ) ? sanitize_text_field( wp_unslash( $_POST['cf7_sp_title'] ) ) : '' );
				$properties['cf7_sp_summary']['message_check']   = ( isset( $_POST['cf7_sp_message_check'] ) ? sanitize_text_field( wp_unslash( $_POST['cf7_sp_message_check'] ) ) : '' );
				$properties['cf7_sp_summary']['message']         = ( isset( $_POST['cf7_sp_message'] ) ? wp_kses_post( wp_unslash( $_POST['cf7_sp_message'] ) ) : '' );
				$properties['cf7_sp_summary']['print_btn_txt']   = ( isset( $_POST['cf7_sp_print_btn_text'] ) ? sanitize_text_field( wp_unslash( $_POST['cf7_sp_print_btn_text'] ) ) : '' );
				$properties['cf7_sp_summary']['additional_text'] = '';
				$contact_form->set_properties( $properties );
		}
	}
}

$cf7_sp_settings = new CF7_SP_Settings();
