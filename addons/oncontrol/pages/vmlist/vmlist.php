<?php
use WHMCS\Database\Capsule;

function vmlist_javaScripts()
{?>
    <script>

        $( document ).ready(function() {
            $('#host').selectize({
                plugins: ['remove_button']
            });

            $('#statusWhmcs').selectize({
                plugins: ['remove_button']
            });

            $('#statusOn').selectize({
                plugins: ['remove_button']
            });

            $('#tabs').tabs({
                active:false,
                collapsible: true
            });

            $('#selectFilterStatus').selectize({
                plugins:['remove_button'],
                onChange:function () {
                    var valueSearch = this.getValue();
                    if(this.getValue().length>1 && (valueSearch.indexOf('notInWhmcs')!=-1)){
                        this.clear();
                        this.setValue('notInWhmcs');
                        $.growl.warning({ message: "Invalid combination of parameters \"Filter by problem\"" });
                    }
                }
            });
        });

    </script>
<?}

$start = microtime(true);
vmlist_javaScripts();
$cloudlink = Capsule::table('tblconfiguration')
    ->select('value')->where('setting',ione_address)->get();


$arrayVariant=[
    'Active'=>['RUNNING','POWEROFF'],
    'Suspended'=>['SUSPENDED','POWEROFF']
];

$arrayProblems=[
    'notInWhmcs'=>$this->LANG['misswhmcs'],
    'hasError'=>$this->LANG['fillederror'],
    'noStatus'=>$this->LANG['notmatch']
];

$searchStatusWhmcs=$_REQUEST['statusWhmcs'];
$statusOn=$_REQUEST['statusOn'];
$searchHost=$_REQUEST['host'];
$searchIP=$_REQUEST['searchForIp'];
$smartStatus=$_REQUEST['selectFilterStatus'];

$page=$_REQUEST['page'];
$pageCount=20;

$hostsON=Capsule::table('mod_iOne_vmlist_cache')
    ->groupBy('host')
    ->lists('host');

$satesON=Capsule::table('mod_iOne_vmlist_cache')
    ->groupBy('state')
    ->lists('state');

$satusWhmcs=Capsule::table('tblhosting')
    ->groupBy('domainstatus')
    ->lists('domainstatus');


if(!$searchStatusWhmcs){
    $searchStatusWhmcs=['Suspended','Active'];
};

if(!$statusOn){
    $statusOn=$satesON;
};

if(!$searchHost){
    $searchHost=$hostsON;
}

if(in_array('notInWhmcs',$smartStatus)){
    $loginWhmcs=Capsule::table('tblhosting')
        ->join('mod_on_user','mod_on_user.id_service','=','tblhosting.id')
        ->lists('vmid');

    $userIdOn=Capsule::table('mod_iOne_vmlist_cache')
        ->whereNotIn('vmid',$loginWhmcs)
        ->lists('login');
}

if(in_array('hasError',$smartStatus)){
    $hostingIds[]=Capsule::table('tblhosting')
        ->join('mod_on_user','mod_on_user.id_service','=','tblhosting.id')
        ->orWhere('mod_on_user.vmid',' ')
        ->orWhere('mod_on_user.userid',' ')
        ->orWhere('mod_on_user.loginon',' ')
        ->orwhereNull('mod_on_user.vmid')
        ->orWhereNull('mod_on_user.userid')
        ->orWhereNull('mod_on_user.loginon')
        ->lists('hosting.id');
}


if(in_array('noStatus',$smartStatus)){
    foreach ($arrayVariant as $key=>$variant) {
        $hostingIds[] = Capsule::table('tblhosting')
            ->select('mod_iOne_vmlist_cache.state','tblhosting.domainstatus','tblhosting.id')
            ->join('mod_on_user', 'mod_on_user.id_service', '=', 'tblhosting.id')
            ->join('mod_iOne_vmlist_cache', 'mod_iOne_vmlist_cache.vmid', '=', 'mod_on_user.vmid')
            ->where('tblhosting.domainstatus',$key)
            ->whereNotIn('mod_iOne_vmlist_cache.state',$variant)
            ->lists('tblhosting.id');
    }
}


$pageGetUrl['statusWhmcs']=$searchStatusWhmcs;
$pageGetUrl['statusOn']=$statusOn;
$pageGetUrl['selectFilterStatus']=$smartStatus;
$pageGetUrl['host']=$searchHost;
$pageGetUrl['searchForIp']=$searchIP;
$pageGetUrl=http_build_query($pageGetUrl);


