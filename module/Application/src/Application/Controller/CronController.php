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

class CronController extends AbstractActionController {
    var $cronLib;
    var $customerLib;
    public function __construct() {
        $this->cronLib = new cron();
    }
    public function sendnotificationAction() {
        $response = $this->cronLib->sendNotification();
        $this->cronLib->sendSms();
        echo json_encode($response);
        exit;
    }
    public function updatepaymentstatusAction(){
        if(!empty($_REQUEST['TransactionId'])) {
            $response = array('status' => 'fail', 'msg' => 'Payment Failed.');
            $this->customerLib = new customer();
            $response = $this->customerLib->updatePaymentStatus($_REQUEST);
        }
        echo json_encode($_REQUEST);
        exit;
    }
}
