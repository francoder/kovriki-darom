
    <?
    
    
    $adr = $_GET['city'];
        
$cityID = "";
    
    
    
    
        
    $ch = curl_init();

 $url = 'https://pecom.ru/ru/calc/towns.php';
    
    
        // echo $params;
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true
        ));

        $out = curl_exec($ch);
        // print_r($out);
        $res = json_decode($out, true);
       
          foreach($res as $key => $arras) {
              
                $sea = 0;
                foreach($arras as $key2 => $value) {
                    $value = explode('(', $value);  
                    $value = $value[0]; 
                    if (stristr($value, $adr) === FALSE) {
                       
                    } else {
                        $sea++;
                    }
                }
              if ($sea > 0) {
                  reset($arras);
                  $cityID = key($arras);
              }
        }

        
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    $ch = curl_init();

 $url = 'http://calc.pecom.ru/bitrix/components/pecom/calc/ajax.php';

         $arr = [
         	'places' => [
         		[
                     0.60, // Ширина
                     0.80, // Длина
                     0.10, // Высота
                     0.05, // Объем
                     2, // Вес
                     1, // Признак негабаритности груза
                     1 // Признак ЖУ
                 ]
         	],
         	'take' => [
         		'town' => -472, // челны
         	],
         	'deliver' => [
         		'town' => $cityID, // Город назначения
         	],
         ];
        $params = http_build_query($arr);
        // echo $params;
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url.'?'.$params,
            CURLOPT_RETURNTRANSFER => true
        ));

        $out = curl_exec($ch);
        $res = json_decode($out, true);
        
        if($cityID !== "") {
            $result['price'] = $res['auto'][2] + 30;
            
            $result['days'] = $res['periods_days'];
            if ($res['periods_days'] == null) {
                $result['days'] = "в течении ";
            }
        } else {
            $result['can'] = "no";
        }

        
        echo json_encode($result); 
    
    
    
    ?>