<?php

/*
 * @package   QWcrm
 * @author    Jon Brown https://quantumwarp.com/
 * @copyright Copyright (C) 2016 - 2017 Jon Brown, All rights reserved.
 * @license   GNU/GPLv3 or later; https://www.gnu.org/licenses/gpl.html
 */

/*
 * Mandatory Code - Code that is run upon the file being loaded
 * Display Functions - Code that is used to primarily display records - linked tables
 * New/Insert Functions - Creation of new records
 * Get Functions - Grabs specific records/fields ready for update - no table linking
 * Update Functions - For updating records/fields
 * Close Functions - Closing Work Orders code
 * Delete Functions - Deleting Work Orders
 * Other Functions - All other functions not covered above
 */

defined('_QWEXEC') or die;

/** Mandatory Code **/

/** Display Functions **/

#########################################
#     Display Invoices                  #
#########################################

function display_invoices($order_by, $direction, $use_pages = false, $records_per_page = null, $page_no = null, $search_category = null, $search_term = null, $status = null, $employee_id = null, $client_id = null) {

    $db = QFactory::getDbo();
    $smarty = QFactory::getSmarty();
    
    // Process certain variables - This prevents undefined variable errors
    $records_per_page = $records_per_page ?: '25';
    $page_no = $page_no ?: '1';
    $search_category = $search_category ?: 'invoice_id';
    $havingTheseRecords = '';
        
    /* Records Search */
    
    // Default Action
    $whereTheseRecords = "WHERE ".PRFX."invoice_records.invoice_id\n";
    $havingTheseRecords = '';
    
    // Restrict results by search category (client) and search term
    if($search_category == 'client_display_name') {$havingTheseRecords .= " HAVING client_display_name LIKE ".$db->qstr('%'.$search_term.'%');}
    
    // Restrict results by search category (employee) and search term
    elseif($search_category == 'employee_display_name') {$havingTheseRecords .= " HAVING employee_display_name LIKE ".$db->qstr('%'.$search_term.'%');}
    
    // Restrict results by search category (labour items / labour descriptions) and search term
    elseif($search_category == 'labour_items') {$whereTheseRecords .= " AND labour.labour_items LIKE ".$db->qstr('%'.$search_term.'%');} 

    // Restrict results by search category (parts items / parts descriptions) and search term
    elseif($search_category == 'parts_items') {$whereTheseRecords .= " AND parts.parts_items LIKE ".$db->qstr('%'.$search_term.'%');}    
    
    // Restrict results by search category and search term
    elseif($search_term != null) {$whereTheseRecords .= " AND ".PRFX."invoice_records.$search_category LIKE ".$db->qstr('%'.$search_term.'%');}
    
    /* Filter the Records */
    
    // Restrict by Status
    if($status) {
        
        // All Open Invoices
        if($status == 'open') {
            
            $whereTheseRecords .= " AND ".PRFX."invoice_records.is_closed != '1'";
        
        // All Closed Invoices
        } elseif($status == 'closed') {
            
            $whereTheseRecords .= " AND ".PRFX."invoice_records.is_closed = '1'";
        
        // Return Invoices for the given status
        } else {
            
            $whereTheseRecords .= " AND ".PRFX."invoice_records.status= ".$db->qstr($status);
            
        }
        
    }

    // Restrict by Employee
    if($employee_id) {$whereTheseRecords .= " AND ".PRFX."invoice_records.employee_id=".$db->qstr($employee_id);}        

    // Restrict by Client
    if($client_id) {$whereTheseRecords .= " AND ".PRFX."invoice_records.client_id=".$db->qstr($client_id);}
    
    /* The SQL code */
    
    $sql = "SELECT        
        ".PRFX."invoice_records.*,
            
        IF(company_name !='', company_name, CONCAT(".PRFX."client_records.first_name, ' ', ".PRFX."client_records.last_name)) AS client_display_name,
        ".PRFX."client_records.first_name AS client_first_name,
        ".PRFX."client_records.last_name AS client_last_name,
        ".PRFX."client_records.primary_phone AS client_phone,
        ".PRFX."client_records.mobile_phone AS client_mobile_phone,
        ".PRFX."client_records.fax AS client_fax,
            
        CONCAT(".PRFX."user_records.first_name, ' ', ".PRFX."user_records.last_name) AS employee_display_name,
        ".PRFX."user_records.work_primary_phone AS employee_work_primary_phone,
        ".PRFX."user_records.work_mobile_phone AS employee_work_mobile_phone,
        ".PRFX."user_records.home_mobile_phone AS employee_home_mobile_phone,
        
        labour.labour_items,
        parts.parts_items

        FROM ".PRFX."invoice_records
            
        LEFT JOIN (
            SELECT ".PRFX."invoice_labour.invoice_id,            
            GROUP_CONCAT(
                CONCAT(".PRFX."invoice_labour.unit_qty, ' x ', ".PRFX."invoice_labour.description)                
                ORDER BY ".PRFX."invoice_labour.invoice_labour_id
                ASC
                SEPARATOR '|||'                
            ) AS labour_items           
            FROM ".PRFX."invoice_labour
            GROUP BY ".PRFX."invoice_labour.invoice_id
            ORDER BY ".PRFX."invoice_labour.invoice_id
            ASC            
        ) AS labour
        ON ".PRFX."invoice_records.invoice_id = labour.invoice_id 
        
        LEFT JOIN (
            SELECT ".PRFX."invoice_parts.invoice_id,            
            GROUP_CONCAT(
                CONCAT(".PRFX."invoice_parts.unit_qty, ' x ', ".PRFX."invoice_parts.description)                
                ORDER BY ".PRFX."invoice_parts.invoice_parts_id
                ASC
                SEPARATOR '|||'                
            ) AS parts_items
            FROM ".PRFX."invoice_parts
            GROUP BY ".PRFX."invoice_parts.invoice_id
            ORDER BY ".PRFX."invoice_parts.invoice_id
            ASC            
        ) AS parts
        ON ".PRFX."invoice_records.invoice_id = parts.invoice_id 

        LEFT JOIN ".PRFX."client_records ON ".PRFX."invoice_records.client_id = ".PRFX."client_records.client_id         
        LEFT JOIN ".PRFX."user_records ON ".PRFX."invoice_records.employee_id = ".PRFX."user_records.user_id
        
        ".$whereTheseRecords."
        GROUP BY ".PRFX."invoice_records.".$order_by."
        ".$havingTheseRecords."
        ORDER BY ".PRFX."invoice_records.".$order_by."
        ".$direction;

    /* Restrict by pages */
    
    if($use_pages) {
        
        // Get the start Record
        $start_record = (($page_no * $records_per_page) - $records_per_page);        
        
        // Figure out the total number of records in the database for the given search        
        if(!$rs = $db->Execute($sql)) {
            force_error_page('database', __FILE__, __FUNCTION__, $db->ErrorMsg(), $sql, _gettext("Failed to count the matching Invoice records."));
        } else {        
            $total_results = $rs->RecordCount();            
            $smarty->assign('total_results', $total_results);
        } 
        
        // Figure out the total number of pages. Always round up using ceil()
        $total_pages = ceil($total_results / $records_per_page);
        $smarty->assign('total_pages', $total_pages);

        // Set the page number
        $smarty->assign('page_no', $page_no);

        // Assign the Previous page        
        $previous_page_no = ($page_no - 1);        
        $smarty->assign('previous_page_no', $previous_page_no);        
        
        // Assign the next page        
        if($page_no == $total_pages) {$next_page_no = 0;}
        elseif($page_no < $total_pages) {$next_page_no = ($page_no + 1);}
        else {$next_page_no = $total_pages;}
        $smarty->assign('next_page_no', $next_page_no);
        
        // Only return the given page's records
        $limitTheseRecords = " LIMIT ".$start_record.", ".$records_per_page;
        
        // add the restriction on to the SQL
        $sql .= $limitTheseRecords;
        
    } else {
        
        // This make the drop down menu look correct
        $smarty->assign('total_pages', 1);
        
    }

    /* Return the records */
         
    if(!$rs = $db->Execute($sql)) {
        force_error_page('database', __FILE__, __FUNCTION__, $db->ErrorMsg(), $sql, _gettext("Failed to return the matching Invoice records."));
    } else {
        
        $records = $rs->GetArray();   // do i need to add the check empty

        if(empty($records)){
            
            return false;
            
        } else {

            return $records;
            
        }
        
    }
    
}

/** Insert Functions **/

#####################################
#     insert invoice                #
#####################################

function insert_invoice($client_id, $workorder_id, $unit_discount_rate) {
    
    $db = QFactory::getDbo();
    
    // Unify Dates and Times
    $timestamp = time();
    
    // Get invoice tax type
    $tax_system = QW_TAX_SYSTEM;
    
    // Sales Tax Rate based on Tax Type
    $sales_tax_rate = ($tax_system == 'sales_tax_cash') ? $sales_tax_rate = get_company_details('sales_tax_rate') : 0.00;
    
    $sql = "INSERT INTO ".PRFX."invoice_records SET     
            employee_id     =". $db->qstr( QFactory::getUser()->login_user_id   ).",
            client_id       =". $db->qstr( $client_id                           ).",
            workorder_id    =". $db->qstr( $workorder_id                        ).",
            date            =". $db->qstr( mysql_date($timestamp)               ).",
            due_date        =". $db->qstr( mysql_date($timestamp)               ).",            
            unit_discount_rate   =". $db->qstr( $unit_discount_rate             ).",
            tax_system      =". $db->qstr( $tax_system                          ).",
            sales_tax_rate  =". $db->qstr( $sales_tax_rate                      ).",            
            status          =". $db->qstr( 'pending'                            ).",
            opened_on       =". $db->qstr( mysql_datetime($timestamp)           ).",
            is_closed       =". $db->qstr( 0                                    ); 

    if(!$rs = $db->Execute($sql)) {
        force_error_page('database', __FILE__, __FUNCTION__, $db->ErrorMsg(), $sql, _gettext("Failed to insert the invoice record into the database."));
    } else {
        
        // Get invoice_id
        $invoice_id = $db->Insert_ID();
        
        // Create a Workorder History Note  
        insert_workorder_history_note($workorder_id, _gettext("Invoice").' '.$invoice_id.' '._gettext("was created for this Work Order").' '._gettext("by").' '.QFactory::getUser()->login_display_name.'.');
                
        // Log activity        
        if($workorder_id) {            
            $record = _gettext("Invoice").' '.$invoice_id.' '._gettext("for Work Order").' '.$workorder_id.' '._gettext("was created by").' '.QFactory::getUser()->login_display_name.'.';
        } else {            
            $record = _gettext("Invoice").' '.$invoice_id.' '._gettext("Created with no Work Order").'.';
        }        
        write_record_to_activity_log($record, QFactory::getUser()->login_user_id, $client_id, $workorder_id, $invoice_id);
        
        // Update last active record    
        update_client_last_active($client_id);
        update_workorder_last_active($workorder_id);        
        
        return $invoice_id;
        
    }
    
}

