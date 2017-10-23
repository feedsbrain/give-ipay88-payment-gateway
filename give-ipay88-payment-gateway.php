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

function add_ipay88_gateway_section($sections)
{
    $sections['ipay88'] = __( 'iPay88', 'give' );
    return $sections;
}

add_filter( 'give_get_sections_gateways', 'add_ipay88_gateway_section');

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
                'name' => __( 'iPay88 ID', 'give' ),
                'desc' => __( 'Enter your iPay88 account\'s ID.', 'give' ),
                'id'   => 'ipay88_id',
                'type' => 'text',
                ),
                array(
                    'name'    => __( 'Billing Details', 'give' ),
                    'desc'    => __( 'This option will enable the billing details section for iPay88 Standard which requires the donor\'s address to complete the donation. These fields are not required by PayPal to process the transaction, but you may have a need to collect the data.', 'give' ),
                    'id'      => 'paypal_standard_billing_details',
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
