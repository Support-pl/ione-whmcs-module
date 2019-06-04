<?php

use WHMCS\Database\Capsule;



function hook_onconnector_clientedit(array $params)
{
    try {

    } catch (Exception $e) {
        // Consider logging or reporting the error.
    }
}

function hook_onconnector_InvoicePaid(array $params)
{
    try {
        $order = Capsule::table('tblorders')
            ->where('invoiceid', $params['invoiceid'])
            ->get();

        $tblhosting = Capsule::table('tblhosting')
            ->where('orderid', $order[0]->id)
            ->get();

        logModuleCall(
            'onconnector',
            __FUNCTION__,
            $params,
            $params,
            "Paid"
        );

        $data = Capsule::table('mod_on_user')
            ->where('id_service', $tblhosting[0]->id)
            ->get();

        $server = Capsule::table('tblservers')
            ->where('id', $tblhosting[0]->server)
            ->get();
        if (isset($server[0]->ipaddress) && ctype_digit($data[0]->userid) && ctype_digit($data[0]->vmid)) {

            require_once("lib/ONConnect.php");
            $onconnect = new ONConnect($server[0]->ipaddress);
            $result = $onconnect->Unsuspend($data[0]->vmid);

            Capsule::table('tblhosting')
                ->where('orderid', $order[0]->id)
                ->update(
                    array(
                        'domainstatus' => 'Activity',
                    ));
        }
    } catch (Exception $e) {
        logModuleCall(
            'onconnector',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );
    }

}

function hook_onconnector_EmailTplMergeFields(array $params)
{
    $merge_fields = [];
    $merge_fields['ONLogin'] = "User ON";
    $merge_fields['OnPassword'] = "Password ON";
    $merge_fields['wiki_link'] = "Wiki link";
    return $merge_fields;
}

function hook_onconnector_EmailPreSend(array $params)
{
    $date = Capsule::table('mod_on_user')
        ->where('id_service', $params['relid'])
        ->get();
    $merge_fields = [];
    $merge_fields['ONLogin'] = $date[0]->loginon;
    $merge_fields['OnPassword'] = $date[0]->passwordon;
    $marge_fields['wiki_link'] = 'https://goo.gl/k5VgQE';
    return $merge_fields;
}

add_hook('AddonSuspended', 1, function ($vars) {
    $command = 'GetClientsProducts';
    $postData = array(
        'serviceid' => $vars['serviceid'],
    );                                                                      // Getting service;
    $adminlog = Capsule::table('tblconfiguration')->where('setting', 'whmcs_admin')->get();
    $adminUsername = $adminlog[0]->value;                                             // Init using hell api;

    $results = localAPI($command, $postData, $adminUsername);

    logModuleCall(
        'AddonSuspended',
        __FUNCTION__,
        'Suspend attempt',
        $vars,
        $vars
    );

    if ($results['products']['product'][0]['status'] == 'Active') {

        $command = 'UpdateClientAddon';
        $postData = array(
            'id' => $vars['id'],
            'status' => 'Active',
        );
        $adminlog = Capsule::table('tblconfiguration')->where('setting', 'whmcs_admin')->get();
        $adminUsername = $adminlog[0]->value; // Optional for WHMCS 7.2 and later

        $results = localAPI($command, $postData, $adminUsername);
        print_r($results);
    }
});

