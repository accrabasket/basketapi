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
use Zend\Mail;
use Application\Library\customercurl;
class customer {
    public $customerModel;
    public $customercurlLib;
    public function __construct() {
        $this->customerModel = new customerModel();
        $this->customercurlLib = new customercurl();
    }
    function addtocart($parameters) {
        $response = array('status' => 'fail', 'msg' => 'Nothing to add ');
        $status = TRUE;
        $where = array();
        if(isset($parameters['item_name'])) {
            $params['item_name'] = $parameters['item_name'];
        }
        if(empty($parameters['action'])) {
            $response['msg'] = "Please pass action";
        }
        if($parameters['action'] != 'clearcart') {
            if(isset($parameters['number_of_item'])) {
                $params['number_of_item'] = $parameters['number_of_item'];
            }else {
                $response['msg'] = "Number of item not supplied";
                $status = FALSE;
            }
            if(!empty($parameters['merchant_inventry_id'])) {
                $params['merchant_inventry_id'] = $parameters['merchant_inventry_id'];
            }else {
                $response['msg'] = "Please select product";
                $status = FALSE;
            }
        }
        if(!empty($parameters['user_id'])){
            $where['user_id'] = $params['user_id'] = $parameters['user_id'];
        }
        if(!empty($parameters['guest_user_id'])) {
            $where['guest_user_id'] = $params['guest_user_id'] = $parameters['guest_user_id'];
        }
        if(empty($parameters['guest_user_id']) && empty($parameters['user_id'])) {
            $response['msg'] = "user Id not supplied";
            $status = FALSE;
        }        
        if($status) {
            $itemIntoCartResponse = $this->getItemIntoCart($params);
            
            if(!empty($itemIntoCartResponse['data'])) {
                $itemIntoCart = $itemIntoCartResponse['data'];
                if($parameters['action'] == 'delete') {
                    if(!empty($params['number_of_item']) && $itemIntoCart[$params['merchant_inventry_id']]['number_of_item'] >$params['number_of_item']) {
                       $params['number_of_item'] = $itemIntoCart[$params['merchant_inventry_id']]['number_of_item']- $params['number_of_item'];
                       $parameters['action'] = "update";
                       $where['merchant_inventry_id'] = $params['merchant_inventry_id'];
                    }
                }else if($parameters['action'] == "add"){
                    $params['number_of_item'] = $itemIntoCart[$params['merchant_inventry_id']]['number_of_item']+$params['number_of_item']; 
                    $parameters['action'] = "update";
                    $where['merchant_inventry_id'] = $params['merchant_inventry_id'];
                }
            }
            switch($parameters['action']) {
                case "add":
                    $result = $this->customerModel->addToCart($params);
                   
                    break;
                case "update":
                    $result = $this->customerModel->updateCart($params, $where);
                    break;
                case "delete":
                    $where['merchant_inventry_id'] = $params['merchant_inventry_id'];
                    $result = $this->customerModel->deleteCart($where);
                    break;
                case "clearcart":
                    $result = $this->customerModel->deleteCart($where);
                    break;                
            }
            if(!empty($result)) {
               $response['status'] = "success"; 
               $response['msg'] = "Cart Updated";
            }
        }
        
        return $response;
    }
    
    public function updateCart($parameters) {
        $response = array('status' => 'fail', 'msg' => 'Nothing to update');
        $status = true;
        if(empty($parameters['user_id'])) {
            $status = false;
            $response['msg'] = "Please enter user id";
        }
        if(empty($parameters['guest_user_id'])) {
            $status = false;
            $response['msg'] = "Please enter guest user id";            
        }
        if(!empty($parameters['user_id'])){
            $params['user_id'] = $parameters['user_id'];
        }

        if(!empty($parameters['guest_user_id'])) {
            $where['guest_user_id'] = $parameters['guest_user_id'];
        }
        if($status){
            $result = $this->customerModel->updateCart($params, $where);
            if(!empty($result)) {
                $response['status'] = "success";
                $response['msg'] = 'cart Updated';
            }
        }
        
        return $response;
    }
    public function getItemIntoCart($params) { 
        $response = array('status' => 'fail', 'msg' => 'No Record found');
        $where = array();
        $status = true;
        if(!empty($params['merchant_inventry_id'])) {
            $where['merchant_inventry_id'] = $params['merchant_inventry_id'];
        }
        if(!empty($params['user_id'])){
            $where['user_id'] = $params['user_id'];
        }
        if(!empty($params['guest_user_id'])) {
            $where['guest_user_id'] = $params['guest_user_id'];
        }        
        if(empty($params['guest_user_id']) && empty($params['user_id'])) {
            $response['msg'] = "user Id not supplied";
            $status = FALSE;
        }        
        if($status){
            $data = $this->customerModel->getItemIntoCart($params);
        }
        $cartData = array();
        if(!empty($data)) {
            $cartData = $this->processResult($data, 'merchant_inventry_id');
            if(!empty($cartData)) {
                $params = array();
                $params['merchant_inventry_id'] = array_keys($cartData);
                $productDetails = $this->customercurlLib->getProductByMerchantAttributeId($params);
                $response = array('status' => 'success', 'data' => $cartData,'productDetails'=>$productDetails, 'imageRootPath'=>HTTP_ROOT_PATH);
            }
        }
        
        return $response;
    }
    public function addEditUser($parameters) {
        $response = array('status'=>'fail','msg'=>'User not saved');
        $userParams = array();
        $rules = array();
        $parameters['mobile_number'] = str_replace('+233',"","$parameters[mobile_number]");
        if (!empty($parameters['id'])) {
            $where = array();
            $where['id'] = $userParams['id'] = $parameters['id'];
            if(isset($parameters['email'])) {
                $userParams['email'] = $parameters['email'];
                $rules['email'] = array('type'=>'string', 'is_required'=>true);
            }
            if(isset($parameters['mobile_number'])) {
                $userParams['mobile_number'] = $parameters['mobile_number'];
                $rules['mobile_number'] = array('type'=>'string', 'is_required'=>true);
            }
            $userInputParams = $userParams;
            if(isset($parameters['name'])) {
                $userParams['name'] = $parameters['name'];
                $rules['name'] = array('type'=>'string', 'is_required'=>true);
            }            
            if(!empty($parameters['city_id'])){
               $userParams['city_id'] = $parameters['city_id']; 
            }
            if(!empty($parameters['address'])){
               $userParams['address'] = $parameters['address']; 
            }
            if(!empty($parameters['password'])){
               $userParams['password'] = md5($parameters['password']); 
            }
            if(isset($parameters['status'])) {
                $userParams['status'] = $parameters['status'];
            }
            if(isset($parameters['verified_mobile'])) {
                $userParams['verified_mobile'] = $parameters['verified_mobile'];
            }            
            if(isset($parameters['verified_email'])) {
                $userParams['verified_email'] = $parameters['verified_email'];
            }            
            if(isset($parameters['fcm_reg_id'])) {
                $userParams['fcm_reg_id'] = $parameters['fcm_reg_id'];
            }            
        }else {
            $userParams['email']         =  isset($parameters['email'])?$parameters['email']:'';
            $userParams['mobile_number'] =  isset($parameters['mobile_number'])?$parameters['mobile_number']:'';
            $userInputParams             =  array();
            $userInputParams             =  $userParams;
            $userParams['name']          =  isset($parameters['name'])?$parameters['name']:'';
            $userParams['city_id']       =  isset($parameters['city_id'])?$parameters['city_id']:''; 
            $userParams['address']       =  !empty($parameters['address'])?$parameters['address']:""; 
            $userParams['password']      =  md5($parameters['password']); 
            $userParams['created_date']  =  date('Y-m-d H:i:s'); 
            if(!empty($parameters['fcm_reg_id'])) {
                $userParams['fcm_reg_id'] = $parameters['fcm_reg_id'];
            }            
            $rules['password']           =  array('type'=>'string', 'is_required'=>true);
            $rules['city_id']            =  array('type'=>'numeric', 'is_required'=>true);            
            $rules['mobile_number']      =  array('type'=>'string', 'is_required'=>true);            
            $rules['email']              =  array('type'=>'string', 'is_required'=>true);            
            $rules['name']               =  array('type'=>'string', 'is_required'=>true);                        
        }
        
        $response = $this->isValid($rules, $userParams);        
        if(empty($response)) {
            $response['status'] = 'fail';
            $userDetails = $this->getUserDetail($userInputParams);
            if(!empty($userParams['id'])) {
                if(!empty($userDetails['data'])) {
                    if(count($userDetails['data'])>1) {
                        if(!empty($userParams['email'])) {
                            $response['msg'] = "Email Already in use.";
                        }
                        if(!empty($userParams['mobile_number'])) {
                            $response['msg'] = "mobile number Already in use.";
                        }                    
                        if(!empty($userParams['email']) && !empty($userParams['mobile_number'])) {
                            $response['msg'] = "mobile number/Email Already in use.";
                        }
                    }else {
                       $result = $this->customerModel->updateUser($userParams, $where); 
                       if(!empty($result)) {
                            $response = array('status'=>'success', 'msg'=>"User updated");
                       }
                    }
                }
            }else {
                if(!empty($userDetails['data'])) {
                    $response['msg'] = "mobile number/Email Already in use.";
                }else{
                    $userParams['key'] = md5($userParams['email'].time());
                    $result = $this->customerModel->addUser($userParams);
                    if(!empty($result)) {
                        $generateOtpParams = array();
                        if(empty($parameters['country_code'])) {
                            $parameters['country_code'] = '+233';
                        }
                        $generateOtpParams['mobile_number'] = $parameters['country_code'].$userParams['mobile_number'];
                        $generateOtpParams['otp_type'] = 'register';
                        $otpDetails = $this->generateotp($generateOtpParams);
                        $userDetails = $this->getUserDetail(array('email'=>$parameters['email'], 'verified_email'=>0));
                        $resetParams = array();
                        $resetParams['method'] = 'verifyemail';
                        $resetParams['key'] = $userParams['key'];
                        $parameters['reset_link'] = "http://".$_SERVER["HTTP_HOST"].'/basketapi/application/customer?parameters='.json_encode($resetParams);
                        $parameters['email_template_type'] = 'email_verification';
                        $parameters['otp'] = $otpDetails['data']['otp'];
                        $this->enterDataIntoMailQueue($parameters);
                        $response = array('status'=>'success', 'msg'=>"User created successfully. Otp send on your mobile number $generateOtpParams[mobile_number] and registered email id.",'data'=>$userDetails['data']);
                    }                    
                }
            }
        } 
        
        return $response;
    }
    
    public function getUserDetail($parameters, $optional = array()){
        $response = array('status'=>'fail','msg'=>'No Record Found.');
        $where = array();
        if(!empty($parameters['id'])) {
            $where['id'] = $parameters['id'];
        }
        if(!empty($parameters['name'])) {
            $where['name'] = $parameters['name'];
        }
        if(!empty($parameters['email'])) {
            $where['email'] = $parameters['email'];
        }
        if(isset($parameters['verified_email'])) {
            $where['verified_email'] = $parameters['verified_email'];
        }        
        if(!empty($parameters['password'])) {
            $where['password'] = $parameters['password'];
        }
        if(!empty($parameters['mobile_number'])) {
            $where['mobile_number'] = $parameters['mobile_number'];
        }        
        if(isset($parameters['verified_mobile'])) {
            $where['verified_mobile'] = $parameters['verified_mobile'];
        }
        if(!empty($parameters['status'])) {
            $where['status'] = $parameters['status'];
        }        
        
        
        if(!empty($parameters['pagination'])) {
            $optional['pagination'] = true;
            $optional['page'] = !empty($parameters['page'])?$parameters['page']:1;
        }
        $this->customerModel = new customerModel();
        $result = $this->customerModel->getUserDetail($where, $optional);        
        if(!empty($parameters['count'])) {
            $countOptional = array();
            $countOptional['columns'] = array('count' => new \Zend\Db\Sql\Expression('count(*)'));
            $countOptional['count_row'] = true;
            $customerModel = new customerModel();

            $totalNumberOfUser = $customerModel->getUserDetail($where, $countOptional);        
        }else{
            $totalNumberOfUser['count'] = 1;
        }
        if(!empty($result)) {
            $customerData = $this->processResult($result, 'id');
            if(!empty($customerData)) {
                $response = array('status'=>'success', 'data'=>$customerData, 'totalNumberOfUser'=>$totalNumberOfUser['count']);
            }
        }
        
        return $response;
    }
    
