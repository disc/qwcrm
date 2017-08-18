<?php

defined('_QWEXEC') or die;

require(INCLUDES_DIR.'modules/administrator.php');
require(INCLUDES_DIR.'modules/user.php');

// Clear Smarty Cache
if($VAR['clear_smarty_cache'] == 'true') {
    if(check_page_accessed_via_qwcrm('administrator:config')) {
        clear_smarty_cache();
    }
    die();
}

// Clear Smarty Compile
if($VAR['clear_smarty_compile'] == 'true') {    
    if(check_page_accessed_via_qwcrm('administrator:config')) {
        clear_smarty_compile();        
    }    
    die();
}

// Send a Test Mail
if($VAR['send_test_mail'] == 'true') {
    if(check_page_accessed_via_qwcrm('administrator:config')) {
        send_test_mail($db);
    }
    die();    
}

// Update Company details
if($VAR['submit'] == 'update') {   
    
    if(update_qwcrm_config($VAR)) {
        
        // Reload Page to get the new settings - QConfig has already been decalred before the settings are updated
        force_page('administrator', 'config', 'information_msg='.gettext("Config settings updated successfully."));
        
    } else {
        
        // Load the submitted values
        $smarty->assign('warning_msg', gettext("Some information was invalid, please check for errors and try again."));
        $smarty->assign('qwcrm_config', $VAR);        
        
    }
    
} else {
    
    // No data submitted so just load the current config settings
    $smarty->assign('qwcrm_config', get_qwcrm_config() );
    
}

// Fetch page
$BuildPage .= $smarty->fetch('administrator/config.tpl');

