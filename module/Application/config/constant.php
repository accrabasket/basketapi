<?php
define('HTTP_ROOT_PATH', "http://".$_SERVER["HTTP_HOST"].'/basketapi/public/images');
$GLOBALS['IMAGEROOTPATH'] = $_SERVER['DOCUMENT_ROOT'].'basketapi/public/images';
define('PER_PAGE_LIMIT', 20);
define('OTP_EXPIRE_TIME', 15);//in minutes
define('FRONT_END_PATH', "http://".$_SERVER["HTTP_HOST"].'/accrafrontend/');
define('FROM_EMAIL', 'raviducat@gmail.com');
define('FIREBASE_API_KEY', 'AAAAV-MIXEM:APA91bH-1Jh90nCdh3jQ_ixWSR9n79opjdrIBfRt1QHlLdR-wN1_x5nZ3ff5RQFz1Jx1fqy7vzG-kwMtaBGNu5dicOOGd9MLpVGuuuveArv0RaWw7DtheBHIlf0x0XiRq6VtewCPyXON');
define('CUSTOMER_FIREBASE_API_KEY', 'AAAAsk4yo8c:APA91bGnLQIsAd1KM5rgymkAo5zivmkBOC6KfK4h5eCpMDI7fG4gmPZuAANggRqmRhYwgxS558rpxEkNZb7JoJiExwNI16512w09cXp1gINkl5g_4RsoNA5gL1RNtW5aNEm5EnFJjntE');
define('SMS_GATEWAY_API','http://api.rmlconnect.net/bulksms/bulksms');
define('SMS_GATEWAY_USERNAME','Afrobaskets');
define('SMS_GATEWAY_PASSWORD','SFlg67yf');
define('THRESOLD_VALUE', 5);