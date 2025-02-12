<!-- install_administrator_account_block.tpl -->
{*
 * @package   QWcrm
 * @author    Jon Brown https://quantumwarp.com/
 * @copyright Copyright (C) 2016 - 2017 Jon Brown, All rights reserved.
 * @license   GNU/GPLv3 or later; https://www.gnu.org/licenses/gpl.html
*}
<script src="{$theme_js_dir}tinymce/tinymce.min.js"></script>
<script>{include file="../`$theme_js_dir_finc`editor-config.js"}</script>

<table width="100%" border="0" cellpadding="20" cellspacing="0">
    <tr>
        <td>
            <table width="900" cellpadding="5" cellspacing="0" border="0">
                <tr>
                    <td class="menuhead2" width="80%">&nbsp;{t}Create an Administrator{/t}</td>
                    <td class="menuhead2" width="20%" align="right" valign="middle">                        
                        <img src="{$theme_images_dir}icons/16x16/help.gif" border="0" onMouseOver="ddrivetip('<div><strong>{t escape=tooltip}USER_NEW_HELP_TITLE{/t}</strong></div><hr><div>{t escape=tooltip}USER_NEW_HELP_CONTENT{/t}</div>');" onMouseOut="hideddrivetip();">
                    </td>
                </tr>
                <tr>
                    <td class="menutd2" colspan="2">
                        <table width="100%" class="olotable" cellpadding="5" cellspacing="0" border="0">
                            <tr>
                                <td width="100%" valign="top">                                    
                                    <form action="index.php?component=setup&page_tpl=install" method="post" name="new_user" id="new_user" onsubmit="return confirmPasswordsMatch();"> 
                                        <table class="menutable" width="100%" border="0" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td class="menutd">
                                                    <table width="100%" cellpadding="2" cellspacing="2" border="0" class="menutd2">
                                                        <tr>
                                                            <td>                                                                
                                                                <table class="olotable" width="100%" cellpadding="5" cellspacing="0" border="0">
                                                                    
                                                                    <!-- Common -->
                                                                    
                                                                    <tr class="row2">
                                                                        <td class="menuhead" colspan="3" width="100%">&nbsp;{t}Common{/t}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td align="left">
                                                                            <table>
                                                                                <tbody align="left">
                                                                                    <tr>
                                                                                        <td align="right"><strong>{t}First Name{/t}</strong><span style="color: #ff0000">*</span></td>
                                                                                        <td><input name="first_name" class="olotd5" value="{$user_details.first_name}" type="text" maxlength="20" required onkeydown="return onlyName(event);"></td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td align="right"><strong>{t}Last Name{/t}</strong><span style="color: #ff0000">*</span></td>
                                                                                        <td><input name="last_name" class="olotd5" value="{$user_details.last_name}" type="text" maxlength="20" required onkeydown="return onlyName(event);"></td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td align="right"><strong>{t}User Type{/t}</strong><span style="color: #ff0000">*</span></td>                                                                                        
                                                                                        <td>                                                                                            
                                                                                            <span style="color: red; font-weight: 900;">{t}Employee{/t}</span>
                                                                                            <input type="hidden" name="is_employee" value="1">                                                                                            
                                                                                            &nbsp;-&nbsp;{t}The user type cannot be changed.{/t}
                                                                                        </td>                                                                                        
                                                                                    </tr>                                                                                    
                                                                                    <tr>
                                                                                        <td align="right"><strong>{t}Based{/t}</strong><span style="color: #ff0000">*</span></td>
                                                                                        <td>
                                                                                            <select name="based" class="olotd5" required>
                                                                                                <option selected hidden disabled></option>
                                                                                                {section name=l loop=$user_locations}    
                                                                                                    <option value="{$user_locations[l].location_key}"{if $user_details.based == $user_locations[l].location_key} selected{/if}>{t}{$user_locations[l].display_name}{/t}</option>
                                                                                                {/section} 
                                                                                            </select>
                                                                                            <input type="hidden" name="client_id" value="">
                                                                                        </td>
                                                                                    </tr>                                                                                                                                                                       
                                                                                </tbody>
                                                                            </table>
                                                                        </td>
                                                                    </tr>
                                                                    
                                                                    <!-- Account -->
                                                                    
                                                                    <tr class="row2">
                                                                        <td class="menuhead" colspan="3" width="100%">&nbsp;{t}Account{/t}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td align="left">
                                                                            <table>
                                                                                <tbody align="left">
                                                                                    <tr>
                                                                                        <td align="right"><strong>{t}Email{/t}</strong><span style="color: #ff0000">*</span></td>
                                                                                        <td><input name="email" class="olotd5" size="50" value="{$user_details.email}" type="email" maxlength="50" required onkeydown="return onlyEmail(event);"></td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td align="right"><strong>{t}Username{/t}</strong><span style="color: #ff0000">*</span></td>
                                                                                        <td><input name="username" class="olotd5" value="{$user_details.username}" type="text" maxlength="20" required onkeydown="return onlyUsername(event);"></td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td align="right"><strong>{t}Password{/t}</strong></td>
                                                                                        <td><input id="password" name="password" class="olotd5" type="password" minlength="8" maxlength="20" required oninput="checkPasswordsMatch('{t}Passwords Match!{/t}', '{t}Passwords Do Not Match!{/t}', true);" onkeydown="return onlyPassword(event);"></td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td align="right"><strong>{t}Confirm Password{/t}</strong></td>
                                                                                        <td>
                                                                                            <input id="confirmPassword" name="confirmPassword" class="olotd5" type="password" minlength="8" maxlength="20" required oninput="checkPasswordsMatch('{t}Passwords Match!{/t}', '{t}Passwords Do Not Match!{/t}', true);" onkeydown="onlyPassword(event);">
                                                                                            <div id="passwordMessage" style="min-height: 5px;"></div>
                                                                                        </td>
                                                                                    </tr>                                                                                    
                                                                                    <tr>
                                                                                        <td align="right"><strong>{t}Usergroup{/t}</strong></td>
                                                                                        <td>                                                                                                
                                                                                            {*<select name="usergroup" class="olotd5">
                                                                                                {section name=b loop=$usergroups}
                                                                                                    <option value="{$usergroups[b].usergroup_id}" {if $user_details.usergroup == $usergroups[b].usergroup_id} selected{/if}>{$usergroups[b].display_name}</option>
                                                                                                {/section}
                                                                                            </select>*}
                                                                                            <input type="hidden" name="usergroup" value="1">
                                                                                            {t}Administrator{/t}
                                                                                        </td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td><input type="hidden" name="active" value="1"></td>
                                                                                        {*<td colspan="1" align="right"><b>{t}Status{/t}</b></td>
                                                                                        <td>
                                                                                            <select name="active" class="olotd5">                                                                                                
                                                                                                <option value="1" {if $user_details.active == '1'} selected {/if}>{t}Active{/t}</option>
                                                                                                <option value="0" {if $user_details.active == '0'} selected {/if}>{t}Blocked{/t}</option>
                                                                                            </select>
                                                                                            
                                                                                            {t}Active{/t}
                                                                                        </td>*}
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td><input type="hidden" name="require_reset" value="0"></td>
                                                                                        {*<td colspan="1" align="right"><b>{t}Require Reset{/t}</b></td>
                                                                                        <td>
                                                                                            <select name="require_reset" class="olotd5">
                                                                                                <option value="0" {if $user_details.require_reset == '0'} selected {/if}>{t}No{/t}</option>
                                                                                                <option value="1" {if $user_details.require_reset == '1'} selected {/if}>{t}Yes{/t}</option>
                                                                                            </select>                                                                                            
                                                                                            {t}No{/t}
                                                                                        </td>*}
                                                                                    </tr>                                                                                    
                                                                                    
                                                                                </tbody>
                                                                            </table>
                                                                        </td>
                                                                    </tr>
                                                                    
                                                                    <!-- Work -->
                                                                    
                                                                    <tr class="row2">
                                                                        <td class="menuhead" colspan="2">&nbsp;{t}Work{/t}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2" align="left">
                                                                            <table>
                                                                                <tr>
                                                                                    <td align="right"><strong>{t}Work Phone{/t}</strong></td>
                                                                                    <td><input name="work_primary_phone" class="olotd5" value="{$user_details.work_primary_phone}" type="tel" maxlength="20" onkeydown="return onlyPhoneNumber(event);"></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td align="right"><strong>{t}Work Mobile Phone{/t}</strong></td>
                                                                                    <td><input name="work_mobile_phone" class="olotd5" value="{$user_details.work_mobile_phone}" type="tel" maxlength="20" onkeydown="return onlyPhoneNumber(event);"></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td align="right"><strong>{t}Work Fax{/t}</strong></td>
                                                                                    <td><input name="work_fax" class="olotd5" value="{$user_details.work_fax}" type="tel" maxlength="20" onkeydown="return onlyPhoneNumber(event);"></td>
                                                                                </tr>                                                                                
                                                                            </table>
                                                                        </td>
                                                                    </tr>
                                                                    
                                                                    <!-- Home -->
                                                                    
                                                                    <tr class="row2">
                                                                        <td class="menuhead" colspan="2">&nbsp;{t}Home{/t}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2" align="left">
                                                                            <table>
                                                                                <tr>
                                                                                    <td align="right"><strong>{t}Home Phone{/t}</strong></td>
                                                                                    <td><input name="home_primary_phone" class="olotd5" value="{$user_details.home_primary_phone}" type="tel" maxlength="20" onkeydown="return onlyPhoneNumber(event);"></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td align="right"><strong>{t}Home Mobile Phone{/t}</strong></td>
                                                                                    <td><input name="home_mobile_phone" class="olotd5" value="{$user_details.home_mobile_phone}" type="tel" maxlength="20" onkeydown="return onlyPhoneNumber(event);"></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td align="right"><strong>{t}Home Email{/t}</strong></td>
                                                                                    <td><input name="home_email" class="olotd5" size="50" value="{$user_details.home_email}" type="email" maxlength="50" onkeydown="return onlyEmail(event);"></td>
                                                                                </tr>
                                                                                                                                                           
                                                                                <tr>
                                                                                    <td align="right"><strong>{t}Address{/t}</strong></td>
                                                                                    <td><textarea name="home_address" class="olotd5 mceNoEditor" cols="30" rows="3" maxlength="100" onkeydown="return onlyAddress(event);">{$user_details.home_address}</textarea></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td align="right"><strong>{t}City{/t}</strong></td>
                                                                                    <td><input name="home_city" class="olotd5" value="{$user_details.home_city}" type="text" maxlength="20" onkeydown="return onlyAlpha(event);"></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td align="right"><strong>{t}State{/t}</strong></td>
                                                                                    <td><input name="home_state" class="olotd5" value="{$user_details.home_state}" type="text" maxlength="20" onkeydown="return onlyAlpha(event);"></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td align="right"><strong>{t}Zip{/t}</strong></td>
                                                                                    <td ><input name="home_zip" class="olotd5" value="{$user_details.home_zip}" type="text" maxlength="20" onkeydown="return onlyAlphaNumeric(event);"></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td align="right"><strong>{t}Country{/t}</strong></td>
                                                                                    <td><input name="home_country" class="olotd5" value="{$user_details.home_country}" type="text" maxlength="50" onkeydown="return onlyAlpha(event);"></td>
                                                                                </tr>
                                                                            </table>
                                                                        </td>
                                                                    </tr>
                                                                    
                                                                    <!-- note -->
                                                                    
                                                                    <tr class="row2">
                                                                        <td class="menuhead" colspan="2">{t}Note{/t}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2">
                                                                            <table>
                                                                                <tr>
                                                                                    <td align="left"><strong>{t}Note{/t}</strong></td>
                                                                                    <td><textarea name="note" class="olotd5" cols="50" rows="2">{$user_details.note}</textarea></td> 
                                                                                </tr>                                                                                
                                                                            </table>
                                                                        </td>
                                                                    </tr>                                                                     
                                                                    
                                                                    <!-- Submit Button -->
                                                                    
                                                                    <tr>                                                                        
                                                                        <td colspan="2">
                                                                            <button id="submit_button" class="olotd5" type="submit" name="submit" value="administrator_account" disabled>{t}Next{/t}</button>
                                                                        </td>
                                                                    </tr>
                                                                                                                                        
                                                                </table>                                                                
                                                            </td>
                                                    </table>
                                                </td>
                                        </table>
                                    </form>                                                                        
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>