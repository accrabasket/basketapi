<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Library;
use Application\Model\commonModel;
class common  {
    public function __construct() {
        $this->commonModel = new commonModel();
    }
    public function addEditCategory($parameters , $optional =array()) {
        $response = array('status'=>'fail','msg'=>'fail ');
       // $validate  = $this->validation($parameters);
        if(!empty($parameters['id'])){
            $result = $this->commonModel->updateCategory($parameters);
            if(!empty($result)){
                $response = array('status'=>'success','msg'=>'category updated ');
            }
            return $response;
        }
        
        $result = $this->commonModel->addCategory($parameters);
        if(!empty($result)){
                $response = array('status'=>'success','msg'=>'category created ');
            }
        return $response;
    }
    
    public function addEditProduct($parameters , $optional =array()) {
        $response = array('status'=>'fail','msg'=>'fail ');
       // $validate  = $this->validation($parameters);
       if (!empty($parameters['id'])) {
            $result = $this->commonModel->updateProduct($parameters);
            if (!empty($result)) {
                if (!empty($parameters['attribute_id'])) {
                    $attribute = array();
                    $attribute['product_id'] = $result;
                    $attribute['attribute_type'] = $parameters['name'];
                    $attribute['quantity'] = $parameters['quantity'];
                    $attribute['unit'] = $parameters['unit'];
                    $attribute['status'] = 1;
                    $optional['id'] = $parameters['attribute_id'];

                    $returnAttr = $this->commonModel->updateAttribute($attribute, $optional);
                   
                }
                
            }
            $response = array('status' => 'success', 'msg' => 'product updated ');
            return $response;
        }
//        print_r($parameters);die;
        $data = array();
        $result = $this->commonModel->addProduct($parameters);
        $data['product_id'] = $result;
        if (!empty($result) && !empty($optional['attribute'])) {
            foreach ($optional['attribute'] as $key => $value) {
                $attribute = array();
                $attribute['product_id'] = $result;
                $attribute['name'] = $value['name'];
                $attribute['quantity'] = $value['quantity'];
                $attribute['unit'] = $value['unit'];
                $attribute['status'] = 1;

                $returnAttr = $this->commonModel->addAttribute($attribute);
                $data['attribute'][$key] = $returnAttr;
            }
            if (!empty($returnAttr)) {
                $response = array('status' => 'success', 'data' => $data);
            }
        }
        return $response;
    }
    
    public function categoryList($parameters, $optional = array()) {
        $response = array('status' => 'fail', 'msg' => 'No record found ');
        $result = $this->commonModel->categoryList($parameters, $optional);
        if (!empty($result)) {
            $data = array();
            foreach ($result as $key => $value) {
                $data[$value['id']] = $value;
            }
            $response = array('status' => 'success', 'data' => $data);
        }
        return $response;
    }
    
    public function getMarchantList($parameters, $optional = array()) {
        $response = array('status' => 'fail', 'msg' => 'No record found ');
        $result = $this->commonModel->getMarchantList($parameters, $optional);
        if (!empty($result)) {
            $data = array();
            foreach ($result as $key => $value) {
                $data[] = $value;
            }
            $response = array('status' => 'success', 'data' => $data);
        }
        return $response;
    }
    
    public function addEditLocation($parameters) {
        $params = array();
        $rule = array();
        if(!empty($parameters['id'])){
            $where = array('id'=>$parameters['id']);
            if(isset($parameters['googlelocation'])) {
                $params['googlelocation'] = $parameters['googlelocation'];
                $params['lat'] = $parameters['lat'];
                $params['lng'] = $parameters['lng'];
                $rule['googlelocation'] = array('type'=>'string', 'is_required'=>true); 
                $rule['lat'] = array('type'=>'numeric', 'is_required'=>true);
                $rule['lng'] = array('type'=>'numeric', 'is_required'=>true);
            }
            if(isset($parameters['address'])) {
                $params['address'] = $parameters['address'];
                $rule['address'] = array('type'=>'string', 'is_required'=>true);                
            }
            if(isset($parameters['country_id'])) {
                $params['country_id'] = (int)$parameters['country_id'];
                $rule['country_id'] = array('type'=>'integer', 'is_required'=>true);
            }
            if(isset($parameters['active'])) {
                $params['active'] = $parameters['active'];                
            }         
        }else{
            $params['googlelocation'] = $parameters['googlelocation'];
            $params['address'] = $parameters['address'];
            $params['country_id'] = (int)$parameters['country_id'];
            $params['active'] = $parameters['active'];
            $params['lat'] = $parameters['lat'];
            $params['lng'] = $parameters['lng'];
            
            $rule['googlelocation'] = array('type'=>'string', 'is_required'=>true);
            $rule['address'] = array('type'=>'string', 'is_required'=>true);
            $rule['country_id'] = array('type'=>'integer', 'is_required'=>true);
            $rule['lat'] = array('type'=>'numeric', 'is_required'=>true);
            $rule['lng'] = array('type'=>'numeric', 'is_required'=>true);
        }
        $response = $this->isValid($rule, $params);
        if(empty($response)){
            $response = array('status' => 'fail', 'msg' => 'No Record Saved ');
            if(!empty($parameters['id'])){
                $result = $this->commonModel->updateLocation($params, $where);
            }else {
                $params['created_date'] = date('Y-m-d H:i:s');
                $result = $this->commonModel->addLocation($params);
            }
            if(!empty($result)){
                $response = array('status'=>'success','msg'=>'Record Saved');
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
    
    function getLocationList($parameters) {
        $response = array('status' => 'fail', 'msg' => 'No record found ');
        $optional = array();        
        if(!empty($parameters['id'])) {
            $optional['id'] = $parameters['id'];
        }        
        if(!empty($parameters['pagination'])) {
            $optional['pagination'] = $parameters['pagination'];
            $optional['page'] = !empty($parameters['page'])?$parameters['page']:1;
        }
        if(!empty($parameters['address'])) {
            $optional['address'] = $parameters['address'];
        }
        if(isset($parameters['active'])) {
            $optional['active'] = $parameters['active'];
        }        
        
        $result = $this->commonModel->locationList($optional);
        if (!empty($result)) {
            $data = array();
            foreach ($result as $key => $value) {
                $data[$value['id']] = $value;
            }
            $response = array('status' => 'success', 'data' => $data);
        }
        return $response;        
    }
    
    function getProductList($parameters) {
        $response = array('status' => 'fail', 'msg' => 'No record found ');
        $optional = array();        
        if(!empty($parameters['id'])) {
            $optional['id'] = $parameters['id'];
        }        
        if(!empty($parameters['pagination'])) {
            $optional['pagination'] = $parameters['pagination'];
            $optional['page'] = !empty($parameters['page'])?$parameters['page']:1;
        }
        
        if(isset($parameters['active'])) {
            $optional['active'] = $parameters['active'];
        }        
        
        $result = $this->commonModel->getProductList($optional);
        if (!empty($result)) {
            $data = array();
            foreach ($result as $key => $value) {
                $data[$value['id']] = $value;
            }
            $response = array('status' => 'success', 'data' => $data);
        }
        return $response;        
    }
    
    function deleteCategory($parameters) {
        $response = array('status' => 'fail', 'msg' => 'Category Not Deleted '); 
        $rule['id'] = array('type'=>'integer', 'is_required'=>true);
        if(!empty($parameters['id'])) {
            $result = $this->commonModel->deleteCategory($parameters);
            if (!empty($result)) {
                $response = array('status' => 'success', 'msg' => 'Category deleted ');
            }
        }        
        
        return $response;        
    }
    
}
