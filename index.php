<?php
/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
// Decline static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

// Setup autoloading
require 'init_autoloader.php';
if(!empty($_REQUEST['parameters'])) {
    $rqid = hash('sha512','secure#api$__'.$_REQUEST['parameters']);
    if($rqid != $_REQUEST['rqid']){
        echo json_encode(array('status'=>"fail", "msg"=>"rqid not match"));
        exit;
    }
}
// Run the application!
Zend\Mvc\Application::init(require 'config/application.config.php')->run();
