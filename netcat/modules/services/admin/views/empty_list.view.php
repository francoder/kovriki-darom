<?php if (!class_exists('nc_core')) { die; } ?>

<?php
    // Может быть задана переменная is_error, тогда сообщение выводится как ошибка
    $type = isset($is_error) ? 'error' : 'info';
    echo $ui->alert->$type($message)
?>