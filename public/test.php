<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
class ezeepay {
	var $url;
	var $merchantId;
	var $merchantCode;
	var $secretKey;
	public function __construct() {
		$this->url = 'https://payments.ezeepaygh.com/api/';
		$this->merchantId = 'F9D844F5-9f1C-4F24-857C-D012A04B5230';
		$this->merchantCode = 'AFRBAS';
		$this->secretKey = '&3bwa3*ngedSne54$U8u';
	}
	public function getToken($orderId, $amount, $userId) {
		$fields = array();
		$fields['SecretKey'] = $this->secretKey;
		$fields['Customer'] = $userId;
		$fields['TransactionId'] = md5($orderId);
		$fields['MerchantId'] = $this->merchantId;
		$fields['MerchantCode'] = $this->merchantCode;
		$fields['Description'] = 'Payment For order ' . $orderId;
		$fields['Amount'] = $amount;
		$fields['Signature'] = hash_hmac("sha256", $fields['MerchantId'] . $fields['Amount'] . $fields['Customer'] . $fields['TransactionId'], $fields['SecretKey']);
		$parameters = http_build_query($fields);
		$genrateTokenUrl = $this->url . '/requesttoken?' . $parameters;
		$tokenResponse = $this->curlHit($genrateTokenUrl);
		$response = json_decode($tokenResponse, TRUE);
		print_r($response);
			}

		private function curlHit($url) {
		// Open connection
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
		echo $result = curl_exec($ch);
		curl_close($ch);
		return $result;

	}
}

$boj = new ezeepay();
$boj->getToken(147258,2.0,369);