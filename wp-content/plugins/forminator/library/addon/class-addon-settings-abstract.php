<?php
/**
 * The Forminator_Addon_Settings_Abstract class.
 *
 * @package Forminator
 */

/**
 * Class Forminator_Addon_Settings_Abstract
 * Any change(s) to this file is subject to:
 * - Properly Written DocBlock! (what is this, why is that, how to be like those, etc, as long as you want!)
 * - Properly Written Changelog!
 *
 * @since 1.1
 */
abstract class Forminator_Addon_Settings_Abstract {

	/**
	 * Get HTML select-options
	 *
	 * @param object $options Options.
	 * @param string $selected_value Saved value.
	 * @return string
	 */
	public static function get_select_html( $options, $selected_value = '' ) {
		$html = '<option value="">' . esc_html__( 'None', 'forminator' ) . '</option>';

		foreach ( $options as $id => $title ) {
			$html .= '<option value="' . esc_attr( $id ) . '" ' . selected(
				$selected_value,
				$id,
				false
			) . '>' . esc_html( $title ) . '</option>';
		}

		return $html;
	}

	/**
	 * Get HTML checkbox-options
	 *
	 * @param object $options Options.
	 * @param string $name Name attribute.
	 * @param array  $selected_values Saved values.
	 * @return string
	 */
	public static function get_checkboxes_html( $options, $name, $selected_values = array() ) {
		$html = '';

		foreach ( $options as $id => $title ) {
			$html .= '<label for="' . esc_attr( $id ) . '" class="sui-checkbox sui-checkbox-sm sui-checkbox-stacked">' .
				'<input id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" type="checkbox" value="' . esc_attr( $id ) . '"' .
				checked( is_array( $selected_values ) && in_array( $id, $selected_values, true ), true, false ) .
				'><span aria-hidden="true"></span><span>' . esc_html( $title ) . '</span></label>';
		}

		return $html;
	}

	/**
	 * Get HTML radio-options
	 *
	 * @param object $options Options.
	 * @param string $name Name attribute.
	 * @param array  $selected_value Saved values.
	 * @return string
	 */
	public static function get_radios_html( $options, $name, $selected_value = '' ) {
		$html = '';

		foreach ( $options as $id => $title ) {
			$html .= '<label for="' . esc_attr( $id ) . '" class="sui-radio sui-radio-sm sui-radio-stacked">' .
				'<input id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" type="radio" value="' . esc_attr( $id ) . '"' .
				checked( $id === $selected_value, true, false ) .
				'><span aria-hidden="true"></span><span>' . esc_html( $title ) . '</span></label>';
		}

		return $html;
	}

	/**
	 * Get HTML for refresh button
	 *
	 * @return string
	 */
	public static function refresh_button() {
		$html = '<button class="sui-button-icon sui-tooltip forminator-refresh-email-lists" data-tooltip="'
				. esc_html__( 'Refresh list', 'forminator' ) . '" type="button">'
				. '<span class="sui-loading-text" aria-hidden="true">'
				. '<i class="sui-icon-refresh"></i>'
				. '</span>'
				. '<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>'
				. '<span class="sui-screen-reader-text">' . esc_html__( 'Refresh', 'forminator' ) . '</span>'
				. '</button>';

		return $html;
	}

	/**
	 * Meta key that will be used to save addon setting on WP post_meta
	 *
	 * @return string
	 */
	final public function get_settings_meta_key() {
		$addon     = $this->addon;
		$global_id = ! empty( $addon->multi_global_id ) ? '_' . $addon->multi_global_id : '';
		return 'forminator_addon_' . $addon->get_slug() . '_' . static::$module_slug . '_settings' . $global_id;
	}

	/**
	 * Replace '-' to '_' in keys because some integrations don't support dashes like tray.io and workato.
	 * don't do it for zapier for backward compatibility
	 *
	 * @param array  $array_values Original array.
	 * @param string $endpoint Endpoint URL.
	 */
	public static function replace_dashes_in_keys( $array_values, $endpoint ) {
		if ( strpos( $endpoint, 'zapier' ) ) {
			return $array_values;
		}

		foreach ( $array_values as $key => $value ) {
			if ( is_array( $value ) ) {
				// Replace it recursively.
				$value = self::replace_dashes_in_keys( $value, $endpoint );
			}
			unset( $array_values[ $key ] );
			$new_key                  = str_replace( '-', '_', $key );
			$array_values[ $new_key ] = $value;
		}

		return $array_values;
	}

	/**
	 * Get HTML for GDPR fields
	 *
	 * @param array $current_data Saved data.
	 * @return string
	 */
	protected static function gdpr_fields_html( $current_data ) {
		return '<div class="sui-form-field">' .
			'<label class="sui-label">' . esc_html__( 'Enable GDPR', 'forminator' ) . '</label>
			<input type="checkbox" name="enable_gdpr" value="1" ' . checked( 1, $current_data['enable_gdpr'], false ) . '>
		</div>

		<div class="sui-form-field">
			<label class="sui-label">' . esc_html__( 'GDPR Text', 'forminator' ) . '</label>
			<textarea name="gdpr_text">' . wp_kses_post( $current_data['gdpr_text'] ) . '</textarea>
		</div>';
	}

