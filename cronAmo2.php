<?
error_reporting(-1);
ini_set('display_errors', 'On');

use AmoCrm\OrderUpdateService;

require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
require $_SERVER['DOCUMENT_ROOT'] . "/vars.inc.php";
require $_SERVER['DOCUMENT_ROOT'] . "/netcat/connect_io.php";
require $_SERVER['DOCUMENT_ROOT'] . "/AmoCrm/OrderUpdateService.php";

global $nc_core, $current_user, $AUTH_USER_ID;
$messageStack = [];
$orderUpdateService = new OrderUpdateService(
        [
                'USER_LOGIN' => 'evakovrikoff@yandex.ru',
                'USER_HASH' => '109732db776b857667be1a21e1d372ad',
        ],
        'kovrikidarom'
);

try {
    if (isset($_POST['leads'])) {
        $encodedRequest = json_encode($_POST['leads']);  // JSON формат сохраняемого значения.
        $requestLead = json_decode($encodedRequest, true);
        $leadId = $requestLead['status'][0]['id'] ?? null;

        if ($leadId === null) {
            $messageStack[] = 'Неправильная структура запроса';
        }

        $orderUpdateService->authorize();
        $leadsList = $orderUpdateService->getLeadInfo($leadId);

        //$messageStack[] = print_r($leadInfo, true);
        $lead = $leadsList[0] ?? null;
        $leadFields = $lead['custom_fields'] ?? null;
        $leadStatus = (int)$lead['status_id'];
        if ($lead === null || $leadFields === null) {
            $messageStack[] = 'Не найдены данные лида в амо';
        }

        $orderNumber = null;
        foreach ($leadFields as $field) {
            if ($field['name'] === 'Номер заказа' && isset($field['values']['0']['value'])) {
                $orderNumber = (int)$field['values']['0']['value'];
            }
        }


//    if ($leadStatus === 18505183) {
//        $db->query("UPDATE payList SET sendPay = 1, status = 'Выплачено' WHERE id = " . $leadFields[2]['values'][0]['value']);
//        $balanceC = $nc_core->db->get_var("SELECT balance FROM User WHERE User_ID = '" . $leadFields[1]['values'][0]['value'] . "'");
//        $payStat = $nc_core->db->get_var("SELECT status FROM User WHERE User_ID = '" . $leadFields[1]['values'][0]['value'] . "'");
//
////        if ($payStat !== "Выплачено") {
////            $db->query("UPDATE User SET balance = ".intval($balanceC - $Response[0]['custom_fields'][0]['values'][0]['value'])." WHERE User_ID = '".$Response[0]['custom_fields'][1]['values'][0]['value']."'");
////        }
//    }


        if ($orderNumber !== null) {
            $statusEnumMap = [
                    18065506 => [
                            'name' => 'Отправлен',
                            'needCheck' => true,
                    ],
                    18480928 => [
                            'name' => 'Отправлен',
                            'needCheck' => true,
                    ],
                    142 => [
                            'name' => 'Завершен',
                            'needCheck' => true,
                    ],
                    18006571 => [
                            'name' => 'Ожидает отправки',
                            'needCheck' => true,
                    ],
                    18006577 => [
                            'name' => 'Ожидает отправки',
                            'needCheck' => true,
                    ],
                    18006574 => [
                            'name' => 'В обработке',
                            'needCheck' => false,
                    ],
                    143 => [
                            'name' => 'Не реализован',
                            'needCheck' => false,
                    ],
            ];

            $needCheck = false;
            if (isset($statusEnumMap[$leadStatus])) {
                $statusName = $statusEnumMap[$leadStatus]['name'];
                $needCheck = $statusEnumMap[$leadStatus]['needCheck'];
                $db->query(
                        "UPDATE orders SET status = '{$statusName}' WHERE ord_number = '" . $orderNumber . "'"
                );
            }

            if ($needCheck) {
                $arr = $db->get_results("SELECT * FROM orders WHERE ord_number = '" . $orderNumber . "'");
                $paySend = (int)$arr[0]->paySend;
                $referral = $arr[0]->referral;


                if (($paySend == 0) && ($referral !== '')) {
                    $db->query("UPDATE orders SET paySend = 1 WHERE ord_number = '" . $orderNumber . "'");
                    $refAcc = $db->get_results("SELECT * FROM User WHERE referralLink = '" . $referral . "'");

                    $referralStatus = $refAcc[0]->referralStatus;
                    $balance = $refAcc[0]->balance;
                    $profit = $refAcc[0]->profit;


                    $refCount = $db->get_results("SELECT * FROM orders WHERE referral = '" . $referral . "' AND paySend > 0");
                    $refCount = count($refCount);

                    if ($refCount > 10) {
                        $referralStatus = 1;
                    } elseif ($refCount > 50) {
                        $referralStatus = 2;
                    } elseif ($refCount > 100) {
                        $referralStatus = 3;
                    } else {
                        $referralStatus = 0;
                    }

                    if ($referralStatus === 0) {
                        $rBon = 0.05;
                    } elseif ($referralStatus === 1) {
                        $rBon = 0.1;
                    } elseif ($referralStatus === 2) {
                        $rBon = 0.15;
                    } elseif ($referralStatus === 3) {
                        $rBon = 0.2;
                    } else {
                        $rBon = 0.2;
                    }

                    $zakPrice = $nc_core->db->get_var("SELECT price FROM orders WHERE ord_number = '" . $orderNumber . "'");
                    $newBalance = $balance + ((int)$zakPrice * $rBon);
                    $profit += ((int)$zakPrice * $rBon);
                    $refBon = ((int)$zakPrice * $rBon);
                    $db->query("UPDATE User SET balance = " . $newBalance . ", profit = " . $profit . ",referralStatus = " . $referralStatus . "  WHERE User_ID = '" . $refAcc[0]->User_ID . "'");
                    $db->query("UPDATE orders SET paySend = " . $refBon . " WHERE ord_number = '" . $orderNumber . "'");
                }
            }
        }
    }
} catch (\Throwable $exception) {
    $messageStack[] = $exception->getMessage();
}

$transport = (new Swift_SmtpTransport('smtp.mailtrap.io', 2525))
        ->setUsername('58afa3f8d6c791')
        ->setPassword('eb813132df671f');
$mailer = new Swift_Mailer($transport);
$message = (new Swift_Message($messageStack[0]))
        ->setFrom(['john@doe.com' => 'John Doe'])
        ->setTo(['receiver@domain.org', 'other@domain.org' => 'A name'])
        ->setBody(implode('
            ', $messageStack));
$result = $mailer->send($message);
