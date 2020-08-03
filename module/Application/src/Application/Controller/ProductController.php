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
        $this->checkRqid();
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
                case 'brandlist':
                    $response = $this->productLib->brandlist($parameters);
                    break;                
                case 'getProductByMerchantAttributeId':
                    $response = $this->productLib->getProductByMerchantAttributeId($parameters);
                    break;
                case 'cashCollected':
                    $response = $this->productLib->cashCollected($parameters);
                    break;
                case 'notifyproduct':
                    $response = $this->productLib->notifyproduct($parameters);
                    break;    
                case 'notifiedproductlist':
                    $response = $this->productLib->getNotifiedProductList($parameters);
            }
        }
        $responseStr = json_encode($response);
        echo $responseStr;
        $logText = $requestParams."\n Response :- \n".$responseStr;            
        $this->commonLib->writeDebugLog($logText, 'product', $parameters['method']);
        exit;
    }
    
    function checkRqid() {
        $rqid = hash('sha512', SECURE_KEY.$_REQUEST['parameters']);
        if($rqid != $_REQUEST['rqid']){
           // echo json_encode(array('status'=>"fail", "msg"=>"rqid not match"));
           // exit;
        }
    }
}
