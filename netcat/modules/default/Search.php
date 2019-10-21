<?php
include_once '../../../vars.inc.php';
include_once '../../connect_io.php';
include_once '../default/function.inc.php';
include_once '../netshop/function.inc.php';
include_once '../search/function.inc.php';

$nc_core = nc_Core::get_object();
$netshop = nc_netshop::get_instance();

$areas = array('/catalog/*' => 'в каталоге');

$heh = $nc_search->get_results($_GET['search_query'], '/catalog/*');

$result = [];

$host = array_reverse(explode('.', $_SERVER['SERVER_NAME']));

foreach ($heh as $value) {
  $resname = $nc_core->subdivision->get_by_uri($value->get('path'), 1, 'Subdivision_Name');
  $parsub = intval($nc_core->subdivision->get_by_uri($value->get('path'), 1, 'Parent_Sub_ID'));
  $siteUrl = $value->get('url');

  if ($parsub !== 0 && $parsub !== 4 && strpos($siteUrl, $_SERVER['SERVER_NAME']) !== false) {
    $parname = $nc_core->subdivision->get_by_id($parsub, 'Subdivision_Name'); ?>
      <a href="<?= $value->get('path') ?>" class="item">
    <img src="/style/cars/<?= $parname ?>/<?= $resname ?>.jpg" alt="">
    <div class="text">
          <?php $name = $nc_core->subdivision->get_by_uri($value->get('path'), 1, 'Subdivision_Name'); ?>
      <span><?= mb_strimwidth($name, 0, 10, '...') ?></span>
      <p></p>
    </div>
  </a>

    <?php
  }
}

?>
