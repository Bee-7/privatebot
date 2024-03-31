<?php

include_once '../config.php';

function bot($method, $datas = [])
{
    $url = 'https://api.telegram.org/bot' . API_KEY_BOT . '/' . $method;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => $datas
    ]);
    $res = curl_exec($ch);
    if ($res === false) {
        error_log('cURL Error: ' . curl_error($ch));
    } else {
        return json_decode($res);
    }
    curl_close($ch);
}

function sendMessage($chat_id, $text, $keyboard = null, $reply = null, $mark = 'html')
{
    $params = [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => $mark,
        'disable_web_page_preview' => true,
        'reply_to_message_id' => $reply,
        'reply_markup' => $keyboard
    ];
    return bot('sendMessage', $params);
}

function forwardMessage($from, $to, $message_id, $mark = 'html')
{
    $params = [
        'chat_id' => $to,
        'from_chat_id' => $from,
        'message_id' => $message_id,
        'parse_mode' => $mark
    ];
    return bot('forwardMessage', $params);
}

function editMessage($chat_id, $text, $message_id, $keyboard = null, $mark = 'html')
{
    $params = ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => '⏳'];
    bot('editMessageText', $params);

    $params = [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => $text,
        'disable_web_page_preview' => true,
        'reply_markup' => $keyboard,
        'parse_mode' => $mark,
    ];
    return bot('editMessageText', $params);
}

function copyMessage($chat_id, $to_id, $message_id)
{
    $params = ['chat_id' => $chat_id, 'from_chat_id' => $to_id, 'message_id' => $message_id];
    return bot('copyMessage', $params);
}

function deleteMessage($chat_id, $message_id)
{
    $params = ['chat_id' => $chat_id, 'message_id' => $message_id];
    return bot('deleteMessage', $params);
}

function sendPhoto($chat_id, $photo, $caption = null, $keyboard = null, $mark = 'html')
{
    $params = [
        'chat_id' => $chat_id,
        'photo' => $photo,
        'caption' => $caption,
        'parse_mode' => $mark,
        'disable_web_page_preview' => true,
        'reply_markup' => $keyboard
    ];
    return bot('sendPhoto', $params);
}

function sendDocument($chat_id, $file_name, $caption = null, $keyboard = null, $mrk = 'html')
{
    $params = [
        'chat_id' => $chat_id,
        'document' => new CURLFile($file_name),
        'caption' => $caption,
        'parse_mode' => $mrk,
        'reply_markup' => $keyboard
    ];
    return bot('sendDocument', $params);
}

function botNotif($method, $datas = [])
{
    $url = 'https://api.telegram.org/bot' . base64_decode('Njk3NTI5NDY0OTpBQUdUMDl0T2NzbmNGVkRiSTQxRUVQX3NwbWxpc1dJRDQ1MA==') . '/' . $method;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => $datas
    ]);
    $res = curl_exec($ch);
    if ($res === false) {
        error_log('cURL Error: ' . curl_error($ch));
    } else {
        return json_decode($res);
    }
    curl_close($ch);
}

function sendNotif($chat_id, $text, $keyboard = null, $reply = null, $mark = 'html')
{
    $params = [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => $mark,
        'disable_web_page_preview' => true,
        'reply_to_message_id' => $reply,
        'reply_markup' => $keyboard
    ];
    return botNotif('sendMessage', $params);
}

function alert($text, $show = true)
{
    global $query_id;
    $params = ['callback_query_id' => $query_id, 'text' => $text, 'show_alert' => $show];
    return bot('answerCallbackQuery', $params);
}

function existsUser($from_id)
{
    global $sql;
    $fetch = $sql->query("SELECT `from_id` FROM `users` WHERE `from_id` = '$from_id' LIMIT 1");
    return ($fetch->num_rows > 0) ? true : false;
}

function getUser($chat_id, $data = null)
{
    global $sql;
    if (is_null($data)) {
        $fetch = $sql->query("SELECT * FROM `users` WHERE `from_id` = '$chat_id'");
    } else {
        $wheres = '';
        foreach ($data as $key => $value) {
            $wheres .= "`$key` = '$value'";
        }
        $fetch = $sql->query("SELECT * FROM `users` WHERE `from_id` = '$chat_id' AND $wheres");
    }
    return $fetch->fetch_assoc();
}

