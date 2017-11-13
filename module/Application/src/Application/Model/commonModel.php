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
define('PER_PAGE_LIMIT', 20);
class commonModel  {
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
    public function addCategory($parameters) {
        try {
            $query = $this->sql->insert('category_master')
                        ->values($parameters);
            //echo $query->getSqlString();die;
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute()->getAffectedRows();
            return $result;
        } catch (\Exception $ex) {
            return false;
        }
    }
    
    public function updateCategory($parameters) {
        try {
            $params = array();
            $params['category_name'] = $parameters['category_name'];
            $params['category_des'] = $parameters['category_des'];
            $params['parent_category_id'] = $parameters['parent_category_id'];
            $query = $this->sql->update('category_master')
                        ->set($params)
                        ->where(array('id'=>$parameters['id']));
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute()->getAffectedRows();
            return $result;
        } catch (\Exception $ex) {
            return false;
        }
    }
    
    public function addProduct($parameters) {
        try {
            $query = $this->sql->insert('product_master')
                        ->values($parameters);
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute();
            return $this->adapter->getDriver()->getLastGeneratedValue();
        } catch (\Exception $ex) {
            return false;
        }
    }
    
    public function updateProduct($data, $where) {
        try {
            $query = $this->sql->update('product_master')
                        ->set($data)
                        ->where($where);
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute();
            return $parameters['id'];
        } catch (\Exception $ex) {
            return false;
        }
    }
    
    public function categoryList ($parameters) {
        try {
            $query = $this->sql->select('category_master');    
            if(!empty($optional['columns'])){
                $query->columns($optional['columns']); 
            }            
            if (!empty($parameters['id'])) {
                $query = $query->where(array('category_master.id' => $parameters['id']));
            }
            if(!empty($parameters['categoryNotIn'])){
                $query->where->notIn('category_master.id', $parameters['categoryNotIn']);
            }
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute();
            return $result;
        } catch (\Exception $ex) {
            return false;
        }
    }
    
    public function addAttribute($parameters) {
        try {
            $query = $this->sql->insert('product_attribute')
                        ->values($parameters);
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute()->getAffectedRows();
            return $this->adapter->getDriver()->getLastGeneratedValue();
        } catch (\Exception $ex) {
            return false;
        }
    }
    
    public function updateAttribute($parameters, $opation = array()) {
        try {
            $query = $this->sql->update('product_attribute')
                        ->set($parameters)
                        ->where(array('id'=>$opation['id']));
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute()->getAffectedRows();
            return $opation['id'];
        } catch (\Exception $ex) {
            return false;
        }
    }
    
    public function getMarchantList ($parameters, $optional = array()) {
        try {
            
            $query = $this->sql->select('user_master');
            if (!empty($optional['id'])) {
                $query = $query->where(array('id' => $optional['id']));
            }else{
                $query = $query->join('user_role_mapping', 'user_master.id = user_role_mapping.user_id')
                        ->where(array('role_id' => 2));
            }
            
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute();
            return $result;
        } catch (\Exception $ex) {
            return false;
        }
    }
    public function addLocation($parameters) {
        try {
            $query = $this->sql->insert('location_master')
                        ->values($parameters);
           // print_r($parameters);die;
            //print $query->getSqlString();die;
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute()->getAffectedRows();
            return $result;
        } catch (\Exception $ex) {
            return false;
        }
    }
    
    public function updateLocation($parameters, $where) {
        try {            
            $query = $this->sql->update('location_master')
                        ->set($parameters)
                        ->where(array('id'=>$where['id']));
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute()->getAffectedRows();
            return $result;
        } catch (\Exception $ex) {
            return false;
        }
    }    
    
    public function locationList($optional = array()) {
        try {
            $where = new \Zend\Db\Sql\Where();

            $query = $this->sql->select('location_master', array('*'));
            if (!empty($optional['id'])) {
                $query = $query->where(array('id' => $optional['id']));
            }
            if(!empty($optional['address'])) {
                $query = $query->where($where->like('address', "%".$optional['address']."%"));
            }            
            if(isset($optional['active'])) {
                $query = $query->where(array('active'=>$optional['active']));
            } 
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
    
    public function getProductList($optional = array()) {
        try {
            $where = new \Zend\Db\Sql\Where();

            $query = $this->sql->select('product_master');
            if(!empty($optional['columns'])){
                $query->columns($optional['columns']); 
            }            
            if (!empty($optional['id'])) {
                $query = $query->where(array('id' => $optional['id']));
            }
                        
            if(isset($optional['active'])) {
                $query = $query->where(array('active'=>$optional['active']));
            } 
            if(!empty($optional['pagination'])) {
                $startLimit = ($optional['page']-1)*PER_PAGE_LIMIT;
                $query->limit(PER_PAGE_LIMIT)->offset($startLimit);
            }
            if(empty($optional['onlyProductDetails'])){
                $query = $query->join('product_attribute', 'product_attribute.product_id = product_master.id',array('name','unit','quantity'))
                        ;
                $query = $query->join('category_master', 'category_master.id = product_master.category_id',array('category_name'))
                        ;
            }
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute();
            return $result;
        } catch (\Exception $ex) {
            return false;
        }        
    }
    
    public function deleteCategory($parameters) {
        try {            
            $query = $this->sql->delete('category_master')
                        ->where(array('id'=>$parameters['id']));
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute()->getAffectedRows();
            return $result;
        } catch (Exception $ex) {
            return false;
        }
    }
    public function addRider($parameters) {
        try {
            $query = $this->sql->insert('rider_master')
                        ->values($parameters);
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute()->getAffectedRows();
            return $result;
        } catch (\Exception $ex) {
            return false;
        }
    }   
    public function riderList($optional = array()) {
        try {
            $where = new \Zend\Db\Sql\Where();

            $query = $this->sql->select('rider_master');
            if(!empty($optional['columns'])){
                $query->columns($optional['columns']);
            }
            $query = $query->join('location_master', 'location_master.id = rider_master.location_id',array('location_name'=>'address'));
            if (!empty($optional['id'])) {
                $query = $query->where(array('rider_master.id' => $optional['id']));
            }
            if(!empty($optional['name'])) {
                $query = $query->where($where->like('rider_master.name', "%".$optional['name']."%"));
            }            
            if(!empty($optional['email'])) {
                $query = $query->where($where->like('rider_master.email', "%".$optional['email']."%"));
            }            
            if(isset($optional['location_id'])) {
                $query = $query->where(array('rider_master.location_id'=>$optional['location_id']));
            }             
            if(isset($optional['status'])) {
                $query = $query->where(array('rider_master.status'=>$optional['status']));
            } 
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
    public function updateRider($parameters, $where) {
        try {            
            $query = $this->sql->update('rider_master')
                        ->set($parameters)
                        ->where(array('id'=>$where['id']));
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute()->getAffectedRows();
            return $result;
        } catch (\Exception $ex) {
            return false;
        }
    }
    function saveMerchant($parameters, $where) {
        try {            
            $query = $this->sql->update('user_master')
                        ->set($parameters)
                        ->where(array('id'=>$where['id']));
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute()->getAffectedRows();
            return $result;
        } catch (\Exception $ex) {
            return false;
        }        
    }    
}
