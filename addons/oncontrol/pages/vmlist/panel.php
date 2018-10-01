<?php

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}


use WHMCS\Database\Capsule as Capsule;


if (isset($_POST['action']))
{
    require_once 'service_manager.php';
}

$system_id = $_GET['serviceId'];


$addons = Capsule::table('tbladdons')
    ->select('id', 'name', 'description')
    ->get();

$service = Capsule::table('tblproducts')
    ->select('tblproducts.name', 'tblhosting.userid', 'tblhosting.packageid', 'tblhosting.dedicatedip',
        'tblhosting.domain', 'tblhosting.id', 'server')
    ->join('tblhosting', 'tblproducts.id', '=', 'tblhosting.packageid')
    ->where('tblhosting.id', $system_id)
    ->first();

$service_addons = Capsule::table('tblhostingaddons')
    ->where('hostingid', $system_id)
    ->get();

$service_user = Capsule::table('tblclients')
    ->where('id', $service->userid)
    ->first();

$ansibles = Capsule::table('mod_onconfiguratorAddon')
    ->get();

$machineInfo = Capsule::table('mod_on_user')
    //->select('vmid')
    ->where('id_service', $system_id)
    ->first();

$server_ip = Capsule::table('tblservers')
    ->where('id', $service->server)
    ->select('ipaddress')
    ->first()->ipaddress;

$snapshotList = $this->onconnect->getSnapshotList($machineInfo->vmid);
if ($snapshotList['result']['0'] == '') {
    unset ($snapshotList['result']['0']);
}


$reinstal_settings = Capsule::table('tblproduct_upgrade_products')
    ->where('product_id', $service->packageid)
    ->select('upgrade_product_id')
    ->get();

$vm_data = $this->onconnect->getVmData($machineInfo->vmid)['result'];

$groups = [];
foreach ($addons as $addon) {
    $addon->description = json_decode($addon->description, 1);
    if (json_last_error() === JSON_ERROR_NONE && $addon->description != '') {
        $groups = add_group_to_array($groups, $addon->description['GROUP']);
    }
}

function getStatusMachine($onconnect, $vmid)
{
    $status = $onconnect->lcmStateStr($vmid);
    if ($status->result == 'LCM_INIT') {
        $status = $onconnect->stateStr($vmid);
    }
    return $status;
}

function add_group_to_array($current_array, $group)
{
    if (isset($group)) {
        if (!in_array($group, $current_array)) {
            $current_array[] = $group;
        }
    }
    return $current_array;
}


function is_checked_box($addon_name)
{
    if ($_POST[$addon_name] == 'on') {
        return 'checked';
    } else {
        return '';
    }
}

function get_color_by_status($status)
{
    switch ($status) {

        case 'RUNNING':
            return 'green';

        default :
            return 'blue';
    }
}


$wanted_groups = [];

foreach ($_POST as $wanted_group => $value) {
    if ($value == 'on' && is_string($wanted_group)) {
        $wanted_groups[] = $wanted_group;
    }
}

if (count($wanted_groups) == 0) {
    $_POST[$groups[0]] = 'on';
}

function disable_current_state($service, $status)
{
    if ($service->domainstatus == $status) {
        echo 'disabled';
    }
}

$status = getStatusMachine($this->onconnect, $machineInfo->vmid);


switch ($status['result']) {
    case 'RUNNING':
        $suspendButtonTitle = 'Suspend';
        $suspendButtonValue = 'suspend';
        $suspendButtonGlyph = 'glyphicon-pause';
        $suspendButtonType = 'warning';
        break;

    default :
        $suspendButtonTitle = 'Resume';
        $suspendButtonValue = 'unsuspend';
        $suspendButtonGlyph = 'glyphicon-play';
        $suspendButtonType = 'success';
        if ($status['result'] == 'SAVE_SUSPEND' or $status['result'] == 'BOOT_SUSPENDED') {
            $suspendButtondDisable = 'disabled';
        }
        break;

}


switch ($status['result']) {
    case 'RUNNING':
        $powerButtonTitle = 'Power off';
        $powerButtonValue = 'shutdown';
        $powerButtonGlyph = 'glyphicon-off';
        $powerButtonType = 'danger';
        break;

    default :
        $powerButtonTitle = 'Power on';
        $powerButtonValue = 'shutdown';
        $powerButtonGlyph = 'glyphicon-off';
        $powerButtonType = 'success';
        $powerButtonDisable = 'disabled';
        break;
}


// REBOOT BUTTON PROPERTIES
switch ($status['result']) {
    case 'RUNNING':
        break;

    default:
        $rebootButtonDisable = 'disabled';
        break;
}


