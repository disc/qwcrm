<!-- choice.tpl -->
{*
 * @package   QWcrm
 * @author    Jon Brown https://quantumwarp.com/
 * @copyright Copyright (C) 2016 - 2017 Jon Brown, All rights reserved.
 * @license   GNU/GPLv3 or later; https://www.gnu.org/licenses/gpl.html
*}
<script>
    $(function() {

        // Unhide setup buttons - registered onclick event
        $("#accept_license_button").click(function(event) {
            
            // not sure what this does
            event.preventDefault();
            
            // if the checkbox is ticked
            if($("#accept_license_checkbox").is(':checked')) {
                $("#accept_license").hide();
                $("#license_accepted").show();
            }
        
        } );

    } );
</script>
 
<table width="100%" border="0" cellpadding="20" cellspacing="5">
    <tr>
        <td>            
            <table width="700" cellpadding="3" cellspacing="0" border="0">
                <tr>
                    <td class="menuhead2" width="80%">&nbsp;{t}QWcrm Installation / MyITCRM Migration / QWcrm Upgrade{/t}</td>
                    <td class="menuhead2" width="20%" align="right" valign="middle">
                        <a>
                            <img src="{$theme_images_dir}icons/16x16/help.gif" border="0" onMouseOver="ddrivetip('<div><strong>{t escape=tooltip}SETUP_CHOICE_HELP_TITLE{/t}</strong></div><hr><div>{t escape=tooltip}SETUP_CHOICE_HELP_CONTENT{/t}</div>');" onMouseOut="hideddrivetip();">
                        </a>
                    </td>
                </tr>                
                <tr>
                    <td class="menutd2" colspan="2">
                        <table class="olotable" width="100%" border="0" cellpadding="5" cellspacing="0">                            
                            
                            <!-- Accept License -->
                            
                            <tr id="accept_license">
                                <td>
                                    <table class="olotable" width="100%" border="0" cellpadding="5" cellspacing="0">
                                        <tr>
                                            <td>
                                                {t}QWcrm software is released under the GNU General Public License V3 (GPL-3.0){/t}<br>
                                                {t}The license can be found in the root directory of QWcrm.{/t} <a href="LICENSE.txt" target="_blank">({t}Click here to read{/t})</a><br>
                                                {t}You must confirm you understand and accept the license before continuing.{/t}                                                
                                            </td>                                            
                                        </tr>
                                        <tr>
                                            <td>
                                                <p><input type="checkbox" id="accept_license_checkbox">{t}I have read and understood the license.{/t}</p>
                                                <button href="javascript:void(0)" id="accept_license_button">Accept License</button>
                                            </td>
                                        </tr>
                                    </table>                                    
                                </td>                                
                            </tr>
                                        
                            <!-- License Accepted -->
                            
                            <tr id="license_accepted" style="display: none;">
                                <td class="menutd">
                                    <table width="100%" border="0" cellpadding="10" cellspacing="0">
                                        
                                        <!-- Installation Message-->   
                                        <tr>
                                            <td>                                                                                                  
                                                {t}Choose choose whether you want to install a fresh copy of QWcrm or migrate from MyITCRM{/t}
                                            </td>
                                        </tr> 
                                        
                                        <!-- Install QWcrm -->   
                                        <tr>
                                            <td>                                                                                                  
                                                <a href="index.php?page=setup:install"><button type="submit" name="submit" value="update">{t}Install QWcrm{/t}</button></a>
                                            </td>
                                        </tr>                                        
                                        
                                        <!-- Migrate from MyITCRM -->  
                                        <tr>
                                            <td>                                                                                                 
                                                <a href="index.php?page=setup:migrate"><button type="submit" name="submit" value="update">{t}Migrate from MyITCRM{/t}</button></a>
                                            </td>
                                        </tr>                                                                          
                                        
                                        <!-- Upgrade QWcrm -->  
                                        {*<tr>
                                            <td>                                                                                                 
                                                <a href="index.php?page=setup:upgrade"><button type="submit" name="submit" value="update">{t}Upgrade QWcrm{/t}</button></a>
                                            </td>
                                        </tr>*}
                                        
                                    </table>
                                </td>
                            </tr>
                            
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>