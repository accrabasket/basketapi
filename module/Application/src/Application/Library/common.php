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
    public $commonModel;
    public $redis;
    public function __construct() {
        $this->commonModel = new commonModel();
        $this->redis = new \Redis();
        $this->redisObj = $this->redis->pconnect('127.0.0.1', 6379);        
    }
    public function addEditCategory($parameters , $optional =array()) {
        $response = array('status'=>'fail','msg'=>'fail ');
        if(!empty($parameters['id'])){
            $this->commonModel->updateCategory($parameters);
            $result = $parameters['id'];
        }else {        
            $result = $this->commonModel->addCategory($parameters);
        }
        if(!empty($result)){            
            if(!empty($optional['image'])) {
                $this->deleteImage($result, 'category');
                $imageParams = array();
                $imageParams['type'] = 'category';
                $imageParams['id'] = $result;
                $imageParams['imageType'] = "string";
                $imageParams['images'][] = $optional['image'];
                $this->uploadImgParams($imageParams, $result);
            }            
            $response = array('status'=>'success','msg'=>'category Data Saved');
        }
        return $response;   
    }
    
    function deleteImage($imageId, $type){
        $commonModel = new commonModel();
        $commonModel->deleteImage($imageId, $type);
    }
    
    public function addEditProduct($parameters) {
        $response = array('status'=>'fail','msg'=>'fail ');
        $productParams = array();
        $productRules = array();
        if (!empty($parameters['id'])) {
            $productId = $parameters['id'];
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
            if(!empty($parameters['product_desc'])) {
                $productParams['product_desc'] = $parameters['product_desc'];
                $productRules['product_desc'] = array('type'=>'string', 'is_required'=>true);
            }
            if(!empty($parameters['tax_id'])){
               $productParams['tax_id'] = $parameters['tax_id']; 
            }
            if(!empty($parameters['custom_info'])){
               $productParams['custom_info'] = $parameters['custom_info']; 
            }
            if(isset($parameters['brand_name'])){
               $productParams['brand_name'] = $parameters['brand_name']; 
            }                    
            if(isset($parameters['nutrition'])){
               $productParams['nutrition'] = $parameters['nutrition']; 
            }            
            if(!empty($parameters['bullet_desc'])){
               $productParams['bullet_desc'] = $parameters['bullet_desc']; 
            }  
            if(isset($parameters['hotdeals'])){
               $productParams['hotdeals'] = $parameters['hotdeals']; 
            }            
            if(isset($parameters['offers'])){
               $productParams['offers'] = $parameters['offers']; 
            }
            if(isset($parameters['new_arrival'])){
               $productParams['new_arrival'] = $parameters['new_arrival']; 
            }            
            if(isset($parameters['product_discount_type']) && isset($parameters['product_discount_value'])){
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
                            if (isset($value['commission_value'])) {
                                $attributeParams['commission_value'] = isset($value['commission_value'])?$value['commission_value']:'flat';
                                $attributeParams['commission_type'] = $value['commission_type'];
                            }
                            if(isset($value['attribute_discount_value']) && isset($value['attribute_discount_type'])){
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
                                $value['type'] = "attribute";
                                if(!empty($parameters['attribute image'])){
                                    $value['attribute image'] = $parameters['attribute image'];
                                    $this->uploadImgParamsViaCsv($value, $returnAttr);
                                }  else {
                                    $this->uploadImgParams($value, $returnAttr);         
                                }
                                $data['attribute'][$key] = $returnAttr;
                            }
                        }
                    }
                }
                $response = array('status' => 'success', 'data' => $data);
            }
        }else {
            $data = array();
            $productParams['item_code'] = $parameters['item_code'];
            $productParams['product_name'] = $parameters['product_name'];
            $productParams['category_id'] = (int)$parameters['category_id'];
            $productParams['status'] = isset($parameters['status'])?$parameters['status']:1;
            $productParams['product_desc'] = !empty($parameters['product_desc'])?$parameters['product_desc']:'';
            if(!empty($parameters['product_discount_type']) && !empty($parameters['product_discount_value'])){
               $productParams['discount_value'] = $parameters['product_discount_value'];
               $productParams['discount_type'] = $parameters['product_discount_type']; 
            }
            
            $productParams['created_date'] = date('Y-m-d H:i:s');
            $productRules['item_code'] = array('type'=>'string', 'is_required'=>true);
            $productRules['product_name'] = array('type'=>'string', 'is_required'=>true);
            $productRules['category_id'] = array('type'=>'integer', 'is_required'=>true);
            //$productRules['product_desc'] = array('type'=>'string', 'is_required'=>true);            
            if(!empty($parameters['tax_id'])){
               $productParams['tax_id'] = $parameters['tax_id']; 
            }
            $productParams['custom_info'] = '';
            if(!empty($parameters['custom_info'])){
               $productParams['custom_info'] = $parameters['custom_info']; 
            }
            if(!empty($parameters['brand_name'])){
               $productParams['brand_name'] = $parameters['brand_name']; 
            }                    
            if(!empty($parameters['nutrition'])){
               $productParams['nutrition'] = $parameters['nutrition']; 
            }            
            $productParams['bullet_desc'] = '';
            if(!empty($parameters['bullet_desc'])){
               $productParams['bullet_desc'] = $parameters['bullet_desc']; 
            }
            
            if(isset($parameters['hotdeals'])){
               $productParams['hotdeals'] = $parameters['hotdeals']; 
            }            
            if(isset($parameters['offers'])){
               $productParams['offers'] = $parameters['offers']; 
            }
            if(isset($parameters['new_arrival'])){
               $productParams['new_arrival'] = $parameters['new_arrival']; 
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
                                $value['type'] = "attribute";
                                if(!empty($parameters['attribute image'])){
                                    $value['attribute image'] = $parameters['attribute image'];
                                    $this->uploadImgParamsViaCsv($value, $returnAttr);
                                }  else {
                                    $this->uploadImgParams($value, $returnAttr);         
                                }
                                 
                            }
                        }
                        $response = array('status' => 'success', 'data' => $data);
                    }
                }
            }
        }
        $parameters['type'] = "product";
        if(!empty($productId)){
            if(!empty($parameters['product_image'])){
                $this->uploadImgParamsViaCsv($parameters, $productId);
            }  else {
                $this->uploadImgParams($parameters, $productId);         
            }
            $parameters['type'] = "nutrition_image";
            if(!empty($parameters['nutrition_image'])){
                $this->uploadImgParamsViaCsv($parameters, $productId);
            }else if(!empty($parameters['nutrition_img'])){
                $parameters['images'] = $parameters['nutrition_img'];
                $this->uploadImgParams($parameters, $productId);         
            }   
            if(!empty($parameters['merchant_ids'])) {
                $where = array();
                $where['merchant_id'] = $parameters['merchant_ids'];
                $where['product_id'] = $productId;
                $optionalParams = array();
                $optionalParams['column'] = array('merchant_id');
                $mappedMerchantListWithProductData = $this->getMerchantProductMapping($where, $optionalParams);
                $mappedMerchantData = array_keys($mappedMerchantListWithProductData);
                if(isset($mappedMerchantData[0]) && $mappedMerchantData[0]==0) {
                    //do nothing
                }else{
                    $merchantList = array_diff($parameters['merchant_ids'], $mappedMerchantData);
                    if(!empty($merchantList)) {
                        foreach($merchantList as $merchant) {
                            $productMappingData = array();
                            $productMappingData['product_id'] = $productId;
                            $productMappingData['merchant_id'] = $merchant;
                            $productMappingData['created_date'] = date('Y-m-d H:i:s');
                            
                            $this->insertIntoProductMapping($productMappingData);
                        }
                    }
                }
            }
        }
        return $response;
    }
    
    public function insertIntoProductMapping($data) {
        $commonModel = new commonModel();
        $commonModel->insertIntoProductMapping($data);
    }
    public function getMerchantProductMapping($where, $optional = array()) {
        $commonModel = new commonModel();
        $merchantMappingResult = $commonModel->getMerchantMapping($where, $optional);
        $merchantData = $this->processResult($merchantMappingResult, 'merchant_id');
        return $merchantData;
    }
    public function uploadImgParams($value, $id){
        $imageParams = array();
        $imageParams['type'] = $value['type'];
        $imageParams['imageType'] = "string";
        if(!empty($value['images']) && is_array($value['images'])) {
            foreach($value['images'] as $image) {
                $imageParams['id'] = $id;
                $imageParams['imageData'] = $image;
                if(!empty($image)) {
                    $imageData = $this->uploadImage($imageParams);
                    $imageParams['image_name'] = $imageData['imagename'];
                    $imageParams['image_id'] = $imageParams['id'];
                    unset($imageParams['imageData']);
                    unset($imageParams['imageType']);
                    unset($imageParams['id']);
                    $this->commonModel->saveImage($imageParams);
                }
            }
        }
        return $imageParams;
    }
    
    public function uploadImgParamsViaCsv($value, $id){
        $newloc = $GLOBALS['IMAGEROOTPATH'].'/'.$value['type'].'/'.$id.'/';
        @mkdir($newloc, 0777, true);
        if($value['type'] == 'product'){
            $image = $value['product_image'];
        }elseif($value['type'] == 'nutrition_image'){
            $image = $value['nutrition_image'];
        }else if(!empty($value['attribute image'])){
            $image = $value['attribute image'];
        }
        if(!empty($image)){
            foreach ($image as $key => $values) {
                if(!empty($values)){
                    $curretfile = $GLOBALS['IMAGEROOTPATH'].'/media/'.$values;
                    $name = $values;
                    if(copy($curretfile, $newloc.$name)) {
                        $imageParams = array();
                        $imageParams['type'] = $value['type'];
                        $imageParams['image_name'] = $name;
                        $imageParams['image_id'] = $id;
                        $this->commonModel->saveImage($imageParams);
                    }                    
                } 
            }
        }
        return true;
    }
    public function categoryList($parameters) {
        $keyStr = md5(json_encode($parameters));
        //$response = $this->redis->get($keyStr);
        if(!empty($response)) {
            $response = json_decode($response, true);
            return $response;
        }        
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
        $data = array();
        if (!empty($result)) {
            foreach ($result as $key => $value) {
                $data[$value['id']] = $value;
            }
            $imageData = array();
            if(!empty($data)) {
                $whereParams = array();
                $whereParams['image_id'] = array_keys($data);
                $whereParams['type'] = 'category';
                $categoryImageData = $this->commonModel->fetchImage($whereParams);
                if(!empty($categoryImageData)) {
                    $imageData = $this->processResult($categoryImageData, 'image_id', true);
                }
            }
            $response = array('status' => 'success', 'data' => $data, 'images'=>$imageData,'imageRootPath'=>HTTP_ROOT_PATH);
        }
        $this->redis->set($keyStr, json_encode($response));
        $this->redis->expire($keyStr, 3600);        
        return $response;
    }
    
    public function getMarchantList($parameters) {
        $keyStr = md5(json_encode($parameters));
        //$response = $this->redis->get($keyStr);
        if(!empty($response)) {
            $response = json_decode($response, true);
            return $response;
        }        
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
            $imageData = array();
            foreach ($result as $key => $value) {
                $data[] = $value;
                if(!empty($value['image_ext'])){
                 $imageData[$value['id']] = HTTP_ROOT_PATH.'/merchant/'.$value['id'].'.'.$value['image_ext'];
                  
                }
                
            }
            $response = array('status' => 'success', 'data' => $data, 'images'=>$imageData);
        }
        $this->redis->set($keyStr, json_encode($response));
        $this->redis->expire($keyStr, 3600);        
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
        $keyStr = md5(json_encode($parameters));
        //$response = $this->redis->get($keyStr);
        //if(!empty($response)) {
        {
            //$response = json_decode($response, true);
            //return $response;
        //}else {
            $response = array('status' => 'fail', 'msg' => 'No record found ');
            $optional = array();        
            $inventryWhere = array();
            if(!empty($parameters['id'])) {
                $optional['id'] = $parameters['id'];
                $inventryWhere['product_id'] = $parameters['id'];
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
            if(!empty($parameters['merchant_id'])) {
                $optional['merchant_id'] = $parameters['merchant_id'];
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
                $data = $this->processResult($result, $parameters['key'], false, true);            
                if(!empty($data)) {

                    $productImageWhere = array();
                    $productImageWhere['image_id'] = array_keys($data);
                    $productImageWhere['type'] = array('product', 'nutrition_image');
                    $productImageData = $this->fetchImage($productImageWhere);

                    $optional['product_id'] = array_keys($data);
                    $getattribute = $this->commonModel->getAttributeList($optional);
                    $attdata = $this->processResult($getattribute);
                    $attributeImageData = array();
                    if(!empty($attdata)) {
                        $attrImageWhere = array();
                        $attrImageWhere['image_id'] = array_keys($attdata);
                        $attrImageWhere['type'] = 'attribute';
                        $attributeImageData = $this->fetchImage($attrImageWhere);                    
                    }
                    $prepairdata = $this->prepairProduct($data,$attdata);
                    if(!empty($parameters['merchant_id'])) {
                        $inventryWhere['merchant_id'] = $parameters['merchant_id'];
                    }
                    $getInventryDetails = array();
                    if(!empty($inventryWhere)){
                        $getInventryInfo = $this->commonModel->checkAttributeExist($inventryWhere);    
                        $getInventryDetails = $this->processResult($getInventryInfo, 'store_id', true, false, 'attribute_id');
                    }
                    $response = array('status' => 'success', 'data' => $prepairdata, 'inventry_detail'=>$getInventryDetails,'productimage'=>$productImageData, 'attributeimage'=>$attributeImageData, 'imageRootPath'=>HTTP_ROOT_PATH, 'totalRecord'=>$count);
                }
            }
        }
        $this->redis->set($keyStr, json_encode($response));
        $this->redis->expire($keyStr, 3600);                 
        return $response;        
    }
    function processResult($result,$dataKey='', $multipleRowOnKey = false, $format_custom_info = false, $multipleRowKey='') {
        $data = array();
        if(!empty($result)) {
            foreach ($result as $key => $value) {
                if($format_custom_info) {
                    $value['custom_info'] = json_decode($value['custom_info']);
                    if(isset($value['bullet_desc'])) {
                        $value['bullet_desc'] = json_decode($value['bullet_desc']);
                    }
                }
                if(!empty($dataKey)){
                    if($multipleRowOnKey) {
                        if(!empty($multipleRowKey)){
                            $data[$value[$dataKey]][$value[$multipleRowKey]] = $value;
                        }else{
                            $data[$value[$dataKey]][] = $value;
                        }
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
            
    function prepairProduct($productdata,$attribute) {
        $data = array();
        $return = array();
        foreach ($attribute as $key => $value) {
            $data[$value['product_id']][] = $value;
        }
        
        foreach ($productdata as $key => $value) {
            $return[$key] = $value;
            $return[$key]['atribute'] = !empty($data[$key])?$data[$key]:'';
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
    function deleteProduct($parameters) {
        $response = array('status' => 'fail', 'msg' => 'Product Not Deleted '); 
        $rule['product_id'] = array('type'=>'integer', 'is_required'=>true);
        if(!empty($parameters['product_id'])) {
            $this->commonModel->beginTransaction();
            $result = $this->commonModel->deleteProduct($parameters);
            if (!empty($result)) {
                $result = $this->commonModel->deleteAttribute($parameters);
                $result = $this->commonModel->deleteMerchantInvernty($parameters);
                $response = array('status' => 'success', 'msg' => 'Product deleted ');
                $this->commonModel->commit();
            }else{
                $this->commonModel->rollback();
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
            if(isset($parameters['mobile_number'])) {
                $params['mobile_number'] = $parameters['mobile_number'];
                $rule['mobile_number'] = array('type'=>'numeric', 'is_required'=>true);               
            }            
            if(isset($parameters['location_id'])) {
                $params['location_id'] = (int)$parameters['location_id'];
                $rule['location_id'] = array('type'=>'integer', 'is_required'=>true);
            }
            if(isset($parameters['password'])) {
                $params['password'] = md5($parameters['password']);
                $rule['password'] = array('type'=>'string', 'is_required'=>true);
            }            
            if(!empty($parameters['fcm_reg_id'])) {
                $params['fcm_reg_id'] = $parameters['fcm_reg_id'];
            }            
            if(isset($parameters['status'])) {
                $params['status'] = $parameters['status'];                
            }         
        }else{
            $params['name'] = $parameters['name'];
            $params['email'] = $parameters['email'];
            $params['mobile_number'] = $parameters['mobile_number'];
            $params['password'] = $parameters['password'];            
            $params['location_id'] = (int)$parameters['location_id'];
            $params['status'] = $parameters['status'];
            
            $rule['name'] = array('type'=>'string', 'is_required'=>true);
            $rule['email'] = array('type'=>'string', 'is_required'=>true);
            $rule['mobile_number'] = array('type'=>'numeric', 'is_required'=>true);
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
        if(!empty($parameters['mobile_number'])) {
            $optional['mobile_number'] = $parameters['mobile_number'];
        }        
        if(!empty($parameters['password'])) {
            $optional['password'] = $parameters['password'];
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
    
    function getRidersByStoreId($parameters) {
        $response = array('status' => 'fail', 'msg' => 'No record found ');
        $status = true;
        $where = array();
        if(!empty($parameters['store_id'])) {
            $where['id'] = $parameters['store_id'];
        }else{
            $status = false;
            $response['msg'] = 'Store id not supplied';
        }
        if($status) {
            $storeResponse = $this->storelist($where);
            if(!empty($storeResponse['data'])) {
                $storeDetails = array_values($storeResponse['data']);
                $riderWhere = array();
                $riderWhere['location_id'] = $storeDetails[0]['location_id'];
                $response = $this->riderList($riderWhere);
            }
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
            if(isset($parameters['address'])) {
                $params['address'] = $parameters['address'];
                $rule['address'] = array('type'=>'string', 'is_required'=>true);               
            }
            if(isset($parameters['username'])) {
                $params['username'] = $parameters['username'];               
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
            if(!empty($parameters['image'])) {
                $imgHeader = explode(';', $parameters['image']);
                $imageExt = explode('/', $imgHeader[0]);
                
                $params['image_ext'] = $imageExt[1];
            }
            $response = $this->isValid($rule, $params);
            if(empty($response)) {
                $result = $this->commonModel->saveMerchant($params, $where);
                if(!empty($result)){
                    if(!empty($parameters['image'])) {
                        $imageParams = array();
                        $imageParams['type'] = 'merchant';
                        $imageParams['id'] = $result;
                        $imageParams['imageType'] = "string";
                        $imageParams['imageData'] = $parameters['image'];
                        $imageParams['make_id_wise_folder'] = 'no';
                        $this->uploadImage($imageParams);
                    }
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
                
            } 
            
            if(isset($parameters['lng'])) {
                $params['lng'] = $parameters['lng'];
            }

        }else{
            $params['store_name'] = $parameters['store_name'];
            $params['address'] = $parameters['address'];
            $params['location_id'] = (int)$parameters['location_id'];
            $params['status'] = $parameters['status'];
            $params['lat'] = $parameters['lat'];
            $params['lng'] = $parameters['lng'];
            
            $rule['store_name'] = array('type'=>'string', 'is_required'=>true);
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
                        foreach ($attributeExist as $key => $attribute) {
                            $where['id'] = $attribute['id'];
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
        if(!empty($parameters['out_of_stock'])) {
            $optional['out_of_stock'] = $parameters['out_of_stock'];
        }        
        if(!empty($parameters['pagination'])) {
            $optional['pagination'] = $parameters['pagination'];
            $optional['page'] = !empty($parameters['page'])?$parameters['page']:1;
        }
        
        if(isset($parameters['merchant_id'])) {
            $optional['merchant_id'] = $parameters['merchant_id'];
        }
        
        $result = $this->commonModel->stockList($optional);
        $optional['columns'] = array('count' => new \Zend\Db\Sql\Expression('count(*)'));
        $optional['count_row'] = true;
        $commonModel = new commonModel();
        $totalNumberOfRecords = $commonModel->stockList($optional);                
        if (!empty($result)) {
            $data = array();
            foreach ($result as $key => $value) {
                $data[$value['id']] = $value;
            }
            $response = array('status' => 'success', 'data' => $data, 'totalNumberOfOrder'=>$totalNumberOfRecords['count']);
        }
        return $response;        
    }
    
    function addProductByCsv($parameters) {
            $data = array();
            $productParams['item_code'] = (string)$parameters['item_code'];
            $productResult = $this->commonModel->getProductList($productParams);
            $productData = $this->processResult($productResult);
            if(count($productData)>0) {
                $productParams['id'] = $productData[0]['id'];
            }
            $categoryParams = array();
            $categoryParams['category_name'] = $parameters['category_name'];
            $categoryResult = $this->commonModel->categoryList($categoryParams);
            $categoryData = $this->processResult($categoryResult);
            
            $productParams['product_name'] = $parameters['product_name'];
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
                $productParams['attribute'][$i]['name'] = !empty($parameters['attribute_name'][$i])?$parameters['attribute_name'][$i]:$parameters['product_name'];
                $productParams['attribute'][$i]['quantity'] = $parameters['quantity'][$i];
                $productParams['attribute'][$i]['unit'] = $parameters['unit'][$i];
                $productParams['attribute'][$i]['commission_type'] = $parameters['commission_type'][$i];
                $productParams['attribute'][$i]['commission_value'] = $parameters['commission_value'][$i];
            }
            if(!empty($parameters['custom_info'])){
                $productParams['custom_info'] = json_encode($parameters['custom_info']);
            }
            if(!empty($parameters['bullet_desc'])){
                $productParams['bullet_desc'] = json_encode($parameters['bullet_desc']);
            }            
            if(!empty($parameters['brand_name'])){
                $productParams['brand_name'] = $parameters['brand_name'];
            }
            if(!empty($parameters['nutrition'])){
                $productParams['nutrition'] = $parameters['nutrition'];
            }
            if(!empty($parameters['nutrition_image'])){
                $productParams['nutrition_image'] = $parameters['nutrition_image'];
            }            
            if(!empty($parameters['product_image'])){
                $productParams['product_image'] = $parameters['product_image'];
            }
            if(!empty($parameters['attribute_image'])){
                $productParams['attribute_image'] = $parameters['attribute_image'];
            }
            if(!empty($parameters['merchant_name'])) {
                $where['email'] = $parameters['merchant_name'];
                $merchantList = $this->getMerchantList($where);
                $merchantIds = array_keys($merchantList);
                $productParams['merchant_ids'] = $merchantIds;
            }
          return $this->addEditProduct($productParams);  
    }
    
    public function getMerchantList($where) {
        $result = $this->commonModel->getMarchantList($where);
        $merchantList = $this->processResult($result, 'id');
        return $merchantList;
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
                $data[$value['id']] = $value;
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
        $locationList = $this->getLocationListByCity($parameters);
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
        return $response;
    }
      function uploadImage($imageParams) {         
        error_reporting(0);
        if(empty($imageParams['make_id_wise_folder'])){
            $imagePath = $GLOBALS['IMAGEROOTPATH'].'/'.$imageParams['type'].'/'.$imageParams['id'].'/';
        }else{
            $imagePath = $GLOBALS['IMAGEROOTPATH'].'/'.$imageParams['type'].'/';
        }
        
        if(!empty($imageParams['imageData'])) {
            if(empty($imageParams['make_id_wise_folder'])){
                $imageName = $imageParams['id'].'_'.time();
            }else{
                $imageName = $imageParams['id'];
            }            
            //$data = explode(',', $imageParams['imageData']);
            //$imagData = base64_decode($data[1]);
            @mkdir($imagePath, 0755, true);
            $imagePath = $imagePath.$imageName;
            $data = explode(';', $imageParams['imageData']);
            $imageData = explode(',', $data[1]);
            $imageBase64Data = base64_decode($imageData[1]); 
            if($data[0] == 'data:image/jpeg'){
                $return['imagename'] = $imageName.'.jpg';
                file_put_contents($imagePath.'.jpg', $imageBase64Data); 
            }else {
                $return['imagename'] = $imageName.'.png';
                file_put_contents($imagePath.'.png', $imageBase64Data);            
            }            
/*            $im = imagecreatefromstring($imagData);die('ss');
            if ($im !== false) {
                if($data[0] == 'data:image/jpeg;base64'){
                    header('Content-Type: image/jpeg');
                    imagejpeg($im, $imagePath.'.jpg');
                    $return['imagename'] = $imageName.'.jpg';
                }else {
                    header('Content-Type: image/png');
                    imagepng($im, $imagePath.'.png');
                    $return['imagename'] = $imageName.'.png';
                }
                imagedestroy($im);
            }*/
        }
        return $return;
    }
    function getStoreByCity($parameters) {
        $response = array('status' => 'fail', 'msg' => 'No record found ');
        $locationList = $this->getLocationListByCity($parameters);
        if(!empty($locationList['data'])) {
            $storeParams = array();
            $locationListIds = array_keys($locationList['data']);
            $storeParams['columns'] = array(new \Zend\Db\Sql\Expression('merchant_store.id as id'));
            $storeParams['location_id'] = $locationListIds;
            $storeList = $this->commonModel->storeList($storeParams);
            $storeListData = $this->processResult($storeList, 'id');
            if(!empty($storeListData)) {
                $response = array('status' => 'success', 'data' => $storeListData);
            }
        }
        return $response;
    }  
    
    function fetchImage($where) {
        $commonModel = new commonModel();
        $imageData = $commonModel->fetchImage($where);
        $data = array();
        if(!empty($imageData)) {
            $data = $this->processResult($imageData, 'image_id', true);
        }
        return (object)$data;
    }
    
    function addInventryByCsv($parameters) {
        $productParams = array();
        if(!empty($parameters['store_name'])) {
            $where['store_name'] = $parameters['store_name'];
            $data = $this->commonModel->storeList($where);
            if(!empty($data)){
                foreach ($data as $key => $value) {
                    $store_id = $value['id'];
                }
            }
        }
        $productParams = $parameters;
        $productParams['store_id'][] = $store_id;
        return $this->addEditInventry($productParams);  
    }
    
    public function settinglist($parameters, $optional = array()) {
        $response = array('status' => 'fail', 'msg' => 'No record found ');
        
        if(!empty($parameters['pagination'])) {
                $optional['pagination'] = $parameters['pagination'];
        }
        
        $result = $this->commonModel->settinglist($optional);
        
        if (!empty($result)) {
            $data = array();
            foreach ($result as $key => $value) {
                $data = $value;
            }
            $response = array('status' => 'success', 'data' => $data);
        }
        return $response;
    }
    
    public function saveSetting($parameters, $optional = array()) {
        $response = array('status' => 'fail', 'msg' => 'No record saved ');
        $params = array();
        $params['minimum_order'] = $parameters['minimum_order'];
        $params['free_delivery'] = $parameters['free_delivery'];
        $rule['minimum_order'] = array('type' => 'numeric', 'is_required' => true);
        $rule['free_delivery'] = array('type' => 'numeric', 'is_required' => true);
        $response = $this->isValid($rule, $params);
        if (empty($response)) {
            if (!empty($parameters['id'])) {
                $result = $this->commonModel->updateSetting($params, $parameters['id']);
            } else {
                $result = $this->commonModel->saveSetting($params);
            }
            if (!empty($result)) {
                $response = array('status' => 'success', 'msg' => 'Record Saved');
            }
        }
        return $response;
    }
    function banner($parameters) {
        $response = array('status'=>"fail", "msg"=>"No records found");
        $where = array();
        if(!empty($parameters['id'])) {
            $where['id'] = $parameters['id'];
        }
        $banner = $this->commonModel->getBanner($where);
        if(!empty($banner)) {
            $bannerData = $this->processResult($banner);
            $response = array('status'=>'success', 'data'=>$bannerData);
            $response['imageRootPath'] = HTTP_ROOT_PATH.'/banner';
        }
        
        return $response;
    }
    
    public function addeditcity($parameters) {
        $response = array('status'=>'fail','msg'=>'Nothing to save.');
        $params = array();
        $rule = array();        
        $params['country_id'] = $parameters['country_id'];
        $params['city_name'] = $parameters['city_name'];
     
        $rule['city_name'] = array('type' => 'string', 'is_required' => true);
        $rule['country_id'] = array('type' => 'numeric', 'is_required' => true);
        if (!empty($parameters['id'])){
            $params['id'] = (int) $parameters['id'];
            $rule['id'] = array('type' => 'numeric', 'is_required' => true);
        }  
        $valid = $this->isValid($rule, $params);
        
        if (empty($valid) && empty($params['id'])) {
            $params['created_on'] = date('Y-m-d H:i:s');
            $result = $this->commonModel->checkcityexist($params['city_name']);
            if(!empty($result)){
                foreach ($result as $key => $value) {
                    $count = $value['count'];
                }
            }
            if($count < 1){
                $result = $this->commonModel->savecity($params);
                if (!empty($result)) {
                    $response = array('status' => 'success', 'msg' => 'Record Saved Successfully.');
                }
            }else{
                $response = array('status' => 'false', 'msg' => 'city allready exist.');
            }
             
        }else if(empty($valid) && !empty($params['id'])){
            $params['updated_on'] = date('Y-m-d H:i:s');
            $result = $this->commonModel->updatecity($params, $params['id']);
            if (!empty($result)) {
                $response = array('status' => 'success', 'msg' => 'Record upadate Successfully.');
            }
        }
        return $response;
    }
    
    function deletecity($parameters) {
        $response = array('status' => 'fail', 'msg' => 'City Not Deleted '); 
        $rule['id'] = array('type'=>'integer', 'is_required'=>true);
        if(!empty($parameters['id'])) {
            $result = $this->commonModel->deletecity($parameters);
            if (!empty($result)) {
                $response = array('status' => 'success', 'msg' => 'city deleted ');
            }
        }        
        
        return $response;        
    }
    
    public function addedittimeslot($parameters) {
        $response = array('status'=>'fail','msg'=>'Nothing to save.');
        $params = array();
        $rule = array();        
        $params['start_time_slot'] = $parameters['start_time_slot'];
        $params['end_time_slot'] = $parameters['end_time_slot'];
     
        $rule['start_time_slot'] = array('type' => 'numeric', 'is_required' => true);
        $rule['end_time_slot'] = array('type' => 'numeric', 'is_required' => true);
        if (!empty($parameters['id'])){
            $params['id'] = (int) $parameters['id'];
            $rule['id'] = array('type' => 'numeric', 'is_required' => true);
        }  
        $valid = $this->isValid($rule, $params);
        
        if (empty($valid) && empty($params['id'])) {
            $params['created_on'] = date('Y-m-d H:i:s');
            $slotAvl = $this->checkDeliverySlotAvailable($params);
            
            if($slotAvl){
                $result = $this->commonModel->savetimeslot($params);
                if (!empty($result)) {
                    $response = array('status' => 'success', 'msg' => 'Record Saved Successfully.');
                }
            }else{
                $response = array('status' => 'false', 'msg' => 'time slot allready exist.');
            }
             
        }else if(empty($valid) && !empty($params['id'])){
            $params['updated_on'] = date('Y-m-d H:i:s');
            $slotAvl = $this->checkDeliverySlotAvailable($params);
            if($slotAvl){
                $result = $this->commonModel->updatetimeslot($params, $params['id']);
                if (!empty($result)) {
                    $response = array('status' => 'success', 'msg' => 'Record upadate Successfully.');
                }
            }else{
                $response = array('status' => 'false', 'msg' => 'time slot allready exist.');
            } 
        }
        return $response;
    }
    
    public function deliveryTimeSlotList($parameters, $optional = array()) {
        $response = array('status' => 'fail', 'msg' => 'No record found ');
        if(!empty($parameters['id'])){
            $optional['id'] = $parameters['id'];
        }
        
        if(!empty($parameters['pagination'])) {
                $optional['pagination'] = $parameters['pagination'];
        }
        
        $result = $this->commonModel->deliveryTimeSlotList($optional);
        
        if (!empty($result)) {
            $data = array();
            $today = date('Y-m-d');
            $tomorrow = date('Y-m-d', time()+86400);
            $dateWiseTimeSlot = array();
            $hour = date('H');
            foreach ($result as $key => $value) {
                $data[$value['id']] = $value;
                $dateWiseTimeSlot[$tomorrow][$value['id']] = $value;
                if($value['start_time_slot'] > $hour) {
                    $dateWiseTimeSlot[$today][$value['id']] = $value;
                }
            }
            $response = array('status' => 'success', 'data' => $data, 'datewisetimeslot'=>$dateWiseTimeSlot, 'response_time'=>date('Y-m-d H:i:s'));
        }
        return $response;
    }
    
    function deletetimeslot($parameters) {
        $response = array('status' => 'fail', 'msg' => 'time slot Not Deleted '); 
        $rule['id'] = array('type'=>'integer', 'is_required'=>true);
        if(!empty($parameters['id'])) {
            $result = $this->commonModel->deletetimeslot($parameters);
            if (!empty($result)) {
                $response = array('status' => 'success', 'msg' => 'time slot  deleted ');
            }
        }        
        
        return $response;        
    }
    
    function checkDeliverySlotAvailable($params) {
        $slotAvl = TRUE;
        $result = $this->commonModel->deliveryTimeSlotList();
        
        if (!empty($result)) {
            foreach ($result as $key => $value) {
                if ($value['start_time_slot'] == $params['start_time_slot'] || $value['end_time_slot'] == $params['end_time_slot']) {
                    $slotAvl = FALSE;
                    break;
                }
                if ($params['start_time_slot'] > $value['start_time_slot'] && $params['start_time_slot'] < $value['end_time_slot']) {
                    $slotAvl = FALSE;
                    break;
                }
                if ($params['end_time_slot'] > $value['start_time_slot'] && $params['end_time_slot'] < $value['end_time_slot']) {
                    $slotAvl = FALSE;
                    break;
                }
            }
        }
        return $slotAvl;
    }
    
    function riderLogin($parameters) {
        $response = array('status'=>'fail','msg'=>'Invalid credentials');
        $status = true;
        $where = array();
        if(!empty($parameters['email'])) {
            $where['email'] = isset($parameters['email'])?$parameters['email']:'';
            //$where['mobile_number'] = isset($parameters['mobile_number'])?$parameters['mobile_number']:'';
        }else{
            $status = false;
            $response = array('status'=>'fail','msg'=>'Email not supplied');
        }
        if(!empty($parameters['password'])) {
            $where['password'] = md5($parameters['password']);
        }else{
            $status = false;
            $response = array('status'=>'fail','msg'=>'Password not supplied');
        }        
        if(!empty($parameters['fcm_reg_id'])) {
            $params['fcm_reg_id'] = $parameters['fcm_reg_id'];
        }else{
            $status = false;
            $response = array('status'=>'fail','msg'=>'Fcm Register id not supplied');
        }        
        if($status) {
            $riderDetails = $this->riderList($where);
            if(!empty($riderDetails['data'])) {
                $response = $riderDetails;
                $riderData = array_values($riderDetails['data']);
                $params['id'] = $riderData[0]['id'];
                $this->addEditRider($params);
            }
        }
        
        return $response;
    }
    
    public function addEditBanner($parameters , $optional =array()) {
        $response = array('status'=>'fail','msg'=>'fail ');
        
        if(!empty($parameters['id'])){
            $data['status'] = $parameters['status'];
            $data['link'] = $parameters['link'];
            if(!empty($parameters['description'])){
                $data['description'] = $parameters['description'];
            }
            $where = $parameters['id'];
            $this->commonModel->updateBanner($data,$where);
            $result = $parameters['id'];
        }else {       
            $data['link'] = $parameters['link'];
            $data['status'] = $parameters['status'];
            if(!empty($parameters['description'])){
                $data['description'] = $parameters['description'];
            }
            
            $result = $this->commonModel->addBanner($data);
        }
        if(!empty($result)){
            if(!empty($parameters['image'])) {
                $imageParams = array();
                $imageParams['type'] = 'banner';
                $imageParams['imageType'] = "string";
                $imageParams['id'] = $result;
                $imageParams['imageData'] = $parameters['image'];
                $imageParams['make_id_wise_folder'] = 'no';
                $imageResponse = $this->uploadImage($imageParams);
                $where = $result;
                $newdata = array();
                $newdata['image_name'] = $imageResponse['imagename'];
                $commonModel = new commonModel();
                $commonModel->updateBanner($newdata,$where);
            }            
            $response = array('status'=>'success','msg'=>'banner Data Saved');
        }
        return $response;   
    }

    function getMerchantProductDetail($parameters) {
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
        $getMerchant = $this->commonModel->getMerchantCount($whereParams, $optional);
        $MerchantByDate = $this->processResult($getMerchant, 'created_date');
        $data = array('merchantByDate'=>$MerchantByDate);
        
        $response = array('status'=>'success', 'data'=>$data);        
        return $response;
    }
    
    function getTotalNumberOfProductAndMerchant($optional = array()) {
        $data = array();
        $data['totalNumberOfMerchant'] = $this->getMerchantCount();
        $data['totalNumberOfProduct'] = $this->getProductCount();
        
        return array('status'=>'success', 'data'=>$data);
    }
            
    function getMerchantCount() {
        $getTotalMerchant = $this->commonModel->getMerchantCount();
        $merchantDetails = $getTotalMerchant->current();
        $totalMerchant = $merchantDetails['count'];        
        return $totalMerchant;
    }
    
    function getProductCount() {
        $optional = array();
        $optional['onlyProductDetails'] = 1;
        $productResponse = $this->commonModel->getProductListCount($optional);
        $productDetails = $productResponse->current();
        $totalNumberOfProduct = $productDetails['count'];        
        
        return $totalNumberOfProduct;
    }
}
