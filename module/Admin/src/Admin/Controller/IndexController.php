<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Admin\Model\School;

class IndexController extends AbstractActionController {

    var $userObj;
    protected $storage;
    protected $authservice;

    public function __construct($modelobj) {
		$this->userObj = $modelobj;
    }
    
    public function indexAction() {
		$data = $this->getRequest()->getQuery();
		
    }
    
    public function success($data = null){
        $data['status'] = "success";
        echo json_encode($data);
        die;
    }
    private function failure($data = null){
        $data['status'] = "fail";
        echo json_encode($data);
        die;
    }
}
