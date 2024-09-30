<?php
/**
 * The Forminator_Form_Views_Model class.
 *
 * @package Forminator
 */

/**
 * Form Views
 * Handles conversions and views of the different forms
 */
class Forminator_Form_Views_Model {

	/**
	 * The table name
	 *
	 * @var string
	 */
	protected $table_name;


	/**
	 * Plugin instance
	 *
	 * @var null
	 */
	private static $instance = null;

	/**
	 * Return the plugin instance
	 *
	 * @since 1.0
	 * @return Forminator_Form_Views_Model
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Forminator_Form_Views_Model constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		$this->table_name = Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_VIEWS );
	}

	/**
	 * Save conversion
	 *
	 * @since 1.0
	 * @param int    $form_id - the form id.
	 * @param int    $page_id - the page id.
	 * @param string $ip - the ip.
	 */
	public function save_view( $form_id, $page_id, $ip ) {
		global $wpdb;
		if ( ! defined( 'FORMINATOR_VIEWS_ENABLE_TRACK_IP' ) || ( defined( 'FORMINATOR_VIEWS_ENABLE_TRACK_IP' ) && ! FORMINATOR_VIEWS_ENABLE_TRACK_IP ) ) {
			$ip = null;
		}

		if ( ! is_null( $ip ) ) {
			$ip_query = ' AND `ip` = %s';
		} else {
			$ip_query = ' AND `ip` IS NULL';
		}

		$sql = "SELECT `view_id` FROM {$this->get_table_name()} WHERE `form_id` = %d AND `page_id` = %d {$ip_query} AND DATE(`date_created`) = CURDATE()";

		if ( ! is_null( $ip ) ) {
			$prepared_sql = $wpdb->prepare( $sql, $form_id, $page_id, $ip ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		} else {
			$prepared_sql = $wpdb->prepare( $sql, $form_id, $page_id ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}

		$view_id = $wpdb->get_var( $prepared_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery
		if ( $view_id ) {
			$this->_update( $view_id, $wpdb );
		} else {
			$this->_save( $form_id, $page_id, $ip, $wpdb );
		}
	}

	/**
	 * Save Data to database
	 *
	 * @param int         $form_id - the form id.
	 * @param int         $page_id - the page id.
	 * @param string      $ip - the user ip.
	 * @param bool|object $db - the wp db object.
	 */
	private function _save( $form_id, $page_id, $ip, $db = false ) { // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
		if ( ! $db ) {
			global $wpdb;
			$db = $wpdb;
		}

		$db->insert(
			$this->table_name,
			array(
				'form_id'      => $form_id,
				'page_id'      => $page_id,
				'ip'           => $ip,
				'date_created' => date_i18n( 'Y-m-d H:i:s' ),
			)
		);
	}

	/**
	 * Update view
	 *
	 * @since 1.0
	 * @param int         $id - entry id.
	 * @param bool|object $db - the wp db object.
	 */
	private function _update( $id, $db = false ) { // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
		if ( ! $db ) {
			global $wpdb;
			$db = $wpdb;
		}
		$db->query( $db->prepare( "UPDATE {$this->get_table_name()} SET `count` = `count`+1, `date_updated` = now() WHERE `view_id` = %d", $id ) );
	}

	/**
	 * Count views
	 *
	 * @since 1.0
	 * @param int    $form_id - the form id.
	 * @param string $starting_date - the start date (dd-mm-yyy).
	 * @param string $ending_date - the end date (dd-mm-yyy).
	 *
	 * @return int - totol views based on parameters
	 */
	public function count_views( $form_id, $starting_date = null, $ending_date = null ) {
		return $this->_count( $form_id, $starting_date, $ending_date );
	}

	/**
	 * Delete views by form
	 *
	 * @since 1.0
	 * @param int $form_id - the form id.
	 */
	public function delete_by_form( $form_id ) {
		global $wpdb;
		$sql = "DELETE FROM {$this->get_table_name()} WHERE `form_id` = %d";
		$wpdb->query( $wpdb->prepare( $sql, $form_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery
	}

	/**
	 * Count data
	 *
	 * @since 1.0
	 * @param int    $form_id - the form id.
	 * @param string $starting_date - the start date (dd-mm-yyy).
	 * @param string $ending_date - the end date (dd-mm-yyy).
	 *
	 * @return int - totol counts based on parameters
	 */
	private function _count( $form_id, $starting_date = null, $ending_date = null ) { // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
		global $wpdb;
		$date_query = $this->generate_date_query( $wpdb, $starting_date, $ending_date );
		$sql        = "SELECT SUM(`count`) FROM {$this->get_table_name()} WHERE `form_id` = %d $date_query";
		$counts     = $wpdb->get_var( $wpdb->prepare( $sql, $form_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery

		if ( $counts ) {
			return $counts;
		}

		return 0;
	}

	/**
	 * Generate the date query
	 *
	 * @since 1.0
	 * @param object $wpdb - the WordPress database object.
	 * @param string $starting_date - the start date (dd-mm-yyy).
	 * @param string $ending_date - the end date (dd-mm-yyy).
	 * @param string $prefix Prefix.
	 * @param string $clause Clause.
	 *
	 * @return string $date_query
	 */
	private function generate_date_query( $wpdb, $starting_date = null, $ending_date = null, $prefix = '', $clause = 'AND' ) {
		$date_query = '';
		if ( ! is_null( $starting_date ) && ! is_null( $ending_date ) && ! empty( $starting_date ) && ! empty( $ending_date ) ) {
			$ending_date = $ending_date . ' 23:59:00';
			$date_query  = $wpdb->prepare( "$clause date_created >= %s AND date_created <= %s", $starting_date, $ending_date ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		} elseif ( ! is_null( $starting_date ) && ! empty( $starting_date ) ) {
				$date_query = $wpdb->prepare( "$clause date_created >= %s", $starting_date ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		} elseif ( ! is_null( $ending_date ) && ! empty( $ending_date ) ) {
			$date_query = $wpdb->prepare( "$clause date_created <= %s", $starting_date ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}

		return $date_query;
	}

	/**
	 * Count views record with non empty ip address
	 *
	 * @since 1.5.4
	 * @return int
	 */
	public function count_non_empty_ip_address() {
		global $wpdb;
		$sql   = "SELECT COUNT(`ip`) FROM {$this->get_table_name()} WHERE `ip` IS NOT NULL AND `ip` != '' LIMIT 1";
		$total = $wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery

		return intval( $total );
	}

	/**
	 * Cleanup ip address on views
	 *
	 * @since 1.5.4
	 */
	public function maybe_cleanup_ip_address() {
		global $wpdb;
		if ( $this->count_non_empty_ip_address() ) {
			$wpdb->query( "UPDATE {$this->get_table_name()} SET `ip` = NULL" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery
			forminator_maybe_log( __METHOD__ );
			return true;
		}

		return false;
	}

	/**
	 * Return views table name
	 *
	 * @since 1.6.3
	 *
	 * @return string
	 */
	public function get_table_name() {
		return Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_VIEWS );
	}
}
