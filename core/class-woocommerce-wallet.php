<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'Woocommerce_Wallet' ) ) :

	/**
	 * Main Woocommerce_Wallet Class.
	 *
	 * @package		WOOCOMMERC
	 * @subpackage	Classes/Woocommerce_Wallet
	 * @since		1.0.0
	 * @author		Abhishek Potdar
	 */
	final class Woocommerce_Wallet {

		/**
		 * The real instance
		 *
		 * @access	private
		 * @since	1.0.0
		 * @var		object|Woocommerce_Wallet
		 */
		private static $instance;

		/**
		 * WOOCOMMERC helpers object.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @var		object|Woocommerce_Wallet_Helpers
		 */
		public $helpers;

		/**
		 * WOOCOMMERC settings object.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @var		object|Woocommerce_Wallet_Settings
		 */
		public $settings;

		/**
		 * Throw error on object clone.
		 *
		 * Cloning instances of the class is forbidden.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @return	void
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'You are not allowed to clone this class.', 'woocommerce-wallet' ), '1.0.0' );
		}

		/**
		 * Disable unserializing of the class.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @return	void
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'You are not allowed to unserialize this class.', 'woocommerce-wallet' ), '1.0.0' );
		}

		/**
		 * Main Woocommerce_Wallet Instance.
		 *
		 * Insures that only one instance of Woocommerce_Wallet exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @access		public
		 * @since		1.0.0
		 * @static
		 * @return		object|Woocommerce_Wallet	The one true Woocommerce_Wallet
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Woocommerce_Wallet ) ) {
				self::$instance					= new Woocommerce_Wallet;
				self::$instance->base_hooks();
				self::$instance->includes();
				self::$instance->helpers		= new Woocommerce_Wallet_Helpers();
				self::$instance->settings		= new Woocommerce_Wallet_Settings();

				//Fire the plugin logic
				new Woocommerce_Wallet_Run();

				/**
				 * Fire a custom action to allow dependencies
				 * after the successful plugin setup
				 */
				do_action( 'WOOCOMMERC/plugin_loaded' );
			}

			return self::$instance;
		}

		/**
		 * Include required files.
		 *
		 * @access  private
		 * @since   1.0.0
		 * @return  void
		 */
		private function includes() {
			require_once WOOCOMMERC_PLUGIN_DIR . 'core/includes/classes/class-woocommerce-wallet-helpers.php';
			require_once WOOCOMMERC_PLUGIN_DIR . 'core/includes/classes/class-woocommerce-wallet-settings.php';

			if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
				require_once WOOCOMMERC_PLUGIN_DIR . 'core/includes/classes/class-woocommerce-payment-method.php';
			}

			require_once WOOCOMMERC_PLUGIN_DIR . 'core/includes/classes/class-woocommerce-wallet-run.php';

		}

		/**
		 * Add base hooks for the core functionality
		 *
		 * @access  private
		 * @since   1.0.0
		 * @return  void
		 */
		private function base_hooks() {
			add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );
		}

		/**
		 * Loads the plugin language files.
		 *
		 * @access  public
		 * @since   1.0.0
		 * @return  void
		 */
		public function load_textdomain() {
			load_plugin_textdomain( 'woocommerce-wallet', FALSE, dirname( plugin_basename( WOOCOMMERC_PLUGIN_FILE ) ) . '/languages/' );
		}

	}

endif; // End if class_exists check.