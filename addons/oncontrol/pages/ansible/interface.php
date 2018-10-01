<?php
if (!defined("WHMCS"))
    die("This file cannot be accessed directly");
use WHMCS\Database\Capsule;
$allAddon=Capsule::table( 'tbladdons' )
    ->select("id","name","description")
    ->where( 'description','like',  '%"TYPE": "playbook"%')
    ->get();

$curentAddon=Capsule::table('tbladdons')
    ->select("id","name","description")
    ->where( 'description','like',  '%"TYPE": "playbook"%')
    ->where('id',$addonid)
    ->first();
$oss=Capsule::table( 'tbladdons' )
    ->select("id","description")
    ->where( 'description','like',  '%"GROUP": "os"%')
    ->get();
?>

<div class="previous"><a href="<?=$this->vars["modulelink"]?>&tabs=ansible&mod=ansibledb"><?=$this->LANG['backlist']?></a> </div>

<fieldset class="col-lg-5">
    <form method="post" action="<?=$this->vars["modulelink"]?>&mod=ansibledb&tabs=ansible&action=<?=$_GET['action']?>">
        <div class="form-group">
            <label for="name"><?=$this->LANG['name']?>:</label>
            <input class="form-control" id="name" type="text" name="name" value="<?=$this->updateDate->name?>">
        </div>
        <fieldset class="form-group">
            <label for="descriptions"><?=$this->LANG['descs']?>:</label>
            <textarea maxlength="512" rows="5" class="form-control" id="descriptions" name="descriptions" type="text"><?=$this->updateDate->descriptions?></textarea>
        </fieldset>
        <fieldset class="form-group">
            <label for="addon"><?=$this->LANG['addon']?>:</label><br>
            <select id="addon" name="addonid" class="form-control select-inline">
                <option value="<?=$curentAddon->id?>"><?=$curentAddon->id?>: <?=$curentAddon->name?></option>
                <? foreach ($allAddon as $addon):?>
                    <option value="<?=$addon->id?>"><?=$addon->id?>: <?=$addon->name?></option>
                <?endforeach; ?>
            </select>
        </fieldset>
        <fieldset class="form-group">
            <label for="body"><?=$this->LANG['body']?>:</label>
            <textarea rows="10" maxlength="1024" class="form-control" id="body" name="body" size="10" type="text"><?=$this->updateDate->body?></textarea>
        </fieldset>
        <fieldset class="form-check">
            <legend><?=$this->LANG['noteos']?>:</legend>
            <? foreach ($oss as $os):?>
                        <input class="form-check-input" type="checkbox" name="check[<?=$os->id?>]" id="<?=$os->id?>" <?if(in_array($os->id, $this->updateDate->check)){print 'checked';}?>>
                        <label class="form-check-label" for="<?=$os->id?>"><?=json_decode($os->description)->TITLE?></label><br>
            <?endforeach;?>
        </fieldset>
        <?if($_GET['action']=="change"):?>
            <input name="upgrade[<?=$id?>]" class="btn btn-primary" type="submit" value="<?=$this->LANG['update']?>">
        <?else:?>
            <input name="save" class="btn btn-primary" type="submit" value="<?=$this->LANG['buttonOnconSubmit']?>">
        <?endif;?>
    </form>
</fieldset>
