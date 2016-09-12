<?php

//require('includes'.SEP.'modules'.SEP.'core_error.php'); not needed

/* Grab and Process Values befor sending to the log and displaying */
//$error_page         = prepare_error_data('error_page'); // only needed whn using referrer
$error_page         = $VAR['error_page'];
$error_type         = $VAR['error_type'];
$error_location     = $VAR['error_location'];
$php_function       = $VAR['php_function'];
$database_error     = $VAR['database_error'];
$error_msg          = $VAR['error_msg'];

/* This logs errors to the error log */
if($qwcrm_error_log === 'on'){    
    
    // Error page when logged in - these variables have just been set in the error.php controller
    if(isset($_SESSION['login_hash']) && isset($error_type) && $module === 'core' && $page_tpl === 'error'){
        write_record_to_error_log($login_usr, $error_page, $error_type, $error_location, $php_function, $database_error, $error_msg);
    }
    
    // Error page when NOT logged in - find out which ones are missing and perhaps do coding on them - most of these variables are not set
    elseif(!isset($_SESSION['login_hash']) && isset($error_type) && $module === 'core' && $page_tpl === 'error') {
        write_record_to_error_log('-', $error_page, $error_type , $error_location, $php_function, $database_error, $error_msg);
    }
    
}

/* Smarty Template output */

// Assign variables to display on the error page (core:error)
$smarty->assign('error_page',       $error_page             );
$smarty->assign('error_type',       $error_type             );
$smarty->assign('error_location',   $error_location         );
$smarty->assign('php_function',     $php_function           );
$smarty->assign('database_error',   $database_error         );
$smarty->assign('error_msg',        $error_msg              );
    
// Prevent guests/scapers accidentally seeing the errors
if($VAR['theme'] != 'off' && $login_account_type_id != 6){
    $smarty->display('core'.SEP.'error.tpl');
} else {
    echo 'an error has occured but you are not allowed to see it if you are a guest.';
    echo 'Time and date here - Give this to an admin and they can have a look at it for you';
}
