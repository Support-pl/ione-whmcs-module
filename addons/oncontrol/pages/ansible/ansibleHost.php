<script type="text/javascript">
    $("#sortable").ready(function () {
        $( "#sortable" ).sortable({
        });
    });
</script>

<form method="post" action="<?=$this->link.'&action='.$action?>">
    <div class="panel panel-default">
        <div class="panel-heading"><?=$this->LANG['PanelHeading']?>:</div>
        <div class="panel-body">
            <div class="col-lg-10">
                <label for='IP'><?=$this->LANG['IP']?>:</label>
                <input id='IP' class='form-control' name='IP' type='text' value='<?=$ip->domain?>'>
            </div>
            <div class="col-lg-2">
                <label for='port'><?=$this->LANG['PanelPort']?>:</label>
                <input id='port' class='form-control' name='port' type='text' value='<?=$portAnsible[0]->value?>'>
            </div>
        </div>
    </div>
    <div class="row"></div>

    <input type="hidden" name="idNewProduct" value="<?=$idNewProduct?>">
    <input type="hidden" name="idNewOS" value="<?=$idNewOS?>">
    <input type="hidden" name="serviceId" value="<?=$serviceId?>">
    <input class='btn btn-primary' type='submit' name='full' value='<?=$this->LANG['activate']?>'>
</form>