#####################################
#     Insert Labour Items           #
#####################################

function insert_labour_items($invoice_id, $labour_items = null) {
    
    $db = QFactory::getDbo();
    
    // Get Invoice Details
    $invoice_details = get_invoice_details($invoice_id); 
    
    // Insert Labour Items into database (if any)
    if($labour_items) {
        
        $sql = "INSERT INTO `".PRFX."invoice_labour` (`invoice_id`, `tax_system`, `description`, `unit_qty`, `unit_net`, `sales_tax_exempt`, `vat_tax_code`, `unit_tax_rate`, `unit_tax`, `unit_gross`, `sub_total_net`, `sub_total_tax`, `sub_total_gross`) VALUES ";
           
        foreach($labour_items as $labour_item) {
            
            // Add in missing sales tax exempt option - This prevents undefined variable errors
            $sales_tax_exempt = isset($labour_item['sales_tax_exempt']) ? $labour_item['sales_tax_exempt'] : 0;
            
            // Add in missing vat_tax_codes (i.e. submissions from 'no_tax' and 'sales_tax_cash' dont have VAT codes) - This prevents undefined variable errors
            $vat_tax_code = isset($labour_item['vat_tax_code']) ? $labour_item['vat_tax_code'] : get_default_vat_tax_code($invoice_details['tax_system']); 
            
            // Calculate the correct tax rate based on tax system (and exemption status)
            if($invoice_details['tax_system'] == 'sales_tax_cash' && $sales_tax_exempt) { $unit_tax_rate = 0.00; }
            elseif($invoice_details['tax_system'] == 'sales_tax_cash') { $unit_tax_rate = $invoice_details['sales_tax_rate']; }
            elseif(preg_match('/^vat_/', $invoice_details['tax_system'])) { $unit_tax_rate = get_vat_rate($labour_item['vat_tax_code']); }
            else { $unit_tax_rate = 0.00; }
                       
            // Build labour item totals based on selected TAX system
            $labour_totals = calculate_invoice_item_sub_totals($invoice_details['tax_system'], $labour_item['unit_qty'], $labour_item['unit_net'], $unit_tax_rate);
            
            $sql .="(".
                    
                    $db->qstr( $invoice_id                         ).",".                    
                    $db->qstr( $invoice_details['tax_system']      ).",".                    
                    $db->qstr( $labour_item['description']         ).",".                    
                    $db->qstr( $labour_item['unit_qty']            ).",".
                    $db->qstr( $labour_item['unit_net']            ).",".
                    $db->qstr( $sales_tax_exempt                   ).",".
                    $db->qstr( $vat_tax_code                       ).",".
                    $db->qstr( $unit_tax_rate                      ).",".
                    $db->qstr( $labour_totals['unit_tax']          ).",".
                    $db->qstr( $labour_totals['unit_gross']        ).",".                    
                    $db->qstr( $labour_totals['sub_total_net']     ).",".
                    $db->qstr( $labour_totals['sub_total_tax']     ).",".
                    $db->qstr( $labour_totals['sub_total_gross']   )."),";
            
        }
        
        // Strips off last comma as this is a joined SQL statement
        $sql = substr($sql , 0, -1);
        
        if(!$rs = $db->Execute($sql)) {
            force_error_page('database', __FILE__, __FUNCTION__, $db->ErrorMsg(), $sql, _gettext("Failed to insert Labour item into the database."));
        }
        
        return;
        
    }
        
}

#####################################
#     Insert Parts Items           #
#####################################

function insert_parts_items($invoice_id, $parts_items = null) {
    
    $db = QFactory::getDbo();    
    
    // Get Invoice Details
    $invoice_details = get_invoice_details($invoice_id); 
            
    // Insert Parts Items into database (if any)
    if($parts_items) {
        
        $sql = "INSERT INTO `".PRFX."invoice_parts` (`invoice_id`, `tax_system`, `description`, `unit_qty`, `unit_net`, `sales_tax_exempt`, `vat_tax_code`, `unit_tax_rate`, `unit_tax`, `unit_gross`, `sub_total_net`, `sub_total_tax`, `sub_total_gross`) VALUES ";
           
        foreach($parts_items as $parts_item) {
            
            // Add in missing sales tax exempt option - This prevents undefined variable errors
            $sales_tax_exempt = isset($parts_item['sales_tax_exempt']) ? $parts_item['sales_tax_exempt'] : 0;
            
            // Add in missing vat_tax_codes (i.e. submissions from 'no_tax' and 'sales_tax_cash' dont have VAT codes) - This prevents undefined variable errors
            $vat_tax_code = isset($parts_item['vat_tax_code']) ? $parts_item['vat_tax_code'] : get_default_vat_tax_code($invoice_details['tax_system']); 
            
            // Calculate the correct tax rate based on tax system (and exemption status)
            if($invoice_details['tax_system'] == 'sales_tax_cash' && $sales_tax_exempt) { $unit_tax_rate = 0.00; }
            elseif($invoice_details['tax_system'] == 'sales_tax_cash') { $unit_tax_rate = $invoice_details['sales_tax_rate']; }
            elseif(preg_match('/^vat_/', $invoice_details['tax_system'])) { $unit_tax_rate = get_vat_rate($parts_item['vat_tax_code']); }
            else { $unit_tax_rate = 0.00; }
                       
            // Build labour item totals based on selected TAX system
            $parts_totals = calculate_invoice_item_sub_totals($invoice_details['tax_system'], $parts_item['unit_qty'], $parts_item['unit_net'], $unit_tax_rate);
            
            $sql .="(".
                    
                    $db->qstr( $invoice_id                        ).",".                    
                    $db->qstr( $invoice_details['tax_system']     ).",".                    
                    $db->qstr( $parts_item['description']         ).",".                    
                    $db->qstr( $parts_item['unit_qty']            ).",".
                    $db->qstr( $parts_item['unit_net']            ).",".
                    $db->qstr( $sales_tax_exempt                  ).",".
                    $db->qstr( $vat_tax_code                      ).",".
                    $db->qstr( $unit_tax_rate                     ).",".
                    $db->qstr( $parts_totals['unit_tax']          ).",".
                    $db->qstr( $parts_totals['unit_gross']        ).",".                    
                    $db->qstr( $parts_totals['sub_total_net']     ).",".
                    $db->qstr( $parts_totals['sub_total_tax']     ).",".
                    $db->qstr( $parts_totals['sub_total_gross']   )."),";
            
        }
        
        // Strips off last comma as this is a joined SQL statement
        $sql = substr($sql , 0, -1);
        
        if(!$rs = $db->Execute($sql)) {
            force_error_page('database', __FILE__, __FUNCTION__, $db->ErrorMsg(), $sql, _gettext("Failed to insert parts item into the database."));
        }
        
        return;
        
    }
        
}

/*#####################################
#     Insert Labour Items           # // KEEP this old version for the code as a reference
##################################### // This function combines multiple arrays created by the invoice:edit page.

function insert_labour_items($invoice_id, $descriptions, $amounts, $qtys) {
    
    $db = QFactory::getDbo();
    
    // Insert Labour Items into database (if any)
    if($qtys > 0 ) {
        
        $i = 1;
        
        $sql = "INSERT INTO ".PRFX."invoice_labour (invoice_id, description, amount, qty, sub_total) VALUES ";
        
        foreach($qtys as $key) {
            
            // Rrename $key to $qty, and then below swap $qty[$i] --> $qty - removes the error, both work
            
            $sql .="(".
                    
                    $db->qstr( $invoice_id               ).",".                    
                    $db->qstr( $descriptions[$i]         ).",".
                    $db->qstr( $amounts[$i]              ).",".
                    $db->qstr( $qtys[$i]                 ).",".
                    $db->qstr( $qtys[$i] * $amounts[$i]  ).
                    
                    "),";
            
            $i++;
            
        }
        
        // Strips off last comma as this is a joined SQL statement
        $sql = substr($sql , 0, -1);
        
        if(!$rs = $db->Execute($sql)) {
            force_error_page('database', __FILE__, __FUNCTION__, $db->ErrorMsg(), $sql, _gettext("Failed to insert Labour item into the database."));
        }
        
    }
        
}
*/

#####################################
#   insert invoice prefill item     #
#####################################

function insert_invoice_prefill_item($VAR) {
    
    $db = QFactory::getDbo();
    
    $sql = "INSERT INTO ".PRFX."invoice_prefill_items SET
            description =". $db->qstr( $VAR['description']  ).",
            type        =". $db->qstr( $VAR['type']         ).",
            unit_net    =". $db->qstr( $VAR['unit_net']     ).",
            active      =". $db->qstr( $VAR['active']       );

    if(!$rs = $db->execute($sql)){        
        force_error_page('database', __FILE__, __FUNCTION__, $db->ErrorMsg(), $sql, _gettext("Failed to insert an invoice prefill item into the database."));
        
    } else {
        
        // Log activity       
        write_record_to_activity_log(_gettext("The Invoice Prefill Item").' '.$db->Insert_ID().' '._gettext("was added by").' '.QFactory::getUser()->login_display_name.'.');    
        
    }
    
}

