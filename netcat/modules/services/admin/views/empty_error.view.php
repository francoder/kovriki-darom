<?php if (!class_exists('nc_core')) { die; } 

echo $ui->alert->error($message);

echo '<a href="javascript:history.back()">'.NETCAT_MODULE_SERVICES_BUTTON_BACK.'</a>';