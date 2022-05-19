<?php
use WHMCS\Database\Capsule;
use WHMCS\Billing\Invoice;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}
require_once(dirname(__FILE__) . '/include-code/instances.php');
function GBP_autoloader($class)
{
    if (strpos($class, 'Gbprimepay') !== false):
        if (!class_exists('AS_Gbprimepay_API', false)):
            #doesnt exist so include it            
            include_once(dirname(__FILE__) . '/include-code/class-as-gbprimepay-api.php');
        endif;
    endif;

}
spl_autoload_register('GBP_autoloader');

/**
 * Define module related meta data.
 *
 * Values returned here are used to determine module related capabilities and
 * settings.
 *
 * @see https://developers.whmcs.com/payment-gateways/meta-data-params/
 *
 * @return array
 */
function gatewaymodule_MetaData()
{
    return array(
        'DisplayName' => 'GBPrimePay Checkout',
        'APIVersion' => '1.1', // Use API Version 1.1
        'DisableLocalCredtCardInput' => true,
        'TokenisedStorage' => false,
    );
}

/**
 * Define gateway configuration options.
 *
 * @return array
 */
function gbprimepay_config()
{
    return array(
        // the friendly display name for a payment gateway should be
        // defined here for backwards compatibility
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'GBPrimePay Checkout',
        ),
        'gbp_checkout_enabled' => array(
            'FriendlyName' => 'Enable/Disable',
            'Type' => 'yesno',
			"Default" => '1',
            'Description' => 'Enable/Disable GBPrimePay Checkout',
        ),
        'environment' => array(
            'FriendlyName' => 'Environment',
            'Type' => 'dropdown',
            'Options' => array(
                'prelive' => 'Test Mode',
                'production' => 'Production Mode',
            ),
            'Default' => 'prelive',
            'Description' => 'Set The Test Mode or Production Mode',
        ),
        'live_public_key' => array(
            'FriendlyName' => 'Live Public Key',
            'Type' => 'text',
            'Size' => '100',
            'Default' => '',
            'Description' => 'Get your Public Key credentials from GB Prime Pay.',
        ),
        'live_secret_key' => array(
            'FriendlyName' => 'Live Private Key',
            'Type' => 'text',
            'Size' => '100',
            'Default' => '',
            'Description' => 'Get your Secret Key credentials from GB Prime Pay.',
        ),
        'live_token_key' => array(
            'FriendlyName' => 'Production Token<br><br><br><br>',
            'Type' => 'textarea',
            'Rows' => '2',
            'Cols' => '60',
            'Description' => 'Get your Token Key credentials from GB Prime Pay.',
        ),
        'test_public_key' => array(
            'FriendlyName' => 'Test Public Key',
            'Type' => 'text',
            'Size' => '100',
            'Default' => '',
            'Description' => 'Get your Public Key credentials from GB Prime Pay.',
        ),
        'test_secret_key' => array(
            'FriendlyName' => 'Test Secret Key',
            'Type' => 'text',
            'Size' => '100',
            'Default' => '',
            'Description' => 'Get your Secret Key credentials from GB Prime Pay.',
        ),
        'test_token_key' => array(
            'FriendlyName' => 'Test Token<br><br><br><br>',
            'Type' => 'textarea',
            'Rows' => '2',
            'Cols' => '60',
            'Description' => 'Get your Token Key credentials from GB Prime Pay.',
        ),
        'gbp_direct_row' => array(
			"FriendlyName" => "1.)", 
			"Type" => 'hidden',
			"Size" => "64",
			"Default" => '',
			"Description" => '3-D Secure Credit Card Payment Gateway with GBPrimePay',
		),
        'gbp_direct_enabled' => array(
            'FriendlyName' => 'Enable/Disable',
            'Type' => 'yesno',
			"Default" => '1',
            'Description' => 'Pay with Credit Card',
        ),
        'gbp_direct_display' => array(
            'FriendlyName' => 'Title',
            'Type' => 'text',
            'Size' => '100',
            'Default' => 'Pay with Credit Card',
            'Description' => '',
        ),
        'gbp_installment_row' => array(
			"FriendlyName" => "2.)", 
			"Type" => 'hidden',
			"Size" => "64",
			"Default" => '',
			"Description" => 'Credit Card Installment integration with GBPrimePay',
		),
        'gbp_installment_enabled' => array(
            'FriendlyName' => 'Enable/Disable',
            'Type' => 'yesno',
			"Default" => '1',
            'Description' => 'Pay with Credit Card Installment',
        ),
        'gbp_installment_display' => array(
            'FriendlyName' => 'Title',
            'Type' => 'text',
            'Size' => '100',
            'Default' => 'Pay with Credit Card Installment',
            'Description' => '',
        ),
        'gbp_installment_issuers_row' => array(
			"FriendlyName" => "Issuers Bank/Installment Terms.<br><br><br>", 
			"Type" => 'hidden',
			"Size" => "64",
			"Default" => '',
			"Description" => 'Input the desired Installment Terms. Separate with comma.<br>example: 3 months, 6 months, 10 months <br>eg: <b>3, 6, 10</b> ', 'gbprimepay-payment-gateways-installment',
		),
        'kasikorn_installment_term' => array(
            'FriendlyName' => 'KASIKORN<br><br><br>',
            'Type' => 'text',
            'Size' => '26',
            'Description' => '<br>Kasikornbank Public Company Limited. <br>Installment Terms. default: <b>3, 4, 5, 6, 7, 8, 9, 10</b>',
            'Default' => '3, 4, 5, 6, 7, 8, 9, 10',
        ),
        'krungthai_installment_term' => array(
            'FriendlyName' => 'KRUNG THAI<br><br><br>',
            'Type' => 'text',
            'Size' => '26',
            'Description' => '<br>Krung Thai Bank Public Company Limited. <br>Installment Terms. default: <b>3, 4, 5, 6, 7, 8, 9, 10</b>',
            'Default' => '3, 4, 5, 6, 7, 8, 9, 10',
        ),
        'thanachart_installment_term' => array(
            'FriendlyName' => 'TTB<br><br><br>',
            'Type' => 'text',
            'Size' => '26',
            'Description' => '<br>TMBThanachart Bank Public Company Limited. <br>Installment Terms. default: <b>3, 4, 6, 10</b>',
            'Default' => '3, 4, 6, 10',
        ),
        'ayudhya_installment_term' => array(
            'FriendlyName' => 'AYUDHYA<br><br><br>',
            'Type' => 'text',
            'Size' => '26',
            'Description' => '<br>Bank of Ayudhya Public Company Limited <br>Installment Terms. default: <b>3, 4, 6, 9, 10</b>',
            'Default' => '3, 4, 6, 9, 10',
        ),
        'firstchoice_installment_term' => array(
            'FriendlyName' => 'FIRST CHOICE<br><br><br>',
            'Type' => 'text',
            'Size' => '26',
            'Description' => '<br>Krungsri First Choice. <br>Installment Terms. default: <b>3, 4, 6, 9, 10, 12, 18, 24</b>',
            'Default' => '3, 4, 6, 9, 10, 12, 18, 24',
        ),
        'scb_installment_term' => array(
            'FriendlyName' => 'SCB<br><br><br>',
            'Type' => 'text',
            'Size' => '26',
            'Description' => '<br>Siam Commercial Bank Public Company Limited. <br>Installment Terms. default: <b>3, 4, 6, 10</b>',
            'Default' => '3, 4, 6, 10',
        ),
        'bbl_installment_term' => array(
            'FriendlyName' => 'BBL<br><br><br>',
            'Type' => 'text',
            'Size' => '26',
            'Description' => '<br>Bangkok Bank Public Company Limited. <br>Installment Terms. default: <b>3, 4, 6, 8, 9, 10</b>',
            'Default' => '3, 4, 6, 8, 9, 10',
        ),
        'gbp_qrcode_row' => array(
			"FriendlyName" => "3.)", 
			"Type" => 'hidden',
			"Size" => "64",
			"Default" => '',
			"Description" => 'QR Code integration with GBPrimePay',
		),
        'gbp_qrcode_enabled' => array(
            'FriendlyName' => 'Enable/Disable',
            'Type' => 'yesno',
            'Default' => '1',
            'Description' => 'Pay with QR Code',
        ),
        'gbp_qrcode_display' => array(
            'FriendlyName' => 'Title',
            'Type' => 'text',
            'Size' => '100',
            'Default' => 'Pay with QR Code',
            'Description' => '',
        ),
        'gbp_qrcredit_row' => array(
			"FriendlyName" => "4.)", 
			"Type" => 'hidden',
			"Size" => "64",
			"Default" => '',
			"Description" => 'QR Visa integration with GBPrimePay',
		),
        'gbp_qrcredit_enabled' => array(
            'FriendlyName' => 'Enable/Disable',
            'Type' => 'yesno',
			"Default" => '1',
            'Description' => 'Pay with QR Visa',
        ),
        'gbp_qrcredit_display' => array(
            'FriendlyName' => 'Title',
            'Type' => 'text',
            'Size' => '100',
            'Default' => 'Pay with QR Visa',
            'Description' => '',
        ),
        'gbp_qrwechat_row' => array(
			"FriendlyName" => "5.)", 
			"Type" => 'hidden',
			"Size" => "64",
			"Default" => '',
			"Description" => 'QR Wechat integration with GBPrimePay',
		),
        'gbp_qrwechat_enabled' => array(
            'FriendlyName' => 'Enable/Disable',
            'Type' => 'yesno',
			"Default" => '1',
            'Description' => 'Pay with QR Wechat',
        ),
        'gbp_qrwechat_display' => array(
            'FriendlyName' => 'Title',
            'Type' => 'text',
            'Size' => '100',
            'Default' => 'Pay with QR Wechat',
            'Description' => '',
        ),
        'gbp_linepay_row' => array(
			"FriendlyName" => "6.)", 
			"Type" => 'hidden',
			"Size" => "64",
			"Default" => '',
			"Description" => 'Rabbit Line Pay integration with GBPrimePay',
		),
        'gbp_linepay_enabled' => array(
            'FriendlyName' => 'Enable/Disable',
            'Type' => 'yesno',
			"Default" => '1',
            'Description' => 'Pay with Rabbit Line Pay',
        ),
        'gbp_linepay_display' => array(
            'FriendlyName' => 'Title',
            'Type' => 'text',
            'Size' => '100',
            'Default' => 'Pay with Rabbit Line Pay',
            'Description' => '',
        ),
        'gbp_truewallet_row' => array(
			"FriendlyName" => "7.)", 
			"Type" => 'hidden',
			"Size" => "64",
			"Default" => '',
			"Description" => 'TrueMoney Wallet integration with GBPrimePay',
		),
        'gbp_truewallet_enabled' => array(
            'FriendlyName' => 'Enable/Disable',
            'Type' => 'yesno',
			"Default" => '1',
            'Description' => 'Pay with TrueMoney Wallet',
        ),
        'gbp_truewallet_display' => array(
            'FriendlyName' => 'Title',
            'Type' => 'text',
            'Size' => '100',
            'Default' => 'Pay with TrueMoney Wallet',
            'Description' => '',
        ),
        'gbp_mbanking_row' => array(
			"FriendlyName" => "8.)", 
			"Type" => 'hidden',
			"Size" => "64",
			"Default" => '',
			"Description" => 'Mobile Banking integration with GBPrimePay',
		),
        'gbp_mbanking_enabled' => array(
            'FriendlyName' => 'Enable/Disable',
            'Type' => 'yesno',
			"Default" => '1',
            'Description' => 'Pay with Mobile Banking',
        ),
        'gbp_mbanking_display' => array(
            'FriendlyName' => 'Title',
            'Type' => 'text',
            'Size' => '100',
            'Default' => 'Pay with Mobile Banking',
            'Description' => '',
        ),
        'gbp_barcode_row' => array(
			"FriendlyName" => "9.)", 
			"Type" => 'hidden',
			"Size" => "64",
			"Default" => '',
			"Description" => 'Bill Payment integration with GBPrimePay',
		),
        'gbp_barcode_enabled' => array(
            'FriendlyName' => 'Enable/Disable',
            'Type' => 'yesno',
			"Default" => '1',
            'Description' => 'Pay with Bill Payment',
        ),
        'gbp_barcode_display' => array(
            'FriendlyName' => 'Title',
            'Type' => 'text',
            'Size' => '100',
            'Default' => 'Pay with Bill Payment',
            'Description' => '',
        ),
    );
}

