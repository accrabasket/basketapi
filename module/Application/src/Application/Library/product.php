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
            $storeParams['city_id'] = $parameters['city_id'];
            $storeList = $this->commonLib->getStoreByCity($storeParams);
            if(!empty($storeList['data']))
            $optional['store_id'] = array_keys ($storeList['data']);
        }        
        if (!empty($parameters['pagination'])) {
            $optional['pagination'] = $parameters['pagination'];
            $optional['page'] = !empty($parameters['page']) ? $parameters['page'] : 1;
        }
        $result = $this->productModel->productList($optional);
        if (!empty($result)) {
            $productData = $this->commonLib->processResult($result, 'product_id');
            if (!empty($productData)) {
                $getattribute = $this->commonModel->getAttributeList(array('product_id' => array_keys($productData)));
                $attdata = $this->commonLib->processResult($getattribute, 'id');
                $minPriceParams = array();
                $minPriceParams['attribute_id'] = array_keys($attdata);
                if(!empty($optional['store_id'])) {
                    $minPriceParams['store_id'] = $optional['store_id'];
                }
                $prodcutAttribute = $this->getMinPriceProductAttribute($minPriceParams, $attdata);
                $productDetaList = $this->prepareProductWiseAttribute($productData, $prodcutAttribute);
                $response = array('status' => 'success', 'data' => $productDetaList);
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
    function getMinPriceProductAttribute($parameters, $attributeDetail) {
        $data = $this->productModel->getMinPriceProductAttribute($parameters);
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

}