/** Get Functions **/

#####################################
#   Get invoice details             #
#####################################

function get_invoice_details($invoice_id, $item = null) {
    
    $db = QFactory::getDbo();
    
    $sql = "SELECT * FROM ".PRFX."invoice_records WHERE invoice_id =".$db->qstr($invoice_id);
    
    if(!$rs = $db->execute($sql)){        
        force_error_page('database', __FILE__, __FUNCTION__, $db->ErrorMsg(), $sql, _gettext("Failed to get invoice details."));
    } else {
        
        // This makes sure there is a record to return to prevent errors (currently only needed for upgrade)
        if(!$rs->recordCount()) {
            
            return false;
                    
        } else {
        
            if($item === null){

                return $rs->GetRowAssoc(); 

            } else {

                return $rs->fields[$item];   

            } 
        }
    }
        
}

#########################################
#   Get All invoice labour items        #
#########################################

function get_invoice_labour_items($invoice_id) {
    
    $db = QFactory::getDbo();
    
    $sql = "SELECT * FROM ".PRFX."invoice_labour WHERE invoice_id=".$db->qstr($invoice_id);
    
    if(!$rs = $db->Execute($sql)) {
        force_error_page('database', __FILE__, __FUNCTION__, $db->ErrorMsg(), $sql, _gettext("Failed to get invoice labour items."));
    } else {
        
        if(!empty($rs)) {
        
            return $rs->GetArray();
        
        }
        
    }    
    
}

#######################################
#   Get invoice labour item details   #
#######################################

function get_invoice_labour_item_details($invoice_labour_id, $item = null) {
    
    $db = QFactory::getDbo();
    
    $sql = "SELECT * FROM ".PRFX."invoice_labour WHERE invoice_labour_id =".$db->qstr($invoice_labour_id);
    
    if(!$rs = $db->execute($sql)){        
        force_error_page('database', __FILE__, __FUNCTION__, $db->ErrorMsg(), $sql, _gettext("Failed to get invoice labour item details."));
    } else {
        
        if($item === null){
            
            return $rs->GetRowAssoc();
            
        } else {
            
            return $rs->fields[$item];   
            
        } 
        
    }
        
}

#####################################
#   Get All invoice parts items     #
#####################################

function get_invoice_parts_items($invoice_id) {
    
    $db = QFactory::getDbo();
    
    $sql = "SELECT * FROM ".PRFX."invoice_parts WHERE invoice_id=".$db->qstr( $invoice_id );
    
    if(!$rs = $db->Execute($sql)) {
        force_error_page('database', __FILE__, __FUNCTION__, $db->ErrorMsg(), $sql, _gettext("Failed to get invoice parts items."));
    } else {
        
        if(!empty($rs)) {
        
            return $rs->GetArray();
        
        }
        
    }    
    
}

#######################################
#   Get invoice parts item details    #
#######################################

function get_invoice_parts_item_details($invoice_parts_id, $item = null) {
    
    $db = QFactory::getDbo();
    
    $sql = "SELECT * FROM ".PRFX."invoice_parts WHERE invoice_parts_id =".$db->qstr($invoice_parts_id);
    
    if(!$rs = $db->execute($sql)){        
        force_error_page('database', __FILE__, __FUNCTION__, $db->ErrorMsg(), $sql, _gettext("Failed to get invoice parts item details."));
    } else {
        
        if($item === null){
            
            return $rs->GetRowAssoc(); 
            
        } else {
            
            return $rs->fields[$item];   
            
        } 
        
    }
        
}

#######################################
#   Get invoice prefill items         #
#######################################

function get_invoice_prefill_items($type = null, $status = null) {
    
    $db = QFactory::getDbo();
    
    $sql = "SELECT * FROM ".PRFX."invoice_prefill_items";
    
    // prepare the sql for the optional filter
    $sql .= " WHERE invoice_prefill_id >= 1";

    // filter by type
    if($type) { $sql .= " AND type=".$db->qstr($type);}    
    
    // filter by status
    if($status) {$sql .= " AND active=".$db->qstr($status);}
    
    if(!$rs = $db->Execute($sql)) {
        force_error_page('database', __FILE__, __FUNCTION__, $db->ErrorMsg(), $sql, _gettext("Failed to get the invoice prefill items for the selected status."));
    } else {
        
        if(!empty($rs)) {
        
            return $rs->GetArray();
        
        }
        
    }    
    
}

#####################################
#    Get Invoice Statuses           #
#####################################

function get_invoice_statuses($restricted_statuses = false) {
    
    $db = QFactory::getDbo();
    
    $sql = "SELECT * FROM ".PRFX."invoice_statuses";
    
    // Restrict statuses to those that are allowed to be changed by the user
    if($restricted_statuses) {
        $sql .= "\nWHERE status_key NOT IN ('partially_paid', 'paid', 'in_dispute', 'overdue', 'collections', 'refunded', 'cancelled', 'deleted')";
    }

    if(!$rs = $db->execute($sql)){        
        force_error_page('database', __FILE__, __FUNCTION__, $db->ErrorMsg(), $sql, _gettext("Failed to get invoice statuses."));
    } else {
        
        return $rs->GetArray();      
        
    }    
    
}

######################################
#  Get Invoice status display name   #
######################################

function get_invoice_status_display_name($status_key) {
    
    $db = QFactory::getDbo();
    
    $sql = "SELECT display_name FROM ".PRFX."invoice_statuses WHERE status_key=".$db->qstr($status_key);

    if(!$rs = $db->execute($sql)){        
        force_error_page('database', __FILE__, __FUNCTION__, $db->ErrorMsg(), $sql, _gettext("Failed to get the invoice status display name."));
    } else {
        
        return $rs->fields['display_name'];
        
    }    
    
}

############################################
#   Get Labour Invoice Sub Totals          #
############################################

function get_labour_items_sub_totals($invoice_id) {
    
    $db = QFactory::getDbo();
    
    // I could use sum_labour_items() 
    // NB: i dont think i need the aliases
    
    $sql = "SELECT
            SUM(sub_total_net) AS sub_total_net,
            SUM(sub_total_tax) AS sub_total_tax,
            SUM(sub_total_gross) AS sub_total_gross
            FROM ".PRFX."invoice_labour
            WHERE invoice_id=". $db->qstr($invoice_id);
    
    if(!$rs = $db->execute($sql)){        
        force_error_page('database', __FILE__, __FUNCTION__, $db->ErrorMsg(), $sql, _gettext("Failed to get the invoice labour sub total."));
    } else {
        
        return $rs->GetRowAssoc(); 
        
    }    
    
}

###########################################
#   Get Parts Invoice Sub Total           #
###########################################

function get_parts_items_sub_totals($invoice_id) {
    
    $db = QFactory::getDbo();
    
    // I could use sum_parts_items()
    // NB: i dont think i need the aliases
    
    $sql = "SELECT
            SUM(sub_total_net) AS sub_total_net,
            SUM(sub_total_tax) AS sub_total_tax,
            SUM(sub_total_gross) AS sub_total_gross
            FROM ".PRFX."invoice_parts
            WHERE invoice_id=". $db->qstr($invoice_id);
    
    if(!$rs = $db->execute($sql)){        
        force_error_page('database', __FILE__, __FUNCTION__, $db->ErrorMsg(), $sql, _gettext("Failed to get the invoice parts sub total."));
    } else {
        
        return $rs->GetRowAssoc(); 
        
    }  
}

/** Update Functions **/

####################################
#   update invoice static values   #  // This is used when a user updates an invoice before any payments
####################################

function update_invoice_static_values($invoice_id, $date, $due_date, $unit_discount_rate) {
    
    $db = QFactory::getDbo();
    
    $sql = "UPDATE ".PRFX."invoice_records SET
            date                =". $db->qstr( date_to_mysql_date($date)     ).",
            due_date            =". $db->qstr( date_to_mysql_date($due_date) ).",
            unit_discount_rate  =". $db->qstr( $unit_discount_rate           )."               
            WHERE invoice_id    =". $db->qstr( $invoice_id                   );

    if(!$rs = $db->execute($sql)){        
        
        force_error_page('database', __FILE__, __FUNCTION__, $db->ErrorMsg(), $sql, _gettext("Failed to update the invoice dates and discount rate."));
        
    } else {
        
        $invoice_details = get_invoice_details($invoice_id);
        
        // Create a Workorder History Note  
        insert_workorder_history_note($invoice_details['workorder_id'], _gettext("Invoice").' '.$invoice_id.' '._gettext("was updated by").' '.QFactory::getUser()->login_display_name.'.');
        
        // Log activity        
        $record = _gettext("Invoice").' '.$invoice_id.' '._gettext("was updated by").' '.QFactory::getUser()->login_display_name.'.';        
        write_record_to_activity_log($record, $invoice_details['employee_id'], $invoice_details['client_id'], $invoice_details['workorder_id'], $invoice_id);

        // Update last active record    
        update_client_last_active(get_invoice_details($invoice_id, 'client_id'));
        update_workorder_last_active(get_invoice_details($invoice_id, 'workorder_id'));
        update_invoice_last_active($invoice_id);
        
    }
    
}

#####################################
#     update invoice (full)         #  // not currently used
#####################################

