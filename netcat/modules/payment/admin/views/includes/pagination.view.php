<?php

if (!class_exists('nc_core')) {
    die;
}

/** @var int $page */
/** @var int $total_pages */
/** @var string $page_url */

if ($total_pages < 2) {
    return;
}

$n = 4; // число страниц влево-вправо от текущей
$left = $page - $n;
$right = $page + $n;

// делаем так, чтобы общее количество страниц было не меньше $n*2 + 1

if ($left < 1) {
    $right -= $left - 1;
}

if ($right > $total_pages) {
    $left += ($total_pages - $right);
}

$first = max(1, $left);
$last = min($total_pages, $right);

$page_numbers_to_show = array_unique(array_merge(array(1, $total_pages), range($first, $last)));
sort($page_numbers_to_show);

$previous = 0;

?>
<div class="nc-pagination">
<?php
    foreach ($page_numbers_to_show as $p) {
        if ($p != $previous + 1) {
            echo "\n&hellip;\n";
        }
        echo '<a' . ($p == $page ? ' class="nc--active"' : '') .
             " href=\"$page_url&page=$p\">$p</a>\n";
        $previous = $p;
    }
?>
</div>
