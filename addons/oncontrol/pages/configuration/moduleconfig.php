<?php if( !defined( "WHMCS" ) )
    die( "This file cannot be accessed directly" );
use WHMCS\Database\Capsule;


function addOrUpdate($name,$value){
    if(Capsule::table( 'tblconfiguration' )
        ->where('setting',$name)
        ->get()) {
        Capsule::table( 'tblconfiguration' )
            ->where('setting',$name)
            ->update([
                'setting'=>$name,
                'value'=>$value
            ]);
    }
    else{
        $result = Capsule::table('tblconfiguration')
            ->insert([
                'setting' => $name,
                'value' => $value
            ]);
    };
}


    if ($_POST['save'] == 'save') {
        if ($_POST['host']) {
            $this->addOrUpdate('ione_config_host', $_POST['host']);
        }

        if ($_POST['port']) {
            $this->addOrUpdate('ione_config_port', $_POST['port']);
        }

        if ($_POST['address']) {
            $this->addOrUpdate('ione_address', $_POST['address']);
        }

        if ($_POST['admin']) {
            $this->addOrUpdate('whmcs_admin', $_POST['admin']);
        }

        if ($_POST['customfield']) {
            $this->addOrUpdate('customfield', $_POST['customfield']);
        }

        if ($_POST['iaas_group']) {
            $this->addOrUpdate('ione_iaas_group', $_POST['iaas_group']);
        }

        if ($_POST['ansiblePort']) {
            $this->addOrUpdate('ansibledb_config_port', $_POST['ansiblePort']);
        }

        if ($_POST['groups']) {
            $strGroup = implode($_POST['groups'], ',');
            $this->addOrUpdate('vmlist_config_groups', $strGroup);
        }
        if ($_POST['useAnsible']) {
            $this->addOrUpdate('ione_use_ansible', $_POST['useAnsible']);
        } else {
            $this->addOrUpdate('ione_use_ansible', 'off');
        }
        if ($_POST['deletedate']) {
            $this->addOrUpdate('ione_delete', $_POST['deletedate']);
        } else {
            $this->addOrUpdate('ione_delete', 'off');
        }

}

    $host = Capsule::table('tblconfiguration')->where('setting', 'ione_config_host')->get();
    $port = Capsule::table('tblconfiguration')->where('setting', 'ione_config_port')->get();
    $address = Capsule::table('tblconfiguration')->where('setting', 'ione_address')->get();
    $admin = Capsule::table('tblconfiguration')->where('setting', 'whmcs_admin')->get();
    $custom = Capsule::table('tblconfiguration')->where('setting', 'customfield')->get();
    $portAnsible = Capsule::table('tblconfiguration')->where('setting', 'ansibledb_config_port')->get();
    $isCheckAnsible = Capsule::table('tblconfiguration')->where('setting', 'ione_use_ansible')->get();
    $deletedate = Capsule::table('tblconfiguration')->where('setting', 'ione_delete')->get();
    $iaas = Capsule::table('tblconfiguration')->where('setting', 'ione_iaas_group')->get();


    $descriptions = Capsule::table('tbladdons')
        ->select('description')
        ->where('description', 'like', '%"GROUP"%')
        ->lists('description');
    $groups = [];

    foreach ($descriptions as $description) {
        $jsn = json_decode($description);
        if (!in_array($jsn->GROUP, $groups)) {
            $groups[] = $jsn->GROUP;
        }
    }

    $currentGroups = Capsule::table('tblconfiguration')
        ->where('setting', 'vmlist_config_groups')
        ->pluck('value');
    $currentGroups = explode(',', $currentGroups);

?>

<div class="panel panel-default">
    <div class="panel-body">
        <form method="post" name="save">
            <div class="form-group">
                <label for="host"><?=$this->LANG['whmcsadmin']?></label>
                <input id="host" class="form-control" type="text" name="admin" value="<?=$admin[0]->value?>">
            </div>
            <div class="form-group">
                <label for="host"><?=$this->LANG['PanelAddress']?></label>
                <input id="host" class="form-control" type="text" name="address" value="<?=$address[0]->value?>">
            </div>
            <div class="form-group">
                <label for="host"><?=$this->LANG['PanelIP']?></label>
                <input id="host" class="form-control" type="text" name="host" value="<?=$host[0]->value?>">
            </div>
            <div class="form-group">
                <label for="port"><?=$this->LANG['PanelPort']?></label>
                <input id="port" class="form-control" type="text" name="port" value="<?=$port[0]->value?>">
            </div>
            <div class="form-group">
                <label for="port"><?=$this->LANG['customfield']?></label>
                <input id="port" class="form-control" type="text" name="customfield" value="<?=$custom[0]->value?>">
            </div>
            <div class="form-group">
                <label for="iaas_group">ID группы iaas пользователей в ON</label>
                <input id="iaas_group" class="form-control" type="text" name="iaas_group" value="<?=$iaas[0]->value?>">
            </div>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-body">
            <div class="form-group">
                <label for="ansiblePort"><?=$this->LANG['defaultPortAnsible']?></label>
                <input id="ansiblePort" class="form-control" type="text" name="ansiblePort" value="<?=$portAnsible[0]->value?>">
            </div>
    </div>
</div>


<div class="panel panel-default">
    <div class="panel-body">
            <table class="table table-striped table-bordered table-hover">
                <input id="useAnsibleCheckbox" type="checkbox" name="useAnsible" <?if($isCheckAnsible[0]->value=='on'){print 'checked';}?>>
                <label for="useAnsibleCheckbox"><?=$this->LANG['ansibledbuse']?>: </label>
            </table>
        <table class="table table-striped table-bordered table-hover">
            <input id="deletedate" type="checkbox" name="deletedate" <?if($deletedate[0]->value=='on'){print 'checked';}?>>
            <label for="deletedate"><?=$this->LANG['deleteall']?>: </label>
        </table>
            <button class="form-control" type="submit" name="save" value="save"><?=$this->LANG['buttonOnconSubmit']?></button>
        </form>
    </div>
</div>


</div>

