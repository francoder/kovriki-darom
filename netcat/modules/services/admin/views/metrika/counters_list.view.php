<?php
if (!class_exists('nc_core')) {
    die;
}
?>
<div class="nc_admin_fieldset_head"><?= NETCAT_MODULE_SERVICES_METRIKA_COUNTERS; ?></div>
<?php
$table = $ui->table()->wide()->striped()->bordered()->hovered();

$thead = $table->thead(); // chaining produces invalid code

$thead->th(NETCAT_MODULE_SERVICES_METRIKA_COUNTER_NAME)->text_center();
$thead->th(NETCAT_MODULE_SERVICES_METRIKA_COUNTER_SITE)->compact()->text_center();
$thead->th(NETCAT_MODULE_SERVICES_METRIKA_COUNTER_ID)->compact()->text_center();
$thead->th(NETCAT_MODULE_SERVICES_METRIKA_COUNTER_STATUS)->text_center();
$thead->th(NETCAT_MODULE_SERVICES_METRIKA_COUNTER_PERMISSION)->text_center();
$thead->th()->compact();
$thead->th()->compact();


$tr = $table->row();
$tr->name = $tr->td();
$tr->site = $tr->td();
$tr->id = $tr->td();
$tr->status = $tr->td()->text_center();
$tr->permission = $tr->td()->text_center();
$tr->stat_link = $tr->td()->text_center();
$tr->delete_button = $tr->td()->text_center();

foreach ($counters as $row) {
    $counter_id = $row['id'];
    $edit_link = "$link_prefix.counter.edit($counter_id)";
    $update_status_link = "$link_prefix.counter.update_status($counter_id)";

    $post_actions_params = array('controller' => "metrika", 'counter_id' => $counter_id);

    $tr->name->text("<a href='$edit_link' target='_top' class='nc-netshop-list-item-title'>" . $row['name'] . "</a>");
    $tr->site->text($row['site']);
    $tr->id->text($counter_id);
    $tr->status->text("<a href='$update_status_link' target='_top' class='nc-netshop-list-item-title'>" . $row['code_status_text'] . "</a>");
    $tr->permission->text($row['permission_text']);

    $view_stat_link = "$link_prefix.stat.traffic($counter_id)";
    $tr->stat_link->text("<a href='$view_stat_link' target='_top' class='nc-netshop-list-item-title'>" . NETCAT_MODULE_SERVICES_METRIKA_COUNTER_VIEW_STAT . "</a>");

    $tr->delete_button->text(
      $ui->controls->delete_button(
        sprintf(NETCAT_MODULE_SERVICES_CONFIRM_DELETE, $row['name']), $post_actions_params
    ));

    $table->add_row($tr);
}


echo $table, "<br>";
