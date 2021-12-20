<?php
use WHMCS\Database\Capsule;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

set_time_limit(0);

function passgenerator()
{
    $chars = "qazxswedasdfcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP";
    $sait="+=.-_!*";
    $saitcount=rand(1,4);
    $sitesize=StrLen( $sait ) -1;
    $max = 16;
    $size = StrLen( $chars ) - 1;
    $password = null;
    while ($max--) {
        $password .= $chars[rand( 0, $size )];
    }

    while ($saitcount--) {
        $password[rand( 0, 15 )] = $sait[rand( 0, $sitesize)];
    }

    return $password;
}

function addinbd($serviceid,$loginon,$password,$userid,$vmid,$modifyUser = 0,$adminUser='unknown')
{
    $result=Capsule::table( 'mod_on_user' )
        ->where('id_service',$serviceid)
        ->first();

    if($modifyUser!=0){
        $res=Capsule::table( 'tblactivitylog' )->insert(
            array(
                'date'=>date('Y-m-d G:i:s'),
                'description'=>"Modified ON data [login: $result->loginon, password: $result->passwordon, UserId: $result->userid, Vmid: $result->vmid] - modified on [login: $loginon, password: $password, UserId: $userid, Vmid: $vmid]  Service ID: $serviceid",
                'user'=>$adminUser,
                'userid'=>$modifyUser,
                'ipaddr'=>$_SERVER['REMOTE_ADDR']
            )
        );
    }

    if($result){
        return Capsule::table('mod_on_user')->where('id_service',$serviceid)
            ->update(
            array(
                'id_service'=>$serviceid,
                'loginon'=>$loginon,
                'passwordon'=>$password,
                'userid'=>$userid,
                'vmid'=>$vmid)
        );
    }
    else {
        return Capsule::table( 'mod_on_user' )->insert(
            array(
                'id_service'=>$serviceid,
                'loginon'=>$loginon,
                'passwordon'=>$password,
                'userid'=>$userid,
                'vmid'=>$vmid)
            );
    }
}


function onconnector_MetaData()
{
    return array(
        'DisplayName' => 'IOne',
        'APIVersion' => '1.1', // Use API Version 1.1
        'RequiresServer' => true, // Set true if module requires a server to work
        'DefaultNonSSLPort' => '8008', // Default Non-SSL Connection Port
        'DefaultSSLPort' => '1112', // Default SSL Connection Port
        'ServiceSingleSignOnLabel' => 'VM Control Panel',
        'AdminSingleSignOnLabel' => 'Login to Open Nebula as Admin',
    );
}

function onconnector_ConfigOptions()
{
    return array(// a text field type allows for single line text input
        'vCPU:' => array(
            'FriendlyName' => "vCPU:",
            'Type' => 'text',
            'Size' => '25',
            'Description' => 'vCPU',
        ),

        'RAM:' => array(
            'FriendlyName' => "RAM:",
            'Type' => 'text',
            'Size' => '25',
            'Description' => 'RAM',
        ),

        'DISK:' => array(
            'FriendlyName' => "DISK:",
            'Type' => 'text',
            'Size' => '25',
            'Description' => 'TYPE DISK(HDD,SSD,OTHER)',
        ),

        'DISK VALUE:' => array(
            'FriendlyName' => "DISK VALUE:",
            'Type' => 'text',
            'Size' => '25',
            'Description' => 'GB',
        ),

        'NT:' => array(
            'FriendlyName' => "NODE TYPE:",
            'Type' => 'dropdown',
            'Options' => [
                'pachage1' => 'Select a node',
                'pachage2' => 'vCenter',
                'pachage3' => 'KVM '
            ],
            'Description' => '',
        ),

        'SNAPSHOT:' => array(
            'FriendlyName' => "SNAPSHOT:",
            'Type' => 'yesno',
            'Description' => 'ALLOW SNAPSHOT',
        ),

        'OS:' => array(
            'FriendlyName' => "OS:",
            'Type' => 'yesno',
            'Description' => 'ADDON IN OS ',
        ),

    );
}




