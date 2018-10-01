<?php
use WHMCS\Database\Capsule;
use Symfony\Component\Yaml\Yaml;


class ActivateAnsible
{
    protected $ids,$serviceIP,$pointer,$serviceId;
    protected $arrayVariable=[];
    protected $variableNames;
    protected $arrayResult;
    function __construct($ids=[],$link=null){
        $this->ids=$ids;
        $this->link=$link;
    }

    public function requireFile($fileName)
    {
        require_once ($_SERVER['DOCUMENT_ROOT'].'/modules/addons/oncontrol/pages/ansible/'.$fileName);
    }


    public function automatActivate($serviceId)
    {

    }

    protected function constructResult()
    {
        foreach ($this->arrayVariable as $key=>$value){
            $YamlString=Capsule::table('mod_onconfiguratorAddon')
                ->select('body','name')
                ->where('id',$key)
                ->first();
            $YamlArray=Yaml::parse($YamlString->body);
            $YamlArray[0]['vars']=$value;
            $YamlArray[0]['hosts']='{{group}}';
            $this->arrayResult[$YamlString->name] = htmlspecialchars_decode(Yaml::dump($YamlArray));
        }
    }

    public function fullActivate($onIp=null,$arrayVariable=null,$portModule=52222)
    {
        $this->ip=$onIp;
        $this->arrayVariable=$arrayVariable;
        $this->ipModule=$ipModule;
        $this->portModule=$portModule;
        $this->constructResult();
    }

    public function getArrayResult()
    {
        return $this->arrayResult;
    }

    public function getAnsibleHost()
    {
        return $this->ip.':'.$this->portModule;
    }

    protected function password()
    {
        $pass=Capsule::table('tblhosting')
            ->select('password')
            ->where('tblhosting.id', $this->serviceId)
            ->first();

        $postData = array(
            'password2' => $pass->password,
        );

        $passroot = localAPI('DecryptPassword', $postData);
        return $passroot['password'];
    }

    protected function email()
    {
        $email=Capsule::table('tblclients')
            ->select('tblclients.email')
            ->Join('tblhosting','tblclients.id','=','tblhosting.userid')
            ->where('tblhosting.id', $this->serviceId)
            ->first();
        return $email->email;
    }

    protected function checkMethods($method)
    {
        return method_exists($this,$method);
    }

    protected function substituteVariable($itemId)
    {
        $YamlString=Capsule::table('mod_onconfiguratorAddon')
            ->where('id',$itemId)
            ->first();
        $YamlArray=Yaml::parse($YamlString->body);
        $this->arrayVariable[$itemId]['ids']=$YamlArray[0]['vars'];
    }

    protected function setVariableInYaml(){
        foreach ($this->arrayVariable as $key=>&$oneAnsible){
            foreach ($oneAnsible as $nameOneVariable=>&$valueOneVariable){
                if($this->checkMethods($nameOneVariable)){
                    $valueOneVariable=$this->$nameOneVariable();
                };
            }
        }
    }
}

class AutoActivateAnsible extends ActivateAnsible
{
    public function __construct($serviceId){
        $this->serviceId = $serviceId;
    }
    public function getServicesFromIds($ids)
    {
        foreach ($ids as $id){
            $this->substituteVariable($id);
        }
        $this->setVariableInYaml();
        $this->constructResult();
        return $this->arrayResult;
    }
}

class MainModuleActivate extends ActivateAnsible
{

    public function __construct($link,$vars)
    {
        $this->vars = $vars;
        $this->link=$link;

    }

    public function setIds($ids)
    {
        $this->ids=$ids;
    }


    public function activate($serviceId,$LANG,$action='fullActivation')
    {
        $this->portAnsible=Capsule::table( 'tblconfiguration' )
            ->where('setting','ansibledb_config_port')
            ->get();
        if($serviceId){
            $this->serviceId=$serviceId;
            $this->servicesDedicetedIp=Capsule::table('tblhosting')
                ->where('id', $this->serviceId)
                ->pluck('domain');
        }
        foreach (Capsule::table('mod_onconfiguratorAddon')
                     ->whereIn('id', $this->ids)
                     ->get() as $item){
            $this->substituteVariable($item->id);
            $this->arrayVariable[$item->id]['name']=$item->name;
        }
        $this->LANG = $LANG;
        $this->requireFile('getVariablesBlock.php');
    }
}