if(!$page) {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/modules/servers/onconnector/lib/ONConnect.php');
    $onconnect = new ONConnect();
    $dataON = $onconnect->compare_info();
    $userON = $dataON['result'][0];
    $hostsON = $dataON['result'][1];
        if(count($userON)>0) {
            $answerCount = Capsule::table('mod_iOne_vmlist_cache')
                ->truncate();
            $answerCount = Capsule::table('mod_iOne_vmlist_cache')
                ->insert($userON);
        }
}

if(count($hostingIds)==0 && count($userIdOn)==0) {
    $answerCount = Capsule::table('tblproducts')
        ->select(
            'tblproducts.gid',
            'tblhosting.userid',
            'tblhosting.domainstatus',
            'tblhosting.dedicatedip',
            'mod_on_user.id_service',
            'mod_on_user.loginon',
            'mod_on_user.passwordon',
            'mod_on_user.userid as useridOn',
            'mod_on_user.vmid')
        ->join('tblhosting', 'tblproducts.id', '=', 'tblhosting.packageid')
        ->join('mod_on_user', 'mod_on_user.id_service', '=', 'tblhosting.id')
        ->leftJoin('mod_iOne_vmlist_cache', 'mod_iOne_vmlist_cache.vmid', '=', 'mod_on_user.vmid')
        ->whereIn('tblproducts.servertype', ['onconnector','ione'])
        ->whereIn('tblhosting.domainstatus', $searchStatusWhmcs)
        ->whereIn('mod_iOne_vmlist_cache.state', $statusOn)
        ->whereIn('mod_iOne_vmlist_cache.host', $searchHost)
        ->where('tblhosting.dedicatedip', 'like', '%' . $searchIP . '%')
        ->count();

    $userWHMCS = Capsule::table('tblproducts')
        ->select(
            'tblproducts.gid',
            'tblhosting.domainstatus',
            'tblhosting.userid',
            'mod_on_user.id_service',
            'mod_on_user.loginon',
            'mod_on_user.passwordon',
            'mod_on_user.userid as useridOn',
            'mod_on_user.vmid',
            'tblhosting.dedicatedip',
            'mod_iOne_vmlist_cache.state',
            'mod_iOne_vmlist_cache.host')
        ->join('tblhosting', 'tblproducts.id', '=', 'tblhosting.packageid')
        ->join('mod_on_user', 'mod_on_user.id_service', '=', 'tblhosting.id')
        ->leftJoin('mod_iOne_vmlist_cache', 'mod_iOne_vmlist_cache.vmid', '=', 'mod_on_user.vmid')
        ->whereIn('tblproducts.servertype', ['onconnector','ione'])
        ->whereIn('tblhosting.domainstatus', $searchStatusWhmcs)
        ->whereIn('mod_iOne_vmlist_cache.state', $statusOn)
        ->whereIn('mod_iOne_vmlist_cache.host', $searchHost)
        ->where('tblhosting.dedicatedip', 'like', '%' . $searchIP . '%')
        ->offset(($page - 1) * $pageCount)
        ->limit($pageCount)
        ->orderBy('mod_iOne_vmlist_cache.vmid', 'asc')
        ->get();
}
elseif(count($hostingIds)!=0 && count($userIdOn)==0){

    $resultHostingIds=[];
    foreach ($hostingIds as $ids){
        $resultHostingIds=array_merge($resultHostingIds,$ids);
    }

    $answerCount = Capsule::table('tblproducts')
        ->select(
            'tblproducts.gid',
            'tblhosting.userid',
            'tblhosting.domainstatus',
            'tblhosting.dedicatedip',
            'mod_on_user.id_service',
            'mod_on_user.loginon',
            'mod_on_user.passwordon',
            'mod_on_user.userid as useridOn',
            'mod_on_user.vmid')
        ->join('tblhosting', 'tblproducts.id', '=', 'tblhosting.packageid')
        ->join('mod_on_user', 'mod_on_user.id_service', '=', 'tblhosting.id')
        ->leftJoin('mod_iOne_vmlist_cache', 'mod_iOne_vmlist_cache.vmid', '=', 'mod_on_user.vmid')
        ->whereIn('tblproducts.servertype', ['onconnector','ione'])
        ->whereIn('tblhosting.domainstatus', $searchStatusWhmcs)
        ->whereIn('mod_iOne_vmlist_cache.state', $statusOn)
        ->whereIn('mod_iOne_vmlist_cache.host', $searchHost)
        ->whereIn('tblhosting.id',$resultHostingIds)
        ->where('tblhosting.dedicatedip', 'like', '%' . $searchIP . '%')
        ->count();

    $userWHMCS = Capsule::table('tblproducts')
        ->select(
            'tblproducts.gid',
            'tblhosting.domainstatus',
            'tblhosting.userid',
            'mod_on_user.id_service',
            'mod_on_user.loginon',
            'mod_on_user.passwordon',
            'mod_on_user.userid as useridOn',
            'mod_on_user.vmid',
            'tblhosting.dedicatedip',
            'mod_iOne_vmlist_cache.state',
            'mod_iOne_vmlist_cache.host')
        ->join('tblhosting', 'tblproducts.id', '=', 'tblhosting.packageid')
        ->join('mod_on_user', 'mod_on_user.id_service', '=', 'tblhosting.id')
        ->leftJoin('mod_iOne_vmlist_cache', 'mod_iOne_vmlist_cache.vmid', '=', 'mod_on_user.vmid')
        ->whereIn('tblproducts.servertype', ['onconnector','ione'])
        ->whereIn('tblhosting.domainstatus', $searchStatusWhmcs)
        ->whereIn('mod_iOne_vmlist_cache.state', $statusOn)
        ->whereIn('mod_iOne_vmlist_cache.host', $searchHost)
        ->whereIn('tblhosting.id',$resultHostingIds)
        ->where('tblhosting.dedicatedip', 'like', '%' . $searchIP . '%')
        ->offset(($page - 1) * $pageCount)
        ->limit($pageCount)
        ->orderBy('mod_iOne_vmlist_cache.vmid', 'asc')
        ->get();
}
elseif(count($hostingIds)==0 && count($userIdOn)!=0){
    $answerCount = Capsule::table('mod_iOne_vmlist_cache')
        ->whereIn('login',$userIdOn)
        ->where('ip', 'like', '%' . $searchIP . '%')
        ->count();

    $userWHMCS = Capsule::table('mod_iOne_vmlist_cache')
        ->whereIn('login',$userIdOn)
        ->where('ip', 'like', '%' . $searchIP . '%')
        ->offset(($page - 1) * $pageCount)
        ->limit($pageCount)
        ->orderBy('mod_iOne_vmlist_cache.vmid', 'asc')
        ->get();
    var_dump($answerCount);
}
elseif(count($hostingIds)!=0 && count($userIdOn)!=0){}

