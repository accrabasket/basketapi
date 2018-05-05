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
class commonModel  {
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
    public function addCategory($parameters) {
        try {
            $query = $this->sql->insert('category_master')
                        ->values($parameters);
            //echo $query->getSqlString();die;
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute();
            return $this->adapter->getDriver()->getLastGeneratedValue();
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
            return $where['id'];
        } catch (\Exception $ex) {
            return false;
        }
    }
    
    public function categoryList ($parameters) {
        try {
            $where = new \Zend\Db\Sql\Where();
            $query = $this->sql->select('category_master');    
            if(!empty($optional['columns'])){
                $query->columns($optional['columns']); 
            }            
            if (!empty($parameters['id'])) {
                $query = $query->where(array('category_master.id' => $parameters['id']));
            }
            if (!empty($parameters['category_name'])) {
                $query = $query->where($where->like('category_master.category_name',"%".$parameters['category_name']."%"));
            }            
            if(!empty($parameters['categoryNotIn'])){
                $query->where->notIn('category_master.id', $parameters['categoryNotIn']);
            }
            if(!empty($parameters['parent_category_list'])) {
                $query = $query->where(array('category_master.parent_category_id' => 0));
            }
            if(!empty($parameters['parent_category_id'])) {
                $query = $query->where(array('category_master.parent_category_id' => $parameters['parent_category_id']));
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
    
    public function insertIntoProductMapping($data) {
        try {
            $query = $this->sql->insert('product_merchant_mapping')
                        ->values($data);
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
    
    public function getMarchantList ($optional = array()) {
        try {
            
            $query = $this->sql->select('user_master');
            if (!empty($optional['id'])) {
                $query = $query->where(array('id' => $optional['id']));
            }elseif(!empty($optional['email'])){
                $query = $query->where(array('email' => $optional['email']));
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
    
    public function getMerchantMapping($where, $optional = array()) {
        try {
            $query = $this->sql->select('product_merchant_mapping');
            if(!empty($optional['column'])) {
                $query->columns($optional['column']);
            }
            $query = $query->where($where);
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
            
            $query = $this->sql->select('location_master');
            if(!empty($optional['columns'])){
                $query->columns($optional['columns']); 
            }             
            if (!empty($optional['id'])) {
                $query = $query->where(array('id' => $optional['id']));
            }
            if(!empty($optional['address'])) {
                $query = $query->where($where->like('address', "%".$optional['address']."%"));
            } 
            if(!empty($optional['city_id'])) {
                $query = $query->where(array('city_id'=>$optional['city_id']));
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
                $query = $query->where(array('product_master.id' => $optional['id']));
            }
            if (!empty($optional['item_code'])) {
                $query = $query->where(array('product_master.item_code' => $optional['item_code']));
            }            
            if(!empty($optional['product_name'])) {
                $value = '%'.$optional['product_name'].'%';
                $query = $query->where($where->like('product_master.product_name', $value));                
            }                        
            if(isset($optional['active'])) {
                $query = $query->where(array('product_master.status'=>$optional['active']));
            } 
            if(!empty($optional['pagination'])) {
                $startLimit = ($optional['page']-1)*PER_PAGE_LIMIT;
                $query->limit(PER_PAGE_LIMIT)->offset($startLimit);
            }
            if(!empty($optional['merchant_id'])) {
                $query = $query->join('product_merchant_mapping', 'product_master.id = product_merchant_mapping.product_id',array());
                $query = $query->where(array('product_merchant_mapping.merchant_id'=>$optional['merchant_id']));
            }
            if(empty($optional['onlyProductDetails'])){
//                $query = $query->join('product_attribute', 'product_attribute.product_id = product_master.id',array('name','unit','quantity'))
                if(!empty($optional['name'])) {
                      $query = $query->join('product_attribute', 'product_attribute.product_id = product_master.id',array('name','unit','quantity'));
                      $value = '%'.$optional['name'].'%';
                      $query = $query->where($where->like('product_attribute.name', $value));                

                      
                }        
                $query = $query->join('category_master', 'category_master.id = product_master.category_id',array('category_name'))
                        ;
                if(!empty($optional['category_name'])) {
                    $value = '%'.$optional['category_name'].'%';
                    $query = $query->where($where->like('category_master.category_name', $value));                
                }
            }
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute();
            return $result;
        } catch (\Exception $ex) {
            return false;
        }        
    }
    
    public function getAttributeList($optional = array()) {
        try {
            $where = new \Zend\Db\Sql\Where();

            $query = $this->sql->select('product_attribute');
            if(!empty($optional['product_id'])) {
                $query = $query->where(array('product_id'=>$optional['product_id']));
            }
            if(!empty($optional['name'])){
                $value = '%'.$optional['name'].'%';
                $query = $query->where($where->like('product_attribute.name',$value));
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
    
    public function deleteProduct($parameters) {
        try {            
            if(!empty($parameters)) {
                $query = $this->sql->delete('product_master')
                        ->where(array('id'=>$parameters['product_id']));
                $satements = $this->sql->prepareStatementForSqlObject($query);
                $result = $satements->execute()->getAffectedRows();
                return $result;
            }
            return false;
        } catch (Exception $ex) {
            return false;
        }        
    }
    public function deleteAttribute($parameters) {
        try {            
            if(!empty($parameters)) {
                $query = $this->sql->delete('product_attribute')
                        ->where(array('product_id'=>$parameters['product_id']));
                $satements = $this->sql->prepareStatementForSqlObject($query);
                $result = $satements->execute()->getAffectedRows();
                return $result;
            }
            return false;
        } catch (Exception $ex) {
            return false;
        }            
    }

    public function deleteMerchantInvernty($parameters) {
        try {            
            if(!empty($parameters)) {
                $query = $this->sql->delete('merchant_inventry')
                        ->where(array('product_id'=>$parameters['product_id']));
                $satements = $this->sql->prepareStatementForSqlObject($query);
                $result = $satements->execute()->getAffectedRows();
                return $result;
            }
            return false;
        } catch (Exception $ex) {
            return false;
        }            
    }

    function beginTransaction() {
        $this->adapter->getDriver()->getConnection()->beginTransaction();
    }
    function commit() {
        $this->adapter->getDriver()->getConnection()->commit();
    }    
    function rollback() {
        $this->adapter->getDriver()->getConnection()->rollback();
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
                $query = $query->where(array('rider_master.email'=>$optional['email']));
            }
            if(!empty($optional['password'])) {
                $query = $query->where(array('rider_master.password'=>$optional['password']));
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
            ///echo $query->getSqlString();die;
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
            return $where['id'];
        } catch (\Exception $ex) {
            return false;
        }        
    }

    function savetax($parameters, $where = array()) {
        try {            
            $query = $this->sql->insert('tax_master')
                        ->values($parameters);       
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute()->getAffectedRows();
            return $result;
        } catch (\Exception $ex) {
            return false;
        }        
    }
    
     function saveStore($parameters, $where = array()) {
        try {     
            $query = $this->sql->insert('merchant_store')
                        ->values($parameters);       
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute()->getAffectedRows();
            return $result;
        } catch (\Exception $ex) {
            return false;
        }        
    }
    
    public function updateStore($parameters, $where) {
        try {            
            $query = $this->sql->update('merchant_store')
                        ->set($parameters)
                        ->where(array('id'=>$where['id']));
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute()->getAffectedRows();
            return $result;
        } catch (\Exception $ex) {
            return false;
        }
    }

    function updatetax($parameters, $where) {
        try {            
            $query = $this->sql->update('tax_master')
                        ->set($parameters)
                        ->where(array('id'=>$where));
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute()->getAffectedRows();
            return $result;
        } catch (\Exception $ex) {
            return false;
        }        
    }

   function taxlist($parameters, $where) {
        try {            
            $query = $this->sql->select('tax_master');
            if(!empty($where)){
                $query = $query->where(array('id'=>$where['id']));
            }
                        
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute();
            return $result;
        } catch (\Exception $ex) {
            return false;
        }        
    }

    public function deletetax($parameters) {
        try {            
            $query = $this->sql->delete('tax_master')
                        ->where(array('id'=>$parameters['id']));
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute()->getAffectedRows();
            return $result;
        } catch (Exception $ex) {
            return false;
        }
    }

    public function storeList($optional = array()) {
        try {
            $where = new \Zend\Db\Sql\Where();

            $query = $this->sql->select('merchant_store');
            if(!empty($optional['columns'])){
                $query->columns($optional['columns']); 
            }               
            if (!empty($optional['id'])) {
                $query = $query->where(array('id' => $optional['id']));
            }
            if(!empty($optional['address'])) {
                $query = $query->where($where->like('address', "%".$optional['address']."%"));
            } 
            if(!empty($optional['store_name'])) {
                $query = $query->where($where->like('store_name', "%".$optional['store_name']."%"));
            } 
            if(!empty($optional['merchant_id'])) {
                $query = $query->where(array('merchant_id'=>$optional['merchant_id']));
            } 
            if(!empty($optional['location_id'])) {
                $query = $query->where(array('location_id'=>$optional['location_id']));
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

    public function deleteStore($parameters) {
        try {            
            $query = $this->sql->delete('merchant_store')
                        ->where(array('id'=>$parameters['id']));
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute()->getAffectedRows();
            return $result;
        } catch (Exception $ex) {
            return false;
        }
    }
    
    function saveInventry($parameters) {
        try {     
            $query = $this->sql->insert('merchant_inventry')
                        ->values($parameters);       
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute()->getAffectedRows();
            return $result;
        } catch (\Exception $ex) {
            return false;
        }        
    }
    
    public function stockList($optional = array()) {
        try {
            $where = new \Zend\Db\Sql\Where();

            $query = $this->sql->select('merchant_inventry');            
            if (!empty($optional['id'])) {
                $query = $query->where(array('merchant_inventry.id' => $optional['id']));
            } 
            if(isset($optional['merchant_id'])) {
                $query = $query->where(array('merchant_inventry.merchant_id'=>$optional['merchant_id']));
            }
            if(isset($optional['out_of_stock'])) {
                $query = $query->where($where->lessThanOrEqualTo('merchant_inventry.stock', THRESOLD_VALUE));
            }            
            $query = $query->join('product_attribute', 'product_attribute.id = merchant_inventry.attribute_id',array('name','unit','quantity'));
            $query = $query->join('product_master', 'product_master.id = merchant_inventry.product_id',array('product_name'));
            $query = $query->join('merchant_store', 'merchant_store.id = merchant_inventry.store_id',array('store_name'));
            if(!empty($optional['columns'])) {
                $query->columns($optional['columns']);
            }
            if(empty($optional['count_row']) && !empty($optional['pagination'])) {
                $startLimit = ($optional['page']-1)*PER_PAGE_LIMIT;
                $query->limit(PER_PAGE_LIMIT)->offset($startLimit);
            }            
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute();
            if(!empty($optional['count_row'])) {
                $result = $result->current();
            }            
            return $result;
        } catch (\Exception $ex) {
            return false;
        }        
    }
    
    public function checkAttributeExist($optional = array()) {
        try {
            $where = new \Zend\Db\Sql\Where();
            $query = $this->sql->select('merchant_inventry');
            if(isset($optional['product_id'])) {
                $query = $query->where(array('merchant_inventry.product_id'=>$optional['product_id']));
            }
            if(isset($optional['attribute_id'])) {
                $query = $query->where(array('merchant_inventry.attribute_id'=>$optional['attribute_id']));
            }            
            if (!empty($optional['store_id'])) {
                $query = $query->where(array('merchant_inventry.store_id' => $optional['store_id']));
            } 
            if(isset($optional['merchant_id'])) {
                $query = $query->where(array('merchant_inventry.merchant_id'=>$optional['merchant_id']));
            }            
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute();
            return $result;
        } catch (\Exception $ex) {
            return false;
        }        
    }
    
    function updateInventry($parameters, $where) {
        try {            
            $query = $this->sql->update('merchant_inventry')
                        ->set($parameters)
                        ->where(array('id'=>$where));
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute()->getAffectedRows();
            return $result;
        } catch (\Exception $ex) {
            return false;
        }        
    }
    
    public function countryList($optional = array()) {
        try {
            $where = new \Zend\Db\Sql\Where();

            $query = $this->sql->select('country_master', array('*'));
            if (!empty($optional['id'])) {
                $query = $query->where(array('id' => $optional['id']));
            }
            if(!empty($optional['country_name'])) {
                $query = $query->where($where->like('country_name', "%".$optional['country_name']."%"));
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
    
    public function cityList($optional = array()) {
        try {
            $where = new \Zend\Db\Sql\Where();

            $query = $this->sql->select('city_master', array('*'));
            if (!empty($optional['id'])) {
                $query = $query->where(array('id' => $optional['id']));
            }
            if(!empty($optional['city_name'])) {
                $query = $query->where($where->like('city_name', "%".$optional['city_name']."%"));
            }            
            if(isset($optional['country_id'])) {
                $query = $query->where(array('country_id'=>$optional['country_id']));
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
    
    public function getProductListCount($optional = array()) {
        try {
            $where = new \Zend\Db\Sql\Where();

            $query = $this->sql->select('product_master');
            $query->columns(array('count' => new \Zend\Db\Sql\Expression('count(*)')));
                       
            if (!empty($optional['id'])) {
                $query = $query->where(array('product_master.id' => $optional['id']));
            }
            if(!empty($optional['product_name'])) {
                $value = '%'.$optional['product_name'].'%';
                $query = $query->where($where->like('product_master.product_name', $value));                
            }                        
            if(isset($optional['active'])) {
                $query = $query->where(array('product_master.status'=>$optional['active']));
            } 
            if(!empty($optional['merchant_id'])) {
                $query = $query->join('product_merchant_mapping', 'product_master.id = product_merchant_mapping.product_id',array());
                $query = $query->where(array('product_merchant_mapping.merchant_id'=>$optional['merchant_id']));
            }            
            if(empty($optional['onlyProductDetails'])){
             if(!empty($optional['name'])) {
                      $query = $query->join('product_attribute', 'product_attribute.product_id = product_master.id',array());
                      $value = '%'.$optional['name'].'%';
                      $query = $query->where($where->like('product_attribute.name', $value));                

                      
                }        
                $query = $query->join('category_master', 'category_master.id = product_master.category_id',array())
                        ;
                if(!empty($optional['category_name'])) {
                    $value = '%'.$optional['category_name'].'%';
                    $query = $query->where($where->like('category_master.category_name', $value));                
                }
            }
//            echo $query->getSqlString();die;
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute();
            return $result;
        } catch (\Exception $ex) {
            return false;
        } 
    }
    
    function saveImage($imageData) {
        try {
            $query = $this->sql->insert('image_master')
                        ->values($imageData);
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute();
            return $this->adapter->getDriver()->getLastGeneratedValue();
        } catch (\Exception $ex) {
            return false;
        }        
    }
    function fetchImage($where) {
        try {
            if(!empty($where)) {
                $query = $this->sql->select('image_master');
                $query = $query->where($where);
                $satements = $this->sql->prepareStatementForSqlObject($query);
                $result = $satements->execute();
                
                return $result;                
            }else {
                return false;
            }
        }  catch (\Exception $ex) {
            echo $ex->getMessage();die;
            return false;
        }
    }
    
    public function saveSetting($parameters) {
        try {
            $query = $this->sql->insert('setting_master')
                        ->values($parameters);
            //echo $query->getSqlString();die;
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute();
            return $this->adapter->getDriver()->getLastGeneratedValue();
        } catch (\Exception $ex) {
            return false;
        }
    }
    
    public function settinglist($optional = array()) {
        try {
            $where = new \Zend\Db\Sql\Where();

            $query = $this->sql->select('setting_master', array('*'));
           
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
    
    function updateSetting($parameters, $where) {
        try {            
            $query = $this->sql->update('setting_master')
                        ->set($parameters)
                        ->where(array('id'=>$where));
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute()->getAffectedRows();
            return $result;
        } catch (\Exception $ex) {
            return false;
        }        
    }
    
    function getBanner($where){
        try {
            $query = $this->sql->select('banner');
            $query = $query->where($where);
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute();
            return $result;
        } catch (\Exception $ex) {
            return false;
        }        
    }
    
    public function savecity($parameters) {
        try {
            $query = $this->sql->insert('city_master')
                        ->values($parameters);
            //echo $query->getSqlString();die;
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute();
            return $this->adapter->getDriver()->getLastGeneratedValue();
        } catch (\Exception $ex) {
            return false;
        }
    }
    
    public function checkcityexist($optional = array()) {
        try {
            $where = new \Zend\Db\Sql\Where();

            $query = $this->sql->select('city_master');
            $query->columns(array('count' => new \Zend\Db\Sql\Expression('count(*)')));
                       
            if (!empty($optional)) {
                $query = $query->where(array('city_name' => $optional));
            }
            
//            echo $query->getSqlString();die;
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute();
            return $result;
        } catch (\Exception $ex) {
            return false;
        } 
    }
    
    function updatecity($parameters, $where) {
        try {            
            $query = $this->sql->update('city_master')
                        ->set($parameters)
                        ->where(array('id'=>$where));
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute()->getAffectedRows();
            return $result;
        } catch (\Exception $ex) {
            return false;
        }        
    }
    
    public function deletecity($parameters) {
        try {            
            $query = $this->sql->delete('city_master')
                        ->where(array('id'=>$parameters['id']));
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute()->getAffectedRows();
            return $result;
        } catch (Exception $ex) {
            return false;
        }
    }
    
    public function savetimeslot($parameters) {
        try {
            $query = $this->sql->insert('timeslot_master')
                        ->values($parameters);
//            echo $query->getSqlString();die;
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute();
            return $this->adapter->getDriver()->getLastGeneratedValue();
        } catch (\Exception $ex) {
            return false;
        }
    }
    
    public function deliveryTimeSlotList($optional = array()) {
        try {
            $where = new \Zend\Db\Sql\Where();

            $query = $this->sql->select('timeslot_master', array('*'));
            if (!empty($optional['id'])) {
                $query = $query->where(array('id' => $optional['id']));
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
    
    function updatetimeslot($parameters, $where) {
        try {            
            $query = $this->sql->update('timeslot_master')
                        ->set($parameters)
                        ->where(array('id'=>$where));
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute()->getAffectedRows();
            return $result;
        } catch (\Exception $ex) {
            return false;
        }        
    }
    
    public function deletetimeslot($parameters) {
        try {            
            $query = $this->sql->delete('timeslot_master')
                        ->where(array('id'=>$parameters['id']));
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute()->getAffectedRows();
            return $result;
        } catch (Exception $ex) {
            return false;
        }
    }
    
     public function addBanner($parameters) {
        try {
            $query = $this->sql->insert('banner')
                        ->values($parameters);
            //echo $query->getSqlString();die;
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute();
            return $this->adapter->getDriver()->getLastGeneratedValue();
        } catch (\Exception $ex) {
            return false;
        }
    }
    
    public function updateBanner($parameters,$where) {
        try {
            $query = $this->sql->update('banner')
                        ->set($parameters)
                        ->where(array('id'=>$where));
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute()->getAffectedRows();
            return $result;
        } catch (\Exception $ex) {
            return false;
        }
    }
    
    public function getMerchantCount($whereParams = array(), $optional=array()) {
        try {
            if(empty($optional['date_formate'])){
                $optional['date_formate'] = "%Y-%m-%d";
            }            
            $where = new \Zend\Db\Sql\Where();
            $query = $this->sql->select('user_master');
            if(!empty($whereParams['start_date']) && !empty($whereParams['end_date'])) {
                $query->columns(array('count'=>new Expression("count(*)"),'created_date'=>new Expression("DATE_FORMAT(created_date, '$optional[date_formate]')")));
                $query->group(new Expression("DATE_FORMAT(created_date, '$optional[date_formate]')"));
            }else{
                $query->columns(array('count'=>new Expression("count(*)")));
            }    
            if(!empty($whereParams['start_date'])) {
                $query = $query->where($where->greaterThanOrEqualTo('user_master.created_date', $whereParams['start_date']));                
            }
            if(!empty($whereParams['end_date'])) {
                $query = $query->where($where->lessThanOrEqualTo('user_master.created_date', $whereParams['end_date']));                
            }
            //echo $query->getSqlString();die;
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute();
            
            return $result;
        } catch (\Exception $ex) {
            return false;
        }           
    }
    
    function deleteImage($imageId, $type) {
        try {            
            $query = $this->sql->delete('image_master')
                        ->where(array('image_id'=>$imageId))
                        ->where(array('type'=>$type));
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute()->getAffectedRows();
            return $result;
        } catch (Exception $ex) {
            return false;
        }        
    }
    
}
