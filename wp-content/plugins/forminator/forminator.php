<?php
/**
 * Plugin Name: Forminator
 * Version: 1.35.1
 * Plugin URI:  https://wpmudev.com/project/forminator/
 * Description: Capture user information (as detailed as you like), engage users with interactive polls that show real-time results and graphs, “no wrong answer” Facebook-style quizzes and knowledge tests.
 * Author: WPMU DEV
 * Author URI: https://wpmudev.com
 * Requires at least: 6.4
 * Tested up to: 6.6
 * Requires PHP: 7.4
 * Text Domain: forminator
 * Domain Path: /languages/
 *
 * @package    Forminator
 */

/*
Copyright 2009-2024 Incsub (http://incsub.com)
Author – WPMU DEV

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License (Version 2 – GPLv2) as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/

if ( ! defined( 'ABSPATH' ) ) {
	die();
}
// Constants.
require_once plugin_dir_path( __FILE__ ) . 'constants.php';

// Include API.
require_once plugin_dir_path( __FILE__ ) . 'library/class-api.php';

// Register activation hook.
register_activation_hook( __FILE__, array( 'Forminator', 'activation_hook' ) );
// Register deactivation hook.
register_deactivation_hook( __FILE__, array( 'Forminator', 'deactivation_hook' ) );

if ( ! defined( 'FORMINATOR_PLUGIN_BASENAME' ) ) {
	define( 'FORMINATOR_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
}

if ( ! class_exists( 'Forminator' ) ) {
	/**
	 * Class Forminator
	 *
	 * Main class. Initialize plugin
	 *
	 * @since 1.0
	 */
	class Forminator {

		/**
		 * Plugin instance
		 *
		 * @var null
		 */
		private static $instance = null;

		/**
		 * Forminator_Core instance
		 *
		 * @var Forminator_Core
		 */
		public $forminator;

		/**
		 * Forminator_Integration_Loader instance
		 *
		 * @var Forminator_Integration_Loader
		 */
		private $forminator_addon_loader;

		/**
		 * Return the plugin instance
		 *
		 * @since 1.0
		 * @return Forminator
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Forminator constructor.
		 *
		 * @since 1.0
		 */
		public function __construct() {
			add_action( 'admin_init', array( $this, 'initialize_admin' ) );
			add_action( 'admin_init', array( $this, 'add_custom_cap' ) );

			$this->includes();
			$this->include_vendors();

			if ( self::is_addons_feature_enabled() ) {
				$this->init_addons();
			}

			$this->init();
			$this->load_textdomain();
		}

		/**
		 * Called on plugin activation
		 *
		 * @since 1.3
		 */
		public static function activation_hook() {
			add_option( 'forminator_activation_hook', 'activated' );

			self::set_free_installation_timestamp();
		}

		/**
		 * Called on plugin deactivation
		 *
		 * @since 1.11
		 */
		public static function deactivation_hook() {
			as_unschedule_action( 'forminator_action_scheduler_cleanup', array(), 'forminator' );
			as_unschedule_action( 'forminator_send_export', array(), 'forminator' );
			as_unschedule_action( 'forminator_daily_cron', array(), 'forminator' );
			as_unschedule_action( 'forminator_process_report', array(), 'forminator' );
			as_unschedule_action( 'forminator_general_data_protection_cleanup', array(), 'forminator' );
		}

		/**
		 * Called on admin_init
		 *
		 * Flush rewrite rules are not called directly on activation hook, because CPT are not initialized yet
		 *
		 * @since 1.3
		 */
		public function initialize_admin() {
			if ( is_admin() && 'activated' === get_option( 'forminator_activation_hook' ) ) {
				delete_option( 'forminator_activation_hook' );
				flush_rewrite_rules();
			}
		}

		/**
		 * Add manage_forminator custom capability
		 *
		 * @since 1.15
		 */
		public function add_custom_cap() {
			$admin = get_role( 'administrator' );
			if ( $admin ) {
				$admin->add_cap( 'manage_forminator', true );
			}
		}

		/**
		 * Return status of Addon feature
		 *
		 * If this function return false, then addon functionality will be disabled
		 *
		 * @since 1.1
		 *
		 * @return bool
		 */
		public static function is_addons_feature_enabled() {
			// force enable addon on entire planet.
			$enabled = true;

			/**
			 * Filter the status of addons feature.
			 *
			 * @since 1.1
			 *
			 * @param bool $enabled current status of addons feature.
			 */
			$enabled = apply_filters( 'forminator_is_addons_feature_enabled', $enabled );

			return $enabled;
		}

		/**
		 * Return status of Import/export feature
		 *
		 * If this function return false, then Import/export functionality will be disabled
		 *
		 * @since 1.4
		 * @since 1.5 enabled by default
		 *
		 * @return bool
		 */
		public static function is_import_export_feature_enabled() {
			// enable import export feature for entire planet by default.
			$enabled = true;

			/**
			 * Filter the status of Import/export feature
			 *
			 * @since 1.4
			 *
			 * @param bool $enabled current status of Import/export feature
			 */
			$enabled = apply_filters( 'forminator_is_import_export_feature_enabled', $enabled );

			return $enabled;
		}

		/**
		 * Return status of Import integrations feature
		 *
		 * If this function return false, then Import integrations functionality will be disabled
		 *
		 * @since 1.4
		 *
		 * @return bool
		 */
		public static function is_import_integrations_feature_enabled() {
			// default is disabled unless `FORMINATOR_ENABLE_IMPORT_INTEGRATIONS` = true,
			// integrations data probably contains sensitive content
			// not 100% will worked if current addon not enabled / not setup properly.
			$enabled = ( defined( 'FORMINATOR_ENABLE_IMPORT_INTEGRATIONS' ) && FORMINATOR_ENABLE_IMPORT_INTEGRATIONS );

			/**
			 * Filter the status of Import integrations feature
			 *
			 * @since 1.4
			 *
			 * @param bool $enabled current status of Import integrations feature
			 */
			$enabled = apply_filters( 'forminator_is_import_integrations_feature_enabled', $enabled );

			return $enabled;
		}

		/**
		 * Return status of Export integrations feature
		 *
		 * If this function return false, then Import integrations functionality will be disabled
		 *
		 * @since 1.4
		 *
		 * @return bool
		 */
		public static function is_export_integrations_feature_enabled() {
			// default is disabled unless `FORMINATOR_ENABLE_EXPORT_INTEGRATIONS` = true,
			// integrations data probably contains sensitive content
			// not 100% will worked if current addon not enabled / not setup properly.
			$enabled = ( defined( 'FORMINATOR_ENABLE_EXPORT_INTEGRATIONS' ) && FORMINATOR_ENABLE_EXPORT_INTEGRATIONS );

			/**
			 * Filter the status of Export integrations feature
			 *
			 * @since 1.4
			 *
			 * @param bool $enabled current status of export integrations feature
			 */
			$enabled = apply_filters( 'forminator_is_export_integrations_feature_enabled', $enabled );

			return $enabled;
		}

		/**
		 * Return status of Internal Page Cache support
		 *
		 * @since 1.6.1
		 * @return bool
		 */
		public static function is_internal_page_cache_support_enabled() {
			// default is enabled unless `FORMINATOR_ENABLE_INTERNAL_PAGE_CACHE_SUPPORT` = false.
			$enabled = true;
			if ( defined( 'FORMINATOR_ENABLE_INTERNAL_PAGE_CACHE_SUPPORT' ) && ! FORMINATOR_ENABLE_INTERNAL_PAGE_CACHE_SUPPORT ) {
				$enabled = false;
			}
			/**
			 * Filter the status of Internal Page Cache support
			 *
			 * @since 1.6.1
			 *
			 * @param bool $enabled current status of internal page cache support
			 */
			$enabled = apply_filters( 'forminator_is_internal_page_cache_support_enabled', $enabled );

			return $enabled;
		}

		/**
		 * Initiate Addons Helper and Register internal Addons
		 *
		 * This function will also trigger action `forminator_addons_loaded`
		 *
		 * @since 1.1
		 */
		public function init_addons() {

			/**
			 * Triggered before load and registering internal addons
			 *
			 * Only triggered when addons feature is enabled @see Forminator::is_addons_feature_enabled()
			 * Keep in mind that @see Forminator_Integration_Loader not yet instantiated
			 *
			 * @since 1.1
			 */
			do_action( 'forminator_before_load_addons' );

			include_once forminator_plugin_dir() . 'library/helpers/helper-addon.php';
			$this->forminator_addon_loader = Forminator_Integration_Loader::get_instance();
			$this->load_forminator_addons();

			/**
			 * Triggered after internal addons of forminator loaded
			 *
			 * This action will be used by external addon to register
			 * Registering addon will use @see Forminator_Integration_Loader::register()
			 *
			 * @since 1.1
			 */
			do_action( 'forminator_addons_loaded' );
		}

		/**
		 * Load internal addons
		 *
		 * Load pre-packaged addons
		 *
		 * @since 1.1
		 */
		public function load_forminator_addons() {
			$addons_directory = forminator_addons_dir();
			if ( file_exists( $addons_directory . '/class-addon-autoload.php' ) ) {
				include_once $addons_directory . '/class-addon-autoload.php';
				$autoloader = new Forminator_Addon_Autoload();
				$autoloader->load();
			}
		}

		/**
		 * Load plugin files
		 *
		 * @since 1.0
		 */
		private function includes() {
			// Core files.
			/* @noinspection PhpIncludeInspection */
			include_once forminator_plugin_dir() . 'library/class-core.php';
			include_once forminator_plugin_dir() . 'library/class-addon-loader.php';
			include_once forminator_plugin_dir() . 'library/class-integration-loader.php';
			include_once forminator_plugin_dir() . 'library/calculator/class-calculator.php';
		}

		/**
		 * Add option with plugin install date
		 *
		 * @since 1.10
		 */
		public static function set_free_installation_timestamp() {
			// We need the install date only on free version.
			if ( FORMINATOR_PRO ) {
				return;
			}

			$install_date = get_site_option( 'forminator_free_install_date' );

			if ( empty( $install_date ) ) {
				update_site_option( 'forminator_free_install_date', current_time( 'timestamp' ) ); // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested -- We are using the current timestamp based on the site's timezone.
			}
		}

		/**
		 * Init the plugin
		 *
		 * @since 1.0
		 */
		private function init() {
			// Initialize plugin core.
			$this->forminator = Forminator_Core::get_instance();
			/**
			 * Triggered when plugin is loaded
			 */
			do_action( 'forminator_loaded' );
		}

		/**
		 * Include Vendors
		 *
		 * @since 1.0
		 */
		private function include_vendors() {
			if ( file_exists( forminator_plugin_dir() . 'library/lib/dash-notice/wpmudev-dash-notification.php' ) ) {
				// load dashboard notice.
				global $wpmudev_notices;
				$wpmudev_notices[] = array(
					'id'      => 2097296,
					'name'    => FORMINATOR_PRO ? 'Forminator Pro' : 'Forminator',
					'screens' => array(
						'toplevel_page_forminator',
						'toplevel_page_forminator-network',
						'forminator_page_forminator-cform',
						'forminator_page_forminator-cform-network',
						'forminator_page_forminator-poll',
						'forminator_page_forminator-poll-network',
						'forminator_page_forminator-quiz',
						'forminator_page_forminator-quiz-network',
						'forminator_page_forminator-settings',
						'forminator_page_forminator-settings-network',
						'forminator_page_forminator-cform-wizard',
						'forminator_page_forminator-cform-wizard-network',
						'forminator_page_forminator-cform-view',
						'forminator_page_forminator-cform-view-network',
						'forminator_page_forminator-poll-wizard',
						'forminator_page_forminator-poll-wizard-network',
						'forminator_page_forminator-poll-view',
						'forminator_page_forminator-poll-view-network',
						'forminator_page_forminator-nowrong-wizard',
						'forminator_page_forminator-nowrong-wizard-network',
						'forminator_page_forminator-knowledge-wizard',
						'forminator_page_forminator-knowledge-wizard-network',
						'forminator_page_forminator-quiz-view',
						'forminator_page_forminator-quiz-view-network',
					),
				);
				// @noinspection PhpIncludeInspection.
				include_once forminator_plugin_dir() . 'library/lib/dash-notice/wpmudev-dash-notification.php';
			}

			// Prefixed vendor autoload.
			include_once forminator_plugin_dir() . 'library/external/vendor/autoload.php';

			include_once forminator_plugin_dir() . 'library/lib/analytics/autoload.php';

			if ( ! FORMINATOR_PRO ) {

				if ( file_exists( forminator_plugin_dir() . 'library/lib/recommended-plugins/notice.php' ) ) {

					require_once forminator_plugin_dir() . 'library/lib/recommended-plugins/notice.php';

					do_action(
						'wpmudev-recommended-plugins-register-notice',
						plugin_basename( __FILE__ ), // Plugin basename.
						'Forminator', // Plugin Name.
						array(
							'toplevel_page_forminator',
							'toplevel_page_forminator-network',
							'forminator_page_forminator-cform',
							'forminator_page_forminator-cform-network',
							'forminator_page_forminator-poll',
							'forminator_page_forminator-poll-network',
							'forminator_page_forminator-quiz',
							'forminator_page_forminator-quiz-network',
							'forminator_page_forminator-settings',
							'forminator_page_forminator-settings-network',
							'forminator_page_forminator-cform-wizard',
							'forminator_page_forminator-cform-wizard-network',
							'forminator_page_forminator-cform-view',
							'forminator_page_forminator-cform-view-network',
							'forminator_page_forminator-poll-wizard',
							'forminator_page_forminator-poll-wizard-network',
							'forminator_page_forminator-poll-view',
							'forminator_page_forminator-poll-view-network',
							'forminator_page_forminator-nowrong-wizard',
							'forminator_page_forminator-nowrong-wizard-network',
							'forminator_page_forminator-knowledge-wizard',
							'forminator_page_forminator-knowledge-wizard-network',
							'forminator_page_forminator-quiz-view',
							'forminator_page_forminator-quiz-view-network',
							'forminator_page_forminator-entries',
							'forminator_page_forminator-entries-network',
							'forminator_page_forminator-integrations',
							'forminator_page_forminator-integrations-network',
						),
						array( 'after', '.sui-wrap .sui-header' ) // selector.
					);

				}
			}
		}

		/**
		 * Load language files
		 *
		 * @since 1.0
		 */
		private function load_textdomain() {
			load_plugin_textdomain( 'forminator', false, 'forminator/languages' );
		}

		/**
		 * Check if Dash plugin installed and full membership
		 *
		 * @since 1.6
		 * @return bool
		 */
		public static function is_wpmudev_member() {
			if ( function_exists( 'is_wpmudev_member' ) ) {
				return is_wpmudev_member();
			}

			return false;
		}
	}
}

// Functions.
require_once plugin_dir_path( __FILE__ ) . 'functions.php';

if ( file_exists( forminator_plugin_dir() . 'library/external/src/Forminator/woocommerce/action-scheduler/action-scheduler.php' ) ) {
	add_action(
		'plugins_loaded',
		function () {
			require_once forminator_plugin_dir() . 'library/external/src/Forminator/woocommerce/action-scheduler/action-scheduler.php';
		},
		-10 // Don't change.
	);

	// Re-register Action Scheduler tables if `priority` column is missing in actionscheduler_actions table.
	add_action(
		'action_scheduler_pre_init',
		function () {
			$key = 'forminator_action_scheduler_db_updated';
			if ( ! get_option( $key ) && class_exists( 'ActionScheduler_StoreSchema' ) ) {
				global $wpdb;
				$table = $wpdb->prefix . ActionScheduler_StoreSchema::ACTIONS_TABLE;
				// It doesn't required cache as it run only once.
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$res = $wpdb->get_var( "SHOW COLUMNS FROM {$table} LIKE 'priority'" );
				if ( ! $res ) {
					$store_schema = new ActionScheduler_StoreSchema();
					$store_schema->register_tables( true );
				}
				update_option( $key, '1' );
			}
		}
	);
}
