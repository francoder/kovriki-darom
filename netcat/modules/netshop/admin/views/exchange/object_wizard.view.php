<?php if (!class_exists('nc_core')) { die; } ?>

<?php

/** @var string $next_controller */
/** @var string $next_action */
/** @var integer $object_id */
/** @var string $phase */
/** @var string $current_action */

?>

<h2><?= NETCAT_MODULE_NETSHOP_EXCHANGE_WIZARD; ?></h2>
<h3><?= NETCAT_MODULE_NETSHOP_EXCHANGE_WIZARD_CURRENT_STEP; ?>: <?= $step; ?></h3>

<form class="nc-form validate" method="POST" enctype="multipart/form-data" id="nc-netshop-exchange-form" onsubmit="return nc_netshop_exchange_wizard_form_submit();">
    <input type="hidden" name="controller" value="<?= (isset($next_controller) && $next_controller) ? $next_controller : $controller; ?>">
    <input type="hidden" name="action" value="<?= $next_action; ?>">
    <input type="hidden" name="site_id" value="<?= $site_id ?>">
    <input type="hidden" name="object_id" value="<?= $object_id ?>">
    <input type="hidden" name="phase" value="<?= $phase ?>">

    <?= $this->include_view('object_wizard_' . $current_action, get_defined_vars()) ?>

</form>