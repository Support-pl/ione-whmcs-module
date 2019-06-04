<?php
if (!defined("WHMCS"))
    die("This file cannot be accessed directly");
use WHMCS\Database\Capsule;
ini_set('display_errors', 0);



function firstOrUpdate($hostingId,$addonid)
{
    if(Capsule::table( 'tblhostingaddons' )
        ->where('hostingid', $hostingId)
        ->where('addonid', $addonid)
        ->first()){

        return true;
    }
    else {
         $attachedHosing = Capsule::table('tblhosting')
             ->select('orderid','nextduedate')
             ->where('id',$hostingId)
             ->first();
         $date=date('Y-m-d');
         Capsule::table( 'tblhostingaddons' )
            ->insert(
            [
                'hostingid'=>$hostingId,
                'addonid'=>$addonid,
                'orderid'=>$attachedHosing->orderid,
                'status'=>'Active',
                'nextduedate'=>$date,
                'billingcycle'=>'Free Account',
                'nextinvoicedate'=>$date,
                'regdate'=>$date]
            );
    }
}
    require_once ($_SERVER['DOCUMENT_ROOT'] . '/modules/addons/oncontrol/classes/AnsibleFacade.php');
    $ansibleFacade= new AnsibleFacade($this->vars);
    $mod=$_GET["action"];
    switch ($mod) {
        case "addAnsibleOnExternalLink":
            $ansibleFacade->addAnsibleOnExternalLink();
            break;
        case "reinstall":
            $ansibleFacade->reinstallComoareInfo();
            break;
        case "fullReinstall":
            $ansibleFacade->fullReinstall();
            break;
        case "add":
            $ansibleFacade->add();
            break;
        case "allTable":
            $ansibleFacade->allTable();
            break;
        case "activation":
            $ansibleFacade->activation();
            break;
        case "change":
            $ansibleFacade->change();
           // require_once('getVariablesBlock.php');
            break;
        case "fullActivation":
            $ansibleFacade->fullActivation();
            break;
        default:
            require_once('allansible.php');
    }