    public function login($parameters) {
        $response = array('status'=>'fail','msg'=>'Invalid credentials');
        $status = true;
        $where = array();
        $params = array();
        if(!empty($parameters['email']) || !empty($parameters['mobile_number'])) {
            if(!empty($parameters['email'])) {
                $where['email'] = $parameters['email'];
            }
            if(!empty($parameters['mobile_number'])) {
                $where['mobile_number'] = isset($parameters['mobile_number']);
            }
        }else{
            $status = false;
            $response = array('status'=>'fail','msg'=>'Email/Mobile not supplied');
        }
        if(!empty($parameters['password'])) {
            $where['password'] = md5($parameters['password']);
        }else{
            $status = false;
            $response = array('status'=>'fail','msg'=>'Password not supplied');
        }
        if(!empty($parameters['fcm_reg_id'])) {
            $params['fcm_reg_id'] = $parameters['fcm_reg_id'];
        }        
        if($status){
            $userDetails = $this->getUserDetail($where);
            if(!empty($userDetails['data'])){
                $response = $userDetails;
                $userIdArr = array_keys($userDetails['data']);
                $params['id'] = $userIdArr[0];
                $this->addEditUser($params);                
            }
        }
        if(!empty($params['id'])) {
            if(empty($response['data'][$params['id']]['verified_mobile']) && !empty($parameters['mobile_number'])) {
                $generateOtpParams = array();
                $generateOtpParams['mobile_number'] = $response['data'][$params['id']]['mobile_number'];
                $generateOtpParams['otp_type'] = 'register';
                $otpDetails = $this->generateotp($generateOtpParams);                
                $response['data'][$params['id']]['id'] = 0;
            }
        }        
        return $response;
    }
    public function isValid($rules, $parameters) {
        $return = array();
        foreach($rules as $key=>$rule) {
            if($rule['type']=='string' && is_string($parameters[$key])) {
                if(!($rule['is_required'] && !empty($parameters[$key]))) {
                    $return = array('status'=>'fail', 'msg'=>$key.' not supplied');
                    break;
                }
            }
            else if($rule['type']=='integer' && is_int($parameters[$key])) {
                if(!($rule['is_required'] && !empty($parameters[$key]))) {
                    $return = array('status'=>'fail', 'msg'=>$key.' not supplied');
                    break;
                }
            }            
            else if($rule['type']=='numeric' && is_numeric($parameters[$key])) {
                if(!($rule['is_required'] && !empty($parameters[$key]))) {
                    $return = array('status'=>'fail', 'msg'=>$key.' not supplied');
                    break;
                }
            }else{
                $return = array('status'=>'fail', 'msg'=>$key.' not '.$rule['type']);
                break;
            }            
        }
        
        return $return;
    }
    
    function addEditDeleveryAddress($parameters) {
        $response = array('status'=>'fail','msg'=>'address not saved');
        $addressParams = array();
        $rules = array();
        if(empty($parameters['user_id'])) {
            $response['msg'] = "user not supplied"; 
            return $response;
        }   
        $addressParams['user_id'] = $parameters['user_id'];
        if (!empty($parameters['id'])) {
            $where = array();
            $where['id'] = $addressParams['id'] = $parameters['id'];
            $where['user_id'] = $parameters['user_id'];
            if(!empty($parameters['address_nickname'])) {
                $addressParams['address_nickname'] = $parameters['address_nickname'];
            }
            if(isset($parameters['contact_name'])) {
                $addressParams['contact_name'] = $parameters['contact_name'];
                $rules['contact_name'] = array('type'=>'string', 'is_required'=>true);
            }
            if(isset($parameters['contact_number'])) {
                $addressParams['contact_number'] = $parameters['contact_number'];
                $rules['contact_number'] = array('type'=>'string', 'is_required'=>true);
            }            
            if(isset($parameters['city_id'])){
               $addressParams['city_id'] = $parameters['city_id']; 
               $rules['city_id'] = array('type'=>'numeric', 'is_required'=>true);
            }
            if(isset($parameters['city_name'])){
               $addressParams['city_name'] = $parameters['city_name']; 
               $rules['city_name'] = array('type'=>'string', 'is_required'=>true);
            }            
            if(!isset($parameters['house_number'])){
               $addressParams['house_number'] = $parameters['house_number']; 
               $rules['house_number'] = array('type'=>'string', 'is_required'=>true);
            }
            if(isset($parameters['street_detail'])){
               $addressParams['street_detail'] = $parameters['street_detail']; 
            }            
            if(isset($parameters['landmark'])){
               $addressParams['landmark'] = $parameters['landmark']; 
            }            
            if(isset($parameters['zipcode'])){
               $addressParams['zipcode'] = $parameters['zipcode']; 
            }
            if(isset($parameters['area'])){
               $addressParams['area'] = $parameters['area']; 
            }
        }else {
            $addressParams['address_nickname'] = isset($parameters['address_nickname'])?$parameters['address_nickname']:'';
            $addressParams['contact_name'] = isset($parameters['contact_name'])?$parameters['contact_name']:'';
            $addressParams['city_id'] = isset($parameters['city_id'])?$parameters['city_id']:''; 
            $addressParams['city_name'] = isset($parameters['city_name'])?$parameters['city_name']:'';
            $addressParams['house_number'] = isset($parameters['house_number'])?$parameters['house_number']:''; 
            if(isset($parameters['street_detail'])){
               $addressParams['street_detail'] = $parameters['street_detail']; 
            }            
            if(isset($parameters['landmark'])){
               $addressParams['landmark'] = $parameters['landmark']; 
            }            
            if(isset($parameters['zipcode'])){
               $addressParams['zipcode'] = $parameters['zipcode']; 
            }
            if(isset($parameters['area'])){
               $addressParams['area'] = $parameters['area']; 
            }  
            $addressParams['created_date'] = date("Y-m-d H:i:s");
            $rules['house_number'] = array('type'=>'string', 'is_required'=>true);            
            $rules['city_name'] = array('type'=>'string', 'is_required'=>true);            
            $rules['city_id'] = array('type'=>'numeric', 'is_required'=>true);
            $rules['contact_name'] = array('type'=>'string', 'is_required'=>true);
        }        
        $response = $this->isValid($rules, $addressParams);
        $data = array();
        if(empty($response)) {
            if(!empty($parameters['id'])) {
                $result = $this->customerModel->updateDeliveryAddress($addressParams, $where);
            }else {
                $result = $this->customerModel->addDeliveryAddress($addressParams);
                $data = array('id'=>$result);
            }
            if(!empty($result)) {
                $response = array('status'=>'success', 'msg'=>"Address Saved", 'data'=>$data);
            }
        }  
        
        return $response;
    }
    
    function getAddressList($parameters) {
        $response = array('status'=>'fail','msg'=>'No record Found');
        $status = true;
        if(!empty($parameters['id'])) {
            $where['id'] = $parameters['id'];
        }        
        if(!empty($parameters['user_id'])) {
            $where['user_id'] = $parameters['user_id'];
        }else{
            $status = false;
            $response['msg'] = "User not supplied";
        }
        if($status) {
            $result = $this->customerModel->getAddressList($where);
            $data = $this->processResult($result, 'id');
            if(!empty($data)) {
                $response = array('status'=>'success', 'data'=>$data);
            }
        }
        return $response;
    }

    function checkout($parameters){
        $cartParams = array();
        $orderDetails = array();
        $status = true;
        $response = array('status'=>'fail');                
        if(!empty($parameters['user_id'])) {
            $cartParams['user_id'] = $parameters['user_id'];
        }else{
            $status = false;
            $response['msg'] = "User not supplied";
        }
        if($status) {
            $cartData = $this->getItemIntoCart($cartParams);
            if(empty($cartData['data'])){
                $response['msg'] = 'No Item found in cart';
                return $response;
            }
            $orderDetails = $this->calculateDiscountAndAmount($cartData);       
            $response = array('status'=>'success','data'=>$orderDetails, 'cartitems'=>$cartData);
        }
        return $response;
    }
    
    function placeOrder($parameters, $userDetails) {
        $optional  = array();
        $optional['agent'] = $parameters['agent'];
        $cartParams = array();
        $orderDetails = array();
        $status = true;
        $response = array('status'=>'fail');                
        if(!empty($parameters['user_id'])) {
            $cartParams['user_id'] = $parameters['user_id'];
        }else{
            $status = false;
            $response['msg'] = "User not supplied";
        }
        if(empty($parameters['shipping_address_id'])) {
            $status = false;
            $response['msg'] = "shipping address not supplied";            
        }else{
            $addressParams = array();
            $addressParams['id'] = $parameters['shipping_address_id'];
            $customerModel = new customerModel();
            $addressList = $customerModel->getAddressList($addressParams);
            $addressDetails = $addressList->current();
            if(empty($addressDetails)) {
                $status = false;
                $response['msg'] = "shipping address not found";  
            }else {            
                $address = $addressDetails['city_name']."<br/> House No. - ".$addressDetails['house_number'].'<br/> Street - '.$addressDetails['street_detail']." ".$addressDetails['zipcode'];
            }
            $restrictedLocationParams = array();
            $restrictedLocationParams['city_id'] = $addressDetails['city_id'];
            $restrictedAreaList = $this->getRestrictedLocationList($restrictedLocationParams);
            if($restrictedAreaList['status'] == 'success') {
                foreach($restrictedAreaList['data'] as $restrictedArea) {
                    $restrictedArea['address'] = strtolower($restrictedArea['address']);
                    
                    $addressDetails['address_nickname'] = strtolower($addressDetails['address_nickname']);
                    $addressDetails['street_detail'] = !empty($addressDetails['street_detail'])?strtolower($addressDetails['street_detail']):'';
                    $addressDetails['area'] = !empty($addressDetails['area'])?strtolower($addressDetails['area']):'';
                    if (strpos($addressDetails['address_nickname'], $restrictedArea['address']) !== false) {
                        $status = false;
                        $response['msg'] = "shipping address Restricted"; 
                    }                
                    if (strpos($addressDetails['street_detail'], $restrictedArea['address']) !== false) {
                        $status = false;
                        $response['msg'] = "shipping address Restricted"; 
                    }          
                    if (strpos($addressDetails['area'], $restrictedArea['address']) !== false) {
                        $status = false;
                        $response['msg'] = "shipping address Restricted"; 
                    }                
                }
            }
        }
        if(empty($parameters['time_slot_id'])) {
            $status = false;
            $response['msg'] = "time_slot_id not supplied";            
        }        
        if(empty($parameters['delivery_date'])) {
            $status = false;
            $response['msg'] = "delivery date not supplied";            
        }        
        if($status) {
            $cartData = $this->getItemIntoCart($cartParams);
            if(empty($cartData['data'])){
                $response['msg'] = 'No Item found in cart';
                
                return $response;
            }
            $orderDetails = $this->calculateDiscountAndAmount($cartData);
        }
        $commonLib = new common();
        $settingData = $commonLib->settinglist(array());
        if(!empty($settingData) && $orderDetails['totalOrderDetails']['payable_amount'] <= $settingData['data']['minimum_order']) {   
            $response['msg'] = 'Minimum order Must Be Greater than '.$settingData['data']['minimum_order'];
            return $response;
        }     
        if(!empty($orderDetails['order'])){
            $this->customerModel->beginTransaction();
            $parentOrderId = 0;
            if(count($orderDetails['order'])>1) {
                $adminOrderId = 'order_P';
                $adminOrderSeq = $this->customerModel->updateOrderSeq($adminOrderId);                 
                $parentOrder = array();
                $parentOrder['user_id'] = $parameters['user_id'];
                $parentOrderId = $parentOrder['order_id'] = $adminOrderId.'_'.$adminOrderSeq[$adminOrderId];
                $parentOrder['store_id'] = 0;
                $parentOrder['merchant_id'] = 0;
                $parentOrder['shipping_address_id'] = $parameters['shipping_address_id'];
                $parentOrder['amount'] = $orderDetails['totalOrderDetails']['amount'];
                $parentOrder['payable_amount'] = $orderDetails['totalOrderDetails']['payable_amount'];
                $parentOrder['discount_amount'] = $orderDetails['totalOrderDetails']['discount_amount'];
                $parentOrder['tax_amount'] = $orderDetails['totalOrderDetails']['tax_amount'];
                $parentOrder['shipping_charges'] = $orderDetails['totalOrderDetails']['delivery_charges'];
                $parentOrder['commission_amount'] = $orderDetails['totalOrderDetails']['commission_amount'];
                $parentOrder['time_slot_id'] = !empty($parameters['time_slot_id'])?$parameters['time_slot_id']:0;
                $parentOrder['delivery_date'] = $parameters['delivery_date'];
                $parentOrder['created_date'] = date('Y-m-d H:i:s');
                $parentOrder['payment_status'] = 'unpaid';
                $result = $this->customerModel->createOrder($parentOrder);
            }
            $timeSlotParams = array();
            $timeSlotParams['id'] = array($parameters['time_slot_id']);
            $customercurlLib = new customercurl();
            $timeSlotList = $customercurlLib->deliveryTimeSlotList($timeSlotParams);
            if(!empty($timeSlotList['data'])) {
                $timeSlot = $timeSlotList['data'][$parameters['time_slot_id']]['start_time_slot'].'-'.$timeSlotList['data'][$parameters['time_slot_id']]['end_time_slot'];
            }                    
            foreach($orderDetails['order'] as $storeId=>$orderDetail) {
                $merchantOrderId = 'order_m'.$storeId;
                $orderSeq = $this->customerModel->updateOrderSeq($merchantOrderId); 
                $orderId = $merchantOrderId.'_'.$orderSeq[$merchantOrderId];
                $orderData = array();
                $orderData['user_id'] = $parameters['user_id'];
                $orderData['time_slot_id'] = !empty($parameters['time_slot_id'])?$parameters['time_slot_id']:0;
                $orderData['delivery_date'] = $parameters['delivery_date'];
                $orderData['order_id'] = $orderId;
                $orderData['parent_order_id'] = $parentOrderId;
                $orderData['store_id'] = $storeId;
                $orderData['merchant_id'] = $orderDetail['merchant_id'];
                $orderData['shipping_address_id'] = $parameters['shipping_address_id'];
                $orderData['amount'] = $orderDetail['amount'];
                $orderData['payable_amount'] = $orderDetail['amount']-$orderDetail['discount_amount'];
                $orderData['discount_amount'] = $orderDetail['discount_amount'];
                $orderData['commission_amount'] = $orderDetail['commission_amount'];
                $orderData['shipping_charges'] = $orderDetail['shipping_charges'];
                $orderData['payment_status'] = 'unpaid';                    
                $orderData['created_date'] = date('Y-m-d H:i:s');
                $result = $this->customerModel->createOrder($orderData);                
                if(!empty($result)) {
                    $notificationData = array();
                    $notifyOrderId = $orderId;
                    if(!empty($parentOrderId)) {
                        $notifyOrderId = $parentOrderId;
                    }
                    $notificationData['order_id'] = $notifyOrderId;
                    $notificationData['user_type'] = 'admin';
                    $notificationData['user_id'] = 0;
                    $this->sentNotification('notification_for_order_placed_for_admin', $notificationData);
                    $this->sentSms('notification_for_order_placed_for_merchant', $notificationData);
                    $notificationData['user_id'] = $orderDetail['merchant_id'];
                    $notificationData['user_type'] = 'merchant';
                    $this->sentNotification('notification_for_order_placed_for_merchant', $notificationData);
                    $this->sentSms('notification_for_order_placed_for_merchant', $notificationData);
                    
                    $emailParams = array();
                    $emailParams['email'] = $userDetails['email'];
                    $emailParams['address'] = $address;
                    //$emailParams['landmark'] = $landmark;
                    $emailParams['order_id'] = $notifyOrderId;
                    $emailParams['name'] = $userDetails['name'];
                    $emailParams['email_template_type'] = 'invoice';
                    $emailParams['item_data'] = $orderDetails['itemWiseOrderDetails'];
                    $emailParams['totalOrderDetails'] = $orderDetails['totalOrderDetails'];
                    $emailParams['delivery_date'] = $parameters['delivery_date'];
                    $emailParams['time_slot'] = $timeSlot;
                    $this->enterDataIntoMailQueue($emailParams);  
                    
                    if(!empty($orderDetails['merchantItemWiseOrderDetails'][$storeId])) {
                        foreach($orderDetails['merchantItemWiseOrderDetails'][$storeId] as $merchantProductId=>$orderItems) {
                            $orderItems['merchant_product_id'] = $merchantProductId;
                            $orderItems['order_id'] = $orderId;
                            $orderItems['product_dump'] = json_encode($orderItems['product_dump']);
                            $orderItems['status'] = 'active';
                            $orderItems['created_by'] = $orderData['user_id'];
                            $result = $this->insertProductIntoOrderItem($orderItems);
                            if(empty($result)) {
                                $this->customerModel->rollback();
                                return $response;
                            }
                        }
                    }
                }else{
                    $this->customerModel->rollback();
                    return $response;                        
                }
            }
            if($result) {
                $this->customerModel->deleteCart(array('user_id'=>$parameters['user_id']));
                $this->customerModel->commit();
                $response['status'] = 'success';
                $response['msg'] = 'order placed successfully.';
                if(!empty($parentOrderId)) {
                    $response['data']['order_id'] = $parentOrderId;
                }else{
                    $response['data']['order_id'] = $orderId;
                }
                if(!empty($parameters['payment_type']) && $parameters['payment_type'] == 'ezeepay') {
                    $paymentObj = new Payment\ezeepay();
                    $amount = !empty($parentOrder['payable_amount'])?$parentOrder['payable_amount']:$orderData['payable_amount'];
                    $response['data']['tokenResponse'] = $paymentObj->getToken($response['data']['order_id'], $amount, $parameters['user_id'], $optional);
                }                

            }else {
                $response['msg'] = 'order Not Placed';
            }
            
        }
        
        return $response;
    }
    function insertProductIntoOrderItem($orderItems) {
        return $this->customerModel->insertProductIntoOrderItem($orderItems);
    }
    
