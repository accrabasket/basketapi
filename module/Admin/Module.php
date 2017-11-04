<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Admin\Model\UserTable;
use Zend\Authentication\Adapter\DbTable as DbAuthAdapter;
use Zend\Authentication\AuthenticationService;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface {
    public function getAutoloaderConfig() {
        //echo __DIR__ . '/autoload_classmap.php';die;
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getConfig() {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getServiceConfig() {
        return array(
            'factories' => array(
                'Admin\Model\UserTable' => function($sm) {
                    $adapter = $sm->get('AdminDbAdapter');
                    $userObj = new \Admin\Model\UserTable($adapter);
                    return $userObj;
                },
                'AdminDbAdapter' => function ($sm) {
                    return $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                },
                'AuthService' => function ($sm) {
                    $adapter = $sm->get('AdminDbAdapter');
                    $dbAuthAdapter = new DbAuthAdapter($adapter, 'sh_system_user', 'email_id', 'password');

                    $auth = new AuthenticationService();
                    $auth->setAdapter($dbAuthAdapter);
                    return $auth;
                },
                'Admin\Controller\Index'=> function ($sm) {die('ghh');
                    $ctrl = new Admin\Controller\Index($sm->get('Admin\Model\UserTable'));
                    return $ctrl;        
                },                    
        ); 
    }

}
