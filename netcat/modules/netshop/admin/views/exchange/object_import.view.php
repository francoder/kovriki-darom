<?php if (!class_exists('nc_core')) { die; } ?>

<form class="nc-form" method="POST" enctype="multipart/form-data" id="nc-netshop-exchange-form">
    <input type="hidden" name="controller" value="<?= $controller; ?>">
    <input type="hidden" name="action" value="<?= $next_action ?: $action; ?>">
    <input type="hidden" name="object_id" value="<?= $object_id ?>">

    <?= $this->include_view('object_import_' . $current_action, get_defined_vars()) ?>

</form>

<form class="nc-form" method="POST" enctype="multipart/form-data" id="nc-netshop-exchange-form-back">
    <input type="hidden" name="controller" value="exchange">
    <input type="hidden" name="action" value="index">
    <input type="hidden" name="site_id" value="<?= $catalogue_id ?>">
</form>