/**
 * Payment link.
 *
 * Required by third party payment gateway modules only.
 *
 * Defines the HTML output displayed on an invoice. Typically consists of an
 * HTML form that will take the user to the payment gateway endpoint.
 *
 * @param array $params Payment Gateway Module Parameters
 *
 * @see https://developers.whmcs.com/payment-gateways/third-party-gateway/
 *
 * @return string
 */
function gbprimepay_link($params)
{
    // Gateway Configuration Parameters
    $init_gbp = array();
    $checkout_sort_method = gbprimepay_get_gateway($params);
    $whmcsgateway = array();
    $gbpgateway = array('gbprimepay','gbprimepay_installment','gbprimepay_qrcode','gbprimepay_qrcredit','gbprimepay_qrwechat','gbprimepay_barcode');
    $sortGateways = array();
    $sortGatewaysTXT = array();

    
    $checkout_language = AS_Gbprimepay_API::getCurrentLanguage();
    $checkout_currency_iso = AS_Gbprimepay_API::getCurrencyISO();
    $whmcsgateway = gbprimepay_get_gateway($params);
    if(!$whmcsgateway){
        return;
    }

    // System Parameters
    $systemUrl = $params['systemurl'];
    $returnUrl = $params['returnurl'];
    $langPayNow = $params['langpaynow'];
    $moduleDisplayName = $params['name'];
    $moduleName = $params['paymentmethod'];
    $whmcsVersion = $params['whmcsVersion'];

    // Invoice Parameters
    $invoiceId = $params['invoiceid'];
    $description = $params["description"];
    $amount = $params['amount'];
    $currencyCode = $params['currency'];

    $checkout_cancelUrl = $returnUrl;
    $checkout_failedUrl = $returnUrl;
    $checkout_responseUrl = $returnUrl;
    $checkout_backgroundUrl = $systemUrl . 'modules/gateways/callback/' . $moduleName . '.php';
    $checkout_SVG = $systemUrl . 'modules/gateways/include-code/SafeSecure.svg';

    // Client Parameters
    $checkout_first_name = $params['clientdetails']['firstname'];
    $checkout_last_name = $params['clientdetails']['lastname'];
    $checkout_customerName = $checkout_first_name. ' ' .$checkout_last_name;
    $checkout_customerEmail = $params['clientdetails']['email'];
    
    // $checkout_customerTelephone = $params['clientdetails']['phonenumberformatted'];
    $checkout_customerTelephone = '0'.$params['clientdetails']['phonenumber'];

    $companyName = $params['companyname'];
    $companyClient = $params['clientdetails']['companyname'];
    $address1 = $params['clientdetails']['address1'];
    $address2 = $params['clientdetails']['address2'];
    $city = $params['clientdetails']['city'];
    $state = $params['clientdetails']['state'];
    $postcode = $params['clientdetails']['postcode'];
    $country = $params['clientdetails']['country'];

    $checkout_customerAddress = '';    
    if($companyClient){
        $checkout_customerAddress .= $companyClient . " ";
    }    
    if($address1){
        $checkout_customerAddress .= $address1 . " ";
    }    
    if($address2){
        $checkout_customerAddress .= $address2 . " ";
    }    
    if($city){
        $checkout_customerAddress .= $city . " ";
    }    
    if($state){
        $checkout_customerAddress .= $state . " ";
    }    
    if($postcode){
        $checkout_customerAddress .= $postcode . " ";
    }   
    // if($country){
    //     $checkout_customerAddress .= $country . " ";
    // }

    if ($params['environment'] === 'prelive') {
        $checkout_url = gbp_instances('URL_CHECKOUT_TEST');
    } else {
        $checkout_url = gbp_instances('URL_CHECKOUT_LIVE');
    }
    $amount = $params['amount'];
    $checkout_amount = number_format((($amount * 100)/100), 2, '.', '');
    $checkout_detail = 'Charge for ' . $params['description'];
    $checkout_referenceNo = ''.substr(time(), 4, 5).'00'.$params['invoiceid'];
    $checkout_serialID = AS_Gbprimepay_API::generateID();
    $checkout_platform = gbp_instances('PLATFORM');
    $checkout_mode = gbp_instances('MODE');
    $checkout_status = gbp_instances('STATUS');
    $checkout_domain = AS_Gbprimepay_API::getDomain();
    $checkout_otpCode = 'Y';
    $checkout_method = AS_Gbprimepay_API::getSelectMethod($whmcsgateway[0]);
    $checkout_environment = $params['environment'];

    if ($params['environment'] === 'prelive') {
        $merchant_url = gbp_instances('URL_MERCHANT_TEST');
        $merchant_configkey = $params['test_public_key'];
    } else {
        $merchant_url = gbp_instances('URL_MERCHANT_LIVE');
        $merchant_configkey = $params['live_public_key'];
    }

    if ($params['environment'] === 'prelive') {
        $checkout_url = gbp_instances('URL_CHECKOUT_TEST');
    } else {
        $checkout_url = gbp_instances('URL_CHECKOUT_LIVE');
    }

    if ($params['environment'] === 'prelive') {
        $init_gbp['environment']['prelive'] = array(
            "public_key" => $params['test_public_key'],
            "secret_key" => $params['test_secret_key'],
            "token_key" => $params['test_token_key'],
        ); 
    } else {
        $init_gbp['environment']['production'] = array(
            "public_key" => $params['live_public_key'],
            "secret_key" => $params['live_secret_key'],
            "token_key" => $params['live_token_key'],
        ); 
    }
    if ($params['gbp_checkout_enabled'] !== '') {
        $init_gbp['init_gateways']['creditcard'] = array(
            "enabled" => 'yes',
            "display" => $params['gbp_direct_display'],
        ); 
    }
    if ($params['gbp_installment_enabled'] !== '') {
        $init_gbp['init_gateways']['installment'] = array(
            "enabled" => 'yes',
            "display" => $params['gbp_installment_display'],
        ); 
        $init_gbp['init_gateways']['installment_options'] = array(
            "kasikorn_installment_term" => $params['kasikorn_installment_term'],
            "krungthai_installment_term" => $params['krungthai_installment_term'],
            "thanachart_installment_term" => $params['thanachart_installment_term'],
            "ayudhya_installment_term" => $params['ayudhya_installment_term'],
            "firstchoice_installment_term" => $params['firstchoice_installment_term'],
            "scb_installment_term" => $params['scb_installment_term'],
            "bbl_installment_term" => $params['bbl_installment_term'],
        ); 
    }
    if ($params['gbp_qrcode_enabled'] !== '') {
        $init_gbp['init_gateways']['qrcode'] = array(
            "enabled" => 'yes',
            "display" => $params['gbp_qrcode_display'],
        ); 
    }
    if ($params['gbp_qrcredit_enabled'] !== '') {
        $init_gbp['init_gateways']['qrcredit'] = array(
            "enabled" => 'yes',
            "display" => $params['gbp_qrcredit_display'],
        ); 
    }

    if ($params['gbp_qrwechat_enabled'] !== '') {
        $init_gbp['init_gateways']['qrwechat'] = array(
            "enabled" => 'yes',
            "display" => $params['gbp_qrwechat_display'],
        ); 
    }

    if ($params['gbp_linepay_enabled'] !== '') {
        $init_gbp['init_gateways']['linepay'] = array(
            "enabled" => 'yes',
            "display" => $params['gbp_linepay_display'],
        ); 
    }

    if ($params['gbp_truewallet_enabled'] !== '') {
        $init_gbp['init_gateways']['truewallet'] = array(
            "enabled" => 'yes',
            "display" => $params['gbp_truewallet_display'],
        ); 
    }

    if ($params['gbp_mbanking_enabled'] !== '') {
        $init_gbp['init_gateways']['mbanking'] = array(
            "enabled" => 'yes',
            "display" => $params['gbp_mbanking_display'],
        ); 
    }

    if ($params['gbp_barcode_enabled'] !== '') {
        $init_gbp['init_gateways']['barcode'] = array(
            "enabled" => 'yes',
            "display" => $params['gbp_barcode_display'],
        ); 
    }
    $merchant_data = AS_Gbprimepay_API::sendMerchantCurl("$merchant_url",$merchant_configkey, [], 'GET');
    $product_data = array();
    $invoice = Invoice::find($params['invoiceid']);
    $client = $params['clientdetails'];
    $products = $invoice->getBillingValues();
    unset($products['overdue']);
    $i = 0;
    $unit = '';
    foreach ( $products as $k => $v ) {
        $name = htmlspecialchars_decode(StripSlashes($v['description']), ENT_COMPAT | ENT_QUOTES);
        $quantity = 1;
        $subtotal = $v['amount'];
        $price = $v['amount'];
        $amount = $v['amount'];
        $tax = 0;        
        if ($v['recurringCyclePeriod'] && $v['recurringCyclePeriod'] > 0) {
            if ($v['recurringCyclePeriod'] && $v['recurringCyclePeriod'] > 1) {
                $unit = $v['recurringCyclePeriod'].' '. $v['recurringCycleUnits'];
            } else {
                $unit = $v['recurringCycleUnits'];
            }
        } else {
            $unit = $v['recurringCycleUnits'];
        }
        $product_data['products_items_'.$i] = array(
            "items_name" => $name,
            "items_price" => $price,
            "items_quantity" => $quantity,
            "items_subtotal" => $subtotal,
            "items_tax" => $tax,
            "items_total" => $amount,
        );
        $i++;
    }
    $currency_data = array(
        "currencyCode" => '764',
        "currencySign" => '฿',
        "currencyISO" => $checkout_currency_iso,
    ); 

    $capsuledata = Capsule::table('tblinvoices')
        ->where('id', (int) $params['invoiceid'])
        ->first();
    if($checkout_language=='Thai'){
        $total_data = array(
            "total_description" => $params['description'],
            "total_subtotal" => $capsuledata->subtotal,
            "total_subtotal_text" => 'รวม',
            "total_total_text" => 'รวมทั้งสิ้น',
            "total_tax_text" => 'ภาษีมูลค่าเพิ่ม ('. abs($capsuledata->taxrate) .'%)',
            "total_unit_text" => $currency_data['currencySign'] .' / '. $unit,
            "total_tax" => ($capsuledata->tax + $capsuledata->tax2),
            "total_total" => $capsuledata->total,
        ); 
    } else {
        $total_data = array(
            "total_description" => $params['description'],
            "total_subtotal" => $capsuledata->subtotal,
            "total_subtotal_text" => 'Sub Total',
            "total_total_text" => 'Grand Total',
            "total_tax_text" => 'Taxes ('. abs($capsuledata->taxrate) .'% VAT)',
            "total_unit_text" => $currency_data['currencySign'] .' / '. $unit,
            "total_tax" => ($capsuledata->tax + $capsuledata->tax2),
            "total_total" => $capsuledata->total,
        ); 
    }
    $payment_data = array(
        "payment_amount" => $checkout_amount,
        "payment_referenceNo" => $checkout_referenceNo,
        "payment_otpCode" => $checkout_otpCode,
        "payment_detail" => $checkout_detail,
        "payment_cancelUrl" => $checkout_cancelUrl,
        "payment_failedUrl" => $checkout_failedUrl,
        "payment_responseUrl" => $checkout_responseUrl,
        "payment_backgroundUrl" => $checkout_backgroundUrl,
        "payment_customerName" => $checkout_customerName,
        "payment_customerEmail" => $checkout_customerEmail,
        "payment_customerAddress" => $checkout_customerAddress,
        "payment_customerTelephone" => $checkout_customerTelephone,
        "payment_merchantDefined1" => $checkout_serialID,
        "payment_merchantDefined2" => '',
        "payment_merchantDefined3" => $checkout_referenceNo,
        "payment_merchantDefined4" => '',
        "payment_merchantDefined5" => '',
    ); 
    $customer_data = array(
        "customer_first_name" => $checkout_first_name,
        "customer_last_name" => $checkout_last_name,
        "customer_name" => $checkout_customerName,
        "customer_email" => $checkout_customerEmail,
        "customer_address" => $checkout_customerAddress,
        "customer_telephone" => $checkout_customerTelephone,
    );

    $htmlOutput = '<form  method="post"  target="_top" action="' . $checkout_url . '">';
    $htmlOutput .= '<input type="hidden" name="serialID" value="'. $checkout_serialID.'">';
    $htmlOutput .= '<input type="hidden" name="domain" value="'. $checkout_domain.'">';
    $htmlOutput .= '<input type="hidden" name="platform" value="'. $checkout_platform.'">';
    $htmlOutput .= '<input type="hidden" name="mode" value="'. $checkout_mode.'">';
    $htmlOutput .= '<input type="hidden" name="status" value="'. $checkout_status.'">';
    $htmlOutput .= '<input type="hidden" name="method" value="'. $checkout_method.'">';
    $htmlOutput .= '<input type="hidden" name="environment" value="'. $checkout_environment.'">';
    $htmlOutput .= '<input type="hidden" name="language" value="'. $checkout_language.'">';
    
    $initgbpArray = $init_gbp;
    if (isset($initgbpArray)) {
        $keys = array_keys($initgbpArray);
        for($i = 0; $i < count($initgbpArray); $i++) {
            if($keys[$i]=='environment') {
                foreach($initgbpArray[$keys[$i]] as $key => $value) {
                    if($key=='prelive'){                    
                        foreach($initgbpArray[$keys[$i]][$key] as $ikey => $ivalue) {
                            $htmlOutput .=  '<input type="hidden" name="init_gbp[environment][prelive]['. $ikey .']" value="'. $ivalue .'">';
                        }
                    }
                    if($key=='production'){                    
                        foreach($initgbpArray[$keys[$i]][$key] as $ikey => $ivalue) {
                            $htmlOutput .=  '<input type="hidden" name="init_gbp[environment][production]['. $ikey .']" value="'. $ivalue .'">';
                        }
                    }
                }
            }
            if ($keys[$i]=='init_gateways') {
                $sortArray = $checkout_sort_method;
                if(isset($sortArray)) {

// 4 TAB
$jkeys = array_keys($sortArray);
foreach($sortArray as $jkey => $jvalue) {
    foreach($initgbpArray[$keys[$i]] as $key => $value) {
        if(($key=='creditcard') && ($jvalue==$key)){                    
            foreach($initgbpArray[$keys[$i]][$key] as $ikey => $ivalue) {
                $htmlOutput .=  '<input type="hidden" name="init_gbp[init_gateways][creditcard]['. $ikey .']" value="'. $ivalue .'">';
            }
        }
        if(($key=='installment') && ($jvalue==$key)){                    
            foreach($initgbpArray[$keys[$i]][$key] as $ikey => $ivalue) {
                $htmlOutput .=  '<input type="hidden" name="init_gbp[init_gateways][installment]['. $ikey .']" value="'. $ivalue .'">';
            }
        }
        if(($key=='installment_options') && ($jvalue=='installment')){                    
            foreach($initgbpArray[$keys[$i]][$key] as $ikey => $ivalue) {
                $htmlOutput .=  '<input type="hidden" name="init_gbp[init_gateways][installment_options]['. $ikey .']" value="'. $ivalue .'">';
            }
        }
        if(($key=='qrcode') && ($jvalue==$key)){                    
            foreach($initgbpArray[$keys[$i]][$key] as $ikey => $ivalue) {
                $htmlOutput .=  '<input type="hidden" name="init_gbp[init_gateways][qrcode]['. $ikey .']" value="'. $ivalue .'">';
            }
        }
        if(($key=='qrcredit') && ($jvalue==$key)){                    
            foreach($initgbpArray[$keys[$i]][$key] as $ikey => $ivalue) {
                $htmlOutput .=  '<input type="hidden" name="init_gbp[init_gateways][qrcredit]['. $ikey .']" value="'. $ivalue .'">';
            }
        }
        if(($key=='qrwechat') && ($jvalue==$key)){                    
            foreach($initgbpArray[$keys[$i]][$key] as $ikey => $ivalue) {
                $htmlOutput .=  '<input type="hidden" name="init_gbp[init_gateways][qrwechat]['. $ikey .']" value="'. $ivalue .'">';
            }
        }
        if(($key=='linepay') && ($jvalue==$key)){                    
            foreach($initgbpArray[$keys[$i]][$key] as $ikey => $ivalue) {
                $htmlOutput .=  '<input type="hidden" name="init_gbp[init_gateways][linepay]['. $ikey .']" value="'. $ivalue .'">';
            }
        }
        if(($key=='truewallet') && ($jvalue==$key)){                    
            foreach($initgbpArray[$keys[$i]][$key] as $ikey => $ivalue) {
                $htmlOutput .=  '<input type="hidden" name="init_gbp[init_gateways][truewallet]['. $ikey .']" value="'. $ivalue .'">';
            }
        }
        if(($key=='mbanking') && ($jvalue==$key)){                    
            foreach($initgbpArray[$keys[$i]][$key] as $ikey => $ivalue) {
                $htmlOutput .=  '<input type="hidden" name="init_gbp[init_gateways][mbanking]['. $ikey .']" value="'. $ivalue .'">';
            }
        }
        if(($key=='barcode') && ($jvalue==$key)){                    
            foreach($initgbpArray[$keys[$i]][$key] as $ikey => $ivalue) {
                $htmlOutput .=  '<input type="hidden" name="init_gbp[init_gateways][barcode]['. $ikey .']" value="'. $ivalue .'">';
            }
        }
    }
}// END 4 TAB
                }
            }
        }
    }
    $merchantArray = $merchant_data;
    if (isset($merchantArray)) {
        $keys = array_keys($merchantArray);
        foreach($merchantArray as $key => $value) {
            if($key=='merchant_conditions'){
                $htmlOutput .=  '<input type="hidden" name="merchant_collection['. $key .']" value="'. htmlentities($value) .'">';
            }else{                    
                $htmlOutput .=  '<input type="hidden" name="merchant_collection['. $key .']" value="'. $value .'">';
            }
        }
    }

    $productArray = $product_data;
    if(isset($productArray)){
        $keys = array_keys($productArray);
        for($i = 0; $i < count($productArray); $i++) {
            foreach($productArray[$keys[$i]] as $key => $value) {
                $htmlOutput .=  '<input type="hidden" name="products_collection[products_items_'. $i .']['. $key .']" value="'. $value .'">';
            }
        }    
    }

    $totalArray = $total_data;
    if(isset($totalArray)){
      $keys = array_keys($totalArray);
          foreach($totalArray as $key => $value) {
              $htmlOutput .=  '<input type="hidden" name="total_collection['. $key .']" value="'. $value .'">';
          }
    }

    $paymentArray = $payment_data;
    if(isset($paymentArray)){
      $keys = array_keys($paymentArray);
          foreach($paymentArray as $key => $value) {
              $htmlOutput .=  '<input type="hidden" name="payment_collection['. $key .']" value="'. $value .'">';
          }
    }

    $currencyArray = $currency_data;
    if(isset($currencyArray)){
      $keys = array_keys($currencyArray);
          foreach($currencyArray as $key => $value) {
              $htmlOutput .=  '<input type="hidden" name="currency['. $key .']" value="'. $value .'">';
          }
    }
    
    $sortArray = $checkout_sort_method;
    if(isset($sortArray)){
      $keys = array_keys($sortArray);
          foreach($sortArray as $key => $value) {
              $htmlOutput .=  '<input type="hidden" name="sort_method['. $key .']" value="'. $value .'">';
          }
    }

    
    $customerArray = $customer_data;
    if(isset($customerArray)){
      $keys = array_keys($customerArray);
          foreach($customerArray as $key => $value) {
              $htmlOutput .=  '<input type="hidden" name="customer_collection['. $key .']" value="'. $value .'">';
          }
    }



    $htmlOutput .= '<input type="hidden" name="url_complete" value="'. $checkout_responseUrl.'">';
    $htmlOutput .= '<input type="hidden" name="url_callback" value="'. $checkout_backgroundUrl.'">';
    $htmlOutput .= '<input type="hidden" name="url_cancel" value="'. $checkout_responseUrl.'">';
    $htmlOutput .= '<input type="hidden" name="url_error" value="'. $checkout_responseUrl.'">';

    $htmlOutput .= '<input type="submit" value="' . $langPayNow . '"  style="display:block;width:200px;padding:0 auto;margin:1.5rem auto 0 auto;"/>';
    $htmlOutput .= '<img src="'.$checkout_SVG.'" style="display:block;width:193px;height:25px;align-items: center;margin: .3rem auto 1.5rem auto;"/>';
    $htmlOutput .= '</form>';

    return $htmlOutput;
}

function gbprimepay_get_gateway($params){
    $whmcsgateway = array();
    if ($params['gbp_direct_enabled'] !== '') {
        $whmcsgateway[] = 'creditcard';
    }
    if ($params['gbp_installment_enabled'] !== '') {
        $whmcsgateway[] = 'installment';
    }
    if ($params['gbp_qrcode_enabled'] !== '') {
        $whmcsgateway[] = 'qrcode';
    }
    if ($params['gbp_qrcredit_enabled'] !== '') {
        $whmcsgateway[] = 'qrcredit';
    }
    if ($params['gbp_qrwechat_enabled'] !== '') {
        $whmcsgateway[] = 'qrwechat';
    }
    if ($params['gbp_linepay_enabled'] !== '') {
        $whmcsgateway[] = 'linepay';
    }
    if ($params['gbp_truewallet_enabled'] !== '') {
        $whmcsgateway[] = 'truewallet';
    }
    if ($params['gbp_mbanking_enabled'] !== '') {
        $whmcsgateway[] = 'mbanking';
    }
    if ($params['gbp_barcode_enabled'] !== '') {
        $whmcsgateway[] = 'barcode';
    }
	return $whmcsgateway;
}