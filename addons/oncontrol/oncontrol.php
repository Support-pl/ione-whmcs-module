<?php
/*if( !defined( "WHMCS" ) )
    die( "This file cannot be accessed directly" );*/

use WHMCS\Database\Capsule;


function oncontrol_config() {
    $configarray = array(
        "name" => "Open Nebula Control",
        "description" => "Open Nebula Control",
        "version" => "0.5",
        "author" => "support.by",
        "language"=> 'english'
        );
    return $configarray;
}


function oncontrol_activate()
{
    $query = "CREATE TABLE `mod_iOne_vmlist_cache` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `vmid` int(11) NOT NULL,
 `userid` int(11) NOT NULL,
 `host` VARCHAR(64) NOT NULL,
 `login` VARCHAR(64) NOT NULL,
 `ip` VARCHAR(64) NOT NULL,
 `state` VARCHAR(64) NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1";
    $result = full_query($query);

    $query = "CREATE TABLE `mod_onconfiguratorOS` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `templateid` int(11) NOT NULL,
      `addonid` int(11) NOT NULL,
      `description` varchar(1024),
      PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1";
    $result = full_query($query);

    $query = "CREATE TABLE `mod_on_user` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `loginon` VARCHAR(64) NOT NULL,
 `passwordon` VARCHAR(64) NOT NULL,
 `userid` int(11) NOT NULL,
 `useridOn`int(11) NOT NULL,
 `vmid` int(11) NOT NULL,
 `id_service` int(11) NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1";
    $result = full_query($query);

    $query = "CREATE TABLE `mod_onconfigurator` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `idtariff` int(11) NOT NULL,
 `os` VARCHAR(64) NOT NULL,
 `addonid` int(11) NOT NULL,
 `templateid`int(11) NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1";
    $result = full_query($query);


}

function oncontrol_deactivate($vars)
{
    $deletedate = Capsule::table('tblconfiguration')->where('setting', 'ione_delete')->get();
    if ($deletedate[0]->value == 'on'){
        $query = "DROP TABLE `mod_onconfiguratorOS`";
        $result = full_query($query);

        $query = "DROP TABLE `mod_on_user`";
        $result = full_query($query);

        $query = "DROP TABLE `mod_iOne_vmlist_cache`";
        $result = full_query($query);

        $query = "DROP TABLE `mod_onconfigurator`";
        $result = full_query($query);

        return array('status'=>'success','description'=>'All data is deleted');
    }else{
        return array('status'=>'success','description'=>'Module removed');
    }
}


function oncontrol_output($vars)
{
    require_once ('classes/Loader.php');
    $loader = new Loader($vars);
    $host = Capsule::table('tblconfiguration')->where('setting', 'ione_config_host')->get();
    $port = Capsule::table('tblconfiguration')->where('setting', 'ione_config_port')->get();
    $address = Capsule::table('tblconfiguration')->where('setting', 'ione_address')->get();
    if ($host == NULL || $port == NULL || $address == NULL){
        $loader->constructPageFirstStart();
    }else{
    $loader->constructPage();
        }
}

