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

/* Plugin Debugging */
if (!function_exists('write_log')) {
    function write_log($log)  {
       if (is_array($log) || is_object($log)) {
          error_log(print_r($log, true));
       } else {
          error_log($log);
       }
    }
 }
 /* End of Plugin Debugging */

 /* iPay88 Functions */
 if (!function_exists('format_amount')){
    function format_amount($amt)
    {
        $remove_dot = str_replace('.', '', $amt);
        $remove_comma = str_replace(',', '', $remove_dot);
        return $remove_comma;
    }
}
if (!function_exists('ipay88_signature')){
    function ipay88_signature($source)
    {
        return base64_encode(hex2bin(sha1($source)));
    }
}
if (!function_exists('hex2bin')){
    function hex2bin($hexSource)
    {
        for ($i=0;$i<strlen($hexSource);$i=$i+2)
        {
            $bin .= chr(hexdec(substr($hexSource,$i,2)));
        }
        return $bin;
    }
}
 /* End of iPay88 Functions */

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
    ?><div class="error"><p>Sorry, but <strong>Give iPay88 Payment Gateway</strong> requires the <strong><a href="/wp-admin/plugin-install.php?tab=plugin-information&plugin=give">Give - Donation Plugin</a></strong> to be installed and active.</p></div><?php
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

/* Payment Gateway Section */
function add_ipay88_payment_gateway($gateways)
{
    $gateways['ipay88'] = array(
        'admin_label'    => __( 'iPay88', 'give' ),
        'checkout_label' => __( 'iPay88', 'give' ),
    );
    return $gateways;
}
add_filter( 'give_payment_gateways', 'add_ipay88_payment_gateway');
/* End of Payment Gateway Section */

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

/* iPay88 Billing Details Form */
function give_ipay88_standard_billing_fields( $form_id ) {
    
    if ( give_is_setting_enabled( give_get_option( 'ipay88_billing_details' ) ) ) {
        give_default_cc_address_fields( $form_id );

        return true;
    }

    return false;

}
add_action( 'give_ipay88_cc_form', 'give_ipay88_standard_billing_fields' );
/* End of iPay88 Billing Details Form */

/* Create Payment Data */
function create_ipay88_payment_data($insert_payment_data)
{
    $insert_payment_data['gateway'] = 'ipay88';
    return $insert_payment_data;
}
add_filter( 'give_create_payment', 'create_ipay88_payment_data');
/* End of Create Payment Data */
 
/* Process iPay88 Payment */
function give_process_ipay88_payment($payment_data)
{
    write_log('-- PROCESS PAYMENT --');
    write_log('Payment Data: ');
    write_log($payment_data);
    // Validate nonce.
    write_log('Validating nonce ...');
    give_validate_nonce( $payment_data['gateway_nonce'], 'give-gateway' );
    $payment_id = give_create_payment( $payment_data, 'ipay88' );
    write_log('Payment ID: ' . $payment_id);

    // Check payment.
    if (empty($payment_id)) {
        // Record the error.
        give_record_gateway_error(
            esc_html__( 'Payment Error', 'give' ),
            sprintf(
            /* translators: %s: payment data */
                esc_html__( 'Payment creation failed before sending donor to iPay88. Payment data: %s', 'give' ),
                json_encode( $payment_data )
            ),
            $payment_id
        );
        // Problems? Send back.
        give_send_back_to_checkout( '?payment-mode=' . $payment_data['post_data']['give-gateway'] );
    }

    // Redirect to iPay88.
    $result = construct_form_and_post($payment_id, $payment_data);
    write_log('Construct Result: ' . $result);
    exit;
}
add_action( 'give_gateway_ipay88', 'give_process_ipay88_payment' );
/* End of Process iPay88 Payment */

