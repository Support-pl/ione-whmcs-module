<?php
use WHMCS\Database\Capsule;


class ONConnect
{

    public function CurlConnect($method, $params)
    {
        $username = Capsule::table('tblconfiguration')->where('setting', 'ione_config_login')->get();
        $password = Capsule::table('tblconfiguration')->where('setting', 'ione_config_passwd')->get();
        $addr = Capsule::table('tblconfiguration')->where('setting', 'ione_address')->get();


        $data = array('params' => $params);
        $data_json = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $addr[0]->value.'/ione/' . $method);
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_USERPWD, $username[0]->value . ":" . $password[0]->value);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURL_HTTP_VERSION_1_1, 1);
        $response = curl_exec($ch);

        curl_close($ch);



        $response_start = stristr($response, '{');
        $array_answer = json_decode ($response_start);



        return $array_answer;
    }

    public function IaaS($method,$params){
        $json=$this->CurlConnect($method,$params);
        return $json;
    }

    public function Suspend($vmid,$force=false)
    {
        $param=array(
            array (
                'vmid'=>$vmid,
                'force'=>$force,
                'log'=>true
            )
        );
        $json=$this->CurlConnect("Suspend",$param);
        return $json;
    }

    public function Unsuspend($vmid)
    {
        $param=array(
            array (
                'vmid' =>$vmid
            )
        );
        $json=$this->CurlConnect("Unsuspend",$param);
        return $json;
    }

    public function Shutdown($vmid)
    {
        $param=array($vmid);
        $json=$this->CurlConnect("Shutdown",$param);
        return $json;
    }

    public function Reboot($vmid,$bool=true)
    {
        $param=array($vmid,$bool);
        $json=$this->CurlConnect("Reboot",$param);
        return $json;
    }

    public function Terminate($userid, $vmid)
    {
        $param=array($userid,$vmid);
        $json=$this->CurlConnect("Terminate",$param);
        return $json;
    }

    public function getVmByName($name)                                  // Getting VM data by name, using for button 'check'
    {
        $param=array($name);
        $json=$this->CurlConnect("get_vm_by_uname",$param);
        return $json;
    }

    public function GetIP($vmid)                                        // Getting VM IP using VMID
    {
        $param=array($vmid);
        $json=$this->CurlConnect("GetIP",$param);
        return $json;
    }

    public function Test()                                             // Checking IONe availability sending 'ping', answer should be 'pong'
    {
        $param=array("PING");
        $json=$this->CurlConnect("Test",$param);
        return $json;
    }

    public function compare_info($ips=[])                               // Getting data about all VMs from IONe
    {
        $params=array($ips);
        $json=$this->CurlConnect("compare_info",$params);
        $json_new = json_decode(json_encode($json),true);
        return $json_new;
    }

    public function createVMwithSpecs($arrayParam)                      // Creating VM from template with params
    {
        $param=array($arrayParam);
        $json=$this->CurlConnect("CreateVMwithSpecs",$param);
        return $json;
    }

    public function NewAccount($login, $pass, $templateid, $groupid, $rootpass, $trial, $services, $ansiblebool, $serviceid)
    {
        $param=array(array (
            'login'=>$login,
            'password'=>$pass,
            "groupid"=>$groupid,
            'templateid'=>$templateid,
            'passwd'=>$rootpass,
            'release'=>true,
            'trial'=>$trial,
            'ansible'=>$ansiblebool,
            'services'=>$services,
            'serviceid'=>$serviceid
        )
        );
        $json=$this->CurlConnect("NewAccount",$param);
        return $json;
    }

    public function Reinstall($params)
    {
        $json=$this->CurlConnect("Reinstall",$params);
        return $json;
    }

    public function setBackup($func_name, $params = array())
    {
        $params=array(array(
            'method'=>$func_name,
            'params'=>$params
        ));
        $json=$this->CurlConnect("FreeNASController",$params);
        return $json;
    }

    public function AnsibleController($host,$serviceName)
    {
        $params=array(array(
            'host'=>$host,
            'services' => $serviceName
        ));
        $json=$this->CurlConnect("AnsibleController",$params);
        return $json;
    }

    public function import($params)
    {
        $params=array($params);
        $json=$this->CurlConnect("IMPORT",$params);
        return $json;
    }

    public function getSnapshotList($vmid)
    {
        $json=$this->CurlConnect("GetSnapshotList",array($vmid));
        return $json;
    }

    public function MKSnapshot($vmid, $name)
    {
        $params=array($vmid,$name);
        $json=$this->CurlConnect("MKSnapshot",$params);
        return $json;
    }

    public function RMSnapshot($vmid, $snapid)
    {
        $params=array($vmid,$snapid);
        $json=$this->CurlConnect("RMSnapshot",$params);
        return $json;
    }

    public function RevSnapshot($vmid, $snapid)
    {
        $params=array($vmid,$snapid);
        $json=$this->CurlConnect("RevSnapshot",$params);
        return $json;
    }

    public function lcmStateStr($vmid)
    {
        $json=$this->CurlConnect("LCM_STATE_STR",array($vmid));
        return $json;
    }

    public function stateStr($vmid)
    {
        $json=$this->CurlConnect("STATE_STR",array($vmid));
        return $json;
    }

    public function getVmData($vmid)
    {
        $json=$this->CurlConnect("get_vm_data",array($vmid));
        return $json;
    }

    public function datastoresMonitoring($type='img')
    {
        $json=$this->CurlConnect("DatastoresMonitoring",array($type));
        return $json;
    }

    public function hostsMonitoring()
    {
        $json=$this->CurlConnect("HostsMonitoring",array());
        return $json;
    }

    public function reinstallNew($param)
    {
        $json=$this->CurlConnect("ReinstallNew",array($param));
        return $json;
    }

}