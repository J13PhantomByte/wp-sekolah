<?php
/**
 * The Events class.
 *
 * @package Forminator
 */

/**
 * Abstract class for Mixpanel Events.
 */
abstract class Events {

	/**
	 * Initialize class.
	 *
	 * @since 1.27.0
	 */
	public static function init() {
	}

	/**
	 * Get mixpanel instance.
	 *
	 * @return Mixpanel
	 *
	 * @since 1.27.0
	 */
	protected static function tracker() {
		return Forminator_Mixpanel::get_instance()->tracker();
	}

	/**
	 * Check if usage tracking is active.
	 *
	 * @return bool
	 * @since 1.27.0
	 */
	protected static function is_tracking_active() {
		return self::get_value( 'forminator_usage_tracking', false );
	}

	/**
	 * Tracking event
	 *
	 * @param string $event Event.
	 * @param array  $properties Properties.
	 *
	 * @return void
	 *
	 * @since 1.27.0
	 */
	public static function track_event( $event, $properties ) {
		self::tracker()->track(
			$event,
			$properties
		);
	}

	/**
	 * Get a usage tracking value
	 *
	 * @param string $key Key for get value.
	 * @param string $default_value Default value.
	 *
	 * @return false|mixed|null
	 * @since 1.27.0
	 */
	protected static function get_value( $key, $default_value = 'false' ) {
		return get_option( $key, $default_value );
	}

	/**
	 * Fetch Settings value
	 *
	 * @param array  $settings Settings.
	 * @param string $key Key name.
	 * @param string $value Value.
	 *
	 * @return string|void
	 * @since 1.27.0
	 */
	protected static function settings_value( $settings, $key, $value = '' ) {
		if ( empty( $settings ) ) {
			return;
		}

		if ( ! empty( $settings[ $key ] ) ) {
			return esc_html( $settings[ $key ] );
		}

		return $value;
	}
}
