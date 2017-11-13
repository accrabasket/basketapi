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
    
    public function addEditProduct($parameters) {
        $response = array('status'=>'fail','msg'=>'fail ');
        $productParams = array();
        $productRules = array();
        if (!empty($parameters['id'])) {
            $productWhere = array();
            $productWhere['id'] = $parameters['id'];
            if(isset($parameters['product_name'])) {
                $productParams['product_name'] = $parameters['product_name'];
                $productRules['product_name'] = array('type'=>'string', 'is_required'=>true);
            }
            if(isset($parameters['category_id'])) {
                $productParams['category_id'] = (int)$parameters['category_id'];
                $productRules['category_id'] = array('type'=>'integer', 'is_required'=>true);
            }
            if(isset($parameters['status'])) {
                $productParams['status'] = $parameters['status'];
            }
            if(isset($parameters['product_desc'])) {
                $productParams['product_desc'] = $parameters['product_desc'];
                $productRules['product_desc'] = array('type'=>'string', 'is_required'=>true);
            }
            $response = $this->isValid($productRules, $productParams);
            if(empty($response)) {
                $result = $this->commonModel->updateProduct($productParams, $productWhere);
                if (!empty($result)) {
                    if (!empty($parameters['attribute_id'])) {
                        $attributeWhere = array();
                        $attributeRules = array();
                        $attributeParams = array();
                        if(isset($parameters['name'])) {
                            $attributeParams['name'] = $parameters['name'];
                            $attributeRules['name'] = array('type'=>'string', 'is_required'=>true);
                        }
                        if(isset($parameters['quantity'])) {
                            $attributeParams['quantity'] = (int)$parameters['quantity'];
                            $attributeRules['quantity'] = array('type'=>'numeric', 'is_required'=>true);
                        }
                        if(isset($parameters['unit'])) {
                            $attributeParams['unit'] = $parameters['unit'];
                            $attributeRules['unit'] = array('type'=>'string', 'is_required'=>true);
                        }
                        $response = $this->isValid($attributeRules, $attributeParams);
                        if(empty($response)) {
                            $attributeWhere['id'] = $parameters['attribute_id'];
                            $returnAttr = $this->commonModel->updateAttribute($attributeParams, $attributeWhere);
                        }
                    }

                }
                $response = array('status' => 'success', 'msg' => 'product updated successfully');
            }
            return $response;
        }else {
            $data = array();
            
            $productParams['product_name'] = $parameters['product_name'];
            $productParams['category_id'] = (int)$parameters['category_id'];
            $productParams['status'] = isset($parameters['status'])?$parameters['status']:1;
            $productParams['product_desc'] = $parameters['product_desc'];
            $productParams['created_date'] = date('Y-m-d H:i:s');

            $productRules['product_name'] = array('type'=>'string', 'is_required'=>true);
            $productRules['category_id'] = array('type'=>'integer', 'is_required'=>true);
            $productRules['product_desc'] = array('type'=>'string', 'is_required'=>true);            
            
            $response = $this->isValid($productRules, $productParams);
            
            if(empty($response)) {
                $productId = $this->commonModel->addProduct($productParams);
                if(!empty($productId)) {
                    $data['product_id'] = $productId;
                    if (!empty($productId) && !empty($parameters['attribute'])) {
                        foreach ($parameters['attribute'] as $key => $value) {
                            $attributeWhere = array();
                            $attributeRules = array();
                            
                            $attributeParams = array();  
                            $attributeParams['product_id'] = $productId;
                            $attributeParams['name'] = $value['name'];
                            $attributeParams['quantity'] = $value['quantity'];
                            $attributeParams['unit'] = $value['unit'];
                            $attributeParams['status'] = 1;
                            $attributeParams['created_date'] = date('Y-m-d H:i:s');
                            
                            $attributeRules['name'] = array('type'=>'string', 'is_required'=>true);
                            $attributeRules['quantity'] = array('type'=>'numeric', 'is_required'=>true);
                            $attributeRules['unit'] = array('type'=>'string', 'is_required'=>true);
                            
                            $response = $this->isValid($attributeRules, $attributeParams);
                            if(empty($response)) {
                                $commonModel = new commonModel();
                                $returnAttr = $commonModel->addAttribute($attributeParams);
                                $data['attribute'][$key] = $returnAttr;
                            }
                        }
                        $response = array('status' => 'success', 'data' => $data);
                    }
                }
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
    public function addEditRider($parameters) {
        $params = array();
        $rule = array();
        if(!empty($parameters['id'])){
            $where = array('id'=>$parameters['id']);
            if(isset($parameters['name'])) {
                $params['name'] = $parameters['name'];
                $rule['name'] = array('type'=>'string', 'is_required'=>true);
            }
            if(isset($parameters['email'])) {
                $params['email'] = $parameters['email'];
                $rule['email'] = array('type'=>'string', 'is_required'=>true);               
            }
            if(isset($parameters['location_id'])) {
                $params['location_id'] = (int)$parameters['location_id'];
                $rule['location_id'] = array('type'=>'integer', 'is_required'=>true);
            }
            if(isset($parameters['password'])) {
                $params['password'] = $parameters['password'];
                $rule['password'] = array('type'=>'string', 'is_required'=>true);
            }            
            if(isset($parameters['status'])) {
                $params['status'] = $parameters['status'];                
            }         
        }else{
            $params['name'] = $parameters['name'];
            $params['email'] = $parameters['email'];
            $params['password'] = $parameters['password'];            
            $params['location_id'] = (int)$parameters['location_id'];
            $params['status'] = $parameters['status'];
            
            $rule['name'] = array('type'=>'string', 'is_required'=>true);
            $rule['email'] = array('type'=>'string', 'is_required'=>true);
            $rule['password'] = array('type'=>'string', 'is_required'=>true);
            $rule['location_id'] = array('type'=>'integer', 'is_required'=>true);
        }
        $response = $this->isValid($rule, $params);
        if(empty($response)){
            $response = array('status' => 'fail', 'msg' => 'No Record Saved ');
            if(!empty($parameters['id'])){
                $result = $this->commonModel->updateRider($params, $where);
            }else {
                $params['created_date'] = date('Y-m-d H:i:s');
                $result = $this->commonModel->addRider($params);
            }
            if(!empty($result)){
                $response = array('status'=>'success','msg'=>'Record Saved Successfully.');
            }            
        }
        
        return $response;
    }
    function riderList($parameters) {
        $response = array('status' => 'fail', 'msg' => 'No record found ');
        $optional = array();        
        if(!empty($parameters['id'])) {
            $optional['id'] = $parameters['id'];
        }        
        if(!empty($parameters['pagination'])) {
            $optional['pagination'] = $parameters['pagination'];
            $optional['page'] = !empty($parameters['page'])?$parameters['page']:1;
        }
        if(!empty($parameters['name'])) {
            $optional['name'] = $parameters['name'];
        }        
        if(!empty($parameters['email'])) {
            $optional['email'] = $parameters['email'];
        }
        if(isset($parameters['location_id'])) {
            $optional['location_id'] = $parameters['location_id'];
        }                
        if(isset($parameters['status'])) {
            $optional['status'] = $parameters['status'];
        }        
        
        $result = $this->commonModel->riderList($optional);
        if (!empty($result)) {
            $data = array();
            foreach ($result as $key => $value) {
                $data[$value['id']] = $value;
            }
            $response = array('status' => 'success', 'data' => $data);
        }
        return $response;        
    }   
    public function saveMerchant($parameters) {
        $response = array('status'=>'fail','msg'=>'Nothing to update.');
        $params = array();
        $rule = array();        
        if(!empty($parameters['id'])){
            $where = array('id'=>$parameters['id']);
            if(isset($parameters['name'])) {
                $params['first_name'] = $parameters['first_name'];
                $rule['first_name'] = array('type'=>'string', 'is_required'=>true);
            }
            if(isset($parameters['email'])) {
                $params['email'] = $parameters['email'];
                $rule['email'] = array('type'=>'string', 'is_required'=>true);               
            }
            if(isset($parameters['ic_number'])) {
                $params['ic_number'] = $parameters['ic_number'];
                $rule['ic_number'] = array('type'=>'string', 'is_required'=>true);
            }
            if(isset($parameters['phone_number'])) {
                $params['phone_number'] = $parameters['phone_number'];
                $rule['phone_number'] = array('type'=>'numeric', 'is_required'=>true);
            }  
            if(isset($parameters['bank_name'])) {
                $params['bank_name'] = $parameters['bank_name'];
                $rule['bank_name'] = array('type'=>'string', 'is_required'=>true);
            } 
            if(isset($parameters['bank_account_number'])) {
                $params['bank_account_number'] = $parameters['bank_account_number'];
                $rule['bank_account_number'] = array('type'=>'numeric', 'is_required'=>true);
            }            
            if(isset($parameters['status'])) {
                $params['status'] = $parameters['status'];                
            }
            $response = $this->isValid($rule, $params);
            if(empty($response)) {
                $result = $this->commonModel->saveMerchant($params, $where);
                if(!empty($result)){
                    $response = array('status'=>'success','msg'=>'Record Saved Successfully.');
                }else{
                    $response = array('status'=>'fail','msg'=>'nothing to update');
                }                
            }
        }
        
        return $response;
    }
    
    public function addedittax($parameters) {
        $response = array('status'=>'fail','msg'=>'Nothing to save.');
        $params = array();
        $rule = array();        
        $params['tax_name'] = $parameters['tax_name'];
        $params['tax_value'] = $parameters['tax_value'];
        
        $rule['tax_name'] = array('type' => 'string', 'is_required' => true);
        $rule['tax_value'] = array('type' => 'numeric', 'is_required' => true);
        if (!empty($parameters['id'])){
            $params['id'] = (int) $parameters['id'];
            $rule['id'] = array('type' => 'numeric', 'is_required' => true);
        }  
        $valid = $this->isValid($rule, $params);
        if (empty($valid) && empty($params['id'])) {
            $result = $this->commonModel->savetax($params);
            if (!empty($result)) {
                $response = array('status' => 'success', 'msg' => 'Record Saved Successfully.');
            } 
        }else if(empty($valid) && !empty($params['id'])){
            $result = $this->commonModel->updatetax($params, $params['id']);
            if (!empty($result)) {
                $response = array('status' => 'success', 'msg' => 'Record upadate Successfully.');
            }
        }
        return $response;
    }
    
    public function taxlist($parameters, $optional = array()) {
        $response = array('status' => 'fail', 'msg' => 'No record found ');
        
        $result = $this->commonModel->taxlist($parameters, $optional);
        
        if (!empty($result)) {
            $data = array();
            foreach ($result as $key => $value) {
                $data[] = $value;
            }
            $response = array('status' => 'success', 'data' => $data);
        }
        return $response;
    }
    
    function deletetax($parameters) {
        $response = array('status' => 'fail', 'msg' => 'Category Not Deleted '); 
        if(!empty($parameters['id'])) {
            $result = $this->commonModel->deletetax($parameters);
            if (!empty($result)) {
                $response = array('status' => 'success', 'msg' => 'Category deleted ');
            }
        }        
        
        return $response;        
    }
}