/* Hidden Form Generation */
function construct_form_and_post($payment_id, $payment_data) {
    
    $post_url = 'https://www.mobile88.com/epayment/entry.asp';

    // Get the success url.
    $return_url = add_query_arg( array(
        'payment-confirmation' => 'ipay88',
        'payment-id'           => $payment_id,
    ), get_permalink( give_get_option( 'success_page' ) ) );

    write_log('Constructing Form ...');
    write_log('Payment ID:');
    write_log($payment_id);
    write_log('');
    write_log('Payment Data:');
    write_log($payment_data);

    // Item name.
    $item_name = give_build_ipay88_item_title($payment_data);
    
    // Setup iPay88 API params.
    $args = array(
        'merchant_code' => give_get_option( 'ipay88_merchant_code', false ),
        'ref_no'        => $payment_id,
        'amount'        => give_is_test_mode() ? '1.00' : $payment_data['price'],
        'currency'      => give_get_currency(),
        'prod_desc'     => stripslashes( $item_name ),
        'user_name'     => $payment_data['user_info']['first_name'] . ' ' . $payment_data['user_info']['last_name'],
        'user_email'    => $payment_data['user_email'],
        'user_contact'  => $payment_data['post_data']['give_phone'],
        'remark'        => $payment_data['post_data']['give_remark'],
        'lang'          => get_bloginfo( 'charset' ),
        'return'        => $return_url,
        'cancel_return' => give_get_failed_transaction_uri( '?payment-id=' . $payment_id ),
        'cbt'           => get_bloginfo( 'name' ),
        'bn'            => 'givewp_SP',
    );

    $format_amt = format_amount($args['amount']);
    $the_string = give_get_option( 'ipay88_merchant_key', false ).$args['merchant_code'].$payment_id.$format_amt.$args['currency'];
    $the_hash = ipay88_signature($the_string);

    $args['check'] = $the_string;
    $args['signature'] = $the_hash;

    write_log('');
    write_log('Payment Args:');
    write_log($args);

   
    ?>
    <form id="ipay88-form" action="<?php echo $post_url; ?>" method="POST">
        <input type="hidden" name="MerchantCode" value="<?php echo $args['merchant_code']; ?>">
        <input type="hidden" name="RefNo" value="<?php echo $args['ref_no']; ?>">
        <input type="hidden" name="Amount" value="<?php echo $args['amount']; ?>">
        <input type="hidden" name="Currency" value="<?php echo $args['currency']; ?>">
        <input type="hidden" name="ProdDesc" value="<?php echo $args['prod_desc']; ?>">
        <input type="hidden" name="UserName" value="<?php echo $args['user_name']; ?>">
        <input type="hidden" name="UserEmail" value="<?php echo $args['user_email']; ?>">
        <input type="hidden" name="UserContact" value="<?php echo $args['user_contact']; ?>">            
        <input type="hidden" name="Remark" value="<?php echo $args['remark']; ?>">
        <input type="hidden" name="Lang" value="<?php echo $args['lang']; ?>">
        <input type="hidden" name="Check" value="<?php echo $args['check']; ?>">       
        <input type="hidden" name="Signature" value="<?php echo $args['signature']; ?>">           
        <input type="hidden" name="ResponseUrl" value="<?php echo $args['return']; ?>">                        
    </form>

    <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
    <script>
        jQuery(document).ready(function(){
            jQuery('#ipay88-form').submit();
        });
    </script>       
    <?php

    return 'success ...';
}
/* End of Hidden Form Generation */

/* Return URL Processing */
function give_ipay88_success_page_content( $content ) {
    write_log('-- PROCESSING RESPONSE --');
    write_log('Response Content:');
    write_log($_REQUEST);

    $merchantcode = $_REQUEST["MerchantCode"];
    $paymentid = $_REQUEST["PaymentId"];
    $refno = $_REQUEST["RefNo"];
    $amount = $_REQUEST["Amount"];
    $ecurrency = $_REQUEST["Currency"];
    $remark = $_REQUEST["Remark"];
    $transid = $_REQUEST["TransId"];
    $authcode = $_REQUEST["AuthCode"];
    $estatus = $_REQUEST["Status"];
    $errdesc = $_REQUEST["ErrDesc"];
    $signature = $_REQUEST["Signature"];

    if (!isset( $_GET['payment-id'] ) && ! give_get_purchase_session() ) {
        return $content;
    }
    $payment_id = isset( $_GET['payment-id'] ) ? absint( $_GET['payment-id'] ) : false;
    if ( ! $payment_id ) {
        $session    = give_get_purchase_session();
        $payment_id = give_get_purchase_id_by_key( $session['purchase_key'] );
    }
    $payment = get_post( $payment_id );
    if ( $payment && 'pending' === $payment->post_status ) {
        // Payment is still pending so show processing indicator to fix the race condition.
        ob_start();
        give_get_template_part( 'payment', 'processing' );
        $content = ob_get_clean();
    }
    write_log('Processing Status from iPay88 ...');
    if ($estatus === 1) {
        //TODO: COMPARE Return Signature with Generated Response Signature
        write_log('Logging Success: Payment ID = ' . $payment_id . ' successful ...');
        give_set_payment_transaction_id( $payment_id, $transid );
        give_update_payment_status( $payment_id, 'publish' );
    }
    else {
        write_log('Logging Error: Payment ID = ' . $payment_id . ', Error = ' . $errdesc);
        give_record_gateway_error( __( 'iPay88 Error', 'give' ), sprintf(__( $errdesc, 'give' ), json_encode( $_REQUEST ) ), $payment_id );
        give_set_payment_transaction_id( $payment_id, $transid );
		give_update_payment_status( $payment_id, 'failed' );
		give_insert_payment_note( $payment_id, __( $errdesc, 'give' ) );
    }
   
    return $content;
}
add_filter('give_payment_confirm_ipay88', 'give_ipay88_success_page_content');
/* End of Return URL Processing */

