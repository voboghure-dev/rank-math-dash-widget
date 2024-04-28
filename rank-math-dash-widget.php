<?php
/**
 * Plugin Name:       Rank Math Dash Widget
 * Description:       Code challenge plugin for Rank Math
 * Requires at least: 5.0
 * Requires PHP:      7.0
 * Version:           1.0.1
 * Author:            Tapan Kumer Das
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       rank-math-dash-widget
 */

if (  ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if (  ! class_exists( 'Rank_Math_Dash_Widget' ) ) {
	class Rank_Math_Dash_Widget {
		private static $_instance = null;

		/**
		 * get single instance of this class
		 *
		 * @return object
		 */
		public static function get_instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * Constructor
		 */
		public function __construct() {
			add_action( 'wp_dashboard_setup', [$this, 'dashboard_setup'] );
			add_action( 'admin_enqueue_scripts', [$this, 'load_scripts'] );
			register_activation_hook( __FILE__, [$this, 'table_init'] );
			register_deactivation_hook( __FILE__, [$this, 'table_drop'] );
			add_action( 'rest_api_init', [$this, 'create_rest_route'] );
		}

		/**
		 * Add dashboard widget
		 *
		 * @return void
		 */
		public function dashboard_setup() {
			wp_add_dashboard_widget( 'rmdw_widget', __( 'Rank Math Graph Widget', 'rank-math-dash-widget' ), [$this, 'widget_callback'] );
		}

		/**
		 * Push root element for reactjs
		 *
		 * @return void
		 */
		public function widget_callback() {
			echo '<div id="rmdw-widget-reactjs"></div>';
		}

		/**
		 * Enqueue scripts and localize
		 *
		 * @return void
		 */
		public function load_scripts() {
			$screen = get_current_screen();
			if ( $screen->id == 'dashboard' ) {
				wp_enqueue_script( 'rmdw-reactjs', plugin_dir_url( __FILE__ ) . 'build/index.js', ['wp-element', 'wp-components', 'wp-api-fetch', 'wp-i18n'] );
				wp_localize_script( 'rmdw-reactjs', 'rmdwData', [
					'apiUrl' => home_url( '/wp-json' ),
					'nonce'  => wp_create_nonce( 'wp_rest' ),
				] );
			}
		}

		/**
		 * Create table and insert data
		 *
		 * @return void
		 */
		public function table_init() {
			global $wpdb;
			$table_name = $wpdb->prefix . 'dash_widget';
			$sql        = "CREATE TABLE {$table_name} (
                `id` INT NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(100),
                `uv` INT,
                `pv` INT,
                `created` DATE,
                PRIMARY KEY (id)
            );";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );

			$insert_query = "INSERT INTO $table_name (`name`, `uv`, `pv`, `created`) VALUES
            ('Page A', 4000, 2300, '2024-04-01'),
            ('Page B', 5500, 1100, '2024-04-03'),
            ('Page C', 4500, 2200, '2024-04-05'),
            ('Page D', 3500, 4300, '2024-04-07'),
            ('Page E', 2300, 3900, '2024-04-09'),
            ('Page F', 1200, 2900, '2024-04-11'),
            ('Page G', 6500, 4300, '2024-04-13'),
            ('Page H', 2700, 3100, '2024-04-15'),
            ('Page I', 2900, 3900, '2024-04-17'),
            ('Page J', 3100, 2900, '2024-04-19'),
            ('Page K', 5400, 4300, '2024-04-21'),
            ('Page L', 3900, 1200, '2024-04-23'),
            ('Page M', 4700, 1100, '2024-04-25'),
            ('Page N', 5100, 1900, '2024-04-27'),
            ('Page O', 6200, 2800, '2024-04-29'),
            ('Page P', 3400, 1700, '2024-04-30')
            ";

			$wpdb->query( $insert_query );
		}

		/**
		 * Drop table
		 *
		 * @return void
		 */
		public function table_drop() {
			global $wpdb;
			$table_name = $wpdb->prefix . 'dash_widget';
			$sql        = "DROP TABLE IF EXISTS $table_name";
			$wpdb->query( $sql );
		}

		/**
		 * Cretae WP REST Route
		 *
		 * @return void
		 */
		public function create_rest_route() {
			register_rest_route( 'rmdw/v1', 'recharts-data/(?P<days>\d+)', [
				'methods'             => 'GET',
				'callback'            => [$this, 'get_recharts_data_by_days'],
				'permission_callback' => [$this, 'get_recharts_permission'],
			] );
		}

		/**
		 * REST Route permissin
		 *
		 * @return boolean
		 */
		public function get_recharts_permission() {
			return current_user_can( 'read' );
		}

		/**
		 * Get rest api data
		 *
		 * @param array $request
		 * @return object
		 */
		public function get_recharts_data_by_days( $request ) {
			global $wpdb;
			$days       = $request['days'];
			$table_name = $wpdb->prefix . 'dash_widget';
			$sql        = $wpdb->prepare( "SELECT * FROM %i WHERE created >= DATE_SUB(NOW(), INTERVAL %d DAY)", $table_name, $days );

			return $wpdb->get_results( $sql );
		}

	}

	function RankMathDashWidget() {
		return Rank_Math_Dash_Widget::get_instance();
	}

	RankMathDashWidget();
}