	/**
	 * Step mapping fields on wizard
	 *
	 * @param array $submitted_data Submitted data.
	 * @param array $addon_settings Addon settingd.
	 *
	 * @return array
	 */
	protected function get_map_fields( $submitted_data, $addon_settings ) {
		$is_close              = false;
		$is_submit             = ! empty( $submitted_data );
		$error_message         = '';
		$html_input_map_fields = '';
		$input_error_messages  = array();

		try {
			$fields_map  = array();
			$fields_list = $this->get_addon_custom_fields();

			$fields_list_ids = wp_list_pluck( $fields_list, 'id' );

			foreach ( $fields_list_ids as $key ) {
				$fields_map[ $key ] = $submitted_data['fields_map'][ $key ] ?? $addon_settings['fields_map'][ $key ] ?? '';
			}

			/** Build table map fields input */
			$html_input_map_fields = $this->get_input_map_fields( $fields_list, $fields_map );

			if ( $is_submit ) {
				$this->step_map_fields_validate( $fields_list, $submitted_data );
				$this->save_module_settings_values( $addon_settings );
				$is_close = true;
			}
		} catch ( Forminator_Addon_Mailjet_Form_Settings_Exception $e ) {
			$input_error_messages = $e->get_input_exceptions();
			if ( ! empty( $html_input_map_fields ) ) {
				foreach ( $input_error_messages as $input_id => $message ) {
					if ( is_array( $message ) ) {
						foreach ( $message as $addr => $m ) {
							$html_input_map_fields = str_replace( '{{$error_css_class_' . $input_id . '_' . $addr . '}}', 'sui-form-field-error', $html_input_map_fields );
							$html_input_map_fields = str_replace( '{{$error_message_' . $input_id . '_' . $addr . '}}', '<span class="sui-error-message">' . esc_html( $m ) . '</span>', $html_input_map_fields );
						}
					} else {
						$html_input_map_fields = str_replace( '{{$error_css_class_' . $input_id . '}}', 'sui-form-field-error', $html_input_map_fields );
						$html_input_map_fields = str_replace( '{{$error_message_' . $input_id . '}}', '<span class="sui-error-message">' . esc_html( $message ) . '</span>', $html_input_map_fields );
					}
				}
			}
		} catch ( Forminator_Addon_Mailjet_Exception $e ) {
			$error_message = '<div role="alert" class="sui-notice sui-notice-red sui-active" style="display: block; text-align: left;" aria-live="assertive">';

				$error_message .= '<div class="sui-notice-content">';

					$error_message .= '<div class="sui-notice-message">';

						$error_message .= '<span class="sui-notice-icon sui-icon-info" aria-hidden="true"></span>';

						$error_message .= '<p>' . $e->getMessage() . '</p>';

					$error_message .= '</div>';

				$error_message .= '</div>';

			$error_message .= '</div>';
		}

		// cleanup map fields input markup placeholder.
		if ( ! empty( $html_input_map_fields ) ) {
			$replaced_html_input_map_fields = $html_input_map_fields;
			$replaced_html_input_map_fields = preg_replace( '/\{\{\$error_css_class_(.+)\}\}/', '', $replaced_html_input_map_fields );
			$replaced_html_input_map_fields = preg_replace( '/\{\{\$error_message_(.+)\}\}/', '', $replaced_html_input_map_fields );
			if ( ! is_null( $replaced_html_input_map_fields ) ) {
				$html_input_map_fields = $replaced_html_input_map_fields;
			}
		}

		$buttons = array(
			'cancel' => array(
				'markup' => Forminator_Addon_Abstract::get_button_markup( esc_html__( 'Back', 'forminator' ), 'sui-button-ghost forminator-addon-back' ),
			),
			'next'   => array(
				'markup' => '<div class="sui-actions-right">' .
					Forminator_Addon_Abstract::get_button_markup( esc_html__( 'Save', 'forminator' ), 'sui-button-primary forminator-addon-finish' ) .
				'</div>',
			),
		);

		$notification = array();

		if ( $is_submit && empty( $error_message ) && empty( $input_error_messages ) ) {
			$notification = array(
				'type' => 'success',
				'text' => '<strong>' . $this->addon->get_title() . '</strong> ' . esc_html__( 'is activated successfully.', 'forminator' ),
			);
		}

		$html  = '<div class="forminator-integration-popup__header">';
		$html .= '<h3 id="dialogTitle2" class="sui-box-title sui-lg" style="overflow: initial; text-overflow: none; white-space: normal;">' . esc_html__( 'Assign Fields', 'forminator' ) . '</h3>';
		$html .= '<p class="sui-description">' . esc_html__( 'Lastly, match up your form fields with your campaign fields to make sure we\'re sending data to the right place.', 'forminator' ) . '</p>';
		$html .= $error_message;
		$html .= '</div>';
		$html .= '<form enctype="multipart/form-data">';
		$html .= $html_input_map_fields;
		$html .= '</form>';

		return array(
			'html'         => $html,
			'redirect'     => false,
			'is_close'     => $is_close,
			'buttons'      => $buttons,
			'has_errors'   => ! empty( $error_message ) || ! empty( $input_error_messages ),
			'notification' => $notification,
			'size'         => 'normal',
			'has_back'     => true,
		);
	}