function update_invoice_full($VAR, $doNotLog = false) {
    
    $db = QFactory::getDbo();
    
    $sql = "UPDATE ".PRFX."invoice_records SET     
            employee_id         =". $db->qstr( $VAR['employee_id']     ).", 
            client_id           =". $db->qstr( $VAR['client_id']       ).",
            workorder_id        =". $db->qstr( $VAR['workorder_id']    ).",               
            date                =". $db->qstr( $VAR['date']            ).",
            due_date            =". $db->qstr( $VAR['due_date']        ).", 
            tax_system          =". $db->qstr( $VAR['tax_system']      ).", 
            unit_discount_rate  =". $db->qstr( $VAR['unit_discount_rate']   ).",                            
            unit_discount       =". $db->qstr( $VAR['unit_discount'] ).",   
            unit_net            =". $db->qstr( $VAR['unit_net']      ).",
            sales_tax_rate      =". $db->qstr( $VAR['sales_tax_rate']  ).",
            unit_tax            =". $db->qstr( $VAR['unit_tax']      ).",             
            unit_gross          =". $db->qstr( $VAR['unit_gross']    ).", 
            unit_paid           =". $db->qstr( $VAR['unit_paid']     ).",
            balance             =". $db->qstr( $VAR['balance']         ).",
            opened_on           =". $db->qstr( $VAR['opened_on']       ).",
            closed_on           =". $db->qstr( $VAR['closed_on']       ).",
            last_active         =". $db->qstr( $VAR['last_active']     ).",
            status              =". $db->qstr( $VAR['status']          ).",
            is_closed           =". $db->qstr( $VAR['is_closed']       )."            
            WHERE invoice_id    =". $db->qstr( $VAR['invoice_id']      );

    if(!$rs = $db->execute($sql)) {        
        
        force_error_page('database', __FILE__, __FUNCTION__, $db->ErrorMsg(), $sql, _gettext("Failed to update the invoice."));
        
    } else {
    
        if (!$doNotLog) {
            
            // Create a Workorder History Note  
            insert_workorder_history_note($db, $VAR['workorder_id'], _gettext("Invoice").' '.$VAR['invoice_id'].' '._gettext("was updated by").' '.QFactory::getUser()->login_display_name.'.');

            // Log activity        
            $record = _gettext("Invoice").' '.$VAR['invoice_id'].' '._gettext("was updated by").' '.QFactory::getUser()->login_display_name.'.';        
            write_record_to_activity_log($record, $VAR['employee_id'], $VAR['client_id'], $VAR['workorder_id'], $VAR['invoice_id']);

            // Update last active record    
            update_client_last_active($db, $VAR['client_id']);
            update_workorder_last_active($db, $VAR['workorder_id']);
            update_invoice_last_active($db, $VAR['invoice_id']);        
        
        }
        
    } 
    
    return true;
    
}

#####################################
#   update invoice prefill item     #
#####################################

function update_invoice_prefill_item($VAR) {
    
    $db = QFactory::getDbo();
    
    $sql = "UPDATE ".PRFX."invoice_prefill_items SET
            description                 =". $db->qstr( $VAR['description']          ).",
            type                        =". $db->qstr( $VAR['type']                 ).",
            unit_net                    =". $db->qstr( $VAR['unit_net']             ).",
            active                      =". $db->qstr( $VAR['active']               )."            
            WHERE invoice_prefill_id    =". $db->qstr( $VAR['invoice_prefill_id']   );

    if(!$rs = $db->execute($sql)){        
        force_error_page('database', __FILE__, __FUNCTION__, $db->ErrorMsg(), $sql, _gettext("Failed to update an invoice labour rates item."));
        
    } else {
        
        // Log activity        
        write_record_to_activity_log(_gettext("The Invoice Prefill Item").' '.$VAR['invoice_prefill_id'].' '._gettext("was updated by").' '.QFactory::getUser()->login_display_name.'.');    

    }
    
}

############################
# Update Invoice Status    #
############################

function update_invoice_status($invoice_id, $new_status) {
    
    $db = QFactory::getDbo();
    
    // Get invoice details
    $invoice_details = get_invoice_details($invoice_id);
    
    // if the new status is the same as the current one, exit
    if($new_status == $invoice_details['status']) {        
        postEmulationWrite('warning_msg', _gettext("Nothing done. The new invoice status is the same as the current invoice status."));
        return false;
    }    
    
    // Set the appropriate employee_id
    $employee_id = ($new_status == 'unassigned') ? '' : $invoice_details['employee_id'];
    
    // Set the appropriate closed_on date
    $closed_on = ($new_status == 'closed') ? mysql_datetime() : '0000-00-00 00:00:00';
    
    $sql = "UPDATE ".PRFX."invoice_records SET   
            employee_id         =". $db->qstr( $employee_id     ).",
            status              =". $db->qstr( $new_status      ).",
            closed_on           =". $db->qstr( $closed_on       )."  
            WHERE invoice_id    =". $db->qstr( $invoice_id      );

    if(!$rs = $db->Execute($sql)) {
        force_error_page('database', __FILE__, __FUNCTION__, $db->ErrorMsg(), $sql, _gettext("Failed to update an Invoice Status."));
        
    } else {    
    
        // Update invoice 'is_closed' boolean
        if($new_status == 'paid' || $new_status == 'refunded' || $new_status == 'cancelled' || $new_status == 'deleted') {
            update_invoice_closed_status($invoice_id, 'closed');
        } else {
            update_invoice_closed_status($invoice_id, 'open');
        }
        
        // Status updated message
        postEmulationWrite('information_msg', _gettext("Invoice status updated."));  
        
        // For writing message to log file, get invoice status display name
        $inv_status_diplay_name = _gettext(get_invoice_status_display_name($new_status));
        
        // Create a Workorder History Note       
        insert_workorder_history_note($invoice_details['workorder_id'], _gettext("Invoice Status updated to").' '.$inv_status_diplay_name.' '._gettext("by").' '.QFactory::getUser()->login_display_name.'.');
        
        // Log activity        
        $record = _gettext("Invoice").' '.$invoice_id.' '._gettext("Status updated to").' '.$inv_status_diplay_name.' '._gettext("by").' '.QFactory::getUser()->login_display_name.'.';
        write_record_to_activity_log($record, $invoice_details['employee_id'], $invoice_details['client_id'], $invoice_details['workorder_id'], $invoice_id);
        
        // Update last active record
        update_client_last_active($invoice_details['client_id']);
        update_workorder_last_active($invoice_details['workorder_id']);
        update_invoice_last_active($invoice_id);                
        
        return true;
        
    }
    
}

###################################
# Update invoice Closed Status    #
###################################

function update_invoice_closed_status($invoice_id, $new_closed_status) {
    
    $db = QFactory::getDbo();
    
    if($new_closed_status == 'open') {
        
        $sql = "UPDATE ".PRFX."invoice_records SET
                closed_on           = '0000-00-00 00:00:00',
                is_closed           =". $db->qstr( 0                )."
                WHERE invoice_id    =". $db->qstr( $invoice_id      );
                
    }
    
    if($new_closed_status == 'closed') {
        
        $sql = "UPDATE ".PRFX."invoice_records SET
                closed_on           =". $db->qstr( mysql_datetime() ).",
                is_closed           =". $db->qstr( 1                )."
                WHERE invoice_id    =". $db->qstr( $invoice_id      );
    }    
    
    if(!$rs = $db->Execute($sql)) {
        force_error_page('database', __FILE__, __FUNCTION__, $db->ErrorMsg(), $sql, _gettext("Failed to update an invoice Closed status."));
    }
    
}

#################################
#    Update Last Active         #
#################################

function update_invoice_last_active($invoice_id = null) {
    
    $db = QFactory::getDbo();
    
    // compensate for some workorders not having invoices
    if(!$invoice_id) { return; }
    
    $sql = "UPDATE ".PRFX."invoice_records SET
            last_active=".$db->qstr( mysql_datetime() )."
            WHERE invoice_id=".$db->qstr($invoice_id);
    
    if(!$rs = $db->Execute($sql)) {
        force_error_page('database', __FILE__, __FUNCTION__, $db->ErrorMsg(), $sql, _gettext("Failed to update an invoice last active time."));
    }
    
}

#################################
#    Update invoice refund ID   #
#################################

function update_invoice_refund_id($invoice_id, $refund_id) {
    
    $db = QFactory::getDbo();
    
    $sql = "UPDATE ".PRFX."invoice_records SET
            refund_id           =".$db->qstr($refund_id)."
            WHERE invoice_id    =".$db->qstr($invoice_id);
    
    if(!$rs = $db->Execute($sql)) {
        force_error_page('database', __FILE__, __FUNCTION__, $db->ErrorMsg(), $sql, _gettext("Failed to add a Refund ID to the invoice."));
    }
    
}

/** Close Functions **/

#####################################
#   Refund Invoice                  #
#####################################

function refund_invoice($refund_details) {
    
    // Make sure the invoice can be refunded
    if(!check_invoice_can_be_refunded($refund_details['invoice_id'])) {
        return false;
    }
    
    // Get invoice details
    $invoice_details = get_invoice_details($refund_details['invoice_id']);
    
    // Insert refund record and return refund_id
    $refund_id = insert_refund($refund_details);
    
    // Refund any Vouchers
    refund_invoice_vouchers($refund_details['invoice_id'], $refund_id);

    // Update the invoice with the new refund_id
    update_invoice_refund_id($refund_details['invoice_id'], $refund_id);    
    
    // Change the invoice status to refunded (I do this here to maintain consistency)
    update_invoice_status($refund_details['invoice_id'], 'refunded');
        
    // Create a Workorder History Note  
    insert_workorder_history_note($invoice_details['invoice_id'], _gettext("Invoice").' '.$refund_details['invoice_id'].' '._gettext("was refunded by").' '.QFactory::getUser()->login_display_name.'.');

    // Log activity        
    $record = _gettext("Invoice").' '.$refund_details['invoice_id'].' '._gettext("for Work Order").' '.$invoice_details['invoice_id'].' '._gettext("was refunded by").' '.QFactory::getUser()->login_display_name.'.';
    write_record_to_activity_log($record, $invoice_details['employee_id'], $invoice_details['client_id'], $invoice_details['workorder_id'], $refund_details['invoice_id']);

    // Update last active record
    update_client_last_active($invoice_details['client_id']);
    update_workorder_last_active($invoice_details['workorder_id']);
    update_invoice_last_active($invoice_details['invoice_id']);

    return $refund_id;
    
}

#####################################
#   Cancel Invoice                  # // This does not delete information i.e. client went bust and did not pay
#####################################