    function calculateDiscountAndAmount($data) {
        $order = array();
        $merchantItemWisePriceDetails = array();
        $itemWisePriceDetails = array();
        $totalOrderDetails = array();
        $totalOrderDetails['amount'] = 0.00;
        $totalOrderDetails['discount_amount'] = 0.00;
        $totalOrderDetails['commission_amount'] = 0.00;
        $totalOrderDetails['tax_amount'] = 0.00;   
        foreach($data['data'] as $key=>$item) {
            if(!empty($data['productDetails']['data'][$key])) {
                $discount = 0.00;
                $productDetails = $data['productDetails']['data'][$key];
                $productImageData = !empty($data['productDetails']['productImageData'][$productDetails['product_id']])?$data['productDetails']['productImageData'][$productDetails['product_id']]:array();
                $amount = $productDetails['price']*$item['number_of_item'];                                
                if(empty($order[$productDetails['store_id']])) {
                    $order[$productDetails['store_id']] = array();
                    $order[$productDetails['store_id']]['amount'] = $amount;
                    $order[$productDetails['store_id']]['discount_amount'] = 0.00;
                    $order[$productDetails['store_id']]['commission_amount'] = 0.00;
                    $order[$productDetails['store_id']]['tax_amount'] = 0.00;
                }else {
                    $order[$productDetails['store_id']]['amount']+=$amount; 
                }
                $order[$productDetails['store_id']]['merchant_id'] = $productDetails['merchant_id'];
                $merchantItemWisePriceDetails[$productDetails['store_id']][$key]['amount'] = $amount; 
                $merchantItemWisePriceDetails[$productDetails['store_id']][$key]['number_of_item'] = $item['number_of_item']; 
                $itemDetails = array();
                $itemDetails['product_details'] = $productDetails; 
                $itemDetails['product_image_data'] = $productImageData;  
                $merchantItemWisePriceDetails[$productDetails['store_id']][$key]['product_dump'] = $itemDetails;
                $itemWisePriceDetails[$key]['amount'] = $amount; 
                $itemWisePriceDetails[$key]['number_of_item'] = $item['number_of_item'];
                $itemWisePriceDetails[$key]['product_dump'] = $itemDetails;
                $totalOrderDetails['amount'] = $totalOrderDetails['amount']+$amount;
                if(!empty($productDetails['discount_value'])) {
                    if($productDetails['discount_type'] != 'flat') {
                        $discount = $amount*$productDetails['discount_value']/100;
                    }else {
                        $discount = $productDetails['discount_value']*$item['number_of_item'];
                    }
                }else if(!empty($productDetails['default_discount_value'])){
                    if($productDetails['default_discount_type'] != 'flat') {
                        $discount = $amount*$productDetails['default_discount_value']/100;
                    }else {
                        $discount = $productDetails['default_discount_value']*$item['number_of_item'];
                    }                    
                }
                $order[$productDetails['store_id']]['discount_amount'] += $discount;
                $merchantItemWisePriceDetails[$productDetails['store_id']][$key]['discount_amount'] = $discount;
                $itemWisePriceDetails[$key]['discount_amount'] = $discount;
                $totalOrderDetails['discount_amount'] = $totalOrderDetails['discount_amount']+$discount;
                if(!empty($productDetails['commission_value'])) {
                    if($productDetails['commission_type'] != 'flat') {
                        $commissionAmount = $amount*$productDetails['commission_value']/100;
                    }else {
                        $commissionAmount = $productDetails['commission_value']*$item['number_of_item'];
                    }
                }                
                $order[$productDetails['store_id']]['commission_amount']+=$commissionAmount;
                $merchantItemWisePriceDetails[$productDetails['store_id']][$key]['commission_amount'] = $commissionAmount;
                $itemWisePriceDetails[$key]['commission_amount'] = $commissionAmount;
                $totalOrderDetails['commission_amount'] = $totalOrderDetails['commission_amount']+$commissionAmount;
                
                $merchantItemWisePriceDetails[$productDetails['store_id']][$key]['tax_amount'] = 0.00;
                $itemWisePriceDetails[$key]['tax_amount'] = 0.00;
                $totalOrderDetails['payable_amount'] = $totalOrderDetails['amount']-$totalOrderDetails['discount_amount']+$totalOrderDetails['tax_amount'];
                
            }
        }
        $orderDetails['totalOrderDetails']['delivery_charges'] = 0.00;
        $shippingCharges = 0.00;
        $commonLib = new common();
        $settingData = $commonLib->settinglist(array());        
        foreach($order as $storeId=>$value) {
            if(!empty($settingData) && $value['amount'] <= $settingData['data']['free_delivery']) {  
                $shippingCharges += $settingData['data']['shipping_charges'];
                $value['amount'] += $settingData['data']['shipping_charges'];
                $order[$storeId] = $value;
                $order[$storeId]['shipping_charges'] = $settingData['data']['shipping_charges'];
            }
        }
        if(!empty($settingData) && $totalOrderDetails['payable_amount'] <= $settingData['data']['free_delivery'] && $totalOrderDetails['payable_amount'] >= $settingData['data']['minimum_order']) {
            $totalOrderDetails['payable_amount'] += $shippingCharges;
            $totalOrderDetails['delivery_charges'] = $shippingCharges;
        }        
        $response = array('totalOrderDetails'=>$totalOrderDetails,'order'=>$order, 'merchantItemWiseOrderDetails'=>$merchantItemWisePriceDetails, 'itemWiseOrderDetails'=>$itemWisePriceDetails);
        
        return $response;
    }

    function orderList($parameters) {
        $status = true;
        $response = array('status'=>'fail', 'msg'=>'No Record Found');                
        $orderWhere = array();
        if(!empty($parameters['user_id'])) {
            $orderWhere['user_id'] = $parameters['user_id'];
        }
        if(!empty($parameters['order_id'])) {
            $orderWhere['order_id'] = $parameters['order_id'];
        }
        if(!empty($parameters['store_id'])) {
            $orderWhere['store_id'] = $parameters['store_id'];
        } 
        if(!empty($parameters['merchant_id'])) {
            $orderWhere['merchant_id'] = $parameters['merchant_id'];
        }          
        if(!empty($parameters['order_status'])){
            $orderWhere['order_status'] = $parameters['order_status'];
            if($parameters['order_status'] == 'current_order'){
               $orderWhere['order_status'] = array('order_placed', 'ready_to_dispatch', 'dispatched', 'return_request'); 
            }else if($parameters['order_status'] == 'past_order') {
                $orderWhere['order_status'] = array('completed','returned','cancelled');
            }
        }
        $optional = array();
        if(!empty($parameters['pagination'])) {
            $optional['pagination'] = true;
            $optional['page'] = !empty($parameters['page'])?$parameters['page']:1;
        }   
        if(!empty($parameters['short_by'])) {
            $opptional['short_by'] = $parameters['short_by'];
            $optional['short_type'] = $orderWhere['short_type'] = $parameters['short_type'] == 'asc'? 'ASC' : 'DESC';
        }

        $orderList = $this->customerModel->orderList($orderWhere, $optional);
        
        $countOptional = array();
        $countOptional['columns'] = array('count' => new \Zend\Db\Sql\Expression('count(*)'));
        $countOptional['count_row'] = true;
        $customerModel = new customerModel();
        $totalNumberOfOrders = $customerModel->orderList($orderWhere, $countOptional);
        $orderListData = $this->prepareOrderList($orderList, $orderWhere);
        if(!empty($orderListData['order_list'])) {
            $response = array('status'=>'success', 'data'=>$orderListData['order_list'],'shipping_address_list'=>$orderListData['shipping_address_list'],'user_details'=>$orderListData['user_details'], 'time_slot_list'=>$orderListData['time_slot_list'],'order_assignment_list'=>$orderListData['order_assignment_list'],'rider_list'=>$orderListData['rider_list'], 'imageRootPath'=>HTTP_ROOT_PATH, 'totalNumberOfOrder'=>$totalNumberOfOrders['count']);
        }
        
        return $response;
    }
    
