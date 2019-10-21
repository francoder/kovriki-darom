<?php if (!class_exists('nc_core')) {
    die;
} ?>

<form class='nc-form' method='post'>
    <input type='hidden' name='action' value='settings_save'>
    <input type='hidden' name='next_action' value='index'>
    <?php
    // свойства организации
    $fields = array(
      'yandex_auth_token' => array(
        'caption' => NETCAT_MODULE_SERVICES_YANDEX_AUTH_TOKEN,
        'type' => 'string',
        'required' => true
      ),
    );

    $values = array();
    foreach ($fields as $name => $field_settings) {
        $values[$name] = nc_core()->get_settings($name, 'services', false, 0);
    }

    $form = new nc_a2f($fields, 'settings');
    $form->set_field_defaults('string', array('size' => 64))
      ->show_default_values(false)
      ->show_header(false)
      ->set_values($values);

    echo $form->render();
    ?>

</form>