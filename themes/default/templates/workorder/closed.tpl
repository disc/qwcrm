<!-- closed.tpl -->
<table width="100%" border="0" cellpadding="20" cellspacing="5">
    <tr>
        <td>
            <table width="700" cellpadding="4" cellspacing="0" border="0" >
                <tr>
                    <td class="menuhead2" width="80%">&nbsp;{$translate_workorder_closed_title} - {$total_results} {$translate_workorder_records_found}</td>
                    <td class="menuhead2" width="20%" align="right" valign="middle">
                        <a>
                            <img src="{$theme_images_dir}icons/16x16/help.gif" border="0" alt=""  onMouseOver="ddrivetip('<b>{$translate_workorder_closed_help_title|nl2br|regex_replace:"/[\r\t\n]/":" "}</b><hr><p>{$translate_workorder_closed_help_content|nl2br|regex_replace:"/[\r\t\n]/":" "}</p>');" onMouseOut="hideddrivetip();">
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
                                            <td valign="top"></td>
                                            <td valign="top" nowrap align="right">
                                                <form id="1" action="">
                                                    <a href="?page=workorder:closed&submit=submit&page_no=1"><img src="{$theme_images_dir}rewnd_24.gif" alt="" border="0"></a>&nbsp;
                                                    {if $previous != ''}
                                                        <a href="?page=workorder:closed&submit=submit&page_no={$previous}"><img src="{$theme_images_dir}back_24.gif" alt="" border="0"></a>&nbsp;
                                                    {/if}
                                                    <select name="page_no" onChange="changePage();">
                                                    {section name=page loop=$total_pages start=1}
                                                        <option value="?page=workorder:closed&submit=submit&page_no={$smarty.section.page.index}" {if $page_no == $smarty.section.page.index } Selected {/if}>
                                                            {$translate_workorder_page} {$smarty.section.page.index} {$translate_workorder_of} {$total_pages} 
                                                        </option>
                                                    {/section}
                                                        <option value="?page=workorder:closed&submit=submit&page_no={$total_pages}" {if $page_no == $total_pages} selected {/if}>
                                                            {$translate_workorder_page} {$total_pages} {$translate_workorder_of} {$total_pages}
                                                        </option>
                                                    </select>
                                                    {if $next != ''}
                                                        <a href="?page=workorder:closed&submit=submit&page_no={$next}"><img src="{$theme_images_dir}forwd_24.gif" alt="" border="0"></a>
                                                    {/if}
                                                    <a href="?page=workorder:closed&submit=submit&page_no={$total_pages}"><img src="{$theme_images_dir}fastf_24.gif" alt="" border="0"></a>
                                                </form>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td valign="top" colspan="2">
                                                <table class="olotable" width="100%" border="0" cellpadding="4" cellspacing="0">
                                                    <tr>
                                                        <td class="olohead"><b>{$translate_workorder_id}</b></td>
                                                        <td class="olohead"><b>{$translate_workorder_opened}</b></td>
                                                        <td class="olohead"><b>{$translate_workorder_closed}</b></td>
                                                        <td class="olohead"><b>{$translate_workorder_customer}</b></td>
                                                        <td class="olohead"><b>{$translate_workorder_scope}</b></td>
                                                        <td class="olohead"><b>{$translate_workorder_status}</b></td>
                                                        <td class="olohead"><b>{$translate_workorder_tech}</b></td>
                                                    </tr>
                                                    {foreach from=$work_order item=work_order}
                                                        {if $work_order.WORK_ORDER_ID  != ""}
                                                            <tr onmouseover="this.className='row2';" onmouseout="this.className='row1';" onDblClick="window.location='?page=workorder:details&wo_id={$work_order.WORK_ORDER_ID}&customer_id={$work_order.CUSTOMER_ID}&page_title={$translate_workorder_work_order_id} {$work_order.WORK_ORDER_ID}';" class="row1">
                                                                <td class="olotd4"><a href="?page=workorder:details&wo_id={$work_order.WORK_ORDER_ID}&customer_id={$work_order.CUSTOMER_ID}&page_title={$translate_workorder_work_order_id} {$work_order.WORK_ORDER_ID}">{$work_order.WORK_ORDER_ID}</a></td>
                                                                <td class="olotd4"> {$work_order.WORK_ORDER_OPEN_DATE|date_format:"$date_format"}</td>
                                                                <td class="olotd4">{$work_order.WORK_ORDER_CLOSE_DATE|date_format:"$date_format"}</td>
                                                                <td class="olotd4" nowrap>
                                                                    <img src="{$theme_images_dir}icons/16x16/view.gif" alt="" border="0"
                                                                         onMouseOver="ddrivetip('<b><center>{$translate_workorder_contact_info_tooltip_title}</b></center><hr><b>{$translate_workorder_phone}: </b>{$work_order.CUSTOMER_PHONE}<br> <b>{$translate_workorder_fax}: </b>{$work_order.CUSTOMER_WORK_PHONE}<br><b>{$translate_workorder_mobile}: </b>{$work_order.CUSTOMER_MOBILE_PHONE}<br><b>{$translate_workorder_address}:</b><br>{$work_order.CUSTOMER_ADDRESS}<br>{$work_order.CUSTOMER_CITY}, {$work_order.CUSTOMER_STATE}<br>{$work_order.CUSTOMER_ZIP}');"
                                                                         onMouseOut="hideddrivetip();">
                                                                    <a class="link1" href="?page=customer:customer_details&customer_id={$work_order.CUSTOMER_ID}&page_title={$work_order.CUSTOMER_DISPLAY_NAME}">{$work_order.CUSTOMER_DISPLAY_NAME}</a>
                                                                </td>
                                                                <td class="olotd4" nowrap>{$work_order.WORK_ORDER_SCOPE}</td>
                                                                <td class="olotd4">{$work_order.CONFIG_WORK_ORDER_STATUS}</td>
                                                                <td class="olotd4" nowrap>
                                                                    <img src="{$theme_images_dir}icons/16x16/view.gif" alt="" border="0" onMouseOver="ddrivetip('<center><b>{$translate_workorder_contact_info_tooltip_title}</b></center><hr><b>{$translate_workorder_fax}: </b>{$work_order.EMPLOYEE_WORK_PHONE}<br><b>{$translate_workorder_mobile}: </b>{$work_order.EMPLOYEE_MOBILE_PHONE}<br><b>{$translate_workorder_home}: </b>{$work_order.EMPLOYEE_HOME_PHONE}');" onMouseOut="hideddrivetip();">                                                                         
                                                                    <a class="link1" href="?page=employee:employee_details&employee_id={$work_order.EMPLOYEE_ID}&page_title={$work_order.EMPLOYEE_DISPLAY_NAME}">{$work_order.EMPLOYEE_DISPLAY_NAME}</a>
                                                                </td>
                                                            </tr>
                                                        {else}
                                                            <tr>
                                                                <td colspan="6" class="error">{$translate_workorder_msg_there_are_no_closed_work_orders}</td>
                                                            </tr>
                                                        {/if}
                                                    {/foreach}
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
        </td>
    </tr>
</table>