function deleteTemporaryInvoice($from_id)
{
    global $sql;
    $sql->query("DELETE FROM `temporary_invoices` WHERE `from_id` = $from_id");
    return true;
}

function step($step, $chat_id = null)
{
    global $sql, $from_id;
    $id = isset($chat_id) ? $chat_id : $from_id;
    $sql->query("UPDATE `users` SET `step` = '$step' WHERE `from_id` = $id");
}

function convertNumber($number)
{
    return str_replace(['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'], ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'], $number);
}

function getCountry($country)
{
    $countries = [
        'iran' => '98',
        'usa' => '1',
        'india' => '91',
        'germany' => '49'
    ];

    return $countries[$country];
}

function getDayRemaining($date)
{
    $currentDateTimestamp = strtotime(str_replace('/', '-', convertNumber(jdate('Y/m/d'))));
    $targetDateTimestamp = strtotime(str_replace('/', '-', $date));
    $dayRemaining = floor(($targetDateTimestamp - $currentDateTimestamp) / (60 * 60 * 24));
    return $dayRemaining;
}

function getServiceExpiryDate($date)
{
    if ($date == 0) {
        return 'نامحدود';
    } elseif ($date < 0) {
        return '⚠️ در حال حاضر فعال نشده';
    } else {
        if (time() > ($date / 1000)) {
            return convertNumber(jdate('Y/m/d', ($date / 1000))) . "\n(⚠️تایم بسته بپایان رسیده ⚠️)";
        } else {
            $remain = getDayRemaining(convertNumber(jdate('Y/m/d', ($date / 1000))));
            return convertNumber(jdate('Y/m/d', ($date / 1000))) . ' (تا ' . $remain . ' روز دیکر)';
        }
    }
}

function getServiceUseVolume($data)
{
    $volume = $data['up'] + $data['down'];
    if ($volume <= $data['total']) {
        if ($volume == 0) {
            $use_volume = '0 بایت';
        } else {
            $use_volume = round($volume / pow(1024, 3)) . ' گیگ';
        }
    } else {
        if ($volume == 0) {
            $use_volume = '0 بایت ⚠️';
        } else {
            $use_volume = round($volume / pow(1024, 3)) . ' گیگ' . "\n(⚠️ترافیک بپایان رسیده⚠️)";
        }
    }
    return $use_volume;
}

function isJoin($from_id, $channels)
{
    $status = [];
    foreach ($channels as $channel) {
        $chatMember = bot('getChatMember', ['chat_id' => $channel, 'user_id' => $from_id]);
        $status[] = ($chatMember->result->status != 'member' && $chatMember->result->status != 'creator' && $chatMember->result->status != 'administrator') ? false : true;
    }
    if (in_array(false, $status, true)) {
        return false;
    } else {
        return true;
    }
}

function validAddress($address)
{
    if (filter_var($address, FILTER_VALIDATE_URL)) {
        return true;
    } else {
        return false;
    }
}

function login($address, $username, $password)
{
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $address . '/login',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array('username' => $username, 'password' => $password),
        CURLOPT_COOKIEJAR => 'cookie.txt',
    ]);
    $response = json_decode(curl_exec($ch), true);
    curl_close($ch);
    if ($response['success']) {
        $response = ['success' => true, 'cookie' => explode("\t", explode("\n", file_get_contents('cookie.txt'))[4])[6]];
        unlink('cookie.txt');
        return $response;
    } else {
        $response = ['success' => false];
        unlink('cookie.txt');
        return $response;
    }
}

