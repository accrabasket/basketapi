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
    var $url;
    var $merchantId;
    var $merchantCode;
    var $secretKey;
    public function __construct() {
        $this->url = 'https://payments.ezipaygh.com/api';
        $this->merchantId = 'f9d844f5-9f1c-4f24-857c-d012a04b5230';
        $this->merchantCode = 'AFRBAS';         
        $this->secretKey = '&3bwa3*ngedSne54$U8u';
    }
    public function getToken($orderId, $amount, $userId, $optional=array())  {
        if($optional['agent'] == 'a') {
            $this->url = 'http://52.40.89.233/aggregator/api/payment';
            $this->merchantId = '8FF5F6D6-E9B6-4120-AB58-515CBD81BC46';
            $this->merchantCode = 'AFRBAS';         
            $this->secretKey = 'K8iuy&hy@p0sb64awPL';            
        }
        $fields = array();
        $fields['SecretKey'] = $this->secretKey;
        $fields['Customer'] = $userId;
        $fields['TransactionId'] = md5($orderId);
        $fields['MerchantId'] = $this->merchantId;
        $fields['MerchantCode'] = $this->merchantCode; 
        $fields['Description'] = 'Payment For order '.$orderId;
        $fields['Amount'] = $amount;
        $fields['Signature'] = hash_hmac("sha256", $fields['MerchantId'].$fields['Amount'].$fields['Customer'].$fields['TransactionId'], $fields['SecretKey']);
        $parameters = http_build_query($fields);
        $genrateTokenUrl = $this->url.'/requesttoken?'.$parameters;  
        if($optional['agent'] == 'a') {
           //do nothing 
        }else {
            $tokenResponse = $this->curlHit($genrateTokenUrl);
        }      
        //$tokenResponse = $this->curlHit($genrateTokenUrl);
        $response = json_decode($tokenResponse, TRUE);
        if($response['StatusCode'] == 200) {
            $paymentRequest = array();
            $paymentRequest['order_id'] = $orderId;
            $paymentRequest['payment_token_id'] = $response['TokenId'];
            $paymentRequest['transaction_id'] = $fields['TransactionId'];
            $paymentRequest['amount'] = $amount;
            $paymentRequest['user_id'] = $userId;
            $paymentRequest['payment_type'] = 'ezeepay';
            $paymentRequest['status'] = '0';
            $paymentRequest['response'] = $tokenResponse;
            $paymentRequest['created_date'] = date('Y-m-d H:i:s');
            $this->savePaymentDetails($paymentRequest);
        }
        if($optional['agent'] == 'a') {
            unset($fields['SecretKey']);
            //unset($fields['MerchantId']);
            unset($fields['MerchantCode']);
            $response['paymentUrl'] = 'https://payments.ezipaygh.com/mobile/checkout?token='.$response['TokenId'].'&returnurl=http://172.104.239.54/basketapi/application/cron/updatepaymentstatus';
            $response['paymentDetails'] = $fields;
        }else {
            $response['paymentUrl'] = 'https://payments.ezipaygh.com/checkout?token='.$response['TokenId'].'&returnurl=http://172.104.239.54/basketapi/application/cron/updatepaymentstatus?agent=w';
        }
        return $response;
    }
    
    public function savePaymentDetails($paymentRequest) {
        $customerModel = new \Application\Model\customerModel();
        $customerModel->savePaymentDetails($paymentRequest);
    }
    
    public function checkPaymentStatus($tokenId) {
        $fields = array();
        $fields['TokenId'] = $tokenId;
        $fields['MerchantId'] = $this->merchantId;
        $parameters = http_build_query($fields);
        $statusUrl = $this->url.'/status?'.$parameters;
        $statusResponse = $this->curlHit($statusUrl);
        $response = json_decode($statusResponse, TRUE);
        
        return $response;
    }
    private function curlHit($url) {
        // Open connection
        $ch = curl_init(); 
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_HEADER, false); 
        $result=curl_exec($ch);
        curl_close($ch);
        
        return $result;
        
    }
}
