<!-- view.tpl -->
<table width="100%" border="0" cellpadding="20" cellspacing="5">
    <tr>
        <td>            
            <table width="700" cellpadding="4" cellspacing="0" border="0" >
                <tr>
                    <td class="menuhead2" width="80%">&nbsp;Scheduled ID {$schedule_details.SCHEDULE_ID} on {$schedule_details.SCHEDULE_START|date_format:$date_format}</td>
                </tr>
                <tr>
                    <td class="menutd2">
                        <table class="olotable" width="100%" border="0" cellpadding="5" cellspacing="0">
                            <tr>
                                <td class="menutd">
                                    <table width="100%" cellpadding="5" cellspacing="5">
                                        <tr>
                                            <td>
                                                <p><b>Date: </b>{$schedule_details.SCHEDULE_START|date_format:$date_format}</p>
                                                <p>
                                                    <b>{$translate_schedule_start}: </b>{$schedule_details.SCHEDULE_START|date_format:"%H:%M"}<br>
                                                    <b>{$translate_schedule_end}: </b>{$schedule_details.SCHEDULE_END|date_format:"%H:%M"}
                                                </p>
                                                <p><b>{$translate_schedule_tech}: </b>{$schedule_details.EMPLOYEE_DISPLAY_NAME}</p>
                                                <b>Notes:</b><br />
                                                <div>{$schedule_details.SCHEDULE_NOTES}</div><br>
                                                <button type="button" onClick="window.location='index.php?page=schedule:edit&schedule_id={$schedule_details.SCHEDULE_ID}';">{$translate_schedule_edit}</button>
                                                <a href="index.php?page=schedule:delete&schedule_id={$workorder_schedule[a].SCHEDULE_ID}" onclick="return confirmDelete('are you sure you want to delete the schedule item');"><button type="button">{$translate_schedule_delete}</button></a>                                                    
                                                <button type="button" onClick="window.location='index.php?page=schedule:icalendar&schedule_id={$schedule_details.SCHEDULE_ID}&theme=print';">Export</button>                                         
                                                <button type="button" onClick="window.location='index.php?page=workorder:details&workorder_id={$schedule_details.WORKORDER_ID}';">View Work Order</button>
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