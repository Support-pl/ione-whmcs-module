<?php
ini_set('display_errors', 0);
$quotas = $this->onconnect->datastoresMonitoring();
$quotasRam = $this->onconnect->hostsMonitoring();
$LANG=$this->vars['_lang'];

?>
<script>
    function progressBarPrint(name,quota) {
        var progressColor;
        if(quota>90){
            progressColor='progress-bar-danger';
        }else if(quota>70){
            progressColor='progress-bar-warning';
        }
        var divProgress = $('<div/>', {
            class: 'progress',
        });

        var mydiv = $('<div/>', {
            class:  'progress-bar '+progressColor,
            text: 	Math.round(quota)+'%',
            style:  'width:'+quota+'%; color: #F8F8FF;',
            role: "progressbar",
            'aria-valuenow': "70",
            'aria-valuemin':"0",
            'aria-valuemax':"100"
        });
        divProgress.append(mydiv);
        $('#'+name).append(divProgress);
    }

    $( function() {
        var quota = <?=json_encode($quotas,true)?>;
        var quotaRam = <?=json_encode($quotasRam,true)?>;
        $(document).ready(function() {
        quotaRam.result.forEach(function (ram) {
            ram.reserved=ram.reserved.substr(0, ram.reserved.length - 2);
            ram.full_size=ram.full_size.substr(0, ram.full_size.length - 2);
            ram.cpu=ram.cpu.substr(0, ram.cpu.length - 1);
            progressBarPrint('ram_'+ram.name,ram.reserved/ram.full_size*100);
            progressBarPrint('cpu_'+ram.name,Math.round(ram.cpu));
        });
        quota.result.forEach(function (obj) {
            if (obj.used.slice(-2) == 'GB'){
                obj.used=obj.used.substr(0, obj.used.length - 2);
                obj.used=obj.used/1000;
            }else {
                obj.used = obj.used.substr(0, obj.used.length - 2);
            }
            obj.full_size=obj.full_size.substr(0, obj.full_size.length - 2);
            progressBarPrint(obj.name,obj.used/obj.full_size*100);
        });
    } )});

</script>

<div class="panel panel-info col-lg-6">
    <table class="table table-bordered table-hover table-condensed">
        <?php foreach ($quotasRam['result'] as $quota):?>
            <tr class="quota">
                <td rowspan="4"><?=$quota['name']?></td>
                <td><b><?=$LANG['cpu']?>:</b> <?=$quota['cpu']?></td>
                <td rowspan="4"><b><?=$LANG['vmquantity']?></b> <?=$quota['running_vms']?></td>
            <tr class="quota">
                <td id="cpu_<?=$quota['name']?>"></td>
            </tr>
            <tr class="quota">
                <td><b><?=$LANG['ram']?>:</b> <?=$quota['reserved']?>/<?=$quota['full_size']?> (<?=round($quota['reserved']/$quota['full_size']*100)?>%)</td>
            </tr>
            <tr class="quota">
                <td id="ram_<?=$quota['name']?>"></td>
            </tr>
            </tr>
        <?php endforeach;?>
    </table>
</div>

<div class="panel panel-info col-lg-6">
    <table class="table table-bordered table-hover table-condensed">
        <?php foreach ($quotas['result'] as $quota):?>
            <tr class="quota">
                <td rowspan="2"><b><?=$quota['name']?></b></td>
                <?if(substr($quota['used'],-2) == 'GB'){
                    $quotamod = round($quota['used']) / 1000;
                }else{
                    $quotamod = round($quota['used']);
                }
                ?>
                <td><?=$quota['used']?>/<?=$quota['full_size']?> (<?=round($quotamod/$quota['full_size']*100)?>%)</td>
            <tr class="quota">
                <td id="<?=$quota['name']?>"></td>
            </tr>
            </tr>
        <?php endforeach;?>
    </table>
</div>