    function prepareOrderList($orderData, $optional){
        $orderListByOrderId = array();
        $orderDataList = array();
        $timeSlotList = array();
        $orderDataList['shipping_address_list'] = array();
        $orderDataList['time_slot_list'] = array();
        $orderDataList['user_details'] = array();
        if(!empty($orderData)) {            
            foreach($orderData as $orders) {
                $orderListByOrderId[$orders['order_id']] = $orders;
                $shippingAddressList[$orders['shipping_address_id']] = $orders['shipping_address_id'];
                $userIds[$orders['user_id']] = $orders['user_id'];
                if(!empty($orders['time_slot_id'])) {
                    $timeSlotList[$orders['time_slot_id']] = $orders['time_slot_id'];
                }
            }
            if(!empty($orderListByOrderId)) {
                $orderIds = array_keys($orderListByOrderId);
                $orderItemWhere = array();
                $orderItemWhere['order_id'] = $orderIds;
                $orderItems = $this->customerModel->getOrderItem($orderItemWhere,$optional);
                $customerModel = new customerModel();
                $assignedRiderWithOrder = $customerModel->getOrderAssignment($orderItemWhere);
                
                $orderDataList['order_assignment_list'] = $this->processResult($assignedRiderWithOrder, 'order_id');
                $orderDataList['rider_list'] = array();
                if(!empty($orderDataList['order_assignment_list'])) {
                    $orderAssignedToRider = $this->processResult($orderDataList['order_assignment_list'], 'rider_id');
                    $riderList = array_keys($orderAssignedToRider);
                    $riderWhere = array();
                    $riderWhere['rider_id'] = $riderList;
                    $customercurlLib = new customercurl();
                    $riderListData = $customercurlLib->riderList($riderWhere);
                    if(!empty($riderListData['data'])) {
                        $orderDataList['rider_list'] = $riderListData['data'];
                    }
                }
                $userParams['id'] = array_keys($userIds);
                $userDetails = $this->getUserDetail($userParams);
                $orderDataList['user_details'] = $userDetails['data'];
                
                $addressParams['id'] = array_keys($shippingAddressList);
                $customerModel = new customerModel();
                $addressList = $customerModel->getAddressList($addressParams);
                $orderDataList['shipping_address_list'] = $this->processResult($addressList, 'id');                
                                
                if(!empty($timeSlotList)) {
                    $timeSlotParams = array();
                    $timeSlotParams['id'] = array_keys($timeSlotList);
                    $timeSlotList = $this->customercurlLib->deliveryTimeSlotList($timeSlotParams);
                    if(!empty($timeSlotList['data'])) {
                        $orderDataList['time_slot_list'] = $timeSlotList['data'];
                    }
                }
                
                if(!empty($orderItems)) {
                    foreach($orderItems as $orderItem){
                        if(!empty($orderItem['product_dump'])) {
                            $orderItem['product_dump'] = json_decode($orderItem['product_dump']);
                        }
                        if(!empty($optional['user_id']) && !empty($orderListByOrderId[$orderItem['order_id']]['parent_order_id'])) {
                            $orderDataList['order_list'][$orderListByOrderId[$orderItem['order_id']]['parent_order_id']]['order_details'] = isset($orderListByOrderId[$orderListByOrderId[$orderItem['order_id']]['parent_order_id']])?$orderListByOrderId[$orderListByOrderId[$orderItem['order_id']]['parent_order_id']]:'';
                            $orderDataList['order_list'][$orderListByOrderId[$orderItem['order_id']]['parent_order_id']]['orderitem'][$orderItem['merchant_product_id']] = $orderItem; 
                        }else{
                            $orderDataList['order_list'][$orderItem['order_id']]['order_details'] = $orderListByOrderId[$orderItem['order_id']];
                            $orderDataList['order_list'][$orderItem['order_id']]['orderitem'][$orderItem['merchant_product_id']] = $orderItem; 
                        }
                    }
                }
                
            }
        }
        return $orderDataList;
    }
    
    function assignOrderToRider($parameters) {
        $response = array('status'=>'fail', 'msg'=>'Nothing to update.');
        $orderWhere = array();
        $optional = array();
        $status = true;
        $params = array();
        if(!empty($parameters['rider_id'])) {
            $params['rider_id'] = $parameters['rider_id'];
        }else{
            $status = false;
            $response['msg'] = "Rider not supplied";
        }        
        if(!empty($parameters['order_id'])) {
            $params['order_id'] = $orderWhere['order_id'] = $parameters['order_id'];
        }else{
            $status = false;
            $response['msg'] = "order not supplied";
        }        
        $orderWhere['status'] = 1;
        if($status) {
            $orderList = $this->customerModel->assignedOrderToRider($orderWhere, $optional); 
            if(!empty($orderList)) {
                $orderDetails = $orderList->current();
                if(!empty($orderDetails)) {
                    if($orderDetails['rider_id'] == $parameters['rider_id']) {
                        $status = false;
                        $response['msg'] = "Already Assigned to this Riders.";
                    }else {
                        $unAssignOrderParams = array();
                        $unAssignOrderParams['rider_id'] = $orderDetails['rider_id'];
                        $unAssignOrderParams['order_id'] = $parameters['order_id'];
                        $unAssignOrderParams['status'] = 0;
                        
                        $orderList = $this->unassignOrder($unAssignOrderParams); 
                    }
                }else{
                    $customerModel = new customerModel();
                    $orderList = $customerModel->orderList($orderWhere);
                    $orderDetails = $orderList->current();
                }
            }else{
                $customerModel = new customerModel();
                $orderList = $customerModel->orderList($orderWhere);
                $orderDetails = $orderList->current();
            }
            if($status) {
                $params['status'] = 1;
                $params['created_date'] = date('Y-m-d H:i:s');
                $result = $this->customerModel->assignOrder($params);
                if(!empty($result)) {
                    $merchantNotification = 1;
                    $orderWhere = array();
                    $orderWhere['order_id'] = $parameters['order_id'];
                    
                    $orderParams = array();
                    $orderParams['order_status'] = 'assigned_to_rider';
                    $orderParams['updated_date'] = date('Y-m-d H:i:s');
                    $customerModel = new customerModel();
                    $customerModel->updateOrder($orderParams, $orderWhere);
                    
                    $params['user_type'] = 'rider';
                    $this->sentNotification('order_assignment_to_rider', $params);
                    if($merchantNotification) {
                        $merchantNotificationParams = array();
                        $merchantNotificationParams['user_type'] = 'merchant';
                        $merchantNotificationParams['user_id'] = $orderDetails['merchant_id'];
                        $merchantNotificationParams['order_id'] = $params['order_id'];
                    
                        $this->sentNotification('order_assignment_to_rider_for_merchant', $merchantNotificationParams);
                    }
                    $response = array('status'=>'success', 'msg'=>'order assigned to rider.');
                }
            }
        }
        
        return $response;
    }
    
    function sentSms($sms, $parameters) {
        $smsTemplateWhere = array();
        $smsTemplateWhere['name'] = $sms;
        $templateDetails = $this->getNotificationTemplate($smsTemplateWhere, 'sms_template');
        $params = array();
        
        $replaceData = array();
        if(!empty($parameters['order_id'])) {
            $replaceData['order_id'] = $parameters['order_id'];
        }
        if(!empty($parameters['minute'])) {
            $replaceData['minute'] = $parameters['minute'];
        }        
        if(isset($parameters['reason'])) {
            $replaceData['reason'] = $parameters['reason'];
        }        
        $params['message'] = $this->prepareEmailBody($templateDetails['body'], $replaceData);         
        if(!empty($parameters['user_type']) && in_array($parameters['user_type'], array('admin', 'merchant'))) {
            $customercurlLib = new customercurl();
            if(!empty($parameters['user_id'])) {
                $optional['id'] = $parameters['user_id'];
            }
            $optional['user_type'] = $parameters['user_type'];
            
            $userList = $customercurlLib->getMarchantList($optional);
        }
        $params['status'] = '0';
        $params['response'] = '';
        $params['created_date'] = date("Y-m-d H:i:s");      
        $customerModel = new customerModel();
        if(!empty($userList)){
            foreach($userList as $user) {
                $params['mobile_number'] = $user['phone_number'];
                $customerModel->enterDataIntoMailQueue($params, array('queue_type'=>'sms_queue'));        
            }
        }
    }
    
    function sentNotification($notification, $parameters) {
        $notificationTemplateWhere = array();
        $notificationTemplateWhere['name'] = $notification;
        $templateDetails = $this->getNotificationTemplate($notificationTemplateWhere);
        $params = array();
        
        $replaceData = array();
        if(!empty($parameters['order_id'])) {
            $replaceData['order_id'] = $parameters['order_id'];
        }
        if(!empty($parameters['minute'])) {
            $replaceData['minute'] = $parameters['minute'];
        }        
        if(isset($parameters['reason'])) {
            $replaceData['reason'] = $parameters['reason'];
        }   
        $params['msg'] = $this->prepareEmailBody($templateDetails['body'], $replaceData); 
        $params['subject'] = $templateDetails['subject'];
        $params['user_id'] = isset($parameters['user_id'])?$parameters['user_id']:$parameters['rider_id'];
        $params['user_type'] = $parameters['user_type'];
        $params['status'] = '0';
        $params['response'] = '';
        $params['created_date'] = date("Y-m-d H:i:s");      
        $customerModel = new customerModel();
        return $customerModel->enterDataIntoMailQueue($params, array('queue_type'=>'notification_queue'));
    }
    
    function getNotificationTemplate($parameters, $type='notification_template') {
        $params = array();
        if(!empty($parameters['name'])) {
            $params['name'] = $parameters['name'];
        }       
        $optional = array();
        $optional['template_type'] = $type;
        $customerModel = new customerModel();
        $result = $customerModel->getTemplate($params, $optional);
        return $result;
    }
                    
    function unassignOrder($parameters) {
        $response = array('status'=>'fail', 'msg'=>'Nothing to update.');
        $status = true;
        if(!empty($parameters['rider_id'])) {
            $where['rider_id'] = $parameters['rider_id'];
        }else{
            $status = false;
            $response['msg'] = "Rider not supplied";
        }        
        if(!empty($parameters['order_id'])) {
            $where['order_id'] = $parameters['order_id'];
        }else{
            $status = false;
            $response['msg'] = "order not supplied";
        }        
        $params = array();
        if(isset($parameters['status'])) {
            $params['status'] = $parameters['status'];
        }
        if($status) {
            $customerModel = new customerModel();
            $return = $customerModel->updateOrderAssignment($params, $where);
            if(!empty($return)) {
                $orderWhere = array();
                $orderWhere['order_id'] = $parameters['order_id'];

                $orderParams = array();
                $orderParams['order_status'] = 'order_placed';
                $orderParams['updated_date'] = date('Y-m-d H:i:s');
                $customerModel = new customerModel();
                $customerModel->updateOrder($orderParams, $orderWhere);                
                $response = array('status'=>'success', 'msg'=>'data updated', 'data'=>array('order_id'=>$parameters['order_id']));
            }
        }
        
        return $response;
    }
    function updateOrderByRider($parameters) {
        $response = array('status'=>'fail', 'msg'=>'Nothing to update.');
        $status = true;
        if(!empty($parameters['rider_id'])) {
            $where['user_id'] = $parameters['rider_id'];
        }else{
            $status = false;
            $response['msg'] = "Rider not supplied";
        }        
        if(!empty($parameters['order_id'])) {
            $where['order_id'] = $parameters['order_id'];
        }else{
            $status = false;
            $response['msg'] = "order not supplied";
        }
        if(empty($parameters['order_status'])) {
            $status = false;
            $response['msg'] = "order status not supplied";
        }        
        
        if($parameters['role'] == 'rider') {
            $where['order_status'] = array('order_placed','ready_to_dispatch','assigned_to_rider','dispatched');
        }
        if($status) {
            $orderList = $this->customerModel->assignedOrderToRider($where); 
            if(!empty($orderList)) {
                $orderDetails = $orderList->current();
                if(!empty($orderDetails)) {
                    $orderWhere = array();
                    $orderWhere['order_id'] = $orderDetails['order_id'];
                    $params = array();
                    $params['order_status'] = $parameters['order_status'];
                    $params['updated_date'] = date('Y-m-d H:i:s');
                    $customerModel = new customerModel();
                    $result = $customerModel->updateOrder($params, $orderWhere);
                    if(!empty($result)) {
                        if($params['order_status'] == 'completed') {
                            $this->updateInventry($orderWhere);
                        
                            $userDetails = $this->getUserDetailsById($orderDetails['user_id']);

                            $addressParams = array();
                            $addressParams['id'] = $orderDetails['shipping_address_id'];
                            $customerModel = new customerModel();
                            $addressList = $customerModel->getAddressList($addressParams);
                            $addressDetails = $addressList->current();
                            $address = '';
                            if(!empty($addressDetails)) {          
                                $address = $addressDetails['city_name']."<br/> House No. - ".$addressDetails['house_number'].'<br/> Street - '.$addressDetails['street_detail']." ".$addressDetails['zipcode'];
                            }

                            $orderItemWhere = array();
                            $orderItemWhere['order_id'] = $parameters['order_id'];
                            $orderItemOptional = array();
                            $customerModel = new customerModel();
                            $orderItems = $customerModel->getOrderItem($orderItemWhere, $orderItemOptional);
                            $orderItemData = $this->processResult($orderItems, '', false, false, 'product_dump');
                            
                            $merchantOptional = array();
                            $customercurlLib = new customercurl();
                            $merchantOptional['id'] = $orderDetails['merchant_id'];
                            $merchantList = $customercurlLib->getMarchantList($optional);                            
                            $merchantDetails = $merchantList[$orderDetails['merchant_id']];
                            
                            $emailParams = array();
                            $emailParams['email'] = $userDetails['email'];
                            $emailParams['cc'] = $merchantDetails['email'];
                            $emailParams['address'] = $address;
                            //$emailParams['landmark'] = $landmark;                
                            $emailParams['order_id'] = $parameters['order_id'];
                            $emailParams['name'] = $userDetails['name'];
                            $emailParams['email_template_type'] = 'delivered_by_rider';
                            $emailParams['item_data'] = $orderItemData;
                            $emailParams['totalOrderDetails'] = $orderDetails;
                            $emailParams['delivery_date'] = $orderDetails['delivery_date'];
                            //$emailParams['time_slot'] = $timeSlot;
                            $this->enterDataIntoMailQueue($emailParams);                        
                        }
                        $merchantNotificationParams = array();
                        $merchantNotificationParams['user_type'] = 'merchant';
                        $merchantNotificationParams['user_id'] = $orderDetails['merchant_id'];
                        $merchantNotificationParams['order_id'] = $orderDetails['order_id'];
                        $merchantNotificationParams['reason'] = isset($parameters['reason'])?$parameters['reason']:'';

                        $this->sentNotification('notification_for_order_'.$parameters['order_status'].'_for_merchant', $merchantNotificationParams);        
                        
                        $adminNotificationParams = array();
                        $adminNotificationParams['user_type'] = 'admin';
                        $adminNotificationParams['user_id'] = 0;
                        $adminNotificationParams['order_id'] = $orderDetails['order_id'];
                        $adminNotificationParams['reason'] = isset($parameters['reason'])?$parameters['reason']:'';
                        
                        $this->sentNotification('notification_for_order_'.$parameters['order_status'].'_for_merchant', $adminNotificationParams);                                
                        
                        $customerNotificationParams = array();
                        $customerNotificationParams['user_type'] = 'customer';
                        $customerNotificationParams['user_id'] = $orderDetails['user_id'];
                        $customerNotificationParams['order_id'] = $orderDetails['order_id'];
                        $customerNotificationParams['reason'] = isset($parameters['reason'])?$parameters['reason']:'';
                        
                        $this->sentNotification('notification_for_order_'.$parameters['order_status'].'_for_merchant', $customerNotificationParams);                        
                        if($orderDetails['payment_status']=='unpaid' && $parameters['order_status']=='completed') {
                            $ledgerParams = $this->prepareDataToInsertIntoLedger($orderDetails);
                            $this->insertIntoLedger($ledgerParams);
                        }
                        $response = array('status'=>'success', 'msg'=>'order updated successfully.');
                    }
                }else {
                    $response['msg']='Order not assigned with this rider.';
                }
            }
        }
        
       return $response;
    }
    
