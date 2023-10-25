<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WC_Gateway_Custom
 *
 * This class contains repetitive functions that
 * are used globally within the plugin.
 *
 * @package		WOOCOMMERC
 * @subpackage	Classes/WC_Gateway_Custom
 * @author		Abhishek Potdar
 * @since		1.0.0
 */

 add_action('plugins_loaded', 'init_custom_gateway_class');
function init_custom_gateway_class(){
    class WC_Woocommerce_Wallet_Gateway extends WC_Payment_Gateway {

    public $domain;

    /**
     * Constructor for the gateway.
     */
    public function __construct() {

        $this->domain = 'woocommerce-wallet';

        $this->id                 = 'woocommerce_wallet_payment_gateway';
        $this->icon               = apply_filters('woocommerce_custom_gateway_icon', '');
        $this->has_fields         = false;
        $this->method_title       = __( 'Woocommerce Wallet', $this->domain );
        $this->method_description = __( 'Allows payments with Wallet.', $this->domain );

        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();

        // Define user set variables
        $this->title        = $this->get_option( 'title' );
        $this->description  = $this->get_option( 'description' );
        $this->instructions = $this->get_option( 'instructions', $this->description );
        $this->order_status = $this->get_option( 'order_status', 'completed' );


        $this->helpers = new Woocommerce_Wallet_Helpers();

        // Actions
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );

        // Customer Emails
        add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
    }

    /**
     * Initialise Gateway Settings Form Fields.
     */
    public function init_form_fields() {

        $this->form_fields = array(
            'enabled' => array(
                'title'   => __( 'Enable/Disable', $this->domain ),
                'type'    => 'checkbox',
                'label'   => __( 'Enable Custom Payment', $this->domain ),
                'default' => 'yes'
            ),
            'title' => array(
                'title'       => __( 'Woocommerce Wallet', $this->domain ),
                'type'        => 'text',
                'description' => __( 'Allow Users to pay using their wallet balance.', $this->domain ),
                'default'     => __( 'Woocommerce Wallet', $this->domain ),
                'desc_tip'    => true,
            ),
            'order_status' => array(
                'title'       => __( 'Order Status', $this->domain ),
                'type'        => 'select',
                'class'       => 'wc-enhanced-select',
                'description' => __( 'Choose whether status you wish after checkout.', $this->domain ),
                'default'     => 'wc-completed',
                'desc_tip'    => true,
                'options'     => wc_get_order_statuses()
            ),
            'description' => array(
                'title'       => __( 'Description', $this->domain ),
                'type'        => 'textarea',
                'description' => __( 'Pay using your wallet balance.', $this->domain ),
                'default'     => __('Pay Using your wallet balance', $this->domain),
                'desc_tip'    => true,
            ),
            'instructions' => array(
                'title'       => __( 'Instructions', $this->domain ),
                'type'        => 'textarea',
                'description' => __( 'Instructions that will be added to the thank you page and emails.', $this->domain ),
                'default'     => '',
                'desc_tip'    => true,
            ),
        );
    }

    /**
     * Output for the order received page.
     */
    public function thankyou_page() {
        if ( $this->instructions )
            echo wpautop( wptexturize( $this->instructions ) );
    }

    /**
     * Add content to the WC emails.
     *
     * @access public
     * @param WC_Order $order
     * @param bool $sent_to_admin
     * @param bool $plain_text
     */
    public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
        if ( $this->instructions && ! $sent_to_admin && 'custom' === $order->payment_method && $order->has_status( 'on-hold' ) ) {
            echo wpautop( wptexturize( $this->instructions ) ) . PHP_EOL;
        }
    }

    /**
     * Process the payment and return the result.
     *
     * @param int $order_id
     * @return array
     */
    public function process_payment( $order_id ) {

        $order = wc_get_order( $order_id );

        $user_id = $order->get_user_id();

        $order_total = $order->get_total();

        $user_wallet_balance = get_user_meta( $user_id, 'woocommerce_wallet_user_balance', true );

        $final_balance = $user_wallet_balance - $order_total;

        $status = 'wc-' === substr( $this->order_status, 0, 3 ) ? substr( $this->order_status, 3 ) : $this->order_status;

        // Set order status
        $order->update_status( $status, __( 'Checkout with Woocommerce Wallet. ', $this->domain ) );

        // or call the Payment complete
        // $order->payment_complete();

        // Reduce stock levels
        $order->reduce_order_stock();

        // Remove cart
        WC()->cart->empty_cart();

        update_user_meta($user_id, 'woocommerce_wallet_user_balance', $final_balance);



        // Return thankyou redirect
        return array(
            'result'    => 'success',
            'redirect'  => $this->get_return_url( $order )
        );
    }
    }
}