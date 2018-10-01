<?php
if( !defined( "WHMCS" ) )
    die( "This file cannot be accessed directly" );
use WHMCS\Database\Capsule;

function onconfigurator_errorMessage($bigText,$allText){
    printf('<div class="errorbox">
<strong class="title">%s</strong><br>
%s</div>',$bigText,$allText);
}

function onconfigurator_successMessage($bigText,$allText){
    printf('<div class="successbox">
<strong class="title">%s</strong><br>%s
</div>',$bigText,$allText);
}


if($_POST['action']){
    $action=key($_POST['action']);
    switch ($action){
        case 'add':
            $result=Capsule::table( 'mod_onconfiguratorOS' )
                ->insert([
                    'addonid'=>$_POST['osadd'],
                    'templateid'=>$_POST['templateid'],
                    'description'=>$_POST['descriptions'],
                ]);
            if ($result){
                onconfigurator_successMessage($this->LANG['dataadd'].' ',$this->LANG['dataaddbd']);
            }else{
                onconfigurator_errorMessage($this->LANG['Error'].' ',$this->LANG['faildb']);
            }
            break;
        case 'edit':
            $result=Capsule::table( 'mod_onconfiguratorOS' )
                ->where('id',$_POST['id'])
                ->update([
                    'addonid'=>$_POST['osadd'],
                    'templateid'=>$_POST['templateid'],
                    'description'=>$_POST['descriptions'],
                ]);
            if ($result){
                onconfigurator_successMessage($this->LANG['updatedate'].' ',$this->LANG['updatedatedb']);
            }else{
                onconfigurator_errorMessage($this->LANG['error'].' ',$this->LANG['errordb']);
            }
            break;
        case 'delete':
            foreach ($_POST['check'] as $key=>$value){
                $check[]=$key;
            }
            $result=Capsule::table( 'mod_onconfiguratorOS' )
                ->whereIn('id',$check)
                ->delete();
            if ($result){
                onconfigurator_successMessage($this->LANG['datadel'].' ',$this->LANG['datadeldb']);
            }else{
                onconfigurator_errorMessage($this->LANG['error'].' ',$this->LANG['deldb']);
            }
            break;
    }
}
$tariffs=Capsule::table('mod_onconfiguratorOS')->get();
?>

<script>
    var tariffs=<?=json_encode($tariffs,true)?>;

    $(document).ready(
        function () {

            dialog = $("#edit-options").dialog({
                autoOpen: false,
                modal: true,
                width: 900
            });

            $("input:checkbox").bind("change click", function () {
                checkbox = $('input:checked');
                if(checkbox.length==1){
                    $('#editButton').prop("disabled",false);
                }else{
                    $('#editButton').prop("disabled",true);
                }
                if(checkbox.length>0){
                    $('#deleteButton').prop("disabled",false);
                }else{
                    $('#deleteButton').prop("disabled",true);
                }
            });

            $("#editButton").button().on("click", function () {
                var checkbox = $('input:checked').first().attr('name');
                checkbox=checkbox.substr(6,checkbox.length-7);

                var findObject=tariffs.find(function (obj) {
                    if(obj.id==checkbox){
                        return true;
                    }
                });
                $('#templateid').attr({
                    "value":findObject.templateid
                });
                $('#osadd').val(findObject.addonid);
                $('#descriptions').text(findObject.description);
                $('#hidden_value').attr("value",findObject.id);
                $('#actionButton').attr({
                    "name":"action[edit]",
                    "value":"<?=$this->LANG['savech']?>"
                });
                $('.panel-heading').text("<?=$this->LANG['cortariff']?>");
                dialog.dialog("open");
            });

            $("#addButton").button().on("click", function () {
                $('#actionButton').attr({
                    "name":"action[add]",
                    "value":"<?=$this->LANG['add']?>"
                });
                $('#templateid').attr({
                    "value":""
                });
                $('#descriptions').text("");
                $('#hidden_value').attr("value","");
                $('.panel-heading').text("<?=$this->LANG['addtariff']?>");
                dialog.dialog("open");
            });
            $(".have-tooltip").tooltip({
                track: false
            });
            $('#editButton').prop("disabled",true);
            $('#deleteButton').prop("disabled",true);
        });
</script>

<form method="post">
    <table class="table table-striped table-bordered table-hover">
            <tr>
                <th>#</th>
                <th><?=$this->LANG['osname']?></th>
                <th><?=$this->LANG['idtem']?></th>
                <th><?=$this->LANG['desc']?></th>
            </tr>
        <?php foreach ($tariffs as $os):?>
            <tr>
                <td id="<?=$os->id?>"><input type="checkbox" name="check[<?=$os->id?>]"></td>
                <td><?=Capsule::table( 'tbladdons' )->select('name')->where( 'id',$os->addonid)->first()->name?></td>
                <td><?=$os->templateid?></td>
                <td class="have-tooltip" title="<?=$os->description?>"><?=substr($os->description,0,200)?>...</td>
            </tr>
        <?php endforeach;?>
    </table>

    <div class="btn-group" role="group">
        <button id="addButton" type="button" class="btn btn-info"><?=$this->LANG['buttonAdd']?></button>
        <input id="deleteButton" type="submit" class="btn btn-info ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" name="action[delete]" value="<?=$this->LANG['buttonDelete']?>">
        <button id="editButton" type="button" class="btn btn-info"><?=$this->LANG['buttonEdit']?></button>
    </div>
</form>

<div id="edit-options" class="panel panel-info">
    <div class="panel-heading"><?=$this->LANG['addtariff']?></div>
    <form method="post">
        <input id="hidden_value" type="hidden" name="id" value="">
        <div class="form-group">
            <label for="templateid"><?=$this->LANG['idtem']?> :</label>
            <input type="text" name="templateid" class="form-control" id="templateid" autocomplete="off" placeholder="<?=$this->LANG['enttem']?>">
            <small class="form-text text-muted"><?=$this->LANG['entid']?></small>
        </div>
        <div class="form-group">
            <select id="osadd" class="form-control" type="select" name="osadd">
                <?php foreach (Capsule::table( 'tbladdons' )->where( 'description','like',  '%"GROUP": "os"%')->get() as $os):?>
                    <option value="<?=$os->id?>"><?=$os->name?></option>
                <?php endforeach;?>
            </select>
        </div>
        <div class="form-group">
            <label for="descriptions"><?=$this->LANG['descs']?>:</label>
            <textarea name="descriptions" class="form-control" id="descriptions"></textarea>
            <small id="descriptionsHelp" class="form-text text-muted"><?=$this->LANG['optfield']?></small>
        </div>
        <input id="actionButton" type="submit" name="action[edit]" value="Принять изменения" class="btn btn-info">
    </form>
</div>