switch ($status['result']) {
    case 'RUNNING':
        break;

    default:
        $refreshButtonDisable = 'disabled';
        break;
}

$isCheckAnsible=Capsule::table( 'tblconfiguration' )->where('setting','ione_use_ansible')->get();
$LANG=$this->vars['_lang'];
$cloudlink = Capsule::table('tblconfiguration')
    ->select('value')->where('setting',ione_address)->get();
?>

<style>
    .jumbotron {
        padding: 15px;
    }
</style>

<div class="container-fluid ">
    <div class="jumbotron">
        <div class="row ">

            <form method="POST">
                <input type="hidden" name="serviceId" value="<?php echo $system_id ?>"> <input type="hidden"
                                                                                               name="serverId" value="<?php echo $service->server; ?>"> <input type="hidden" name="groupid"
                                                                                                                                                               value="<?php echo $service_user->groupid; ?>">

                <div class="col-sm-8">
                    <div class="col-sm-12">
                        <div class="col-sm-6">
                            <div class="col-sm-12">
                                <b><?=$LANG['thId']?>:</b> <?php echo $service->id; ?>
                            </div>
                            <div class="col-sm-12">
                                <b><?=$LANG['Status']?>:</b> <b id="state"
                                                   style="font-size: 110%; border: 1px solid <? echo get_color_by_status($status['result']) ?>; color: <? echo get_color_by_status($status['result']); ?>;">
                                    <?= $vm_data['STATE'] ?>
                                </b>
                            </div>
                            <div class="col-sm-12">
                                <b><?=$LANG['IP']?>:</b> <?= $vm_data['IP'] ?>
                            </div>
                            <div class="col-sm-12">
                                <b><?=$LANG['VMID']?></b> <a
                                    href="<?=$cloudlink[0]->value?>/#vms-tab/<?= $machineInfo->vmid ?>"><?= $machineInfo->vmid ?></a>
                            </div>
                            <div class="col-sm-12">
                                <b><?=$LANG['PanelHeading']?>:</b><?= $vm_data['HOST']; ?>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="col-sm-12">
                                <b><?=$LANG['client']?>:</b> <a
                                    href="/admin/clientsservices.php?userid=<?= $service_user->id ?>&id=<?= $system_id ?>">
                                    <?= $service_user->firstname . ' ' . $service_user->lastname ?>
                                </a>
                            </div>
                            <div class="col-sm-12">
                                <b><?=$LANG['tariff']?>:</b> <?= $service->name ?>
                            </div>
                            <div class="col-sm-12">
                                <b><?=$LANG['vcpu']?>:</b> <?= $vm_data['CPU']; ?>
                            </div>
                            <div class="col-sm-12">
                                <b><?=$LANG['ram']?>:</b> <?= $vm_data['RAM']; ?>MB
                            </div>
                            <div class="col-sm-12">
                                <b><?=$LANG['import']?>:</b> <?= $vm_data['IMPORTED'] ?>
                            </div>
                        </div>
                        <div class="col-sm-12">

                            <!-- STATE MONITORING -->
                            <script>
                                $(document).ready(function () {

                                    function buttonsByStateChange(state) {
                                        switch (state) {
                                            case 'RUNNING':
                                                $('#suspendButton').attr({
                                                    'value': 'suspend',
                                                    'glyph': 'glyphicon-pause',
                                                    'class': 'btn btn-warning',
                                                    'disabled': false
                                                });
                                                $('#suspendButton').text('Suspend');

                                                $('#powerButton').attr({
                                                    'disabled': false
                                                });

                                                $('#rebootButton').attr({
                                                    'disabled': false
                                                });

                                                $('#refreshButton').attr({
                                                    'disabled': false
                                                });

                                                $('button[data-toogle="collapse"').attr('disabled', false);
                                                $('button[data-toogle="collapse"').text('Snapshots(opens)');

                                                break;

                                            case 'SUSPENDED' :
                                                $('#suspendButton').attr({
                                                    'disabled': false
                                                });
                                                break;

                                            default:
                                                $('#suspendButton').attr({
                                                    'text': 'Resume',
                                                    'value': 'unsuspend',
                                                    'glyph': 'glyphicon-play',
                                                    'class': 'btn btn-success',
                                                    'disabled': true
                                                });
                                                break;
                                        }
                                    }

                                    stateChecker = setInterval(function () {

                                        state = $('#state').text().trim();
                                        if (state != 'RUNNING' &&
                                            state != 'FAIL' &&
                                            state != 'SUSPENDED' &&
                                            state != 'POWEROFF') {

                                            $.ajax({
                                                url: "addonmodules.php",
                                                data: {
                                                    module: 'tabl',
                                                    mod: 'currentState',
                                                    vmid: '<?=$machineInfo->vmid;?>'
                                                }
                                            }).done(function (data) {

                                                var re = new RegExp("<div id=\"state\">.*?</div>");

                                                stateStr = re.exec(data)[0];

                                                stateStr = stateStr.replace("<div id=\"state\">", "");
                                                stateStr = stateStr.replace("<\/div>", "");

                                                state = JSON.parse(stateStr);

                                                console.log(state.state);

                                                $('#state').text(state.state);
                                                buttonsByStateChange(state.state);

                                            });
                                        } else {
                                            clearInterval(stateChecker);
                                        }
                                    }, 5000);

                                });
                            </script>
                            <div class="col-md-3 col-sm-6 text-center" style="margin-top: 20px;">
                                <button id="suspendButton" <?= $suspendButtonDisable; ?>
                                        type="submit" name="action" value="<?= $suspendButtonValue; ?>"
                                        class="btn btn-<?= $suspendButtonType ?>"><?= $suspendButtonTitle; ?>
                                    <span class="glyphicon <?= $suspendButtonGlyph; ?>"></span>
                                </button>
                            </div>
                            <div class="col-md-3 col-sm-6 text-center" style="margin-top: 20px;">
                                <button id="powerButton" <?= $powerButtonDisable; ?> type="submit" name="action"
                                        value="<?= $powerButtonValue; ?>"
                                        class="btn btn-<?= $powerButtonType ?> <?php disable_current_state($service,
                                            'Active'); ?>"><?= $powerButtonTitle; ?> <span
                                        class="glyphicon <?= $powerButtonGlyph; ?>"></span>
                                </button>
                            </div>

                            <div class="col-md-3 col-sm-6 text-center" style="margin-top: 20px;">
                                <button id="rebootButton" <?= $rebootButtonDisable; ?> type="submit" name="action"
                                        value="reboot" class="btn btn-info">
                                    <?=$LANG['reboot']?> <span class="glyphicon glyphicon-repeat"></span>
                                </button>
                            </div>
                            <div id="refreshButton" class="col-md-3 col-sm-6 text-center" style="margin-top: 20px;">
                                <button <?= $refreshButtonDisable; ?> type="submit" name="action" value="reset"
                                                                      class="btn btn-info">
                                    <?=$LANG['reset']?> <span class="glyphicon glyphicon-refresh"></span>
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </form>
            <div class="col-sm-4">

                <form action="" method="POST"></form>

                <form action="<?= $this->modulelink."&mod=reinstall&serviceId=" . $system_id ?>" method="POST">
                    <div class="col-sm-12 text-center" style="margin-top: 20px;">
                        <button class="btn btn-block btn-default"><?=$LANG['reinstall']?></button>
                    </div>
                </form>

                <form action="">
                    <div class="col-sm-12 text-center" style="margin-top: 20px;">
                        <button class="btn btn-block btn-default"><?=$LANG['restore']?></button>
                    </div>
                </form>

                <div class="col-sm-12 text-center" style="margin-top: 20px;">
                    <a id="terminate" class="btn btn-block btn-danger"><?=$LANG['terminate']?></a>
                </div>

                <div class="col-sm-12 text-center" style="margin-top: 20px;">
                    <button <?= $vm_data['STATE'] != 'RUNNING' ? 'disabled' : ''; ?>
                        data-toggle="collapse" data-target="#collapse1" class="btn btn-block btn-default">
                        Snapshots(<?= $vm_data['STATE'] != 'RUNNING' ? ''.$LANG["tariff"].'' : ''; ?><?=$LANG['open']?>)
                    </button>

                    <div id="collapse1" class="collapse">
                        <ul class="list-group">
                            <?php foreach ($snapshotList['result'] as $snapshot): ?>
                                <form action="" method="POST">
                                    <li class="list-group-item"><?= $snapshot['NAME'] ?>: <?= date('H:i:s Y/m/d',
                                            $snapshot['TIME']) ?> :
                                        <button type="submit" name="action" value="revertSnapshot"
                                                class="btn btn-default btn-xs">
                                            <span class="fa fa-mail-reply"></span> <?=$LANG['revert']?>
                                        </button>
                                        <button type="submit" name="action" value="deleteSnapshot"
                                                class="btn btn-danger btn-xs">
                                            <span class="glyphicon glyphicon-remove"></span> <?=$LANG['buttonDelete']?>
                                        </button>
                                        <input type="hidden" name="snapshotId" value="<?= $snapshot['SNAPSHOT_ID'] ?>">
                                        <input type="hidden" name="serviceId" value="<?= $system_id ?>">
                                    </li>
                                </form>
                            <?php endforeach; ?>
                            <button rel="createSnapshotPopup" name="createSnapshot[<?= $machineInfo->vmid ?>]"
                                    class="btn-block btn-info showCreateSnapshot"><?=$LANG['createsnap']?>
                            </button>
                            <div class="overlay_popup" style="
									display: none;
									position: fixed;
									z-index: 999;
									top: 0;
									right: 0;
									bottom: 0;
									left: 0;
									background: black;
									opacity: 0.7;
									transition: opacity: .5s;
								"></div>

                            <div class="popup" id="createSnapshotPopup" style="
								display: none;
								z-index: 1000;
								position: fixed;
								top: 40%;
								right: 0;
								bottom: 0;
								left: 30%;
								width: 40%;
								max-height: 20%;
								background: white;
								border 1px solid black;
								border-radius: 5px;
								padding: 10px;
										">
                                <form class="form" action="" method="POST">
                                    <div class="form-group">
                                        <label for="snapshotNameInput" style="color: black;"><?=$LANG['name']?>:</label> <input
                                            id="snapshotNameInput" class="form-control" type="text" name="snapshotName"
                                            placeholder="<?=$LANG['name']?>" style=" width: 100%"> <input type="hidden" name="serviceId"
                                                                                           value="<?php echo $system_id ?>"> <input type="hidden" name="serverId"
                                                                                                                                    value="<?php echo $service->server; ?>"> <input type="hidden" name="groupid"
                                                                                                                                                                                    value="<?php echo $service_user->groupid; ?>">
                                    </div>

                                    <button class="btn btn-block btn-primary" type="submit" name="action"
                                            value="MKSnapshot"><?=$LANG['create']?>
                                    </button>
                                </form>
                            </div>
                            <script type="text/javascript">
                                $(document).ready(function () {
                                    $('.showCreateSnapshot').on('click', function () {
                                        var popup_id = $('#' + $(this).attr('rel'));
                                        $(popup_id).show();
                                        $('.overlay_popup').show();
                                    });
                                    $('.overlay_popup').on('click', function () {
                                        $('.overlay_popup, .popup').hide();
                                    });
                                });

                            </script>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?if($isCheckAnsible[0]->value=='on'):?>
    <div class="jumbotron">
        <div class="row">

            <div class="col-sm-12">
                <h2 class="text-center"><?=$LANG['tabsansible']?>:</h2>
                <div class="form-check">
                    <form id="ansiblesForm" action="/admin/addonmodules.php" method="GET">
                        <?php $i = 0; ?>
                        <?php foreach ($ansibles as $ansible): ?>
                            <div class="col-sm-4">
                                <input type="checkbox" name="ansibles[]" value="<?php echo $ansible->id; ?>"
                                       id="<?php echo $ansible->name; ?>"><label
                                    for="<?php echo $ansible->name; ?>">&#160;<?php echo $ansible->name; ?></label><br>
                            </div>
                            <?php $i++;
                        endforeach; ?>
                        <input type="hidden" name="serviceId" value="<?php echo $system_id; ?>">
                        <div class="col-sm-12 text-center">
                            <input type="hidden" name="module" value="oncontrol">
                            <input type="hidden" name="tabs" value="ansible">
                            <input type="hidden" name="mod" value="ansibledb">
                            <input type="hidden" name="action" value="addAnsibleOnExternalLink">
                            <input type="hidden" name="idSystem" value="<?php echo $system_id; ?>">
                            <button class="btn btn-success" style="width: 180px; margin-top: 20px;"><?=$LANG['activate']?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?endif;?>



    <script type="text/javascript">
        $(document).ready(function () {
            $("select[name='addedAddons[]']").on('change', function (e) {
                var optionSelectedText = $("option:selected", this).text();
                var optionSelectedGroup = $("option:selected", this).attr('group');

                $("#selectedAddonsList li[group='" + optionSelectedGroup + "']").remove();

                if (optionSelectedText != '') {
                    $('#selectedAddonsList').append(
                        "<li group=\"" + optionSelectedGroup + "\" class=\"list-group-item\">" + optionSelectedText + "</li>"
                    )
                    ;
                }
            });


            $("a[href=\"#backup\"]").on("click", function () {
                $('#selectbackup').selectmenu("open");
            });
        });
    </script>

</div>