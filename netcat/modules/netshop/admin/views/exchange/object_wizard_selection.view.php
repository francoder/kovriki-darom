<?php if (!class_exists('nc_core')) { die; } ?>

<?php

/** @var array $items */

?>

<?php foreach ($items as $i => $item_data) { ?>

    <label>

        <input type="checkbox" name="item_index[]" value="<?= $i; ?>" class="nc-item" <?= !$item_data['checked'] ?: 'checked'; ?>> <?= $item_data['text']; ?>

        <?php if (!$item_data['mapped']) { ?>

            (<?= NETCAT_MODULE_NETSHOP_EXCHANGE_NOT_MAPPED; ?>)

        <?php } ?>

    </label>
    <br>

<?php } ?>

<!-- ----- SCRIPTS ----- -->

<script>
    function nc_netshop_exchange_wizard_form_submit() {
        if ($nc('.nc-item:checked').length === 0) {
            alert('<?= NETCAT_MODULE_NETSHOP_EXCHANGE_SELECT_AT_LEAST_ONE_OBJECT_FOR_MAPPING; ?>');
            return false;
        }
        return true;
    }
</script>