    function updateInventry($parameters) {
        $customerModel = new customerModel();
        $orderItems = $customerModel->getOrderItem($parameters);
        
        foreach($orderItems as $item){
            $where = array();
            $params = array();
            $where['id'] = $item['merchant_product_id'];
            $params['number_of_item'] = $item['number_of_item'];
            $response = $this->customercurlLib->updateInventry($params, $where);
        }
    }
    
    function updateOrderStatus($parameters) {
        $status = true;
        $response = array('status'=>'fail', 'msg'=>'Nothing to update.');  
        $orderWhere = array();
        $orderParams = array();
        if(!empty($parameters['order_status'])) {
            $orderParams['order_status'] = $parameters['order_status'];
        }else {
            $status = false;
            $response['msg'] = 'Please pass order status';                            
        }
        $orderWhere['order_status'] = array('order_placed', 'ready_to_dispatch', 'assigned_to_rider', 'dispatched');
        if(!empty($parameters['role']) && $parameters['role'] == 'merchant') {
            if(!empty($parameters['merchant_id'])) {
                $orderWhere['merchant_id'] = $parameters['merchant_id'];
            }else{
                $status = false;
                $response['msg'] = 'Please Pass merchant id';                
            }     
            $orderWhere['order_status'] = array('order_placed');
        }else if(empty($parameters['user_id'])) {
            $status = false;
            $response['msg'] = 'Please Pass user id';
        }
        
        if(!empty($parameters['user_id'])) {
            $orderWhere['user_id'] = $parameters['user_id'];
        }
        if(!empty($parameters['order_id'])) {
            $orderWhere['order_id'] = $parameters['order_id'];
        }else {
            $status = false;
             $response['msg'] = 'Please order id not Supplied';
        }
        if(!empty($parameters['store_id'])) {
            $orderWhere['store_id'] = $parameters['store_id'];
        }                         
        if($status) {
            $orderParams['updated_date'] = date('Y-m-d H:i:s');
            $return = $this->customerModel->updateOrder($orderParams, $orderWhere);        
            if(!empty($return)) {                
                        $adminNotificationParams = array();
                        $adminNotificationParams['user_type'] = 'admin';
                        $adminNotificationParams['user_id'] = 0;
                        $adminNotificationParams['order_id'] = $parameters['order_id'];
                        
                        $this->sentNotification('notification_for_order_'.$parameters['order_status'].'_for_admin', $adminNotificationParams);                
                        if(empty($orderWhere['merchant_id'])) {
                            $merchantNotificationParams = array();
                            $merchantNotificationParams['user_type'] = 'merchant';
                            $merchantNotificationParams['user_id'] = $orderWhere['merchant_id'];
                            $merchantNotificationParams['order_id'] = $parameters['order_id'];

                            $this->sentNotification('notification_for_order_'.$parameters['order_status'].'_for_merchant', $merchantNotificationParams);                            
                        }
                $response = array('status'=>'success', 'msg'=>'Record updated', 'data'=>$orderWhere);  
            }
        }
        
        return $response;
    }
    function getAssignedOrderToRider($parameters) {
        $status = true;
        $response = array('status'=>'fail', 'msg'=>'No Record Found');                
        $orderWhere = array();
        if(!empty($parameters['user_id'])) {
            $orderWhere['user_id'] = $parameters['user_id'];
        }else{
            $status = false;
            $response['msg'] = "Rider not supplied";
        } 
        if(!empty($parameters['order_status'])){
            $orderWhere['order_status'] = $parameters['order_status'];
            if($parameters['order_status'] == 'current_order'){
               $orderWhere['order_status'] = array('assigned_to_rider'); 
            }else if($parameters['order_status'] == 'past_order') {
                $orderWhere['order_status'] = array('completed','returned','cancelled', 'return_request');
            }
        }
        $optional = array();
        if(!empty($parameters['pagination'])) {
            $optional['pagination'] = true;
            $optional['page'] = !empty($parameters['page'])?$parameters['page']:1;
        }
        $orderList = $this->customerModel->assignedOrderToRider($orderWhere, $optional);
        $timeSlotList = $this->customercurlLib->deliveryTimeSlotList(array());        
        $countOptional = array();
        $countOptional['columns'] = array('count' => new \Zend\Db\Sql\Expression('count(*)'));
        $countOptional['count_row'] = true;
        $customerModel = new customerModel();
        $totalNumberOfOrders = $customerModel->assignedOrderToRider($orderWhere, $countOptional);        
        $orderListData = $this->prepareOrderForRiders($orderList);
        if(!empty($orderListData)) {
            $orderListData['time_slot_list'] = $timeSlotList['data'];
            $response = array('status'=>'success', 'data'=>$orderListData, 'imageRootPath'=>HTTP_ROOT_PATH, 'totalNumberOfOrder'=>$totalNumberOfOrders['count']);
        }
        return $response;
    }
    
    function prepareOrderForRiders($orderData){
        $shippingAddressList = array();
        $storeList = array();
        $orderItemList = array();
        $orderList = array();
        $userListData = array();
        foreach($orderData as $orders) {
            $shippingAddressList[$orders['shipping_address_id']] = $orders['shipping_address_id'];
            $storeList[$orders['store_id']] = $orders['store_id'];
            $orderList[$orders['order_id']] = $orders;
            $userIds[$orders['user_id']] = $orders['user_id'];
        }
        
        if(!empty($shippingAddressList)) {
            $addressParams['id'] = array_keys($shippingAddressList);
            $addressList = $this->customerModel->getAddressList($addressParams);
            $shippingAddressList = $this->processResult($addressList, 'id');
            
            $userWhere['id'] = $userIds;
            $userList = $this->getUserDetail($userWhere);
            if(!empty($userList['data'])) {
                $userListData = $userList['data'];
            }
            $orderParams['order_id'] = array_keys($orderList);
            $orderItems = $this->customerModel->getOrderItem($orderParams);
            $orderItemList = $this->processResult($orderItems, 'order_id', true);
            
            $storeParams['id'] = array_keys($storeList);
            $storeList = $this->customercurlLib->getStoreListById($storeParams);
        }
        if(!empty($shippingAddressList)) {
            return array('orderList'=>$orderList,'shippingAddressList'=>$shippingAddressList,'userList'=>$userListData, 'orderItemList'=>$orderItemList, 'storeList'=>$storeList['data']);
        }
        
        return false;
    }
    function processResult($result,$dataKey='', $multipleRowOnKey = false, $additionOfValue=false, $json_decode_column='') {
        $data = array();        
        if(!empty($result)) {
            foreach ($result as $key => $value) {
                if(!empty($dataKey)){
                    if($multipleRowOnKey) {
                        $data[$value[$dataKey]][] = $value;
                    }else {
                        $data[$value[$dataKey]] = $value;
                    }
                }else {
                    if(!empty($value["$json_decode_column"]) && !empty($json_decode_column)) {
                        $value["$json_decode_column"] = json_decode($value["$json_decode_column"], true);
                    }
                    $data[] = $value;
                }
                if($additionOfValue) {
                    if(empty($data['totalCount'])) {
                        $data['totalCount'] = 0;
                    }
                    $data['totalCount'] = $data['totalCount']+$value['count'];
                }                
            }    
        }
        
        return $data;
    } 
    
    public function generateotp($parameters) {
        $response = array('status'=>'fail','msg'=>'Invalid details');
        $status = true;
        $where = array();
        $countryCode = isset($parameters['country_code'])?$parameters['country_code']:'';
        if(!empty($parameters['mobile_number'])) {
            $where['mobile_number'] = $countryCode.$parameters['mobile_number'];
        }else{
            $status = false;
            $response = array('status'=>'fail','msg'=>'Mobile number not supplied');
        }
        if(!empty($parameters['otp_type'])) {
            $where['otp_type'] = $parameters['otp_type'];
            /*if($where['otp_type'] == 'register') {
                $whereUserParams['mobile_number'] = $parameters['mobile_number'];
                $userData = $this->getUserDetail($whereUserParams);
                if(!empty($userData['data'])) {
                    $status = false;
                    $response = array('status'=>'fail','msg'=>'Mobile Number Already registered.');                    
                }
            }*/
        }else{
            $status = false;
            $response = array('status'=>'fail','msg'=>'Otp type is not supplied');
        }        
        
        if($status){
            $result = $this->customerModel->deleteOtp($where);
            $expireTime = date('Y-m-d H:i:s', strtotime("+".OTP_EXPIRE_TIME." minutes"));
            $randomNumber = mt_rand(1000, 9999);
           // $randomNumber = '1234';
            $smsQueueData = array();
            $otpData = array();
            $otpData['mobile_number'] = $smsQueueData['mobile_number'] = $countryCode.$parameters['mobile_number'];
            $otpData['otp_type'] = $parameters['otp_type'];
            $otpData['user_id'] = isset($parameters['user_id'])?$parameters['user_id']:0;
            $otpData['otp'] = $randomNumber;
            //$otpData['otp'] = '1234';
            $otpData['expiry_date'] = $expireTime;
            $otpResponse = $this->customerModel->insertIntoOtpMaster($otpData);
            if(!empty($otpResponse)) {
                $smsQueueData['message'] = $randomNumber.' is your OTP for '.$otpData['otp_type'].' Enter this in the box provided within 15 minuts.';
                $result = $this->customerModel->smsqueue($smsQueueData);
            }
            if(!empty($result)){
                $response = array('status'=>'success','msg'=>'Otp send', 'data'=>$otpData);
            }
        }
        return $response;
    }
    
