<?php
/**
 * The Forminator_Mixpanel_Settings class.
 *
 * @package Forminator
 */

/**
 * Mixpanel Settings Events class
 */
class Forminator_Mixpanel_Settings extends Events {

	/**
	 * Initialize class.
	 *
	 * @since 1.27.0
	 */
	public static function init() {
		add_action( 'forminator_after_dashboard_settings', array( __CLASS__, 'tracking_settings_update' ) );
		add_action( 'forminator_feature_usage_settings', array( __CLASS__, 'tracking_settings_update' ) );
		add_action( 'forminator_after_reset_settings', array( __CLASS__, 'tracking_settings_reset' ) );
		add_action( 'forminator_after_uninstall', array( __CLASS__, 'tracking_plugin_uninstall' ), 10, 2 );
		add_action( 'deactivated_plugin', array( __CLASS__, 'tracking_deactivate' ) );
		add_action( 'forminator_before_stripe_connected', array( __CLASS__, 'tracking_stripe_rak_use' ), 10, 4 );
	}

	/**
	 * Handle settings reset.
	 *
	 * We need to opt out after settings reset.
	 *
	 * @param bool $active usage tracking active or not.
	 *
	 * @return void
	 * @since 1.27.0
	 */
	public static function tracking_settings_reset( $active ) {
		if ( $active ) {
			self::track_opt_toggle( false, 'Data Reset' );
		}
	}

	/**
	 * Handle Plugin Deactivate.
	 *
	 * We need to opt out after plugin deactivate.
	 *
	 * @param mixed $plugin Plugin name.
	 *
	 * @return void
	 * @since 1.27.0
	 */
	public static function tracking_deactivate( $plugin ) {
		if ( ! self::is_tracking_active() ) {
			return;
		}
		// Only if Forminator plugin.
		if ( FORMINATOR_PLUGIN_BASENAME !== $plugin ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : '';

		$triggered_from = 'Unknown';

		// Deactivated from WPMUDEV Dashboard.
		if ( 'wdp-project-deactivate' === $action ) {
			$triggered_from = 'Plugin deactivation - dashboard';
		} elseif ( 'deactivate' === $action ) {
			// Deactivated from WP plugins page.
			$triggered_from = 'Plugin deactivation - wpadmin';
		}

		self::track_opt_toggle( false, $triggered_from );
	}

	/**
	 * Handle settings uninstall.
	 *
	 * We need to opt out after plugin uninstall if not keep settings.
	 *
	 * @param bool $active usage tracking active or not.
	 * @param bool $keep_settings Determine whether to save current settings for next time, or reset them.
	 *
	 * @return void
	 *
	 * @since 1.27.0
	 */
	public static function tracking_plugin_uninstall( $active, $keep_settings ) {
		// Opt out only if it was opted in before and doesn't require to keep settings.
		if ( $active && $keep_settings ) {
			self::track_opt_toggle( false, 'Data Reset' );
		}
	}

	/**
	 * Handle settings update.
	 *
	 * @param bool $old_value usage tracking active or not.
	 *
	 * @return void
	 *
	 * @since 1.27.0
	 */
	public static function tracking_settings_update( $old_value ) {
		$active = self::is_tracking_active();
		if ( ( ! $old_value && ! $active ) || $old_value === $active ) {
			return;
		}
		self::track_opt_toggle( $active, 'Disable Tracking' );
		if ( $active ) {
			$properties = Forminator_Mixpanel::module_updates( 'opt-in' );
			self::track_event( 'for_module_updated', $properties );
		}
	}

	/**
	 * Track data tracking opt in and opt out.
	 *
	 * @param bool   $active Toggle value.
	 * @param string $method method.
	 *
	 * @return void
	 * @since 1.27.0
	 */
	private static function track_opt_toggle( $active, $method = '' ) {
		$properties = array();
		if ( ! $active ) {
			$properties = array( 'Method' => $method );
		}
		self::tracker()->track( $active ? 'Opt In' : 'Opt Out', $properties );
	}

	/**
	 * Track stripe RAK use.
	 *
	 * @param string $test_key Test API key.
	 * @param string $test_secret Test Secret/Restricted Key.
	 * @param string $live_key Live API key.
	 * @param string $live_secret Live Secret/Restricted Key.
	 *
	 * @return void
	 */
	public static function tracking_stripe_rak_use( string $test_key, string $test_secret, string $live_key, string $live_secret ) {
		$config = get_option( 'forminator_stripe_configuration', array() );
		if ( empty( $config ) ) {
			return;
		}

		if ( ( ! empty( $test_secret ) && 'sk_' === substr( $test_secret, 0, 3 ) ) && ( ! empty( $live_secret ) && 'sk_' === substr( $live_secret, 0, 3 ) ) ) {
			return;
		}

		$config_test_secret = $config['test_secret'] ?? '';
		$config_live_secret = $config['live_secret'] ?? '';
		if ( ( ! empty( $config_test_secret ) && 'rk_' === substr( $config_test_secret, 0, 3 ) ) && ( ! empty( $config_live_secret ) && 'rk_' === substr( $config_live_secret, 0, 3 ) ) ) {
			return;
		}

		self::track_event( 'for_stripe_rak_use', array() );
	}
}
