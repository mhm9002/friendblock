<?php

if (!defined('DOMAIN')){
	define ('DOMAIN', $_SERVER['HTTP_HOST']);
}

//True to show errors (for development)
if(!defined('DEVELOPER_MODE'))
	define('DEVELOPER_MODE', TRUE);

if (!defined('SITE_NAME')){
	define ('SITE_NAME', DOMAIN);
}

if (!defined('version')){
	define('version','v0.2');
}

// Directories
if (!defined('DIR_ROOT')){
	define ('DIR_ROOT', ".");
}

if (!defined('DIR_INC')){
	define ('DIR_INC', DIR_ROOT.'/includes');
}

if (!defined('DIR_FUN')){
	define ('DIR_FUN', DIR_ROOT.'/functions');
}

if (!defined('DIR_CLASS')){
	define ('DIR_CLASS', DIR_INC.'/class');
}

if (!defined('DIR_IMG')){
	define ('DIR_IMG', DIR_ROOT.'/images');
}

if (!defined('DIR_IMG_TMP')){
	define ('DIR_IMG_TMP', DIR_IMG .'/tmp');
}

if (!defined('DIR_TEMPLATE')){
	define ('DIR_TEMPLATE', DIR_ROOT.'/template');
}

if (!defined('DIR_JS')){
	define ('DIR_JS', DIR_ROOT.'/'.version.'/js');
}

if (!defined('DIR_CSS')){
	define ('DIR_CSS', DIR_ROOT.'/'.version.'/css');
}

// Database_info
if(!defined('DATABASE_HOST'))
    define('DATABASE_HOST', 'localhost');
if(!defined('DATABASE_USERNAME'))
    define('DATABASE_USERNAME', 'root');
if(!defined('DATABASE_PASSWORD'))
    define('DATABASE_PASSWORD', '');
if(!defined('DATABASE_NAME'))
    define('DATABASE_NAME', 'db');

//site related constants
if(!defined('NT_SITE_NAME'))
    define('NT_SITE_NAME', 'db');
if(!defined('SITE_USING_SSL'))
    define('SITE_USING_SSL', FALSE);

//SMTP Port
if(!defined('SMTP_PORT'))
	define('SMTP_PORT', 587);

//SMTP Host
if(!defined('SMTP_HOST'))
	define('SMTP_HOST', 'smtp.email.com');

//SMTP Username
if(!defined('SMTP_USERNAME'))
	define('SMTP_USERNAME', 'email@e.com');

//SMTP Password
if(!defined('SMTP_PASSWORD'))
	define('SMTP_PASSWORD', 'hehehe');

//Admin email (new users will be friends by default)
if(!defined('NT_ADMIN_EMAIL'))
	define('NT_ADMIN_EMAIL', 'admin@e.com');

//ini_set("SMTP", SMTP_HOST);
//ini_set("smtp_port",SMTP_PORT);
//ini_set("sendmail_from", NT_ADMIN_EMAIL);


//Define Session Name
if(!defined('SESSION_NAME'))
	define('SESSION_NAME', '_NAJO');

//Define Default Session LifeTIme
if(!defined('SESSION_LIFETIME')){
	if(ini_get('session.gc_maxlifetime') <= 0)
		define('SESSION_LIFETIME', 1440);
	else
		define('SESSION_LIFETIME', ini_get('session.gc_maxlifetime'));
}

//Define the cookie lifetime for Keep Signed In
if(!defined('COOKIE_LIFETIME'))
	define('COOKIE_LIFETIME', 60 * 60 * 24 * 30); //30 Days

if(!defined('COOKIE_KEEP_ME_NAME1'))
	define('COOKIE_KEEP_ME_NAME1', '_bk_uid1');
if(!defined('COOKIE_KEEP_ME_NAME2'))
	define('COOKIE_KEEP_ME_NAME2', '_bk_uid2');
if(!defined('COOKIE_KEEP_ME_NAME3'))
	define('COOKIE_KEEP_ME_NAME3', '_bk_uid3');

//Default Default Template Name
if(!defined('DEFAULT_THEME'))
	define('DEFAULT_THEME', 'default');

//Define Recaptcha Keys
if(!defined('RECAPTCHA_PUBLIC_KEY'))
	define('RECAPTCHA_PUBLIC_KEY', 'recaptcha_public');
if(!defined('RECAPTCHA_PRIVATE_KEY'))
	define('RECAPTCHA_PRIVATE_KEY', 'recaptch_secret');

