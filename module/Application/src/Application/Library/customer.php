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
        if(empty($params['guest_id']) && empty($params['user_id'])) {
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
            $rules['password']           =  array('type'=>'string', 'is_required'=>true);
            $rules['city_id']            =  array('type'=>'numeric', 'is_required'=>true);            
            $rules['mobile_number']      =  array('type'=>'string', 'is_required'=>true);            
            $rules['email']              =  array('type'=>'string', 'is_required'=>true);            
            $rules['name']               =  array('type'=>'string', 'is_required'=>true);                        
        }
        
        $response = $this->isValid($rules, $userParams);
        if(empty($response)) {
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
                    $result = $this->customerModel->addUser($userParams);
                    if(!empty($result)) {
                         $response = array('status'=>'success', 'msg'=>"User created successfully.");
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
        if(!empty($parameters['password'])) {
            $where['password'] = $parameters['password'];
        }
        if(!empty($parameters['mobile_number'])) {
            $where['mobile_number'] = $parameters['mobile_number'];
        }        
        
        
        if(!empty($parameters['pagination'])) {
            $optional['pagination'] = true;
            $optional['page'] = !empty($parameters['page'])?$parameters['page']:1;
        } 
        $result = $this->customerModel->getUserDetail($where, $optional);
        if(!empty($result)) {
            $customerData = $this->processResult($result, 'id');
            if(!empty($customerData)) {
                $response = array('status'=>'success', 'data'=>$customerData);
            }
        }
        
        return $response;
    }
    
    public function login($parameters) {
        $response = array('status'=>'fail','msg'=>'Invalid credentials');
        $status = true;
        $where = array();
        if(!empty($parameters['email']) || !empty($parameters['mobile_number'])) {
            $where['email'] = isset($parameters['email'])?$parameters['email']:'';
            $where['mobile_number'] = isset($parameters['mobile_number'])?$parameters['mobile_number']:'';
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
        if($status){
            $userDetails = $this->getUserDetail($where);
            if(!empty($userDetails['data'])){
                $response = $userDetails;
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
               $rules['area'] = array('type'=>'string', 'is_required'=>true);
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
            $rules['area'] = array('type'=>'string', 'is_required'=>true);
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
    
    function placeOrder($parameters) {
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
        }
        if($status) {
            $cartData = $this->getItemIntoCart($cartParams);
            if(empty($cartData['data'])){
                $response['msg'] = 'No Item found in cart';
                
                return $response;
            }
            $orderDetails = $this->calculateDiscountAndAmount($cartData);
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
                $parentOrder['shipping_address_id'] = $parameters['shipping_address_id'];
                $parentOrder['amount'] = $orderDetails['totalOrderDetails']['amount'];
                $parentOrder['payable_amount'] = $orderDetails['totalOrderDetails']['payable_amount'];
                $parentOrder['discount_amount'] = $orderDetails['totalOrderDetails']['discount_amount'];
                $parentOrder['tax_amount'] = $orderDetails['totalOrderDetails']['tax_amount'];
                $parentOrder['commission_amount'] = $orderDetails['totalOrderDetails']['commission_amount'];
                $parentOrder['created_date'] = date('Y-m-d H:i:s');
                $parentOrder['payment_status'] = 'unpaid';
                $result = $this->customerModel->createOrder($parentOrder);
            }
            foreach($orderDetails['order'] as $storeId=>$orderDetail) {
                $merchantOrderId = 'order_m'.$storeId;
                $orderSeq = $this->customerModel->updateOrderSeq($merchantOrderId); 
                $orderId = $merchantOrderId.'_'.$orderSeq[$merchantOrderId];
                $orderData = array();
                $orderData['user_id'] = $parameters['user_id'];
                $orderData['time_slot_id'] = !empty($parameters['time_slot_id'])?$parameters['time_slot_id']:0;
                $orderData['order_id'] = $orderId;
                $orderData['parent_order_id'] = $parentOrderId;
                $orderData['store_id'] = $storeId;
                $orderData['shipping_address_id'] = $parameters['shipping_address_id'];
                $orderData['amount'] = $orderDetail['amount'];
                $orderData['payable_amount'] = $orderDetail['amount']-$orderDetail['discount_amount'];
                $orderData['discount_amount'] = $orderDetail['discount_amount'];
                $orderData['commission_amount'] = $orderDetail['commission_amount'];
                $orderData['payment_status'] = 'unpaid';    
                $orderData['created_date'] = date('Y-m-d H:i:s');
                $result = $this->customerModel->createOrder($orderData);
                if(!empty($result)) {
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
        $totalOrderDetails['amount'] = 0;
        $totalOrderDetails['discount_amount'] = 0;
        $totalOrderDetails['commission_amount'] = 0;
        $totalOrderDetails['tax_amount'] = 0;        
        foreach($data['data'] as $key=>$item) {
            if(!empty($data['productDetails']['data'][$key])) {
                $discount = 0;
                $productDetails = $data['productDetails']['data'][$key];
                $productImageData = !empty($data['productDetails']['productImageData'][$productDetails['product_id']])?$data['productDetails']['productImageData'][$productDetails['product_id']]:array();
                $amount = $productDetails['price']*$item['number_of_item'];                                
                if(empty($order[$productDetails['store_id']])) {
                    $order[$productDetails['store_id']] = array();
                    $order[$productDetails['store_id']]['amount'] = $amount;
                    $order[$productDetails['store_id']]['discount_amount'] = 0;
                    $order[$productDetails['store_id']]['commission_amount'] = 0;
                    $order[$productDetails['store_id']]['tax_amount'] = 0;
                }else {
                    $order[$productDetails['store_id']]['amount']+=$amount; 
                }
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
                
                $merchantItemWisePriceDetails[$productDetails['store_id']][$key]['tax_amount'] = 0;
                $itemWisePriceDetails[$key]['tax_amount'] = 0;
                $totalOrderDetails['payable_amount'] = $totalOrderDetails['amount']-$totalOrderDetails['discount_amount']+$totalOrderDetails['tax_amount'];
                
            }
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
        }else{
            $status = false;
            $response['msg'] = "User not supplied";
        }      
        if(!empty($parameters['order_status'])){
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
        $orderList = $this->customerModel->orderList($orderWhere, $optional);
        
        $countOptional = array();
        $countOptional['columns'] = array('count' => new \Zend\Db\Sql\Expression('count(*)'));
        $countOptional['count_row'] = true;
        $customerModel = new customerModel();
        $totalNumberOfOrders = $customerModel->orderList($orderWhere, $countOptional);
        $orderListData = $this->prepareOrderList($orderList);
        if(!empty($orderListData)) {
            $response = array('status'=>'success', 'data'=>$orderListData, 'imageRootPath'=>HTTP_ROOT_PATH, 'totalNumberOfOrder'=>$totalNumberOfOrders['count']);
        }
        
        return $response;
    }
    
    function prepareOrderList($orderData){
        $orderListByOrderId = array();
        $orderDataList = array();
        if(!empty($orderData)) {
            foreach($orderData as $orders) {
                $orderListByOrderId[$orders['order_id']] = $orders;
            }
            if(!empty($orderListByOrderId)) {
                $orderIds = array_keys($orderListByOrderId);
                $orderItemWhere = array();
                $orderItemWhere['order_id'] = $orderIds;
                $orderItems = $this->customerModel->getOrderItem($orderItemWhere);
                if(!empty($orderItems)) {
                    foreach($orderItems as $orderItem){
                        if(!empty($orderItem['product_dump'])) {
                            $orderItem['product_dump'] = json_decode($orderItem['product_dump']);
                        }
                        if(!empty($orderListByOrderId[$orderItem['order_id']]['parent_order_id'])) {
                            $orderDataList[$orderListByOrderId[$orderItem['order_id']]['parent_order_id']]['order_details'] = isset($orderListByOrderId[$orderListByOrderId[$orderItem['order_id']]['parent_order_id']])?$orderListByOrderId[$orderListByOrderId[$orderItem['order_id']]['parent_order_id']]:'';
                            $orderDataList[$orderListByOrderId[$orderItem['order_id']]['parent_order_id']]['orderitem'][$orderItem['merchant_product_id']] = $orderItem; 
                        }else{
                            $orderDataList[$orderItem['order_id']]['order_details'] = $orderListByOrderId[$orderItem['order_id']];
                            $orderDataList[$orderItem['order_id']]['orderitem'][$orderItem['merchant_product_id']] = $orderItem; 
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
                }
            }
            if($status) {
                $params['status'] = 1;
                $params['created_date'] = date('Y-m-d H:i:s');
                $result = $this->customerModel->assignOrder($params);
                if(!empty($result)) {
                    $response = array('status'=>'success', 'msg'=>'order assigned to rider.');
                }
            }
        }
        
        return $response;
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
            $where['order_status'] = array('order_placed','ready_to_dispatch','dispatched');
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
                    $customerModel = new customerModel();
                    $result = $customerModel->updateOrder($params, $where);
                    if(!empty($result)) {
                        $response = array('status'=>'success', 'msg'=>'order updated successfully.');
                    }
                }else {
                    $response['msg']='Order not assigned with this rider.';
                }
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
            if($parameters['order_status'] == 'current_order'){
               $orderWhere['order_status'] = array('order_placed','ready_to_dispatch', 'dispatched'); 
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
        
        $countOptional = array();
        $countOptional['columns'] = array('count' => new \Zend\Db\Sql\Expression('count(*)'));
        $countOptional['count_row'] = true;
        $customerModel = new customerModel();
        $totalNumberOfOrders = $customerModel->assignedOrderToRider($orderWhere, $countOptional);        
        $orderListData = $this->prepareOrderForRiders($orderList);
        if(!empty($orderListData)) {
            $response = array('status'=>'success', 'data'=>$orderListData, 'imageRootPath'=>HTTP_ROOT_PATH, 'totalNumberOfOrder'=>$totalNumberOfOrders['count']);
        }
        return $response;
    }
    
    function prepareOrderForRiders($orderData){
        $shippingAddressList = array();
        $storeList = array();
        $orderItemList = array();
        $orderList = array();
        foreach($orderData as $orders) {
            $shippingAddressList[$orders['shipping_address_id']] = $orders['shipping_address_id'];
            $storeList[$orders['store_id']] = $orders['store_id'];
            $orderList[$orders['order_id']] = $orders;
        }
        
        if(!empty($shippingAddressList)) {
            $addressParams['id'] = array_keys($shippingAddressList);
            $addressList = $this->customerModel->getAddressList($addressParams);
            $shippingAddressList = $this->processResult($addressList, 'id');
            
            $orderParams['order_id'] = array_keys($orderList);
            $orderItems = $this->customerModel->getOrderItem($orderParams);
            $orderItemList = $this->processResult($orderItems, 'order_id', true);
            
            $storeParams['id'] = array_keys($storeList);
            $storeList = $this->customercurlLib->getStoreListById($storeParams);
        }
        if(!empty($shippingAddressList)) {
            return array('orderList'=>$orderList,'shippingAddressList'=>$shippingAddressList, 'orderItemList'=>$orderItemList, 'storeList'=>$storeList['data']);
        }
        
        return false;
    }
    function processResult($result,$dataKey='', $multipleRowOnKey = false) {
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
                    $data[] = $value;
                }
            }        
        }
        
        return $data;
    } 
    
    public function generateotp($parameters) {
        $response = array('status'=>'fail','msg'=>'Invalid details');
        $status = true;
        $where = array();
        if(!empty($parameters['mobile_number'])) {
            $where['mobile_number'] = $parameters['mobile_number'];
        }else{
            $status = false;
            $response = array('status'=>'fail','msg'=>'Mobile number not supplied');
        }
        if(!empty($parameters['otp_type'])) {
            $where['otp_type'] = $parameters['otp_type'];
        }else{
            $status = false;
            $response = array('status'=>'fail','msg'=>'Otp type is not supplied');
        }        
        
        if($status){
            $result = $this->customerModel->deleteOtp($where);
            $expireTime = date('Y-m-d H:i:s', strtotime("+".OTP_EXPIRE_TIME." minutes"));
            $randomNumber = mt_rand(1000, 10000);
            $randomNumber = 1234;
            $smsQueueData = array();
            $otpData = array();
            $otpData['mobile_number'] = $smsQueueData['mobile_number'] = $parameters['mobile_number'];
            $otpData['otp_type'] = $parameters['otp_type'];
            $otpData['user_id'] = isset($parameters['user_id'])?$parameters['user_id']:0;
            $otpData['otp'] = $randomNumber;
            $otpData['expiry_date'] = $expireTime;
            $otpResponse = $this->customerModel->insertIntoOtpMaster($otpData);
            if(!empty($otpResponse)) {
                $smsQueueData['message'] = $randomNumber.' is your OTP for '.$otpData['otp_type'].' Enter this in the box provided within 15 minuts.';
                $result = $this->customerModel->smsqueue($smsQueueData);
            }
            if(!empty($result)){
                $response = array('status'=>'success','msg'=>'Otp send');
            }
        }
        return $response;
    }
    
    function verifyotp($parameters) {
        $response = array('status' => 'fail', 'msg' => 'Otp not valid');
        $status = true;
        $where = array();
        if (!empty($parameters['mobile_number'])) {
            $where['mobile_number'] = $parameters['mobile_number'];
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
                foreach ($userDetails['data'] as $key => $value) {
                   $to_mail_id =  $value['email'];
                   $to_name =  $value['name'];
                }
                if(!empty($to_mail_id)){
                    $body = "Hi '$to_name', \r\n   We have sent you this email in response to your request to reset your password on Accra Basket. Please click on <a href='#' >Click here</a> to change password. ";
                    $mail = new Mail\Message();
                    $mail->setBody($body);
                    $mail->setFrom('vgiri8308@gmail.com', 'Vikash');
                    $mail->addTo($to_name, $to_name);
                    $mail->setSubject('Forget Password');
                    $transport = new Mail\Transport\Sendmail();
                    $result = $transport->send($mail);
                    if (!empty($result)){
                        $response = array('status' => 'success', 'msg' => 'password change request send');
                    }
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
        $response = array('status' => 'fail', 'msg' => 'change not change');
        $status = true;
        $data = array();
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
        }
        
        if ($status) {
            $data['updated_date'] = date('Y-m-d H:i:s');
            $result = $this->customerModel->changepassword($data, $where['id']);
            if (!empty($result)) {
                $response = array('status' => 'success', 'msg' => 'password changed');
            }
        }
        return $response;
    }
}
