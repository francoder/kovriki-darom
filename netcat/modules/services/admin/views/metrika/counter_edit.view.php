<?php
if (!class_exists('nc_core')) {
    die;
}
?>
<div class="nc_admin_fieldset_head"><?= !empty($counter_id) ? NETCAT_MODULE_SERVICES_METRIKA_COUNTER_EDIT : NETCAT_MODULE_SERVICES_METRIKA_COUNTER_ADD; ?></div>
<?php
$form = $ui->form("?controller=$controller_name")->vertical();
$form->add()->input('hidden', 'action', 'save_counter');
$form->add()->input('hidden', 'data[counter_id]', (!empty($counter_id) ? $counter_id : 0));

$form->add_row(NETCAT_MODULE_SERVICES_METRIKA_COUNTER_NAME)->horizontal()
  ->string('data[name]', $counter['name']);
$form->add_row(NETCAT_MODULE_SERVICES_METRIKA_COUNTER_SITE)->horizontal()
  ->span( NETCAT_MODULE_SERVICES_METRIKA_COUNTER_HREF_PREFIX . "&nbsp;")
  ->string('data[site]', $counter['site']);
if (!empty($counter_id)) {
    $form->add_row(NETCAT_MODULE_SERVICES_METRIKA_COUNTER_CODE)->horizontal()
      ->textarea('data[code]', $counter['code'])->attr('class', 'no_cm textarea yandex_code')->style('height: 200px; width: 500px;');
}
echo $form; 
?>

<script>
    (function() {
        $nc(".yandex_code").focus(function() {
            var $this = $(this);
            $this.select();
            // Work around Chrome's little problem
            $this.mouseup(function() {
                // Prevent further mouseup intervention
                $this.unbind("mouseup");
                return false;
            });
        });
    })();
</script>
