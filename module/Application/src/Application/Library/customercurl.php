<?php
namespace Application\Library;
use Application\Model\customercurlModel;
use Application\Model\commonModel;
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
    
    function getStoreListById($parameters){
        $response = array('status'=>'fail', 'msg'=>'No Record Found');
        $where = array();
        $where['id'] = $parameters['id'];
        $storeList = $this->customerCurlModel->getStoreListById($where);
        $storeListData = $this->processResult($storeList, 'id');
        if(!empty($storeListData)) {
            $response = array('status'=>'success', 'data'=>$storeListData);
        }
        
        return $response;
    }
    public function deliveryTimeSlotList($parameters, $optional = array()) {
        $commonModel = new commonModel();
        $response = array('status' => 'fail', 'msg' => 'No record found ');
        if(!empty($parameters['id'])){
            $optional['id'] = $parameters['id'];
        }
        
        if(!empty($parameters['pagination'])) {
                $optional['pagination'] = $parameters['pagination'];
        }
        
        $result = $commonModel->deliveryTimeSlotList($optional);
        if (!empty($result)) {
            $data = array();
            foreach ($result as $key => $value) {
                $data[$value['id']] = $value;
            }
            $response = array('status' => 'success', 'data' => $data);
        }
        return $response;
    }    
    
    function riderList($parameters) {
        $response = array('status' => 'fail', 'msg' => 'No record found ');
        $where = array();
        if(!empty($parameters['rider_id'])) {
            $where['id'] = $parameters['rider_id'];
        }     
        if(!empty($where)){
            $riderData = $this->customerCurlModel->getRiderList($where);
            $data = $this->processResult($riderData, 'id');
            $response = array('status'=>'success', 'data'=>$data);
        }
        
        return $response;
    }
    
    function getMarchantList($optional) {
        $commonModel = new commonModel();
        $merchantList = $commonModel->getMarchantList($optional);
        $result = $this->processResult($merchantList, 'id');
        
        return $result;
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
    
    function updateInventry($params, $where) {
        return $this->customerCurlModel->updateInventry($params, $where);
    }
}