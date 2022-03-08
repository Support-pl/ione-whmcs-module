<?php
if( !defined( "WHMCS" ) )
    die( "This file cannot be accessed directly" );
use WHMCS\Database\Capsule;

?>

    <script>
        $( document ).ready(function(){
            $("#add_disk" ).click(function(){
                $('.type_disk:last').after('<br><br><input type="text" name="disk[]" class="type_disk">');
            });
        });
    </script>


    <form method="post">
        <h3>Standard product package configuration</h3>
        <span>Enter the disc type:</span><br><br>
        <div class="input_disk">
            <input type="text" name="disk[]" class="type_disk">
        </div>
        <span id="add_disk" style="cursor: pointer;color: #337ab7;font-weight: 600;font-size: 15px;">Add more</span>
        <br><br>

        <label for="groud_hidden" style="cursor: pointer;width: 220px;">Create groups hidden?</label><input type="checkbox" name="groud_hidden" id="groud_hidden"><br>
        <label for="product_hidden" style="cursor: pointer;width: 220px;">Create hidden products?</label><input type="checkbox" name="product_hidden" id="product_hidden"><br>
        <br>

        <label for="disk_size">Select the minimum disk size:</label>
        <input type="number" name="disk_size"><br><br>

        <label for="node">Select the node type:</label>
        <select name="node">
            <option value="pachage1">Choose one of the options (variants?)</option>
            <option value="pachage2">vCenter</option>
            <option value="pachage3">KVM</option>
        </select>
        <br>

        <input type="submit" style="width: 200px;height: 30px;background: #fff;border: 1px solid;" value="Create">
    </form>

<?php


