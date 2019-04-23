<!-- new.tpl -->
{*
 * @package   QWcrm
 * @author    Jon Brown https://quantumwarp.com/
 * @copyright Copyright (C) 2016 - 2017 Jon Brown, All rights reserved.
 * @license   GNU/GPLv3 or later; https://www.gnu.org/licenses/gpl.html
*}
<script src="{$theme_js_dir}tinymce/tinymce.min.js"></script>
<script>{include file="`$theme_js_dir_finc`editor-config.js"}</script>
<link rel="stylesheet" href="{$theme_js_dir}jscal2/css/jscal2.css" />
<link rel="stylesheet" href="{$theme_js_dir}jscal2/css/steel/steel.css" />
<script src="{$theme_js_dir}jscal2/jscal2.js"></script>
<script src="{$theme_js_dir}jscal2/unicode-letter.js"></script>
<script>{include file="`$theme_js_dir_finc`jscal2/language.js"}</script>
<script>
    
    $( document ).ready(function() {
        
        // Set the intial VAT rate from the selected VAT Tax Code
        var selected_vat_tax_code = $('#vat_tax_code').find('option:selected');
        var tcVatRate = selected_vat_tax_code.data('rate');            
        $('#vat_rate').val(tcVatRate);
        calculateTotals('vat_tax_code');        
        
        // Bind an action to the VAT Tax Code dropdown to update the totals on change
        $('#vat_tax_code').change(function() {            
            var selected = $(this).find('option:selected');
            var tcVatRate = selected.data('rate');            
            $('#vat_rate').val(tcVatRate);
            calculateTotals('vat_tax_code');
        } );
            
    } );

    // automatically calculate totals
    function calculateTotals(fieldName) {
        
        // Get input field values
        var net_amount  = Number(document.getElementById('net_amount').value);
        var vat_rate    = Number(document.getElementById('vat_rate').value);
        var vat_amount  = Number(document.getElementById('vat_amount').value);
        
        // Calculations        
        var auto_vat_amount = (net_amount * (vat_rate/100));        
        if(fieldName !== 'vat_amount') {
            var auto_gross_amount = (net_amount + auto_vat_amount);
        } else {            
            var auto_gross_amount = (net_amount + vat_amount);
        }
        
        // Set the new vat_amount input value if not editing the vat_amount input field
        if(fieldName !== 'vat_amount') {
            document.getElementById('vat_amount').value = auto_vat_amount.toFixed(2);
        }
        
        // Set the new gross_amount input value
        document.getElementById('gross_amount').value = auto_gross_amount.toFixed(2);        
    
    }

</script>

