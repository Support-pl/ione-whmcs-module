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

        <br><br>
        <input type="submit" style="width: 200px;height: 30px;background: #fff;border: 1px solid;" value="Create">
    </form>
<?php



if($_REQUEST['disk'][0] != NULL) {

    $hidden = 1;
    $order_form = 'standard_cart';

    $time = date('Y-m-d H:i:s');
    $last_order_temp = Capsule::table('tblproductgroups')->orderBy('order', 'desc')->limit(1)->select('order')->get();
    $last_order = $last_order_temp[0]->order;
    foreach ($_REQUEST['disk'] as $item) {
        $last_order++;
        $group_array[] = array(
            'name' => 'Self-Service VDS ' . $item,
            'slug' => 'Self-Service VDS ' . $item,
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

    $new_group = Capsule::table('tblproductgroups')->where('order', '>', $last_order_temp[0]->order)->get();

    $i = 0;

    foreach ($new_group as $groups) {
        $product_array[] = array(
            'type' => 'other',
            'gid' => $groups->id,
            'name' => 'VDS M ' . $_REQUEST['disk'][$i],
            'slug' => '',
            'description' => 'VDS M',
            'hidden' => $_REQUEST['product_hidden'] == 'on' ? '1' : '0',
            'paytype' => 'free',
            'servertype' => 'onconnector',
            'configoption1' => 1,
            'configoption2' => 2,
            'configoption3' => $_REQUEST['disk'][$i],
            'configoption4' => $_REQUEST['disk_size'],
            'configoption5' => $_REQUEST['node'],
        );
        $product_array[] = array(
            'type' => 'other',
            'gid' => $groups->id,
            'name' => 'VDS L ' . $_REQUEST['disk'][$i],
            'slug' => '',
            'description' => 'VDS L',
            'hidden' => $_REQUEST['product_hidden'] == 'on' ? '1' : '0',
            'paytype' => 'free',
            'servertype' => 'onconnector',
            'configoption1' => 2,
            'configoption2' => 4,
            'configoption3' => $_REQUEST['disk'][$i],
            'configoption4' => $_REQUEST['disk_size'],
            'configoption5' => $_REQUEST['node'],
        );
        $product_array[] = array(
            'type' => 'other',
            'gid' => $groups->id,
            'name' => 'VDS XL ' . $_REQUEST['disk'][$i],
            'slug' => '',
            'description' => 'VDS XL',
            'hidden' => $_REQUEST['product_hidden'] == 'on' ? '1' : '0',
            'paytype' => 'free',
            'servertype' => 'onconnector',
            'configoption1' => 3,
            'configoption2' => 6,
            'configoption3' => $_REQUEST['disk'][$i],
            'configoption4' => $_REQUEST['disk_size'],
            'configoption5' => $_REQUEST['node'],
        );
        $product_array[] = array(
            'type' => 'other',
            'gid' => $groups->id,
            'name' => 'VDS XXL ' . $_REQUEST['disk'][$i],
            'slug' => '',
            'description' => 'VDS XXL',
            'hidden' => $_REQUEST['product_hidden'] == 'on' ? '1' : '0',
            'paytype' => 'free',
            'servertype' => 'onconnector',
            'configoption1' => 4,
            'configoption2' => 8,
            'configoption3' => $_REQUEST['disk'][$i],
            'configoption4' => $_REQUEST['disk_size'],
            'configoption5' => $_REQUEST['node'],
        );
        $product_array[] = array(
            'type' => 'other',
            'gid' => $groups->id,
            'name' => 'VDS 3XL ' . $_REQUEST['disk'][$i],
            'slug' => '',
            'description' => 'VDS 3XL',
            'hidden' => $_REQUEST['product_hidden'] == 'on' ? '1' : '0',
            'paytype' => 'free',
            'servertype' => 'onconnector',
            'configoption1' => 6,
            'configoption2' => 12,
            'configoption3' => $_REQUEST['disk'][$i],
            'configoption4' => $_REQUEST['disk_size'],
            'configoption5' => $_REQUEST['node'],
        );
        $product_array[] = array(
            'type' => 'other',
            'gid' => $groups->id,
            'name' => 'VDS 4XL ' . $_REQUEST['disk'][$i],
            'slug' => '',
            'description' => 'VDS 4XL',
            'hidden' => $_REQUEST['product_hidden'] == 'on' ? '1' : '0',
            'paytype' => 'free',
            'servertype' => 'onconnector',
            'configoption1' => 8,
            'configoption2' => 16,
            'configoption3' => $_REQUEST['disk'][$i],
            'configoption4' => $_REQUEST['disk_size'],
            'configoption5' => $_REQUEST['node'],
        );
        $product_array[] = array(
            'type' => 'other',
            'gid' => $groups->id,
            'name' => 'VDS 5XL ' . $_REQUEST['disk'][$i],
            'slug' => '',
            'description' => 'VDS 5XL',
            'hidden' => $_REQUEST['product_hidden'] == 'on' ? '1' : '0',
            'paytype' => 'free',
            'servertype' => 'onconnector',
            'configoption1' => 10,
            'configoption2' => 24,
            'configoption3' => $_REQUEST['disk'][$i],
            'configoption4' => $_REQUEST['disk_size'],
            'configoption5' => $_REQUEST['node'],
        );
        Capsule::table('tblproducts')->insert($product_array);
        unset($product_array);

        $i++;
    }


}

