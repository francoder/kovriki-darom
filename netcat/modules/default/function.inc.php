<?php

/*
Пример перехвата события "авторизация пользователя"
class ListenUser {
	public function __construct () {
		$nc_core = nc_Core::get_object();
		$nc_core->event->bind($this, array(nc_Event::AFTER_USER_AUTHORIZED => 'authorize_user') );
	}

	public function  authorize_user ( $user_id ) {
		return 0;
	}
}

$listenObj = new  ListenUser();
*/

/*
function my_func () {
}
*/

// Загрузка класса nc_netshop_delivery_service_kit при необходимости
spl_autoload_register(function($class_name) {
    if ($class_name === 'nc_netshop_delivery_service_kit') {
        include_once nc_module_folder('netshop') . 'classes/delivery/service/kit.php';
    }
});