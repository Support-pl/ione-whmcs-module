<?php

use WHMCS\Database\Capsule;


function hook_connectvdc_EmailTplMergeFields(array $params)
{
    $merge_fields = [];
    $merge_fields['VDCONLogin'] = "User ON";
    $merge_fields['VDCOnPassword'] = "Password ON";
    return $merge_fields;
}

function hook_connectvdc_EmailPreSend(array $params)
{
    $date = Capsule::table('mod_iaas_user')
        ->where('id_service', $params['relid'])
        ->get();
    $merge_fields = [];
    $merge_fields['ONLogin'] = $date[0]->loginon;
    $merge_fields['OnPassword'] = $date[0]->passwordon;
    return $merge_fields;
}


add_hook('AddonSuspended', 1, function ($vars) {
    $command = 'GetClientsProducts';
    $postData = array(
        'serviceid' => $vars['serviceid'],
    );                                                                      // Getting service;
    $adminlog = Capsule::table('tblconfiguration')->where('setting', 'whmcs_admin')->get();
    $adminUsername = $adminlog[0]->value;

    $results = localAPI($command, $postData, $adminUsername);


    if ($results['products']['product'][0]['status'] == 'Active') {

        $command = 'UpdateClientAddon';
        $postData = array(
            'id' => $vars['id'],
            'status' => 'Active',
        );
        $adminlog = Capsule::table('tblconfiguration')->where('setting', 'whmcs_admin')->get();
        $adminUsername = $adminlog[0]->value;

        $results = localAPI($command, $postData, $adminUsername);
        print_r($results);
    }
});



add_hook('EmailPreSend', 1, 'hook_connectvdc_EmailPreSend');

add_hook('EmailTplMergeFields', 1, 'hook_connectvdc_EmailTplMergeFields');


add_hook('ClientAreaPageProductDetails', 1, function ($vars) {
    $userDate = Capsule::table('mod_iaas_user')
        ->select('loginon', 'passwordon')
        ->where('id_service', $vars['serviceid'])
        ->first();
    $extraTemplateVariables['loginOn'] = $userDate->loginon;
    $extraTemplateVariables['passwordOn'] = $userDate->passwordon;
    return $extraTemplateVariables;
});


