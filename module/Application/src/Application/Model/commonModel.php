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
        } catch (Exception $ex) {
            return false;
        }
    }
    
    public function updateCategory($parameters) {
        try {
            $params = array();
            $params['category_name'] = $parameters['category_name'];
            $params['category_des'] = $parameters['category_des'];
            
            $query = $this->sql->update('category_master')
                        ->set($params)
                        ->where(array('id'=>$parameters['id']));
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute()->getAffectedRows();
            return $result;
        } catch (Exception $ex) {
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
        } catch (Exception $ex) {
            return false;
        }
    }
    
    public function updateProduct($parameters) {
        try {
            $params = array();
            $params['product_name'] = $parameters['product_name'];
            $params['category_id'] = $parameters['category_id'];
            $params['product_des'] = $parameters['product_des'];
            
            $query = $this->sql->update('product_master')
                        ->set($params)
                        ->where(array('id'=>$parameters['id']));
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute();
            return $parameters['id'];
        } catch (Exception $ex) {
            return false;
        }
    }
    
    public function categoryList ($parameters, $optional = array()) {
        try {
            $query = $this->sql->select('category_master');
            if (!empty($optional['id'])) {
                $query = $query->where(array('id' => $optional['id']));
            }
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute();
            return $result;
        } catch (Exception $ex) {
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
        } catch (Exception $ex) {
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
        } catch (Exception $ex) {
            return false;
        }
    }
    
    public function getMarchantList ($parameters, $optional = array()) {
        try {
            
            $query = $this->sql->select('user_master');
            $query = $query->join('user_role_mapping', 'user_master.id = user_role_mapping.user_id')
                        ->where(array('role_id' => 2));

            if (!empty($optional['id'])) {
                $query = $query->where(array('id' => $optional['id']));
            }
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute();
            return $result;
        } catch (Exception $ex) {
            die('fsdfdsfs');
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
        } catch (Exception $ex) {
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
        } catch (Exception $ex) {
            return false;
        }
    }    

}
