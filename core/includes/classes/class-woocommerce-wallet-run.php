<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class Woocommerce_Wallet_Run
 *
 * Thats where we bring the plugin to life
 *
 * @package		WOOCOMMERC
 * @subpackage	Classes/Woocommerce_Wallet_Run
 * @author		Abhishek Potdar
 * @since		1.0.0
 */
class Woocommerce_Wallet_Run{

	/**
	 * Our Woocommerce_Wallet_Run constructor 
	 * to run the plugin logic.
	 *
	 * @since 1.0.0
	 */
	function __construct(){
		$this->add_hooks();
		$this->helpers = new Woocommerce_Wallet_Helpers();
		$this->user_meta_arr = $this->helpers->user_metafields();
	}

	/**
	 * ######################
	 * ###
	 * #### WORDPRESS HOOKS
	 * ###
	 * ######################
	 */

	/**
	 * Registers all WordPress and plugin related hooks
	 *
	 * @access	private
	 * @since	1.0.0
	 * @return	void
	 */
	private function add_hooks(){
	
		add_action( 'plugin_action_links_' . WOOCOMMERC_PLUGIN_BASE, array( $this, 'add_plugin_action_link' ), 20 );

		register_activation_hook(__FILE__, array($this, 'woocommerce_wallet_activate') );

		add_action( 'admin_menu', array( $this, 'intro_menu_page' ) );

		/*add_action( 'admin_menu', function() {
			remove_menu_page( 'woocommerce-wallet-introduction' );
		} );*/

		add_action ( 'admin_head', array($this, 'add_custom_styles') );

		add_action( 'admin_init', array( $this, 'redirect_to_intro_page' ) );

		add_action( 'show_user_profile', array($this, 'woo_wallet_add_user_meta') );
		add_action( 'edit_user_profile', array($this, 'woo_wallet_add_user_meta') );

		add_action( 'personal_options_update', array( $this, 'woo_wallet_update_user_meta') );
		add_action( 'edit_user_profile_update', array( $this, 'woo_wallet_update_user_meta') );

		add_filter( 'manage_users_columns', array($this, 'add_custom_user_table_cols'), 99, 1 );
		add_filter( 'manage_users_custom_column', array($this, 'add_custom_user_table_cols_value'), 99, 3 );
	
		if(!is_admin() && !wp_doing_ajax()){
			add_filter( 'woocommerce_payment_gateways', array($this, 'add_woocommerce_wallet_gateway_class') );

			add_action('woocommerce_checkout_process', array($this, 'process_woocommerce_wallet_payment') );

			add_action('woocommerce_review_order_after_order_total', array($this, 'show_user_balance_on_checkout'));
		}
		
	}

	/**
	 * ######################
	 * ###
	 * #### WORDPRESS HOOK CALLBACKS
	 * ###
	 * ######################
	 */

	/**
	* Adds action links to the plugin list table
	*
	* @access	public
	* @since	1.0.0
	*
	* @param	array	$links An array of plugin action links.
	*
	* @return	array	An array of plugin action links.
	*/
	public function add_plugin_action_link( $links ) {

		$links['our_shop'] = sprintf( '<a href="%s" title="How it Works">%s</a>', admin_url().'/admin.php?page=woocommerce-wallet-introduction', __( 'How it Works', 'woocommerce-wallet' ) );

		return $links;
	}


	public function woocommerce_wallet_activate(){
		add_option( 'woocommerce_wallet_activated', true );
	}

	/**
	* Redirects to Introduction Page after Plugin Activation
	*
	* @access	public
	* @since	1.0.0
	*/

	public function redirect_to_intro_page(){
		echo get_option('is_plugin_activated');
		if( get_option('is_plugin_activated', false) ) {
			exit( wp_redirect( admin_url().'/admin.php?page=woocommerce-wallet-introduction' ) );
		}
	}

	/**
	* Adds a custom introduction page
	*
	* @access	public
	* @since	1.0.0
	*/

	public function intro_menu_page() {
		add_menu_page(
			__( 'Woocommerce Wallet', 'woocommerce-wallet' ),
			__( 'Woocommerce Wallet', 'woocommerce-wallet' ),
			'manage_options',
			'woocommerce-wallet-introduction',
			array($this, 'woocommerce_wallet_admin_page_content'),
			'dashicons-schedule',
			55
		);
	}

