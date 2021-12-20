<?php if( !defined( "WHMCS" ) )
    die( "This file cannot be accessed directly" );
use WHMCS\Database\Capsule;


function addOrUpdate($name,$value){
    $date = Capsule::table( 'tblconfiguration' )
        ->where('setting',$name)
        ->get();
    if($date[0] != NULL){
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





if($_POST['save'] == 'save') {
    if ($_POST['address']) {
        addOrUpdate('ione_address', $_POST['address']);
    }

    if ($_POST['admin']) {
        addOrUpdate('whmcs_admin', $_POST['admin']);
    }

    if ($_POST['customfield']) {
        addOrUpdate('customfield', $_POST['customfield']);
    }

    if ($_POST['groups']) {
        $strGroup = implode($_POST['groups'], ',');
        addOrUpdate('vmlist_config_groups', $strGroup);
    }
    if ($_POST['deletedate']) {
        addOrUpdate('ione_delete', $_POST['deletedate']);
    } else {
        addOrUpdate('ione_delete', 'off');
    }

    if ($_POST['login']) {
        addOrUpdate('ione_config_login', $_POST['login']);
    }
    if ($_POST['passwd']) {
        addOrUpdate('ione_config_passwd', $_POST['passwd']);
    }

}

    if($_POST['testconnect'] == true){
    require_once($_SERVER['DOCUMENT_ROOT'] . '/modules/servers/onconnector/lib/ONConnect.php');
    $onconnect = new ONConnect();
    $dataON = $onconnect->Test();
    if($dataON->response == 'PONG'){
        ?>
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="form-group">
                    <label style="color:green;"><?=$this->LANG['connecttrue']?></label>
                </div>
            </div>
        </div>
        <?php
    }else{
        ?>
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="form-group">
                    <label style="color:red;"><?=$this->LANG['connectfalse']?></label>
                </div>
            </div>
        </div>
        <?php
        $connect = false;
    }
}


    $address = Capsule::table('tblconfiguration')->where('setting', 'ione_address')->get();
    $admin = Capsule::table('tblconfiguration')->where('setting', 'whmcs_admin')->get();
    $custom = Capsule::table('tblconfiguration')->where('setting', 'customfield')->get();
    $deletedate = Capsule::table('tblconfiguration')->where('setting', 'ione_delete')->get();
    $login = Capsule::table('tblconfiguration')->where('setting', 'ione_config_login')->get();
    $passwd = Capsule::table('tblconfiguration')->where('setting', 'ione_config_passwd')->get();


    $descriptions = Capsule::table('tbladdons')
        ->select('description')
        ->where('description', 'like', '%"GROUP"%')
        ->pluck('description');
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
                <label for="custom"><?=$this->LANG['customfield']?></label>
                <input id="custom" class="form-control" type="text" name="customfield" value="<?=$custom[0]->value?>">
            </div>
            <div class="form-group">
                <label for="login">ON Login</label>
                <input id="login" class="form-control" type="text" name="login" value="<?=$login[0]->value?>">
            </div>
            <div class="form-group">
                <label for="passwd">ON Passwrod</label>
                <input id="passwd" class="form-control" type="password" name="passwd" value="<?=$passwd[0]->value?>">
            </div>
    </div>
</div>


<div class="panel panel-default">
    <div class="panel-body">
        <table class="table table-striped table-bordered table-hover">
            <input id="deletedate" type="checkbox" name="deletedate" <?php if($deletedate[0]->value=='on'){print 'checked';}?>>
            <label for="deletedate"><?=$this->LANG['deleteall']?>: </label>
        </table>
            <button class="form-control" type="submit" name="save" value="save"><?=$this->LANG['buttonOnconSubmit']?></button>
        </form>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-body">
        <div class="form-group">
            <form method="post" name="test">
                <label for="host"><?=$this->LANG['testconnect']?></label>
                <button id="testconnect" class="form-control" type="submit" name="testconnect" value="true"><?=$this->LANG['сheck']?></button>
            </form>
        </div>
    </div>
</div>


</div>