function cancel_invoice($invoice_id) {
    
    // Make sure the invoice can be cancelled
    if(!check_invoice_can_be_cancelled($invoice_id)) {        
        return false;
    }
    
    // Get invoice details
    $invoice_details = get_invoice_details($invoice_id);  
    
    // Cancel any Vouchers
    cancel_invoice_vouchers($invoice_id);
    
    // Change the invoice status to cancelled (I do this here to maintain consistency)
    update_invoice_status($invoice_id, 'cancelled');      
        
    // Create a Workorder History Note  
    insert_workorder_history_note($invoice_details['invoice_id'], _gettext("Invoice").' '.$invoice_id.' '._gettext("was cancelled by").' '.QFactory::getUser()->login_display_name.'.');

    // Log activity        
    $record = _gettext("Invoice").' '.$invoice_id.' '._gettext("for Work Order").' '.$invoice_id.' '._gettext("was cancelled by").' '.QFactory::getUser()->login_display_name.'.';
    write_record_to_activity_log($record, $invoice_details['employee_id'], $invoice_details['client_id'], $invoice_details['workorder_id'], $invoice_id);

    // Update last active record
    update_client_last_active($invoice_details['client_id']);
    update_workorder_last_active($invoice_details['workorder_id']);
    update_invoice_last_active($invoice_id);

    return true;
    
}

/** Delete Functions **/

#####################################
#   Delete Invoice                  #
#####################################

function delete_invoice($invoice_id) {
    
    $db = QFactory::getDbo();
        
    // Make sure the invoice can be deleted 
    if(!check_invoice_can_be_deleted($invoice_id)) {        
        return false;
    }
    
    // Get invoice details
    $invoice_details = get_invoice_details($invoice_id);
    
    // Delete any Vouchers
    delete_invoice_vouchers($invoice_id);  
    
    // Delete parts and labour
    delete_invoice_labour_items($invoice_id);
    delete_invoice_parts_items($invoice_id);
    
    // Change the invoice status to deleted (I do this here to maintain log consistency)
    update_invoice_status($invoice_id, 'deleted'); 
    
    // Build the data to replace the invoice record (some stuff has just been updated with update_invoice_status())
    $sql = "UPDATE ".PRFX."invoice_records SET        
            employee_id         = '', 
            client_id           = '',
            workorder_id        = '',               
            date                = '0000-00-00',    
            due_date            = '0000-00-00', 
            tax_system          = '',  
            unit_discount_rate  = '0.00',                         
            unit_discount       = '0.00', 
            unit_net            = '0.00', 
            sales_tax_rate      = '0.00', 
            unit_tax            = '0.00',             
            unit_gross          = '0.00',  
            unit_paid           = '0.00', 
            balance             = '0.00',
            status              = 'deleted',
            opened_on           = '0000-00-00 00:00:00',
            closed_on           = '0000-00-00 00:00:00',
            last_active         = '0000-00-00 00:00:00',            
            is_closed           = '1'            
            WHERE invoice_id    =". $db->qstr( $invoice_id  );
    
    if(!$rs = $db->execute($sql)){        
        force_error_page('database', __FILE__, __FUNCTION__, $db->ErrorMsg(), null, _gettext("Failed to delete the invoice."));
    } else {
        
        // Remove the invoice_if from the related Workorder record (if present)
        update_workorder_invoice_id($invoice_details['workorder_id'], '');               
        
        // Create a Workorder History Note  
        insert_workorder_history_note($invoice_id, _gettext("Invoice").' '.$invoice_id.' '._gettext("was deleted by").' '.QFactory::getUser()->login_display_name.'.');
            
        // Log activity        
        $record = _gettext("Invoice").' '.$invoice_id.' '._gettext("for Work Order").' '.$invoice_id.' '._gettext("was deleted by").' '.QFactory::getUser()->login_display_name.'.';
        write_record_to_activity_log($record, $invoice_details['employee_id'], $invoice_details['client_id'], $invoice_details['workorder_id'], $invoice_id);
        
        // Update workorder status
        update_workorder_status($invoice_details['workorder_id'], 'closed_without_invoice');        
                
        // Update last active record
        update_client_last_active($invoice_details['client_id']);
        update_workorder_last_active($invoice_details['workorder_id']);
        update_invoice_last_active($invoice_id);
        
        return true;
        
    }
    
}

#####################################
#   Delete Labour Item              #
#####################################

function delete_invoice_labour_item($invoice_labour_id) {
    
    $db = QFactory::getDbo();
    
    $invoice_details = get_invoice_details(get_invoice_labour_item_details($invoice_labour_id, 'invoice_id'));    
    
    $sql = "DELETE FROM ".PRFX."invoice_labour WHERE invoice_labour_id=" . $db->qstr($invoice_labour_id);

    if(!$rs = $db->execute($sql)){        
        force_error_page('database', __FILE__, __FUNCTION__, $db->ErrorMsg(), $sql, _gettext("Failed to delete an invoice labour item."));
    } else {
        
        // Recalculate the invoice totals and update them
        recalculate_invoice_totals($invoice_details['invoice_id']);

        // Create a Workorder History Note 
        // not currently needed
        
        // Log activity        
        $record = _gettext("The Invoice Labour Item").' '.$invoice_labour_id.' '._gettext("was deleted by").' '.QFactory::getUser()->login_display_name.'.';
        write_record_to_activity_log($record, $invoice_details['employee_id'], $invoice_details['client_id'], $invoice_details['workorder_id'], $invoice_details['invoice_id']);
        
        // Update last active record
        update_client_last_active($invoice_details['client_id']);
        update_workorder_last_active($invoice_details['workorder_id']);
        update_invoice_last_active($invoice_details['invoice_id']);  
        
        return true;

    }
    
}

#############################################
#   Delete an invoice's Labour Items (ALL)  #
#############################################

function delete_invoice_labour_items($invoice_id) {
    
    $db = QFactory::getDbo();
    
    $sql = "DELETE FROM ".PRFX."invoice_labour WHERE invoice_id=" . $db->qstr($invoice_id);

    if(!$rs = $db->execute($sql)){        
        force_error_page('database', __FILE__, __FUNCTION__, $db->ErrorMsg(), $sql, _gettext("Failed to delete all of an invoice's labour items."));
    } else {
        
        return true;

    }
    
}

#####################################
#   Delete Parts Item               #
#####################################

function delete_invoice_parts_item($invoice_parts_id) {
    
    $db = QFactory::getDbo();
    
    $invoice_details = get_invoice_details(get_invoice_parts_item_details($invoice_parts_id, 'invoice_id'));  
    
    $sql = "DELETE FROM ".PRFX."invoice_parts WHERE invoice_parts_id=" . $db->qstr($invoice_parts_id);

    if(!$rs = $db->execute($sql)){        
        force_error_page('database', __FILE__, __FUNCTION__, $db->ErrorMsg(), $sql, _gettext("Failed to delete an invoice parts item."));
        
    } else {
        
        // Recalculate the invoice totals and update them
        recalculate_invoice_totals($invoice_details['invoice_id']);
        
        // Create a Workorder History Note 
        // not currently needed
        
        // Log activity        
        $record = _gettext("The Invoice Parts Item").' '.$invoice_parts_id.' '._gettext("was deleted by").' '.QFactory::getUser()->login_display_name.'.';
        write_record_to_activity_log($record, $invoice_details['employee_id'], $invoice_details['client_id'], $invoice_details['workorder_id'], $invoice_details['invoice_id']);
        
        // Update last active record
        update_client_last_active($invoice_details['client_id']);
        update_workorder_last_active($invoice_details['workorder_id']);
        update_invoice_last_active($invoice_details['invoice_id']);  
        
        return true;

    }
    
}

#############################################
#   Delete an invoice's Parts Items (ALL)   #
#############################################

function delete_invoice_parts_items($invoice_id) {
    
    $db = QFactory::getDbo();
    
    $sql = "DELETE FROM ".PRFX."invoice_parts WHERE invoice_id=" . $db->qstr($invoice_id);

    if(!$rs = $db->execute($sql)){        
        force_error_page('database', __FILE__, __FUNCTION__, $db->ErrorMsg(), $sql, _gettext("Failed to delete all of an invoice's parts items."));
    } else {
        
        return true;

    }
    
}

#####################################
#     delete labour rate item       #
#####################################

function delete_invoice_prefill_item($invoice_prefill_id) {
    
    $db = QFactory::getDbo();
    
    $sql = "DELETE FROM ".PRFX."invoice_prefill_items WHERE invoice_prefill_id =".$db->qstr($invoice_prefill_id);

    if(!$rs = $db->execute($sql)){        
        force_error_page('database', __FILE__, __FUNCTION__, $db->ErrorMsg(), $sql, _gettext("Failed to delete an invoice prefill item."));
        
    } else {
        
        // Log activity        
        write_record_to_activity_log(_gettext("The Invoice Prefill Item").' '.$invoice_prefill_id.' '._gettext("was deleted by").' '.QFactory::getUser()->login_display_name.'.');
        
        return true;

    }
    
}

/** Other Functions **/

################################################
#   calculate an Invoice Item Sub Totals       #  // remove sales tax rate? or should i put it back to vat rate
################################################

function calculate_invoice_item_sub_totals($tax_system, $unit_qty, $unit_net, $unit_tax_rate = null) {
           
    $item_totals = array();
    
    // No Tax
    if($tax_system == 'no_tax') {        
        $item_totals['unit_tax'] = 0.00;
        $item_totals['unit_gross'] = $unit_net;
        $item_totals['sub_total_net'] = $unit_net * $unit_qty;
        $item_totals['sub_total_tax'] = 0.00;
        $item_totals['sub_total_gross'] = $item_totals['sub_total_net'];
    }
    
    // Sales Tax Calculations
    if($tax_system == 'sales_tax_cash') {        
        $item_totals['unit_tax'] = $unit_net * ($unit_tax_rate / 100);
        $item_totals['unit_gross'] = $unit_net + $item_totals['unit_tax'];
        $item_totals['sub_total_net'] = $unit_net * $unit_qty;
        $item_totals['sub_total_tax'] = $item_totals['sub_total_net'] * ($unit_tax_rate / 100);
        $item_totals['sub_total_gross'] = $item_totals['sub_total_net'] + $item_totals['sub_total_tax'];
    }
    
    // VAT Calculations
    if(preg_match('/^vat_/', $tax_system)) {        
        $item_totals['unit_tax'] = $unit_net * ($unit_tax_rate / 100);
        $item_totals['unit_gross'] = $unit_net + $item_totals['unit_tax'];
        $item_totals['sub_total_net'] = $unit_net * $unit_qty;
        $item_totals['sub_total_tax'] = $item_totals['sub_total_net'] * ($unit_tax_rate / 100);
        $item_totals['sub_total_gross'] = $item_totals['sub_total_net'] + $item_totals['sub_total_tax'];
    }
    
    return $item_totals;
    
}

