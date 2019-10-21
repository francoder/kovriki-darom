<?php
 
function downloadJs($file_url, $save_to)
{
    $content = file_get_contents($file_url);
    file_put_contents($save_to, $content);
}
 
// Указываем URL, затем папку от корня сайта и имя файла с расширением.
// Проверьте чтобы на папке были права на запись 777/755
// Метрика
downloadJs('https://www.googletagmanager.com/gtm.js?id=GTM-5P2G5K2', realpath("./js") . '/gtm.js');
 
// Google Analytics
downloadJs('https://mc.yandex.ru/metrika/watch.js', realpath("./js") . '/watch.js');
 
// Для скриптов без расширения
//downloadJs('https://www.google-analytics.com/analytics.js', realpath("./js") . '/analytics.js');
 
?>