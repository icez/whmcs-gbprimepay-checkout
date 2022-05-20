<?php
/**
 * WHMCS Sample Payment Callback File
 *
 *
 * For more information, please refer to the online documentation.
 *
 * @see https://developers.whmcs.com/payment-gateways/callbacks/
 *
 * @copyright Copyright (c) WHMCS Limited 2017
 * @license http://www.whmcs.com/license/ WHMCS Eula
 */

// Require libraries needed for gateway module functions.
require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';
require_once(dirname(__FILE__) . '/../include-code/instances.php');
include_once(dirname(__FILE__) . '/../include-code/class-as-gbprimepay-api.php');

// Detect module name from filename.
$gatewayModuleName = basename(__FILE__, '.php');

// Fetch gateway configuration parameters.
$gatewayParams = getGatewayVariables($gatewayModuleName);

// Die if module is not active.
if (!$gatewayParams['type']) {
    die("Module Not Activated");
}

// Retrieve data returned in payment gateway callback
// Varies per payment gateway

      
if ($gatewayParams['environment'] === 'prelive') {
    $checkout_url = gbp_instances('URL_CHECKOUT_TEST');
    $checkout_configkey = $gatewayParams['test_public_key'];
} else {
    $checkout_url = gbp_instances('URL_CHECKOUT_LIVE');
    $checkout_configkey = $gatewayParams['live_public_key'];
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('no further process');
}