#####################################
#   Recalculate Invoice Totals      #   ///  re-check these calcuclationsa s they are wrong (not much though) i should account for vouchers as if they had tax allow for development later.
#####################################  // Vouchers are not discounted

function recalculate_invoice_totals($invoice_id) {
    
    $db = QFactory::getDbo();
    
    $invoice_details            = get_invoice_details($invoice_id);    
    
    $labour_items_sub_totals    = get_labour_items_sub_totals($invoice_id); 
    $parts_items_sub_totals     = get_parts_items_sub_totals($invoice_id);   
    $voucher_sub_totals         = get_invoice_vouchers_sub_totals($invoice_id);
    
    $unit_discount              = ($labour_items_sub_totals['sub_total_net'] + $parts_items_sub_totals['sub_total_net']) * ($invoice_details['unit_discount_rate'] / 100); // divide by 100; turns 17.5 in to 0.17575
    $unit_net                   = ($labour_items_sub_totals['sub_total_net'] + $parts_items_sub_totals['sub_total_net'] + $voucher_sub_totals['sub_total_net']) - $unit_discount;
    $unit_tax                   = $labour_items_sub_totals['sub_total_tax'] + $parts_items_sub_totals['sub_total_tax'] + $voucher_sub_totals['sub_total_tax'];
    $unit_gross                 = $unit_net + $unit_tax;    
    $payments_sub_total         = sum_payments(null, null, 'date', null, null, 'valid', 'invoice', null, null, null, $invoice_id);
    $balance                    = $unit_gross - $payments_sub_total;

    $sql = "UPDATE ".PRFX."invoice_records SET            
            unit_discount       =". $db->qstr( $unit_discount       ).",
            unit_net            =". $db->qstr( $unit_net            ).",
            unit_tax            =". $db->qstr( $unit_tax            ).",
            unit_gross          =". $db->qstr( $unit_gross          ).",
            unit_paid           =". $db->qstr( $payments_sub_total  ).",
            balance             =". $db->qstr( $balance             )."
            WHERE invoice_id    =". $db->qstr( $invoice_id          );

    if(!$rs = $db->execute($sql)){        
        force_error_page('database', __FILE__, __FUNCTION__, $db->ErrorMsg(), $sql, _gettext("Failed to recalculate the invoice totals."));
    } else {
     
        /* Update Status - only change if there is a change in status*/        
        
        // No invoiceable amount, set to pending (if not already)
        if($unit_gross == 0 && $invoice_details['status'] != 'pending') {
            update_invoice_status($invoice_id, 'pending');
        }
        
        // Has invoiceable amount with no payments, set to unpaid (if not already)
        elseif($unit_gross > 0 && $unit_gross == $balance && $invoice_details['status'] != 'unpaid') {
            update_invoice_status($invoice_id, 'unpaid');
        }
        
        // Has invoiceable amount with partially payment, set to partially paid (if not already)
        elseif($unit_gross > 0 && $payments_sub_total > 0 && $payments_sub_total < $unit_gross && $invoice_details['status'] != 'partially_paid') {            
            update_invoice_status($invoice_id, 'partially_paid');
        }
        
        // Has invoicable amount and the payment(s) match the invoiceable amount, set to paid (if not already)
        elseif($unit_gross > 0 && $unit_gross == $payments_sub_total && $invoice_details['status'] != 'paid') {            
            update_invoice_status($invoice_id, 'paid');
        }        
        
        return;        
        
    }
    
}

#####################################
#   Upload labour rates CSV file    #
#####################################

function upload_invoice_prefill_items_csv($VAR) {
    
    $db = QFactory::getDbo();

    // Allowed extensions
    $allowedExts = array('csv');
    
    // Get file extension
    $filename_info = pathinfo($_FILES['invoice_prefill_csv']['name']);
    $extension = $filename_info['extension'];
    
    // Validate the uploaded file is allowed (extension, mime type, 0 - 2mb)
    if ((($_FILES['invoice_prefill_csv']['type'] == 'text/csv'))            
            || ($_FILES['invoice_prefill_csv']['type'] == 'application/vnd.ms-excel')     // CSV files created by excel - i might remove this
                //|| ($_FILES['invoice_prefill_csv']['type'] == 'text/plain')             // this seems a bit dangerous   
            && ($_FILES['invoice_prefill_csv']['size'] > 0)   
            && ($_FILES['invoice_prefill_csv']['size'] < 2048000)
            && in_array($extension, $allowedExts)) {

        // Check for file submission errors and echo them
        if ($_FILES['invoice_prefill_csv']['error'] > 0 ) {
            echo _gettext("Return Code").': ' . $_FILES['invoice_prefill_csv']['error'] . '<br />';                

        // If no errors then proceed to processing the data
        } else {        

            // Empty Current Invoice Rates Table (if set)
            if($VAR['empty_prefill_items_table'] === '1') {
                
                $sql = "TRUNCATE ".PRFX."invoice_prefill_items";
                
                if(!$rs = $db->execute($sql)) {
                    force_error_page('database', __FILE__, __FUNCTION__, $db->ErrorMsg(), $sql, _gettext("Failed to empty the prefill items table."));
                }
            }
            
            // Open CSV file            
            $handle = fopen($_FILES['invoice_prefill_csv']['tmp_name'], 'r');

            // Row counter to allow for header line
            $row = 1;

            // Read CSV data and insert into database            
            while (($data = fgetcsv($handle)) !== FALSE) {
                
                // Skip the first line with the column names
                if($row == 1) {                    
                    $row++;
                    continue;               
                }

                $sql = "INSERT INTO ".PRFX."invoice_prefill_items(description, type, unit_net, active) VALUES ('$data[0]','$data[1]','$data[2]','$data[3]')";

                if(!$rs = $db->execute($sql)) {
                    force_error_page('database', __FILE__, __FUNCTION__, $db->ErrorMsg(), $sql, _gettext("Failed to insert the new prefill items into the database."));
                }
                
                $row++;

            }

            // Close CSV file
            fclose($handle);

            // Delete CSV file - not sure this is needed becaus eit is temp
            unlink($_FILES['invoice_prefill_csv']['tmp_name']);
            
            // Log activity        
            write_record_to_activity_log(_gettext("Invoice Prefill Items were uploaded via csv by").' '.QFactory::getUser()->login_display_name.'.'); 

        }

    // If file is invalid then load the error page  
    } else {
        
        /*
        echo "Upload: "    . $_FILES['invoice_prefill_csv']['name']           . '<br />';
        echo "Type: "      . $_FILES['invoice_prefill_csv']['type']           . '<br />';
        echo "Size: "      . ($_FILES['invoice_prefill_csv']['size'] / 1024)  . ' Kb<br />';
        echo "Temp file: " . $_FILES['invoice_prefill_csv']['tmp_name']       . '<br />';
        echo "Stored in: " . MEDIA_DIR . $_FILES['file']['name']       ;
         */
        force_error_page('database', __FILE__, __FUNCTION__, $db->ErrorMsg(), $sql, _gettext("Failed to update the invoice labour rates because the submitted file was invalid."));

    }     

}

##################################################
#   Export Invoice Prefill Items as a CSV file   #
##################################################

function export_invoice_prefill_items_csv() {
    
    $db = QFactory::getDbo();
    
    $sql = "SELECT description, type, unit_net, active FROM ".PRFX."invoice_prefill_items";
    
    if(!$rs = $db->Execute($sql)) {        
        force_error_page('database', __FILE__, __FUNCTION__, $db->ErrorMsg(), $sql, _gettext("Failed to get invoice prefill items from the database."));
    } else {        
        
        $prefill_items = $rs->GetArray();
        
        // output headers so that the file is downloaded rather than displayed
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=qwcrm_invoice_prefill_items.csv');
        
        // create a file pointer connected to the output stream
        $output_stream = fopen('php://output', 'w');
        
        // output the column headings
        fputcsv($output_stream, array(_gettext("Description"), _gettext("Type"), _gettext("Amount"), _gettext("Active")));

        // loop over the rows, outputting them
        foreach($prefill_items as $key => $value) {
            $row = array($value['description'], $value['type'], $value['unit_net'], $value['active']);
            fputcsv($output_stream, $row);            
        }       
        
        // close the csv file
        fclose($output_stream);
        
        // Log activity        
        write_record_to_activity_log(_gettext("Invoice Prefill Items were exported by").' '.QFactory::getUser()->login_display_name.'.');
        
    }    
    
}


#########################################
# Assign Workorder to another employee  #
#########################################

