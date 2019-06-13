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
        $response = $this->cronLib->sendNotification();
        $this->cronLib->sendSms();
        echo json_encode($response);
        exit;
    }
    public function updatepaymentstatusAction(){
        $response = array('status' => 'fail', 'msg' => 'Payment Failed.');
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
            header('Location:'.FRONT_END_PATH.$msg);
        }   
        ?>
<script type="text/javascript">
    var agent = "<?php echo $_REQUEST['agent']?>" ; 
    //if(agent == 'a'){
        window.location = "myapp://com.afrobaskets:failed:0:0:0:0";
    //}
    if(agent == 'i'){
        window.location =  "myapp://com.afrobasket.app:failed:0:0:0:0";
    }   
</script>

<?php
        $requestParams = json_encode($_REQUEST);
        $responseStr = json_encode($response);
        echo $responseStr;
        $logText = $requestParams."\n Response :- \n".$responseStr;  
        $this->commonLib->writeDebugLog($logText, 'cron', 'updatepaymentstatus');
        exit;
    }
}
