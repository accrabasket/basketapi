<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Application\Library;
use Application\Model\customerModel;
use Application\Library\common;
use Zend\Mail;
class cron {
    public $customerModel;
    public $commonLib;
    
    public function __construct() {
        $this->customerModel = new customerModel();
        $this->commonLib = new common();
    }
    function sendNotification() {
        $response = array('status' => 'fail', 'msg' => 'Nothing to send ');
        $where = array();
        $where['status']='0';
        $notificationList = $this->customerModel->getNotification($where);
        if(!empty($notificationList)) {
            foreach($notificationList as $notification) {
                $this->setTitle($notification['subject']);
                $this->setMessage($notification['msg']);
                $this->setImage('http://api.androidhive.info/images/minion.jpg');
                $this->setIsBackground(TRUE);        
                $json = $this->getPush();
                if($notification['user_type'] == 'rider'){
                    $riderWhere = array();
                    $riderWhere['id'] = $notification['user_id'];
                    $userDetail = $this->commonLib->riderList($riderWhere);
                }
                if(!empty($userDetail['data'])) {
                    
                    $userData = array_values($userDetail['data']);
                    if(empty($userData[0]['fcm_reg_id'])) {
                        continue;
                    }
                }
                
                $regId = $userData[0]['fcm_reg_id'];//to Do
                $response = $this->send($regId, $json); 
                $notificationResponse = json_decode($response, true);
                $notificationParams = array();
                $notificationParams['status'] = !empty($notificationResponse['success'])?1:2;
                $notificationParams['response'] = $response;
                $whereParams['id'] = $notification['id'];
                $customerModel = new customerModel();
                $customerModel->updateNotification($notificationParams, $whereParams);
            }
        }
        return $response;
    }
    
    public function sendSms(){
        $response = array('status' => 'fail', 'msg' => 'Sms sent.');
        $where = array();
        $where['status']='0';        
        $customerModel = new customerModel();
        $smsList = $customerModel->getSms($where);        
        if(!empty($smsList)) {
            foreach($smsList as $smsDetails) {
                
                $smsData= array();
                $smsData['type'] = 0;
                $smsData['dlr'] = 1;
                $smsData['destination'] = $smsDetails['mobile_number'];
                $smsData['message'] = $smsDetails['message'];
                $smsData['source'] = 'AFFROBASKET';
                
                $response = $this->sendSmsToCustomer($smsData); 
                $smsResponse = explode('|', $response);
                $smsParams = array();
                $smsParams['status'] = ($smsResponse[0]==1701)?1:2;
                $smsParams['response'] = $response;
                
                $whereParams = array();
                $whereParams['id'] = $smsDetails['id'];
                
                $customerModel = new customerModel();
                $customerModel->updateSms($smsParams, $whereParams);
            }
        }
        
        return $response;
    }
    
    public function sendSmsToCustomer($smsData) {
        $smsData['username'] = SMS_GATEWAY_USERNAME;
        $smsData['password'] = SMS_GATEWAY_PASSWORD;
        $url = SMS_GATEWAY_API.'?'.http_build_query($smsData);
        return $this->curlHit($url);
    }
    
    private function curlHit($url){
        $ch = curl_init(); 
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_HEADER, false); 
        $result=curl_exec($ch);
        curl_close($ch);
        
        return $result;        
    }
    public function send($to, $message) {
        $fields = array(
            'to' => $to,
            'data' => $message,
        );
        return $this->sendPushNotification($fields);
    }

    // Sending message to a topic by topic name
    public function sendToTopic($to, $message) {
        $fields = array(
            'to' => '/topics/' . $to,
            'data' => $message,
        );
        return $this->sendPushNotification($fields);
    }

    // sending push message to multiple users by firebase registration ids
    public function sendMultiple($registration_ids, $message) {
        $fields = array(
            'to' => $registration_ids,
            'data' => $message,
        );

        return $this->sendPushNotification($fields);
    }

    // function makes curl request to firebase servers
    private function sendPushNotification($fields) {

        // Set POST variables
        $url = 'https://fcm.googleapis.com/fcm/send';

        $headers = array(
            'Authorization: key=' . FIREBASE_API_KEY,
            'Content-Type: application/json'
        );
        // Open connection
        $ch = curl_init();

        // Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        // Execute post
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }

        // Close connection
        curl_close($ch);

        return $result;
    }
    /*push */
    public function setTitle($title) {
        $this->title = $title;
    }

    public function setMessage($message) {
        $this->message = $message;
    }

    public function setImage($imageUrl) {
        $this->image = $imageUrl;
    }

    public function setPayload($data) {
        $this->data = $data;
    }

    public function setIsBackground($is_background) {
        $this->is_background = $is_background;
    }

    public function getPush() {
        $res = array();
        $res['data']['title'] = $this->title;
        $res['data']['is_background'] = $this->is_background;
        $res['data']['message'] = $this->message;
        $res['data']['image'] = $this->image;
        //$res['data']['payload'] = $this->data;
        $res['data']['timestamp'] = date('Y-m-d G:i:s');
        return $res;
    }    
    
}