if($_REQUEST['disk'][0] != NULL) {

    $hidden = 1;
    $order_form = 'standard_cart';

    $time = date('Y-m-d H:i:s');
    $last_order_temp = Capsule::table('tblproductgroups')->orderBy('order', 'desc')->limit(1)->select('order')->get();
    $last_order = $last_order_temp[0]->order;
    $last_prod_temp = Capsule::table('tblproducts')->orderBy('id', 'desc')->limit(1)->select('id')->get();
    $last_prod = $last_prod_temp[0]->id;
    foreach ($_REQUEST['disk'] as $item) {
        $last_order++;
        $group_array[] = array(
            'name' => 'VDS ' . $item,
            'slug' => 'VDS ' . $item,
            'headline' => '',
            'tagline' => '',
            'orderfrmtpl' => $order_form,
            'disabledgateways' => '',
            'hidden' => $_REQUEST['groud_hidden'] == 'on' ? '1' : '0',
            'order' => $last_order,
            'created_at' => $time,
            'updated_at' => $time
        );
    }


    Capsule::table('tblproductgroups')->insert($group_array);


    $i_count = 1;
    if(!$last_order_temp[0]->order){
        foreach ($_REQUEST['disk'] as $item) {
            $new_group[]->id = $i_count;
            $i_count++;
        }

    }else {
        $new_group = Capsule::table('tblproductgroups')->where('order', '>', $last_order_temp[0]->order)->get();
    }

    $i = 0;
    if($last_prod == NULL){
        $last_prod = 0;
    }

    foreach ($new_group as $groups) {
        //standart prod
        $product_array[] = array(
            'type' => 'other',
            'gid' => $groups->id,
            'name' => 'VDS M ' . $_REQUEST['disk'][$i],
            'description' => 'CPU: 1
RAM: 2
'.$_REQUEST['disk'][$i].': '.$_REQUEST['disk_size'].'
            ',
            'hidden' => $_REQUEST['product_hidden'] == 'on' ? '1' : '0',
            'paytype' => 'recurring',
            'servertype' => 'onconnector',
            'configoption1' => 1,
            'configoption2' => 2,
            'configoption3' => $_REQUEST['disk'][$i],
            'configoption4' => $_REQUEST['disk_size'],
            'configoption5' => $_REQUEST['node'],
            "autosetup" => 'payment',
            'order' => '1'
        );

        $last_prod++;
        $product_price[] = array(
            'type' => 'product',
            'currency' => '1',
            'relid' => $last_prod,
            'msetupfee' => '0.00',
            'qsetupfee' => '0.00',
            'ssetupfee' => '0.00',
            'asetupfee' => '0.00',
            'bsetupfee' => '0.00',
            'tsetupfee' => '0.00',
            'monthly' => '5.00',
            'quarterly' => '-1.00',
            'semiannually' => '-1.00',
            'annually' => '-1.00',
            'biennially' => '-1.00',
            'triennially' => '-1.00',
        );


        $product_array[] = array(
            'type' => 'other',
            'gid' => $groups->id,
            'name' => 'VDS L ' . $_REQUEST['disk'][$i],
            'description' => 'CPU: 2
RAM: 4
'.$_REQUEST['disk'][$i].': '.$_REQUEST['disk_size'].'
            ',
            'hidden' => $_REQUEST['product_hidden'] == 'on' ? '1' : '0',
            'paytype' => 'recurring',
            'servertype' => 'onconnector',
            'configoption1' => 2,
            'configoption2' => 4,
            'configoption3' => $_REQUEST['disk'][$i],
            'configoption4' => $_REQUEST['disk_size'],
            'configoption5' => $_REQUEST['node'],
            "autosetup" => 'payment',
            'order' => '2'
        );
        $last_prod++;
        $product_price[] = array(
            'type' => 'product',
            'currency' => '1',
            'relid' => $last_prod,
            'msetupfee' => '0.00',
            'qsetupfee' => '0.00',
            'ssetupfee' => '0.00',
            'asetupfee' => '0.00',
            'bsetupfee' => '0.00',
            'tsetupfee' => '0.00',
            'monthly' => '10.00',
            'quarterly' => '-1.00',
            'semiannually' => '-1.00',
            'annually' => '-1.00',
            'biennially' => '-1.00',
            'triennially' => '-1.00',
        );

        $product_array[] = array(
            'type' => 'other',
            'gid' => $groups->id,
            'name' => 'VDS XL ' . $_REQUEST['disk'][$i],
            'description' => 'CPU: 3
RAM: 6
'.$_REQUEST['disk'][$i].': '.$_REQUEST['disk_size'].'
            ',
            'hidden' => $_REQUEST['product_hidden'] == 'on' ? '1' : '0',
            'paytype' => 'recurring',
            'servertype' => 'onconnector',
            'configoption1' => 3,
            'configoption2' => 6,
            'configoption3' => $_REQUEST['disk'][$i],
            'configoption4' => $_REQUEST['disk_size'],
            'configoption5' => $_REQUEST['node'],
            "autosetup" => 'payment',
            'order' => '3'
        );
        $last_prod++;
        $product_price[] = array(
            'type' => 'product',
            'currency' => '1',
            'relid' => $last_prod,
            'msetupfee' => '0.00',
            'qsetupfee' => '0.00',
            'ssetupfee' => '0.00',
            'asetupfee' => '0.00',
            'bsetupfee' => '0.00',
            'tsetupfee' => '0.00',
            'monthly' => '15.00',
            'quarterly' => '-1.00',
            'semiannually' => '-1.00',
            'annually' => '-1.00',
            'biennially' => '-1.00',
            'triennially' => '-1.00',
        );

        $product_array[] = array(
            'type' => 'other',
            'gid' => $groups->id,
            'name' => 'VDS XXL ' . $_REQUEST['disk'][$i],
            'description' => 'CPU: 4
RAM: 8
'.$_REQUEST['disk'][$i].': '.$_REQUEST['disk_size'].'
            ',
            'hidden' => $_REQUEST['product_hidden'] == 'on' ? '1' : '0',
            'paytype' => 'recurring',
            'servertype' => 'onconnector',
            'configoption1' => 4,
            'configoption2' => 8,
            'configoption3' => $_REQUEST['disk'][$i],
            'configoption4' => $_REQUEST['disk_size'],
            'configoption5' => $_REQUEST['node'],
            "autosetup" => 'payment',
            'order' => '4'
        );

        $last_prod++;
        $product_price[] = array(
            'type' => 'product',
            'currency' => '1',
            'relid' => $last_prod,
            'msetupfee' => '0.00',
            'qsetupfee' => '0.00',
            'ssetupfee' => '0.00',
            'asetupfee' => '0.00',
            'bsetupfee' => '0.00',
            'tsetupfee' => '0.00',
            'monthly' => '20.00',
            'quarterly' => '-1.00',
            'semiannually' => '-1.00',
            'annually' => '-1.00',
            'biennially' => '-1.00',
            'triennially' => '-1.00',
        );

        $product_array[] = array(
            'type' => 'other',
            'gid' => $groups->id,
            'name' => 'VDS 3XL ' . $_REQUEST['disk'][$i],
            'description' => 'CPU: 6
RAM: 12
'.$_REQUEST['disk'][$i].': '.$_REQUEST['disk_size'].'
            ',
            'hidden' => $_REQUEST['product_hidden'] == 'on' ? '1' : '0',
            'paytype' => 'recurring',
            'servertype' => 'onconnector',
            'configoption1' => 6,
            'configoption2' => 12,
            'configoption3' => $_REQUEST['disk'][$i],
            'configoption4' => $_REQUEST['disk_size'],
            'configoption5' => $_REQUEST['node'],
            "autosetup" => 'payment',
            'order' => '5'
        );

        $last_prod++;
        $product_price[] = array(
            'type' => 'product',
            'currency' => '1',
            'relid' => $last_prod,
            'msetupfee' => '0.00',
            'qsetupfee' => '0.00',
            'ssetupfee' => '0.00',
            'asetupfee' => '0.00',
            'bsetupfee' => '0.00',
            'tsetupfee' => '0.00',
            'monthly' => '25.00',
            'quarterly' => '-1.00',
            'semiannually' => '-1.00',
            'annually' => '-1.00',
            'biennially' => '-1.00',
            'triennially' => '-1.00',
        );


        $product_array[] = array(
            'type' => 'other',
            'gid' => $groups->id,
            'name' => 'VDS 4XL ' . $_REQUEST['disk'][$i],
            'description' => 'CPU: 8
RAM: 16
'.$_REQUEST['disk'][$i].': '.$_REQUEST['disk_size'].'
            ',
            'hidden' => $_REQUEST['product_hidden'] == 'on' ? '1' : '0',
            'paytype' => 'recurring',
            'servertype' => 'onconnector',
            'configoption1' => 8,
            'configoption2' => 16,
            'configoption3' => $_REQUEST['disk'][$i],
            'configoption4' => $_REQUEST['disk_size'],
            'configoption5' => $_REQUEST['node'],
            "autosetup" => 'payment',
            'order' => '6'
        );

        $last_prod++;
        $product_price[] = array(
            'type' => 'product',
            'currency' => '1',
            'relid' => $last_prod,
            'msetupfee' => '0.00',
            'qsetupfee' => '0.00',
            'ssetupfee' => '0.00',
            'asetupfee' => '0.00',
            'bsetupfee' => '0.00',
            'tsetupfee' => '0.00',
            'monthly' => '30.00',
            'quarterly' => '-1.00',
            'semiannually' => '-1.00',
            'annually' => '-1.00',
            'biennially' => '-1.00',
            'triennially' => '-1.00',
        );


        $product_array[] = array(
            'type' => 'other',
            'gid' => $groups->id,
            'name' => 'VDS 5XL ' . $_REQUEST['disk'][$i],
            'description' => 'CPU: 10
RAM: 24
'.$_REQUEST['disk'][$i].': '.$_REQUEST['disk_size'].'
            ',
            'hidden' => $_REQUEST['product_hidden'] == 'on' ? '1' : '0',
            'paytype' => 'recurring',
            'servertype' => 'onconnector',
            'configoption1' => 10,
            'configoption2' => 24,
            'configoption3' => $_REQUEST['disk'][$i],
            'configoption4' => $_REQUEST['disk_size'],
            'configoption5' => $_REQUEST['node'],
            "autosetup" => 'payment',
            'order' => '7'
        );

        $last_prod++;
        $product_price[] = array(
            'type' => 'product',
            'currency' => '1',
            'relid' => $last_prod,
            'msetupfee' => '0.00',
            'qsetupfee' => '0.00',
            'ssetupfee' => '0.00',
            'asetupfee' => '0.00',
            'bsetupfee' => '0.00',
            'tsetupfee' => '0.00',
            'monthly' => '35.00',
            'quarterly' => '-1.00',
            'semiannually' => '-1.00',
            'annually' => '-1.00',
            'biennially' => '-1.00',
            'triennially' => '-1.00',
        );


        //x2cpu
        $product_array[] = array(
            'type' => 'other',
            'gid' => $groups->id,
            'name' => 'VDS M X2CPU ' . $_REQUEST['disk'][$i],
            'description' => 'CPU: 2
RAM: 2
'.$_REQUEST['disk'][$i].': '.$_REQUEST['disk_size'].'
            ',
            'hidden' => $_REQUEST['product_hidden'] == 'on' ? '1' : '0',
            'paytype' => 'recurring',
            'servertype' => 'onconnector',
            'configoption1' => 2,
            'configoption2' => 2,
            'configoption3' => $_REQUEST['disk'][$i],
            'configoption4' => $_REQUEST['disk_size'],
            'configoption5' => $_REQUEST['node'],
            "autosetup" => 'payment',
            'order' => '8'
        );

        $last_prod++;
        $product_price[] = array(
            'type' => 'product',
            'currency' => '1',
            'relid' => $last_prod,
            'msetupfee' => '0.00',
            'qsetupfee' => '0.00',
            'ssetupfee' => '0.00',
            'asetupfee' => '0.00',
            'bsetupfee' => '0.00',
            'tsetupfee' => '0.00',
            'monthly' => '10.00',
            'quarterly' => '-1.00',
            'semiannually' => '-1.00',
            'annually' => '-1.00',
            'biennially' => '-1.00',
            'triennially' => '-1.00',
        );

        $product_array[] = array(
            'type' => 'other',
            'gid' => $groups->id,
            'name' => 'VDS L X2CPU ' . $_REQUEST['disk'][$i],
            'description' => 'CPU: 4
RAM: 4
'.$_REQUEST['disk'][$i].': '.$_REQUEST['disk_size'].'
            ',
            'hidden' => $_REQUEST['product_hidden'] == 'on' ? '1' : '0',
            'paytype' => 'recurring',
            'servertype' => 'onconnector',
            'configoption1' => 4,
            'configoption2' => 4,
            'configoption3' => $_REQUEST['disk'][$i],
            'configoption4' => $_REQUEST['disk_size'],
            'configoption5' => $_REQUEST['node'],
            "autosetup" => 'payment',
            'order' => '9'
        );

        $last_prod++;
        $product_price[] = array(
            'type' => 'product',
            'currency' => '1',
            'relid' => $last_prod,
            'msetupfee' => '0.00',
            'qsetupfee' => '0.00',
            'ssetupfee' => '0.00',
            'asetupfee' => '0.00',
            'bsetupfee' => '0.00',
            'tsetupfee' => '0.00',
            'monthly' => '15.00',
            'quarterly' => '-1.00',
            'semiannually' => '-1.00',
            'annually' => '-1.00',
            'biennially' => '-1.00',
            'triennially' => '-1.00',
        );


        $product_array[] = array(
            'type' => 'other',
            'gid' => $groups->id,
            'name' => 'VDS XL X2CPU ' . $_REQUEST['disk'][$i],
            'description' => 'CPU: 6
RAM: 6
'.$_REQUEST['disk'][$i].': '.$_REQUEST['disk_size'].'
            ',
            'hidden' => $_REQUEST['product_hidden'] == 'on' ? '1' : '0',
            'paytype' => 'recurring',
            'servertype' => 'onconnector',
            'configoption1' => 6,
            'configoption2' => 6,
            'configoption3' => $_REQUEST['disk'][$i],
            'configoption4' => $_REQUEST['disk_size'],
            'configoption5' => $_REQUEST['node'],
            "autosetup" => 'payment',
            'order' => '10'
        );

        $last_prod++;
        $product_price[] = array(
            'type' => 'product',
            'currency' => '1',
            'relid' => $last_prod,
            'msetupfee' => '0.00',
            'qsetupfee' => '0.00',
            'ssetupfee' => '0.00',
            'asetupfee' => '0.00',
            'bsetupfee' => '0.00',
            'tsetupfee' => '0.00',
            'monthly' => '20.00',
            'quarterly' => '-1.00',
            'semiannually' => '-1.00',
            'annually' => '-1.00',
            'biennially' => '-1.00',
            'triennially' => '-1.00',
        );


        $product_array[] = array(
            'type' => 'other',
            'gid' => $groups->id,
            'name' => 'VDS XXL X2CPU ' . $_REQUEST['disk'][$i],
            'description' => 'CPU: 8
RAM: 8
'.$_REQUEST['disk'][$i].': '.$_REQUEST['disk_size'].'
            ',
            'hidden' => $_REQUEST['product_hidden'] == 'on' ? '1' : '0',
            'paytype' => 'recurring',
            'servertype' => 'onconnector',
            'configoption1' => 8,
            'configoption2' => 8,
            'configoption3' => $_REQUEST['disk'][$i],
            'configoption4' => $_REQUEST['disk_size'],
            'configoption5' => $_REQUEST['node'],
            "autosetup" => 'payment',
            'order' => '11'
        );

        $last_prod++;
        $product_price[] = array(
            'type' => 'product',
            'currency' => '1',
            'relid' => $last_prod,
            'msetupfee' => '0.00',
            'qsetupfee' => '0.00',
            'ssetupfee' => '0.00',
            'asetupfee' => '0.00',
            'bsetupfee' => '0.00',
            'tsetupfee' => '0.00',
            'monthly' => '25.00',
            'quarterly' => '-1.00',
            'semiannually' => '-1.00',
            'annually' => '-1.00',
            'biennially' => '-1.00',
            'triennially' => '-1.00',
        );


        $product_array[] = array(
            'type' => 'other',
            'gid' => $groups->id,
            'name' => 'VDS 3XL X2CPU ' . $_REQUEST['disk'][$i],
            'description' => 'CPU: 12
RAM: 12
'.$_REQUEST['disk'][$i].': '.$_REQUEST['disk_size'].'
            ',
            'hidden' => $_REQUEST['product_hidden'] == 'on' ? '1' : '0',
            'paytype' => 'recurring',
            'servertype' => 'onconnector',
            'configoption1' => 12,
            'configoption2' => 12,
            'configoption3' => $_REQUEST['disk'][$i],
            'configoption4' => $_REQUEST['disk_size'],
            'configoption5' => $_REQUEST['node'],
            "autosetup" => 'payment',
            'order' => '12'
        );

        $last_prod++;
        $product_price[] = array(
            'type' => 'product',
            'currency' => '1',
            'relid' => $last_prod,
            'msetupfee' => '0.00',
            'qsetupfee' => '0.00',
            'ssetupfee' => '0.00',
            'asetupfee' => '0.00',
            'bsetupfee' => '0.00',
            'tsetupfee' => '0.00',
            'monthly' => '30.00',
            'quarterly' => '-1.00',
            'semiannually' => '-1.00',
            'annually' => '-1.00',
            'biennially' => '-1.00',
            'triennially' => '-1.00',
        );


        $product_array[] = array(
            'type' => 'other',
            'gid' => $groups->id,
            'name' => 'VDS 4XL X2CPU ' . $_REQUEST['disk'][$i],
            'description' => 'CPU: 16
RAM: 16
'.$_REQUEST['disk'][$i].': '.$_REQUEST['disk_size'].'
            ',
            'hidden' => $_REQUEST['product_hidden'] == 'on' ? '1' : '0',
            'paytype' => 'recurring',
            'servertype' => 'onconnector',
            'configoption1' => 16,
            'configoption2' => 16,
            'configoption3' => $_REQUEST['disk'][$i],
            'configoption4' => $_REQUEST['disk_size'],
            'configoption5' => $_REQUEST['node'],
            "autosetup" => 'payment',
            'order' => '13'
        );

        $last_prod++;
        $product_price[] = array(
            'type' => 'product',
            'currency' => '1',
            'relid' => $last_prod,
            'msetupfee' => '0.00',
            'qsetupfee' => '0.00',
            'ssetupfee' => '0.00',
            'asetupfee' => '0.00',
            'bsetupfee' => '0.00',
            'tsetupfee' => '0.00',
            'monthly' => '35.00',
            'quarterly' => '-1.00',
            'semiannually' => '-1.00',
            'annually' => '-1.00',
            'biennially' => '-1.00',
            'triennially' => '-1.00',
        );


        $product_array[] = array(
            'type' => 'other',
            'gid' => $groups->id,
            'name' => 'VDS 5XL X2CPU ' . $_REQUEST['disk'][$i],
            'description' => 'CPU: 20
RAM: 24
'.$_REQUEST['disk'][$i].': '.$_REQUEST['disk_size'].'
            ',
            'hidden' => $_REQUEST['product_hidden'] == 'on' ? '1' : '0',
            'paytype' => 'recurring',
            'servertype' => 'onconnector',
            'configoption1' => 20,
            'configoption2' => 24,
            'configoption3' => $_REQUEST['disk'][$i],
            'configoption4' => $_REQUEST['disk_size'],
            'configoption5' => $_REQUEST['node'],
            "autosetup" => 'payment',
            'order' => '14'
        );

        $last_prod++;
        $product_price[] = array(
            'type' => 'product',
            'currency' => '1',
            'relid' => $last_prod,
            'msetupfee' => '0.00',
            'qsetupfee' => '0.00',
            'ssetupfee' => '0.00',
            'asetupfee' => '0.00',
            'bsetupfee' => '0.00',
            'tsetupfee' => '0.00',
            'monthly' => '40.00',
            'quarterly' => '-1.00',
            'semiannually' => '-1.00',
            'annually' => '-1.00',
            'biennially' => '-1.00',
            'triennially' => '-1.00',
        );



        //x2ram
        $product_array[] = array(
            'type' => 'other',
            'gid' => $groups->id,
            'name' => 'VDS M X2RAM ' . $_REQUEST['disk'][$i],
            'description' => 'CPU: 1
RAM: 4
'.$_REQUEST['disk'][$i].': '.$_REQUEST['disk_size'].'
            ',
            'hidden' => $_REQUEST['product_hidden'] == 'on' ? '1' : '0',
            'paytype' => 'recurring',
            'servertype' => 'onconnector',
            'configoption1' => 1,
            'configoption2' => 4,
            'configoption3' => $_REQUEST['disk'][$i],
            'configoption4' => $_REQUEST['disk_size'],
            'configoption5' => $_REQUEST['node'],
            "autosetup" => 'payment',
            'order' => '15'
        );

        $last_prod++;
        $product_price[] = array(
            'type' => 'product',
            'currency' => '1',
            'relid' => $last_prod,
            'msetupfee' => '0.00',
            'qsetupfee' => '0.00',
            'ssetupfee' => '0.00',
            'asetupfee' => '0.00',
            'bsetupfee' => '0.00',
            'tsetupfee' => '0.00',
            'monthly' => '15.00',
            'quarterly' => '-1.00',
            'semiannually' => '-1.00',
            'annually' => '-1.00',
            'biennially' => '-1.00',
            'triennially' => '-1.00',
        );


        $product_array[] = array(
            'type' => 'other',
            'gid' => $groups->id,
            'name' => 'VDS L X2RAM ' . $_REQUEST['disk'][$i],
            'description' => 'CPU: 2
RAM: 8
'.$_REQUEST['disk'][$i].': '.$_REQUEST['disk_size'].'
            ',
            'hidden' => $_REQUEST['product_hidden'] == 'on' ? '1' : '0',
            'paytype' => 'recurring',
            'servertype' => 'onconnector',
            'configoption1' => 2,
            'configoption2' => 8,
            'configoption3' => $_REQUEST['disk'][$i],
            'configoption4' => $_REQUEST['disk_size'],
            'configoption5' => $_REQUEST['node'],
            "autosetup" => 'payment',
            'order' => '16'
        );

        $last_prod++;
        $product_price[] = array(
            'type' => 'product',
            'currency' => '1',
            'relid' => $last_prod,
            'msetupfee' => '0.00',
            'qsetupfee' => '0.00',
            'ssetupfee' => '0.00',
            'asetupfee' => '0.00',
            'bsetupfee' => '0.00',
            'tsetupfee' => '0.00',
            'monthly' => '20.00',
            'quarterly' => '-1.00',
            'semiannually' => '-1.00',
            'annually' => '-1.00',
            'biennially' => '-1.00',
            'triennially' => '-1.00',
        );


        $product_array[] = array(
            'type' => 'other',
            'gid' => $groups->id,
            'name' => 'VDS XL X2RAM ' . $_REQUEST['disk'][$i],
            'description' => 'CPU: 3
RAM: 12
'.$_REQUEST['disk'][$i].': '.$_REQUEST['disk_size'].'
            ',
            'hidden' => $_REQUEST['product_hidden'] == 'on' ? '1' : '0',
            'paytype' => 'recurring',
            'servertype' => 'onconnector',
            'configoption1' => 3,
            'configoption2' => 12,
            'configoption3' => $_REQUEST['disk'][$i],
            'configoption4' => $_REQUEST['disk_size'],
            'configoption5' => $_REQUEST['node'],
            "autosetup" => 'payment',
            'order' => '17'
        );

        $last_prod++;
        $product_price[] = array(
            'type' => 'product',
            'currency' => '1',
            'relid' => $last_prod,
            'msetupfee' => '0.00',
            'qsetupfee' => '0.00',
            'ssetupfee' => '0.00',
            'asetupfee' => '0.00',
            'bsetupfee' => '0.00',
            'tsetupfee' => '0.00',
            'monthly' => '25.00',
            'quarterly' => '-1.00',
            'semiannually' => '-1.00',
            'annually' => '-1.00',
            'biennially' => '-1.00',
            'triennially' => '-1.00',
        );


        $product_array[] = array(
            'type' => 'other',
            'gid' => $groups->id,
            'name' => 'VDS XXL X2RAM ' . $_REQUEST['disk'][$i],
            'description' => 'CPU: 4
RAM: 16
'.$_REQUEST['disk'][$i].': '.$_REQUEST['disk_size'].'
            ',
            'hidden' => $_REQUEST['product_hidden'] == 'on' ? '1' : '0',
            'paytype' => 'recurring',
            'servertype' => 'onconnector',
            'configoption1' => 4,
            'configoption2' => 16,
            'configoption3' => $_REQUEST['disk'][$i],
            'configoption4' => $_REQUEST['disk_size'],
            'configoption5' => $_REQUEST['node'],
            "autosetup" => 'payment',
            'order' => '18'
        );

        $last_prod++;
        $product_price[] = array(
            'type' => 'product',
            'currency' => '1',
            'relid' => $last_prod,
            'msetupfee' => '0.00',
            'qsetupfee' => '0.00',
            'ssetupfee' => '0.00',
            'asetupfee' => '0.00',
            'bsetupfee' => '0.00',
            'tsetupfee' => '0.00',
            'monthly' => '30.00',
            'quarterly' => '-1.00',
            'semiannually' => '-1.00',
            'annually' => '-1.00',
            'biennially' => '-1.00',
            'triennially' => '-1.00',
        );


        $product_array[] = array(
            'type' => 'other',
            'gid' => $groups->id,
            'name' => 'VDS 3XL X2RAM ' . $_REQUEST['disk'][$i],
            'description' => 'CPU: 6
RAM: 24
'.$_REQUEST['disk'][$i].': '.$_REQUEST['disk_size'].'
            ',
            'hidden' => $_REQUEST['product_hidden'] == 'on' ? '1' : '0',
            'paytype' => 'recurring',
            'servertype' => 'onconnector',
            'configoption1' => 6,
            'configoption2' => 24,
            'configoption3' => $_REQUEST['disk'][$i],
            'configoption4' => $_REQUEST['disk_size'],
            'configoption5' => $_REQUEST['node'],
            "autosetup" => 'payment',
            'order' => '19'
        );

        $last_prod++;
        $product_price[] = array(
            'type' => 'product',
            'currency' => '1',
            'relid' => $last_prod,
            'msetupfee' => '0.00',
            'qsetupfee' => '0.00',
            'ssetupfee' => '0.00',
            'asetupfee' => '0.00',
            'bsetupfee' => '0.00',
            'tsetupfee' => '0.00',
            'monthly' => '35.00',
            'quarterly' => '-1.00',
            'semiannually' => '-1.00',
            'annually' => '-1.00',
            'biennially' => '-1.00',
            'triennially' => '-1.00',
        );


        $product_array[] = array(
            'type' => 'other',
            'gid' => $groups->id,
            'name' => 'VDS 4XL X2RAM ' . $_REQUEST['disk'][$i],
            'description' => 'CPU: 8
RAM: 32
'.$_REQUEST['disk'][$i].': '.$_REQUEST['disk_size'].'
            ',
            'hidden' => $_REQUEST['product_hidden'] == 'on' ? '1' : '0',
            'paytype' => 'recurring',
            'servertype' => 'onconnector',
            'configoption1' => 8,
            'configoption2' => 32,
            'configoption3' => $_REQUEST['disk'][$i],
            'configoption4' => $_REQUEST['disk_size'],
            'configoption5' => $_REQUEST['node'],
            "autosetup" => 'payment',
            'order' => '20'
        );

        $last_prod++;
        $product_price[] = array(
            'type' => 'product',
            'currency' => '1',
            'relid' => $last_prod,
            'msetupfee' => '0.00',
            'qsetupfee' => '0.00',
            'ssetupfee' => '0.00',
            'asetupfee' => '0.00',
            'bsetupfee' => '0.00',
            'tsetupfee' => '0.00',
            'monthly' => '40.00',
            'quarterly' => '-1.00',
            'semiannually' => '-1.00',
            'annually' => '-1.00',
            'biennially' => '-1.00',
            'triennially' => '-1.00',
        );


        $product_array[] = array(
            'type' => 'other',
            'gid' => $groups->id,
            'name' => 'VDS 5XL X2RAM ' . $_REQUEST['disk'][$i],
            'description' => 'CPU: 10
RAM: 48
'.$_REQUEST['disk'][$i].': '.$_REQUEST['disk_size'].'
            ',
            'hidden' => $_REQUEST['product_hidden'] == 'on' ? '1' : '0',
            'paytype' => 'recurring',
            'servertype' => 'onconnector',
            'configoption1' => 10,
            'configoption2' => 48,
            'configoption3' => $_REQUEST['disk'][$i],
            'configoption4' => $_REQUEST['disk_size'],
            'configoption5' => $_REQUEST['node'],
            "autosetup" => 'payment',
            'order' => '21'
        );

        $last_prod++;
        $product_price[] = array(
            'type' => 'product',
            'currency' => '1',
            'relid' => $last_prod,
            'msetupfee' => '0.00',
            'qsetupfee' => '0.00',
            'ssetupfee' => '0.00',
            'asetupfee' => '0.00',
            'bsetupfee' => '0.00',
            'tsetupfee' => '0.00',
            'monthly' => '45.00',
            'quarterly' => '-1.00',
            'semiannually' => '-1.00',
            'annually' => '-1.00',
            'biennially' => '-1.00',
            'triennially' => '-1.00',
        );



        Capsule::table('tblproducts')->insert($product_array);
        Capsule::table('tblpricing')->insert($product_price);
        unset($product_array);
        unset($product_price);

        $i++;
    }
    $i_disk = 0;
    $ii = 0;
    $last_addons_temp = Capsule::table('tbladdons')->orderBy('id', 'desc')->limit(1)->select('id')->get();
    $last_addons = $last_addons_temp[0]->id;
    foreach ($new_group as $groups) {

        $start = 10;
        $finish = 280;
        $step = 10;
        $price = 2;

        for($i = $start; $i <= $finish; $i = $i + $step){
            $ii++;
            $addons_array[] = array(
                'name' => 'Disk '.$_REQUEST['disk'][$i_disk].' '.$i.' GB',
                'description' => '',
                'billingcycle' => 'monthly',
                'allowqty' => '0',
                'tax' => '0',
                'showorder' => '1',
                'hidden' => '0',
                'retired' => '0',
                'downloads' => '',
                'autoactivate' => 'payment',
                'suspendproduct' => 1,
                'welcomeemail' => 0,
                'type' => 'other',
                'module' => 'onconnector',
                'weight' => $ii,
            );

            $last_addons++;

            $addons_config[] = array(
                'entity_type' => 'addon',
                'entity_id' => $last_addons,
                'setting_name' => 'configoption4',
                'friendly_name' => 'DISK VALUE:',
                'value' => $i
            );

            $product_price[] = array(
                'type' => 'addon',
                'currency' => '1',
                'relid' => $last_addons,
                'msetupfee' => '0.00',
                'qsetupfee' => '0.00',
                'ssetupfee' => '0.00',
                'asetupfee' => '0.00',
                'bsetupfee' => '0.00',
                'tsetupfee' => '0.00',
                'monthly' => $price,
                'quarterly' => '-1.00',
                'semiannually' => '-1.00',
                'annually' => '-1.00',
                'biennially' => '-1.00',
                'triennially' => '-1.00',
            );

            $price = $price + 2;

        }
        $i_disk ++;

        Capsule::table('tbladdons')->insert($addons_array);
        Capsule::table('tblmodule_configuration')->insert($addons_config);
        Capsule::table('tblpricing')->insert($product_price);
        unset($addons_array);
        unset($addons_config);
        unset($product_price);

    }


    $os_name = array(
        'CentOS 7','CentOS 8','Debian 9','Debian 10','Ubuntu 16.04','Ubuntu 18.04','Ubuntu 20.04','Windows Server 2012','Windows Server 2016','Windows Server 2019'
    );


    foreach ($os_name as $item){
        $ii++;
        $addons_array[] = array(
            'name' => $item,
            'description' => '',
            'billingcycle' => 'free',
            'allowqty' => '0',
            'tax' => '0',
            'showorder' => '1',
            'hidden' => '0',
            'retired' => '0',
            'downloads' => '',
            'autoactivate' => 'payment',
            'suspendproduct' => 1,
            'welcomeemail' => 0,
            'type' => 'other',
            'module' => 'onconnector',
            'weight' => $ii,
        );

        $last_addons++;

        $addons_config[] = array(
            'entity_type' => 'addon',
            'entity_id' => $last_addons,
            'setting_name' => 'configoption7',
            'friendly_name' => 'OS:',
            'value' => 'on'
        );
    }

    Capsule::table('tbladdons')->insert($addons_array);
    Capsule::table('tblmodule_configuration')->insert($addons_config);
    unset($addons_array);
    unset($addons_config);

}