foreach ($userWHMCS as $oneUser)
{
    if(!$oneUser->vmid){
        $oneUser->col='warning';
    }
    if(!$oneUser->loginon){
        $oneUser->col='info';
    }
    if(!$oneUser->host){
        $oneUser->col='danger';
    }
    if(!$oneUser->col){
        $oneUser->col='success';
    }
}

$pageAll=ceil($answerCount/$pageCount);

$minPage=$page-3;
$maxPage=$page+3;
if($minPage<1){
    $minPage=1;
}

if($maxPage>$pageAll){
    $maxPage=$pageAll;
}

$LANG=$this->vars['_lang'];
?>

    <link rel="stylesheet" href="/assets/css/selectize.css">
    <script src="/assets/js/selectize.js"></script>
    <form method="post" action="<?=$this->modulelink?>&page=1">
        <div id="tabs">
            <ul>
                <li><a href="#tabs-1"><?=$LANG['searchFilter']?></a></li>
            </ul>
            <div id="tabs-1">
                <div id="dialog-confirm" class="panel panel-default" style="position: relative; z-index: 2;">
                    <ul class="list-group">

                        <li class="list-group-item">
                            <label for="host"><?=$LANG['searchByIP']?></label>
                            <input id="inputForSearch" name="searchForIp" type="text" class="form-control" value="<?=$searchIP?>">
                        </li>

                        <li class="list-group-item">
                            <label for="host"><?=$LANG['thHost']?></label>
                            <select id="host" name="host[]" multiple="multiple">
                                <?foreach ($hostsON as $host):?>
                                    <option value="<?=$host?>" <? if(in_array($host,$searchHost)){print 'selected';}?>><?=$host?></option>
                                <?endforeach;?>
                            </select>
                        </li>

                        <li class="list-group-item">
                            <label for="statusWhmcs"><?=$LANG['thStatusWHMCS']?></label>
                            <select id="statusWhmcs" name="statusWhmcs[]" multiple="multiple">
                                <?foreach ($satusWhmcs as $status):?>
                                    <option value="<?=$status?>" <? if(in_array($status,$searchStatusWhmcs)){print 'selected';}?>><?=$status?></option>
                                <?endforeach;?>
                            </select>
                        </li>

                        <li class="list-group-item">
                            <label for="statusOn"><?=$LANG['thStatusOpenNebula']?></label>
                            <select id="statusOn" name="statusOn[]" multiple="multiple">
                                <?foreach ($satesON as $sate):?>
                                    <option value="<?=$sate?>" <? if(in_array($sate,$statusOn)){print 'selected';}?>><?=$sate?></option>
                                <?endforeach;?>
                            </select>
                        </li>

                        <li class="list-group-item">
                            <label for="statusOn"><?=$LANG['filterByProblem']?></label>
                            <select id="selectFilterStatus" name="selectFilterStatus[]" multiple="multiple">
                                <?foreach ($arrayProblems as $codeProblem=>$problem):?>
                                    <option value="<?=$codeProblem?>" <? if(in_array($codeProblem,$smartStatus)){print 'selected';}?>><?=$problem?></option>
                                <?endforeach;?>
                            </select>
                        </li>
                    </ul>
                    <div class="row">
                        <div class="col-lg-9"></div>
                        <div class="col-lg-1"><input type="submit" style="width: 100%" class="btn btn-info" value="<?=$LANG['search']?>"></div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <table class='table table-bordered table-hover table-condensed'>
        <thead class="thead-inverse">
        <tr>
            <th><?=$LANG['thClientsId']?></th>
            <th><?=$LANG['thHostingId']?></th>
            <th><?=$LANG['thLoginOpenNebula']?></th>
            <th><?=$LANG['thUserIdOpenNebula']?></th>
            <th><?=$LANG['thVmidOpenNebula']?></th>
            <th><?=$LANG['thStatusOpenNebula']?></th>
            <th><?=$LANG['thStatusWHMCS']?></th>
            <th><?=$LANG['thHost']?></th>
            <th><?=$LANG['thIpAddress']?></th>
            <th><?=$LANG['thDo']?></th>
        </tr>
        </thead>

        <?foreach ($userWHMCS as $oneUser):?>
            <tr class='<?=$oneUser->col?>'>
                <td><a href="/admin/clientssummary.php?userid=<?=$oneUser->userid?>" target="_blank"><?=$oneUser->userid?></a></td>
                <td><a href="/admin/clientsservices.php?userid=<?=$oneUser->userid?>&id=<?=$oneUser->id_service?>" target="_blank"><?=$oneUser->id_service?></a></td>
                <td><?=$oneUser->loginon?></td>
                <td><a href="<?=$cloudlink[0]->value?>/#users-tab/<?=$oneUser->useridOn?>" target="_blank"><?=$oneUser->useridOn?></a></td>
                <td><a href="<?=$cloudlink[0]->value?>/#vms-tab/<?=$oneUser->vmid?>" target="_blank"><?=$oneUser->vmid?></a></td>
                <td><?=$oneUser->state?></td>
                <td><?=$oneUser->domainstatus?></td>
                <td><?=$oneUser->host?></td>
                <td><?=$oneUser->dedicatedip?></td>
                <td>
                    <a href="<?=$this->vars['modulelink'].'&tabs=vmlist&mod=panel&serviceId='.$oneUser->id_service?>" class="btn btn-info"><?=$LANG['modEdit']?></a>
                </td>
            </tr>
        <?endforeach;?>
    </table>
    <div class="row">
        <div class="col-lg-10 col-mg-10">
            <ul class="pagination">
                <li><a href="<?=$this->modulelink?>&page=1&<?=$pageGetUrl?>">«</a></li>
                <? for($i=$minPage;$i<=$maxPage;$i++):?>
                    <li class="<?php if($i==$page){print 'active';};?>"><a href="<?=$this->modulelink?>&page=<?=$i?>&<?=$pageGetUrl?>"><?=$i?></a></li>
                <? endfor;?>
                <li><a href="<?=$this->modulelink?>&page=<?=$pageAll?>&<?=$pageGetUrl?>">»</a></li>
            </ul>
        </div>

        <div class="col-lg-2 col-mg-2">
            <a class="btn btn-info" href="<?=$this->modulelink?>"><?=$LANG['updatecache']?><span class="glyphicon glyphicon-refresh"></span></a>
        </div>
    </div>

<?
print ($LANG['timescript'].': '.(microtime(true) - $start).' '.$LANG['second'] );
