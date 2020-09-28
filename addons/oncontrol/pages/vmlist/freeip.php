<?php
ini_set('display_errors', 0);
$result = $this->onconnect->compare_info();
$freeIP = $result['result']['2']['0'];

?>
<table class="table table-bordered table-hover table-condensed">
    <?php foreach ($freeIP as $it): ?>
        <tr>
            <td><?= $it ?></td>
        </tr>
    <?php endforeach; ?>
</table>