    function verifyEmail($parameters) {
        $response = array('status' => 'fail', 'msg' => 'Link not valid');
        $status = true;
        if (!empty($parameters['key'])) {
            $where['key'] = $parameters['key'];
        } else {
            $status = false;
            $response = array('status' => 'fail', 'msg' => 'user_auth not supplied');
        }   
        if($status) {
            $userDetails = $this->getUserDetail(array('key'=>$parameters['key'], 'verified_email'=>0));
            if(!empty($userDetails['status'] == 'success')) {
                $userValues = array_values($userDetails['data']);
                $addeditUserParams = array();
                $addeditUserParams['id'] = $userValues[0]['id'];
                $addeditUserParams['verified_email'] = 1;
                $addeditUserParams['status'] = 1;
                $response = $this->addEditUser($addeditUserParams);        
            }
        }
        
        return $response;
    } 
    
    function verifyotp($parameters) {
        $response = array('status' => 'fail', 'msg' => 'Otp not valid');
        $status = true;
        $where = array();
        $countryCode = '';
        if(!empty($parameters['country_code'])) {
            $countryCode = $parameters['country_code'];
        }
        if (!empty($parameters['mobile_number'])) {
            $where['mobile_number'] = $countryCode.$parameters['mobile_number'];
        } else {
            $status = false;
            $response = array('status' => 'fail', 'msg' => 'Mobile number not supplied');
        }
        
        if (!empty($parameters['otp'])) {
            $where['otp'] = $parameters['otp'];
        } else {
            $status = false;
            $response = array('status' => 'fail', 'msg' => 'Otp not supplied');
        }
        if(!empty($parameters['otp_type'])) {
            $where['otp_type'] = $parameters['otp_type'];
        }else{
            $status = false;
            $response = array('status'=>'fail','msg'=>'Otp type is not supplied');
        }   
        $where['expiry_date'] = date("Y-m-d H:i:s");
        if($status){
            $result = $this->customerModel->verifyOtp($where);
            $params = array();
            $deleteOtpWhere['mobile_number'] = $params['mobile_number'] = $parameters['mobile_number'];
            $deleteOtpWhere['otp_type'] = $params['key_for'] =  $parameters['otp_type'];            
            $this->customerModel->deleteOtp($deleteOtpWhere);            
            if (!empty($result['count'])) {
                $this->customerModel->deleteUserAuth($params);
                $userDetails = $this->getUserDetail(array('mobile_number'=>$parameters['mobile_number'], 'verified_mobile'=>0));
                $userValues = array_values($userDetails['data']);
                $addeditUserParams = array();
                $addeditUserParams['id'] = $userValues[0]['id'];
                $addeditUserParams['verified_mobile'] = 1;
                $addeditUserParams['status'] = 1;
                $this->addEditUser($addeditUserParams);                 
                $params['auth_key'] = md5($parameters['mobile_number'].  time());
                $result = $this->customerModel->saveuserauthlink($params);
                if(!empty($result)){
                    $response = array('status' => 'success', 'msg' => 'Otp verify','data'=>array('auth_key'=>$params['auth_key']));
                }
            }
        }
        return $response;
    }
    
    function forgetpassword($parameters) {
        $response = array('status' => 'fail', 'msg' => 'User does not exist');
        $status = true;
        $data = array();        
        if (!empty($parameters['email'])) {
            $data['email'] = $parameters['email'];
        } else {
            $status = false;
            $response = array('status' => 'fail', 'msg' => 'email not supplied');
        }
        if($status){
            $userDetails = $this->getUserDetail($data);
            if(!empty($userDetails['data'])){
                $replaceData = array();
                $userValues = array_values($userDetails['data']);
                $key = md5($userValues[0]['id'].time());
                
                $replaceData['name'] =  $userValues[0]['name'];
                $replaceData['reset_link'] = FRONT_END_PATH.'application/index/changepassword?key='.$key;
                
                
                $customerModel = new customerModel();
                $templateWhere = array();
                $templateWhere['type'] = 'forget_password';
                $templateData = $customerModel->getTemplate($templateWhere);
                
                $mailQuquedata = array();
                $emailBody = $this->prepareEmailBody($templateData['body'], $replaceData);
                $mailQuquedata['body'] = $emailBody;
                $mailQuquedata['from_email_id'] =  FROM_EMAIL;
                $mailQuquedata['subject'] =  $templateData['name'];
                $mailQuquedata['to_email_id'] = $userValues[0]['email'];
                $customerModel = new customerModel();
                $result = $customerModel->enterDataIntoMailQueue($mailQuquedata);
                if(!empty($result)){
                    
                    $userParams['key'] = $key;
                    $userParams['updated_date'] = date('Y-m-d H:i:s');
                    $where = array('id'=>$userValues[0]['id']);
                    $customerModel = new customerModel();
                    $result = $customerModel->updateUser($userParams, $where); 
                    $response = array('status' => 'success', 'msg' => 'Forget password link send .');
                }
                
            }
            
            
            
//            $authResponse = $this->validateAuthKey($parameters);
//            if (!empty($authResponse['data'])) {
//                $params = array();
//                $params['mobile_number'] = $authResponse['data']['mobile_number'];
//                $userDetails = $this->getUserDetail($params);
//                if(!empty($userDetails['data'])){
//                    $userParams = array();
//                    $where = array();
//                    $userParams['password'] = md5($data['password']);
//                    $userParams['updated_date'] = date('Y-m-d H:i:s');
//                    $userDetails = array_values($userDetails['data']);
//                    $where['id'] = $userDetails[0]['id'];
//                    $result = $this->customerModel->updateUser($userParams, $where);
//                    if (!empty($result)){
//                        $response = array('status' => 'success', 'msg' => 'password changed');
//                    }
//                }
//            }else{
//                $response = $authResponse;
//            }
        }
        return $response;
    }
    function prepareEmailBody($body, $replaceData) {
        if(!empty($replaceData)) {
            foreach($replaceData as $key=>$value) {
                $body = str_replace("{{".$key."}}", $value, $body);
            }
        }
        
        return $body;
    }
    function validateAuthKey($parameters) {
        $response = array('status'=>'fail', 'msg'=>'Atuhentication Failed');
        $status = true;
        if (!empty($parameters['auth_key'])) {
            $authWhere['auth_key'] = $parameters['auth_key'];
        } else {
            $status = false;
            $response = array('status' => 'fail', 'msg' => 'Auth key not supplied');
        }
        if (!empty($parameters['auth_for'])) {
            $authWhere['key_for'] = $parameters['auth_for'];
        } else {
            $status = false;
            $response = array('status' => 'fail', 'msg' => 'Auth for not supplied');
        }
        if($status) {
            $result = $this->customerModel->checkauthkey($authWhere);
            if(!empty($result)) {
                $where = array();
                $where['mobile_number'] = $result['mobile_number'];
                $where['key_for'] = $result['key_for'];
                $this->customerModel->deleteUserAuth($where);                
                $response = array('status'=>'success', 'data'=>$result);
            }
        }
        
        return $response;
    }
    
    function getMinute($expiry_date) {
        $datetime1 = strtotime($expiry_date);
        $datetime2 = time();
        $interval = $datetime2 - $datetime1;
        $minutes = round($interval / 60);
        return $minutes;
    }
    
    function changepassword($parameters) {
        $response = array('status' => 'fail', 'msg' => 'password not change');
        $status = true;
        $data = array();
        $where = array();
        if (!empty($parameters['user_id'])) {
            $where['id'] = $parameters['user_id'];
        }else{
            $status = false;
            $response = array('status' => 'fail', 'msg' => 'User id not supplied');
        }
        
        if (!empty($parameters['new_password'])) {
            $data['password'] = md5($parameters['new_password']);
        } else {
            $status = false;
            $response = array('status' => 'fail', 'msg' => 'new password not supplied');
        }
        
        if (empty($parameters['password'])) {
            $status = false;
            $response = array('status' => 'fail', 'msg' => 'old password not supplied');
        }else{
            $where['password'] = md5($parameters['password']);
        }
        
        if ($status) {
            $data['updated_date'] = date('Y-m-d H:i:s');
            $result = $this->customerModel->changepassword($data, $where);
            if (!empty($result)) {
                $response = array('status' => 'success', 'msg' => 'password changed');
            }
        }
        return $response;
    }
    
    function changepasswordByAuthKey($parameters) {
        $response = array('status' => 'fail', 'msg' => 'password not changed');
        $status = true;
        $data = array();
        $where = array();
        if (!empty($parameters['auth_key'])) {
            $where['key'] = $parameters['auth_key'];
        }else{
            $status = false;
            $response = array('status' => 'fail', 'msg' => 'auth key not supplied');
        }
        
        if (!empty($parameters['new_password'])) {
            $data['password'] = md5($parameters['new_password']);
        } else {
            $status = false;
            $response = array('status' => 'fail', 'msg' => 'new password not supplied');
        }
        
        
        if ($status) {
            $data['key'] = '';
            $data['updated_date'] = date('Y-m-d H:i:s');
            $result = $this->customerModel->changepassword($data, $where);
            if (!empty($result)) {
                $response = array('status' => 'success', 'msg' => 'password changed');
            }
        }
        return $response;
    }    
    function updatePaymentStatus($parameters) {
        $where = array();
        $response = array('status'=>'fail', 'msg'=>'Transaction Failed');
        if(!empty($parameters['TransactionId'])) {
            $where['transaction_id'] = $parameters['TransactionId'];
            $paymentDetails = $this->customerModel->getPaymentDetails($where);
            $paymentDetail = $paymentDetails->current();
            $paymentObj = new Payment\ezeepay();
            $paymentStatus = $paymentObj->checkPaymentStatus($paymentDetail['payment_token_id']);
        }
        if(!empty($paymentStatus) && $paymentDetail['status'] !=1) {
            $customerModel = new customerModel();
            $params = array();
            $params['updated_date'] = date('Y-m-d H:i:s');
            $params['status'] = '2';
            if($paymentStatus['Message']=='SUCCESSFUL') {
                $params['status'] = '1';
                $customerModelObj = new customerModel();
                $orderData = array();
                $orderData['payment_status'] = 'paid';
                $orderData['updated_date'] = date('Y-m-d H:i:s');
                $orderWhere = array();
                $orderWhere['order_id'] = $paymentDetail['order_id'];
                $orderWhere['parent_order_id'] = $paymentDetail['order_id'];
                $status = $customerModelObj->updateOrderPayment($orderData, $orderWhere);
                if($status) {
                    $this->updateLedger($paymentDetail['order_id']);
                }
                $response['status'] = 'success';
                $response['msg'] = 'Transaction Successfull';
            }
            $customerModel->updatePaymentDetails($params, $where);
        }

        if($response['status']=='success'){
            $response['msg'] = "<html><body style='text-align:center'>We have received your payment. <br/> Your Order id is - $paymentDetail[order_id]</body></html>";
        }else{
            $response['msg']= "<html><body style='text-align:center'><p>Your payment has failed. <br/> Your Order id is - $paymentDetail[order_id] </body> </html>";
        }   
        return $response; 
    }
    
    function ledgersummery($parameters) {
        $where = array();
        $response = array('status'=>'fail', 'msg'=>'No record found');
        if(!empty($parameters['merchant_id'])) {
            $where['merchant_id'] = $parameters['merchant_id'];
            $where['start_date'] = $parameters['start_date'];
            $where['end_date'] = $parameters['end_date'];
            $merchantTotalRevenu = array();
            $data = array();
            $totalRevenu = $this->customerModel->getTotalRevenu($where);
//          
            $ledgerSummery = $this->customerModel->getOrderWiseLedger($where);
            
            if(!empty($ledgerSummery)) {
                foreach ($ledgerSummery as $key => $value) {
                    $data[] = $value;
                }
            }
            
            $data['total_summery'] = $totalRevenu;
            $response = array('status'=>'success', 'data'=>$data);
          
        }else{
            $response = array('status'=>'fail', 'msg'=>'Please select merchant');
        }
        
        return $response; 
    }   
    
    function updateLedger($orderId) {
        $parameters = array();
        $customerModel = new customerModel();        
        $parameters['order_id'] = $orderId;
        $orderList = $customerModel->orderList($parameters);
        if(!empty($orderList)) { 
            foreach($orderList as $order) {
                $ledgerParams = $this->prepareDataToInsertIntoLedger($order);
                $this->insertIntoLedger($ledgerParams);
            }
        }
    }
    
    function prepareDataToInsertIntoLedger($order){
        $ledgerParams = array();
        $ledgerParams['order_id'] = !empty($order['order_id'])?$order['order_id']:0;
        $ledgerParams['merchant_id'] = $order['merchant_id'];
        $ledgerParams['total_amount'] = !empty($order['payable_amount'])?$order['payable_amount']:0;
        $ledgerParams['discount_amount'] = !empty($order['discount_amount'])?$order['discount_amount']:0;
        $ledgerParams['commission_amount'] = !empty($order['commission_amount'])?$order['commission_amount']:0;
        $ledgerParams['type'] = !empty($order['type'])?$order['type']:'credit';
        $ledgerParams['merchant_amount'] = !empty($order['merchant_amount'])?$order['merchant_amount']:($order['amount']-$order['commission_amount']);
        
        return $ledgerParams;
    }
    function insertIntoLedger($params) {
        $customerModel = new customerModel();  
        $params['created_date'] = date('Y-m-d H:i:s');
        $status = $customerModel->insertIntoLedger($params);
        if($status){
            $this->updateLedgerSummary($params);
        }
    }
    