function assign_invoice_to_employee($invoice_id, $target_employee_id) {
    
    $db = QFactory::getDbo();
    
    // get the invoice details
    $invoice_details = get_invoice_details($invoice_id);
    
    // if the new employee is the same as the current one, exit
    if($target_employee_id == $invoice_details['employee_id']) {         
        postEmulationWrite('warning_msg', _gettext("Nothing done. The new employee is the same as the current employee."));
        return false;
    }     
    
    // only change invoice status if unassigned
    if($invoice_details['status'] == 'unassigned') {
        
        $sql = "UPDATE ".PRFX."invoice_records SET
                employee_id         =". $db->qstr( $target_employee_id  ).",
                status              =". $db->qstr( 'assigned'           )."
                WHERE invoice_id    =". $db->qstr( $invoice_id          );

    // Keep the same invoice status    
    } else {    
        
        $sql = "UPDATE ".PRFX."invoice_records SET
                employee_id         =". $db->qstr( $target_employee_id  )."            
                WHERE invoice_id    =". $db->qstr( $invoice_id          );

    }
    
    if(!$rs = $db->Execute($sql)) {
        force_error_page('database', __FILE__, __FUNCTION__, $db->ErrorMsg(), $sql, _gettext("Failed to assign a Work Order to an employee."));
        
    } else {
        
        // Assigned employee success message
        postEmulationWrite('information_msg', _gettext("Assigned employee updated."));        
        
        // Get Logged in Employee's Display Name        
        $logged_in_employee_display_name = QFactory::getUser()->login_display_name;
        
        // Get the currently assigned employee ID
        $assigned_employee_id = $invoice_details['employee_id'];
        
        // Get the Display Name of the currently Assigned Employee
        if($assigned_employee_id == ''){
            $assigned_employee_display_name = _gettext("Unassigned");            
        } else {            
            $assigned_employee_display_name = get_user_details($assigned_employee_id, 'display_name');
        }
        
        // Get the Display Name of the Target Employee        
        $target_employee_display_name = get_user_details($target_employee_id, 'display_name');
        
        // Creates a History record
        insert_workorder_history_note($invoice_details['workorder_id'], _gettext("Invoice").' '.$invoice_id.' '._gettext("has been assigned to").' '.$target_employee_display_name.' '._gettext("from").' '.$assigned_employee_display_name.' '._gettext("by").' '. $logged_in_employee_display_name.'.');

        // Log activity        
        $record = _gettext("Invoice").' '.$invoice_id.' '._gettext("has been assigned to").' '.$target_employee_display_name.' '._gettext("from").' '.$assigned_employee_display_name.' '._gettext("by").' '. $logged_in_employee_display_name.'.';
        write_record_to_activity_log($record, $target_employee_id, $invoice_details['client_id'], $invoice_details['workorder_id'], $invoice_id);
                
        // Update last active record
        update_user_last_active($invoice_details['employee_id']);
        update_user_last_active($target_employee_id);
        update_client_last_active($invoice_details['client_id']);
        update_workorder_last_active($invoice_details['workorder_id']);
        update_invoice_last_active($invoice_id);
        
        return true;
        
    }
    
}
 
##########################################################
#  Check if the invoice status is allowed to be changed  #
##########################################################

 function check_invoice_status_can_be_changed($invoice_id) {
     
    // Get the invoice details
    $invoice_details = get_invoice_details($invoice_id);
    
    // Is partially paid
    if($invoice_details['status'] == 'partially_paid') {
        //postEmulationWrite('warning_msg', _gettext("The invoice status cannot be changed because the invoice has payments and is partially paid."));
        return false;        
    }
    
    // Is paid
    if($invoice_details['status'] == 'paid') {
        //postEmulationWrite('warning_msg', _gettext("The invoice status cannot be changed because the invoice has payments and is paid."));
        return false;        
    }
    
    // Is partially refunded (not currently used)
    if($invoice_details['status'] == 'partially_refunded') {
        //postEmulationWrite('warning_msg', _gettext("The invoice status cannot be changed because the invoice has been partially refunded."));
        return false;        
    }
    
    // Is refunded
    if($invoice_details['status'] == 'refunded') {
        //postEmulationWrite('warning_msg', _gettext("The invoice status cannot be changed because the invoice has been refunded."));
        return false;        
    }
    
    // Is cancelled
    if($invoice_details['status'] == 'cancelled') {
        //postEmulationWrite('warning_msg', _gettext("The invoice status cannot be changed because the invoice has been cancelled."));
        return false;        
    }
    
    // Is deleted
    if($invoice_details['status'] == 'deleted') {
        //postEmulationWrite('warning_msg', _gettext("The invoice status cannot be changed because the invoice has been deleted."));
        return false;        
    }
        
    // Has payments (Fallback - is currently not needed because of statuses, but it might be used for information reporting later)
    if(count_payments(null, null, 'date', null, null, null, 'invoice', null, null, null, $invoice_id)) {       
        //postEmulationWrite('warning_msg', _gettext("The invoice status cannot be changed because the invoice has payments."));
        return false;        
    }

    // Does the invoice have any Vouchers preventing changing the invoice status
    if(!check_invoice_vouchers_allow_editing($invoice_id)) {
        //postEmulationWrite('warning_msg', _gettext("The invoice cannot be refunded because of Vouchers on it prevent this."));
        return false;
    } 
    
    // All checks passed
    return true;     
     
 }

###############################################################
#   Check to see if the invoice can be refunded (by status)   #
###############################################################

function check_invoice_can_be_refunded($invoice_id) {
    
    // Get the invoice details
    $invoice_details = get_invoice_details($invoice_id);
        
    // Is partially paid
    if($invoice_details['status'] == 'partially_paid') {
        //postEmulationWrite('warning_msg', _gettext("This invoice cannot be refunded because the invoice is partially paid."));
        return false;
    }
    
    // Is partially refunded (not currently used)
    if($invoice_details['status'] == 'partially_refunded') {
        //postEmulationWrite('warning_msg', _gettext("The invoice status cannot be changed because the invoice has been partially refunded."));
        return false;        
    }
        
    // Is refunded
    if($invoice_details['status'] == 'refunded') {
        //postEmulationWrite('warning_msg', _gettext("The invoice cannot be refunded because the invoice has already been refunded."));
        return false;        
    }
    
    // Is cancelled
    if($invoice_details['status'] == 'cancelled') {
        //postEmulationWrite('warning_msg', _gettext("The invoice cannot be refunded because the invoice has been cancelled."));
        return false;        
    }
    
    // Is deleted
    if($invoice_details['status'] == 'deleted') {
        //postEmulationWrite('warning_msg', _gettext("The invoice cannot be refunded because the invoice has been deleted."));
        return false;        
    }    
    
    // Has no payments (Fallback - is currently not needed because of statuses, but it might be used for information reporting later)
    if(!count_payments(null, null, 'date', null, null, null, 'invoice', null, null, null, $invoice_id)) { 
        //postEmulationWrite('warning_msg', _gettext("This invoice cannot be refunded because the invoice has no payments."));
        return false;        
    }

    // Has Refunds (Fallback - is currently not needed because of statuses, but it might be used for information reporting later)
    if(count_refunds(null, null, null, null, $invoice_id) > 0) {
        //postEmulationWrite('warning_msg', _gettext("The invoice cannot be refunded because the invoice has already been refunded."));
        return false;
    }
    
    // Does the invoice have any Vouchers preventing refunding the invoice (i.e. any that have been used)
    if(!check_invoice_vouchers_allow_refunding($invoice_id)) {
        //postEmulationWrite('warning_msg', _gettext("The invoice cannot be refunded because of Vouchers on it prevent this."));
        return false;
    }    
     
    // All checks passed
    return true;
    
}

###############################################################
#   Check to see if the invoice can be cancelled              #
###############################################################

function check_invoice_can_be_cancelled($invoice_id) {
    
    // Get the invoice details
    $invoice_details = get_invoice_details($invoice_id);
    
    // Does not have a balance
    if($invoice_details['balance'] == 0) {
        //postEmulationWrite('warning_msg', _gettext("This invoice cannot be cancelled because the invoice does not have a balance."));
        return false;
    }
    
    // Is partially paid
    if($invoice_details['status'] == 'partially_paid') {
        //postEmulationWrite('warning_msg', _gettext("This invoice cannot be cancelled because the invoice is partially paid."));
        return false;
    }
    
    // Is partially refunded (not currently used)
    if($invoice_details['status'] == 'partially_refunded') {
        //postEmulationWrite('warning_msg', _gettext("The invoice status cannot be changed because the invoice has been partially refunded."));
        return false;        
    }
     
    
    // Is refunded
    if($invoice_details['status'] == 'refunded') {
        //postEmulationWrite('warning_msg', _gettext("The invoice cannot be cancelled because the invoice has been refunded."));
        return false;        
    }
    
    // Is cancelled
    if($invoice_details['status'] == 'cancelled') {
        //postEmulationWrite('warning_msg', _gettext("The invoice cannot be cancelled because the invoice has already been cancelled."));
        return false;        
    }
    
    // Is deleted
    if($invoice_details['status'] == 'deleted') {
        //postEmulationWrite('warning_msg', _gettext("The invoice cannot be cancelled because the invoice has been deleted."));
        return false;        
    }    
    
    // Has no payments (Fallback - is currently not needed because of statuses, but it might be used for information reporting later)
    if(count_payments(null, null, 'date', null, null, null, 'invoice', null, null, null, $invoice_id)) { 
        //postEmulationWrite('warning_msg', _gettext("This invoice cannot be cancelled because the invoice has payments."));
        return false;        
    }

    // Has Refunds (Fallback - is currently not needed because of statuses, but it might be used for information reporting later)
    if(count_refunds(null, null, null, null, $invoice_id) > 0) {
        //postEmulationWrite('warning_msg', _gettext("The invoice cannot be cancelled because the invoice has been refunded."));
        return false;
    }
    
    // Does the invoice have any Vouchers preventing cancelling the invoice (i.e. any that have been used)
    if(!check_invoice_vouchers_allow_cancellation($invoice_id)) {
        //postEmulationWrite('warning_msg', _gettext("The invoice cannot be cancelled because of Vouchers on it prevent this."));
        return false;
    } 
    
    // All checks passed
    return true;
    
}

###############################################################
#   Check to see if the invoice can be deleted                #
###############################################################

