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
            'password' => 'truefalse',
        ));
        $this->sql = new Sql\Sql($this->adapter);
    }
    public function productList($optional) {
        try {
            $where = new \Zend\Db\Sql\Where();

            $query = $this->sql->select('merchant_inventry');
            if(empty($optional['all_product'])) {
                $query->where($where->greaterThanOrEqualTo('merchant_inventry.stock', 1));
            }            
            if(!empty($optional['count'])) {
                $query->join('product_master', 'product_master.id = merchant_inventry.product_id',array());
                $query->columns(array('count'=>new Expression("count(DISTINCT(merchant_inventry.product_id))")));
            }else{ 
                $query->group('merchant_inventry.product_id');
                $query->join('product_master', 'product_master.id = merchant_inventry.product_id',array('product_name','discount_type', 'discount_value', 'product_desc', 'category_id','custom_info','brand_name','bullet_desc','nutrition'));
                if(!empty($optional['merchant_inventry_id'])) {
                    $query->columns(array('id'=>'id','price' => 'price', 'product_id' => 'product_id'));
                    $query->where(array('merchant_inventry.id' => $optional['merchant_inventry_id']));
                }else {
                    $query->columns(array('product_id', 'price'=>new Expression("min(merchant_inventry.price)")));
                }     
            }
            $querySeparator = "";
            $whereStr = '';
            if(!empty($optional['category_id'])) {
                $querySeparator = "OR";
                if(is_array($optional['category_id'])) {
                    $optional['category_id'] = implode(",", $optional['category_id']);
                }
                $whereStr .= " (product_master.category_id IN ($optional[category_id]) ";   
                //$query->where(array('product_master.category_id' => $optional['category_id']));
               
            }
            if(!empty($optional['product_name'])){              
                $optional['product_name'] = addslashes($optional['product_name']);
                $whereStr .= " $querySeparator(product_master.product_name LIKE '%$optional[product_name]%'";
                $whereStr .= " OR product_master.brand_name LIKE '%$optional[product_name]%' )";                 
            } 
            if($querySeparator == 'OR') {
                $whereStr .= ")";
            }
            if(!empty($whereStr)) {
                $query->Where($whereStr);
            }
            if(!empty($optional['product_id'])) {
                $query->where(array('product_master.id' => $optional['product_id']));
            } 
            if(!empty($optional['hotdeals'])  || !empty($optional['offers'])) {
                $query->where('(product_master.hotdeals=1 OR product_master.offers=1)');
            }
            if(!empty($optional['new_arrival'])) {
                $query->where(array('product_master.new_arrival' => $optional['new_arrival']));
            }            
            if(!empty($optional['store_id'])) {
                $query->where(array('merchant_inventry.store_id' => $optional['store_id']));
            }            
            if(!empty($optional['merchant_id'])) {
                $query->where(array('merchant_inventry.merchant_id' => $optional['merchant_id']));
            } 
            if(!empty($optional['brand_name'])) {
                $query->where(array('product_master.brand_name' => explode(",",$optional['brand_name'])));
            }  
            if(!empty($optional['min_price'])) {
                $query->where($where->greaterThanOrEqualTo('merchant_inventry.price', $optional['min_price']));
            }  
            if(!empty($optional['max_price'])) {
                $query->where($where->lessThanOrEqualTo('merchant_inventry.price', $optional['max_price']));
            }  
            if(!empty($optional['min_discount'])) {
                $joinWithAttribute = 1;
                $query->where($where->greaterThanOrEqualTo('product_attribute.discount_value', $optional['min_discount']));
            }  
            if(!empty($optional['max_discount'])) {
                $joinWithAttribute = 1;
                $query->where($where->lessThanOrEqualTo('product_attribute.discount_value', $optional['max_discount']));
            }      
            if(!empty($joinWithAttribute)) {
                $query->join('product_attribute', 'product_attribute.product_id = merchant_inventry.product_id',array());
            }
            if(!empty($optional['merchant_id'])) {
                $query->where(array('merchant_inventry.merchant_id' => $optional['merchant_id']));
            }              
            $query->where(array('product_master.status' => 1));
            if(!empty($optional['pagination'])) {
                $startLimit = ($optional['page']-1)*PER_PAGE_LIMIT;
                $query->limit(PER_PAGE_LIMIT)->offset($startLimit);
            }
            if(!empty($optional['order_by']) && !empty($optional['sort_by'])) {
                $query->order("$optional[sort_by] $optional[order_by]");
            }
            echo $query->getSqlString();die;
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
            if(empty($optional['all_product'])) {
                $query->where($where->greaterThanOrEqualTo('merchant_inventry.stock', 1));     
            }
            if(!empty($optional['order_by']) && !empty($optional['sort_by'])) {
                $query->order("$optional[sort_by] $optional[order_by]");
            }            
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute();
            
            return $result;
        } catch (\Exception $ex) {
            return false;
        }         
    }
    function brandList($params) {
        try {
            $whereStr = "";
            $query = $this->sql->select('product_master');  
            $query->columns(array('brand_name'=>new Expression("DISTINCT(product_master.brand_name)")));
            if($params['brand_name']) {
                $query->where->like('brand_name', "%$params[brand_name]%");
            }
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute(); 
            
            return $result;
        } catch (Exception $ex) {
            return false;
        }
    }    
}
