<?php
/*=========== Skylab interactive - 1.1.2 ========================*/
$perm->ExitIfNotAccess(NC_PERM_MODULE, 0, 0, 0, 1);

$prevMessage = '';
if ($act === "send") {
    // Проверка валидности электронной почты
    if (!sitesecure_is_email($nc_core->input->fetch_get_post('email'))) {
        nc_print_status(SKYLAB_MODULE_SITESECURE_SETTINGS_NOVALID, "error");
    } else {
        $text = $nc_core->input->fetch_get_post('message');
        $userEmail = $nc_core->input->fetch_get_post('email');

        if (nc_sitesecure_sendUserReply($text, $userEmail)) {
            nc_print_status(SKYLAB_MODULE_SITESECURE_REPLY_SEND_SUCCESS, "ok");
        } else {
            $prevMessage = $text;
            nc_print_status(SKYLAB_MODULE_SITESECURE_REPLY_SEND_FAILURE, "error");
        }
    }
}

?>
<form method="post" action="admin.php" id="MainSettigsForm" style="padding:0; margin:0;">
    <input type="hidden" name="view" value="reply" />
    <input type="hidden" name="act" value="send" />
    <fieldset>
        <table id="replyOptions">
            <tbody>
                <tr>
                    <td><?= SKYLAB_MODULE_SITESECURE_REPLY_EMAIL ?>: </td>
                </tr>
                <tr>
                    <td><input type="text" size="50" name="email" value="<?= htmlspecialchars($email) ?>"></td>
                </tr>
                <tr>
                    <td><?= SKYLAB_MODULE_SITESECURE_REPLY_MESSAGE ?>: </td>
                </tr>
                <tr>
                    <td><textarea name="message"><?= htmlspecialchars($prevMessage) ?></textarea></td>
                </tr>
            </tbody>
        </table>
    </fieldset>
</form>
<?php

$UI_CONFIG->actionButtons[] = array(
    "id"      => "submit",
    "caption" => SKYLAB_MODULE_SITESECURE_REPLY_SEND,
    "action"  => "mainView.submitIframeForm('MainSettingsForm')"
);

function sitesecure_is_email($mail) {
    return preg_match("/^([a-zA-Z0-9])+([\.a-zA-Z0-9_-])*@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-]+)*\.([a-zA-Z]{2,6})$/", $mail);
}

/**
 * The return value is the number of recipients who were accepted for
 * delivery.
 *
 * @param $text
 * @param $userEmail
 * @return int
 */
function nc_sitesecure_sendUserReply($text, $userEmail)
{
    $nc_core = nc_Core::get_object();

    $to        = SITESECURE_REPLY_EMAIL_MAIN;
    $cc        = SITESECURE_REPLY_EMAIL_CC;
    $from      = $nc_core->get_settings('SpamFromEmail');
    $to_name   = 'SiteSecure';
    $from_name = $nc_core->get_settings('SpamFromName');
    $subject   = 'Отзыв через форму обратной связи в NetCat CMS';
    $prefix    = 'E-mail пользователя: ' . $userEmail;

    if (empty($from)) {
        $from = $userEmail;
    }

    if (!$nc_core->NC_UNICODE) {
        $subject = $nc_core->utf8->utf2win($subject);
        $prefix = $nc_core->utf8->utf2win($prefix);
    }

    $message = $prefix . "\n" . $text;

    $nc_core->mail->set_cc($cc);
    $nc_core->mail->mailbody(strip_tags($message), $message);

    return $nc_core->mail->send($to, $from, $from, $subject, $from_name, $to_name);
}