function onconnector_CreateAccount(array $params)
{try
{
    if($params['addonId'] == 0) {
        ini_set('error_reporting', E_ALL);
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        $allOptions['serviceid'] = $params['serviceid'];
        $allOptions['release'] = true;

        $templateid = Capsule::table('tblhostingaddons')
            ->select('mod_onconfiguratorOS.templateid', 'tbladdons.name')
            ->where('tblhostingaddons.hostingid', $params['serviceid'])
            ->join('tbladdons', 'tbladdons.id', '=', 'tblhostingaddons.addonid')
            ->join('mod_onconfiguratorOS', 'mod_onconfiguratorOS.addonid', '=', 'tblhostingaddons.addonid')
            ->first();

        $addons = Capsule::table('tblhostingaddons')
            ->select('tbladdons.*', 'tblhostingaddons.*')
            ->where('tblhostingaddons.hostingid', $params['serviceid'])
            ->join('tbladdons', 'tbladdons.id', '=', 'tblhostingaddons.addonid')
            ->get();

        foreach ($addons as $addon) {
            $addons_params[] = Capsule::table('tblmodule_configuration')->where('entity_id', '=', $addon->addonid)->get();
        }


        $allOptions['templateid'] = $templateid->templateid;

        $root = 'root';
        if ($templateid->name) {
            if (stristr($templateid->name, 'windows') != false) {
                $root = 'Administrator';
            } else {
                $root = 'root';
            }
        };

        $allOptions2['serviceid'] = $params['serviceid'];
        $allOptions2['templateid'] = $templateid->templateid;
        $allOptions2['release'] = true;
        $allOptions2['cpu'] = $params['configoption1'];
        $allOptions2['ram'] = $params['configoption2'];
        $allOptions2['drive'] = $params['configoption4'];
        $allOptions2['units'] = 'GB';
        $allOptions2['ds_type'] = $params['configoption3'];
        if($params['configoption6'] == 'on'){
            $allOptions2['allow_snapshots'] = true;
        }else{
            $allOptions2['allow_snapshots'] = false;
        }
        $allOptions2['groupid'] = 1;

        if($params['configoption5'] == 'pachage1'){
            return 'Node not selected! Check product configuration';
        }elseif($params['configoption5'] == 'pachage2'){
            $allOptions2['extra'] = array(
                'type' => 'vcenter'
            );
        }elseif($params['configoption5'] == 'pachage3'){
            $allOptions2['extra'] = array(
                'type' => 'kvm'
            );
        }

        $allOptions2['password'] = passgenerator();
        $allOptions2['login'] = 'user_' . $params['serviceid'];

        $postData = array(
            'password2' => passgenerator(),
        );


        $allOptions2['passwd'] = $postData['password2'];


        foreach ($addons_params as $addons_param) {
            foreach ($addons_param as $item) {
                switch ($item->setting_name) {
                    case 'configoption1':
                        $allOptions2['cpu'] += $item->value;
                        break;
                    case 'configoption2':
                        $allOptions2['ram'] += $item->value;
                        break;
                    case 'configoption4':
                        $allOptions2['drive'] += $item->value;
                        break;
                    case 'configoption6':
                        if($item->value == 'on'){
                            $allOptions2['allow_snapshots'] = true;
                        }
                        break;
                }
            }
        }


        $adminlog = Capsule::table('tblconfiguration')->where('setting', 'whmcs_admin')->get();
        $adminUsername = $adminlog[0]->value;

        $passroot = localAPI('EncryptPassword', $postData, $adminUsername);
        if ($passroot['result'] == 'error') {
            return "Error adminuser. Check configure module.";
        }

        require_once("lib/ONConnect.php");


        if (!$allOptions2['cpu'])                                // If CPU is null
        {
            return "Error allocating a new virtual machine template. CPU attribute must be a positive integer value";
        }

        if (!$allOptions2['ram'])                                // If RAM is null
        {
            return "Error allocating a new virtual machine template. RAM attribute must be a positive integer value";
        }

        if (!$allOptions2['drive'])                                // If Drive is null
        {
            return "Error allocating a new virtual machine template. Drive attribute must be a positive integer value";
        }
        if (!($allOptions2['ds_type'])) // If drive type is not set
        {
            return "Error allocating a new virtual machine template. Disk type attribute must be (HDD/SSD) constant value";
        }

        $onconnect = new ONConnect();
        $result = $onconnect->createVMwithSpecs($allOptions2);



        if ($result->response->error == true) {
            return 'error: ' . $result->errorMessage;
        }

        if ($result->response->error == 'UserAllocateError')                 //If user already exists(name taken);
        {
            return "User user_$params[serviceid] already exists in ON";
        }


        if ($result->response->exception) {                                  //If some unhandled Exeption
            return $result->response->exception;
        }

        if ($result->error == 'TemplateLoadError')                //If template not set
        {
            return 'error: Template load error';
        }

        if (!$result->response) {                                             //If answer is empty.

            return 'error: Error getting data from IONe';
        }

        Capsule::table('tblhosting')
            ->where('id', $params['serviceid'])
            ->update(
                array(
                    'dedicatedip' => $result->response->ip,
                    'username' => $root,
                    'password' => $passroot['password'],
                    'domain' => $result->response->ip,
                )
            );

        addinbd($params['serviceid'], $allOptions2['login'], $allOptions2['password'], $result->response->userid, $result->response->vmid);

    }
}

catch (Exception $e)
{
    // Record the error in WHMCS's module log.
    logModuleCall(
        'onconnector',
        __FUNCTION__,
        $params,
        $e->getMessage(),
        $e->getTraceAsString()
    );

    return $e->getMessage();
}
    return 'success';
}

