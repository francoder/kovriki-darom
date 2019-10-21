<?php if (!class_exists('nc_core')) { die; } ?>

<form class='nc-form' method='post'>
    <?=$form ?>
</form>

<?php

nc_netshop_condition_admin_helpers::include_condition_editor_js();

?>

<script>
    (function() {
        var container = $nc('#nc_netshop_condition_editor'),
            condition_editor = new nc_netshop_condition_editor({
                container: container,
                input_name: 'data[Condition]',
                conditions: <?=$condition_json ?>,
                site_id: <?=$catalogue_id ?>,
                conditions_to_exclude: ['cart_sum']
            });

        container.closest('.ncf_value').removeClass('ncf_value');
        container.closest('form').get(0).onsubmit = function() {
            return condition_editor.onFormSubmit();
        };

    })();
</script>