<?php

$SERVICES_FOLDER = dirname(__FILE__);

// новый класс
require_once "$SERVICES_FOLDER/nc_services.class.php";
nc_core()->register_class_autoload_path('nc_services_', "$SERVICES_FOLDER/classes");
