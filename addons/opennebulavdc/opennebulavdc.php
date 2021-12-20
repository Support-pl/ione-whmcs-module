<?php
if( !defined( "WHMCS" ) )
    die( "This file cannot be accessed directly" );

use WHMCS\Database\Capsule;

function opennebulavdc_config() {
    $configarray = array(
        "name" => "Open Nebula VDC",
        "description" => "Open Nebula VDC",
        "version" => "1",
        "author" => "support.by",
        "language"=> 'english',
    );
    return $configarray;
}

function opennebulavdc_activate()
{

    $query = "CREATE TABLE `mod_iaas_user` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `loginon` VARCHAR(64) NOT NULL,
 `passwordon` VARCHAR(64) NOT NULL,
 `userid` int(11) NOT NULL,
 `useridOn`int(11) NOT NULL,
 `id_service` int(11) NOT NULL,
 `total` VARCHAR(64) ,
 `last_pay` VARCHAR(64) ,
 `status` VARCHAR(64),
 `statusAlert` VARCHAR(64),
 `statusBalance` VARCHAR(64) ,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1";
    $result = full_query($query);
    ;


}

function opennebulavdc_deactivate($vars)
{

    return array('status'=>'success','description'=>'Module removed');
}


function opennebulavdc_output($vars)
{

    require_once ('iaasservers.php');

}

