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
        $GLOBALS['CATEGORYIMAGEPATH'] = $_SERVER['DOCUMENT_ROOT'].'/basketapi/category/';
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
                if(!empty($optional['image'])) {
                    $path = $GLOBALS['CATEGORYIMAGEPATH'];
                    $this->uploadImage($optional['image'],$path,$result);
                }
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
            if(!empty($parameters['tax_id'])){
               $productParams['tax_id'] = $parameters['tax_id']; 
            }
            if(!empty($parameters['product_discount_type']) && !empty($parameters['product_discount_value'])){
               $productParams['discount_value'] = $parameters['product_discount_value'];
               $productParams['discount_type'] = $parameters['product_discount_type']; 
            }
            $response = $this->isValid($productRules, $productParams);
            if(empty($response)) {
                $result = $this->commonModel->updateProduct($productParams, $productWhere);
                if (!empty($result)) {
                    if (!empty($parameters['attribute'])) {
                        $data['product_id'] = $result;
                        foreach ($parameters['attribute'] as $key => $value) {
                            $attributeWhere = array();
                            $attributeRules = array();
                            $attributeParams = array();
                            if (isset($value['name'])) {
                                $attributeParams['name'] = $value['name'];
                                $attributeRules['name'] = array('type' => 'string', 'is_required' => true);
                            }
                            if (isset($value['quantity'])) {
                                $attributeParams['quantity'] = (int) $value['quantity'];
                                $attributeRules['quantity'] = array('type' => 'numeric', 'is_required' => true);
                            }
                            if (isset($value['unit'])) {
                                $attributeParams['unit'] = $value['unit'];
                                $attributeRules['unit'] = array('type' => 'string', 'is_required' => true);
                            }
                            if (!empty($value['commission_value'])) {
                                $attributeParams['commission_value'] = $value['commission_value'];
                                $attributeParams['commission_type'] = $value['commission_type'];
                            }
                            if(!empty($value['attribute_discount_value']) && !empty($value['attribute_discount_type'])){
                                $attributeParams['discount_value'] = $value['attribute_discount_value'];
                                $attributeParams['discount_type'] = $value['attribute_discount_type']; 
                             }

                            $response = $this->isValid($attributeRules, $attributeParams);
                            
                            if (empty($response)) {
                                if(!empty($value['id'])){
                                    $attributeWhere['id'] = $value['id'];
                                    $returnAttr = $this->commonModel->updateAttribute($attributeParams, $attributeWhere);
                                }else{
                                    $attributeParams['product_id'] = $data['product_id'];
                                    $attributeParams['status'] = 1;
                                    $attributeParams['created_date'] = date('Y-m-d H:i:s');
                                    $returnAttr = $this->commonModel->addAttribute($attributeParams);
                                }
                                
                                $data['attribute'][$key] = $returnAttr;
                            }
                        }
                    }
                }
                $response = array('status' => 'success', 'data' => $data);
            }
            return $response;
        }else {
            $data = array();
            
            $productParams['product_name'] = $parameters['product_name'];
            $productParams['category_id'] = (int)$parameters['category_id'];
            $productParams['status'] = isset($parameters['status'])?$parameters['status']:1;
            $productParams['product_desc'] = $parameters['product_desc'];
            if(!empty($parameters['product_discount_type']) && !empty($parameters['product_discount_value'])){
               $productParams['discount_value'] = $parameters['product_discount_value'];
               $productParams['discount_type'] = $parameters['product_discount_type']; 
            }
            
            $productParams['created_date'] = date('Y-m-d H:i:s');

            $productRules['product_name'] = array('type'=>'string', 'is_required'=>true);
            $productRules['category_id'] = array('type'=>'integer', 'is_required'=>true);
            $productRules['product_desc'] = array('type'=>'string', 'is_required'=>true);            
            if(!empty($parameters['tax_id'])){
               $productParams['tax_id'] = $parameters['tax_id']; 
            }
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
                            if(!empty($value['commission_value'])) {
                                $attributeParams['commission_value'] = $value['commission_value'];
                                $attributeParams['commission_type'] = $value['commission_type'];

                            }
                            if(!empty($value['attribute_discount_value']) && !empty($value['attribute_discount_type'])){
                                $attributeParams['discount_value'] = $value['attribute_discount_value'];
                                $attributeParams['discount_type'] = $value['attribute_discount_type']; 
                             }
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
    
    public function categoryList($parameters) {
        $response = array('status' => 'fail', 'msg' => 'No record found ');
        if(!empty($parameters['categoryHavingNoProduct'])) {
            $productOptional = array();
            $productOptional['key'] = 'category_id';
            $productOptional['onlyProductDetails'] = 1;
            $productOptional['columns'] = array(new \Zend\Db\Sql\Expression('DISTINCT(category_id) as category_id'));
            $productategoryData = $this->getProductList($productOptional);
            if(!empty($productategoryData['data'])) {
                $productCategoryList = array_keys($productategoryData['data']);
                $parameters['categoryNotIn'] = $productCategoryList;
            }
        }else if(!empty($parameters['categoryHavingNoChild'])){
            $categoryOptional = array();
            $categoryOptional['columns'] = array(new \Zend\Db\Sql\Expression('DISTINCT(parent_category_id) as parent_category_id'));
            $parentCategoryIdsResult = $this->commonModel->categoryList($categoryOptional);
            if(!empty($parentCategoryIdsResult)){
                $parentCategoryIds = $this->processResult($parentCategoryIdsResult, 'parent_category_id');
                $parameters['categoryNotIn'] = array_keys($parentCategoryIds);
            }
        }                    
        $result = $this->commonModel->categoryList($parameters);
        if (!empty($result)) {
            $data = array();
            foreach ($result as $key => $value) {
                $data[$value['id']] = $value;
            }
            $response = array('status' => 'success', 'data' => $data);
        }
        return $response;
    }
    
    public function getMarchantList($parameters) {
        $response = array('status' => 'fail', 'msg' => 'No record found ');
        $optional = array();
        if (!empty($parameters['id'])) {
            $optional['id'] = $parameters['id'];
        }
        if(!empty($parameters['city_id'])) {
            $params = array();
            $params['city_id'] = $parameters['city_id'];
            $merchantList = $this->getMerchantByCity($params);
            if(!empty($merchantList['data'])){
                $optional['id'] = array_keys($merchantList['data']);
            }else {
                return $response;
            }
        }
        $result = $this->commonModel->getMarchantList($optional);
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
            if(isset($parameters['city_id'])) {
                $params['city_id'] = (int)$parameters['city_id'];
                $rule['city_id'] = array('type'=>'integer', 'is_required'=>true);
            }
            if(isset($parameters['active'])) {
                $params['active'] = $parameters['active'];                
            }         
        }else{
            $params['googlelocation'] = $parameters['googlelocation'];
            $params['address'] = $parameters['address'];
            $params['country_id'] = (int)$parameters['country_id'];
            $params['city_id'] = (int)$parameters['city_id'];
            $params['active'] = $parameters['active'];
            $params['lat'] = $parameters['lat'];
            $params['lng'] = $parameters['lng'];
            
            $rule['googlelocation'] = array('type'=>'string', 'is_required'=>true);
            $rule['address'] = array('type'=>'string', 'is_required'=>true);
            $rule['country_id'] = array('type'=>'integer', 'is_required'=>true);
            $rule['city_id'] = array('type'=>'integer', 'is_required'=>true);
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
        if(!empty($parameters['columns'])) {
            $optional['columns'] = $parameters['columns'];
        }      
        if(!empty($parameters['onlyProductDetails'])) {
            $optional['onlyProductDetails'] = $parameters['onlyProductDetails'];
        }              
        
        if(isset($parameters['active'])) {
            $optional['active'] = $parameters['active'];
        }
        
        if(isset($parameters['filter_type'])) {
            if($parameters['filter_type'] == 'Product_name'){
                $optional['product_name'] = $parameters['value'];
            }
            if($parameters['filter_type'] == 'Attribute_name'){
                $optional['name'] = $parameters['value'];
            }
            if($parameters['filter_type'] == 'Category_name'){
                $optional['category_name'] = $parameters['value'];
            }
            
        }
        $totalRecord = $this->commonModel->getProductListCount($optional);
        foreach ($totalRecord as $key => $value) {
            $count = $value['count'];
        }
        
        $result = $this->commonModel->getProductList($optional);
        if (!empty($result)) {
            if(empty($parameters['key'])){
                $parameters['key'] = 'id';
            }
            $data = $this->processResult($result, $parameters['key']);
            if(!empty($data)) {
                $optional['product_id'] = array_keys($data);
                $getattribute = $this->commonModel->getAttributeList($optional);
                $attdata = $this->processResult($getattribute);
                $prepairdata['data'] = $this->prepairProduct($data,$attdata);
                $prepairdata['count'] = $count;
                $response = array('status' => 'success', 'data' => $prepairdata);
            }
        }
        return $response;        
    }
    function processResult($result,$dataKey='') {
        $data = array();
        if(!empty($result)) {
            foreach ($result as $key => $value) {
                if(!empty($dataKey)){
                    $data[$value[$dataKey]] = $value;
                }else {
                    $data[] = $value;
                }
            }        
        }
        
        return $data;
    }
    
    function prepairProduct($productdata,$attribute) {
        $data = array();
        $return = array();
        foreach ($attribute as $key => $value) {
            $data[$value['product_id']][] = $value;
        }
        
        foreach ($productdata as $key => $value) {
            $return[$key] = $value;
            $return[$key]['atribute'] = $data[$key];
        }
        return $return;
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
                $data[$key] = $value;
            }
            $response = array('status' => 'success', 'data' => $data);
        }
        return $response;
    }
    
    function deletetax($parameters) {
        $response = array('status' => 'fail', 'msg' => 'Tax Not Deleted '); 
        if(!empty($parameters['id'])) {
            $result = $this->commonModel->deletetax($parameters);
            if (!empty($result)) {
                $response = array('status' => 'success', 'msg' => 'Tax deleted ');
            }
        }        
        
        return $response;        
    }
    
    public function addEditStore($parameters) {
        $params = array();
        $rule = array();
        if(!empty($parameters['id'])){
            $where = array('id'=>$parameters['id']);
            if(isset($parameters['store_name'])) {
                $params['store_name'] = $parameters['store_name'];
                $rule['store_name'] = array('type'=>'string', 'is_required'=>true); 
            }
            if(isset($parameters['address'])) {
                $params['address'] = $parameters['address'];
                $rule['address'] = array('type'=>'string', 'is_required'=>true);                
            }
            if(isset($parameters['location_id'])) {
                $params['location_id'] = (int)$parameters['location_id'];
                $rule['location_id'] = array('type'=>'integer', 'is_required'=>true);
            }
            if(isset($parameters['status'])) {
                $params['status'] = $parameters['status'];                
            } 
            
            if(isset($parameters['lat'])) {
                $params['lat'] = (int) $parameters['lat'];
                $rule['lat'] = array('type'=>'numeric', 'is_required'=>true);
                
            } 
            
            if(isset($parameters['lng'])) {
                $params['lng'] = (int)$parameters['lng'];
                $rule['lng'] = array('type'=>'numeric', 'is_required'=>true);
            }

        }else{
            $params['store_name'] = $parameters['store_name'];
            $params['address'] = $parameters['address'];
            $params['location_id'] = (int)$parameters['location_id'];
            $params['status'] = $parameters['status'];
            $params['lat'] = $parameters['lat'];
            $params['lng'] = $parameters['lng'];
            
            $rule['store_name'] = array('type'=>'string', 'is_required'=>true);
            $rule['address'] = array('type'=>'string', 'is_required'=>true);
            $rule['location_id'] = array('type'=>'integer', 'is_required'=>true);
            $rule['lat'] = array('type'=>'numeric', 'is_required'=>true);
            $rule['lng'] = array('type'=>'numeric', 'is_required'=>true);
        }
        $response = $this->isValid($rule, $params);
        $params['merchant_id'] = $parameters['merchant_id'];
        if(empty($response)){
            $response = array('status' => 'fail', 'msg' => 'No Record Saved ');
            if(!empty($parameters['id'])){
                $result = $this->commonModel->updateStore($params, $where);
            }else {
                $params['created_on'] = date('Y-m-d H:i:s');
//                $params['updated_on'] = date('Y-m-d H:i:s');
                $result = $this->commonModel->saveStore($params);
            }
            if(!empty($result)){
                $response = array('status'=>'success','msg'=>'Record Saved');
            }            
        }
        
        return $response;
    }
    
    function storeList($parameters) {
        $response = array('status' => 'fail', 'msg' => 'No record found ');
        $optional = array();        
        if(!empty($parameters['id'])) {
            $optional['id'] = $parameters['id'];
        }        
        if(!empty($parameters['location_id'])) {
            $optional['location_id'] = $parameters['location_id'];
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
        
        if(isset($parameters['merchant_id'])) {
            $optional['merchant_id'] = $parameters['merchant_id'];
        }
        
        $result = $this->commonModel->storeList($optional);
        if (!empty($result)) {
            $data = array();
            foreach ($result as $key => $value) {
                $data[$value['id']] = $value;
            }
            $response = array('status' => 'success', 'data' => $data);
        }
        return $response;        
    }
    
    function deleteStore($parameters) {
        $response = array('status' => 'fail', 'msg' => 'Store Not Deleted '); 
        if(!empty($parameters['id'])) {
            $result = $this->commonModel->deleteStore($parameters);
            if (!empty($result)) {
                $response = array('status' => 'success', 'msg' => 'Store deleted ');
            }
        }        
        
        return $response;        
    }
    
    public function addEditInventry($parameters) {
        $response = array('status' => 'fail', 'msg' => 'No Record Saved ');
        foreach ($parameters['store_id'] as $key => $value) {
            if (!empty($parameters['attribute_id'])) {
                foreach ($parameters['attribute_id'] as $keys => $values) {
                    $params = array();
                    $params['store_id'] = (int)$value;
                    $params['product_id'] = $parameters['product_id'];
                    $params['attribute_id'] = $parameters['attribute_id'][$keys];
                    $params['price'] = $parameters['price'][$keys];
                    $params['stock'] = $parameters['stock'][$keys];
                    $params['merchant_id'] = $parameters['merchant_id'];
//                        
                    $rule['store_id'] = array('type' => 'numeric', 'is_required' => true);
                    $rule['product_id'] = array('type' => 'numeric', 'is_required' => true);
                    $rule['attribute_id'] = array('type' => 'numeric', 'is_required' => true);
                    $rule['price'] = array('type' => 'numeric', 'is_required' => true);
                    $rule['stock'] = array('type' => 'numeric', 'is_required' => true);
                    $response = $this->isValid($rule, $params);

                    $optional = array();
                    $optional['store_id'] = (int) $value;
                    $optional['attribute_id'] = $params['attribute_id'];
                    $optional['merchant_id'] = $params['merchant_id'];
                    $where = array();
                    $attributeExist = $this->commonModel->checkAttributeExist($optional);
                    if(!empty($attributeExist)){
                        foreach ($attributeExist as $key => $value) {
                            $where['id'] = $value['id'];
                        }
                    }
                    
                    if (empty($response) && empty($where)) {
                        $params['created_date'] = date('Y-m-d H:i:s');
                        $result = $this->commonModel->saveInventry($params);
                    } else if(empty($response) && !empty($where)) {
                        $params['updated_date'] = date('Y-m-d H:i:s');
                        $result = $this->commonModel->updateInventry($params,$where);;
                    }else if(!empty($response) ){
                        break;
                    }
                }
            }
            if (!empty($result)) {
                $response = array('status' => 'success', 'msg' => 'Record Saved');
            }


            return $response;
        }
    }
        function stockList($parameters) {
        $response = array('status' => 'fail', 'msg' => 'No record found ');
        $optional = array();        
        if(!empty($parameters['id'])) {
            $optional['id'] = $parameters['id'];
        }        
        if(!empty($parameters['pagination'])) {
            $optional['pagination'] = $parameters['pagination'];
            $optional['page'] = !empty($parameters['page'])?$parameters['page']:1;
        }
        
        if(isset($parameters['merchant_id'])) {
            $optional['merchant_id'] = $parameters['merchant_id'];
        }
        
        $result = $this->commonModel->stockList($optional);
        if (!empty($result)) {
            $data = array();
            foreach ($result as $key => $value) {
                $data[$value['id']] = $value;
            }
            $response = array('status' => 'success', 'data' => $data);
        }
        return $response;        
    }
    
    function addProductByCsv($parameters) {
            $data = array();
            $productParams['product_name'] = $parameters['product_name'];
            $productResult = $this->commonModel->getProductList($productParams);
            $productData = $this->processResult($productResult);
            if(count($productData)>0) {
                $productParams['id'] = $productData[0]['id'];
            }
            $categoryParams = array();
            $categoryParams['category_name'] = $parameters['category_name'];
            $categoryResult = $this->commonModel->categoryList($categoryParams);
            $categoryData = $this->processResult($categoryResult);
            if(count($categoryData)>0) {
                $productParams['category_id'] = $categoryData[0]['id'];
            }else {
                $categoryParams['parent_category_id'] = 0;
                $categoryParams['category_des'] = !empty($parameters['category_des'])?$parameters['category_des']:'';
                $categoryId = $this->commonModel->addCategory($categoryParams);
                
                $productParams['category_id'] = $categoryId;
            }
            $productParams['product_desc'] = $parameters['product_desc'];
            $productParams['created_date'] = date('Y-m-d H:i:s');  
            $productParams['attribute'] = array();
            for($i=0; $i<count($parameters['attribute_name']); $i++) {
                if(!empty($productParams['id'])) {
                    $attributParams = array();
                    $attributParams['product_id'] = $productParams['id'];
                    $attributParams['name'] = $parameters['attribute_name'][$i];
                    $attributeResult = $this->commonModel->getAttributeList($attributParams);
                    $attributeData = $this->processResult($attributeResult);
                    if(count($attributeData)>0) {
                        $productParams['attribute'][$i]['id'] = $attributeData[0]['id'];
                    }                    
                }
                $productParams['attribute'][$i]['name'] = $parameters['attribute_name'][$i];
                $productParams['attribute'][$i]['quantity'] = $parameters['quantity'][$i];
                $productParams['attribute'][$i]['unit'] = $parameters['unit'][$i];
                $productParams['attribute'][$i]['commission_type'] = $parameters['commission_type'][$i];
                $productParams['attribute'][$i]['commission_value'] = $parameters['commission_value'][$i];
            }
          return $this->addEditProduct($productParams);  
    }
    
    public function cityList($parameters, $optional = array()) {
        $response = array('status' => 'fail', 'msg' => 'No record found ');
        if(!empty($parameters['id'])){
            $optional['id'] = $parameters['id'];
        }
        if(!empty($parameters['country_id'])){
            $optional['country_id'] = $parameters['country_id'];
        }
        
        if(!empty($parameters['pagination'])) {
                $optional['pagination'] = $parameters['pagination'];
        }
        if(!empty($parameters['city_name'])) {
                $optional['city_name'] = $parameters['city_name'];
        }
        
        $result = $this->commonModel->cityList($optional);
        
        if (!empty($result)) {
            $data = array();
            foreach ($result as $key => $value) {
                $data[$key] = $value;
            }
            $response = array('status' => 'success', 'data' => $data);
        }
        return $response;
    }
    
    public function countryList($parameters, $optional = array()) {
        $response = array('status' => 'fail', 'msg' => 'No record found ');
        if(!empty($parameters['id'])){
            $optional['id'] = $parameters['id'];
        }
        
        if(!empty($parameters['pagination'])) {
                $optional['pagination'] = $parameters['pagination'];
        }
        
        if(!empty($parameters['country_name'])) {
                $optional['country_name'] = $parameters['country_name'];
        }
        
        $result = $this->commonModel->countryList($optional);
        
        if (!empty($result)) {
            $data = array();
            foreach ($result as $key => $value) {
                $data[$key] = $value;
            }
            $response = array('status' => 'success', 'data' => $data);
        }
        return $response;
    }

    function getMerchantByCity($parameters) {
        $response = array('status' => 'fail', 'msg' => 'No record found ');
        $locationList = $this->getLocationList($parameters);
        if(!empty($locationList['data'])) {
            $storeParams = array();
            $locationListIds = array_keys($locationList['data']);
            $storeParams['columns'] = array(new \Zend\Db\Sql\Expression('merchant_store.merchant_id as merchant_id'));
            $storeParams['location_id'] = $locationListIds;
            $merchantList = $this->commonModel->storeList($storeParams);
            $merchantListData = $this->processResult($merchantList, 'merchant_id');
            if(!empty($merchantListData)) {
                $response = array('status' => 'success', 'data' => $merchantListData);
            }
        }
        return $response;
    }
    
    function getLocationListByCity($parameters) {
        $response = array('status' => 'fail', 'msg' => 'No record found ');
        $locationParams = array();
        $locationParams['city_id'] = $parameters['city_id'];
        $locationParams['active'] = 1;
        $locationParams['columns'] = array(new \Zend\Db\Sql\Expression('location_master.id as id'));
        $rules['city_id'] = array('type'=>'numeric', 'is_required'=>true);
        $response = $this->isValid($rules, $locationParams);
        if(empty($response)) {
            $response = $this->getLocationList($locationParams);
        }
        return getLocationList;
    }
    
    function uploadImage($data,$path,$id) {
        if(!empty($data)) {
            $data = explode(',', $data);
            $imagData = base64_decode($data[1]);
            $imagePath = $path.'/'.$id.'/';
            @mkdir($imagePath, '0777', true);
            $im = imagecreatefromstring($imagData);
            if ($im !== false) {
                if($data[0] == 'data:image/jpeg;base64'){
                    header('Content-Type: image/jpeg');
                    imagejpeg($im, $imagePath.'category.jpg');
                    $return['imageExt'] = 'jpg';
                }else {
                    header('Content-Type: image/png');
                    imagepng($im, $imagePath.'category.png');
                    $return['imageExt'] = 'png';
                }
                imagedestroy($im);
            }
        }
        return true;
    }
}