function onconnector_SuspendAccount(array $params)
{

    if ($params['suspendreason'] == 'force') {
        $reason = true;
    } else {
        $reason = false;
    }

    logModuleCall(
        'test',
        'suspend start',
        'Immun-service suspend attempt',
        $params,

        );
    $onaccaunt = Capsule::table('mod_on_user')
        ->select("vmid")
        ->where('id_service', $params['serviceid'])
        ->first();
    $vmid = $onaccaunt->vmid;

    $resultSuspend = Capsule::table('tblhostingaddons')
        ->select('id')
        ->where('hostingid', $params['serviceid'])
        ->update(['status' => 'Suspended']);



    if (is_numeric($vmid)) {

        require_once("lib/ONConnect.php");
        $onconnect = new ONConnect($params['serverip']);
        $result = $onconnect->Suspend($vmid, $reason);
        logModuleCall(
            'test',
            'suspend finish',
            'Immun-service suspend attempt',
            $result,

        );
    } else {
        $command = 'OpenTicket';
        $postData = array(
            'deptid' => '7',
            'subject' => 'Account data is not set',
            'message' => 'Service suspend error: ' . $params['serviceid'] . ' Account: ' . $params['clientsdetails']['fullname'] . ' enter VMid and/or userId on service page: /admin/clientsservices.php?userid=' . $params['userid'] . '&id=' . $params['serviceid'],
            'name' => 'onconector',
            'priority' => 'Medium',
            'markdown' => true,
        );
        $adminlog = Capsule::table('tbladmins')
            ->select('username')->where('disabled', '=', '0')->get();
        $adminUsername = $adminlog[0]->username;

        $results = localAPI($command, $postData, $adminUsername);
        return "error: Auth data is incorrect";
    }

    return 'success';
}

function onconnector_ChangePackage(array $params)
{
        $onaccaunt=Capsule::table('mod_on_user')
            ->select('vmid')
            ->where('id_service',$params['serviceid'])
            ->first();                                    // Getting user_id, vmid, Login, Password,
        $vmid=$onaccaunt->vmid;
        if(is_numeric($vmid)) {
            require_once("lib/ONConnect.php");
            $onconnect = new ONConnect($params['serverip']);
            $result = $onconnect->Unsuspend($vmid);
            if ($result->response->userid) {
                Capsule::table('mod_on_user')
                    ->where('id_service', $params['serviceid'])
                    ->update(
                        array(
                            'userid' => $result->response->userid,
                        )
                    );
            }
            $result = json_encode( $result );
        }
        else
        {
            return "error: Auth data is incorrect";
        }
    return 'success';
}

function onconnector_UnsuspendAccount(array $params)
{

    $onaccaunt = Capsule::table('mod_on_user')
        ->where('id_service', $params['serviceid'])
        ->first();                                      // Getting data for ON from DB.

    $resultSuspend = Capsule::table('tblhostingaddons')
        ->select('id')
        ->where('hostingid', $params['serviceid'])
        ->update(['status' => 'Active']);

    $vmid = $onaccaunt->vmid;                             // Putting query-results to vars

    if (is_numeric($vmid)) {
        require_once("lib/ONConnect.php");
        $onconnect = new ONConnect($params['serverip']);
        $result = $onconnect->Unsuspend($vmid);
    } else {
        return "error: Auth data is incorrect";
    }

    return 'success';
}

function onconnector_TerminateAccount(array $params)
{


    try {
        $onaccaunt=Capsule::table('mod_on_user')->where('id_service',$params['serviceid'])->first();
        $userid=$onaccaunt->userid;
        $vmid=$onaccaunt->vmid;

        if(is_numeric($userid) && is_numeric($vmid) && $userid != 0 && $vmid != 0)
        {
            require_once( "lib/ONConnect.php" );
            $onconnect = new ONConnect($params['serverip']);
            $result = $onconnect->Terminate($userid, $vmid);

        }
        else
        {

            return "error: Auth data is incorrect";
        }
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'onconnector',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );
        return $e->getMessage();
    }
    return 'success';
}

function onconnector_AdminCustomButtonArray()
{
    return array(
        "Reboot" => "restart",
        "Shutdown" => "down",
        "Check"=>"buttonForeFunction",
    );
}

