<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Application\Library\Payment;
class ezeepay {
    public function __construct() {
    }
    public function getToken($orderId, $amount, $userId) {
        $fields = array();
        $fields['SecretKey'] = '$UedS5&3a348unbwe*ng';
        $fields['Customer'] = $userId;
        $fields['TransactionId'] = md5($orderId);
        $fields['MerchantId'] = 'B8155F06-AE8C-426B-80C4-E2636C1BDAE9';
        $fields['MerchantCode'] = 'AFRBAS'; 
        $fields['Description'] = 'Payment For order '.$orderId;
        $fields['Amount'] = $amount;
        $fields['Signature'] = hash_hmac("sha256", $fields['MerchantId'].$fields['Amount'].$fields['Customer'].$fields['TransactionId'], $fields['SecretKey']);
        $tokenResponse = $this->genrateToken($fields);
        $response = json_decode($tokenResponse, TRUE);
        if($response['response'] == 200) {
            $paymentRequest = array();
            $paymentRequest['order_id'] = $orderId;
            $paymentRequest['payment_token_id'] = $response['TokenId'];
            $paymentRequest['transaction_id'] = $fields['TransactionId'];
            $paymentRequest['amount'] = $amount;
            $paymentRequest['user_id'] = $userId;
            $paymentRequest['payment_type'] = 'ezeepay';
            $paymentRequest['status'] = 'payment_awaited';
        }
        return $response;
    }
    public function genrateToken($fields) {
        $parameters = http_build_query($fields);
        $url = 'http://52.35.53.106/gateway/api/requesttoken?'.$parameters;
        // Open connection
        $ch = curl_init(); 
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_HEADER, false); 
        $result=curl_exec($ch);
        curl_close($ch);
        
        return $result;
        
    }
    
    public function savePaymentDetails() {
        
    }
}
