<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Application\Library\common;
use Zend\Mail;

class IndexController extends AbstractActionController {

    public function __construct() {
        $this->commonLib = new common();
    }

    public function indexAction() {
        $response = array('status' => 'fail', 'msg' => 'Method not supplied ');
        $parameters = $this->getRequest()->getQuery('parameters');
        $parameters = json_decode($parameters,true);
        if (!empty($parameters['method'])) {
            switch ($parameters['method']) {
                case 'addEditCategory':
                    $params = array();
                    $params['category_name'] = $parameters['category_name'];
                    $params['parent_category_id'] = !empty($parameters['parent_category_id']) ? $parameters['parent_category_id']:0;
                    $params['category_des'] = !empty($parameters['category_des']) ? $parameters['category_des'] : '';
                    if (!empty($parameters['id'])) {
                        $params['id'] = $parameters['id'];
                    }

                    $response = $this->commonLib->addEditCategory($params);
                    break;

                case 'addEditProduct':
                    $response = $this->commonLib->addEditProduct($parameters);
                    break;
                    
                case 'categoryList':
                    $optional = array();
                    if (!empty($parameters['id'])) {
                        $optional['id'] = $parameters['id'];
                    }

                    $response = $this->commonLib->categoryList($parameters ,$optional );
                    break;
                case 'getMarchantList':
                    $option = array();
                    if (!empty($parameters['id'])) {
                        $option['id'] = $parameters['id'];
                    }
                    $response = $this->commonLib->getMarchantList($parameters ,$option );
                    break;
                    
                case 'addEditLocation':
                    $response = $this->commonLib->addEditLocation($parameters);
                    break;
                case 'getLocationList':
                    $response = $this->commonLib->getLocationList($parameters);
                    break; 
                case 'getProductList':
                    $response = $this->commonLib->getProductList($parameters);
                    break;
                case 'deleteCategory':
                    $response = $this->commonLib->deleteCategory($parameters);
                    break; 
                case 'addEditRider':
                    $response = $this->commonLib->addEditRider($parameters);
                    break;                 
                case 'getRiderList':
                    $response = $this->commonLib->riderList($parameters);
                    break;                 
                case 'saveMerchant':
                    $response = $this->commonLib->saveMerchant($parameters);
                    break;
                case 'addedittax':
                    $response = $this->commonLib->addedittax($parameters);
                    break;  
                case 'taxlist':
                    $option = array();
                    if (!empty($parameters['id'])) {
                        $option['id'] = $parameters['id'];
                    }
                    $response = $this->commonLib->taxlist($parameters ,$option );
                    break;
                case 'deletetax':
                    $response = $this->commonLib->deletetax($parameters );
                    break;
                    
            }
            
            echo json_encode($response);
            exit;
        }
        echo json_encode($response);
        exit;
    }

}
