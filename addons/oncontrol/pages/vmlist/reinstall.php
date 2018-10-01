<?php


use WHMCS\Database\Capsule;
$LANG=$this->vars['_lang'];
if (!defined("WHMCS")) {
	die("This file cannot be accessed directly");
}

$idRefreshSystem=$_GET['serviceId'];
extract($_POST);?>
    <div class="container-fluid">
    <div id="alert"></div>
<?php
	$packageid = Capsule::table('tblhosting')
		->where('id', $idRefreshSystem)
		->first()->packageid;

	$reinstal_settings = Capsule::table('tblproduct_upgrade_products')
		->where('product_id', $packageid)
		->select('id', 'upgrade_product_id', 'product_id')
		->get();
    if(!$reinstal_settings){
        $oneObject = new stdClass();
        $oneObject -> upgrade_product_id=$packageid;
        $reinstal_settings[]=$oneObject;
        print ($packageid);
    }

    $ansibles = Capsule::table('mod_onconfiguratorAddon')
        ->select('id','name','descriptions','os')
        ->get();

    $operating_systems = Capsule::table('tbladdons')
    ->select('tbladdons.id as addonid','tbladdons.name as os')
    ->join('mod_onconfiguratorOS','mod_onconfiguratorOS.addonid','=','tbladdons.id')
    ->where('tbladdons.description','like','%"GROUP": "os"%')
    ->get();

    $osConfig = Capsule::table('mod_onconfigurator')
        ->select('os','addonid','idtariff')
        ->get();


    if(!$operating_systems):?>
        <div class="infobox">
            <span class="title"><?=$LANG['attention']?>:</span>
            <div><?=$LANG['attentiontariff']?></div>
        </div>
    <?php endif; ?>

	<script type="text/javascript">
        var tariffs = <?=json_encode($reinstal_settings)?>;
        var ansible = <?=json_encode($ansibles)?>;
        var osConfig = <?=json_encode($osConfig)?>;

        ansible = ansible.map(function(item){
            item.os=item.os.match(/\d+/g);
            return item;
        });

        $(document).ready(
            function () {
                $('#idNewOS').on('change', function() {
                    var checkBoxs = $('input:checkbox');
                    var idChangeOs = this.value;
                    $('input:checkbox:checked').prop( "checked", false );

                    ansibleConfigFilter = ansible.filter(function (currentValue) {
                        if(currentValue.os.indexOf(idChangeOs)!='-1'){
                            return true;
                        } else {
                            return false;
                        }
                    }).map(function(item){
                        return $('#checkbox_'+item.id)[0];
                    });
                    checkBoxs.removeAttr("disabled");
                    checkBoxs.not(ansibleConfigFilter).attr("disabled","disabled");
                });

                $('#idNewProduct').on('change', function() {
                    var spanAlert= $('#alert');
                    spanAlert.html('');
                    var idChangeProduct=this.value;
                    osConfigFilter = osConfig.filter(function (currentValue) {
                        if(currentValue.idtariff==idChangeProduct){
                            return true;
                        } else {
                            return false;
                        }
                    });

                    if(osConfigFilter.length==0){
                        $.growl.warning({ message: "The selected tariff plan has not yet been configured!" });
                    }
                    var idNewOsSelector=$("#idNewOS");
                    idNewOsSelector.html('');
                    osConfigFilter.forEach(function (item) {
                        var oneOption =$('<option/>',{
                                value:item.addonid,
                                text:item.os
                            }
                        );
                        idNewOsSelector.append(oneOption);
                    });
                });
            });
	</script>

	<div class="jumbotron">
		<div class="row">
			<p id="test"></p>
			<div class="col-sm-12">

				<div class="panel panel-danger">
					<div class="panel-body">
                        <h3><b><?=$LANG['thId']?></b> = <?php echo $idRefreshSystem; ?></h3>
						<h2><?=$LANG['alldata']?> <b style="color: red"><?=$LANG['deletecaps']?></b></h2>
					</div>
				</div>

				<form method="GET" action="/admin/addonmodules.php">
                    <div class=" test col-sm-12"></div>

                    <div>
                        <?php foreach ($ansibles as $ansible):?>
                            <label class="checkbox-inline">
                                <input id="checkbox_<?=$ansible->id?>" name="ansibles[]" type="checkbox" value="<?=$ansible->id?>"><?=$ansible->name?>
                            </label>
                        <? endforeach;?>
                    </div>

					<div class="col-sm-12">
						<label for="groupId"><?=$LANG['reinstallgp']?>:</label>
                        <select class="form-control"	id="idNewProduct" name="idNewProduct">
							<?php foreach ($reinstal_settings as $reinstal_setting) :
								$possible_product = Capsule::table('tblproducts')
									->where('id', $reinstal_setting->upgrade_product_id)
									->first() ?>
								<option value="<?php echo $possible_product->id;?>">
									<?php echo $possible_product->name ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>

					<div class="col-sm-12">
						<label for="idNewOS" style="margin-top: 10px;"><?=$LANG['reinstallos']?>:</label>
                        <select class="form-control" id="idNewOS" name="idNewOS" style="width: 100%;">
							<?php foreach ($operating_systems as $operating_system) : ?>
								<option value="<?php echo $operating_system->addonid; ?>">
									<?php echo $operating_system->os ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>

					<div class="col-sm-6" class="text-center" style="margin-top: 20px;">
                        <input type="hidden" name="module" value="oncontrol">
                        <input type="hidden" name="tabs" value="ansible">
                        <input type="hidden" name="mod" value="ansibledb">
                        <input type="hidden" name="action" value="reinstall">
						<input type="hidden" name="serviceId" value="<?php echo $idRefreshSystem ?>">
                        <input type="hidden" name="forced" value="true">
                        <input type="submit" class="btn btn-block btn-danger" value="Reinstall system!">
					</div>

				</form>
				<form method="POST" action="<?=$vars['modulelink']?>&mod=test">
				<div class="col-sm-6 text-center" style="margin-top: 20px;">
					<input type="hidden" name="id" value="<?php echo $idRefreshSystem ?>">
                    <input type="submit" class="btn btn-block btn-success" value="Back to change page">
				</div>
				</form>
			</div>
		</div>
	    </div>
    </div>


    
