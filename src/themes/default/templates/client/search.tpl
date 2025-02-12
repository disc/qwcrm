<!-- search.tpl -->
{*
 * @package   QWcrm
 * @author    Jon Brown https://quantumwarp.com/
 * @copyright Copyright (C) 2016 - 2017 Jon Brown, All rights reserved.
 * @license   GNU/GPLv3 or later; https://www.gnu.org/licenses/gpl.html
*}
<table width="100%" border="0" cellpadding="20" cellspacing="5">
    <tr>
        <td>
            <table width="700" cellpadding="4" cellspacing="0" border="0" >
                <tr>
                    <td class="menuhead2" width="80%">&nbsp;&nbsp;{t}Client Search{/t}</td>
                    <td class="menuhead2" width="20%" align="right" valign="middle">
                        <a>                            
                            <img src="{$theme_images_dir}icons/16x16/help.gif" border="0" onMouseOver="ddrivetip('<div><strong>{t escape=tooltip}CLIENT_SEARCH_HELP_TITLE{/t}</strong></div><hr><div>{t escape=tooltip}CLIENT_SEARCH_HELP_CONTENT{/t}</div>');" onMouseOut="hideddrivetip();">
                        </a>
                    </td>
                </tr>
                <tr>
                    <td class="menutd2" colspan="2">
                        <table class="olotable" width="100%" border="0" cellpadding="5" cellspacing="0">
                            <tr>
                                <td class="menutd">
                                    <table class="menutable" width="100%" border="0" cellpadding="5" cellspacing="0">
                                        <tr>
                                            
                                            <!-- Category Search -->
                                            <td valign="top">                                                
                                                <form action="index.php?component=client&page_tpl=search" method="post" name="client_search" id="client_search">                                                
                                                    <div>                                                        
                                                        <table border="0">
                                                            <tr>
                                                                <td align="left" valign="top"><b>{t}Client Search{/t}</b>
                                                                    <br />
                                                                    <select class="olotd5" id="search_category" name="search_category">
                                                                        <option value="display_name"{if $search_category == 'display_name'} selected{/if}>{t}Name{/t}</option>
                                                                        <option value="client_id"{if $search_category == 'client_id'} selected{/if}>{t}Client ID{/t}</option>                                                                        
                                                                        <option value="company_name"{if $search_category == 'company_name'} selected{/if}>{t}Company{/t}</option>
                                                                        <option value="full_name"{if $search_category == 'full_name'} selected{/if}>{t}Contact{/t}</option>
                                                                        <option value="email"{if $search_category == 'email'} selected{/if}>{t}Email{/t}</option>
                                                                        <option value="note"{if $search_category == 'note'} selected{/if}>{t}Note{/t}</option>                                                                        
                                                                    </select>
                                                                   <br />
                                                                   <b>{t}for{/t}</b>
                                                                   <br />
                                                                   <input name="search_term" class="olotd4" value="{$search_term}" type="text" maxlength="50" onkeydown="return onlySearch(event);">                                                                   
                                                                   <button type="submit" name="submit" value="submit">{t}Search{/t}</button>
                                                                   <button type="button" class="olotd4" onclick="window.location.href='index.php?component=client&page_tpl=search';">{t}Reset{/t}</button>
                                                                </td>
                                                            </tr>                                                            
                                                            <tr>
                                                                <td><font color="red">{t}NO special characters like !@#$%^*(){/t}</font></td>
                                                            </tr>
                                                            <tr>
                                                                <td>
                                                                    <b>{t}Filter By Status{/t}</b><br>
                                                                    <select class="olotd5" id="filter_status" name="filter_status">
                                                                        <option value=""{if $filter_status == ''} selected{/if}>{t}None{/t}</option>
                                                                        <option disabled>----------</option>
                                                                        <option value="1"{if $filter_status == '1'} selected{/if}>{t}Active{/t}</option>
                                                                        <option value="0"{if $filter_status == '0'} selected{/if}>{t}Blocked{/t}</option>
                                                                    </select>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>
                                                                    <b>{t}Filter By Type{/t}</b><br>
                                                                    <select class="olotd5" id="filter_type" name="filter_type">
                                                                        <option value=""{if !$filter_type} selected{/if}>{t}None{/t}</option>
                                                                        <option disabled>----------</option>                                                                        
                                                                        {section name=t loop=$client_types}    
                                                                            <option value="{$client_types[t].type_key}"{if $filter_type == $client_types[t].type_key} selected{/if}>{t}{$client_types[t].display_name}{/t}</option>        
                                                                        {/section}
                                                                    </select>
                                                                </td>
                                                            </tr> 
                                                        </table>
                                                    </div>
                                                </form>
                                            </td>
                                            
                                            <!-- Navigation -->
                                            <td valign="top" nowrap>
                                                <form id="navigation">                                                    
                                                    <table>
                                                        <tr>
                                                            
                                                            <!-- Left Side Buttons -->
                                                            <td>
                                                                {if $previous_page_no && $display_clients}
                                                                    <a href="index.php?component=client&page_tpl=search&search_category={$search_category}&search_term={$search_term}&page_no=1{if $filter_status}&filter_status={$filter_status}{/if}{if $filter_type}&filter_type={$filter_type}{/if}"><img src="{$theme_images_dir}rewnd_24.gif" border="0" alt=""></a>&nbsp;                                                    
                                                                    <a href="index.php?component=client&page_tpl=search&search_category={$search_category}&search_term={$search_term}&page_no={$previous_page_no}{if $filter_status}&filter_status={$filter_status}{/if}{if $filter_type}&filter_type={$filter_type}{/if}"><img src="{$theme_images_dir}back_24.gif" border="0" alt=""></a>&nbsp;
                                                                {/if}
                                                            </td>                                                   
                                                    
                                                            <!-- Dropdown Menu -->
                                                            <td>                                                                    
                                                                <select id="changeThisPage" onChange="changePage();">
                                                                    {section name=page loop=$total_pages start=1}
                                                                        <option value="index.php?component=client&page_tpl=search&search_category={$search_category}&search_term={$search_term}&page_no={$smarty.section.page.index}" {if $page_no == $smarty.section.page.index } Selected {/if}>
                                                                            {t}Page{/t} {$smarty.section.page.index} {t}of{/t} {$total_pages} 
                                                                        </option>
                                                                    {/section}
                                                                    <option value="index.php?component=client&page_tpl=search&search_category={$search_category}&search_term={$search_term}&page_no={$total_pages}" {if $page_no == $total_pages} selected {/if}>
                                                                        {t}Page{/t} {$total_pages} {t}of{/t} {$total_pages}
                                                                    </option>
                                                                </select>
                                                            </td>
                                                            
                                                            <!-- Right Side Buttons --> 
                                                            <td>
                                                                {if $next_page_no && $display_clients}
                                                                    <a href="index.php?component=client&page_tpl=search&search_category={$search_category}&search_term={$search_term}&page_no={$next_page_no}{if $filter_status}&filter_status={$filter_status}{/if}{if $filter_type}&filter_type={$filter_type}{/if}"><img src="{$theme_images_dir}forwd_24.gif" border="0" alt=""></a>                                                   
                                                                    <a href="index.php?component=client&page_tpl=search&search_category={$search_category}&search_term={$search_term}&page_no={$total_pages}{if $filter_status}&filter_status={$filter_status}{/if}{if $filter_type}&filter_type={$filter_type}{/if}"><img src="{$theme_images_dir}fastf_24.gif" border="0" alt=""></a>
                                                                {/if}
                                                            </td>                                                                                             
                                                    
                                                        </tr>
                                                        <tr>

                                                            <!-- Page Number Display -->
                                                            <td></td>
                                                            <td>
                                                                <p style="text-align: center;">{$total_results} {t}records found.{/t}</p>
                                                            </td>
                                                            
                                                        </tr>                                                    
                                                    </table>                                                    
                                                </form>
                                            </td>
                                            
                                        </tr>
                                        
                                        <!-- Search Results Table -->
                                        <tr>
                                            <td valign="top" colspan="2">
                                                {include file='client/blocks/display_clients_block.tpl' display_clients=$display_clients block_title=''}
                                            </td>
                                        </tr>
                                        
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