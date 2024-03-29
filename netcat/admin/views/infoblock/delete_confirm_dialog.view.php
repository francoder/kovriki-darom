<?php

if (!class_exists('nc_core')) {
    die;
}

/** @var int $infoblock_id */
/** @var nc_core $nc_core */

?>
<div class="nc-modal-dialog" data-width="300" data-height="auto">
    <div class="nc-modal-dialog-header">
        <h2><?= NETCAT_MODERATION_REMOVE_INFOBLOCK_CONFIRMATION_HEADER ?></h2>
    </div>
    <div class="nc-modal-dialog-body">
        <form action="<?= $nc_core->SUB_FOLDER . $nc_core->HTTP_ROOT_PATH ?>action.php" method="post" class="nc-form">
            <input type="hidden" name="ctrl" value="admin.infoblock">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="infoblock_id" value="<?= $infoblock_id ?>">

            <?= NETCAT_MODERATION_REMOVE_INFOBLOCK_CONFIRMATION_BODY ?>

        </form>
    </div>
    <div class="nc-modal-dialog-footer">
        <button data-action="submit"><?= NETCAT_MODERATION_DELETE_BLOCK ?></button>
        <button data-action="close"><?= CONTROL_BUTTON_CANCEL ?></button>
    </div>
</div>
