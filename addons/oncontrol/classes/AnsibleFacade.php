<?php
use WHMCS\Database\Capsule;

class AnsibleFacade{
    protected $LANG;
    private $comand;
    private $vars;
    private $serviceId;
    private $mainModuleActivate;
    private $updateDate;
    public function __construct($vars)
    {
        $this->vars = $vars;
        require_once ($_SERVER['DOCUMENT_ROOT'].'/modules/addons/oncontrol/classes/AnsibleComand.php');
        require_once ($_SERVER['DOCUMENT_ROOT'].'/modules/addons/oncontrol/classes/ActivateAnsible.php');
        require_once($_SERVER['DOCUMENT_ROOT'] . '/modules/servers/onconnector/lib/ONConnect.php');
        $this->onconnect = new ONConnect();
        $this->mainModuleActivate = new MainModuleActivate($this->vars["modulelink"]);
        $this->comand = new AnsibleComand();
        $this->LANG = $this->vars['_lang'];
    }

    public function requireFile($fileName){
        require_once ($_SERVER['DOCUMENT_ROOT'].'/modules/addons/oncontrol/pages/ansible/'.$fileName);
    }
    public function addAnsibleOnExternalLink(){
        $ids=$this->comand->getIds();
        $this->mainModuleActivate->setIds($ids);
        $this->mainModuleActivate->activate($_GET['serviceId'],$this->LANG);
    }
    public function reinstallComoareInfo(){
        $ids=$this->comand->getIds();
        $this->mainModuleActivate->setIds($ids);
        $this->mainModuleActivate->activate($_GET['serviceId'],$this->LANG);
    }
    public function fullReinstall(){
        $ids=getPostIds();
        $activate = new MainModuleActivate($ids, $this->vars["modulelink"]);
        $param=$activate->comparateInfo();
        require_once($_SERVER['DOCUMENT_ROOT'] . '/modules/servers/onconnector/lib/ONConnect.php');
        $onconnect = new ONConnect();
        $result = $onconnect->Reinstall($param);
        try {
            Capsule::beginTransaction();
            if(count($_POST['variable'])){
                $ansibles=array_keys($_POST['variable']);

                $allNewAddons=Capsule::table('mod_onconfiguratorAddon')
                    ->whereIn('id',$ansibles)
                    ->lists('id');

                foreach ($allNewAddons as $newAddonId){
                    firstOrUpdate($_POST['serviceId'],$newAddonId);
                }

                Capsule::table('tblhosting')
                ->where('id',$_POST['serviceId'])
                    ->update(['packageid'=>$_POST['idNewProduct']]);
            }

            if ($result['result']['vmid']) {
                $dataUser = Capsule::table('mod_on_user')
                    ->where('id_service', $_POST['serviceId'])
                    ->update(['vmid' => $result['result']['vmid']]);
                printSuccess('Успех:', 'Процесс reinstall запущен');
            }
            else {
                logModuleCall(
                    __CLASS__,
                    __FUNCTION__,
                    $result, $result, $allNewAddons
                );
                Capsule::rollback();
            }
            Capsule::commit();
        }catch (Exception $e){
            logModuleCall(
                'name',
                'full_name',
                $e, $result, $allNewAddons
            );
            Capsule::rollback();
        }
    }
    public function add(){
        if (isset($_POST['save'])) {
            $this->comand->preProcessing($_POST,$this->LANG);
            extract($_POST);
        }
        $this->requireFile('interface.php');
    }
    public function allTable(){
        preProcessing($_POST);
    }
    public function activation(){
        $ids=$this->comand->getIds();
        $this->mainModuleActivate->setIds($ids);
        $this->mainModuleActivate->activate($_GET['serviceId'],$this->LANG);
        pageActivation();
    }
    public function change(){
        if (is_array($_POST['upgrade'])) {
            if (isset($_POST['name'])) {
                $this->comand->updateAnsible($_POST);
            }
            $this->updateDate = Capsule::table('mod_onconfiguratorAddon')
                    ->where('id', key($_POST['upgrade']))
                    ->first();
            $this->updateDate->check = explode('/', $this->updateDate->os);
            $this->requireFile('interface.php');
        } elseif (isset($_POST['del'])) {
            $this->comand->deleteAnsible($_POST,$this->LANG);
            $this->requireFile('allansible.php');
        } elseif (isset($_POST['activate'])){
              $ids=$this->comand->getPostIds();
              $this->mainModuleActivate->setIds($ids);
              $this->mainModuleActivate->activate($_GET['serviceId'],$this->LANG);

        }elseif(!isset($_GET['serviceId'])){
            extract($_POST);
            $this->requireFile('interface.php');
        }
    }
    public function fullActivation(){

        $this->mainModuleActivate->fullActivate($_POST['IP'], $_POST['variable'], $_POST['port']);
            if ($this->onconnect->AnsibleController($this->mainModuleActivate->getAnsibleHost(),$this->mainModuleActivate->getArrayResult())) {
                $this->comand->printSuccess("Успех:", "Данные успешно переданы");
            } else{
                $this->comand->printError("Ошибка", "ON не ответила на запрос, или ответ является пустым.");
            }
    }
}