

{literal}
    <style>
        .fieldarea{
            font-weight: bold;
        }
    </style>
{/literal}

<table align="center">
    <tbody>
    <tr>
        <td class="fieldarea">
            {$LANG.gogetssl_status}
        </td>
        <td style="padding: 5px;">
            {$goget_status}
        </td>
    </tr>
    {if $goget_order.status_description != '' && $goget_status != $goget_order.status_description}
        <tr>
            <td class="fieldarea">
                {$LANG.gogetssl_status_description}
            </td>
            <td style="padding: 5px;">
                {$goget_order.status_description}
            </td>
        </tr>
    {/if}
    {if $goget_order.status eq "active"}
        <tr>
            <td class="fieldarea">
                {$LANG.gogetssl_valid_from}
            </td>
            <td style="padding: 5px;">
                <b>
                    {$goget_order.valid_from}
                </b>
            </td>
        </tr>
        <tr>
            <td class="fieldarea">
                {$LANG.gogetssl_valid_till}
            </td>
            <td style="padding: 5px;">
                <b>
                    {$goget_order.valid_till}
                </b>
            </td>
        </tr>
    {/if}
    {if $goget_order.domain != ''}
        <tr>
            <td class="fieldarea">
                {$LANG.gogetssl_domain}
            </td>
            <td style="padding: 5px;">
                {$goget_order.domain}
            </td>
        </tr>
    {/if}
    {if $goget_order.domains != ''}
        <tr>
            <td class="fieldarea">
                {$LANG.gogetssl_san_domains}
            </td>
            <td style="padding: 5px;">
                {assign var=domains value=','|explode:$goget_order.domains}

                {foreach from=$domains item=domain}
                    {$domain}<br/>
                {/foreach}
            </td>
        </tr>
    {/if}
    {if $goget_order.approver_emails != '' || $goget_order.approver_email != ''}
        <tr>
            <td class="fieldarea">
                {$LANG.gogetssl_approver_email}
            </td>
            <td style="padding: 5px;">
                {if $goget_order.approver_emails != ''}
                    {assign var=approver_emails value=','|explode:$goget_order.approver_emails}

                    {foreach from=$approver_emails item=approver_email}
                        {$approver_email}<br/>
                    {/foreach}
                {else}
                    {$goget_order.approver_email}
                {/if}
            </td>
        </tr>
    {/if}
    {if $goget_order.partner_order_id != ''}
        <tr>
            <td class="fieldarea">
                {$LANG.gogetssl_partner_order_id}
            </td>
            <td style="padding: 5px;">
                {$goget_order.partner_order_id}
            </td>
        </tr>
    {/if}
    {if $goget_order.status eq 'active'}
        <tr>
            <td class="fieldarea">
                {$LANG.gogetssl_csr_code}
            </td>
            <td style="padding: 5px;">
                <textarea rows="10" cols="100" style="margin: 0px 0px 9px; width: 380px; height: 200px;">{$goget_order.csr_code}</textarea>
            </td>
        </tr>
        <tr>
            <td class="fieldarea">
                {$LANG.gogetssl_crt_code}
            </td>
            <td style="padding: 5px;">
                <textarea rows="10" cols="100" style="margin: 0px 0px 9px; width: 380px; height: 200px;">{$goget_order.crt_code}</textarea>
            </td>
        </tr>
        <tr>
            <td class="fieldarea">
                {$LANG.gogetssl_ca_code}
            </td>
            <td style="padding: 5px;">
                <textarea rows="10" cols="100" style="margin: 0px 0px 9px; width: 380px; height: 200px;">{$goget_order.ca_code}</textarea>
            </td>
        </tr>
    {/if}
    {if $goget_raw_status eq 'awaiting_configuration'}
        <tr>
            <td colspan="2">
                <a class="btn" onclick="location.href='{$configuration_url}'"> <i class="icon icon-cog"></i> {$LANG.gogetssl_goto_conf}</a>
            </td>
        </tr>
    {/if}
    </tbody>
</table>
