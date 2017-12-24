<?php
define('HTTP_ROOT_PATH', "http://".$_SERVER["HTTP_HOST"].'/basketapi/public/images');
$GLOBALS['IMAGEROOTPATH'] = $_SERVER['DOCUMENT_ROOT'].'basketapi/public/images';
define('PER_PAGE_LIMIT', 10);
define('OTP_EXPIRE_TIME', 15);//in minutes
define('FRONT_END_PATH', "http://".$_SERVER["HTTP_HOST"].'/accrafrontend/');
define('FROM_EMAIL', 'raviducat@gmail.com');