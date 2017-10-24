<?php
/*
Plugin Name:  Give iPay88 Payment Gateway
Plugin URI:   https://github.com/feedsbrain/give-ipay88-payment-gateway
Description:  iPay88 Payment Gateway Support for Give Donation Platform
Version:      0.1
Author:       Indra Gunawan
Author URI:   https://indragunawan.com/
License:      GPL3
License URI:  https://github.com/feedsbrain/give-ipay88-payment-gateway/blob/master/LICENSE
*/
if (! defined( 'ABSPATH' )) {
    exit;
}

/* Plugin Dependencies */
function check_give_plugin_dependency() {
    if ( is_admin() && current_user_can( 'activate_plugins' ) &&  !is_plugin_active( 'give/give.php' ) ) {
        add_action( 'admin_notices', 'give_plugin_notification' );

        deactivate_plugins( plugin_basename( __FILE__ ) ); 

        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }
    }
}
function give_plugin_notification(){
    ?><div class="error"><p>Sorry, but <strong>Give iPay88 Payment Gateway</strong> requires the <strong>Give - Donation Plugin</strong> to be installed and active.</p></div><?php
}
add_action( 'admin_init', 'check_give_plugin_dependency' );
/* End of Plugin Dependencies */

/* Disabled Plugin Activation Link */
function give_ipay88_payment_gateway_activation( $links, $file ) {
    if ( 'give-ipay88-payment-gateway/give-ipay88-payment-gateway.php' == $file and isset($links['activate']) )
        $links['activate'] = '<span>Activate</span>';

    return $links;
}
add_filter( 'plugin_action_links', 'give_ipay88_payment_gateway_activation', 10, 2 );
/* End of Disabled Plugin Activation Link */

/* Gateway Section */
function add_ipay88_gateway_section($sections)
{
    $sections['ipay88'] = __( 'iPay88', 'give' );
    return $sections;
}
add_filter( 'give_get_sections_gateways', 'add_ipay88_gateway_section');
/* End of Gateway Section */

/* Gateway Settings */
function add_ipay88_gateway_settings($settings)
{
    $current_section = give_get_current_setting_section();
    switch ($current_section) {
        case 'ipay88':
            $settings = array(
                array(
                    'type' => 'title',
                    'id'   => 'give_title_gateway_settings_ipay88',
                ),
                array(
                    'name' => __( 'Organization Name', 'give' ),
                    'desc' => __( 'Enter your organization name details to be displayed to your donors.', 'give' ),
                    'id'   => 'ipay88_organization_name',
                    'type' => 'text',
                    ),
                array(
                    'name' => __( 'Merchant Code', 'give' ),
                    'desc' => __( 'iPay88 Merchant Code. Please consult with iPay88 Account Manager.', 'give' ),
                    'id'   => 'ipay88_merchant_code',
                    'type' => 'text',
                    ),
                array(
                    'name' => __( 'Merchant Key', 'give' ),
                    'desc' => __( 'iPay88 Merchant Key. Please consult with iPay88 Account Manager.', 'give' ),
                    'id'   => 'ipay88_merchant_key',
                    'type' => 'text',
                    ),
                array(
                    'name'    => __( 'Billing Details', 'give' ),
                    'desc'    => __( 'Requires the donor\'s address to complete the donation?', 'give' ),
                    'id'      => 'ipay88_billing_details',
                    'type'    => 'radio_inline',
                    'default' => 'disabled',
                    'options' => array(
                        'enabled'  => __( 'Enabled', 'give' ),
                        'disabled' => __( 'Disabled', 'give' ),
                    )
                ),
                array(
                    'type' => 'sectionend',
                    'id'   => 'give_title_gateway_settings_ipay88',
                )
            );
            break;
    }
    return $settings;
}
add_filter( 'give_get_settings_gateways', 'add_ipay88_gateway_settings');
/* End of Gateway Settings */