<?php
/**
 * Plugin Name:       Rank Math Dash Widget
 * Description:       Code challenge plugin for Rank Math
 * Requires at least: 5.0
 * Requires PHP:      7.0
 * Version:           1.0.0
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
			wp_add_dashboard_widget( 'rmdw_widget', 'Rank Math Graph Widget', [$this, 'widget_callback'] );
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
			wp_enqueue_script( 'rmdw-reactjs', plugin_dir_url( __FILE__ ) . 'build/index.js', ['wp-element'] );
			wp_localize_script( 'rmdw-reactjs', 'rmdwData', [
				'apiUrl' => home_url( '/wp-json' ),
				'nonce'  => wp_create_nonce( 'wp_rest' ),
			] );
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
            ('Page A', 4000, 2300, '2024-03-20'),
            ('Page B', 5500, 1100, '2024-03-22'),
            ('Page C', 4500, 2200, '2024-03-24'),
            ('Page D', 3500, 4300, '2024-03-26'),
            ('Page E', 2300, 3900, '2024-03-28'),
            ('Page F', 1200, 2900, '2024-03-30'),
            ('Page G', 6500, 4300, '2024-04-01'),
            ('Page H', 2700, 3100, '2024-04-03'),
            ('Page I', 2900, 3900, '2024-04-05'),
            ('Page J', 3100, 2900, '2024-04-07'),
            ('Page K', 5400, 4300, '2024-04-09'),
            ('Page L', 3900, 1200, '2024-04-11'),
            ('Page M', 4700, 1100, '2024-04-13'),
            ('Page N', 5100, 1900, '2024-04-15'),
            ('Page O', 6200, 2800, '2024-04-17'),
            ('Page P', 3400, 1700, '2024-04-19')
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
			return true;
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