//Define Social Media Meta data and Constants
if(!defined('SITE_G_SIGN_SCOPE'))
define('SITE_G_SIGN_SCOPE', 'profile email'); 
if(!defined('SITE_G_SIGN_C_ID'))
define('SITE_G_SIGN_C_ID', 'the very long google app id'); 

//Define pusher data
if(!defined('PUSHER_AUTH'))
define('PUSHER_AUTH', 'pusher auth code'); 
if(!defined('PUSHER_SECRET'))
define('PUSHER_SECRET', 'and its secret code'); 
if(!defined('PUSHER_APP'))
define('PUSHER_APP', 'the app id in pusher'); 

//Define Message Types
if(!defined('MSG_TYPE_SUCCESS'))
	define('MSG_TYPE_SUCCESS', 1);
if(!defined('MSG_TYPE_ERROR'))
	define('MSG_TYPE_ERROR', 0);
if(!defined('MSG_TYPE_NOTIFY'))
	define('MSG_TYPE_NOTIFY', 0);

define('MAX_IMAGE_WIDTH', 1200);
define('MAX_IMAGE_HEIGHT', 900);
define('PROFILE_IMAGE_WIDTH', 230);
define('PROFILE_IMAGE_HEIGHT', 230);
define('POST_IMAGE_WIDTH', 400);
define('POST_IMAGE_HEIGHT', 300);
define('MAX_POST_IMAGE_WIDTH', 677);
define('MAX_POST_IMAGE_HEIGHT', 525);
define('MAX_COMMENT_IMAGE_WIDTH', 520);
define('MAX_COMMENT_IMAGE_HEIGHT', 135);
define('IMAGE_THUMBNAIL_WIDTH', 200);
define('IMAGE_THUMBNAIL_HEIGHT', 200);

define('MAX_PRODUCT_FILE_SIZE', 300 * 1024 * 1024);

//Users Daily Limit
define('USER_DAILY_LIMIT_POSTS', 20);
define('USER_DAILY_LIMIT_COMMENTS', 50);
define('USER_DAILY_LIMIT_LIKES', 100);
define('USER_DAILY_LIMIT_FRIEND_REQUESTS', 50);
define('USER_DAILY_LIMIT_ACCOUNTS', 2);

//For Security
define('MAX_LOGIN_ATTEMPT', 5);
define('MAX_LOGIN_ATTEMPT_PERIOD', 15 * 60); // 900 Seconds = 15 Mins
define('PASSWORD_TOKEN_EXPIRY_DATE', 1); //Password Token Expiry Date = 1 Day
    
