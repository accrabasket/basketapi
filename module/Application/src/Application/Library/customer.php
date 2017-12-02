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
class customer {

    public function __construct() {
        $this->customerModel = new customerModel();
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
        if(!empty($params['guest_id'])) {
            $where['guest_user_id'] = $params['guest_user_id'] = $parameters['guest_user_id'];
        }
        if(empty($parameters['guest_id']) && empty($parameters['user_id'])) {
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
                    }
                }else if($parameters['action'] == "add"){
                    $params['number_of_item'] = $itemIntoCart[$params['merchant_inventry_id']]['number_of_item']+$params['number_of_item']; 
                    $parameters['action'] = "update";
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
        if(!empty($params['guest_id'])) {
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
                $response = array('status' => 'success', 'data' => $cartData);
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
               $userParams['password'] = $parameters['password']; 
            }
            if(isset($parameters['status'])) {
                $userParams['status'] = $parameters['status'];
            }            
        }else {
            $userParams['email'] = $parameters['email'];
            $userParams['mobile_number'] = $parameters['mobile_number'];
            
            $userInputParams = array();
            
            $userInputParams = $userParams;
            $userParams['name'] = $parameters['name'];
            $userParams['city_id'] = $parameters['city_id']; 
            $userParams['address'] = !empty($parameters['address'])?$parameters['address']:""; 
            $userParams['password'] = $parameters['password']; 
            $userParams['created_date'] = date('Y-m-d H:i:s'); 
            $rules['password'] = array('type'=>'string', 'is_required'=>true);
            $rules['city_id'] = array('type'=>'numeric', 'is_required'=>true);            
            $rules['mobile_number'] = array('type'=>'string', 'is_required'=>true);            
            $rules['email'] = array('type'=>'string', 'is_required'=>true);            
            $rules['name'] = array('type'=>'string', 'is_required'=>true);                        
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
                       $result = $this->customerModel->updateUser($userParams); 
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
            $where['password'] = $parameters['password'];
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
    
    function addEditAddress($parameters) {
        
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
}