/* Build Item Title */
function give_build_ipay88_item_title($payment_data)
{
    $form_id   = intval( $payment_data['post_data']['give-form-id'] );
    $item_name = $payment_data['post_data']['give-form-title'];

    // Verify has variable prices.
    if (give_has_variable_prices( $form_id ) && isset( $payment_data['post_data']['give-price-id'] )) {
        $item_price_level_text = give_get_price_option_name( $form_id, $payment_data['post_data']['give-price-id'] );
        $price_level_amount    = give_get_price_option_amount( $form_id, $payment_data['post_data']['give-price-id'] );

        // Donation given doesn't match selected level (must be a custom amount).
        if ($price_level_amount != give_sanitize_amount( $payment_data['price'] )) {
            $custom_amount_text = give_get_meta( $form_id, '_give_custom_amount_text', true );
            // user custom amount text if any, fallback to default if not.
            $item_name .= ' - ' . give_check_variable( $custom_amount_text, 'empty', esc_html__( 'Custom Amount', 'give' ) );
        } //Is there any donation level text?
        elseif (! empty( $item_price_level_text )) {
            $item_name .= ' - ' . $item_price_level_text;
        }
    } //Single donation: Custom Amount.
    elseif (give_get_form_price( $form_id ) !== give_sanitize_amount( $payment_data['price'] )) {
        $custom_amount_text = give_get_meta( $form_id, '_give_custom_amount_text', true );
        // user custom amount text if any, fallback to default if not.
        $item_name .= ' - ' . give_check_variable( $custom_amount_text, 'empty', esc_html__( 'Custom Amount', 'give' ) );
    }

    return $item_name;
}
/* End of Build Item Title */

/* Add Phone Number Field */
function give_phone_number_form_fields( $form_id ) {
	?>
    <p id="give-phone-wrap" class="form-row form-row-wide">
        <label class="give-label" for="give-phone">
            <?php esc_html_e( 'Phone Number', 'give' ); ?>
            <?php if ( give_field_is_required( 'give_phone', $form_id ) ) { ?>
                <span class="give-required-indicator">*</span>
            <?php } ?>
            <span class="give-tooltip give-icon give-icon-question" data-tooltip="<?php esc_attr_e( 'Phone number information is required for iPay88.', 'give' ); ?>"></span>
        </label>
        <input
                class="give-input required"
                type="text"
                name="give_phone"
                placeholder="<?php esc_attr_e( 'Phone Number', 'give' ); ?>"
                id="give-phone"
                value="<?php echo isset( $give_user_info['give_phone'] ) ? $give_user_info['give_phone'] : ''; ?>"
            <?php echo( give_field_is_required( 'give_phone', $form_id ) ? ' required aria-required="true" ' : '' ); ?>
        />
    </p>
    <p id="give-remark-wrap" class="form-row form-row-wide">
        <label class="give-label" for="give-remark">
            <?php esc_html_e( 'Remark', 'give' ); ?>
            <?php if ( give_field_is_required( 'give_remark', $form_id ) ) { ?>
                <span class="give-required-indicator">*</span>
            <?php } ?>
            <span class="give-tooltip give-icon give-icon-question" data-tooltip="<?php esc_attr_e( 'Remark of donation transaction.', 'give' ); ?>"></span>
        </label>
        <input
                class="give-input required"
                type="text"
                name="give_remark"
                placeholder="<?php esc_attr_e( 'Remark', 'give' ); ?>"
                id="give-remark"
                value="<?php echo isset( $give_user_info['give_remark'] ) ? $give_user_info['give_remark'] : ''; ?>"
            <?php echo( give_field_is_required( 'give_remark', $form_id ) ? ' required aria-required="true" ' : '' ); ?>
        />
    </p>
	<?php
} 
add_action( 'give_donation_form_after_email', 'give_phone_number_form_fields', 10, 1 );
/* End of Add Phone Number Field */

/* Make Phone Number Field Required */
function give_required_phone_number($required_fields)
{
    $required_fields['give_phone'] =  array(
		'give_phone' => array(
			'error_id'      => 'invalid_phone',
			'error_message' => __( 'Please enter phone number.', 'give' ),
		));
    return $required_fields;
}
add_filter( 'give_donation_form_required_fields', 'give_required_phone_number');
/* End of Make Phone Number Field Required */