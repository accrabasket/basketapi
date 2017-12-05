<?php
namespace Application\Library;
use Application\Model\customercurlModel;
use Application\Library\customer;
class customercurl {
    public function __construct() {
        $this->customerCurlModel = new customercurlModel();
    }
    function getProductByMerchantAttributeId($parameters) {
        $response = array();
        $optional = array();
        if(!empty($parameters['merchant_inventry_id'])) {
            $optional['merchant_inventry_id'] = $parameters['merchant_inventry_id'];
            $data = $this->customerCurlModel->productList($optional);
            $productData = $this->processResult($data, 'id');
            if(!empty($productData)){
                $dataByProductId = $this->processResult($productData, 'product_id');
                $productImageWhere = array();
                $productImageWhere['image_id'] = array_keys($dataByProductId);
                $productImageWhere['type'] = 'product';
                $productImageData = $this->fetchImage($productImageWhere);                
                $response = array('status'=>'success', 'data'=>$productData, 'productImageData'=>$productImageData);
            }
        }
        return $response;
    }
    function fetchImage($where) {
        $imageData = $this->customerCurlModel->fetchImage($where);
        $data = array();
        if(!empty($imageData)) {
            $data = $this->processResult($imageData, 'image_id', true);
        }
        return $data;
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