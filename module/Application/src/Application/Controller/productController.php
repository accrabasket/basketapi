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
use Application\Library\product;
use Zend\Mail;

class ProductController extends AbstractActionController {

    public function __construct() {
        $this->commonLib = new common();
    }

    public function indexAction() {
        $response = array('status' => 'fail', 'msg' => 'Method not supplied ');
        $parameters = $this->getRequest()->getQuery('parameters');
        $parameters = json_decode($parameters,true);
        if (!empty($parameters['method'])) {
            switch ($parameters['method']) {
                case 'citylist':
                    $response = $this->commonLib->getCityList($parameters);
                    break;
                    
                case 'categoryList':
                    $response = $this->commonLib->categoryList($parameters );
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
