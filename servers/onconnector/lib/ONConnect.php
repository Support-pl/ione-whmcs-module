<?php
use WHMCS\Database\Capsule;

ini_set('display_errors', 0);

class ONConnect
{
    private $context;
    private $socket;
    private $dsn;
    private $debug;

    public function __construct($ip=false,$port=8008,$debugMod=false)
    {
        if(!class_exists('ZMQContext')){
            logModuleCall(__CLASS__,__FUNCTION__,'error', $this->dsn, 'Failed to load class ZMQContext');
        }
        if(!$ip) {
            $ip = Capsule::table('tblconfiguration')->where('setting', 'ione_config_host')->get();
            $ip=$ip[0]->value;
        };
        if(!$port) {
            $port = Capsule::table('tblconfiguration')->where('setting', 'ione_config_port')->get();
            $port=$port[0]->value;
        }
        $this->debug=$debugMod;
        $this->dsn="tcp://".$ip.":".$port;
    }

    private function farmJson($method,$params)
    {
        $message= array (
            "jsonrpc"=>"2.0",
            "method"=>$method,
            "params"=>$params,
            "id"=>rand(0,999),
        );
        return json_encode($message);
    }

    private function closeConnection(){
        $endpoints = $this->socket->getEndpoints();
        if (!in_array($this->dsn, $endpoints['connect'])) {
            $this->socket->disconnect($this->dsn);
        }
    }

    private function logParams($params,$message=null)
    {
        logModuleCall(
            __CLASS__,
            __FUNCTION__,
            $this->dsn, $params, $message
        );
        return '{"id":970,"jsonrpc":"2.0","result":{"userid":666,"vmid":666,"ip":"255.255.255.255"}}';
    }

    private function zmqCommunication($method,$params,$persistenId='onConnector')
    {
        $json = $this->farmJson($method,$params);

        try {
            $this->context = new ZMQContext();
            $this->socket = $this->context->getSocket(ZMQ::SOCKET_REQ, 'ZMQSockIone');
            $this->socket->setSockOpt(ZMQ::SOCKOPT_RCVTIMEO,-1);
            $this->socket->setSockOpt(ZMQ::SOCKOPT_SNDTIMEO,10000);
            $endpoints = $this->socket->getEndpoints();
            if (!in_array($this->dsn, $endpoints['connect'])) {
                $this->socket->connect($this->dsn);
            }
            if($this->debug){
                $message=$this->logParams($json);
            }else {
                $this->socket->send($json);
                $message = $this->socket->recv();
                $this->logParams($json,$message);
            }
            $this->closeConnection();
            return $message;
        } catch (Exception $exception){
            $this->closeConnection();
                logModuleCall(
                    __CLASS__,
                    __FUNCTION__,
                    $exception, $this->dsn, 'Information could not be retrieved because the server was not reachable'
                );
                return ['error'=>true,
                        'errorMessage'=>'Information could not be retrieved because the server was not reachable'];
        }
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
        $json=$this->zmqCommunication("Suspend",$param);
        return json_decode($json,true);
    }

    public function Unsuspend($vmid)
    {
        $param=array(
            array (
                'vmid' =>$vmid
            )
        );
        $json=$this->zmqCommunication("Unsuspend",$param);
        return json_decode($json,true);
    }

    public function Shutdown($vmid)
    {
        $param=array($vmid);
        $json=$this->zmqCommunication("Shutdown",$param);
        return json_decode($json,true);
    }

    public function Reboot($vmid,$bool=true)
    {
        $param=array($vmid,$bool);
        $json=$this->zmqCommunication("Reboot",$param);
        return json_decode($json,true);
    }

    public function Terminate($userid, $vmid)
    {
        $param=array($userid,$vmid);
        $json=$this->zmqCommunication("Terminate",$param);
        return json_decode($json,true);
    }
    public function TerminateIaas($uid)
    {
        $param=array($uid);
        $json=$this->zmqCommunication("UserDelete",$param);
        return json_decode($json,true);
    }

    public function getVmByName($name)                                  // Getting VM data by name, using for button 'check'
    {
        $param=array($name);
        $json=$this->zmqCommunication("get_vm_by_uname",$param);
        return json_decode($json,true);
    }

    public function GetIP($vmid)                                        // Getting VM IP using VMID
    {
        $param=array($vmid);
        $json=$this->zmqCommunication("GetIP",$param);
        return json_decode($json,true);
    }

    public function Test($param=array("PING"))                          // Checking IONe availability sending 'ping', answer should be 'pong'
    {
        $json=$this->zmqCommunication("Test",$param);
        return json_decode($json,true);
    }