	/**
	* Add a custom introduction page content
	*
	* @access	public
	* @since	1.0.0
	*/

	public function woocommerce_wallet_admin_page_content(){
		include_once(WOOCOMMERC_PLUGIN_DIR.'core/includes/admin-pages/intropage.php');
	}

	/**
	* Method to Add User Meta Fields
	*
	* @access	public
	* @since	1.0.0

	* @param	array	$user An array of current user details.
	*/

	public function woo_wallet_add_user_meta($user){
		?>
<table class="form-table">
    <h3><?php echo __( 'Wocommerce Wallet', 'woocommerce-wallet') ?></h3>
    <?php 
			
			foreach($this->user_meta_arr as $uarray){ ?>
    <tr>
        <th><label for="<?php echo $uarray['id'] ?>"><?php echo __($uarray['label']); ?></label></th>
        <td><input type="<?php echo $uarray['type']; ?>" name="<?php echo $uarray['name'] ?>"
                value="<?php echo get_user_meta($user->ID, $uarray['name'], true); ?>"
                class="regular-text <?php echo (!empty($uarray['class'])) ? $uarray['class'] : ''; ?>" /></td>
    </tr>
</table>
<?php
		}
	}

	/**
	* Method to Update User Meta Fields
	*
	* @access	public
	* @since	1.0.0

	* @param	int	$user_id An id of current user.
	*/

	public function woo_wallet_update_user_meta($user_id){
		foreach($this->user_meta_arr as $uarray){
			update_user_meta( $user_id, $uarray['name'], sanitize_text_field( $_POST[$uarray['name']] ) );
		}
	}

	public function add_custom_user_table_cols($column){
		$column['woocommerce_wallet_balance'] = 'Woo Wallet Balance';
		return $column;
	}

	public function add_custom_user_table_cols_value($val, $column_name, $user_id){
		switch ($column_name) {
			case 'woocommerce_wallet_balance' :
				$balance = (!empty(get_user_meta( $user_id, 'woocommerce_wallet_user_balance', true ))) ? get_user_meta( $user_id, 'woocommerce_wallet_user_balance', true ) : 0;
				return $this->helpers->get_woo_currency().$balance;
			default:
		}
		return $val;
	}


	public function add_woocommerce_wallet_gateway_class( $methods ) {

		$cart_total = WC()->cart->total;
		$user_wallet_balance = (int)$this->helpers->get_current_user_wallet_balance();

		if($user_wallet_balance >= $cart_total && is_user_logged_in()){
			$methods[] = 'WC_Woocommerce_Wallet_Gateway';
		}
		return $methods;
	}

	public function process_custom_payment(){

		$domain = 'wocommerce-wallet';
		if($_POST['payment_method'] != 'woocommerce_wallet_payment_gateway')
			return;
	
	}

	public function show_user_balance_on_checkout(){
		?>
<tr>
    <th><?php echo __('Wallet Balance'); ?></th>
    <td><?php echo $this->helpers->get_woo_currency().$this->helpers->get_current_user_wallet_balance(); ?></td>
</tr>

<?php
	}

	/**
	* Custom Styling for Introduction Page
	*
	* @access	public
	* @since	1.0.0

	* @param	int	$user_id An id of current user.
	*/

	public function add_custom_styles(){
		echo '<style>
		.woo-wallet-intro-page-wrap {
			padding: 20px;
			background-color: #fff;
			margin-top: 30px;
			margin: 40px 20px;
		}
		.woo-button{
			background-color: #FF5722;
			padding: 15px 40px;
			text-decoration: none;
			color: #fff !important;
			display: inline-block;
			border-radius: 5px;
		}
		.woo-wallet-intro-head p {
			margin-bottom: 25px;
		}
		.woo-wallet-intro-tabs {
			margin-top: 40px;
		}
		
		.woo-wallet-intro-tabs .nav-tab-wrapper .nav-tab{
			font-size: 16px;
			font-weight: 400;
		}
		.woo-wallet-intro-tabs .nav-tab-wrapper .nav-tab-active {background-color: #fff;}
		.woo-wallet-tab-content {
			padding: 20px 20px;
		}
		.woo-wallet-tab-content p{
			font-size: 16px;
			line-height: 1.5
		}
		</style>';
	}

}