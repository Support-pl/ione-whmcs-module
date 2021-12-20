<?php if( !defined( "WHMCS" ) )
    die( "This file cannot be accessed directly" );
use WHMCS\Database\Capsule;


set_time_limit(0);

require_once($_SERVER['DOCUMENT_ROOT'] . '/modules/servers/onconnector/lib/ONConnect.php');

if(isset($_POST['email'])){
    $te = Capsule::table( 'tblconfiguration' )
        ->where('setting','email_iaas')
        ->get();
    if($te[0] != NULL) {
        Capsule::table( 'tblconfiguration' )
            ->where('setting','email_iaas')
            ->update([
                'setting'=>'email_iaas',
                'value'=>$_POST['email']['0']
            ]);
    }else{

        $result = Capsule::table('tblconfiguration')
            ->insert([
                'setting'=>'email_iaas',
                'value'=>$_POST['email']['0']
            ]);
    };
}

if(isset($_POST['id_group'])) {
    $gr = Capsule::table('tblconfiguration')
        ->where('setting', 'id_group_iaas')
        ->get();
    if ($gr[0] != NULL) {
        Capsule::table('tblconfiguration')
            ->where('setting', 'id_group_iaas')
            ->update([
                'setting' => 'id_group_iaas',
                'value' => $_POST['id_group']
            ]);
    } else {

        $result = Capsule::table('tblconfiguration')
            ->insert([
                'setting' => 'id_group_iaas',
                'value' => $_POST['id_group']
            ]);
    }
}


if(isset($_POST['write_off'])){
    $mo = Capsule::table( 'tblconfiguration' )
        ->where('setting','write_off')
        ->get();
    if($mo[0] != NULL) {
        Capsule::table( 'tblconfiguration' )
            ->where('setting','write_off')
            ->update([
                'setting'=>'write_off',
                'value'=>$_POST['write_off']
            ]);
    }else{
        $result = Capsule::table('tblconfiguration')
            ->insert([
                'setting'=>'write_off',
                'value'=>$_POST['write_off']
            ]);
    };
}

?>

<?php $iaas_write_off = Capsule::table('tblconfiguration')->where('setting','write_off')->get(); ?>

<form method="post">
    Minimum charge
    <input type="text" name="write_off" value="<?=$iaas_write_off[0]->value?>" pattern="[0-9]{1,}">
    <input type="submit" value="Save">
</form>

<?php

$iaas_id_group = Capsule::table('tblconfiguration')->where('setting','id_group_iaas')->get();

?>

<form method="post">
    ID IaaS user ON
    <input type="text" name="id_group" value="<?=$iaas_id_group[0]->value?>" pattern="[0-9]{1,}">
    <input type="submit" value="Save">
</form>

<?php

$command = 'GetEmailTemplates';
$postData = array(
    'type' => 'general',
);

$email_templates = localAPI($command, $postData);
$iaas_email = Capsule::table('tblconfiguration')->where('setting','email_iaas')->get();
?>
<form method="post">
    Choose a letter to alert balance
    <select type="select" name="email[]">
        <option>Choose a letter</option>
        <?php
        foreach ($email_templates['emailtemplates']['emailtemplate'] as $email_template) {
            if($iaas_email[0]->value == $email_template["name"]) {
                echo '<option value="'.$email_template["name"].'" selected>'.$email_template["name"].'</option>';
            }else{
                echo '<option value="'.$email_template["name"].'">'.$email_template["name"].'</option>';
            }
        }
        ?>
    </select>
    <input type="submit" value="Save">
</form>


