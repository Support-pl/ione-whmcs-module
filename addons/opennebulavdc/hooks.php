<?php

use WHMCS\Database\Capsule;

add_hook('AfterCronJob', 1, function($vars) {

    $connector = stristr(__DIR__,'addons',true).'servers/onconnector/lib/ONConnect.php';
    require_once($connector);

    $uidforshowback = Capsule::table('mod_iaas_user')
        ->join('tblhosting', 'mod_iaas_user.id_service', '=', 'tblhosting.id')
        ->join('tblorders', 'tblhosting.orderid', '=', 'tblorders.id')
        ->join('tblclients','mod_iaas_user.userid','=','tblclients.id')
        ->select('mod_iaas_user.id as id','tblclients.credit as credit','mod_iaas_user.total as total', 'mod_iaas_user.last_pay as last_pay', 'mod_iaas_user.useridon as uid', 'mod_iaas_user.userid as userid','mod_iaas_user.statusAlert as statusAlert', 'mod_iaas_user.id_service as id_service', 'tblhosting.domainstatus as domainstatus')
        ->where('tblhosting.domainstatus','=','Active')
        ->orWhere('tblhosting.domainstatus','=','Suspended')
        ->get();

    $on_connect = new ONConnect();
    $iaas_write_off = Capsule::table('tblconfiguration')->where('setting','write_off')->get();
    $money_write_off = $iaas_write_off[0]->value;

    foreach ($uidforshowback as $paid_ord) {

        if(!empty($paid_ord->last_pay)){

            if($paid_ord->domainstatus == 'Suspended'){
                if($paid_ord->credit > 0){
                    $params = array($paid_ord->uid);
                    $getpricetic = $on_connect->IaaS('UnsuspendUser',$params);
                    unset($params);

                    Capsule::table( 'tblhosting' )
                        ->where('id',$paid_ord->id_service)
                        ->update([
                            'domainstatus'=>'Active'
                        ]);
                    continue;
                }
            };

            $timeisnow = time();

            $params = array(
                array(
                    'uid' => $paid_ord->uid,
                    'time' => $paid_ord->last_pay * 1,
                    'balance' => $paid_ord->credit
                )
            );
            $getpricetic = $on_connect->IaaS('IaaS_Gate',$params);
            unset($params);

            $total_fin = $paid_ord->total * 1 + $getpricetic->response->showback->TOTAL * 1;

            if($total_fin > $money_write_off){
                $toch = strpos($total_fin,'.');
                $pay = substr($total_fin,0,$toch + 3);
                $total_fin_new = substr_replace($total_fin,'0.00',0,$toch+3);

                $balance = $paid_ord->credit * 1 - $pay;
                $arr_credit[] = [
                    'clientid' => $paid_ord->userid,
                    'date' => date('Y-m-d'),
                    'description' => 'debiting server '. date('Y-m-d H:i:s'),
                    'amount' => '-'.$pay,
                    'relid' => '0'
                ];

                Capsule::table('tblclients')->where('id',$paid_ord->userid)->update(['credit' => $balance]);

                Capsule::table('mod_iaas_user')->where('id',$paid_ord->id)->update(['last_pay' => $timeisnow, 'total' => $total_fin_new]);

                if($balance < 0){
                    $customfildcheck = Capsule::table('tblcustomfields')
                        ->select('id')->where('fieldname', 'Immunity')->get();
                    $castomfild = Capsule::table('tblcustomfieldsvalues')
                        ->where('relid', $paid_ord->userid)
                        ->where('fieldid', $customfildcheck[0]->id)
                        ->get();

                    if($castomfild[0]->value != 'Yes') {
                        $params = array($paid_ord->uid);
                        $getpricetic = $on_connect->IaaS('SuspendUser', $params);

                        Capsule::table('tblhosting')
                            ->where('id', $paid_ord->id_service)
                            ->update([
                                'domainstatus' => 'Suspended'
                            ]);
                    }
                }

            }else{
                Capsule::table('mod_iaas_user')->where('id',$paid_ord->id)->update(['last_pay' => $timeisnow, 'total' => $total_fin]);
            }

            if($getpricetic->response->alert == '1'){
                if($paid_ord->statusAlert == 'send'){

                }else{

                    $iaas_email = Capsule::table('tblconfiguration')->where('setting','email_iaas')->get();
                    $command = 'SendEmail';
                    $postData = array(
                        'messagename' => $iaas_email[0]->value,
                        'id' => $paid_ord->userid,
                    );
                    $results = localAPI($command, $postData);

                    Capsule::table('mod_iaas_user')->where('userid','=',$paid_ord->userid)->update(
                        array('statusAlert' => 'send')
                    );
                }

            }else{

                if($paid_ord->statusAlert == 'send'){
                    Capsule::table('mod_iaas_user')->where('userid','=',$paid_ord->userid)->update(
                        array('statusAlert' => 'nosend'));
                }
            }

        }else{

            $time = 0;
            $timeisnow = time();
            $params = array(
                array(
                    'uid' => $paid_ord->uid,
                    'time' => $paid_ord->last_pay * 1,
                    'balance' => $paid_ord->credit
                )
            );
            $getpricetic = $on_connect->IaaS('IaaS_Gate',$params);
            unset($params);

            Capsule::table('mod_iaas_user')->where("id",$paid_ord->id)
                ->update(['last_pay'=> $timeisnow, "total" => $getpricetic->response->showback->TOTAL]);
        }
    }

    if($arr_credit != NULL) {
        Capsule::table('tblcredit')->insert($arr_credit);
    }

});