    function updateLedgerSummary($params) {
        $customerModel = new customerModel();
        $where = array();
        $data = array();
        if($params['type']=='debit'){
            $data['total_revenue']         = -$params['total_amount'];
            $data['total_commission']      = -$params['commission_amount'];
            $data['total_discount']        = -$params['discount_amount'];
            $data['total_merchant_amount'] = -$params['merchant_amount'];
        }else{
            $data['total_revenue']         = $params['total_amount'];
            $data['total_commission']      = $params['commission_amount'];
            $data['total_discount']        = $params['discount_amount'];
            $data['total_merchant_amount'] = $params['merchant_amount'];
        }
        $data['updated_date']          = date('Y-m-d H:i:s');
        
        $where['merchant_id'] = $params['merchant_id'];
        $customerModel->updateLedgerSummary($data, $where);
    }
    
    function PayToMerchant($parameters) {
        $response = array('status' => 'fail', 'msg' => 'Nothing To update');
        $status = true;  
        $data = array();
        if(!empty($parameters['merchant_id'])) {
            $data['merchant_id'] = $parameters['merchant_id'];
        } else {
            $status = false;
            $response['msg']= 'Please provide merchant Id';
        }        
        if (!empty($parameters['amount']) && $parameters['amount']>0) {
            $data['merchant_amount'] = $parameters['amount'];
        } else {
            $status = false;
            $response['msg'] ='Please provide amount';
        }        
        $data['type'] = 'debit';
        if($status){
            $ledgerData = $this->prepareDataToInsertIntoLedger($data);
            $this->insertIntoLedger($ledgerData);
            $response = array('status' => 'success', 'msg' => 'Account Updated.');
        }
        
        return $response;
    }
 function getCustomerSalesDetails($parameters) {
        $whereParams = array();
        if(!empty($parameters['start_date'])) {
            $whereParams['start_date'] = $parameters['start_date'].' 00:00:00';
        }
        if(!empty($parameters['end_date'])) {
            $whereParams['end_date'] = $parameters['end_date'].' 23:59:59';
        }
        $optional['date_formate'] = "%Y-%m-%d";
        if(!empty($parameters['report'])) {
            if($parameters['report'] == 'monthly') {
                $optional['date_formate'] = "%Y-%m";
            }
        }
        if(!empty($parameters['merchant_id'])) {
            $whereParams['merchant_id'] = $parameters['merchant_id'];
        }
        $allCustomer = $this->customerModel->getCustomerCount($whereParams, $optional);
        $customerByDate = $this->processResult($allCustomer, 'created_date');
        $allOrders = $this->customerModel->getOrderCount($whereParams, $optional);        
        $allOrderByDate = $this->processResult($allOrders, 'created_date', false, true);
        $totalOrder = isset($allOrderByDate['totalCount'])?$allOrderByDate['totalCount']:0;
        unset($allOrderByDate['totalCount']);
        $whereParams['order_status'] = 'completed';
        $completedOrders = $this->customerModel->getOrderCount($whereParams, $optional);        
        $completedOrderByDate = $this->processResult($completedOrders, 'created_date', false, true);
        $totalConfirmedOrder = isset($completedOrderByDate['totalCount'])?$completedOrderByDate['totalCount']:0;
        unset($completedOrderByDate['totalCount']);
       
        
        $whereParams['order_status'] = 'cancelled';
        $cancelledOrders = $this->customerModel->getOrderCount($whereParams, $optional);        
        $cancelledOrderByDate = $this->processResult($cancelledOrders, 'created_date', false, true);
        $totalCancelledOrder = isset($cancelledOrderByDate['totalCount'])?$cancelledOrderByDate['totalCount']:0;
        unset($cancelledOrderByDate['totalCount']); 
        
        $data = array('customerByDate'=>$customerByDate, 'allOrderByDate'=>$allOrderByDate, 'completedOrderByDate'=>$completedOrderByDate, 'cancelledOrderByDate'=>$cancelledOrderByDate, 'totalOrder'=>$totalOrder, 'totalConfirmedOrder'=>$totalConfirmedOrder, 'totalCancelledOrder'=>$totalCancelledOrder);
        $response = array('status'=>'success', 'data'=>$data);
        
        return $response;
    }
    
    function getCustomerCount() {
        $where = array();
        $customer = $this->customerModel->getCustomerCount($where);
        $customerData = $customer->current();
        $data = array('totalNumberOfCustomer'=>$customerData['count']);
        
        return array('status'=>'success', 'data'=>$data);
    }  
    
    function getNotification($parameters) {
        $where = array();
        $optional = array();
        if(!empty($parameters['user_type'])){
            $where['user_type'] = $parameters['user_type'];
        }
        if(!empty($parameters['user_id'])){
            $where['user_id'] = $parameters['user_id'];
        }        
        if(empty($parameters['all_notification'])) {
            $where['updated_date'] = null;
        }
        if(!empty($parameters['pagination'])) {
            $optional['pagination'] = true;
            $optional['page'] = !empty($parameters['page'])?$parameters['page']:1;
        }        
        $notificationResponse = $this->customerModel->getNotification($where, $optional);
        
        $notificationList = $this->processResult($notificationResponse);
        $optional = array();
        $optional['count'] = 1;
        $notificationCountResponse = $this->customerModel->getNotification($where, $optional);
        $notificationCount = $notificationCountResponse->current();
        $totalNotification = $notificationCount['count'];
        return array('status'=>'success', 'data'=>$notificationList, 'totalRecord'=>$totalNotification);        
    }
    
    function updateNotification($parameters) {
        $where = array();
        if(!empty($parameters['user_type'])){
            $where['user_type'] = $parameters['user_type'];
        }
        if(!empty($parameters['user_id'])){
            $where['user_id'] = $parameters['user_id'];
        }        
        if(empty($parameters['all_notification'])) {
            $where['updated_date'] = null;
        }
        $params['updated_date'] = date('Y-m-d H:i:s');
        $notificationResponse = $this->customerModel->updateNotification($params, $where);
        return array('status'=>'success', 'data'=>$notificationResponse);                
    }
    
    function sendManualNotificationByRider($parameters) {
        $orderWhere = array();
        $status = true;
        $response = array('status'=>'fail', 'msg'=>'notification not sent');
        if(!empty($parameters['order_id'])) {
          $orderWhere['order_id'] = $parameters['order_id'];
        }else{
            $status = false;
            $response['msg'] = "order not supplied";
        }
        if(!empty($parameters['rider_id'])) {
            $orderWhere['user_id'] = $parameters['rider_id'];
        }else{
            $status = false;
            $response['msg'] = "Rider not supplied";
        }       
        if(!empty($parameters['minute'])) {
          $minute = $parameters['minute'];
        }else{
            $status = false;
            $response['msg'] = "Minute not supplied";
        }        
        $orderWhere['order_status'] = 'dispatched';
        $orderList = $this->customerModel->assignedOrderToRider($orderWhere); 
        if($status &&!empty($orderList)) {
            $orderDetails = $orderList->current();
        
            $customerNotificationParams = array();
            $customerNotificationParams['user_type'] = 'customer';
            $customerNotificationParams['user_id'] = $orderDetails['user_id'];
            $customerNotificationParams['order_id'] = $orderDetails['order_id'];
            $customerNotificationParams['minute'] = $minute;
 
            $this->sentNotification('order_delivered_in', $customerNotificationParams);                                
            $response = array('status'=>'success', 'msg'=>'notification sent to customer');
        }
        return $response;
    }
    
    function addEditRestrictedLocation($parameters) {
        $params = array();
        $rule = array();
        if(!empty($parameters['id'])){
            $where = array('id'=>$parameters['id']);
            if(isset($parameters['address'])) {
                $params['address'] = $parameters['address'];
                $rule['address'] = array('type'=>'string', 'is_required'=>true);                
            }
            if(isset($parameters['country_id'])) {
                $params['country_id'] = (int)$parameters['country_id'];
                $rule['country_id'] = array('type'=>'integer', 'is_required'=>true);
            }
            if(isset($parameters['city_id'])) {
                $params['city_id'] = (int)$parameters['city_id'];
                $rule['city_id'] = array('type'=>'integer', 'is_required'=>true);
            }
        }else{
            $params['address'] = $parameters['address'];
            $params['country_id'] = (int)$parameters['country_id'];
            $params['city_id'] = (int)$parameters['city_id'];
            $rule['address'] = array('type'=>'string', 'is_required'=>true);
            $rule['country_id'] = array('type'=>'integer', 'is_required'=>true);
            $rule['city_id'] = array('type'=>'integer', 'is_required'=>true);
        }
        $response = $this->isValid($rule, $params);
        if(empty($response)){
            $response = array('status' => 'fail', 'msg' => 'No Record Saved ');
            if(!empty($parameters['id'])){
                $result = $this->customerModel->updateRestrictedLocation($params, $where);
            }else {
                $params['created_date'] = date('Y-m-d H:i:s');
                $result = $this->customerModel->addRestrictedLocation($params);
            }
            if(!empty($result)){
                $response = array('status'=>'success','msg'=>'Record Saved');
            }            
        }
        
        return $response;
        
    }
    
    public function getRestrictedLocationList($parameters) {
        $response = array('status' => 'fail', 'msg' => 'No record found ');
        $optional = array();
        if(!empty($parameters['id'])) {
            $optional['id'] = $parameters['id'];
        }         
        if(!empty($parameters['columns'])) {
            $optional['columns'] = $parameters['columns'];
        }    
        if(!empty($parameters['pagination'])) {
            $optional['pagination'] = $parameters['pagination'];
            $optional['page'] = !empty($parameters['page'])?$parameters['page']:1;
        }
        if(!empty($parameters['address'])) {
            $optional['address'] = $parameters['address'];
        }
        if(!empty($parameters['city_id'])) {
            $optional['city_id'] = $parameters['city_id'];
        }        
        if(isset($parameters['active'])) {
            $optional['active'] = $parameters['active'];
        }        
        $customerModel = new customerModel();
        $result = $customerModel->restrictedLocationList($optional);
        if (!empty($result)) {
            $data = array();
            foreach ($result as $key => $value) {
                $data[$value['id']] = $value;
            }
            $response = array('status' => 'success', 'data' => $data);
        }
        return $response;        
    }    
    
    public function deleteRestrictedLocation($parameters) {
        $response = array('status' => 'fail', 'msg' => 'Nothing to delete');
        $where = array();
        if(!empty($parameters['id'])) {
            $where['id'] = $parameters['id'];
        }      
        $customerModel = new customerModel();
        $result = $customerModel->deleteRestrictedLocation($where);        
        if(!empty($result)) {
            $response = array('status' => 'success', 'msg' => 'Location deleted.');    
        }
        
        return $response;
    }
    
