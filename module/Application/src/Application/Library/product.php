<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Application\Library;
use Application\Library\common;
use Application\Model\commonModel;
use Application\Model\productModel;
class product {

    public function __construct() {
        $this->commonLib = new common;
        $this->commonModel = new commonModel();
        $this->productModel = new productModel();
    }
    function getProductList($parameters) {
        $response = array('status' => 'fail', 'msg' => 'No record found ');
        $optional = array();
        if (!empty($parameters['id'])){
            $optional['id'] = $parameters['id'];
        }
        if (!empty($parameters['city_id'])){
            $storeParams = array();
            $optional['store_id'][]= 0;
            $storeParams['city_id'] = $parameters['city_id'];
            $storeList = $this->commonLib->getStoreByCity($storeParams);
            if(!empty($storeList['data'])) {
                $optional['store_id'] = array_keys($storeList['data']);
            }
        }   
        if(!empty($parameters['product_name'])) {
            $optional['product_name'] = $parameters['product_name'];
        }
        if(!empty($optional['category_name'])) {
            $parameters['product_name'] = $parameters['category_name'];
        }        
        if (!empty($parameters['merchant_id'])){
            $optional['merchant_id'] = $parameters['merchant_id'];
        }     
        $categoryParams = array();
        if (!empty($parameters['category_name'])){
            $categoryParams['category_name'] = $parameters['category_name'];
        }  
        if (!empty($parameters['category_id'])){
            $categoryParams['parent_category_id'] = $parameters['category_id'];
        }        
        if(!empty($categoryParams)) {
            $categoryParams['columns'] = array(new \Zend\Db\Sql\Expression('category_master.id as id'));
            $categoryData = $this->commonLib->categoryList($categoryParams);
        }
        if(!empty($categoryData['data'])) {
            $optional['category_id'] = array_keys($categoryData['data']);
        }
        if (!empty($parameters['category_id'])){
            $optional['category_id'][] = $parameters['category_id'];         
        }
        if (!empty($parameters['pagination'])) {
            $optional['pagination'] = $parameters['pagination'];
            $optional['page'] = !empty($parameters['page']) ? $parameters['page'] : 1;
        }
        $result = $this->productModel->productList($optional);
        $attributeImageData = array();
        if (!empty($result)) {
            $productData = $this->commonLib->processResult($result, 'product_id', false, true);
            if (!empty($productData)) {
                $getattribute = $this->commonModel->getAttributeList(array('product_id' => array_keys($productData)));
                $attdata = $this->commonLib->processResult($getattribute, 'id');
                if(!empty($attdata)) {
                    $attrImageWhere = array();
                    $attrImageWhere['image_id'] = array_keys($attdata);
                    $attrImageWhere['type'] = 'attribute';
                    $attributeImageData = $this->commonLib->fetchImage($attrImageWhere);                
                }
                $productImageWhere = array();
                $productImageWhere['image_id'] = array_keys($productData);
                $productImageWhere['type'] = 'product';
                $commonModel = new commonModel();
                $productImageData = $this->commonLib->fetchImage($productImageWhere);                                
                $minPriceParams = array();
                $minPriceParams['attribute_id'] = array_keys($attdata);
                if(!empty($optional['store_id'])) {
                    $minPriceParams['store_id'] = $optional['store_id'];
                }
                if (!empty($parameters['merchant_id'])){
                    $minPriceParams['merchant_id'] = $parameters['merchant_id'];
                }                        
                $prodcutAttribute = $this->getMerchantProductAttribute($minPriceParams, $attdata);
                $productDetaList = $this->prepareProductWiseAttribute($productData, $prodcutAttribute);
                $response = array('status' => 'success', 'data' => $productDetaList, 'attributeImageData'=>$attributeImageData, 'productImageData'=>$productImageData, 'imageRootPath'=>HTTP_ROOT_PATH);
            }
        }
        return $response;
    }
    
    function prepareProductWiseAttribute($productData, $productAttribute) {
        $productDetaList= array();
        foreach ($productData as $key=>$productDetails) {
            $productDetaList[$key] = $productDetails;
            $productDetaList[$key]['attribute'] = $productAttribute[$key];
        }
        return $productDetaList;
    }
    function getMerchantProductAttribute($parameters, $attributeDetail) {
        $data = $this->productModel->getMerchantProductAttribute($parameters);
        $attributeByProduct = array();
        if(!empty($data)) {
            foreach($data as $row) {
                if(empty($attributeByProduct[$row['product_id']][$row['attribute_id']])) {
                    $attributeByProduct[$row['product_id']][$row['attribute_id']] = $row;
                    $attributeByProduct[$row['product_id']][$row['attribute_id']]['attribute_name'] = $attributeDetail[$row['attribute_id']]['name'];
                    $attributeByProduct[$row['product_id']][$row['attribute_id']]['discount_type'] = $attributeDetail[$row['attribute_id']]['discount_type'];
                    $attributeByProduct[$row['product_id']][$row['attribute_id']]['discount_value'] = $attributeDetail[$row['attribute_id']]['discount_value'];
                    $attributeByProduct[$row['product_id']][$row['attribute_id']]['unit'] = $attributeDetail[$row['attribute_id']]['unit'];
                }else if($attributeByProduct[$row['product_id']][$row['attribute_id']]['price']>$row['price']) {
                    $attributeByProduct[$row['product_id']][$row['attribute_id']] = $row;
                    $attributeByProduct[$row['product_id']][$row['attribute_id']]['attribute_name'] = $attributeDetail[$row['attribute_id']]['name'];
                    $attributeByProduct[$row['product_id']][$row['attribute_id']]['discount_type'] = $attributeDetail[$row['attribute_id']]['discount_type'];
                    $attributeByProduct[$row['product_id']][$row['attribute_id']]['discount_value'] = $attributeDetail[$row['attribute_id']]['discount_value'];
                    $attributeByProduct[$row['product_id']][$row['attribute_id']]['unit'] = $attributeDetail[$row['attribute_id']]['unit'];                    
                }
            }
        }
        return $attributeByProduct;
    }
    
    function getProductByMerchantAttributeId($parameters) {
        $response = array('status' => 'fail', 'msg' => 'No record found ');
        $optional = array();
        if(!empty($parameters['merchant_inventry_id'])) {
            $optional['merchant_inventry_id'] = $parameters['merchant_inventry_id'];
            $data = $this->productModel->productList($optional);
            $productData = $this->commonLib->processResult($data, 'id');
            if(!empty($productData)){
                $dataByProductId = $this->commonLib->processResult($productData, 'product_id');
                $productImageWhere = array();
                $productImageWhere['image_id'] = array_keys($dataByProductId);
                $productImageWhere['type'] = 'product';
                $commonModel = new commonModel();
                $productImageData = $this->commonLib->fetchImage($productImageWhere);                
                $response = array('status'=>'success', 'data'=>$productData, 'productImageData'=>$productImageData);
            }
        }
        
        return $response;
    }

}