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
            $itemIntoCart = $this->getItemIntoCart($params);
            if(!empty($itemIntoCart)) {
                if($parameters['action'] == 'delete') {
                    if($itemIntoCart[$params['merchant_inventry_id']]['number_of_item'] >$params['number_of_item']) {
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
        $data = $this->customerModel->getItemIntoCart($params);
        $cartData = array();
        if(!empty($data)) {
            $cartData = $this->processResult($data, 'merchant_inventry_id');
        }
        
        return $cartData;
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
}
