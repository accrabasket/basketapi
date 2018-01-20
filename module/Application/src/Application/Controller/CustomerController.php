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
use Application\Library\customer;
use Zend\Mail;

class CustomerController extends AbstractActionController {

    public function __construct() {
        $this->customerLib = new customer();
    }
    public function indexAction() {
        $response = array('status' => 'fail', 'msg' => 'Method not supplied ');
        $parameters = trim($_REQUEST['parameters'], "\"");
        $parameters = json_decode($parameters, true);
        if (!empty($parameters['method'])) {
            switch ($parameters['method']) {
                case 'addtocart':
                    $response = $this->customerLib->addtocart($parameters);
                    break;
                case 'updatecart':
                    $response = $this->customerLib->updateCart($parameters);
                    break;
                case "getitemintocart":
                    $response = $this->customerLib->getItemIntoCart($parameters);
                    break;  
                case 'addedituser':
                    $response = $this->customerLib->addEditUser($parameters);
                    break;
                case 'login':
                    $response = $this->customerLib->login($parameters);
                    break;                
                case 'addeditdeliveryaddress':
                    $response = $this->customerLib->addEditDeleveryAddress($parameters);
                    break;                
                case 'getaddresslist':
                    $response = $this->customerLib->getAddressList($parameters);
                    break;   
                case 'checkout':
                    $response = $this->customerLib->checkout($parameters);
                    break;                
                case 'placeorder':
                    $response = $this->customerLib->placeOrder($parameters);
                    break;
                case 'orderlist':
                    $response = $this->customerLib->orderList($parameters);
                    break;
                case 'assignedordertorider':
                    $response = $this->customerLib->getAssignedOrderToRider($parameters);
                    break;
                case 'assignordertorider':
                    $response = $this->customerLib->assignOrderToRider($parameters);
                    break;                
                case 'generateotp':
                    $response = $this->customerLib->generateotp($parameters);
                    break;
                case 'verifyotp':
                    $response = $this->customerLib->verifyotp($parameters);
                    break;
                case 'forgetpassword':
                    $response = $this->customerLib->forgetpassword($parameters);
                    break;
                case 'changepassword':
                    $response = $this->customerLib->changepassword($parameters);
                    break;
                case 'updateorderbyrider':
                    $parameters['role'] = 'rider';
                    $response = $this->customerLib->updateOrderByRider($parameters);
                    break;       
                case 'updateOrderstatus':
                    $response = $this->customerLib->updateOrderStatus($parameters);
                    break;
                case 'ledgersummery':
                    $response = $this->customerLib->ledgersummery($parameters);
                    break;
                case 'paytomerchant':
                    $response = $this->customerLib->PayToMerchant($parameters);
                    break;                
                case 'getcustomersaledetail':
                    $response = $this->customerLib->getCustomerSalesDetails($parameters);
                    break;                
            }

            echo json_encode($response);
            exit;
        }
        echo json_encode($response);
        exit;
    }

}