add_hook('PreModuleSuspend', 1, function ($params) {

    $iaas_servers =  Capsule::table( 'mod_iaas_servers' )->where('idproduct','=',$params['pid'])->get();
    if($iaas_servers == null) {

        if ($params['params']['moduletype'] != 'onconnector') {
            return true;
        } else {
            logModuleCall(
                'immun',
                __FUNCTION__,
                'Suspend attempt',
                $params,
                get_declared_classes()
            );
        }

        if (class_exists('WHMCS\Form')) {
            logModuleCall(
                'immun',
                __FUNCTION__,
                'Manual suspend',
                $params,
                get_declared_classes()
            );
        }

        if (strstr($params['params']['suspendreason'], 'force')) {
            return true;
        } else {
            $castomfild = Capsule::table('tblcustomfieldsvalues')
                ->where('relid', $params['params']['userid'])
                ->where('fieldid', '3697')
                ->get();



            $customname = Capsule::table('tblconfiguration')->where('setting', 'customfield')->get();
            $customfildcheck = Capsule::table('tblcustomfields')
                ->select('id')->where('fieldname', $customname[0]->value)->get();
            $castomfild = Capsule::table('tblcustomfieldsvalues')
                ->where('relid', $params['params']['userid'])
                ->where('fieldid', $customfildcheck[0]->id)
                ->get();

            if (((!isset($castomfild[0]->value)) || ($castomfild[0]->value != 'Да')) && ((!isset($castomfild[0]->value)) || ($castomfild[0]->value != 'Yes')) && (date(N) < 5)) {

                // If immun field not created or not filled with 'Yes', and today is not 5, 6 or 7-th weekday.

                $command = 'GetClientsProducts';
                $postData = array(
                    'serviceid' => $params['params']['serviceid'],
                );                                                                      // Getting service;
                $adminlog = Capsule::table('tblconfiguration')->where('setting', 'whmcs_admin')->get();
                $adminUsername = $adminlog[0]->value;                                              // Init using hell api;

                $results = localAPI($command, $postData, $adminUsername);
                $age = time() - strtotime($results['products']['product'][0]['regdate']);   // Service 'age';

                if ($age < 2592000) {                                                       // If service isn't older than 30 days;
                    logModuleCall(
                        'immun',
                        __FUNCTION__,
                        'Service age is lower than 30 days',
                        $params,
                        $params
                    );
                    $result['abortcmd'] = true;                                         // Setting param for suspend cancellation;
                    return $result;                                                     // Return vars to handler;
                }

                $invoiceitems = Capsule::table('tblinvoiceitems')
                    ->where('relid', $params['params']['serviceid'])
                    ->get();                                        // Getting invoices for this service from DB;

                if (!$invoiceitems) {                                 // If no invoices;
                    logModuleCall(
                        'immun',
                        __FUNCTION__,
                        'Service suspended',
                        $params,
                        $params
                    );
                    $result['abortcmd'] = true;                     // Setting param for suspend cancellation;
                    return $result;                                 // Return vars to handler;
                }

                $lastInvoice = array_pop($invoiceitems);              // Getting last invoice(dangerous, check it twice);

                if (time() < strtotime($lastInvoice->duedate)) {        // If pay-date earlier than today.
                    logModuleCall(
                        'immun',
                        __FUNCTION__,
                        'Invoice transfered or delayed',
                        $params,
                        $params
                    );

                    $result['abortcmd'] = true;                     // Setting param for suspend cancellation;
                    return $result;                                 // Return vars to handler;
                }

                $delay = Capsule::table('support_delay_invoice')
                    ->where('invoice_id', $lastInvoice->invoiceid)
                    ->get();                                        // Getting from DB next pay-date;

                if (time() < strtotime($delay[0]->expire_date))        // If today is earlier than delayed pay-date;
                {
                    $result['abortcmd'] = true;                     // Setting param for suspend cancellation;
                    return $result;                                 // Return vars to handler;
                } else {
                    logModuleCall(
                        'immun',
                        __FUNCTION__,
                        'Service suspended',
                        $params,
                        $params
                    );
                    return true;                                         // Suspending
                }
            } else {
                logModuleCall(
                    'immun',
                    __FUNCTION__,
                    'Immun-service suspend attempt',
                    $params,
                    $params['params']['suspendreason']
                );

                $result['error'] = "This service has suspend immun";
                $result['abortcmd'] = true;
                return $result;
            }
        }
    }
});
add_hook('InvoicePaid', 1, function ($vars) {

    $command = 'GetInvoice';
    $postData = array(
        'invoiceid' => $vars['invoiceid'],
    );
    $adminlog = Capsule::table('tblconfiguration')->where('setting', 'whmcs_admin')->get();
    $adminUsername = $adminlog[0]->value;

    $results = localAPI($command, $postData, $adminUsername);
    $usid = $results['userid'];

    $command = 'GetClientsProducts';
    $postData = array(
        'clientid' => $usid,
        'stats' => true,
    );
    $adminlog = Capsule::table('tblconfiguration')->where('setting', 'whmcs_admin')->get();
    $adminUsername = $adminlog[0]->value;

    $results = localAPI($command, $postData, $adminUsername);


    if (strripos($results['products']['product'][0]['translated_groupname'], 'Trial') > -1) {
        $command = 'ModuleCreate';
        $postData = array(
            'accountid' => $results['products']['product'][0]['id'],
        );

        $adminlog = Capsule::table('tblconfiguration')->where('setting', 'whmcs_admin')->get();
        $adminUsername = $adminlog[0]->value;

        $results = localAPI($command, $postData, $adminUsername);

    }

    logModuleCall(
        'onconnector',
        __FUNCTION__,
        $vars, $results, $results
    );

    return;

});
add_hook('EmailPreSend', 1, 'hook_onconnector_EmailPreSend');
add_hook('EmailTplMergeFields', 1, 'hook_onconnector_EmailTplMergeFields');
add_hook('InvoiceUnpaid', 1, 'hook_onconnector_Invoice');
add_hook('InvoiceCancelled', 1, 'hook_onconnector_InvoiceCancelled');
add_hook('ClientEdit', 1, 'hook_onconnector_clientedit');
add_hook('AfterInvoicingGenerateInvoiceItems', 1, 'hook_onconnector_AfterInvoicingGenerateInvoiceItems');
add_hook('ClientAreaPageProductDetails', 1, function ($vars) {
    $userDate = Capsule::table('mod_on_user')
        ->select('loginon', 'passwordon')
        ->where('id_service', $vars['serviceid'])
        ->first();
    $extraTemplateVariables['loginOn'] = $userDate->loginon;
    $extraTemplateVariables['passwordOn'] = $userDate->passwordon;
    return $extraTemplateVariables;
});

add_hook('ClientAreaPageProductDetails', 1, function ($vars) {
    $userDate = Capsule::table('mod_on_user')
        ->select('loginon', 'passwordon')
        ->where('id_service', $vars['serviceid'])
        ->first();
    $extraTemplateVariables['loginOn'] = $userDate->loginon;
    $extraTemplateVariables['passwordOn'] = $userDate->passwordon;
    return $extraTemplateVariables;
});



    add_hook('ClientAreaPageHome', 1, function ($vars) {
        if ($vars['clientsdetails']['userid']){
        $result = '';
        $counter = 0;
        $id = $vars['clientsdetails']['userid'];
        $key = 413397;
        $result = strlen($id) . $id;
        while (strlen($result) < 6) {
            if ($counter > strlen($id)) {
                $counter = 0;
            }
            $result .= str_split($vars['clientsdetails']['userid'])[0];
            $counter++;
        }
        $key = (string)$key;
        for ($i = 0; $i < 6; $i++) {
            $result[$i] = ($result[$i] + $key[$i]) % 10;
        }
        $extraTemplateVariables['clientPinCode'] = $result;
        return $extraTemplateVariables;

        }
    });