$SITE_GLOBALS = [
//Months
	'months'             => ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'], 
	//RelationShipStatus
	'relationShipStatus' => [1 => 'Single', 2 => 'In a Relationship', 3=> 'Married'], 
	//Genders
	'genders'            => ['Male' => 'Male', 'Female' => 'Female'], 
	//image Types
	'imageTypes'         => ['jpg', 'jpeg', 'png', 'gif'], 
	'timezone' => ['(UTC-12:00) International Date Line West' => -12, '(UTC-11:00) Coordinated Universal Time-11' => -11, '(UTC-11:00) Samoa' => -11, '(UTC-10:00) Hawaii' => -10, '(UTC-09:00) Alaska' => -9, '(UTC-08:00) Baja California' => -8, '(UTC-08:00) Pacific Time (US & Canada)' => -8, '(UTC-07:00) Arizona' => -7, '(UTC-07:00) Chihuahua, La Paz, Mazatlan' => -7, '(UTC-07:00) Mountain Time(US & Canada)' => -7, '(UTC-06:00) Central America' => -6, '(UTC-06:00) Central Time(US & Canada)' => -6, '(UTC-06:00) Guadalajara, Mexico City, Monterrey' => -6, '(UTC-06:00) Saskatchewan' => -6, '(UTC-05:00) Bogota, Lima, Quito' => -6, '(UTC-05:00) Eastern Time(US & Canada)' => -6, '(UTC-05:00) Indiana(East)' => -6, '(UTC-04:30) Caracas' => -4.5, '(UTC-04:00) Asuncion' => -4, '(UTC-04:00) Atlantic Time(Canada)' => -4, '(UTC-04:00) Cuiaba' => -4, '(UTC-04:00) Georgetown, La Paz, Manaus, Sna Juan' => -4, '(UTC-04:00) Santiago' => -4, '(UTC-03:30) Newfoundland' => -3.5, '(UTC-03:00) Brasilia' => -3, '(UTC-03:00) Buenos Aires' => -3, '(UTC-03:00) Cayenne, Fortaleza' => -3, '(UTC-03:00) Greenland' => -3, '(UTC-03:00) Montevideo' => -3, '(UTC-02:00) Coordinated Universal Time-02' => -2, '(UTC-02:00) Mid-Atlantic' => -2, '(UTC-02:00) Azores' => -2, '(UTC-01:00) Cape Verde Is.' => -1, '(UTC) Casablanca' => 0, '(UTC) Coordinated Universal Time' => 0, '(UTC) Dublin, Edinburgh, Lisbon, London' => 0, '(UTC) Monrovia, Reykjavik' => 0, '(UTC+01:00) Amsterdam, Berlin, Bern, Rome, Stockholm, Vienna' => 1, '(UTC+01:00) Belgrade, Bratislava, Budapest, Ljubljana, Prague' => 1, '(UTC+01:00) Brussels, Copenhagen, Madrid, Paris' => 1, '(UTC+01:00) Sarajevo, Skopje, Warsaw, Zagreb' => 1, '(UTC+01:00) West Central Africa' => 1, '(UTC+01:00) Windhoek' => 1, '(UTC+02:00) Amman' => 2, '(UTC+02:00) Athens, Bucharest, Istanbul' => 2, '(UTC+02:00) Beirut' => 2, '(UTC+02:00) Cairo' => 2, '(UTC+02:00) Damascus' => 2, '(UTC+02:00) Harare, Pretoria' => 2, '(UTC+02:00) Helsinki, Kyiv, Riga, Sofia, Tallinn, Vilnius' => 2, '(UTC+02:00) Jerusalem' => 2, '(UTC+02:00) Minsk' => 2, '(UTC+03:00) Baghdad' => 3, '(UTC+03:00) Kuwait, Riyadh' => 3, '(UTC+03:00) Moscow, ST. Petersburg, Volgograd' => 3, '(UTC+03:00) Nairobi' => 3, '(UTC+03:30) Tehran' => 3.5, '(UTC+04:00) Abu Dhabi, Muscat' => 4, '(UTC+04:00) Baku' => 4, '(UTC+04:00) Port Louis' => 4, '(UTC+04:00) Tbilisi' => 4, '(UTC+04:00) Yerevan' => 4, '(UTC+04:30) Kabul' => 4.5, '(UTC+05:00) Tashkent' => 5, '(UTC+05:30) Chennai, Kolkata, Mumbai, New Delhi' => 5.5, '(UTC+05:30) Sri Jayawardenepura' => 5.5, '(UTC+05:45) Kathmandu' => 5.75, '(UTC+06:00) Astana' => 6, '(UTC+06:00) Dhaka' => 6, '(UTC+06:00) Novosibirsk' => 6, '(UTC+06:30) Yangon (Rangoon)' => 6.5, '(UTC+07:00) Bangkok, Hanoi, Jakarta' => 7, '(UTC+07:00) Krasnoyarsk' => 7, '(UTC+08:00) Beijing, Chongqing, Hongkong' => 8, '(UTC+08:00) Irkutsk' => 8, '(UTC+08:00) Kuala Lumpur, Singapore' => 8, '(UTC+08:00) Perth' => 8, '(UTC+08:00) Taipei' => 8, '(UTC+08:00) Ulaanbaatar' => 8, '(UTC+08:00) Osaka, Sapporo, Tokyo' => 8, '(UTC+09:00) Seoul' => 9, '(UTC+09:00) Yakutsk' => 9, '(UTC+09:30) Adelaide' => 9.5, '(UTC+09:30) Darwin' => 9.5, '(UTC+10:00) Brisbane' => 10, '(UTC+10:00) Canberra, Melbourne, Sydney' => 10, '(UTC+10:00) Guam, Port Moresby' => 10, '(UTC+10:00) Hobart' => 10, '(UTC+10:00) Vladivostok' => 10, '(UTC+11:00) Magadan' => 11, '(UTC+11:00) Solomon Is., New Caledonia' => 11, '(UTC+12:00) Auckland, Wellington' => 12, '(UTC+12:00) Coordinated Universal Time+12' => 12, '(UTC+12:00) Fiji' => 12, '(UTC+13:00) Nukualofa' => 13], 
	'notify_settings'    => ['notifyRepliedToMyTopic' => 1, 'notifyRepliedToMyReply' => 1, 'notifyMyPostApproved' => 1], 'javascripts' => [],                             //Javascript files
	'stylesheets'        => [],                             //Stylesheet Files: load main.css in default
	'template'           => DEFAULT_THEME,                       //Template Name: Default = default
	'layout'             => 'layout',                            //Layout File Name: Default = layout
	'headerType'         => 'default'                           //Layout File Name: Default = layout
];

?>