	/**
	 * Save module settings
	 *
	 * @param type $addon_settings Addon settings.
	 */
	protected function save_module_settings_values( $addon_settings = null ) {
		$is_null = is_null( $addon_settings );
		if ( 'quiz' === static::$module_slug ) {
			if ( $is_null ) {
				$addon_settings = $this->addon_quiz_settings;
			}
			$this->save_quiz_settings_values( $addon_settings );
		} else {
			if ( $is_null ) {
				$addon_settings = $this->addon_form_settings;
			}
			$this->save_form_settings_values( $addon_settings );
		}
	}

	/**
	 * Get fields for specific field type.
	 *
	 * @param string $type Field type.
	 * @return array
	 */
	protected function get_fields_for_type( $type ) {
		if ( 'email' === $type ) {
			// find email type fields.
			$email_fields = array();
			foreach ( $this->form_fields as $form_field ) {
				if ( $type === $form_field['type'] ) {
					$email_fields[] = $form_field;
				}
			}

			return $email_fields;
		} else {
			return $this->form_fields;
		}
	}

	/**
	 * Get error notice HTML
	 *
	 * @param object $e Excsption.
	 * @return string
	 */
	protected static function get_error_notice( $e ) {
		$error_html = '<div role="alert" class="sui-notice sui-notice-red sui-active" style="display: block; text-align: left;" aria-live="assertive">
			<div class="sui-notice-content">
				<div class="sui-notice-message">
					<span class="sui-notice-icon sui-icon-info" aria-hidden="true"></span>
					<p>' . $e->getMessage() . '</p>
				</div>
			</div>
		</div>';

		return $error_html;
	}

	/**
	 * Get HTML for header on Choose List step.
	 *
	 * @param string $error_message Error message.
	 * @return array
	 */
	protected static function get_choose_list_header( $error_message ) {
		$html  = '<div class="forminator-integration-popup__header">';
		$html .= '<h3 id="dialogTitle2" class="sui-box-title sui-lg" style="overflow: initial; text-overflow: none; white-space: normal;">' . esc_html__( 'Choose contact list', 'forminator' ) . '</h3>';
		$html .= '<p class="sui-description">' . esc_html__( 'Choose the contact list you want to send form data to', 'forminator' ) . '</p>';
		$html .= wp_kses_post( $error_message );
		$html .= '</div>';

		return $html;
	}

	/**
	 * Get HTML for Choose list field
	 *
	 * @param array  $current_data Saved data.
	 * @param array  $lists Lists.
	 * @param string $list_error Error messages.
	 * @return string
	 */
	protected static function get_choose_list_field( $current_data, $lists, $list_error ) {
		$html = '<div class="sui-form-field' . ( $list_error ? ' sui-form-field-error' : '' ) . '" style="margin-bottom: 10px;">
			<label class="sui-label">' . esc_html__( 'Contact list', 'forminator' ) . '</label>
			<div class="forminator-select-refresh">
				<select name="mail_list_id" class="sui-select">' .
					self::get_select_html( $lists, $current_data['mail_list_id'] )
				. '</select>' .
				self::refresh_button()
			. '</div>';
		if ( $list_error ) {
			$html .= '<span class="sui-error-message">' . esc_html( $list_error ) . '</span>';
		}
		$html .= '</div>';

		return $html;
	}

	/**
	 * Get HTML for Double Opt-in field
	 *
	 * @param array $current_data Saved data.
	 * @return string
	 */
	protected static function get_double_optin_field( $current_data ) {
		$html  = '<div class="sui-form-field">';
		$html .= '<label class="sui-toggle">';
		$html .= '<input type="checkbox" name="enable_double_opt_in" value="1" id="forminator_addon_enable_double_opt_in" ' . checked( 1, $current_data['enable_double_opt_in'], false ) . ' />';
		$html .= '<span class="sui-toggle-slider"></span>';
		$html .= '<span class="sui-toggle-label">' . esc_html__( 'Use Double Opt in', 'forminator' ) . '</span>';
		$html .= '</label>';
		$html .= '</div>';

		return $html;
	}
}
