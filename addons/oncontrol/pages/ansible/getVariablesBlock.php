<? if (!defined("WHMCS"))
    die("This file cannot be accessed directly");
use WHMCS\Database\Capsule;
?>
<script type="text/javascript">
    $("#sortable").ready(function () {
        $( "#sortable" ).sortable({});
    });
</script>

<form method="post" action="<?=$this->link.'&tabs=ansible&mod=ansibledb&action=fullActivation'?>">
    <div class="panel panel-default">
        <div class="panel-heading"><?=$this->LANG['PanelHeading']?>:</div>
        <div class="panel-body">
            <div class="col-lg-10">
                <label for='IP'><?=$this->LANG['IP']?>:</label>
                <input id='IP' class='form-control' name='IP' type='text' value='<?=$this->servicesDedicetedIp[0]?>'>
            </div>
            <div class="col-lg-2">
                <label for='port'><?=$this->LANG['PanelPort']?>:</label>
                <input id='port' class='form-control' name='port' type='text' value='<?=$this->portAnsible[0]->value?>'>
            </div>
        </div>
    </div>
    <div class="row"></div>
    <ul id="sortable">
        <?php foreach ($this->arrayVariable as $numberVariable=>$variable):?>
    <li class="list-group-item">
        <fieldset>
            <legend> <?= $variable['name'] ?></legend>
            <input type="hidden" name="variable[<?= $numberVariable ?>]">
            <?php foreach ($variable['ids'] as $key => $value): ?>
                <label for="<?= $key ?>"><?= $key ?> :</label><br>
                <input class="form-control" id="<?= $key ?>" type="text"
                       name="variable[<?= $numberVariable ?>][<?= $key ?>]" value="<?=$value?>"></br>
            <? endforeach; ?>
        </fieldset>
        <? endforeach; ?>
    </li>
    </ul>

    <input type="hidden" name="idNewProduct" value="<?=$this->idNewProduct?>">
    <input type="hidden" name="idNewOS" value="<?=$this->idNewOS?>">
    <input type="hidden" name="serviceId" value="<?=$this->serviceId?>">
    <input class='btn btn-primary' type='submit' name='full' value='<?=$this->LANG['activate']?>'>
</form>