function check_invoice_can_be_deleted($invoice_id) {
    
    // Get the invoice details
    $invoice_details = get_invoice_details($invoice_id);
    
    // Is closed
    if($invoice_details['is_closed'] == true) {
        //postEmulationWrite('warning_msg', _gettext("This invoice cannot be deleted because it is closed."));
        return false;        
    }
    
    // Is partially paid
    if($invoice_details['status'] == 'partially_paid') {
        //postEmulationWrite('warning_msg', _gettext("This invoice cannot be deleted because it has payments and is partially paid."));
        return false;        
    }
    
    // Is paid
    if($invoice_details['status'] == 'paid') {
        //postEmulationWrite('warning_msg', _gettext("This invoice cannot be deleted because it has payments and is paid."));
        return false;        
    }
    
    // Is partially refunded (not currently used)
    if($invoice_details['status'] == 'partially_refunded') {
        //postEmulationWrite('warning_msg', _gettext("The invoice status cannot be changed because the invoice has been partially refunded."));
        return false;        
    }     
    
    // Is refunded
    if($invoice_details['status'] == 'refunded') {
        //postEmulationWrite('warning_msg', _gettext("This invoice cannot be deleted because it has been refunded."));
        return false;        
    }
    
    // Is cancelled
    if($invoice_details['status'] == 'cancelled') {
        //postEmulationWrite('warning_msg', _gettext("This invoice cannot be deleted because it has been cancelled."));
        return false;        
    }
    
    // Is deleted
    if($invoice_details['status'] == 'deleted') {
        //postEmulationWrite('warning_msg', _gettext("This invoice cannot be deleted because it already been deleted."));
        return false;        
    }
    
    // Has payments (Fallback - is currently not needed because of statuses, but it might be used for information reporting later)
    if(count_payments(null, null, 'date', null, null, null, 'invoice', null, null, null, $invoice_id)) { 
        //postEmulationWrite('warning_msg', _gettext("This invoice cannot be deleted because it has payments."));
        return false;        
    }

    /*
    // Has Labour (these will get deleted anyway)
    if(!empty(get_invoice_labour_items($invoice_id))) {
        postEmulationWrite('warning_msg', _gettext("This invoice cannot be deleted because it has labour items."));
        return false;          
    }    
    
    // Has Parts (these will get deleted anyway)
    if(!empty(get_invoice_parts_items($invoice_id))) {
        postEmulationWrite('warning_msg', _gettext("This invoice cannot be deleted because it has parts."));
        return false;          
    }
    */
    
    // Has Refunds (should not be needed)
    if(count_refunds(null, null, null, null, $invoice_id) > 0) {
        //postEmulationWrite('warning_msg', _gettext("This invoice cannot be deleted because it has been refunded."));
        return false;
    }
    
    // Does the invoice have any Vouchers preventing refunding the invoice (i.e. any that have been used)
    if(!check_invoice_vouchers_allow_deletion($invoice_id)) {
        //postEmulationWrite('warning_msg', _gettext("The invoice cannot be deleted because of Vouchers on it prevent this."));
        return false;
    } 
     
    // All checks passed
    return true;
    
}
 
#############################################################
#   Check to see if the invoice can have its refund deleted # // this also checks the associated vouchers
#############################################################

function check_invoice_can_have_refund_deleted($invoice_id) {
    
    // Get the invoice details
    $invoice_details = get_invoice_details($invoice_id);
       
    /* Is partially paid
    if($invoice_details['status'] == 'partially_paid') {
        //postEmulationWrite('warning_msg', _gettext("This invoice cannot be refunded because the invoice is partially paid."));
        return false;
    }
    
    // Is cancelled
    if($invoice_details['status'] == 'cancelled') {
        //postEmulationWrite('warning_msg', _gettext("The invoice cannot be refunded because the invoice has been cancelled."));
        return false;        
    }
    
    // Is deleted
    if($invoice_details['status'] == 'deleted') {
        //postEmulationWrite('warning_msg', _gettext("The invoice cannot be refunded because the invoice has been deleted."));
        return false;        
    }*/
    
    // Is not refunded (fall back)
    if($invoice_details['status'] != 'refunded') {
        //postEmulationWrite('warning_msg', _gettext("The voucher status cannot have it' refund deleted because it is not refunded."));
        return false;        
    }

    // Has no payments (Fallback - is currently not needed because of statuses, but it might be used for information reporting later)
    if(!count_payments(null, null, 'date', null, null, null, 'invoice', null, null, null, $invoice_id)) { 
        //postEmulationWrite('warning_msg', _gettext("This invoice cannot be refunded because the invoice has no payments."));
        return false;        
    }
    
    // Does the invoice have any Vouchers preventing refunding the invoice (i.e. any that have been used)
    if(!check_invoice_vouchers_allow_refund_deletion($invoice_id)) {
        //postEmulationWrite('warning_msg', _gettext("The invoice cannot be refunded because of Vouchers on it prevent this."));
        return false;
    }    
    
    // All checks passed
    return true;
    
}

################################################################
#   Check to see if the invoice can have its refund cancelled  # // this also checks the associated vouchers
################################################################

function check_invoice_can_have_refund_cancelled($invoice_id) {
    
    // Get the invoice details
    $invoice_details = get_invoice_details($invoice_id);
       
    /* Is partially paid
    if($invoice_details['status'] == 'partially_paid') {
        //postEmulationWrite('warning_msg', _gettext("This invoice cannot have its refunded cancelled because the invoice is partially paid."));
        return false;
    }
    
    // Is cancelled
    if($invoice_details['status'] == 'cancelled') {
        //postEmulationWrite('warning_msg', _gettext("This invoice cannot have its refunded cancelled because the invoice has been cancelled."));
        return false;        
    }
    
    // Is deleted
    if($invoice_details['status'] == 'deleted') {
        //postEmulationWrite('warning_msg', _gettext("This invoice cannot have its refunded cancelled because the invoice has been deleted."));
        return false;        
    }*/
    
    // Is not refunded (fall back)
    if($invoice_details['status'] != 'refunded') {
        //postEmulationWrite('warning_msg', _gettext("This invoice cannot have its refunded cancelled because it is not refunded."));
        return false;        
    }

    // Has no payments (Fallback - is currently not needed because of statuses, but it might be used for information reporting later)
    if(!count_payments(null, null, 'date', null, null, null, 'invoice', null, null, null, $invoice_id)) { 
        //postEmulationWrite('warning_msg', _gettext("This invoice cannot have its refunded cancelled because the invoice has no payments."));
        return false;        
    }
    
    // Does the invoice have any Vouchers preventing refunding the invoice (i.e. any that have been used)
    if(!check_invoice_vouchers_allow_refund_cancellation($invoice_id)) {
        //postEmulationWrite('warning_msg', _gettext("This invoice cannot have its refunded cancelled because of Vouchers on it prevent this."));
        return false;
    }    
    
    // All checks passed
    return true;
    
}

##########################################################
#  Check if the invoice status is allowed to be changed  #
##########################################################

 function check_invoice_can_be_edited($invoice_id) {
     
    // Get the invoice details
    $invoice_details = get_invoice_details($invoice_id);
    
    // Is on a different tax system
    if($invoice_details['tax_system'] != QW_TAX_SYSTEM) {
        //postEmulationWrite('warning_msg', _gettext("The invoice cannot be edited because it is on a different Tax system."));
        return false;        
    }
    
    // Is partially paid
    if($invoice_details['status'] == 'partially_paid') {
        //postEmulationWrite('warning_msg', _gettext("The invoice cannot be edited because the invoice has payments and is partially paid."));
        return false;        
    }
    
    // Is paid
    if($invoice_details['status'] == 'paid') {
        //postEmulationWrite('warning_msg', _gettext("The invoice cannot be edited because the invoice has payments and is paid."));
        return false;        
    }
    
    // Is partially refunded (not currently used)
    if($invoice_details['status'] == 'partially_refunded') {
        //postEmulationWrite('warning_msg', _gettext("The invoice cannot be edited because the invoice has been partially refunded."));
        return false;        
    }
    
    // Is refunded
    if($invoice_details['status'] == 'refunded') {
        //postEmulationWrite('warning_msg', _gettext("The invoice cannot be edited because the invoice has been refunded."));
        return false;        
    }
    
    // Is cancelled
    if($invoice_details['status'] == 'cancelled') {
        //postEmulationWrite('warning_msg', _gettext("The invoice cannot be edited because the invoice has been cancelled."));
        return false;        
    }
    
    // Is deleted
    if($invoice_details['status'] == 'deleted') {
        //postEmulationWrite('warning_msg', _gettext("The invoice cannot be edited because the invoice has been deleted."));
        return false;        
    }
        
    // Has payments (Fallback - is currently not needed because of statuses, but it might be used for information reporting later)
    if(count_payments(null, null, 'date', null, null, null, 'invoice', null, null, null, $invoice_id)) {       
        //postEmulationWrite('warning_msg', _gettext("The invoice cannot be edited because the invoice has payments."));
        return false;        
    }

    // Does the invoice have any Vouchers preventing changing the invoice status
    if(!check_invoice_vouchers_allow_editing($invoice_id)) {
        //postEmulationWrite('warning_msg', _gettext("The invoice cannot be edited because of Vouchers on it prevent this."));
        return false;
    }
    
    // The current record VAT code is enabled
    if(!check_invoice_vat_tax_code_statuses($invoice_id)) {
        //postEmulationWrite('warning_msg', _gettext("This invoice cannot be edited because one or more of the parts or labour have a VAT Tax Code that is not enabled."));
        return false; 
    }
    
    // All checks passed
    return true;     
     
}

####################################################################
#   Check invoice Labour and parts VAT Tax Codes are all enabled   #
####################################################################

function check_invoice_vat_tax_code_statuses($invoice_id) {
    
    $vat_tax_codes_all_enabled = true;
    
    // Check all labour
    foreach (get_invoice_labour_items($invoice_id) as $key => $value) {        
        if(!get_vat_tax_code_status($value['vat_tax_code'])) { $vat_tax_codes_all_enabled = false;}        
    }
    
    // Check all parts
    foreach (get_invoice_parts_items($invoice_id) as $key => $value) {        
        if(!get_vat_tax_code_status($value['vat_tax_code'])) { $vat_tax_codes_all_enabled = false;}        
    }
    
    return $vat_tax_codes_all_enabled; 
    
}