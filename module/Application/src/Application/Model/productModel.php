<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql;
use Zend\Db\Sql\Expression;
class productModel  {
    public $adapter;
    public $sql;
    public function __construct() {
        $this->adapter = new Adapter(array(
            'driver' => 'Mysqli',
            'database' => 'accrabasket',
            'username' => 'root',
            'password' => '',
        ));
        $this->sql = new Sql\Sql($this->adapter);
    }
    public function productList($optional) {
        try {
            $where = new \Zend\Db\Sql\Where();

            $query = $this->sql->select('merchant_inventry');
            $query = $query->join('product_master', 'product_master.id = merchant_inventry.product_id',array('product_name', 'product_desc', 'category_id'));           
            if(!empty($optional['merchant_inventry_id'])) {
                $query->columns(array('id'=>'id','price' => 'price', 'product_id' => 'product_id'));
                $query = $query->where(array('merchant_inventry.id' => $optional['merchant_inventry_id']));
            }else {
                $query->columns(array('product_id' => new \Zend\Db\Sql\Expression('DISTINCT(merchant_inventry.product_id)')));
            }            
            if(!empty($optional['category_id'])) {
                $query = $query->where(array('product_master.category_id' => $optional['category_id']));
            }            
            if(!empty($optional['product_name'])){
                $query = $query->Where($where->nest->or->like('product_master.product_name',"%".$optional['product_name']."%"), "OR");
            }            
            if(!empty($optional['store_id'])) {
                $query = $query->where(array('merchant_inventry.store_id' => $optional['store_id']));
            }            
            if(!empty($optional['merchant_id'])) {
                $query = $query->where(array('merchant_inventry.merchant_id' => $optional['merchant_id']));
            }            
            $query = $query->where(array('product_master.status' => 1));
            if(!empty($optional['pagination'])) {
                $startLimit = ($optional['page']-1)*PER_PAGE_LIMIT;
                $query->limit(PER_PAGE_LIMIT)->offset($startLimit);
            }            
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute();
            
            return $result;
        } catch (\Exception $ex) {
            return false;
        } 
    }
    
    public function getMerchantProductAttribute($optional) {
        try {
            $where = new \Zend\Db\Sql\Where();

            $query = $this->sql->select('merchant_inventry');
            if(!empty($optional['id'])) {
                $query = $query->where(array('merchant_inventry.id' => $optional['id']));
            }            
            if(!empty($optional['store_id'])) {
                $query = $query->where(array('merchant_inventry.store_id' => $optional['store_id']));
            }
            if(!empty($optional['merchant_id'])) {
                $query = $query->where(array('merchant_inventry.merchant_id' => $optional['merchant_id']));
            }
            if(!empty($optional['attribute_id'])) {
                $query = $query->where(array('merchant_inventry.attribute_id' => $optional['attribute_id']));
            }
            
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute();
            
            return $result;
        } catch (\Exception $ex) {
            return false;
        }         
    }
}