    public function compare_info($ips=[])                               // Getting data about all VMs from IONe
    {
        $params=array($ips);
        $json=$this->zmqCommunication("compare_info",$params);
        return json_decode($json,true);
    }

    public function createVMwithSpecs($arrayParam)                      // Creating VM from template with params
    {
        $param=array($arrayParam);
        $json=$this->zmqCommunication("CreateVMwithSpecs",$param);
        return json_decode($json,true);
    }

    public function UserCreateIaaS($arrayParam)                      // Creating iaas user
    {

        $param=array( $arrayParam['login'],$arrayParam['pass'],$arrayParam['groupid'],$arrayParam['locale']);
        $json=$this->zmqCommunication("UserCreate",$param);
        return json_decode($json,true);
    }



    public function RetrieveShowback($uid,$time,$balance)                      // get date about iaas users
    {
        $checkiaas = Capsule::table('tbladdonmodules')->select('*')->where('module','=','opennebulavdc')->get();
        if($checkiaas) {
            $param['uid'] = $uid;
            $param['time'] = $time;
            $param['balance'] = $balance;

            $params = array(
                array(
                'uid' => $uid,
                'time' => $time,
                'balance' => $balance
                ),
            );

            logModuleCall(
                'onconnector',
                __FUNCTION__,
                'test',
                $params
            );
            $json = $this->zmqCommunication("IaaS_Gate", $params);
        }

        return json_decode($json,true);
    }

    public function SuspendUserIaas($uid){
        $checkiaas = Capsule::table('tbladdonmodules')->select('*')->where('module','=','opennebulavdc')->get();
        if($checkiaas) {
            $params = array($uid);
            $json = $this->zmqCommunication("SuspendUser", $params);
            return json_decode($json,true);
        }
    }

    public function UnSuspendUserIaas($uid){
        $checkiaas = Capsule::table('tbladdonmodules')->select('*')->where('module','=','opennebulavdc')->get();
        if($checkiaas) {
            $params = array($uid);
            $json = $this->zmqCommunication("UnsuspendUser", $params);
            return json_decode($json,true);
        }
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
        $json=$this->zmqCommunication("NewAccount",$param);
        return json_decode($json,true);
    }

    public function Reinstall($params)
    {
        $json=$this->zmqCommunication("Reinstall",$params);
        return json_decode($json,true);
    }

    public function setBackup($func_name, $params = array())
    {
        $params=array(array(
            'method'=>$func_name,
            'params'=>$params
        ));
        $json=$this->zmqCommunication("FreeNASController",$params);
        return json_decode($json,true);
    }

    public function AnsibleController($host,$serviceName)
    {
        $params=array(array(
            'host'=>$host,
            'services' => $serviceName
        ));
        $json=$this->zmqCommunication("AnsibleController",$params);
        return json_decode($json,true);
    }

    public function import($params)
    {
        $params=array($params);
        $json=$this->zmqCommunication("IMPORT",$params);
        return json_decode($json,true);
    }

    public function getSnapshotList($vmid)
    {
        $json=$this->zmqCommunication("GetSnapshotList",array($vmid));
        return json_decode($json,true);
    }

    public function MKSnapshot($vmid, $name)
    {
        $params=array($vmid,$name);
        $json=$this->zmqCommunication("MKSnapshot",$params);
        return json_decode($json,true);
    }

    public function RMSnapshot($vmid, $snapid)
    {
        $params=array($vmid,$snapid);
        $json=$this->zmqCommunication("RMSnapshot",$params);
        return json_decode($json,true);
    }

    public function RevSnapshot($vmid, $snapid)
    {
        $params=array($vmid,$snapid);
        $json=$this->zmqCommunication("RevSnapshot",$params);
        return json_decode($json,true);
    }

    public function lcmStateStr($vmid)
    {
        $json=$this->zmqCommunication("LCM_STATE_STR",array($vmid));
        return json_decode($json,true);
    }

    public function stateStr($vmid)
    {
        $json=$this->zmqCommunication("STATE_STR",array($vmid));
        return json_decode($json,true);
    }

    public function getVmData($vmid)
    {
        $json=$this->zmqCommunication("get_vm_data",array($vmid));
        return json_decode($json,true);
    }

    public function datastoresMonitoring($type='img')
    {
        $json=$this->zmqCommunication("DatastoresMonitoring",array($type));
        return json_decode($json,true);
    }

    public function hostsMonitoring()
    {
        $json=$this->zmqCommunication("HostsMonitoring",array());
        return json_decode($json,true);
    }

    public function reinstallNew($param)
    {
        $json=$this->zmqCommunication("ReinstallNew",array($param));
        return json_decode($json,true);
    }
}
