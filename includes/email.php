<?php
/**
 * @package   QWcrm
 * @author    Jon Brown https://quantumwarp.com/
 * @copyright Copyright (C) 2016 - 2017 Jon Brown, All rights reserved.
 * @license   GNU/GPLv3 or later; https://www.gnu.org/licenses/gpl.html
 */

/** Main Include File **/

/*
 * Mandatory Code - Code that is run upon the file being loaded
 * Display Functions - Code that is used to primarily display records
 * New/Insert Functions - Creation of new records
 * Get Functions - Grabs specific records/fields ready for update
 * Update Functions - For updating records/fields
 * Close Functions - Closing Work Orders code
 * Delete Functions - Deleting Work Orders
 * Other Functions - All other functions not covered above
 */

defined('_QWEXEC') or die;

// Check if we have a customer_id
if($customer_id == '') {
    force_page('core', 'dashboard', 'warning_msg='.gettext("No Custoemr ID supplied."));
    exit;
}  

#######################################
#   Basic email wrapper function      #
#######################################

function email($customer_id, $email_recipient, $email_subject, $email_content, $email_attachment = null) {
    
    // this wrapper can be used as an intermedery so i can choose what email platform to use and also logg in the future
    
}
