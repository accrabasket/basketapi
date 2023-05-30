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
use Application\Library\cron;
use Zend\Mail;
use Application\Library\customer;
use Application\Library\common;

class CronController extends AbstractActionController {
    var $cronLib;
    var $customerLib;
    var $commonLib;
    public function __construct() {
        $this->cronLib = new cron();
        $this->commonLib = new common();
    }
    public function sendnotificationAction() {
//	print_r($_SERVER);die;
        $response = $this->cronLib->sendNotification();
        $this->cronLib->sendSms();
        echo json_encode($response);
        exit;
    }
    public function updatepaymentstatusAction(){
        $response = array('status' => 'fail', 'msg' => 'Payment Failed.');
       $entityBody = file_get_contents('php://input');
       $result = json_decode($entityBody, true);
       if(!empty($result['InvoiceNo'])) {
           $_REQUEST['TransactionId'] = $result['InvoiceNo'];
       }
        if(!empty($_REQUEST['InvoiceNo'])) {
            $_REQUEST['TransactionId'] = $_REQUEST['InvoiceNo'];
        }
        if(!empty($_REQUEST['TransactionId'])) {
            $this->customerLib = new customer();
            $response = $this->customerLib->updatePaymentStatus($_REQUEST);
        }
        echo $response['msg'];
        $msg = '';
        if($_REQUEST['agent'] =='w') {
            $msg = '?msg=Payment Not Received.';
            if($response['status'] == 'success') {
                $msg = '?msg=Payment Received.';
				
				
            }
		 
		 //	header('Location:myapp://com.afrobaskets:sucess:0:0:0:0');
  		header('Location:'.FRONT_END_PATH.$msg);
        
		} else{
		
			//header('Location:myapp://com.afrobaskets:failed:0:0:0:0');
		
		}
		
        ?>

<?php
        $requestParams = json_encode($_REQUEST);
        $responseStr = json_encode($response);
        echo $responseStr;
        //$logText = $requestParams."\n Response :- \n".$responseStr."boyd".$entityBody;  
       // $this->commonLib->writeDebugLog($logText, 'cron', 'updatepaymentstatus');
     //   exit;
//header('Location:myapp://com.afrobaskets:sucess:0:0:0:0');
exit;
    }
}
