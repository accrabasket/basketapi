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

class WalletController extends AbstractActionController {
    protected $walletLib;
    public $commonLib;
    public $rqid;
    public function __construct() {
        $this->commonLib = new \Application\Library\common();
        $this->walletLib = new \Application\Library\wallet();
        $this->rqid = $this->checkRqid();
    }
    public function indexAction() {
        $response = array('status' => 'fail', 'msg' => 'Method not supplied ');
        $requestParams = $parameters = trim($_REQUEST['parameters'], "\"");
        $parameters = json_decode($parameters, true);
        if (!empty($parameters['method'])) {
            if($this->rqidp['status'] == 'success') {
                switch ($parameters['method']) {
                    case 'creditdebitToWallet':
                        $response = $this->walletLib->creditdebitToWallet($parameters);
                        break;
                }
            }else {
                $response = $this->rqid;
            }
        }
        $responseStr = json_encode($response);
        echo $responseStr;
        $logText = $requestParams."\n\n Response :- \n\n\n".$responseStr;            
        $this->commonLib->writeDebugLog($logText, 'product', $parameters['method']);
        exit;
    }
    
    function checkRqid() {
        $rqid = hash('sha512', SECURE_KEY.$_REQUEST['parameters']);
        if($rqid != $_REQUEST['rqid']){
            return (array('status'=>"fail", "msg"=>"rqid not match"));
        }else {
            return array('status'=>"success");
        }      
    }
}
