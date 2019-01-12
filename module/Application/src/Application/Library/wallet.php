<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Library;
use Application\Model\customerModel;
class wallet  {
    public $commonLib;
    public $redis;
    public $customerModel;    
    public function __construct() {
        $this->commonLib = new common();
        $this->customerModel = new customerModel();
        //$this->redis = new \Redis();
        //$this->redisObj = $this->redis->pconnect('127.0.0.1', 6379);        
    }
    public function creditdebitToWallet($parameters , $optional =array()) {
        $response = array('status'=>'fail','msg'=>'fail ');
        
        $attributeRules['amount'] = array('type' => 'numeric', 'is_required' => true);
        $attributeRules['user_id'] = array('type' => 'integer', 'is_required' => true);
        $attributeRules['created_by'] = array('type' => 'integer', 'is_required' => true);
        $attributeRules['merchant_id'] = array('type' => 'string', 'is_required' => true);
        $attributeRules['transaction_type'] = array('type' => 'in_array', 'is_required' => true, 'array_values'=>array('credit', 'debit'));
        
        $response = $this->commonLib->isValid($attributeRules, $parameters);
        return $response;   
    }
    
    public function getBallance($parameters , $optional =array()) {
        $response = array('status'=>'fail','msg'=>'fail');
        $attributeRules['user_id'] = array('type' => 'integer', 'is_required' => true);
        $attributeRules['wallet_key'] = array('type' => 'string', 'is_required' => true);
        $response = $this->commonLib->isValid($attributeRules, $parameters);
        if(empty($response)) {
            $response = array('status'=>'fail', 'msg'=>'No Record Found.');
            $walletBallance = $this->customerModel->getBallance($parameters);
            if(!empty($walletBallance)) {
                $response = array('status'=>'success', 'data'=>$walletBallance);
            }
        }
        
        return $response;   
    }    
}