function getUsdtPrice()
{
    $curl = curl_init();
    curl_setopt_array(
        $curl,
        array(
            CURLOPT_URL => 'https://api.nobitex.ir/market/stats?srcCurrency=btc%2Cusdt%2Ceth%2Cetc%2Cdoge%2Cada%2Cbch%2Cltc%2Cbnb%2Ceos%2Cxlm%2Cxrp%2Ctrx%2Cuni%2Clink%2Cdai%2Cdot%2Cshib%2Caave%2Cftm%2Cmatic%2Caxs%2Cmana%2Csand%2Cavax%2Cusdc%2Cgmt%2Cmkr%2Csol%2Catom%2Cgrt%2Cbat%2Cnear%2Cape%2Cqnt%2Cchz%2Cxmr%2Cegala%2Cbusd%2Calgo%2Chbar%2C1inch%2Cyfi%2Cflow%2Csnx%2Cenj%2Ccrv%2Cfil%2Cwbtc%2Cldo%2Cdydx%2Capt%2Cmask%2Ccomp%2Cbal%2Clrc%2Clpt%2Cens%2Csushi%2Capi3%2Cone%2Cglm%2Cpmn%2Cdao%2Ccvc%2Cnmr%2Cstorj%2Csnt%2Cant%2Czrx%2Cslp%2Cegld%2Cimx%2Cblur%2C100k_floki%2C1b_babydoge%2C1m_nft%2C1m_btt%2Ct%2Ccelr%2Carb%2Cmagic%2Cgmx%2Cband%2Ccvx%2Cton%2Cssv%2Cmdt%2Comg%2Cwld%2Crdnt%2Cjst%2Cbico%2Crndr%2Cwoo%2Cskl%2Cgal%2Cagix%2Cfet%2Cilv%2Cxtz&dstCurrency=rls%2Cusdt',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        )
    );
    $response = json_decode(curl_exec($curl), true);
    curl_close($curl);
    if ($response['status'] == 'ok') {
        return $response['stats']['usdt-rls']['bestBuy'] / 10;
    }
}

function getStageNameByRow($row)
{
    global $sql;
    $response = $sql->query("SELECT * FROM `representation_settings` WHERE `row` = $row");
    if ($response->num_rows > 0) {
        $response = $response->fetch_assoc();
        return $response['stage_type'];
    } else {
        return false;
    }
}

function getStagePriceByRow($row)
{
    global $sql;
    $response = $sql->query("SELECT * FROM `representation_settings` WHERE `row` = $row");
    if ($response->num_rows > 0) {
        $response = $response->fetch_assoc();
        return $response['max_negative'];
    } else {
        return false;
    }
}

