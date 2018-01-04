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
use Application\Library\customer;

class IndexController extends AbstractActionController {

    public function __construct() {
        $this->commonLib = new common();
    }

    public function indexAction() {
        $response = array('status' => 'fail', 'msg' => 'Method not supplied ');
        $parameters = trim($_REQUEST['parameters'], "\"");
        $parameters = json_decode($parameters,true);
        if (!empty($parameters['method'])) {
            switch ($parameters['method']) {
                case 'addEditCategory':
                    $params = array();
                    $optional = array();
                    $params['category_name'] = $parameters['category_name'];
                    $params['parent_category_id'] = !empty($parameters['parent_category_id']) ? $parameters['parent_category_id']:0;
                    $params['category_des'] = !empty($parameters['category_des']) ? $parameters['category_des'] : '';
                    if (!empty($parameters['id'])) {
                        $params['id'] = $parameters['id'];
                    }
                    if (!empty($parameters['image'])) {
                        $optional['image'] = $parameters['image'];
                    }
                    $response = $this->commonLib->addEditCategory($params,$optional);
                    break;

                case 'addEditProduct':
                    $response = $this->commonLib->addEditProduct($parameters);
                    break;
                    
                case 'categoryList':
                    $response = $this->commonLib->categoryList($parameters );
                    break;
                case 'getMarchantList':
                    $response = $this->commonLib->getMarchantList($parameters);
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
                case 'getRidersByStoreId':
                    $response = $this->commonLib->getRidersByStoreId($parameters);
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
                case 'addEditStore':
                    $response = $this->commonLib->addEditStore($parameters );
                    break;
                case 'storeList':
                    $response = $this->commonLib->storeList($parameters );
                    break;
                case 'deleteStore':
                    $response = $this->commonLib->deleteStore($parameters );
                    break;
                case 'addEditInventry':
                    $response = $this->commonLib->addEditInventry($parameters );
                    break;
                case 'stockList':
                    $response = $this->commonLib->stockList($parameters );
                    break;
                case 'addProductByCsv':
                    $response = $this->commonLib->addProductByCsv($parameters);
                    break;
                case 'cityList':
                    $response = $this->commonLib->cityList($parameters);
                    break;
                case 'countryList':
                    $response = $this->commonLib->countryList($parameters);
                    break;
                case 'addInventryByCsv':
                    $response = $this->commonLib->addInventryByCsv($parameters);
                    break;
                case 'settinglist':
                    $response = $this->commonLib->settinglist($parameters);
                    break;
                case 'saveSetting':
                    $response = $this->commonLib->saveSetting($parameters);
                    break;
                case 'banner':
                    $response = $this->commonLib->banner($parameters);
                    break; 
                case 'addeditcity':
                    $response = $this->commonLib->addeditcity($parameters);
                    break;
                case 'deletecity':
                    $response = $this->commonLib->deletecity($parameters);
                    break;
                case 'addedittimeslot':
                    $response = $this->commonLib->addedittimeslot($parameters);
                    break;
                case 'deliveryTimeSlotList':
                    $response = $this->commonLib->deliveryTimeSlotList($parameters);
                    break;
                case 'deletetimeslot':
                    $response = $this->commonLib->deletetimeslot($parameters);
                    break;
                case 'riderlogin':
                    $response = $this->commonLib->riderLogin($parameters);
                    break; 
                case 'addEditBanner':
                    $response = $this->commonLib->addEditBanner($parameters);
                    break;
            }
        }
            echo json_encode($response);
            exit;
    }
}
