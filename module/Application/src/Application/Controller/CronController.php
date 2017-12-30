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

class CronController extends AbstractActionController {
    var $cronLib;
    public function __construct() {
        $this->cronLib = new cron();
    }
    public function sendnotificationAction() {
        $response = $this->cronLib->sendNotification();
        echo json_encode($response);
        exit;
    }

}