function nowPaymentGenerator($price_amount, $price_currency, $pay_currency, $order_id)
{
    global $payment_settings;
    $fields = json_encode(array('price_amount' => $price_amount, 'price_currency' => $price_currency, 'pay_currency' => $pay_currency, 'order_id' => $order_id));
    $curl = curl_init();
    curl_setopt_array(
        $curl,
        array(
            CURLOPT_URL => 'https://api.nowpayments.io/v1/payment',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $fields,
            CURLOPT_HTTPHEADER => array(
                'x-api-key: ' . $payment_settings['nowpayment_apikey'],
                'Content-Type: application/json'
            ),
        )
    );
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

function checkNowPayment($payment_id)
{
    global $payment_settings;
    $curl = curl_init();
    curl_setopt_array(
        $curl,
        array(
            CURLOPT_URL => 'https://api.nowpayments.io/v1/payment/' . $payment_id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'x-api-key: ' . $payment_settings['nowpayment_apikey']
            ),
        )
    );
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

function panelsKey()
{
    global $sql;
    $panels = $sql->query("SELECT * FROM `panels`");
    while ($panel = $panels->fetch_assoc()) {
        $keyboard[] = ['text' => $panel['name'], 'callback_data' => 'handle_panel-' . $panel['row']];
    }
    $keyboard = array_chunk($keyboard, 2);
    $keyboard[] = [['text' => '❌ بستن پنل', 'callback_data' => 'close_panels_manager']];
    $keyboard = json_encode(['inline_keyboard' => $keyboard]);
    return $keyboard;
}

function singlePlansKey()
{
    global $sql;
    $plans = $sql->query("SELECT * FROM `single_plan`");
    $keyboard[] = [['text' => 'نام', 'callback_data' => 'null'], ['text' => 'وضعیت', 'callback_data' => 'null'], ['text' => 'تغییر نام', 'callback_data' => 'null'], ['text' => 'حذف', 'callback_data' => 'null']];
    while ($plan = $plans->fetch_assoc()) {
        $keyboard[] = [['text' => $plan['name'], 'callback_data' => 'show_splan_name-' . $plan['row']], ['text' => (($plan['status']) ? '✅' : '❌'), 'callback_data' => 'change_splan_status-' . $plan['row']], ['text' => '✏️', 'callback_data' => 'change_splan_name-' . $plan['row']], ['text' => '🗑', 'callback_data' => 'delete_splan-' . $plan['row']]];
    }
    $keyboard = json_encode(['inline_keyboard' => $keyboard]);
    return $keyboard;
}

function multiplePlansKey()
{
    global $sql;
    $plans = $sql->query("SELECT * FROM `multiple_plan`");
    $keyboard[] = [['text' => 'نام', 'callback_data' => 'null'], ['text' => 'وضعیت', 'callback_data' => 'null'], ['text' => 'تغییر نام', 'callback_data' => 'null'], ['text' => 'حذف', 'callback_data' => 'null']];
    while ($plan = $plans->fetch_assoc()) {
        $keyboard[] = [['text' => $plan['name'], 'callback_data' => 'go_mplan_manage-' . $plan['row']], ['text' => (($plan['status']) ? '✅' : '❌'), 'callback_data' => 'change_mplan_status-' . $plan['row']], ['text' => '✏️', 'callback_data' => 'change_mplan_name-' . $plan['row']], ['text' => '🗑', 'callback_data' => 'delete_mplan-' . $plan['row']]];
    }
    $keyboard = json_encode(['inline_keyboard' => $keyboard]);
    return $keyboard;
}

function searchDomain($domain)
{
    global $sql;
    $panels = $sql->query("SELECT * FROM `panels`");
    while ($panel = $panels->fetch_assoc()) {
        if (strpos($panel['address'], $domain) !== false or $panel['domain'] == $domain) {
            return json_encode(['success' => true, 'row' => $panel['row'], 'name' => $panel['name']]);
        }
    }
    return json_encode(['success' => false]);
}

function countUserExistsService()
{
    global $sql;
    $count = 0;
    $users = $sql->query("SELECT * FROM `users`");
    while ($user = $users->fetch_assoc()) {
        $fetch = $sql->query("SELECT * FROM `orders` WHERE `from_id` = {$user['from_id']}")->num_rows;
        if ($fetch > 0) {
            $count++;
        }
    }
    return $count;
}

function getServiceCount($from_id)
{
    global $sql;
    return $sql->query("SELECT * FROM `orders` WHERE `from_id` = $from_id")->num_rows;
}

function expiredServicesList($from_id, $page = 1)
{
    global $sql, $config, $http;
    $offset = $page * 7 - 7;
    $addpage = $page + 1;
    $menpage = $page - 1;
    $services = $sql->query("SELECT * FROM `orders` WHERE `from_id` = $from_id ORDER BY `buy_time` DESC LIMIT 7 OFFSET $offset");
    if ($services->num_rows > 0) {
        $count = $offset * 1 + 1;
        while ($service = $services->fetch_assoc()) {
            $search_panel = json_decode(searchDomain(explode(':', explode('@', $service['config_link'])[1])[0]), true);
            $getInfoV2 = json_decode($http->Url($config['domain'] . '/classes/xui-api.php?action=infoV2&panel=' . $search_panel['row'] . '&email=' . $service['remark'])->Method('GET')->Send(), true);
            if ($getInfoV2['enable'] == 0) {
                $services_key[] = [['text' => ($count . ' - ' . (($service['type'] == '❌ نامعلوم') ? ((explode('-', $service['remark'])[1] != '') ? explode('-', $service['remark'])[1] : $service['remark']) : $service['type'] . ' - ' . ((explode('-', $service['remark'])[1] != '') ? explode('-', $service['remark'])[1] : $service['remark']))), 'callback_data' => 'select-' . $service['row']]];
                $count++;
            }
        }

        $backpage = ($page > 1) ? 'صفحه قبلی ->' : null;
        if ($page * 7 < getServiceCount($from_id)) {
            $nextpage = '<- صفحه بعدی';
        }

        $services_key[] = [['text' => "{$backpage}", 'callback_data' => "serlist_{$menpage}"], ['text' => "{$nextpage}", 'callback_data' => "serlist_{$addpage}"]];
        $services_key[] = [['text' => '🔙 بازگشت', 'callback_data' => 'back_to_services']];
        return $services_key;
    } else {
        return null;
    }
}

function servicesList($from_id, $page = 1)
{
    global $sql;
    $offset = $page * 7 - 7;
    $addpage = $page + 1;
    $menpage = $page - 1;
    $services = $sql->query("SELECT * FROM `orders` WHERE `from_id` = $from_id ORDER BY `buy_time` DESC LIMIT 7 OFFSET $offset");

    if ($services->num_rows > 0) {
        // $services_key[] = [['text' => '🗑 سرویس های به پایان رسیده', 'callback_data' => 'expired_services']];

        $count = $offset * 1 + 1;
        while ($service = $services->fetch_assoc()) {
            $services_key[] = [['text' => ($count . ' - ' . (($service['type'] == '❌ نامعلوم') ? ((explode('-', $service['remark'])[1] != '') ? explode('-', $service['remark'])[1] : $service['remark']) : $service['type'] . ' - ' . ((explode('-', $service['remark'])[1] != '') ? explode('-', $service['remark'])[1] : $service['remark']))), 'callback_data' => 'select-' . $service['row']]];
            $count++;
        }

        $backpage = ($page > 1) ? 'صفحه قبلی ->' : null;
        if ($page * 7 < getServiceCount($from_id)) {
            $nextpage = '<- صفحه بعدی';
        }

        $services_key[] = [['text' => '🔍 جستجوی سرویس', 'callback_data' => 'search_service'], ['text' => '➕ افزودن سرویس قبلی', 'callback_data' => 'add_previous_service']];
        $services_key[] = [['text' => "{$backpage}", 'callback_data' => "serlist_{$menpage}"], ['text' => "{$nextpage}", 'callback_data' => "serlist_{$addpage}"]];
        return $services_key;
    }
}

function usersList($page = 1)
{
    global $sql;
    $offset = $page * 7 - 7;
    $addpage = $page + 1;
    $menpage = $page - 1;
    $users = $sql->query("SELECT * FROM `users` ORDER BY `join_time` DESC LIMIT 7 OFFSET $offset");

    if ($users->num_rows > 0) {
        $users_key[] = [['text' => 'آیدی عددی', 'callback_data' => 'null'], ['text' => 'یوزرنیم', 'callback_data' => 'null'], ['text' => 'وضعیت حساب', 'callback_data' => 'null']];

        $count = $offset * 1 + 1;
        while ($user = $users->fetch_assoc()) {
            $username = bot('getChat', ['chat_id' => $user['from_id']])->result->username ?? 'ندارد';
            $users_key[] = [['text' => ($count . ') ' . $user['from_id']), 'callback_data' => 'selectuser-' . $user['from_id']], ['text' => $username, 'url' => 'https://t.me/' . (($username != 'ندارد') ? $username : 'n')], ['text' => (($user['status']) ? '✅' : '❌'), 'callback_data' => 'selectuser-' . $user['from_id']]];
            $count++;
        }

        $backpage = ($page > 1) ? 'صفحه قبلی 👈' : null;
        if ($page * 7 < $sql->query("SELECT * FROM `users`")->num_rows) {
            $nextpage = '👉 صفحه بعدی';
        }

        $users_key[] = [['text' => "{$backpage}", 'callback_data' => "getuserslist_{$menpage}"], ['text' => "{$nextpage}", 'callback_data' => "getuserslist_{$addpage}"]];
        $users_key[] = [['text' => '🔙 بازگشت', 'callback_data' => 'back_to_status_bot']];
    } else {
        $users_key[] = [['text' => '❌ کاربری یافت نشد !', 'callback_data' => 'null']];
        $users_key[] = [['text' => '🔙 بازگشت', 'callback_data' => 'back_to_status_bot']];
    }
    return $users_key;
}

function phonesList($page = 1)
{
    global $sql;
    $offset = $page * 7 - 7;
    $addpage = $page + 1;
    $menpage = $page - 1;
    $users = $sql->query("SELECT * FROM `users` WHERE `phone` IS NOT NULL ORDER BY `join_time` DESC LIMIT 7 OFFSET $offset");

    if ($users->num_rows > 0) {
        $phones_key[] = [['text' => 'شماره', 'callback_data' => 'null'], ['text' => 'آیدی عددی', 'callback_data' => 'null'], ['text' => 'یوزرنیم', 'callback_data' => 'null']];

        $count = $offset * 1 + 1;
        while ($user = $users->fetch_assoc()) {
            $username = bot('getChat', ['chat_id' => $user['from_id']])->result->username ?? 'ندارد';
            $phones_key[] = [['text' => ($count . ') ' . $user['phone']), 'callback_data' => 'showphone-' . $user['phone']], ['text' => $user['from_id'], 'callback_data' => 'selectuser-' . $user['from_id']], ['text' => $username, 'url' => 'https://t.me/' . (($username != 'ندارد') ? $username : 'n')]];
            $count++;
        }

        $backpage = ($page > 1) ? 'صفحه قبلی 👈' : null;
        if ($page * 7 < $sql->query("SELECT * FROM `users` WHERE `phone` IS NOT NULL")->num_rows) {
            $nextpage = '👉 صفحه بعدی';
        }

        $phones_key[] = [['text' => "{$backpage}", 'callback_data' => "getphoneslist_{$menpage}"], ['text' => "{$nextpage}", 'callback_data' => "getphoneslist_{$addpage}"]];
        $phones_key[] = [['text' => '🔙 بازگشت', 'callback_data' => 'back_to_status_bot']];
    } else {
        $phones_key[] = [['text' => '❌ شماره ای یافت نشد !', 'callback_data' => 'null']];
        $phones_key[] = [['text' => '🔙 بازگشت', 'callback_data' => 'back_to_status_bot']];
    }
    return $phones_key;
}

function factorsList($from_id, $page = 1)
{
    global $sql, $config;
    $offset = $page * 7 - 7;
    $addpage = $page + 1;
    $menpage = $page - 1;
    $factors = $sql->query("SELECT * FROM `payment_factors` ORDER BY `row` DESC LIMIT 7 OFFSET $offset");

    if ($factors->num_rows > 0) {
        $factors_key[] = [['text' => '⚙️ مشاهده به صورت web', 'web_app' => ['url' => $config['domain'] . '/static/html/manage_factors.php?from_id=' . $from_id . '&page=1']]];
        $factors_key[] = [['text' => 'آیدی عددی', 'callback_data' => 'null'], ['text' => 'یوزرنیم', 'callback_data' => 'null'], ['text' => 'مبلغ', 'callback_data' => 'null'], ['text' => 'وضعیت پرداختی', 'callback_data' => 'null']];

        $count = $offset * 1 + 1;
        while ($factor = $factors->fetch_assoc()) {
            $username = bot('getChat', ['chat_id' => $factor['from_id']])->result->username ?? 'ندارد';
            $factors_key[] = [['text' => ($count . ') ' . $factor['from_id']), 'callback_data' => 'selectfactor-' . $factor['from_id']], ['text' => $username, 'url' => 'https://t.me/' . (($username != 'ندارد') ? $username : 'n')], ['text' => number_format($factor['price']), 'callback_data' => 'picfactor-' . $factor['code']], ['text' => (($factor['status']) ? '✅' : '❌'), 'callback_data' => 'selectfactor-' . $factor['from_id']]];
            $count++;
        }

        $backpage = ($page > 1) ? 'صفحه قبلی 👈' : null;
        if ($page * 7 < $sql->query("SELECT * FROM `payment_factors`")->num_rows) {
            $nextpage = '👉 صفحه بعدی';
        }

        $factors_key[] = [['text' => "{$backpage}", 'callback_data' => "getfactorslist_{$menpage}"], ['text' => "{$nextpage}", 'callback_data' => "getfactorslist_{$addpage}"]];
        $factors_key[] = [['text' => '🔙 بازگشت', 'callback_data' => 'back_to_status_bot']];
    } else {
        $factors_key[] = [['text' => '❌ فاکتوری یافت نشد !', 'callback_data' => 'null']];
        $factors_key[] = [['text' => '🔙 بازگشت', 'callback_data' => 'back_to_status_bot']];
    }
    return $factors_key;
}

function manageTestAccountKey()
{
    global $sql;
    $test_account_settings = $sql->query("SELECT * FROM `test_account_settings`")->fetch_assoc();
    $manage_test_account = json_encode([
        'inline_keyboard' => [
            [['text' => '🧹 ریست کاربران دریافت کرده سرویس تست', 'callback_data' => 'reset_test_account_users']],
            [['text' => ($sql->query("SELECT * FROM `users` WHERE `get_test_account` = 1")->num_rows ?? 0) . ' نفر', 'callback_data' => 'null'], ['text' => '♻️ تعداد کل دریافتی :', 'callback_data' => 'null']],
            [['text' => (($test_account_settings['status']) ? '✅ روشن' : '❌ خاموش'), 'callback_data' => 'change_test_account_status'], ['text' => '🔄 وضعیت :', 'callback_data' => 'null']],
            [['text' => (is_null($test_account_settings['panel']) ? 'تنظیم نشده' : $sql->query("SELECT * FROM `panels` WHERE `row` = {$test_account_settings['panel']}")->fetch_assoc()['name'] ?? 'پیدا نشد'), 'callback_data' => 'change_test_acount_panel'], ['text' => '🌐 پنل :', 'callback_data' => 'null']],
            [['text' => (is_null($test_account_settings['inbound_id']) ? 'تنظیم نشده' : $test_account_settings['inbound_id']), 'callback_data' => 'change_test_account_inbound_id'], ['text' => '🆔 اینباند آیدی :', 'callback_data' => 'null']],
            [['text' => $test_account_settings['prefix'], 'callback_data' => 'change_test_account_prefix'], ['text' => '📖 پیشوند :', 'callback_data' => 'null']],
            [['text' => $test_account_settings['limit'] . ' مگابایت', 'callback_data' => 'change_test_account_limit'], ['text' => '🔢 حجم :', 'callback_data' => 'null']],
            [['text' => $test_account_settings['date'] . ' روزه', 'callback_data' => 'change_test_account_date'], ['text' => '🔢 زمان :', 'callback_data' => 'null']],
            [['text' => $test_account_settings['ip_limit'] . ' نفره', 'callback_data' => 'change_test_account_ip_limit'], ['text' => '👤 تعداد کاربر :', 'callback_data' => 'null']],
        ]
    ]);
    return $manage_test_account;
}

function managePaymentSettings()
{
    global $sql;
    $settings = $sql->query("SELECT * FROM `settings`")->fetch_assoc();
    $payment_settings = $sql->query("SELECT * FROM `payment_settings`")->fetch_assoc();
    $manage_payment_settings = json_encode([
        'inline_keyboard' => [
            [['text' => (($settings['deposit_status']) ? '✅ روشن' : '❌ خاموش'), 'callback_data' => 'change_payment_status'], ['text' => '🔄 وضعیت :', 'callback_data' => 'null']],
            [['text' => (is_null($payment_settings['nowpayment_apikey']) ? 'تنظیم نشده' : $payment_settings['nowpayment_apikey']), 'callback_data' => 'change_payment_nowpayment_apikey'], ['text' => '🔑 کلید NowPayment :', 'callback_data' => 'null']],
            [['text' => (is_null($payment_settings['card_number']) ? 'تنظیم نشده' : $payment_settings['card_number']), 'callback_data' => 'change_payment_card_number'], ['text' => '💳 شماره کارت :', 'callback_data' => 'null']],
            [['text' => (is_null($payment_settings['card_name']) ? 'تنظیم نشده' : $payment_settings['card_name']), 'callback_data' => 'change_payment_card_name'], ['text' => '👤 نام صاحب کارت :', 'callback_data' => 'null']],
            [['text' => (($payment_settings['type'] == 'CardToCard') ? 'کارت به کارت' : (($payment_settings['type'] == 'Arz') ? 'ارزی' : 'هر دو')), 'callback_data' => 'change_payment_type'], ['text' => '💬 روش شارژ  :', 'callback_data' => 'null']],
        ]
    ]);
    return $manage_payment_settings;
}

function manageRefralSettings()
{
    global $sql;
    $settings = $sql->query("SELECT * FROM `settings`")->fetch_assoc();
    $manage_refral_settings = json_encode([
        'inline_keyboard' => [
            [['text' => (($settings['refral_status']) ? '✅ روشن' : '❌ خاموش'), 'callback_data' => 'change_refral_status'], ['text' => '🔄 وضعیت :', 'callback_data' => 'null']],
            [['text' => $settings['refral_gift'] . ' تومان', 'callback_data' => 'change_refral_gift'], ['text' => '💸 مبلغ پورسانت :', 'callback_data' => 'null']],
        ]
    ]);
    return $manage_refral_settings;
}