<table width="100%"   border="0" cellpadding="20" cellspacing="5">
    <tr>
        <td>            
            <table width="700" cellpadding="5" cellspacing="0" border="0" >
                <tr>
                    <td class="menuhead2" width="80%">{t}New Other Income{/t}</td>
                    <td class="menuhead2" width="20%" align="right" valign="middle">
                        <a>
                            <img src="{$theme_images_dir}icons/16x16/help.gif" border="0" onMouseOver="ddrivetip('<div><strong>{t escape=tooltip}REFUND_NEW_HELP_TITLE{/t}</strong></div><hr><div>{t escape=tooltip}REFUND_NEW_HELP_CONTENT{/t}</div>');" onMouseOut="hideddrivetip();">
                        </a>
                    </td>
                </tr>
                <tr>
                    <td class="menutd2" colspan="2">                        
                        <table class="olotable" width="100%" border="0" cellpadding="5" cellspacing="0">
                            <tr>
                                <td class="menutd">
                                    <table class="menutable" width="100%" border="0" cellpadding="2" cellspacing="2" >
                                        <tr>
                                            <td>                                               
                                                <form action="index.php?component=otherincome&page_tpl=new" method="post" name="new_otherincome" id="new_otherincome" autocomplete="off">                                                
                                                    <table width="100%" cellpadding="3" cellspacing="0" border="0">
                                                        <tr>
                                                            <td colspan="2" align="left">
                                                                <table>
                                                                    <tbody align="left">
                                                                        <tr>
                                                                            <td class="menuhead" colspan="3">{t}First Group{/t}</td>
                                                                        </tr>
                                                                       <tr>
                                                                            <td align="right"><b>{t}Payee{/t}</b><span style="color: #ff0000"> *</span></td>
                                                                            <td colspan="3"><input id="payee" name="payee" class="olotd5" size="50" type="text" maxlength="50" required onkeydown="return onlyName(event);"></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td align="right"><b>{t}Date{/t}</b><span style="color: #ff0000"> *</span></td>
                                                                            <td>
                                                                                <input id="date" name="date" class="olotd5" size="10" type="text" maxlength="10" pattern="{literal}^[0-9]{2,4}(?:\/|-)[0-9]{2}(?:\/|-)[0-9]{2,4}${/literal}" required onkeydown="return onlyDate(event);">
                                                                                <button type="button" id="date_button">+</button>
                                                                                <script>                                                                                
                                                                                    Calendar.setup( {
                                                                                        trigger     : "date_button",
                                                                                        inputField  : "date",
                                                                                        dateFormat  : "{$date_format}"
                                                                                    } );                                                                                  
                                                                                </script>                                                                                
                                                                            </td>
                                                                        </tr>                                                                        
                                                                        <tr>
                                                                            <td align="right"><b>{t}Item Type{/t}</b><span style="color: #ff0000"> *</span></td>
                                                                            <td>
                                                                                <select id="item_type" name="item_type" class="olotd5"> 
                                                                                    {section name=s loop=$otherincome_types}    
                                                                                        <option value="{$otherincome_types[s].type_key}">{t}{$otherincome_types[s].display_name}{/t}</option>
                                                                                    {/section}    
                                                                                </select>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td align="right"><b>{t}Net Amount{/t}</b><span style="color: #ff0000"> *</span></td>
                                                                            <td><input id="net_amount" name="net_amount" class="olotd5" style="border-width: medium;" size="10" type="text" maxlength="10" pattern="{literal}^[0-9]{1,7}(.[0-9]{0,2})?${/literal}" required onkeydown="return onlyNumberPeriod(event);" onkeyup="calculateTotals('net_amount');"></b></a></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td align="right"><b>{t}VAT Tax Code{/t}</b></td>
                                                                            <td>
                                                                                <select id="vat_tax_code" name="vat_tax_code" class="olotd5"> 
                                                                                    {section name=s loop=$vat_tax_codes}    
                                                                                        <option value="{$vat_tax_codes[s].tax_key}" data-rate="{$vat_tax_codes[s].rate}"{if $default_vat_tax_code == $vat_tax_codes[s].tax_key} selected{/if}>{$vat_tax_codes[s].tax_key} - {t}{$vat_tax_codes[s].display_name}{/t} @ {$vat_tax_codes[s].rate|string_format:"%.2f"}%</option>
                                                                                    {/section}    
                                                                                </select>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td align="right"><b>{t}VAT{/t} {t}Rate{/t}</td>
                                                                            <td><input id="vat_rate" name="vat_rate" class="olotd5" size="5" value="0.00" type="text" maxlength="5" pattern="{literal}^[0-9]{0,2}(\.[0-9]{0,2})?${/literal}" required onkeydown="return onlyNumberPeriod(event);" onkeyup="calculateTotals('vat_rate');" readonly/><b>%</b></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td align="right"><b>{t}VAT{/t} {t}Amount{/t}</b></td>
                                                                            <td><input id="vat_amount" name="vat_amount" class="olotd5" size="10" value="0.00" type="text" maxlength="10" pattern="{literal}^[0-9]{1,7}(.[0-9]{0,2})?${/literal}" onkeydown="return onlyNumberPeriod(event);" onkeyup="calculateTotals('vat_amount');"/></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td align="right"><b>{t}Gross Amount{/t}</b><span style="color: #ff0000"> *</span></td>
                                                                            <td><input id="gross_amount" name="gross_amount" class="olotd5" size="10" type="text" maxlength="10" pattern="{literal}^[0-9]{1,7}(.[0-9]{0,2})?${/literal}" required onkeydown="return onlyNumberPeriod(event);"/></td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="menuhead" colspan="2">{t}Additional Group{/t}</td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="2">
                                                                <table>
                                                                    <tbody align="left">
                                                                        <tr>
                                                                            <td align="right"><b>{t}Items{/t}</b><span style="color: #ff0000"> *</span></td>
                                                                            <td><textarea id="items" name="items" class="olotd5 mceCheckForContent" cols="50" rows="15"></textarea></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td align="right"><b>{t}Note{/t}</b></td>
                                                                            <td><textarea id="note" name="note" class="olotd5" cols="50" rows="15"></textarea></td>
                                                                        </tr>                                                                        
                                                                    </tbody>
                                                                        <tr>
                                                                            <td colspan="2">
                                                                                <button type="submit" name="submit" value="submit">{t}Submit{/t}</button>
                                                                                <button type="submit" name="submitandnew" value="submitandnew">{t}Submit and New{/t}</button>
                                                                                <button type="submit" name="submit" value="submitandpayment">{t}Submit and Payment{/t}</button>
                                                                                <button type="button" class="olotd4" onclick="window.location.href='index.php?component=otherincome&page_tpl=search';">{t}Cancel{/t}</button>
                                                                            </td>
                                                                        </tr>
                                                                </table>
                                                            </td>
                                                        </tr>                                  
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
        </td>
    </tr>
</table>