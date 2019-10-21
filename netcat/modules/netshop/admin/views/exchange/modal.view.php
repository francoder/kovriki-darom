<?php if (!class_exists('nc_core')) { die; } ?>

<script>
    function nc_netshop_exchange_modal_show(name, callback) {
        $nc('.nc-netshop-exchange-modal-item').hide();
        $nc('.nc-netshop-exchange-modal-item#' + name).show();
        $nc('.nc-netshop-exchange-modal').fadeIn('slow', function() {
            if (callback) {
                callback();
            }
        });
    }
    function nc_netshop_exchange_modal_hide(callback) {
        $nc('.nc-netshop-exchange-modal').fadeOut('slow', function() {
            $nc('.nc-netshop-exchange-modal-item').hide();
            if (callback) {
                callback();
            }
        });
    }
    $nc(function() {
        $nc('.nc-netshop-exchange-modal-close').click(function() {
            nc_netshop_exchange_modal_hide();
        });
    });
</script>