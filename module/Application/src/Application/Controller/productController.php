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
use Application\Library\product;
use Zend\Mail;

class ProductController extends AbstractActionController {

    public function __construct() {
        $this->productLib = new product();
    }
    public function indexAction() {
        $response = array('status' => 'fail', 'msg' => 'Method not supplied ');
        $parameters = $this->getRequest()->getQuery('parameters');
        $parameters = json_decode($parameters, true);
        if (!empty($parameters['method'])) {
            switch ($parameters['method']) {
                case 'productlist':
                    $response = $this->productLib->getProductList($parameters);
                    break;
            }

            echo json_encode($response);
            exit;
        }
        echo json_encode($response);
        exit;
    }

}