class ReinstallActivation extends ActivateAnsible {
    public function __construct($link)
    {
        $this->link=$link;
    }

    public function setIds($ids)
    {
        $this->ids=$ids;
    }


    public function activate($serviceId,$action='fullActivation')
    {
        $this->portAnsible=Capsule::table( 'tblconfiguration' )
            ->where('setting','ansibledb_config_port')
            ->get();
        if($serviceId){
            $this->serviceId=$serviceId;
            $this->servicesDedicetedIp=Capsule::table('tblhosting')
                ->where('id', $this->serviceId)
                ->pluck('domain');
        }
        foreach (Capsule::table('mod_onconfiguratorAddon')
                     ->whereIn('id', $this->ids)
                     ->get() as $item){
            $this->substituteVariable($item->id);
            $this->arrayVariable[$item->id]['name']=$item->name;
        }
        $this->requireFile('getVariablesBlock.php');
    }

    public function comparateInfo()
    {
        extract($_POST);

        $dataUser = Capsule::table('mod_on_user')
        ->where('id_service', $serviceId)
            ->first();


        $dataMachine = Capsule::table('tblhosting')
        ->where('id', $serviceId)
            ->first();

        $command = 'DecryptPassword';
        $postData = [
            'password2' => $dataMachine->password,
        ];
        $results = localAPI($command, $postData);


        $temlaitid=Capsule::table( 'tblhostingaddons' )
            ->select('mod_onconfiguratorOS.templateid')
            ->where('tblhostingaddons.hostingid',$serviceId)
            ->join('tbladdons','tbladdons.id','=','tblhostingaddons.addonid')
            ->join('mod_onconfiguratorOS','mod_onconfiguratorOS.addonid','=','tblhostingaddons.addonid')
            ->first();


        $adonbool=false;

        $ansible=$this->automatActivate($serviceId);

        if($ansible){
            $adonbool=true;
        }

        $products = Capsule::table('tblproducts')
        ->where('id', $dataMachine->packageid)
            ->first();

        $cpu=0;$ram=0;$drive=0;$units=0;$ds_type='';$iops=0;

        $properties=json_decode($products->description,true);
        foreach ($properties['properties'] as $propertie){
            switch ($propertie['GROUP']){
                case 'cpu_core':
                    $value=explode(' ',$propertie['TITLE']);
                    $cpu+=$value[0];
                    break;
                case 'ram':
                    $value=explode(' ',$propertie['TITLE']);
                    $ram+=$value[0];
                    break;
                case 'hdd':
                    $value=explode(' ',$propertie['TITLE']);
                    $drive+=$value[0];
                    $units=strtoupper($value[1]);
                    $ds_type='HDD';
                    $iops=$propertie['IOPS'];
                    break;
                case 'ssd':
                    $value=explode(' ',$propertie['TITLE']);
                    $drive+=$value[0];
                    $units=strtoupper($value[1]);
                    $ds_type='SSD';
                    break;
            }
        }

        foreach (Capsule::table( 'tbladdons' )
                     ->select('tbladdons.description')
                     ->join('tblhostingaddons','tbladdons.id','=','tblhostingaddons.addonid')
                     ->where('hostingid',$serviceId)
                     ->get() as $addons){
            $addons = json_decode($addons->description,true);
            switch ($addons['GROUP']){
                case 'hdd':
                    $drive+=$addons['VALUE'];
                    break;
                case 'ssd':
                    $drive+=$addons['VALUE'];
                    break;
            }
        }

        $param = [
            [
                'login' => $dataUser->loginon,
                'password' => $dataUser->passwordon,
                'groupid' => $products->configoption1,
                'vmid' => $dataUser->vmid,
                'userid' => $dataUser->userid,
                'templateid' => $temlaitid->templateid,
                'passwd' => $results['password'],
                'release' => TRUE,
                'ansible' => $adonbool,
                'services' => $ansible,
                'serviceid' => $serviceId,
                'cpu'=>$cpu,
                'ram'=>$ram,
                'drive'=>$drive,
                'units'=>$units,
                'ds_type'=>$ds_type,
                'iops'=>$iops,
            ],
        ];
        return $param;
    }
}