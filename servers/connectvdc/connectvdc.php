<?php
use WHMCS\Database\Capsule;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

set_time_limit(0);

function passgenerator_iaas()
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

function addinbd_iaas($serviceid,$loginon,$password,$useridon , $userid ,$modifyUser = 0,$adminUser='unknown')
{
    $result=Capsule::table( 'mod_iaas_user' )
        ->where('id_service',$serviceid)
        ->first();

    if($modifyUser!=0){
        $res=Capsule::table( 'tblactivitylog' )->insert(
            array(
                'date'=>date('Y-m-d G:i:s'),
                'description'=>"Modified ON data [login: $result->loginon, password: $result->passwordon, UserId: $result->userid] - modified on [login: $loginon, password: $password, UserId: $userid]  Service ID: $serviceid",
                'user'=>$adminUser,
                'userid'=>$modifyUser,
                'ipaddr'=>$_SERVER['REMOTE_ADDR']
            )
        );
    }

    if($result){
        return Capsule::table('mod_iaas_user')->where('id_service',$serviceid)
            ->update(
                array(
                    'id_service'=>$serviceid,
                    'loginon'=>$loginon,
                    'passwordon'=>$password,
                    'useridOn'=>$useridon,
                    'userid'=>$userid,)
            );
    } else {
        return Capsule::table( 'mod_iaas_user' )->insert(
            array(
                'id_service'=>$serviceid,
                'loginon'=>$loginon,
                'passwordon'=>$password,
                'useridOn'=>$useridon,
                'userid'=>$userid,)
        );
    }
}

function connectvdc_MetaData()
{
    return array(
        'DisplayName' => 'IaaS',
        'APIVersion' => '1.1', // Use API Version 1.1
        'RequiresServer' => true, // Set true if module requires a server to work
        'DefaultNonSSLPort' => '8008', // Default Non-SSL Connection Port
        'DefaultSSLPort' => '1112', // Default SSL Connection Port
        'ServiceSingleSignOnLabel' => 'VM Control Panel',
        'AdminSingleSignOnLabel' => 'Login to Open Nebula as Admin',
    );
}


function connectvdc_CreateAccount(array $params)
{try
{
    ini_set('error_reporting', E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);

    $onaccaunt=Capsule::table('mod_iaas_user')
        ->where('id_service',$params['serviceid'])
        ->first();

    $userid=$onaccaunt->userid;
    $vmid=$onaccaunt->vmid;

    if (!ctype_digit($userid) && !ctype_digit($vmid)) {

        $postData = array(
            'password2' => passgenerator_iaas(),
        );
        $groupid = Capsule::table('tblconfiguration')
            ->where('setting', 'id_group_iaas')
            ->get();

        $param = array('user_' . $params['serviceid'],$postData['password2'],$groupid[0]->value * 1,'en_US');
        $createIaaSUser['login'] = 'user_' . $params['serviceid'];
        $createIaaSUser['pass'] = $postData['password2'];
        $createIaaSUser['groupid'] = $groupid[0]->value * 1;
        $createIaaSUser['locale '] = 'en_US';


        require_once( __DIR__."/../onconnector/lib/ONConnect.php" );

        $onconnect = new ONConnect();
        $result = $onconnect->IaaS("UserCreate", $param);
        unset($param);

        if($result->response->error==true){
            return 'error: '.$result->response->error;
        }

        if($result->response->error=='UserAllocateError'){
            return "User user_$params[serviceid] already exists in ON";
        }

        if($result->response->exeption){
            return $result->response->exeption;
        }

        if ($result->response->error=='TemplateLoadError'){
            return 'error: Template load error';
        }

        if(!$result->response){
            return 'error: Error getting data from IONe';
        }

        Capsule::table( 'tblhosting' )
            ->where('id',$params['serviceid'])
            ->update(
                array (
                    'domain'=>$result->response->ip,
                )
            );

        addinbd_iaas( $params['serviceid'], $createIaaSUser['login'], $createIaaSUser['pass'], $result->response, $params['userid']);
    }
    else
    {
        return 'error: Only one account for one service';
    }

}

catch (Exception $e)
{
    logModuleCall(
        'connectvdc',
        __FUNCTION__,
        $params,
        $e->getMessage(),
        $e->getTraceAsString()
    );

    return $e->getMessage();
}
    return 'success';
}

function connectvdc_SuspendAccount(array $params)
{

    require_once( __DIR__."/../onconnector/lib/ONConnect.php" );

    $onaccaunt=Capsule::table('mod_iaas_user')
        ->where('id_service',$params['serviceid'])
        ->first();

    $onconnect = new ONConnect();

    $param = array($onaccaunt->useridOn);

    $result=$onconnect->IaaS('SuspendUser',$param);

    $resultSuspend = Capsule::table('tblhostingaddons')
        ->select('id')
        ->where('hostingid', $params['serviceid'])
        ->update(['status' => 'Suspended']);

    return 'success';
}

function connectvdc_UnsuspendAccount(array $params)
{
    require_once( __DIR__."/../onconnector/lib/ONConnect.php" );
    $onaccaunt=Capsule::table('mod_iaas_user')
        ->where('id_service',$params['serviceid'])
        ->first();
    $onconnect= new ONConnect();
    $param = array($onaccaunt->useridOn);
    $result=$onconnect->IaaS('UnsuspendUser',$param);
    $resultSuspend = Capsule::table('tblhostingaddons')
        ->select('id')
        ->where('hostingid', $params['serviceid'])
        ->update(['status' => 'Active']);

    return 'success';
}

function connectvdc_TerminateAccount(array $params)
{

    $onaccaunt=Capsule::table('mod_iaas_user')
        ->where('id_service',$params['serviceid'])
        ->first();

    require_once( __DIR__."/../onconnector/lib/ONConnect.php" );
    $onconnect= new ONConnect();
    if(is_numeric($onaccaunt->useridOn) && $onaccaunt->useridOn != 0) {
        $param = array($onaccaunt->useridOn);

        $result = $onconnect->IaaS('UserDelete', $param);
    }else{
        return "error: Auth data is incorrect";
    }

    return 'success';
}



function connectvdc_AdminServicesTabFields(array $params)
{
    try{
        $onaccaunt=Capsule::table('mod_iaas_user')
            ->where('id_service',$params['serviceid'])
            ->first();
        {
            $loginon=$onaccaunt->loginon;
            $password=$onaccaunt->passwordon;
            $userid=$onaccaunt->useridOn;
        }
        $fieldsarray = array(
            'Login ON' => '<input type="text" name="loginON" size="30" value="'.$loginon.'" />',
            'Passwordon ON' => '<input type="text" name="passwordON" size="30" value="'.$password.'" />',
            'userid ON' => '<input type="text" name="useridON" size="30" value="'.$userid.'" />',
        );
    }
    catch (Exception $e) {

        logModuleCall(
            'connectvdc',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

    }

    return $fieldsarray;
}

function connectvdc_AdminServicesTabFieldsSave($params)
{

    try {
        $login=$_POST['loginON'];
        $password=$_POST['passwordON'];
        $userid=$_POST['useridON'];

        addinbd_iaas($params['serviceid'],$login,$password,$userid,$params['userid'],$params['serverusername']);

    }
    catch (Exception $e) {
        logModuleCall(
            'connectvdc',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );
    }
}