$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
if(strcasecmp($contentType, 'application/json') == 0) {

    $raw_post = @file_get_contents( 'php://input' );
    $payload  = json_decode( $raw_post );
    $paymentType = $payload->{'paymentType'};

    $referenceNo = $payload->{'referenceNo'};
    $order_id = substr($payload->{'referenceNo'}, 7);
    $invoiceId = checkCbInvoiceID($order_id, $gatewayParams['name']);
    $paymentAmount = $payload->{'amount'};
    $paymentFee = $payload->{'fee'} ?? 0;
    $ordertxt = '';
    if ($invoiceId) {
        $ordertxt = $invoiceId;
    }

    if ($paymentType=='Q') {
        // Qr Code
        if ( isset( $payload->{'resultCode'} ) ) {
            if ($payload->{'resultCode'} == '00') {
                $checkoutgbpReferenceNo = $payload->{'gbpReferenceNo'};
                $transactionStatus = 'QR Code Payment Authorized.';
                checkCbTransID($checkoutgbpReferenceNo);
                addInvoicePayment(
                $invoiceId,
                $checkoutgbpReferenceNo,
                $paymentAmount,
                $paymentFee,
                $gatewayModuleName
                );
                logTransaction($gatewayParams['name'], $payload, $transactionStatus);

                // checkout_afterpay_url
                $checkoutmethod = 'qrcode';
                $checkoutshoprefNo = $ordertxt;
                $checkoutserialID = $payload->{'merchantDefined1'};
                $checkoutID = $payload->{'merchantDefined5'};
                $checkoutamount = $payload->{'amount'};
                $checkoutdate = $payload->{'date'};
                $checkouttime = $payload->{'time'};
                $url = $checkout_url.'/afterpay/'.$checkoutID;
                $field = "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"referenceNo\"\r\n\r\n$referenceNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"method\"\r\n\r\n$checkoutmethod\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"gbpReferenceNo\"\r\n\r\n$checkoutgbpReferenceNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"amount\"\r\n\r\n$checkoutamount\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"date\"\r\n\r\n$checkoutdate\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"time\"\r\n\r\n$checkouttime\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data;  name=\"shoprefNo\"\r\n\r\n$checkoutshoprefNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"serialID\"\r\n\r\n$checkoutserialID\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"checkoutID\"\r\n\r\n$checkoutID\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--";

                $checkoutReturn = AS_Gbprimepay_API::afterpayCheckout("$url", $checkout_configkey, $field, 'POST');

            } else {
                $transactionStatus = 'QR Code Payment failed.';
                logTransaction($gatewayParams['name'], $payload, $transactionStatus);
            }

        }
    }
    if($paymentType=='V'){
        // Qr Visa
        if ( isset( $payload->{'resultCode'} ) ) {
            if ($payload->{'resultCode'} == '00') {
                $checkoutgbpReferenceNo = $payload->{'gbpReferenceNo'};
                $transactionStatus = 'QR Visa Payment Authorized.';
                checkCbTransID($checkoutgbpReferenceNo);
                addInvoicePayment(
                    $invoiceId,
                    $checkoutgbpReferenceNo,
                    $paymentAmount,
                    $paymentFee,
                    $gatewayModuleName
                );
                logTransaction($gatewayParams['name'], $payload, $transactionStatus);

                // checkout_afterpay_url
                $checkoutmethod = 'qrcredit';
                $checkoutshoprefNo = $ordertxt;
                $checkoutserialID = $payload->{'merchantDefined1'};
                $checkoutID = $payload->{'merchantDefined5'};
                $checkoutamount = $payload->{'amount'};
                $checkoutdate = $payload->{'date'};
                $checkouttime = $payload->{'time'};
                $url = $checkout_url.'/afterpay/'.$checkoutID;
                $field = "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"referenceNo\"\r\n\r\n$referenceNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"method\"\r\n\r\n$checkoutmethod\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"gbpReferenceNo\"\r\n\r\n$checkoutgbpReferenceNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"amount\"\r\n\r\n$checkoutamount\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"date\"\r\n\r\n$checkoutdate\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"time\"\r\n\r\n$checkouttime\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"shoprefNo\"\r\n\r\n$checkoutshoprefNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"serialID\"\r\n\r\n$checkoutserialID\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"checkoutID\"\r\n\r\n$checkoutID\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--";

                $checkoutReturn = AS_Gbprimepay_API::afterpayCheckout("$url", $checkout_configkey, $field, 'POST');

            } else {
                $transactionStatus = 'QR Visa Payment failed.';
                logTransaction($gatewayParams['name'], $payload, $transactionStatus);
            }
        }
    }

    if($paymentType=='B'){
        // Bill Payment
        if ( isset( $payload->{'resultCode'} ) ) {
            if ($payload->{'resultCode'} == '00') {
                $checkoutgbpReferenceNo = $payload->{'gbpReferenceNo'};
                $transactionStatus = 'Bill Payment Authorized.';
                checkCbTransID($checkoutgbpReferenceNo);
                addInvoicePayment(
                    $invoiceId,
                    $checkoutgbpReferenceNo,
                    $paymentAmount,
                    $paymentFee,
                    $gatewayModuleName
                );
                logTransaction($gatewayParams['name'], $payload, $transactionStatus);

                // checkout_afterpay_url
                $checkoutmethod = 'barcode';
                $checkoutshoprefNo = $ordertxt;
                $checkoutserialID = $payload->{'merchantDefined1'};
                $checkoutID = $payload->{'merchantDefined5'};
                $checkoutamount = $payload->{'amount'};
                $checkoutdate = $payload->{'date'};
                $checkouttime = $payload->{'time'};
                $url = $checkout_url.'/afterpay/'.$checkoutID;
                $field = "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"referenceNo\"\r\n\r\n$referenceNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"method\"\r\n\r\n$checkoutmethod\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"gbpReferenceNo\"\r\n\r\n$checkoutgbpReferenceNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"amount\"\r\n\r\n$checkoutamount\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"date\"\r\n\r\n$checkoutdate\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"time\"\r\n\r\n$checkouttime\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"shoprefNo\"\r\n\r\n$checkoutshoprefNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"serialID\"\r\n\r\n$checkoutserialID\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"checkoutID\"\r\n\r\n$checkoutID\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--";

                $checkoutReturn = AS_Gbprimepay_API::afterpayCheckout("$url", $checkout_configkey, $field, 'POST');
            }else{
                $transactionStatus = 'Bill Payment failed.';
                logTransaction($gatewayParams['name'], $payload, $transactionStatus);
            }
        }
    }

} else {
    $postData = $_POST;
    $referenceNo = $postData['referenceNo'];
    $paymentType = $postData['paymentType'];
    $order_id = substr($postData['referenceNo'], 7);    
    $invoiceId = checkCbInvoiceID($order_id, $gatewayParams['name']);
    $paymentAmount = $postData['amount'];
    $paymentFee = $postData['fee'] ?? 0;
    $ordertxt = '';
    if ($invoiceId){$ordertxt = $invoiceId;}
if($paymentType=='C'){
// Credit Card 
              if ( isset( $postData['resultCode'] ) ) {
                if ($postData['resultCode'] == '00') {
                    $checkoutgbpReferenceNo = $postData['gbpReferenceNo'];
                    $transactionStatus = '3-D Secure Payment Authorized.';
                    checkCbTransID($checkoutgbpReferenceNo);
                    addInvoicePayment(
                        $invoiceId,
                        $checkoutgbpReferenceNo,
                        $paymentAmount,
                        $paymentFee,
                        $gatewayModuleName
                    );
                    logTransaction($gatewayParams['name'], $postData, $transactionStatus);

// checkout_afterpay_url
$checkoutmethod = 'creditcard';
$checkoutshoprefNo = $ordertxt;
$checkoutserialID = $postData['merchantDefined1'];
$checkoutcardNo = $postData['cardNo'];
$checkoutID = $postData['merchantDefined5'];
$checkoutamount = $postData['amount'];
$checkoutdate = $postData['date'];
$checkouttime = $postData['time'];
$url = $checkout_url.'/afterpay/'.$checkoutID;
$field = "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"referenceNo\"\r\n\r\n$referenceNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"method\"\r\n\r\n$checkoutmethod\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"gbpReferenceNo\"\r\n\r\n$checkoutgbpReferenceNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"amount\"\r\n\r\n$checkoutamount\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"date\"\r\n\r\n$checkoutdate\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"time\"\r\n\r\n$checkouttime\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"shoprefNo\"\r\n\r\n$checkoutshoprefNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"cardNo\"\r\n\r\n$checkoutcardNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"serialID\"\r\n\r\n$checkoutserialID\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"checkoutID\"\r\n\r\n$checkoutID\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--";

$checkoutReturn = AS_Gbprimepay_API::afterpayCheckout("$url", $checkout_configkey, $field, 'POST');

                }else{
                    $transactionStatus = '3-D Secure Payment failed.';
                    logTransaction($gatewayParams['name'], $postData, $transactionStatus);
                }

        }
}
if($paymentType=='I'){
// Credit Card Installment 
            if ( isset( $postData['resultCode'] ) ) {
                if ($postData['resultCode'] == '00') {
                    $checkoutgbpReferenceNo = $postData['gbpReferenceNo'];
                    $transactionStatus = 'Credit Card Installment Payment Authorized.';
                    checkCbTransID($checkoutgbpReferenceNo);
                    addInvoicePayment(
                        $invoiceId,
                        $checkoutgbpReferenceNo,
                        $paymentAmount,
                        $paymentFee,
                        $gatewayModuleName
                    );
                    logTransaction($gatewayParams['name'], $postData, $transactionStatus);
// checkout_afterpay_url
$checkoutmethod = 'installment';
$checkoutshoprefNo = $ordertxt;
$checkoutserialID = $postData['merchantDefined1'];
$checkoutcardNo = $postData['cardNo'];
$checkoutID = $postData['merchantDefined5'];
$checkoutamount = $postData['amount'];
$checkoutdate = $postData['date'];
$checkouttime = $postData['time'];
$url = $checkout_url.'/afterpay/'.$checkoutID;
$field = "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"referenceNo\"\r\n\r\n$referenceNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"method\"\r\n\r\n$checkoutmethod\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"gbpReferenceNo\"\r\n\r\n$checkoutgbpReferenceNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"amount\"\r\n\r\n$checkoutamount\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"date\"\r\n\r\n$checkoutdate\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"time\"\r\n\r\n$checkouttime\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"shoprefNo\"\r\n\r\n$checkoutshoprefNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"cardNo\"\r\n\r\n$checkoutcardNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"serialID\"\r\n\r\n$checkoutserialID\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"checkoutID\"\r\n\r\n$checkoutID\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--";

$checkoutReturn = AS_Gbprimepay_API::afterpayCheckout("$url", $checkout_configkey, $field, 'POST');

                }else{
                    $transactionStatus = 'Credit Card Installment Payment failed.';
                    logTransaction($gatewayParams['name'], $postData, $transactionStatus);
                }

      }
}
if($paymentType=='W'){
// Qr Wechat
if ( isset( $postData['resultCode'] ) ) {
    if ($postData['resultCode'] == '00') {
                    $checkoutgbpReferenceNo = $postData['gbpReferenceNo'];
                    $transactionStatus = 'QR Wechat Payment Authorized.';
                    checkCbTransID($checkoutgbpReferenceNo);
                    addInvoicePayment(
                        $invoiceId,
                        $checkoutgbpReferenceNo,
                        $paymentAmount,
                        $paymentFee,
                        $gatewayModuleName
                    );
                    logTransaction($gatewayParams['name'], $postData, $transactionStatus);

// checkout_afterpay_url
$checkoutmethod = 'qrwechat';
$checkoutshoprefNo = $ordertxt;
$checkoutserialID = $postData['merchantDefined1'];
$checkoutID = $postData['merchantDefined5'];
$checkoutamount = $postData['amount'];
$checkoutdate = $postData['date'];
$checkouttime = $postData['time'];
$url = $checkout_url.'/afterpay/'.$checkoutID;
$field = "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"referenceNo\"\r\n\r\n$referenceNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"method\"\r\n\r\n$checkoutmethod\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"gbpReferenceNo\"\r\n\r\n$checkoutgbpReferenceNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"amount\"\r\n\r\n$checkoutamount\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"date\"\r\n\r\n$checkoutdate\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"time\"\r\n\r\n$checkouttime\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"shoprefNo\"\r\n\r\n$checkoutshoprefNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"serialID\"\r\n\r\n$checkoutserialID\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"checkoutID\"\r\n\r\n$checkoutID\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--";

$checkoutReturn = AS_Gbprimepay_API::afterpayCheckout("$url", $checkout_configkey, $field, 'POST');

            }else{
                $transactionStatus = 'QR Wechat Payment failed.';
                logTransaction($gatewayParams['name'], $postData, $transactionStatus);
            }

  }
}
if($paymentType=='L'){
// Rabbit Line Pay
if ( isset( $postData['resultCode'] ) ) {
    if ($postData['resultCode'] == '00') {
                    $checkoutgbpReferenceNo = $postData['gbpReferenceNo'];
                    $transactionStatus = 'Rabbit Line Pay Payment Authorized.';
                    checkCbTransID($checkoutgbpReferenceNo);
                    addInvoicePayment(
                        $invoiceId,
                        $checkoutgbpReferenceNo,
                        $paymentAmount,
                        $paymentFee,
                        $gatewayModuleName
                    );
                    logTransaction($gatewayParams['name'], $postData, $transactionStatus);

// checkout_afterpay_url
$checkoutmethod = 'linepay';
$checkoutshoprefNo = $ordertxt;
$checkoutserialID = $postData['merchantDefined1'];
$checkoutID = $postData['merchantDefined5'];
$checkoutamount = $postData['amount'];
$checkoutdate = $postData['date'];
$checkouttime = $postData['time'];
$url = $checkout_url.'/afterpay/'.$checkoutID;
$field = "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"referenceNo\"\r\n\r\n$referenceNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"method\"\r\n\r\n$checkoutmethod\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"gbpReferenceNo\"\r\n\r\n$checkoutgbpReferenceNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"amount\"\r\n\r\n$checkoutamount\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"date\"\r\n\r\n$checkoutdate\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"time\"\r\n\r\n$checkouttime\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"shoprefNo\"\r\n\r\n$checkoutshoprefNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"serialID\"\r\n\r\n$checkoutserialID\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"checkoutID\"\r\n\r\n$checkoutID\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--";

$checkoutReturn = AS_Gbprimepay_API::afterpayCheckout("$url", $checkout_configkey, $field, 'POST');

            }else{
                $transactionStatus = 'Rabbit Line Pay Payment failed.';
                logTransaction($gatewayParams['name'], $postData, $transactionStatus);
            }

  }
}
if($paymentType=='T'){
// TrueMoney Wallet
if ( isset( $postData['resultCode'] ) ) {
    if ($postData['resultCode'] == '00') {
                    $checkoutgbpReferenceNo = $postData['gbpReferenceNo'];
                    $transactionStatus = 'TrueMoney Wallet Payment Authorized.';
                    checkCbTransID($checkoutgbpReferenceNo);
                    addInvoicePayment(
                        $invoiceId,
                        $checkoutgbpReferenceNo,
                        $paymentAmount,
                        $paymentFee,
                        $gatewayModuleName
                    );
                    logTransaction($gatewayParams['name'], $postData, $transactionStatus);

// checkout_afterpay_url
$checkoutmethod = 'truewallet';
$checkoutshoprefNo = $ordertxt;
$checkoutserialID = $postData['merchantDefined1'];
$checkoutID = $postData['merchantDefined5'];
$checkoutamount = $postData['amount'];
$checkoutdate = $postData['date'];
$checkouttime = $postData['time'];
$url = $checkout_url.'/afterpay/'.$checkoutID;
$field = "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"referenceNo\"\r\n\r\n$referenceNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"method\"\r\n\r\n$checkoutmethod\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"gbpReferenceNo\"\r\n\r\n$checkoutgbpReferenceNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"amount\"\r\n\r\n$checkoutamount\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"date\"\r\n\r\n$checkoutdate\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"time\"\r\n\r\n$checkouttime\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"shoprefNo\"\r\n\r\n$checkoutshoprefNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"serialID\"\r\n\r\n$checkoutserialID\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"checkoutID\"\r\n\r\n$checkoutID\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--";

$checkoutReturn = AS_Gbprimepay_API::afterpayCheckout("$url", $checkout_configkey, $field, 'POST');

            }else{
                $transactionStatus = 'TrueMoney Wallet Payment failed.';
                logTransaction($gatewayParams['name'], $postData, $transactionStatus);
            }

  }
}
if($paymentType=='M'){
// Mobile Banking
if ( isset( $postData['resultCode'] ) ) {
    if ($postData['resultCode'] == '00') {
                    $checkoutgbpReferenceNo = $postData['gbpReferenceNo'];
                    $transactionStatus = 'Mobile Banking Payment Authorized.';
                    checkCbTransID($checkoutgbpReferenceNo);
                    addInvoicePayment(
                        $invoiceId,
                        $checkoutgbpReferenceNo,
                        $paymentAmount,
                        $paymentFee,
                        $gatewayModuleName
                    );
                    logTransaction($gatewayParams['name'], $postData, $transactionStatus);

// checkout_afterpay_url
$checkoutmethod = 'mbanking';
$checkoutshoprefNo = $ordertxt;
$checkoutserialID = $postData['merchantDefined1'];
$checkoutID = $postData['merchantDefined5'];
$checkoutamount = $postData['amount'];
$checkoutdate = $postData['date'];
$checkouttime = $postData['time'];
$url = $checkout_url.'/afterpay/'.$checkoutID;
$field = "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"referenceNo\"\r\n\r\n$referenceNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"method\"\r\n\r\n$checkoutmethod\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"gbpReferenceNo\"\r\n\r\n$checkoutgbpReferenceNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"amount\"\r\n\r\n$checkoutamount\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"date\"\r\n\r\n$checkoutdate\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"time\"\r\n\r\n$checkouttime\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"shoprefNo\"\r\n\r\n$checkoutshoprefNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"serialID\"\r\n\r\n$checkoutserialID\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"checkoutID\"\r\n\r\n$checkoutID\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--";

$checkoutReturn = AS_Gbprimepay_API::afterpayCheckout("$url", $checkout_configkey, $field, 'POST');

            }else{
                $transactionStatus = 'Mobile Banking Payment failed.';
                logTransaction($gatewayParams['name'], $postData, $transactionStatus);
            }

  }
}

}
