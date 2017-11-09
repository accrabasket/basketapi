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
                $response = array('status'=>'succes','msg'=>'category updated ');
            }
            return $response;
        }
        
        $result = $this->commonModel->addCategory($parameters);
        if(!empty($result)){
                $response = array('status'=>'succes','msg'=>'category created ');
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
            $response = array('status' => 'succes', 'msg' => 'product updated ');
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
                $response = array('status' => 'succes', 'data' => $data);
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
            $response = array('status' => 'succes', 'data' => $data);
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
            $response = array('status' => 'succes', 'data' => $data);
        }
        return $response;
    }

}
