<?php

use WHMCS\Database\Capsule as Capsule;

if (isset($_POST['action'])) {

	extract($_POST);
	require_once($_SERVER['DOCUMENT_ROOT'] . '/modules/servers/onconnector/lib/ONConnect.php');

	$vm_data = Capsule::table('mod_on_user')
		->select('vmid')
		->where('id_service', $serviceId)
		->first();


	$server_ip = Capsule::table('tblservers')
		->where('id', $serverId)
		->select('ipaddress')
		->first()->ipaddress;

	$on_connect = new ONConnect($server_ip);


	switch ($action) {
	    
		case 'suspend':
			$technicalMessage = ('Suspending.');

            $command = 'ModuleSuspend';
            $postData = array(
                'accountid' => $serviceId,
                'suspendreason' => 'force',
            );
            $results = localAPI($command, $postData);
            //print_r($results);
			break;

		case 'unsuspend':
			$technicalMessage = ('Unsuspending.');
            $command = 'ModuleUnsuspend';
            $postData = array(
                'accountid' => $serviceId,
            );
            $results = localAPI($command, $postData);
            //print_r($results);
			break;

		case 'reset':
			$on_connect->Reboot($vm_data->vmid, TRUE);
			$technicalMessage = ('Resetting.');
			break;

		case 'reboot':
			$on_connect->Reboot($vm_data->vmid, FALSE);
			$technicalMessage = ('Rebooting.');
			break;

		case 'shutdown':
			$on_connect->Shutdown($vm_data->vmid);
			$technicalMessage = ('Rebooting.');
			break;

		case 'MKSnapshot':
			$on_connect->MKSnapshot($vm_data->vmid, $snapshotName);
			$technicalMessage = 'Snapshot creating, wait a bit';
			break;

		case 'revertSnapshot':
			$on_connect->RevSnapshot($vm_data->vmid, $snapshotId);
			$technicalMessage = 'Snapshot revert, wait a bit';
			break;

		case 'deleteSnapshot':
			$on_connect->RMSnapshot($vm_data->vmid, $snapshotId);
			$technicalMessage = 'Snapshot  deleting, wait a bit';
			break;
		
	}

}



if(isset($technicalMessage)) : ?>

	<div class="successbox">
		<strong class="title"><?=$technicalMessage?></strong>
	</div>

	<?endif;



if (isset($message)): ?>
	<div class="col-sm-12">
		<div class="panel panel-success">
			<div class="panel-heading">
				<h2><?=$message; ?></h2>
			</div>
		</div>
	</div>
<? endif;