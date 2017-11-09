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
                    $params = array();
                    $params['product_name'] = !empty($parameters['product_name']) ? $parameters['product_name'] : '';
                    $params['category_id'] = !empty($parameters['category_id']) ? $parameters['category_id']:'';
                    if (!empty($parameters['id'])) {
                        $params['id'] = $parameters['id'];
                    }
                    $params['status'] = $parameters['status'];
                    $response = $this->commonLib->addEditProduct($params,$parameters);
                    break;
                    
                case 'categoryList':
                    $option = array();
                    if (!empty($parameters['id'])) {
                        $option['id'] = $parameters['id'];
                    }

                    $response = $this->commonLib->categoryList($parameters ,$option );
                    break;
                case 'getMarchantList':
                    $option = array();
                    if (!empty($parameters['id'])) {
                        $option['id'] = $parameters['id'];
                    }
                    $response = $this->commonLib->getMarchantList($parameters ,$option );
                    break;
            }
            echo json_encode($response);
            exit;
        }
        echo json_encode($response);
        exit;
    }

}