function onconnector_restart(array $params)
{
    try {
        require_once( "lib/ONConnect.php" );
        $onaccaunt=Capsule::table('mod_on_user')->where('id_service',$params['serviceid'])->first();
        $vmid=$onaccaunt->vmid;
        if(is_numeric($vmid)) {
            $onconnect = new ONConnect( $params['serverip'] );
            $result = $onconnect->Reboot( $vmid );
        }
        else
        {
            return "error: Auth data is incorrect";
        }

        // Call the service's function, using the values provided by WHMCS in
        // `$params`.
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'onconnector',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

function onconnector_down(array $params)
{
    try {
        require_once( "lib/ONConnect.php" );
        $onaccaunt=Capsule::table('mod_on_user')
            ->where('id_service',$params['serviceid'])
            ->first();
        $vmid=$onaccaunt->vmid;
        if(is_numeric($vmid)) {
            $onconnect = new ONConnect( $params['serverip'] );
            $result = $onconnect->Shutdown( $vmid );
        }
        else
        {
            return "error: Auth data is incorrect";
        }

        // Call the service's function, using the values provided by WHMCS in
        // `$params`.
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'onconnector',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

function onconnector_buttonForeFunction(array $params){
    require_once("lib/ONConnect.php");

    $onconnect = new ONConnect( $params['serverip'] );
    $user= 'dev_user_'.$params['serviceid'];
    $result = $onconnect->getVmByName($user);

        if($result->response->vmid!='none') {
            Capsule::table('mod_on_user')
                ->where('id_service', $params['serviceid'])
                ->update(
                    [
                        'loginon' => $user,
                        'vmid' => $result->response->vmid,
                        'userid' => $result->response->userid
                    ]
                );
        }
        if($result->response->ip!='nil') {
        Capsule::table('tblhosting')
            ->where('id',$params['serviceid'])
            ->update(
                [
                    'dedicatedip'=>$result->response->ip
                ]
            );
    }

}




function onconnector_AdminServicesTabFields(array $params)
{
try{

    $onaccaunt=Capsule::table('mod_on_user')
        ->where('id_service',$params['serviceid'])
        ->first();
    {
        $loginon=$onaccaunt->loginon;
        $password=$onaccaunt->passwordon;
        $userid=$onaccaunt->userid;
        $vmid=$onaccaunt->vmid;
    }

    $fieldsarray = array(
        'Login ON' => '<input type="text" name="loginON" size="30" value="'.$loginon.'" />',
        'Passwordon ON' => '<input type="text" name="passwordON" size="30" value="'.$password.'" />',
        'userid ON' => '<input type="text" name="useridON" size="30" value="'.$userid.'" />',
        'vmid ON' => '<input type="text" name="vmidON" size="30" value="'.$vmid.'" />',
    );

}
    catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'onconnector',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        // In an error condition, simply return no additional fields to display.
    }

    return $fieldsarray;
}

function onconnector_AdminServicesTabFieldsSave($params)
{

    try {
        $login=$_POST['loginON'];
        $password=$_POST['passwordON'];
        $userid=$_POST['useridON'];
        $vmid=$_POST['vmidON'];

        addinbd($params['serviceid'],$login,$password,$userid,$vmid,$params['userid'],$params['serverusername']);
    }
    catch (Exception $e) {
        logModuleCall(
            'onconnector',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );
    }
}


function onconnector_AdminSingleSignOn(array $params)
{
    try {
        // Call the service's single sign-on admin token retrieval function,
        // using the values provided by WHMCS in `$params`.
        $response = array();

        return array(
            'success' => true,
            'redirectTo' => '/admin/addonmodules.php?module=tabl&mod=test&serviceId='.$params['serviceId']
        );
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'onconnector',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );
        return array(
            'success' => false,
            'errorMsg' => $e->getMessage(),
        );
    }
}


function onconnector_ClientArea(array $params)
{
    // Determine the requested action and set service call parameters based on
    // the action.

    logModuleCall(
        'provisioningmodule',
        __FUNCTION__,
        $params,
        $_REQUEST,
        $params
    );

        $requestedAction = $_REQUEST;
        $templateFile = 'templates/mysimplepanel.tpl';

    try {
        $response = array();
        $extraVariable1 = 'abc';
        $extraVariable2 = '123';
        return array(
            'tabOverviewModuleOutputTemplate' => $templateFile,
            'templateVariables' => array(
                'extraVariable1' => $extraVariable1,
                'extraVariable2' => $extraVariable2,
            ),
        );
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'provisioningmodule',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );
        // In an error condition, display an error page.
        return array(
            'tabOverviewReplacementTemplate' => 'error.tpl',
            'templateVariables' => array(
                'usefulErrorHelper' => $e->getMessage(),
            ),
        );
    }
}


