<?

$adr  = $_GET['city'];
$city = "";


$ch = curl_init();

$url = 'https://tk-kit.ru/API.1.1?f=get_city_list&token=r0y1eyg_lJkwH90z4dGTm3wvqHqPbrex';


// echo $params;
curl_setopt_array($ch, array(
  CURLOPT_URL            => $url,
  CURLOPT_RETURNTRANSFER => true,
));

$out = curl_exec($ch);
// print_r($out);
$res = json_decode($out, true);


//echo "<pre>";
//print_r($res);
//echo "</pre>";

foreach ($res["CITY"] as $key => $value) {

  $value['NAME'] = explode('(', $value['NAME']);
  $value['NAME'] = $value['NAME'][0];
  if (stristr($value['NAME'], $adr) === false) {

  } else {
    $city = $value;
  }
}


$ch = curl_init();

$url = 'https://tk-kit.ru/API.1.1?f=price_order&I_DELIVER=0&I_PICK_UP=1&WEIGHT=30&VOLUME=0.6&SLAND=RU&SZONE=0000001610&SCODE=160000200000&SREGIO=16&RLAND=RU&RZONE='.$city["TZONEID"].'&WEIGHT=2&RCODE='.$city["ID"].'&RREGIO='.$city["REGION"].'&KWMENG=1&LENGTH=80&WIDTH=60&HEIGHT=10&GR_TYPE=&LIFNR=&PRICE=&WAERS=RUB&token=r0y1eyg_lJkwH90z4dGTm3wvqHqPbrex';


// echo $params;
curl_setopt_array($ch, array(
  CURLOPT_URL            => $url,
  CURLOPT_RETURNTRANSFER => true,
));

$out = curl_exec($ch);
$res = json_decode($out, true);


if (($res['PRICE']['TRANSFER'] !== 0) && ($city["ID"] !== "160000200000")) {
  $result['price'] = $res['PRICE']['TRANSFER'];

  $result['days'] = $res['DAYS'];
  if ($res['DAYS'] == null) {
    $result['days'] = "в течении ";

  }
} else {
  $result['can'] = "no";
}


echo json_encode($result);


?>