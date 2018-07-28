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
    protected $productLib;
    public $commonLib;
    public function __construct() {
        $this->productLib = new product();
        $this->commonLib = new \Application\Library\common();
    }
    public function indexAction() {
        $response = array('status' => 'fail', 'msg' => 'Method not supplied ');
        $requestParams = $parameters = trim($_REQUEST['parameters'], "\"");
        $parameters = json_decode($parameters, true);
        if (!empty($parameters['method'])) {
            switch ($parameters['method']) {
                case 'productlist':
                    $response = $this->productLib->getProductList($parameters);
                    break;
                case 'getProductByMerchantAttributeId':
                    $response = $this->productLib->getProductByMerchantAttributeId($parameters);
                    break;
            }
        }
        $responseStr = json_encode($response);
        echo $responseStr;
        $logText = $requestParams."\n Response :- \n".$responseStr;            
        $this->commonLib->writeDebugLog($logText, 'product', $parameters['method']);
        exit;
    }

}
