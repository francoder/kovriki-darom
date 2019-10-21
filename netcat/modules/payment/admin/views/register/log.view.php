<?php

if (!class_exists('nc_core')) {
    die;
}

/** @var nc_ui $ui */
/** @var int $site_id */
/** @var array $events */

?>

<?= $ui->controls->site_select($site_id) ?>

<div class="nc-margin-top-small nc-margin-bottom-medium">
    <?= $this->include_view('../includes/event_list')->with('events', $events)->with('show_receipt_link', true) ?>
</div>

<?= $this->include_view('../includes/pagination', $this->data) ?>
