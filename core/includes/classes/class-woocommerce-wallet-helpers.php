<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class Woocommerce_Wallet_Helpers
 *
 * This class contains repetitive functions that
 * are used globally within the plugin.
 *
 * @package		WOOCOMMERC
 * @subpackage	Classes/Woocommerce_Wallet_Helpers
 * @author		Abhishek Potdar
 * @since		1.0.0
 */
class Woocommerce_Wallet_Helpers{

	/**
	 * ######################
	 * ###
	 * #### CALLABLE FUNCTIONS
	 * ###
	 * ######################
	 */
	
	 

	public function is_woocommerce_activated() {
		if ( class_exists( 'woocommerce' ) ) { return true; } else { return false; }
	}

	 public function get_woo_currency(){
		if($this->is_woocommerce_activated()){
			return get_woocommerce_currency_symbol();
		}
	 }

	 public function get_current_user_wallet_balance(){
		$user_id = get_current_user_id();
		$balance = (!empty(get_user_meta( $user_id, 'woocommerce_wallet_user_balance', true ))) ? get_user_meta( $user_id, 'woocommerce_wallet_user_balance', true ) : 0;

		return $balance;
	}

	public function user_metafields(){
		$meta_fields_arr = array(
			array(
				'type' => 'number',
				'name' => 'woocommerce_wallet_user_balance',
				'class' => '',
				'id' => 'woocommerce_wallet_user_balance',
				'label' => 'User Wallet Balance'
			)
		);

		return $meta_fields_arr;
	}

}