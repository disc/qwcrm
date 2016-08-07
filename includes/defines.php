<?php

// Misc

 /* 
  * this inly kept for legacy purposes, i will slowly remove this pointless thing from the code
  * const SEP = '/'; //this type of constant definition is supposed to be quicker and only php 5.3+  * 
  */
define('SEP',DIRECTORY_SEPARATOR); // was define('SEP','/');

define('FILE_ROOT',dirname(__FILE__).SEP);                  // returns the physical path - is used in places but could be removed
define('INSTALL_DATE','Jul 11 2016 08:46:45 PM');           // is this really needed?

// Root Folder Settings
define('INCLUDES_DIR','includes/'); 
define('QWROOT_MEDIA_DIR','media/');                        // properly name this ie set global varibles

// Smarty
define('SMARTY_URL','includes/smarty/');

// Authentication
define('LOGIN_USR', 'login_usr');
define('LOGIN_PWD', 'login_pwd');

// Configuration
define('ACTIVITY_LOG','log/activity.log');
define('LANG','english.xml');                               // check translate code to tidy this up?
define('THEME_NAME','default/');                            // This value is here so it can be set from the 
define('debug', 'no');                                      // in billing/include.php and core/footer.php - needs fixing - controls some footer informtion

// Theme Settings - should these be set as smarty varibles
define('THEME_DIR','themes/'.THEME_NAME);                   // set the current theme's file locations
define('THEME_IMAGES_DIR',THEME_DIR.'images/');
define('THEME_CSS_DIR',THEME_DIR.'css/');
define('THEME_JS_DIR',THEME_DIR.'js/');

// MySQL Database Settings
define('PRFX',$DB_PREFIX );
define('DB_HOST',$DB_HOST);
define('DB_USER',$DB_USER);
define('DB_PASS',$DB_PASS);
define('DB_NAME',$DB_NAME );