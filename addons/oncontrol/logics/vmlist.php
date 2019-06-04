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
                        $.growl.warning({ message: "Недопустимая комбинация параметров в \"фильтр по проблеме\"" });
                    };
                }
            });
        });

    </script>
<?}

$start = microtime(true);

vmlist_javaScripts();
$vars['cloudLink']= Capsule::table('tblconfiguration')
    ->select('value')->where('setting',ione_address)->get();

$arrayVariant=[
    'Active'=>['RUNNING','POWEROFF'],
    'Suspended'=>['SUSPENDED','POWEROFF']
];

$arrayProblems=[
    'notInWhmcs'=>'Отсутствующие в WHMCS',
    'hasError'=>'Заполнены с ошибками',
    'noStatus'=>'Не совпадают статусы'
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

    $answerCount = Capsule::table('mod_iOne_vmlist_cache')
        ->truncate();
    $answerCount = Capsule::table('mod_iOne_vmlist_cache')
        ->insert($userON);
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
        ->whereIn('tblproducts.gid', $groups)
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
        ->whereIn('tblproducts.gid', $groups)
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
        ->whereIn('tblproducts.gid', $groups)
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
        ->whereIn('tblproducts.gid', $groups)
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
$LANG=$vars['_lang'];
?>