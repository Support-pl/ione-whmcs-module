<?php
use WHMCS\Database\Capsule;
use Symfony\Component\Yaml\Yaml;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}
ini_set('display_errors', 0);

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
        'Group ID:' => array(
            'FriendlyName' => "Group ID:",
            'Type' => 'text',
            'Size' => '25',
            'Description' => 'Group ID',
        ),
    );
}

function onconnector_CreateAccount(array $params)
{try
{

    ini_set('error_reporting', E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    $allOptions['serviceid'] = $params['serviceid'];
    $allOptions['release'] = true;

    $templateid=Capsule::table('tblhostingaddons')
        ->select('mod_onconfiguratorOS.templateid','tbladdons.name')
        ->where('tblhostingaddons.hostingid',$params['serviceid'])
        ->join('tbladdons','tbladdons.id','=','tblhostingaddons.addonid')
        ->join('mod_onconfiguratorOS','mod_onconfiguratorOS.addonid','=','tblhostingaddons.addonid')
        ->first();

    $allOptions['templateid']=$templateid->templateid;

    $root='root';
    if($templateid->name){
        if (stristr( $templateid->name, 'windows')!=false){
            $root='Administrator';
        }
        else{
            $root='root';
        }
    };

    $product=Capsule::table( 'tblproducts' )
        ->select('description')
        ->where('id',$params['packageid'])
        ->first();

    $properties=json_decode($product->description,true);
    foreach ($properties['properties'] as $propertie){
        switch ($propertie['GROUP']){
            case 'trial':
                $allOptions['trial']=true;
                $allOptions['trial-suspend-delay']=(int)$propertie['VALUE'];
                break;
            case 'cpu_core':
                $value=explode(' ',$propertie['TITLE']);
                $allOptions['cpu']+=$value[0];
                break;
            case 'ram':
                $value=explode(' ',$propertie['TITLE']);
                $allOptions['ram']+=$value[0];
                break;
            case 'instance_size':
                $azurename = $propertie['TITLE'];
                break;
            case 'hdd':
                $value=explode(' ',$propertie['TITLE']);
                $allOptions['drive']+=$value[0];
                $allOptions['units']=strtoupper($value[1]);
                $allOptions['ds_type']='HDD';
                $allOptions['iops']=$propertie['IOPS'];
                break;
            case 'ssd':
                $value=explode(' ',$propertie['TITLE']);
                $allOptions['drive']+=$value[0];
                $allOptions['units']=strtoupper($value[1]);
                $allOptions['ds_type']='SSD';
                $allOptions['iops']=$propertie['IOPS'];
                break;
        }
    }

    foreach (Capsule::table( 'tbladdons' )
                 ->select('tbladdons.description')
                 ->join('tblhostingaddons','tbladdons.id','=','tblhostingaddons.addonid')
                 ->where('hostingid',$params['serviceid'])
                 ->get() as $addons){
        $addons = json_decode($addons->description,true);
        switch ($addons['GROUP']){
            case 'hdd':
                $allOptions['drive']+=$addons['VALUE'];
                $allOptions['units']='GB';
                $allOptions['ds_type']='HDD';
                $allOptions['iops']=350;
                break;
            case 'cpu_core':
                $allOptions['cpu']+=$addons['VALUE'];
                break;
            case 'ram':
                $allOptions['ram']+=$addons['VALUE'];
                $allOptions['units']='GB';
                break;
            case 'trial':
                $allOptions['trial']=true;
                break;
            case 'ssd':
                $allOptions['drive']+=$addons['VALUE'];
                $allOptions['units']='GB';
                $allOptions['ds_type']='SSD';
                $allOptions['iops']=1000;
                break;
        }
    }

    $allOptions['groupid']=$params['configoption1'];

    $onaccaunt=Capsule::table('mod_on_user')
        ->where('id_service',$params['serviceid'])
        ->first();



    $userid=$onaccaunt->userid;
    $vmid=$onaccaunt->vmid;


    if (!ctype_digit($userid) && !ctype_digit($vmid))
    {
        $ansibles=[];
        $allOptions['ansible']=false;
        foreach (Capsule::table( 'tblhostingaddons' )
                     ->select('mod_onconfiguratorAddon.id')
                     ->where('tblhostingaddons.hostingid',$params['serviceid'])
                     ->join('mod_onconfiguratorAddon','mod_onconfiguratorAddon.Addon','=','tblhostingaddons.addonid')
                     ->get() as $item) {
            $ansibles[]=$item->id;
        }
        $isCheckAnsible=Capsule::table( 'tblconfiguration' )->where('setting','ione_use_ansible')->get();
        if($isCheckAnsible[0]->value=='on') {
            require_once($_SERVER['DOCUMENT_ROOT'] . '/modules/addons/oncontrol/classes/ActivateAnsible.php');
            $ansibleDB = new AutoActivateAnsible($params['serviceid']);
            $allOptions['services'] = $ansibleDB->getServicesFromIds($ansibles);

        }

        if($allOptions['services']){
            $allOptions['ansible']=true;
        }else{
            $allOptions['ansible']=false;
        }


        $allOptions['password'] = passgenerator();
        $allOptions['login'] = 'user_' . $params['serviceid'];

        $postData = array(
            'password2' => passgenerator(),
        );

        $allOptions['passwd'] = $postData['password2'];

        $adminlog = Capsule::table('tblconfiguration')->where('setting', 'whmcs_admin')->get();
        $adminUsername = $adminlog[0]->value;

        $passroot = localAPI('EncryptPassword', $postData,$adminUsername);
        if ($passroot['result'] == 'error'){
            return "Error adminuser. Check configure module.";
        }

        require_once("lib/ONConnect.php");
            // Record the error in WHMCS's module log.
        logModuleCall(
            'onconnector',
            __FUNCTION__,
            $params,
            $allOptions
        );

        if(!$allOptions['iops'])                                // If IOPS is null
        {
            return "Error allocating a new virtual machine template. IOPS attribute must be a positive integer value";
        }

        if(!$allOptions['cpu'])                                // If CPU is null
        {
            return "Error allocating a new virtual machine template. CPU attribute must be a positive integer value";
        }

        if(!$allOptions['ram'])                                // If RAM is null
        {
            return "Error allocating a new virtual machine template. RAM attribute must be a positive integer value";
        }

        if(!$allOptions['drive'])                                // If Drive is null
        {
            return "Error allocating a new virtual machine template. Drive attribute must be a positive integer value";
        }
        if(!($allOptions['ds_type'] == 'HDD' || $allOptions['ds_type'] == 'SSD')) // If drive type is not set
        {
            return "Error allocating a new virtual machine template. Disk type attribute must be (HDD/SSD) constant value";
        }

        if(!($allOptions['units'] == 'GB' || $allOptions['units'] == 'TB' || $allOptions['units'] == 'MB' || $allOptions['units'] == 'KB' || $allOptions['units'] == 'B')) // If units is not set
        {
            return "Error allocating a new virtual machine template. Units attribute must be (TB/GB/MB/KB/B) constant value";
        }


        $azure =  Capsule::table( 'mod_azure_servers' )->where('idproduct','=',$params['pid'])->get();
        if($azure != null){
            $allOptions['release'] = true;
            $allOptions['username'] = 'azuser';
            $allOptions['extra'] = array('type' => 'azure',
                'instance_size' => $azurename);
            logModuleCall(
                'onconnector',
                'azure',
                $params,
                $allOptions
            );
        }

        logModuleCall(
            'onconnector',
            __FUNCTION__,
            $params,
            $allOptions
        );

        $onconnect = new ONConnect( $params['serverip'],$params['serverport'] );
        $result = $onconnect->createVMwithSpecs($allOptions);

        if($result['result']['error']=='UserAllocateError')                 //If user already exists(name taken);
        {
            return "User user_$params[serviceid] already exists in ON";
        }

        if($result['result']['exeption']){                                  //If some unhandled Exeption
            return $result['result']['exeption'];
        }

        if ($result['result']['error']=='TemplateLoadError')                //If template not set
        {
            return 'error: Template load error';
        }

        if(!$result['result']){                                             //If answer is empty.
            return 'error: Error getting data from IONe';
        }

        Capsule::table( 'tblhosting' )
            ->where('id',$params['serviceid'])
            ->update(
                array (
                    'dedicatedip' => $result['result']['ip'],
                    'username'=>$root,
                    'password'=>$passroot['password'],
                    'domain'=>$result['result']['ip'],
                )
            );

        addinbd( $params['serviceid'], $allOptions['login'], $allOptions['password'], $result['result']['userid'], $result['result']['vmid']);

    }
    else {
        return 'error: Only one account for one service';
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
    $onaccaunt = Capsule::table('mod_on_user')
        ->select("vmid")
        ->where('id_service', $params['serviceid'])
        ->first();
    $vmid = $onaccaunt->vmid;

    $resultSuspend = Capsule::table('tblhostingaddons')
        ->select('id')
        ->where('hostingid', $params['serviceid'])
        ->update(['status' => 'Suspended']);

    if (ctype_digit($vmid)) {
        require_once("lib/ONConnect.php");
        $onconnect = new ONConnect($params['serverip']);
        $result = $onconnect->Suspend($vmid, $reason);
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

        $adminlog = Capsule::table('tblconfiguration')->where('setting', 'whmcs_admin')->get();
        $adminUsername = $adminlog[0]->value;

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
    if(ctype_digit($vmid)) {
        require_once("lib/ONConnect.php");
        $onconnect = new ONConnect($params['serverip']);
        $result = $onconnect->Unsuspend($vmid);
        if ($result['result']['userid']) {
            Capsule::table('mod_on_user')
                ->where('id_service', $params['serviceid'])
                ->update(
                    array(
                        'userid' => $result['result']['userid'],
                    )
                );
        }
        $result = json_encode( $result );
    }
    else {
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

    if (ctype_digit($vmid)) {
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
        if(ctype_digit($userid) && ctype_digit($vmid))
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

function onconnector_TestConnection(array $params)
{
        require_once( "lib/ONConnect.php" );
        $onconnect= new ONConnect($params['serverip']);
        $result=$onconnect->Test();
        if($result['result']=='PONG')
        {
            $success = true;
        }
        else
        {
            $success = false;
            $errorMsg = $result['result'] ;
        }

    return array(
        'success' => $success,
        'error' => $errorMsg,
    );
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
        if(ctype_digit($vmid)) {
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
        if(ctype_digit($vmid)) {
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
    $user= 'user_'.$params['serviceid'];
    $result = $onconnect->getVmByName($user);

        if($result['result']['vmid']!='none') {
            Capsule::table('mod_on_user')
                ->where('id_service', $params['serviceid'])
                ->update(
                    [
                        'loginon' => $user,
                        'vmid' => $result['result']['vmid'],
                        'userid' => $result['result']['userid']
                    ]
                );
        }
        if($result['result']['ip']!='nil') {
        Capsule::table('tblhosting')
            ->where('id',$params['serviceid'])
            ->update(
                [
                    'dedicatedip'=>$result['result']['ip']
                ]
            );
    }

}

function onconnector_buttonThreeFunction(array $params)
{

    try
    {
        $product=Capsule::table( 'tblproducts' )
            ->where('id',$params['packageid'])
            ->first();

        $c=strpos($product->name,"RIAL VDS");
        if (!empty($c)){
            $trial=true;
        }

        $onaccaunt=Capsule::table('mod_on_user')
            ->where('id_service',$params['serviceid'])
            ->first();

        $userid=$onaccaunt->userid;
        $vmid=$onaccaunt->vmid;

        if (!isset($userid) || !ctype_digit($vmid))
        {
            $adonbool=false;
            foreach (Capsule::table( 'tblhostingaddons' )
                         ->select('mod_onconfiguratorAddon.id')
                         ->where('tblhostingaddons.hostingid',$params['serviceid'])
                         ->join('mod_onconfiguratorAddon','mod_onconfiguratorAddon.Addon','=','tblhostingaddons.addonid')
                         ->max('price') as $item) {
                $addons[]=$item->id;
            }

                require_once($_SERVER['DOCUMENT_ROOT'] . '/modules/addons/oncontrol/classes/ActivateAnsible.php');
                $ansibleDB = new AutoActivateAnsible();
                $ansible = $ansibleDB->getServicesFromIds($addons);

                if ($ansible) {
                    $ansiblebool = true;
                }

            $os=Capsule::table( 'tblhostingaddons' )
                ->select('tblhostingaddons.addonid','mod_onconfigurator.templateid','mod_onconfigurator.os')
                ->where('tblhostingaddons.hostingid',$params['serviceid'])
                ->where('idtariff',$params['packageid'])
                ->join('mod_onconfigurator','mod_onconfigurator.addonid','=','tblhostingaddons.addonid')
                ->sum('mod_onconfigurator.os');

            if($os){
                if (stristr( $os->os, 'windows')!=false){
                    $root='Administrator';
                }
                else{
                    $root='root';
                }
                $temlaitid=$os->templateid;
            }

            $pass = passgenerator();
            $user = 'user_' . $params['serviceid'];

            $postData = array(
                'password2' => passgenerator(),
            );



            $passroot = localAPI('EncryptPassword', $postData);
            require_once("lib/ONConnect.php");


            $onconnect = new ONConnect( $params['serverip'],$params['serverport'] );

            $result = $onconnect->NewAccount( $user, $pass, $temlaitid, $params['configoption1'],$postData['password2'],$trial,$ansible,$ansiblebool,$params['serviceid']);

            if($result['error']==true)
            {
                return 'error: '.$result['errorMessage'];
            }
            if($result['result']['error']=='UserAllocateError')                 // If user already exists;
            {
                return "User user_$params[serviceid] already exists in IONe";
            }

            if ($result['result']['error']=='TemplateLoadError')                // If template is not set;
            {
                return 'error: Template error, check template in IONe configurator';
            }

            if(!$result['result']){                                             // If answer is empty.
                return 'error: Error getting data from IONe';
            }

            Capsule::table( 'tblhosting' )
                ->where('id',$params['serviceid'])
                ->update(
                    array (
                        'dedicatedip' => $result['result']['ip'],
                        'username'=>$root,
                        'password'=>$passroot['password'],
                        'domain'=>$result['result']['ip'],
                    )
                );

            $order=Capsule::table( 'tblhosting' )
                ->select('orderid')
                ->where('id',$params['serviceid'])
                ->first();

            addinbd( $params['serviceid'], $user, $pass, $result['result']['userid'], $result['result']['vmid']);

        }
        else
        {
            return 'error: Only one service for one account';
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

function onconnector_buttonTwoFunction(array $params)
{
    try {

        $onaccaunt=Capsule::table('mod_on_user')->where('id_service',$params['serviceid'])->first();
        $vmid=$onaccaunt->vmid;
        if(ctype_digit($vmid)) {
            require_once("lib/ONConnect.php");
            $onconnect = new ONConnect( $params['serverip'] );
            $result = $onconnect->GetIP( $vmid );
        }
        else{
            return "error: Auth data is incorrect";
        }
        Capsule::table( 'tblhosting' )
            ->where('id',$params['serviceid'])
            ->update(
                array (
                    'dedicatedip' => $result['result'],
                    'username'=>'root',
                    'password'=>'l+sxDZmZrK6Ch1AKRVwTatw1wPP4zlCJxaArkCsCkp1a',
                    'domain'=>$result['result'],
                ) );

        logModuleCall(
            'onconnector',
            __FUNCTION__,
            $params,
            $result,
            $result);
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

function onconnector_mycustomfunction($vars) {
    return array(
        'templatefile' => 'templates/mysimplepanel',
        'breadcrumb' => array(
            'stepurl.php?action=this&var=that' => 'Custom Function',
        ),
        'vars' => array(
            'test1' => 'hello',
            'test2' => 'world',
        ),
    );
}
