<?php
use WHMCS\Database\Capsule;
?>
    <table class='table table-striped table-bordered table-hover'>
    <th>#</th>
    <th><?=$this->LANG['name']?></th>
    <th><?=$this->LANG['desc']?></th>
    <th><?=$this->LANG['os']?></th>
    <th></th>
    <form method="post" action="<?=$this->modulelink?>&action=change">
    <?php
foreach (Capsule::table('mod_onconfiguratorAddon')
             ->select("id","name","descriptions","os")
             ->get() as $ansible){
    $allOS='';
    $OSS=explode('/',$ansible->os);
    foreach (Capsule::table('tbladdons')
                 ->whereIn('id',$OSS)
                 ->select('id','name')
                 ->get() as $oneOS){
        $allOS=$allOS.$oneOS->name.'/';
    }
?>
    <tr>
<td><input type="checkbox" name="delete[<?=$ansible->id?>]"></td>
<td><?=$ansible->name?></td>
<td><?=$ansible->descriptions?></td>
<td><?=$allOS?></td>
<td><input type="submit" class="btn btn-primary" name="upgrade[<?=$ansible->id?>]" value="<?=$this->LANG['change']?>"></td>
    </tr>
<?}?>
    </table>
<div class="btn-group">
<input type="submit" class="btn btn-primary" name='activate' value="Activate">
<input type='submit' class="btn btn-primary" name='del' value='<?=$this->LANG['buttonDelete']?>'>
<a class="btn btn-primary" href="<?=$this->modulelink?>&action=add"><?=$this->LANG['addnew']?></a>
</form>

</div>