    public function deleteshippingaddress($parameters) {
        $response = array('status' => 'fail', 'msg' => 'Nothing to delete');
        $status = true;
        $where = array();
        if(!empty($parameters['id'])) {
            $where['id'] = $parameters['id'];
        }else{
            $response['msg'] = "address id not supplied";
            $status = FALSE;            
        }      
        if(!empty($parameters['user_id'])) {
            $where['user_id'] = $parameters['user_id'];
        }else{
            $response['msg'] = "user id id not supplied";
            $status = FALSE;            
        }        
        $customerModel = new customerModel();
        $result = $customerModel->deleteShippingAddress($where);        
        if(!empty($result)) {
            $response = array('status' => 'success', 'msg' => 'Shipping address deleted.');    
        }        
        
        return $response;
    }
    function getUserDetailsById($userId) {
        $customerModel = new \Application\Model\customerModel();
        $whereParams = array('id'=>$userId);
        $optional = array();
        $optional['count_row'] = 1;
        return $customerModel->getUserDetail($whereParams, $optional);
    }
    function enterDataIntoMailQueue($parameters) {
        
        $customerModel = new customerModel();
        $templateWhere = array();
        $templateWhere['type'] = $parameters['email_template_type'];
        $templateData = $customerModel->getTemplate($templateWhere);        
        
        $mailQuquedata = array();
        $replaceData['name'] = $parameters['name'];
        if(isset($parameters['address'])){
            $replaceData['address'] = $parameters['address'];
        }
        $replaceData['landmark'] = '';
        if(isset($parameters['landmark'])){
            $replaceData['landmark'] = $parameters['landmark'];
        }
        if(isset($parameters['order_id'])){
            $replaceData['order_id'] = $parameters['order_id'];
        }
        if(isset($parameters['delivery_date'])){
            $replaceData['delivery_date'] = $parameters['delivery_date'];
        }   
        if(!empty($parameters['time_slot'])) {
            $replaceData['time_slot'] = $parameters['time_slot'];
        }else {
            $replaceData['time_slot'] = '';
        }
        if(isset($parameters['item_data'])){
            $testDetails = "";
            $discount = 0;
            foreach($parameters['item_data'] as $testData) {
                /*$testDetails .= "<tr>";
                $testDetails .= "<td>";
                $testDetails .= $testData['product_dump']['product_details']['test_name'];
                $testDetails .= "</td>";
                
                $testDetails .= "<td>";
                $testDetails .= 1;
                $testDetails .= "</td>";
                
                $testDetails .= "<td>";
                $testDetails .= $testData['product_dump']['product_details']['price'];;
                $testDetails .= "</td> </tr>";*/
                if($templateWhere['type'] == 'report') {
                    $testData['product_dump'] = $testData['test_dump'];
                }
                $status = ($testData['status']=='out_of_stock')?'(Out of stock)':'';
                $testDetails .= '<tbody style="margin:0;padding:0">
                  <tr>
                    <td><span> </span></td>
                  </tr>
                  <tr style="margin:0;padding:0">
                    <td style="vertical-align:top;margin:0;padding:15px;font-weight:bold;border-bottom:1px solid #e9e9e9;font-size:12px">'.$testData['product_dump']['product_details']['product_name'].'</td>
                    <td style="margin:0;padding:15px;font-weight:bold;border-bottom:1px solid #e9e9e9;font-size:12px">'.$testData['number_of_item']. $status.'</td>
                    <td align="right" style="margin:0;padding:15px;font-weight:bold;border-bottom:1px solid #e9e9e9;font-size:12px">'.$testData['amount'].'</td>
                  </tr>
                  <tr width="100%">
                    <td><div style="height:1px;width:100%;background:#e9e9e9;clear:both"></div></td>
                    <td><div style="height:1px;width:100%;background:#e9e9e9;clear:both"></div></td>
                    <td><div style="height:1px;width:100%;background:#e9e9e9;clear:both"></div></td>
                  </tr>
                </tbody>';               
                
                
            }
                $testDetails .= '<tfoot style="margin:0;padding:0">
                  <tr style="margin:0;padding:0">
                    <th width="40%" scope="row" colspan="2" style="margin:0;padding:5px 0;text-align:right;font-weight:bold;border:0;font-size:12px">Cart Subtotal</th>
                    <td width="20%" style="margin:0;padding:5px 0;font-weight:bold;border-bottom:1px solid #e9e9e9;font-size:12px;text-align:right;border:0;padding-right:15px"><span>GHC. '.$parameters['totalOrderDetails']['amount'].'</span></td>
                  </tr>
                  <tr style="margin:0;padding:0">
                    <th width="40%" scope="row" colspan="2" style="margin:0;padding:5px 0;text-align:right;font-weight:bold;border:0;font-size:12px">Delivery Charges</th>
                    <td width="20%" style="margin:0;padding:5px 0;font-weight:bold;border-bottom:1px solid #e9e9e9;font-size:12px;text-align:right;border:0;padding-right:15px">'.$parameters['totalOrderDetails']['delivery_charges'].'</td>
                  </tr>
                  <tr style="margin:0;padding:0">
                    <th width="40%" scope="row" colspan="2" style="margin:0;padding:5px 0;text-align:right;font-weight:bold;border:0;font-size:12px">Discount</th>
                    <td width="20%" style="margin:0;padding:5px 0;font-weight:bold;border-bottom:1px solid #e9e9e9;font-size:12px;text-align:right;border:0;padding-right:15px">-'.$parameters['totalOrderDetails']['discount_amount'].'</td>
                  </tr>
                  <tr class="m_2879509961894745209grand-total" style="margin:0;padding:0;color:#79b33b;background:#f9f9f9">
                    <th width="40%" scope="row" colspan="2" style="margin:0;padding:5px 0;text-align:right;font-weight:bold;border:0;font-size:12px">Grand Total:</th>
                    <td width="20%" style="margin:0;padding:5px 0;font-weight:bold;border-bottom:1px solid #e9e9e9;font-size:12px;text-align:right;border:0;padding-right:15px">'.$parameters['totalOrderDetails']['payable_amount'].'</td>
                  </tr>
                </tfoot>';   
            $replaceData['item_details'] = $testDetails;
        }            
        if(!empty($parameters['reset_link'])) {
            $replaceData['reset_link'] = $parameters['reset_link'];
        }
        if(!empty($parameters['reset_link'])) {
            $replaceData['otp'] = $parameters['otp'];
        }
        $emailBody = $this->prepareEmailBody($templateData['body'], $replaceData);
        $mailQuquedata['body'] = $emailBody;
        $mailQuquedata['from_email_id'] =  FROM_EMAIL;
        $mailQuquedata['subject'] =  $templateData['name'];
        $mailQuquedata['to_email_id'] = $parameters['email'];
        if(!empty($parameters['cc'])) {
            $mailQuquedata['cc_email'] = $parameters['cc'];
        }
        $mailQuquedata['attachments'] = !empty($parameters['attachments'])?$parameters['attachments']:'';
        $customerModel = new customerModel();
        $result = $customerModel->enterDataIntoMailQueue($mailQuquedata);    
    }
    
    function modifyOrder($userDetails, $parameters) {
        $response = array('status' => 'fail', 'msg' => 'Nothing to update');
        if(empty($parameters['order_item_ids'])) {
            $response['msg'] = 'Order id not supplied';
        }
        if(!is_array($parameters['order_item_ids'])) {
            $parameters['order_item_ids'] = explode(',',$parameters['order_item_ids']);
        }
        $requestOrderId = $parameters['order_id'];
        $customerModel = new customerModel();
        $orderOptional['columns'] = array('parent_order_id');
        $orderOptional['count_row'] = true;
        $orderWhere['order_id'] = $parameters['order_id'];
        $orderData = $customerModel->orderList($orderWhere, $orderOptional);
        $parentOrderId = 0;
        if(!empty($orderData['parent_order_id'])) {
            $orderWhere = array();
            $parentOrderId = $orderWhere['parent_order_id'] = $orderData['parent_order_id'];
            $customerModel = new customerModel();
            $orderList = $customerModel->orderList($orderWhere);
            $orderByOrderId = $this->processResult($orderList, 'order_id');
            $orderIds = array_keys($orderByOrderId);
            $parameters['order_id'] = $orderIds;
        }
        
        
        $orderItemWhere = array();
        $orderItemWhere['order_id'] = $parameters['order_id'];
        $orderItemOptional = array();
        $orderItemOptional['id'] = $parameters['order_item_ids'];
        $orderItemOptional['status'] = 'active';
        $customerModel = new customerModel();
        $orderItems = $customerModel->getOrderItem($orderItemWhere, $orderItemOptional);
        if(!empty($orderItems)) {
            $merchantIventoryIds = array();
            $amount = 0;
            $commissionAmount = 0;
            $discountAmount = 0;
            $payableAmount = 0;
            $taxAmount = 0;
            foreach ($orderItems as $items) {
                $amount += $items['amount']; 
                $commissionAmount += $items['commission_amount']; 
                $discountAmount += $items['discount_amount']; 
                $taxAmount += $items['tax_amount']; 
                $merchantIventoryIds[] = $items['merchant_product_id'];
            }
            $payableAmount = $amount-$discountAmount;
        }
        $data = array(
            'amount' => new \Zend\Db\Sql\Expression("amount-".$amount),
            'commission_amount' => new \Zend\Db\Sql\Expression("commission_amount-".$commissionAmount),
            'payable_amount' => new \Zend\Db\Sql\Expression("payable_amount-".$payableAmount),
            'discount_amount' => new \Zend\Db\Sql\Expression("discount_amount-".$discountAmount),
            'tax_amount' => new \Zend\Db\Sql\Expression("tax_amount-".$taxAmount)
        );
        
        $customerModel = new customerModel();
        $itemData = array('status'=>'out_of_stock');
        $updateOrderItemWhereParams = array();
        $updateOrderItemWhereParams['order_id'] = $requestOrderId;
        $updateOrderItemWhereParams['id'] = $parameters['order_item_ids'];
        $orderItemUpdateStatus = $customerModel->updateOrderItem($itemData, $updateOrderItemWhereParams);
          
        if(!empty($orderItemUpdateStatus)) {        
            $customerModel = new customerModel();
            $updateWhereParams = array();
            $updateWhereParams['order_id'] = $requestOrderId;
            $orderUpdateStatus = $customerModel->updateOrder($data, $updateWhereParams);
            if(!empty($orderUpdateStatus)) {
                $orderItemWhere = array();
                $orderItemWhere['order_id'] = $requestOrderId;
                $orderItemOptional = array();
                $orderItemOptional['status'] = 'active';
                $customerModel = new customerModel();
                $orderItems = $customerModel->getOrderItem($orderItemWhere, $orderItemOptional);
                $orderItemData = $this->processResult($orderItems);
                if(empty($orderItemData)) {
                    $customerModel = new customerModel();
                    $updateWhereParams = array();
                    $updateWhereParams['order_id'] = $requestOrderId;
                    
                    $updateOrderData = array();
                    $updateOrderData['order_status'] = 'cancelled';
                    $updateOrderData['payable_amount'] = 0.00;
                    $customerModel->updateOrder($updateOrderData, $updateWhereParams);
                    
                    $customerModel = new customerModel();
                    $orderOptional = array('count_row'=>1);
                    $orderWhere = array('order_id'=>$requestOrderId);
                    
                    $orderDetails = $customerModel->orderList($orderWhere, $orderOptional);
                    $orderDetails['shipping_charges'] = !empty($orderDetails['shipping_charges'])?$orderDetails['shipping_charges']:0;
                    $data['shipping_charges'] = new \Zend\Db\Sql\Expression("shipping_charges-".$orderDetails['shipping_charges']);                    
                    $payableAmount += $orderDetails['shipping_charges'];
                    $data['payable_amount'] = new \Zend\Db\Sql\Expression("payable_amount-".$payableAmount);
                }
                if(!empty($parentOrderId)) {
                    $customerModel = new customerModel();
                    $updateWhereParams = array();
                    $updateWhereParams['order_id'] = $parentOrderId;
                    $orderUpdateStatus = $customerModel->updateOrder($data, $updateWhereParams);                    
                }                
                
                $orderItemWhere = array();
                $orderItemWhere['order_id'] = $parameters['order_id'];
                $orderItemOptional = array();
                $customerModel = new customerModel();
                $orderItems = $customerModel->getOrderItem($orderItemWhere, $orderItemOptional);
                $orderItemData = $this->processResult($orderItems, '', false, false, 'product_dump');

                $customerModel = new customerModel();
                $orderOptional = array('count_row'=>1);
                if(!empty($parentOrderId)) {
                    $orderWhere = array('order_id'=>$parentOrderId);
                }else{
                    $orderWhere = array('order_id'=>$parameters['order_id']);
                }
                
                $orderDetails = $customerModel->orderList($orderWhere, $orderOptional);
                $orderDetails['delivery_charges'] = $orderDetails['shipping_charges'];
                $addressParams = array();
                $addressParams['id'] = $orderDetails['shipping_address_id'];
                $customerModel = new customerModel();
                $addressList = $customerModel->getAddressList($addressParams);
                $addressDetails = $addressList->current();
                $address = '';
                if(!empty($addressDetails)) {          
                    $address = $addressDetails['city_name']."<br/> House No. - ".$addressDetails['house_number'].'<br/> Street - '.$addressDetails['street_detail']." ".$addressDetails['zipcode'];
                }   

                $timeSlotParams = array();
                $timeSlotParams['id'] = array($orderDetails['time_slot_id']);
                $customercurlLib = new customercurl();
                $timeSlotList = $customercurlLib->deliveryTimeSlotList($timeSlotParams);
                if(!empty($timeSlotList['data'])) {
                    $timeSlot = $timeSlotList['data'][$orderDetails['time_slot_id']]['start_time_slot'].'-'.$timeSlotList['data'][$orderDetails['time_slot_id']]['end_time_slot'];
                }                     

                $emailParams = array();
                $emailParams['email'] = $userDetails['email'];
                $emailParams['address'] = $address;
                //$emailParams['landmark'] = $landmark;                
                $emailParams['order_id'] = $parameters['order_id'];
                if(!empty($parentOrderId)) {
                    $emailParams['order_id'] = $parentOrderId;
                }
                $emailParams['name'] = $userDetails['name'];
                $emailParams['email_template_type'] = 'modified_order';
                $emailParams['item_data'] = $orderItemData;
                $emailParams['totalOrderDetails'] = $orderDetails;
                $emailParams['delivery_date'] = $orderDetails['delivery_date'];
                $emailParams['time_slot'] = $timeSlot;
                $this->enterDataIntoMailQueue($emailParams);   
                
                
                $updateInventoryData = array();
                $updateInventoryData['stock'] = 0;
                $where = array();
                $where['id'] = $merchantIventoryIds;
                $customercurlLib = new customercurl();
                $customercurlLib->updateInventry($updateInventoryData, $where);
                $response = array('status' => 'success', 'msg' => 'item Updated successfully');
            }
            
        } 
        
        return $response;
        
    }
}
