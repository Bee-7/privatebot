<?php

if ($_GET['token'] == 'reseller-xui') {

    # ---------------------------------- #

    $telegram_ip_ranges = [['lower' => '149.154.160.0', 'upper' => '149.154.175.255'], ['lower' => '91.108.4.0',    'upper' => '91.108.7.255']];
    $ip_dec = (float) sprintf("%u", ip2long($_SERVER['REMOTE_ADDR']));
    $ok = false;
    foreach ($telegram_ip_ranges as $telegram_ip_range) if (!$ok) {
        $lower_dec = (float) sprintf("%u", ip2long($telegram_ip_range['lower']));
        $upper_dec = (float) sprintf("%u", ip2long($telegram_ip_range['upper']));
        if ($ip_dec >= $lower_dec and $ip_dec <= $upper_dec) $ok=true;
    }
    if (!$ok) die("Access denied :(");

    # ---------------------------------- #

    include_once 'includes/keyboards.php';
    include_once 'includes/functions.php';
    include_once 'classes/request.php';
    include_once 'config.php';

    $http = new HTTPRequest();
    
    # ---------------------------------- #

    if (isJoin($from_id, $channels) == false and $from_id != $config['admin'] and !in_array($from_id, $config['admins']) and !in_array($from_id, $admins)) {
        $channels = implode("\n", $channels);
        $key = json_encode(['inline_keyboard' => [[['text' => '✅ عضو شدم', 'url' => 'https://t.me/' . bot('getMe')->result->username . '?start=' . $from_id]]]]);
        sendMessage($from_id, sprintf($texts['join_channel'], $channels), $key);
    }

    elseif ($settings['bot_status'] == false and $from_id != $config['admin'] and !in_array($from_id, $config['admins'])) {
        step('none');
        sendMessage($from_id, $texts['bot_off']);
    }

    elseif (isset($user['status']) and $user['status'] == false and $from_id != $config['admin']) {
        step('blocked');
        sendMessage($from_id, $texts['account_baned']);
    }

    elseif ($text == '/start' or $text == '🏠') {
        step('none');
        sendMessage($from_id, $texts['start'], $start_key);
    }

    elseif (strpos($text, '/start ') !== false) {
        $to_id = explode(' ', $text)[1];
        if ($to_id != $from_id) {
            $user = $sql->query("SELECT * FROM `users` WHERE `from_id` = $from_id");
            if ($user->num_rows == 0) {
                $getRefral = $sql->query("SELECT * FROM `refrals` WHERE `from_id` = $to_id AND `to_id` = $from_id");
                if ($getRefral->num_rows == 0) {
                    step('none');
                    $sql->query("INSERT INTO `users` (`from_id`, `join_time`) VALUES ($from_id, " . time() . ")");
                    $sql->query("INSERT INTO `refrals` (`from_id`, `to_id`, `status`) VALUES ($to_id, $from_id, 0)");
                    sendMessage($from_id, $texts['start'], $start_key);
                    sendMessage($to_id, sprintf($texts['start_with_link'], $from_id));
                } else {
                    sendMessage($from_id, $texts['start'], $start_key);
                }
            } else {
                sendMessage($from_id, $texts['start'], $start_key);
            }
        } else {
            sendMessage($from_id, $texts['start'], $start_key);
        }
    }

    elseif ($data == 'my_profile') {
        $count_all_service = $sql->query("SELECT * FROM `orders` WHERE `from_id` = $from_id")->num_rows ?? 0;
        $count_active_service = $sql->query("SELECT * FROM `orders` WHERE `from_id` = $from_id AND `status` = 1")->num_rows ?? 0;
        $count_inactive_service = $sql->query("SELECT * FROM `orders` WHERE `from_id` = $from_id AND `status` = 0")->num_rows ?? 0;
        $refral_income = number_format(($sql->query("SELECT * FROM `refrals` WHERE `from_id` = $from_id AND `status` = 1")->num_rows) * $settings['refral_gift'] ?? 0);

        $getUser = $sql->query("SELECT * FROM `representations` WHERE `from_id` = $from_id")->fetch_assoc();
        $stage_name = getStageNameByRow($getUser['stage_type']);
        $stage_price = getStagePriceByRow($getUser['stage_type']);
        editMessage($from_id, sprintf($texts['profile_nemayande'], $from_id, convertNumber(jdate('Y/m/d', $user['join_time'])), $stage_name, $getUser['nick_name'], (($user['coin'] < 0) ? ('منفی ' . number_format($user['coin'] * -1)) : number_format($user['coin'])), number_format($stage_price), number_format($count_all_service), number_format($count_active_service), number_format($count_inactive_service)), $message_id, $representation_key);
    }

    elseif ($text == '👤 حساب کاربری' or $data == 'back_to_profile' or $text == '/profile') {
        step('none');
        if (!in_array($from_id, $representations)) {
            $count_all_service = $sql->query("SELECT * FROM `orders` WHERE `from_id` = $from_id")->num_rows ?? 0;
            $count_active_service = $sql->query("SELECT * FROM `orders` WHERE `from_id` = $from_id AND `status` = 1")->num_rows ?? 0;
            $count_inactive_service = $sql->query("SELECT * FROM `orders` WHERE `from_id` = $from_id AND `status` = 0")->num_rows ?? 0;
            $refral_income = number_format(($sql->query("SELECT * FROM `refrals` WHERE `from_id` = $from_id AND `status` = 1")->num_rows) * $settings['refral_gift'] ?? 0);
            if (isset($text)) {
                sendMessage($from_id, sprintf($texts['my_account'], $from_id, convertNumber(jdate('Y/m/d', $user['join_time'])), number_format($user['coin']), $refral_income, number_format($count_all_service), number_format($count_active_service), number_format($count_inactive_service)), $gift);
            } else {
                editMessage($from_id, sprintf($texts['my_account'], $from_id, convertNumber(jdate('Y/m/d', $user['join_time'])), number_format($user['coin']), $refral_income, number_format($count_all_service), number_format($count_active_service), number_format($count_inactive_service)), $message_id, $gift);
            }
        } else {
            sendMessage($from_id, $texts['lock_profile'], $start_key);
        }
    }

    elseif ($data == 'use_gift_code') {
        $msg_id = editMessage($from_id, $texts['send_gift_code'], $message_id, $back_to_profile)->result->message_id;
        step('send-gift-code_' . $msg_id);
    }

    elseif (strpos($user['step'], 'send-gift-code') !== false) {
        $msg_id = explode('_', $user['step'])[1];
        $gift = $sql->query("SELECT * FROM `gifts` WHERE `gift` = '$text'");
        if ($gift->num_rows > 0) {
            $gift = $gift->fetch_assoc();
            if ($gift['status']) {
                $use_gift = $sql->query("SELECT * FROM `use_gifts` WHERE `from_id` = $from_id AND `gift` = '$text'");
                if ($use_gift->num_rows == 0) {
                    step('none');
                    $sql->query("UPDATE `users` SET `coin` = coin + {$gift['price']} WHERE `from_id` = $from_id");
                    
                    $sql->query("INSERT INTO `use_gifts` (`from_id`, `gift`) VALUES ($from_id, '$text')");
                    if ($gift['count_use'] == 1) {
                        $sql->query("UPDATE `gifts` SET `status` = 0, `count_use` = 0 WHERE `gift` = '$text'");
                    } elseif ($gift['count_use'] > 1) {
                        $sql->query("UPDATE `gifts` SET `status` = 1, `count_use` = count_use - 1 WHERE `gift` = '$text'");
                    }
                    
                    deleteMessage($from_id, $message_id);
                    editMessage($from_id, sprintf($texts['success_gift'], number_format($gift['price'])), $msg_id, $back_to_profile);
                } else {
                    step('none');
                    deleteMessage($from_id, $message_id);
                    editMessage($from_id, $texts['gift_error'], $msg_id, $back_to_profile);
                }
            } else {
                step('none');
                deleteMessage($from_id, $message_id);
                editMessage($from_id, $texts['gift_error'], $msg_id, $back_to_profile);
            }
        } else {
            step('none');
            deleteMessage($from_id, $message_id);
            editMessage($from_id, $texts['gift_error'], $msg_id, $back_to_profile);
        }
    }

    elseif ($text == '🛒 تعرفه خدمات' or $text == '/tariff') {
        step('none');
        sendMessage($from_id, $texts['tariff'], $start_key);
    }

    elseif ($text == '📮 پشتیبانی آنلاین' or $text == '/support') {
        step('none');
        sendMessage($from_id, sprintf($texts['support'], $settings['support']), $start_key);
    }

    elseif ($text == '🔗 راهنمای اتصال' or $text == '/help' or $data == 'education_key') {
        step('select_education');
        if (isset($text)) {
            sendMessage($from_id, $texts['education'], $education);
        } else {
            deleteMessage($from_id, $message_id);
            sendMessage($from_id, $texts['education'], $education);
        }
    }

    elseif ($user['step'] == 'select_education' and strpos($data, 'edu_') !== false) {
        step('select_education');
        $edu_system = explode('_', $data)[1];
        editMessage($from_id, $texts['edu_' . $edu_system], $message_id, $education);
    }

    elseif ($text == '💸 شارژ حساب') {
        if ($settings['deposit_status']) {
            if ($settings['phone_status']) {
                if (!is_null($user['phone'])) {
                    step('send_money');
                    sendMessage($from_id, $texts['send_money'], $back);
                } else {
                    step('send_phone');
                    sendMessage($from_id, $texts['send_phone'], $send_phone);
                }
            } else {
                step('send_money');
                sendMessage($from_id, $texts['send_money'], $back);
            }
        } else {
            sendMessage($from_id, $texts['charge_account_off'], $start_key);
        }
    }

    elseif ($user['step'] == 'send_phone') {
        if ($update->message->contact) {
            if ($update->message->contact->user_id == $from_id) {
                $phone = str_replace('+', '', $update->message->contact->phone_number);
                if ($settings['phone_country'] != 'all') {
                    if (strpos($phone, getCountry($settings['phone_country'])) !== false) {
                        step('none');
                        $sql->query("UPDATE `users` SET `phone` = $phone WHERE `from_id` = $from_id");
                        sendMessage($from_id, $texts['success_phone'], $start_key);
                    } else {
                        sendMessage($from_id, $texts['phone_error_3'], $send_phone);    
                    }
                } else {
                    step('none');
                    $sql->query("UPDATE `users` SET `phone` = $phone WHERE `from_id` = $from_id");
                    sendMessage($from_id, $texts['success_phone'], $start_key);
                }
            } else {
                sendMessage($from_id, $texts['phone_error_2'], $send_phone);
            }
        } else {
            sendMessage($from_id, $texts['phone_error_1'], $send_phone);
        }
    }

    elseif ($user['step'] == 'send_money') {
        if (is_numeric($text) and $text > 2000) {

            if ($payment_settings['type'] == 'CardToCard') {
                step('send_fish-' . $text);
                sendMessage($from_id, sprintf($texts['send_fish'], $payment_settings['card_number'], $payment_settings['card_name']), $back);
            }
            
            elseif ($payment_settings['type'] == 'Arz') {
                step('none');
                sendMessage($from_id, $texts['wait'], $back);
                $code = rand(111111, 999999);
                $usdt = getUsdtPrice();
                $generate = nowPaymentGenerator(($text / $usdt), 'usd', 'trx', $code);
                deleteMessage($from_id, $message_id + 1);
                if (!is_null($generate)) {
                    $response = json_decode($generate, true);
                    $sql->query("INSERT INTO `payment_factors` (`from_id`, `price`, `code`, `status`) VALUES ($from_id, $text, {$response['payment_id']}, 0)");
                    $check = json_encode(['inline_keyboard' => [[['text' => '✅ پرداخت کردم', 'callback_data' => 'check_nowpayment_factor-' . $response['payment_id']]]]]);
                    sendMessage($from_id, sprintf($texts['create_nowpayment_factor'], $response['payment_id'], number_format($text), number_format($usdt), $response['pay_amount'], $response['pay_address']), $check);
                    sendMessage($from_id, $texts['start'], $start_key);
                } else {
                    sendMessage($from_id, $texts['create_nowpayment_factor_error'], $start_key);
                }
            } 
            
            elseif ($payment_settings['type'] == 'both') {
                step('select_payment_method-' . $text);
                sendMessage($from_id, $texts['select_payment_method'], $select_payment_method);
            }

            else {
                step('none');
                sendMessage($from_id, $texts['create_nowpayment_factor_error'], $start_key);
            }
        } else {
            sendMessage($from_id, $texts['invalid_price'], $back);
        }
    }

    elseif ($data == 'cancel_pay' and strpos($user['step'], 'select_payment_method') !== false) {
        step('none');
        deleteMessage($from_id, $message_id);
        sendMessage($from_id, $texts['start'], $start_key);
    }

    elseif ($data == 'Arz' and strpos($user['step'], 'select_payment_method') !== false) {
        $text = explode('-', $user['step'])[1];
        step('none');
        deleteMessage($from_id, $message_id);
        sendMessage($from_id, $texts['wait'], $back);
        $code = rand(111111, 999999);
        $usdt = getUsdtPrice();
        $generate = nowPaymentGenerator(($text / $usdt), 'usd', 'trx', $code);
        deleteMessage($from_id, $message_id + 1);
        if (!is_null($generate)) {
            $response = json_decode($generate, true);
            $sql->query("INSERT INTO `payment_factors` (`from_id`, `price`, `code`, `status`) VALUES ($from_id, $text, {$response['payment_id']}, 0)");
            $check = json_encode(['inline_keyboard' => [[['text' => '✅ پرداخت کردم', 'callback_data' => 'check_nowpayment_factor-' . $response['payment_id']]]]]);
            sendMessage($from_id, sprintf($texts['create_nowpayment_factor'], $response['payment_id'], number_format($text), number_format($usdt), $response['pay_amount'], $response['pay_address']), $check);
            sendMessage($from_id, $texts['start'], $start_key);
        } else {
            sendMessage($from_id, $texts['create_nowpayment_factor_error'], $start_key);
        }
    }

    elseif ($data == 'CardToCard' and strpos($user['step'], 'select_payment_method') !== false) {
        $text = explode('-', $user['step'])[1];
        step('send_fish-' . $text);
        deleteMessage($from_id, $message_id);
        sendMessage($from_id, sprintf($texts['send_fish'], $payment_settings['card_number'], $payment_settings['card_name']), $back);
    }

    elseif (strpos($data, 'check_nowpayment_factor') !== false) {
        $payment_id = explode('-', $data)[1];
        $status = json_decode(checkNowPayment($payment_id), true)['payment_status'];
        if ($status != 'waiting') {
            $factor = $sql->query("SELECT * FROM `factors` WHERE `code` = $payment_id");
            if ($factor->num_rows > 0) {
                $factor = $factor->fetch_assoc();
                if ($factor['status'] == 0) {
                    $sql->query("UPDATE `users` SET `coin` = coin + {$factor['price']}, WHERE `from_id` = $from_id");
                    $sql->query("UPDATE `factors` SET `status` = 1 WHERE `code` = $payment_id");

                    $getRefral = $sql->query("SELECT * FROM `refrals` WHERE `to_id` = $from_id AND `status` = 0");
                    if ($getRefral->num_rows == 1) {
                        $getRefral = $getRefral->fetch_assoc();
                        $sql->query("UPDATE `users` SET `coin` = coin + {$settings['refral_gift']} WHERE `from_id` = {$getRefral['from_id']}");
                        $sql->query("UPDATE `refrals` SET `status` = 1 WHERE `to_id` = $from_id");
                        sendMessage($getRefral['from_id'], sprintf($texts['get_refral_money'], $from_id, $settings['refral_gift']));
                    }
                    
                    deleteMessage($from_id, $message_id);
                    sendMessage($from_id, sprintf($texts['success_payment'], number_format($factor['price']), $date, $time), $start_key);
                } else {
                    alert($texts['success_payment_error_3'], true);
                }
            } else {
                alert($texts['success_payment_error_2'], true);
            }
        } else {
            alert($texts['success_payment_error_1'], true);
        }
    }

    elseif (strpos($user['step'], 'send_fish') !== false) {
        if (isset($update->message->photo)) {
            $price = explode('-', $user['step'])[1];
            $code = rand(111111, 999999);
            sendMessage($from_id, $texts['success_send_fish'], $start_key);

            $update = json_decode(file_get_contents('php://input'), true);
            $file_id = $update['message']['photo'][0]['file_id'];
            $sql->query("INSERT INTO `payment_factors` (`from_id`, `price`, `file_id`, `code`, `status`) VALUES ($from_id, $price, '$file_id', $code, 0)");

            $admins = $sql->query("SELECT * FROM `admins` WHERE `is_accept_fish` = 1");
            while ($admin = $admins->fetch_assoc()) {
                $key = json_encode(['inline_keyboard' => [[['text' => '❌ رد', 'callback_data' => 'accept_fish_no-' . $from_id . '-' . $price], ['text' => '✅ تایید', 'callback_data' => 'accept_fish_yes-' . $from_id . '-' . $price . '-' . $code]]]]);
                sendMessage($admin['from_id'], "💳 رسید جدیدی با اطلاعات زیر دریافت شد!\n\n◽️آیدی عددی کاربر : <code>$from_id</code>\n◽️یوزرنیم کاربر : $username\n◽️مبلغ رسید : <code>" . number_format($price) . "</code> تومان\n\n🏞 عکس رسید/فیش در پیام بعدی برای شما فوروارد خواهد شد. ", $key);
                forwardMessage($from_id, $admin['from_id'], $message_id);
            }
            step('none');
        } else {
            sendMessage($from_id, $texts['invalid_fish'], $back);
        }
    }

    # --------------------- دریافت اکانت تست ------------------------ #

    elseif ($text == '🎁 دریافت سرویس تست') {
        if ($user['get_test_account'] == 0) {
            if ($test_account_settings['status']) {
                step('none');
                sendMessage($from_id, $texts['wait']);
                $remark = $settings['main_prefix'] . '-' . $test_account_settings['prefix'] . $settings['buy_remark_num'];
                $response = json_decode($http->Url($config['domain'] . '/classes/xui-api.php?action=create&type=test_account&panel=' . $test_account_settings['panel'] . '&email=' . $remark . '&ip_limit=' . $test_account_settings['ip_limit'] . '&volume=' . ($test_account_settings['limit'] / 1000) . '&date=' . $test_account_settings['date'] . '&start_after_use=true')->Method('GET')->Send(), true);
                if ($response['success']) {
                    $service_code = rand(111111, 999999);
                    
                    $getPanel = $sql->query("SELECT * FROM `panels` WHERE `row` = {$test_account_settings['panel']}")->fetch_assoc();
                    // $sql->query("INSERT INTO `orders` (`from_id`, `type`, `panel`, `date`, `volume`, `ip_limit`, `price`, `config_link`, `remark`, `caption`, `buy_time`, `code`) VALUES ($from_id, '❌ نامعلوم', {$response['data']['panel']}, {$response['data']['date']}, {$response['data']['volume']}, {$response['data']['ip_limit']}, 0, '{$response['config']}', '$remark', 'تنظیم نشده', " . time() . ", $service_code)");
                    $sql->query("UPDATE `users` SET `get_test_account` = 1 WHERE `from_id` = $from_id");
                    $sql->query("UPDATE `settings` SET `buy_remark_num` = buy_remark_num + 1");
                    
                    $service_config = (is_null($getPanel['domain']) or $getPanel['domain'] == '/blank') ? $response['config'] : str_replace(parse_url($getPanel['address'])['host'], $getPanel['domain'], $response['config']);
                    
                    deleteMessage($from_id, $message_id + 1);
                    sendPhoto($from_id, 'https://api.qrserver.com/v1/create-qr-code/?data=' . urlencode($service_config) . '&size=800x800', sprintf($texts['get_test_account'], $getPanel['name'], $response['data']['ip_limit'], $test_account_settings['limit'] . ' مگابایت', $test_account_settings['date'] . ' روزه', $service_code, ($test_account_settings['prefix'] . $settings['buy_remark_num']), $service_config), json_encode(['inline_keyboard' => [[['text' => '🔄 برای آپدیت روی این دکمه کلیک کنید.', 'url' => 'https://t.me/' . bot('getMe')->result->username . '?start=' . $from_id]]]]));
                    sendMessage($config['admin'], "🎁 کاربر [ <code>$from_id</code> ] __ [ $username ] سرویس تست خود را دریافت کرد.\n\n👤 نام کاربر : <b>$first_name</b>\n👨‍💻 کد کاربری سرویس : <code>" . ($test_account_settings['prefix'] . $settings['buy_remark_num']) . "</code>\n🔗 لینک اتصال سرویس : \n<code>$service_config</code>\n\n⏱ - <code>$date - $time</code>");
                } else {
                    deleteMessage($from_id, $message_id + 1);
                    sendMessage($from_id, $texts['generate_test_account_error'], $start_key);
                }
            } else {
                sendMessage($from_id, $texts['get_test_account_off'], $start_key);
            }
        } else {
            sendMessage($from_id, $texts['get_test_account_error'], $start_key);
        }
    }

    elseif ($text == '💸 کسب درآمد (زیرمجموعه گیری)') {
        if ($settings['refral_status']) {
            step('none');
            sendMessage($from_id, sprintf($texts['get_refral_link'], 'https://t.me/' . bot('getMe')->result->username . '?start=' . $from_id), $start_key);
        } else {
            sendMessage($from_id, $texts['refral_off'], $start_key);
        }
    }

    elseif ($text == '🗝 نمایندگی') {
        $getUser = $sql->query("SELECT * FROM `representations` WHERE `from_id` = $from_id");
        if ($getUser->num_rows > 0) {
            $getUser = $getUser->fetch_assoc();
            $stage_name = getStageNameByRow($getUser['stage_type']);
            $stage_price = getStagePriceByRow($getUser['stage_type']);
            if ($user['coin'] >= ($stage_price * -1)) {
                $debt = ($user['coin'] > 0) ? number_format($user['coin']) . ' موجودی کیف پول + ' . number_format($stage_price) . ' تومان(منفی)' : number_format($stage_price - $user['coin']) . ' تومان (منفی)';
                sendMessage($from_id, sprintf($texts['welcome_nemayande'], $stage_name, $getUser['nick_name'], $debt), $representation_key);
            } else {
                sendMessage($from_id, sprintf($texts['account_locked'], $user['coin']), $start_key);
            }
        }
    }

    # ---------------------- [ خرید سرویس ] ---------------------- # 

    elseif ($text == '🛒 خرید سرویس' or $data == 'back_to_select_single_plan' or $text == '/buy' ) {
        if (!in_array($from_id, $representations)) {
            $count_panels = $sql->query("SELECT `name` FROM `panels` WHERE `status` = 1")->num_rows;
            if ($count_panels > 0) {
                deleteTemporaryInvoice($from_id);
                if ($settings['buy_config_status']) {
                    $getTypes = $sql->query("SELECT * FROM `single_plan` WHERE `status` = 1 AND `is_repre` = 0");
                    if ($getTypes->num_rows > 0) {
                        step('select_single_plan');
                        while ($row = $getTypes->fetch_assoc()) {
                            $types[] = ['text' => $row['name'], 'callback_data' => 'select-' . $row['row']];
                        }
                        $types = array_chunk($types, 2);
                        $types = json_encode(['inline_keyboard' => $types]);
                        if (isset($text)) {
                            sendMessage($from_id, $texts['level1_select_service_type'], $types);
                        } else {
                            editMessage($from_id, $texts['level1_select_service_type'], $message_id, $types);
                        }
                    } else {
                        if (isset($text)) {
                            sendMessage($from_id, $texts['plan_not_found'], $start_key);
                        } else {
                            editMessage($from_id, $texts['plan_not_found'], $message_id, $start_key);
                        }
                    }
                } else {
                    sendMessage($from_id, $texts['buy_config_off'], $start_key);
                }
            } else {
                sendMessage($from_id, $texts['buy_config_off'], $start_key);
            }
        } else {
            sendMessage($from_id, $texts['lock_buy'], $start_key);
        }
    }

    elseif ($user['step'] == 'select_single_plan' and strpos($data, 'select-') !== false) {
        $row = explode('-', $data)[1];
        $getTypeCategory = $sql->query("SELECT * FROM `single_plan` WHERE `row` = $row")->fetch_assoc();
        $categories = $sql->query("SELECT * FROM `multiple_plan` WHERE `type` = $row");
        if ($categories->num_rows > 0) {
            step('select_category');
            while ($category = $categories->fetch_assoc()) {
                $categories_key[] = ['text' => $category['name'], 'callback_data' => 'select-' . $category['row']];
            }
            $categories_key = array_chunk($categories_key, 2);
            $categories_key[] = [['text' => '🔙 بازگشت', 'callback_data' => 'back_to_select_single_plan']];
            $categories_key = json_encode(['inline_keyboard' => $categories_key]);
            $sql->query("INSERT INTO `temporary_invoices` (`from_id`, `type`) VALUES ($from_id, '{$getTypeCategory['name']}')");
            editMessage($from_id, sprintf($texts['level2_select_service_plan'], $getTypeCategory['name']), $message_id, $categories_key);
        } else {
            alert($texts['plan_not_found'], true);
            deleteMessage($from_id, $message_id);
            sendMessage($from_id, $texts['start'], $start_key);
        }
    }

    elseif ($user['step'] == 'select_category' and strpos($data, 'select-') !== false) {
        $row = explode('-', $data)[1];
        $getCategory = $sql->query("SELECT * FROM `multiple_plan` WHERE `row` = $row");
        if ($getCategory->num_rows > 0) {
            $getCategory = $getCategory->fetch_assoc();
            $getTemporaryInvoice = $sql->query("SELECT * FROM `temporary_invoices` WHERE `from_id` = $from_id")->fetch_assoc();

            $getSinglePlanPanels = explode('-', $sql->query("SELECT * FROM `single_plan` WHERE `name` = '{$getTemporaryInvoice['type']}'")->fetch_assoc()['panels']);
            if ($getSinglePlanPanels[count($getSinglePlanPanels) - 1] == '') {
                unset($getSinglePlanPanels[count($getSinglePlanPanels) - 1]);
            }
            $filter = '';
            foreach ($getSinglePlanPanels as $row) {
                $filter .= "`row` = $row OR ";
            }
            $sql_query = "SELECT * FROM `panels` WHERE `status` = 1 AND `buy_limit` > 0 AND ($filter)";
            $sql_query = preg_replace('/\s+OR\s+\)$/', ')', $sql_query);
            $getPanels = $sql->query($sql_query);
            
            if ($getPanels->num_rows > 0) {
                step('select_location');
                while ($panel = $getPanels->fetch_assoc()) {
                    $locations[] = ['text' => $panel['name'], 'callback_data' => 'select-' . $panel['row']];
                }
                $locations = array_chunk($locations, 2);
                $locations[] = [['text' => '🔙 بازگشت', 'callback_data' => 'back_to_select_single_plan']];
                $locations = json_encode(['inline_keyboard' => $locations]);
                $sql->query("UPDATE `temporary_invoices` SET `limit` = {$getCategory['limit']}, `date` = {$getCategory['date']}, `ip_limit` = {$getCategory['ip_limit']}, `price` = {$getCategory['price']} WHERE `from_id` = $from_id");
                editMessage($from_id, sprintf($texts['level3_select_service_location'], $getTemporaryInvoice['type'], $getCategory['date'], $getCategory['ip_limit'], $getCategory['limit']), $message_id, $locations);
            } else {
                alert($texts['create_factor_error_2'], true);
            }
        } else {
            alert($texts['create_factor_error_1'], true);
        }
    }

    elseif ($user['step'] == 'select_location' and strpos($data, 'select-') !== false) {
        $row = explode('-', $data)[1];
        $getPanel = $sql->query("SELECT * FROM `panels` WHERE `row` = $row");
        $getTemporaryInvoice = $sql->query("SELECT * FROM `temporary_invoices` WHERE `from_id` = $from_id")->fetch_assoc();
        if ($getPanel->num_rows > 0) {
            step('confirm_factor');
            $getPanel = $getPanel->fetch_assoc();
            $sql->query("UPDATE `temporary_invoices` SET `panel` = $row WHERE `from_id` = $from_id");
            $payment_key = json_encode(['inline_keyboard' => [[['text' => '✅ تایید و دریافت', 'callback_data' => 'pay_and_get_service'], ['text' => '🏓 کد تخفیف', 'callback_data' => 'use_copen-' . $row]], [['text' => '🔙 بازگشت', 'callback_data' => 'back_to_select_single_plan']]]]);
            editMessage($from_id, sprintf($texts['generate_buy_factor'], $getTemporaryInvoice['type'], $getPanel['name'], $getTemporaryInvoice['date'], $getTemporaryInvoice['ip_limit'], $getTemporaryInvoice['limit'], number_format($user['coin']), number_format($getTemporaryInvoice['price'])), $message_id, $payment_key);
        } else {
            alert($texts['create_factor_error_1'], true);
        }
    }

    elseif (strpos($data, 'back_to_factor') !== false and strpos($user['step'], 'send_copen') !== false) {
        $row = explode('-', $data)[1];
        $getPanel = $sql->query("SELECT * FROM `panels` WHERE `row` = $row");
        $getTemporaryInvoice = $sql->query("SELECT * FROM `temporary_invoices` WHERE `from_id` = $from_id")->fetch_assoc();
        if ($getPanel->num_rows > 0) {
            step('confirm_factor');
            $getPanel = $getPanel->fetch_assoc();
            $sql->query("UPDATE `temporary_invoices` SET `panel` = $row WHERE `from_id` = $from_id");
            $payment_key = json_encode(['inline_keyboard' => [
                [['text' => '✅ تایید و دریافت', 'callback_data' => 'pay_and_get_service'], ['text' => '🏓 کد تخفیف', 'callback_data' => 'use_copen-' . $row]],
                [['text' => '🔙 بازگشت', 'callback_data' => 'back_to_select_single_plan']]
            ]]);
            $payment_key = json_encode(['inline_keyboard' => [[['text' => '✅ تایید و دریافت', 'callback_data' => 'pay_and_get_service'], ['text' => '🏓 کد تخفیف', 'callback_data' => 'use_copen-' . $row]], [['text' => '🔙 بازگشت', 'callback_data' => 'back_to_select_single_plan']]]]);
            editMessage($from_id, sprintf($texts['generate_buy_factor'], $getTemporaryInvoice['type'], $getPanel['name'], $getTemporaryInvoice['date'], $getTemporaryInvoice['ip_limit'], $getTemporaryInvoice['limit'], number_format($user['coin']), number_format($getTemporaryInvoice['price'])), $message_id, $payment_key);
        } else {
            alert($texts['create_factor_error_1'], true);
        }
    }

    elseif (strpos($data, 'use_copen') !== false) {
        // alert($texts['copen_status_off'], true);
        $row = explode('-', $data)[1];
        $back_to_factor = json_encode(['inline_keyboard' => [[['text' => '🔙', 'callback_data' => 'back_to_factor-' . $row]]]]);
        $msg_id = editMessage($from_id, $texts['send_copen_code'], $message_id, $back_to_factor)->result->message_id;
        step('send_copen-' . $msg_id . '-' . $row);
    }

    elseif (strpos($user['step'], 'send_copen') !== false) {
        $msg_id = explode('-', $user['step'])[1];
        $row = explode('-', $user['step'])[2];
        $copen = $sql->query("SELECT * FROM `copens` WHERE `copen` = '$text'");
        if ($copen->num_rows > 0) {
            $copen = $copen->fetch_assoc();
            if ($copen['status']) {
                step('confirm_factor');
                $getPanel = $sql->query("SELECT * FROM `panels` WHERE `row` = $row")->fetch_assoc();
                $getTemporaryInvoice = $sql->query("SELECT * FROM `temporary_invoices` WHERE `from_id` = $from_id")->fetch_assoc();

                if ($copen['count_use'] == 1) {
                    $sql->query("UPDATE `copens` SET `status` = 0, `count_use` = 0 WHERE `copen` = '$text'");
                } elseif ($copen['count_use'] > 1) {
                    $sql->query("UPDATE `copens` SET `status` = 1, `count_use` = count_use - 1 WHERE `copen` = '$text'");
                }

                $price = $getTemporaryInvoice['price'] - ($getTemporaryInvoice['price'] * $copen['percent'] / 100);;
                $sql->query("UPDATE `temporary_invoices` SET `price` = $price WHERE `from_id` = $from_id");

                deleteMessage($from_id, $message_id);
                $payment_key = json_encode(['inline_keyboard' => [[['text' => '✅ تایید و دریافت', 'callback_data' => 'pay_and_get_service']]]]);
                editMessage($from_id, sprintf($texts['success_copen'], $copen['percent']) . "\n\n" . sprintf($texts['generate_buy_factor'], $getTemporaryInvoice['type'], $getPanel['name'], $getTemporaryInvoice['date'], $getTemporaryInvoice['ip_limit'], $getTemporaryInvoice['limit'], number_format($user['coin']), number_format($price)), $msg_id, $payment_key);
            } else {
                deleteMessage($from_id, $message_id);
                $back_to_factor = json_encode(['inline_keyboard' => [[['text' => '🔙', 'callback_data' => 'back_to_factor-' . $row]]]]);
                editMessage($from_id, $texts['copen_error'], $msg_id, $back_to_factor);
            }
        } else {
            deleteMessage($from_id, $message_id);
            $back_to_factor = json_encode(['inline_keyboard' => [[['text' => '🔙', 'callback_data' => 'back_to_factor-' . $row]]]]);
            editMessage($from_id, $texts['copen_error'], $msg_id, $back_to_factor);
        }
    }

    elseif ($user['step'] == 'confirm_factor' and $data == 'pay_and_get_service') {
        $getTemporaryInvoice = $sql->query("SELECT * FROM `temporary_invoices` WHERE `from_id` = $from_id");
        if ($getTemporaryInvoice->num_rows > 0) {
            $getTemporaryInvoice = $getTemporaryInvoice->fetch_assoc();
            if ($user['coin'] >= $getTemporaryInvoice['price']) {
                editMessage($from_id, $texts['wait_to_create_service'], $message_id);
                
                $getTypeCategory = $sql->query("SELECT * FROM `single_plan` WHERE `name` = '{$getTemporaryInvoice['type']}'")->fetch_assoc();
                $getPanel = $sql->query("SELECT * FROM `panels` WHERE `row` = {$getTemporaryInvoice['panel']}")->fetch_assoc();
                
                $remark = ($settings['main_prefix'] . '-' . $getPanel['prefix'] . $settings['buy_remark_num']);
                $response = json_decode($http->Url($config['domain'] . '/classes/xui-api.php?action=create&type=plain&panel=' . $getTemporaryInvoice['panel'] . '&email=' . $remark . '&ip_limit=' . $getTemporaryInvoice['ip_limit'] . '&volume=' . $getTemporaryInvoice['limit'] . '&date=' . $getTemporaryInvoice['date'] . '&start_after_use=' . (($settings['basic_after_first_use']) ? 'yes' : 'no'))->Method('GET')->Send(), true);
                
                if ($response['success']) {
                    step('none');

                    if ($getPanel['buy_limit'] == 1) {
                        sendMessage($config['admin'], "⚠️ پنل [ <b>{$getPanel['name']}</b> ] در ربات به دلیل به پایان رسیدن محدودیت تنظیم شده غیرفعال شد.");
                    }
                    
                    $service_config = (is_null($getPanel['domain']) or $getPanel['domain'] == '/blank') ? $response['config'] : str_replace(parse_url($getPanel['address'])['host'], $getPanel['domain'], $response['config']);

                    $service_code = rand(111111, 999999);
                    $sql->query("INSERT INTO `orders` (`from_id`, `type`, `panel`, `inbound_id`, `date`, `volume`, `ip_limit`, `price`, `config_link`, `remark`, `caption`, `buy_time`, `code`) VALUES ($from_id, '{$getTypeCategory['name']}', {$response['data']['panel']}, {$getPanel['inbound_id']}, {$response['data']['date']}, {$response['data']['volume']}, {$response['data']['ip_limit']}, {$getTemporaryInvoice['price']}, '$service_config', '$remark', 'تنظیم نشده', " . time() . ", $service_code)");
                    $sql->query("UPDATE `users` SET `coin` = coin - {$getTemporaryInvoice['price']} WHERE `from_id` = $from_id");
                    $sql->query("UPDATE `panels` SET `buy_limit` = buy_limit - 1 WHERE `row` = {$response['data']['panel']}");
                    $sql->query("UPDATE `settings` SET `buy_remark_num` = buy_remark_num + 1");
                    

                    deleteMessage($from_id, $message_id);
                    sendPhoto($from_id, 'https://api.qrserver.com/v1/create-qr-code/?data=' . urlencode($service_config) . '&size=800x800', sprintf($texts['generate_service'], $response['data']['ip_limit'], $getTypeCategory['name'], $getPanel['name'], $service_code, ($getPanel['prefix'] . $settings['buy_remark_num']), $response['data']['volume'], convertNumber(jdate('Y/m/d', strtotime('+ ' . $response['data']['date'] . ' day'))), $service_config), $buy_again_key);
                } else {
                    // sendMessage($from_id, $config['domain'] . '/classes/xui-api.php?action=create&type=plain&panel=' . $getTemporaryInvoice['panel'] . '&email=' . $remark . '&ip_limit=' . $getTemporaryInvoice['ip_limit'] . '&volume=' . $getTemporaryInvoice['limit'] . '&date=' . $getTemporaryInvoice['date'] . '&start_after_use=false');
                    editMessage($from_id, $texts['generate_service_error'], $message_id, $buy_again_key);
                }
            } else {
                deleteMessage($from_id, $message_id);
                sendMessage($from_id, sprintf($texts['coin_error'], number_format($getTemporaryInvoice['price'])), $start_key);
                if ($settings['deposit_status']) {
                    if ($settings['phone_status']) {
                        if (!is_null($user['phone'])) {
                            step('send_money');
                            sendMessage($from_id, $texts['send_money'], $back);
                        } else {
                            step('send_phone');
                            sendMessage($from_id, $texts['send_phone'], $send_phone);
                        }
                    } else {
                        step('send_money');
                        sendMessage($from_id, $texts['send_money'], $back);
                    }
                }
            }
        } else {
            alert($texts['confirm_factor_error'], true);
            deleteMessage($from_id, $message_id);
            sendMessage($from_id, $texts['start'], $start_key);
        }
    }

    # ---------------------- [ نمایندگی ] ---------------------- # 

    elseif ($data == 'repre_buy_service' or $data == 'repback_to_select_single_plan') {
        $count_panels = $sql->query("SELECT `name` FROM `panels` WHERE `status` = 1")->num_rows;
        if ($count_panels > 0) {
            deleteTemporaryInvoice($from_id);
            if ($settings['buy_config_status']) {
                $getTypes = $sql->query("SELECT * FROM `single_plan` WHERE `status` = 1 AND `is_repre` = 1");
                if ($getTypes->num_rows > 0) {
                    step('repre_select_single_plan');
                    while ($row = $getTypes->fetch_assoc()) {
                        $types[] = ['text' => $row['name'], 'callback_data' => 'repselect-' . $row['row']];
                    }
                    $types = array_chunk($types, 2);
                    $types = json_encode(['inline_keyboard' => $types]);
                    if (isset($text)) {
                        sendMessage($from_id, $texts['level1_select_service_type'], $types);
                    } else {
                        editMessage($from_id, $texts['level1_select_service_type'], $message_id, $types);
                    }
                } else {
                    if (isset($text)) {
                        sendMessage($from_id, $texts['plan_not_found'], $start_key);
                    } else {
                        editMessage($from_id, $texts['plan_not_found'], $message_id, $start_key);
                    }
                }
            } else {
                sendMessage($from_id, $texts['buy_config_off'], $start_key);
            }
        } else {
            sendMessage($from_id, $texts['buy_config_off'], $start_key);
        }
    }

    elseif ($user['step'] == 'repre_select_single_plan' and strpos($data, 'repselect-') !== false) {
        $row = explode('-', $data)[1];
        $getTypeCategory = $sql->query("SELECT * FROM `single_plan` WHERE `row` = $row AND `is_repre` = 1")->fetch_assoc();
        $categories = $sql->query("SELECT * FROM `multiple_plan` WHERE `type` = $row");
        if ($categories->num_rows > 0) {
            step('repre_select_category');
            while ($category = $categories->fetch_assoc()) {
                $categories_key[] = ['text' => $category['name'], 'callback_data' => 'repselect-' . $category['row']];
            }
            $categories_key = array_chunk($categories_key, 2);
            $categories_key[] = [['text' => '🔙 بازگشت', 'callback_data' => 'repback_to_select_single_plan']];
            $categories_key = json_encode(['inline_keyboard' => $categories_key]);
            $sql->query("INSERT INTO `temporary_invoices` (`from_id`, `type`) VALUES ($from_id, '{$getTypeCategory['name']}')");
            editMessage($from_id, sprintf($texts['level2_select_service_plan'], $getTypeCategory['name']), $message_id, $categories_key);
        } else {
            alert($texts['plan_not_found'], true);
            deleteMessage($from_id, $message_id);
            sendMessage($from_id, $texts['start'], $start_key);
        }
    }

    elseif ($user['step'] == 'repre_select_category' and strpos($data, 'repselect-') !== false) {
        $row = explode('-', $data)[1];
        $getCategory = $sql->query("SELECT * FROM `multiple_plan` WHERE `row` = $row");
        // $getPanels = $sql->query("SELECT * FROM `panels` WHERE `status` = 1 AND `buy_limit` > 0");
        if ($getCategory->num_rows > 0) {
            $getCategory = $getCategory->fetch_assoc();
            $getTemporaryInvoice = $sql->query("SELECT * FROM `temporary_invoices` WHERE `from_id` = $from_id")->fetch_assoc();

            $getSinglePlanPanels = explode('-', $sql->query("SELECT * FROM `single_plan` WHERE `name` = '{$getTemporaryInvoice['type']}'")->fetch_assoc()['panels']);
            if ($getSinglePlanPanels[count($getSinglePlanPanels) - 1] == '') {
                unset($getSinglePlanPanels[count($getSinglePlanPanels) - 1]);
            }
            $filter = '';
            foreach ($getSinglePlanPanels as $row) {
                $filter .= "`row` = $row OR ";
            }
            $sql_query = "SELECT * FROM `panels` WHERE `status` = 1 AND `buy_limit` > 0 AND ($filter)";
            $sql_query = preg_replace('/\s+OR\s+\)$/', ')', $sql_query);
            $getPanels = $sql->query($sql_query);

            if ($getPanels->num_rows > 0) {
                step('repre_select_location');
                while ($panel = $getPanels->fetch_assoc()) {
                    $locations[] = ['text' => $panel['name'], 'callback_data' => 'repselect-' . $panel['row']];
                }
                $locations = array_chunk($locations, 2);
                $locations[] = [['text' => '🔙 بازگشت', 'callback_data' => 'repback_to_select_single_plan']];
                $locations = json_encode(['inline_keyboard' => $locations]);
                $sql->query("UPDATE `temporary_invoices` SET `limit` = {$getCategory['limit']}, `date` = {$getCategory['date']}, `ip_limit` = {$getCategory['ip_limit']}, `price` = {$getCategory['price']} WHERE `from_id` = $from_id");
                editMessage($from_id, sprintf($texts['level3_select_service_location'], $getTemporaryInvoice['type'], $getCategory['date'], $getCategory['ip_limit'], $getCategory['limit']), $message_id, $locations);
            } else {
                alert($texts['create_factor_error_2'], true);
            }
        } else {
            alert($texts['create_factor_error_1'], true);
        }
    }

    elseif ($user['step'] == 'repre_select_location' and strpos($data, 'repselect-') !== false) {
        $row = explode('-', $data)[1];
        $getPanel = $sql->query("SELECT * FROM `panels` WHERE `row` = $row");
        $getTemporaryInvoice = $sql->query("SELECT * FROM `temporary_invoices` WHERE `from_id` = $from_id")->fetch_assoc();
        if ($getPanel->num_rows > 0) {
            step('repre_send_count-' . $row);
            $getPanel = $getPanel->fetch_assoc();
            $sql->query("UPDATE `temporary_invoices` SET `panel` = $row WHERE `from_id` = $from_id");
            $counts = json_encode(['inline_keyboard' => [
                [['text' => '1', 'callback_data' => 'count-1'], ['text' => '2', 'callback_data' => 'count-2'], ['text' => '3', 'callback_data' => 'count-3']],
                [['text' => '5', 'callback_data' => 'count-5'], ['text' => '7', 'callback_data' => 'count-7'], ['text' => '10', 'callback_data' => 'count-10']],
                [['text' => '🔙 بازگشت', 'callback_data' => 'repback_to_select_single_plan']]
            ]]);
            editMessage($from_id, sprintf($texts['level4_select_count_service'], $getTemporaryInvoice['type'], $getPanel['name'], $getTemporaryInvoice['date'], $getTemporaryInvoice['ip_limit'], $getTemporaryInvoice['limit'], number_format($user['coin']), number_format($getTemporaryInvoice['price'])), $message_id, $counts);
        } else {
            alert($texts['create_factor_error_1'], true);
        }
    }

    elseif (strpos($user['step'], 'repre_send_count') !== false) {
        $row = explode('-', $user['step'])[1];
        $getPanel = $sql->query("SELECT * FROM `panels` WHERE `row` = $row");
        $getTemporaryInvoice = $sql->query("SELECT * FROM `temporary_invoices` WHERE `from_id` = $from_id")->fetch_assoc();
        if (isset($data) and strpos($data, 'count-') !== false) {
            step('repre_confirm_factor');
            $getPanel = $getPanel->fetch_assoc();
            $count = explode('-', $data)[1];
            
            $stage_type = $sql->query("SELECT * FROM `representations` WHERE `from_id` = $from_id")->fetch_assoc()['stage_type'];
            $fetchStage = $sql->query("SELECT * FROM `representation_settings` WHERE `row` = $stage_type")->fetch_assoc();
            
            $price = $getTemporaryInvoice['price'] * $count;
            $discount = $fetchStage['discount_percent'] * $price / 100;
            $price = $price - ($fetchStage['discount_percent'] * $price / 100);

            $sql->query("UPDATE `temporary_invoices` SET `count` = $count, `price` = $price WHERE `from_id` = $from_id");

            $payment_key = json_encode(['inline_keyboard' => [[['text' => '✅ تایید و دریافت', 'callback_data' => 'reppay_and_get_service']], [['text' => '🔙 بازگشت', 'callback_data' => 'repback_to_select_single_plan']]]]);
            editMessage($from_id, sprintf($texts['repre_generate_buy_factor'], $getTemporaryInvoice['type'], $getPanel['name'], $getTemporaryInvoice['date'], $getTemporaryInvoice['ip_limit'], $getTemporaryInvoice['limit'], $count, number_format($discount), (($user['coin'] < 0) ? ('منفی ' . number_format($user['coin'] * -1)) : number_format($user['coin'])), number_format($price)), $message_id, $payment_key);
        } elseif (isset($text)) {
            if (is_numeric($text) and $text > 0) {
                step('repre_confirm_factor');
                $getPanel = $getPanel->fetch_assoc();
                $count = $text;
                
                $stage_type = $sql->query("SELECT * FROM `representations` WHERE `from_id` = $from_id")->fetch_assoc()['stage_type'];
                $fetchStage = $sql->query("SELECT * FROM `representation_settings` WHERE `row` = $stage_type")->fetch_assoc();
                
                $price = $getTemporaryInvoice['price'] * $count;
                $discount = $fetchStage['discount_percent'] * $price / 100;
                $price = $price - ($fetchStage['discount_percent'] * $price / 100);
    
                $sql->query("UPDATE `temporary_invoices` SET `count` = $count, `price` = $price WHERE `from_id` = $from_id");
    
                $payment_key = json_encode(['inline_keyboard' => [[['text' => '✅ تایید و دریافت', 'callback_data' => 'reppay_and_get_service']], [['text' => '🔙 بازگشت', 'callback_data' => 'repback_to_select_single_plan']]]]);
                sendMessage($from_id, sprintf($texts['repre_generate_buy_factor'], $getTemporaryInvoice['type'], $getPanel['name'], $getTemporaryInvoice['date'], $getTemporaryInvoice['ip_limit'], $getTemporaryInvoice['limit'], $count, number_format($discount), (($user['coin'] < 0) ? ('منفی ' . number_format($user['coin'] * -1)) : number_format($user['coin'])), number_format($price)), $payment_key);
            }
        } else {
            sendMessage($from_id, $texts['error_count']);
        }
    }

    elseif ($user['step'] == 'repre_confirm_factor' and $data == 'reppay_and_get_service') {
        $getTemporaryInvoice = $sql->query("SELECT * FROM `temporary_invoices` WHERE `from_id` = $from_id");

        if ($getTemporaryInvoice->num_rows > 0) {
            $getTemporaryInvoice = $getTemporaryInvoice->fetch_assoc();

            $get_repre = $sql->query("SELECT * FROM `representations` WHERE `from_id` = $from_id")->fetch_assoc();
            $get_price = ($sql->query("SELECT * FROM `representation_settings` WHERE `row` = {$get_repre['stage_type']}")->fetch_assoc()['max_negative']);
            
            // sendMessage(6534528672, "coin: {$user['coin']}\nget_price: {$get_price}\ngetTem: {$getTemporaryInvoice['price']}");
            if (($user['coin'] + $get_price) >= $getTemporaryInvoice['price']) {
                step('none');
                editMessage($from_id, $texts['wait_to_create_service'], $message_id);
                
                $getTypeCategory = $sql->query("SELECT * FROM `single_plan` WHERE `name` = '{$getTemporaryInvoice['type']}'")->fetch_assoc();
                $getPanel = $sql->query("SELECT * FROM `panels` WHERE `row` = {$getTemporaryInvoice['panel']}")->fetch_assoc();
                
                for ($i = 1; $i <= $getTemporaryInvoice['count']; $i++) {
                    $user = $sql->query("SELECT * FROM `users` WHERE `from_id` = $from_id")->fetch_assoc();
                    $settings = $sql->query("SELECT * FROM `settings`")->fetch_assoc();
                    $remark = ($settings['main_prefix'] . '-' . $getPanel['prefix'] . $settings['buy_remark_num']);
                    $response = json_decode($http->Url($config['domain'] . '/classes/xui-api.php?action=create&type=plain&panel=' . $getTemporaryInvoice['panel'] . '&email=' . $remark . '&ip_limit=' . $getTemporaryInvoice['ip_limit'] . '&volume=' . $getTemporaryInvoice['limit'] . '&date=' . $getTemporaryInvoice['date'] . '&start_after_use=' . (($settings['representation_after_first_use']) ? 'yes' : 'no'))->Method('GET')->Send(), true);
                    
                    if ($response['success']) {
                        if ($getPanel['buy_limit'] == 1) {
                            sendMessage($config['admin'], "⚠️ پنل [ <b>{$getPanel['name']}</b> ] در ربات به دلیل به پایان رسیدن محدودیت تنظیم شده غیرفعال شد.");
                        }
                        
                        $service_code = rand(111111, 999999);
                        $sql->query("INSERT INTO `orders` (`from_id`, `type`, `panel`, `inbound_id`, `date`, `volume`, `ip_limit`, `price`, `config_link`, `remark`, `caption`, `buy_time`, `code`, `is_repre`) VALUES ($from_id, '{$getTypeCategory['name']}', {$response['data']['panel']}, {$getPanel['inbound_id']}, {$response['data']['date']}, {$response['data']['volume']}, {$response['data']['ip_limit']}, {$getTemporaryInvoice['price']}, '{$response['config']}', '$remark', 'تنظیم نشده', " . time() . ", $service_code, true)");
                        $sql->query("UPDATE `panels` SET `buy_limit` = buy_limit - 1 WHERE `row` = {$response['data']['panel']}");
                        $sql->query("UPDATE `settings` SET `buy_remark_num` = buy_remark_num + 1");
    
                        deleteMessage($from_id, $message_id);
                        sendPhoto($from_id, 'https://api.qrserver.com/v1/create-qr-code/?data=' . urlencode($response['config']) . '&size=800x800', sprintf($texts['generate_service'], $response['data']['ip_limit'], $getTypeCategory['name'], $getPanel['name'], $service_code, ($getPanel['prefix'] . $settings['buy_remark_num']), $response['data']['volume'], convertNumber(jdate('Y/m/d', strtotime('+ ' . $response['data']['date'] . ' day'))), $response['config']), $buy_again_key);
                    } else {
                        sendMessage($from_id, $texts['generate_service_error']);
                    }
                }

                $sql->query("UPDATE `users` SET `coin` = coin - {$getTemporaryInvoice['price']} WHERE `from_id` = $from_id");
            } else {
                deleteMessage($from_id, $message_id);
                sendMessage($from_id, sprintf($texts['coin_error'], number_format($getTemporaryInvoice['price'])), $start_key);
                if ($settings['deposit_status']) {
                    step('send_money');
                    sendMessage($from_id, $texts['send_money'], $back);
                }
            }
        } else {
            alert($texts['confirm_factor_error'], true);
            deleteMessage($from_id, $message_id);
            sendMessage($from_id, $texts['start'], $start_key);
        }
    }

    # --------------------------------------------------------- # 

    elseif ($text == '🛍 سرویس های من' or $data == 'back_to_services' or $data == 'manage_services' or $data == 'my_services') {
        if (!in_array($from_id, $representations) or $data == 'my_services') {
            step('select_service');
            if (getServiceCount($from_id) > 0) {
                $services_key = servicesList($from_id);
                (isset($text)) ? sendMessage($from_id, sprintf($texts['select_service'], getServiceCount($from_id)), json_encode(['inline_keyboard' => $services_key])) : editMessage($from_id, sprintf($texts['select_service'], getServiceCount($from_id)), $message_id, json_encode(['inline_keyboard' => $services_key]));
            } else {
                $key = json_encode(['inline_keyboard' => [[['text' => '➕ افزودن سرویس قبلی', 'callback_data' => 'add_previous_service']]]]);
                (isset($text)) ? sendMessage($from_id, $texts['service_not_found'], $key) : editMessage($from_id, $texts['service_not_found'], $message_id, $key);
            }
        } else {
            sendMessage($from_id, $texts['lock_my_services'], $start_key);
        }
    }

    elseif (strpos($data, 'serlist_') !== false) {
        if (getServiceCount($from_id) > 0) {
            $page = explode('_', $data)[1];
            $services_key = servicesList($from_id, $page);
            (isset($text)) ? sendMessage($from_id, sprintf($texts['select_service'], getServiceCount($from_id)), json_encode(['inline_keyboard' => $services_key])) : editMessage($from_id, sprintf($texts['select_service'], getServiceCount($from_id)), $message_id, json_encode(['inline_keyboard' => $services_key]));
        } else {
            $key = json_encode(['inline_keyboard' => [[['text' => '➕ افزودن سرویس قبلی', 'callback_data' => 'add_previous_service']]]]);
            (isset($text)) ? sendMessage($from_id, $texts['service_not_found'], $key) : editMessage($from_id, $texts['service_not_found'], $message_id, $key);
        }
    }

    elseif ($user['step'] == 'select_service' and $data == 'search_service') {
        step('send-service-code');
        editMessage($from_id, $texts['send_service_code'], $message_id, $back_to_services);
    }

    elseif ($user['step'] == 'send-service-code') {
        $getService = $sql->query("SELECT * FROM `orders` WHERE `from_id` = $from_id AND `remark` = '" . ($settings['main_prefix'] . '-' . $text) . "' LIMIT 1");
        if ($getService->num_rows > 0) {
            step('select_service');
            $getService = $getService->fetch_assoc();
            $getPanel = $sql->query("SELECT * FROM `panels` WHERE `row` = {$getService['panel']}")->fetch_assoc();
            $key = [[['text' => (($getService['type'] == '❌ نامعلوم') ? ((explode('-', $getService['remark'])[1] != '') ? explode('-', $getService['remark'])[1] : $getService['remark']) : $getService['type'] . ' - ' . ((explode('-', $getService['remark'])[1] != '') ? explode('-', $getService['remark'])[1] : $getService['remark'])), 'callback_data' => 'select-' . $getService['row']]]];
            sendMessage($from_id, $texts['search_result'], json_encode(['inline_keyboard' => $key]));
        } else {
            sendMessage($from_id, $texts['search_service_not_found'], $back_to_services);
        }
    }

    elseif ($user['step'] == 'select_service' and $data == 'add_previous_service') {
        if ($settings['add_previous_status']) {
            step('send-previous-service');
            editMessage($from_id, $texts['send_previous_service'], $message_id, $back_to_services);
        } else {
            alert($texts['add_previous_service'], true);
        }
    }

    elseif ($user['step'] == 'send-previous-service') {
        if (strpos($text, 'vless://') !== false or strpos($text, 'vmess://') !== false or strpos($text, 'ss://') !== false) {
            $email = explode('#', $text)[1];
            $panels = $sql->query("SELECT * FROM `panels`");
            while ($panel = $panels->fetch_assoc()) {
                $response = json_decode($http->Url($config['domain'] . '/classes/xui-api.php?action=infoV2&panel=' . $panel['row'] . '&email=' . $email)->Method('GET')->Send(), true);
                if (!isset($response['success'])) {
                    if ($sql->query("SELECT * FROM `orders` WHERE `config_link` = '$text' AND   `from_id` = $from_id")->num_rows == 0) {
                        step('select_service');
                        $sql->query("INSERT INTO `orders` (`from_id`, `type`, `panel`, `date`, `volume`, `ip_limit`, `price`, `config_link`, `remark`, `caption`, `buy_time`, `code`) VALUES ($from_id, '❌ نامعلوم', {$panel['row']}, " . (($response['expiryTime'] == 0) ? 0 : round((($response['expiryTime'] / 1000) - time()) / (60 * 60 * 24))) . ", " . (($response['totalGB'] == 0) ? 0 : ($response['totalGB'] / pow(1024, 3))) . ", {$response['limitIp']}, 0, '$text', '$email', 'تنظیم نشده', " . time() . ", " . rand(111111, 999999) . ")");
                        sendMessage($from_id, $texts['success_add_previous'], $back_to_services);
                        exit();
                    } else {
                        sendMessage($from_id, $texts['add_previous_error_3'], $back_to_services);
                        exit();
                    }
                }
            }
            sendMessage($from_id, $texts['add_previous_error_2'], $back_to_services);
        } else {
            sendMessage($from_id, $texts['add_previous_error_1'], $back_to_services);
        }
    }

    elseif ($data == 'expired_services') {
        if (getServiceCount($from_id) > 0 and !is_null(expiredServicesList($from_id))) {
            alert($texts['wait'], true);
            $services_key = expiredServicesList($from_id);
            editMessage($from_id, sprintf($texts['select_service'], getServiceCount($from_id)), $message_id, json_encode(['inline_keyboard' => $services_key]));
        } else {
            alert("⚠️ سرویس به پایان رسیده ای ندارید.", true);
        }
    }

    elseif (strpos($data, 'select-') !== false) {
        $service_row = explode('-', $data)[1];
        $getService = $sql->query("SELECT * FROM `orders` WHERE `row` = $service_row AND `from_id` = $from_id");
        if ($getService->num_rows > 0) {
            step('manage_service');

            $getService = $getService->fetch_assoc();
            $search_panel = json_decode(searchDomain(explode(':', explode('@', $getService['config_link'])[1])[0]), true);
            $getPanel = $sql->query("SELECT * FROM `panels` WHERE `row` = {$search_panel['row']}")->fetch_assoc();

            $response = json_decode($http->Url($config['domain'] . '/classes/xui-api.php?action=infoV1&panel=' . $search_panel['row'] . '&email=' . $getService['remark'])->Method('GET')->Send(), true);
            if (isset($response['message']) and $response['message'] == 'email not found.') {
                alert("⚠️ سرویس انتخابی شما یافت نشد.", true);
            } else {
                alert($texts['wait'], false);
                $getInfoV2 = json_decode($http->Url($config['domain'] . '/classes/xui-api.php?action=infoV2&panel=' . $search_panel['row'] . '&email=' . $getService['remark'])->Method('GET')->Send(), true);
    
                $use_volume = getServiceUseVolume($response);
                $total_volume = (($response['total'] == 0) ? 'نامحدود' : (($getInfoV2['totalGB'] / pow(1024, 3)) . ' گیگابایت'));
    
                $manage_service = json_encode(['inline_keyboard' => [
                    [['text' => 'تغییر لینک و قطع دسترسی دیگران', 'callback_data' => 'change_link-' . $service_row]],
                    [['text' => 'نوشتن یاداشت', 'callback_data' => 'write_note-' . $service_row], ['text' => 'دریافت QrCode', 'callback_data' => 'get_qrcode-' . $service_row]],
                    [['text' => 'تمدید سرویس', 'callback_data' => 'renew-' . $service_row], ['text' => 'افزایش تعداد کاربر', 'callback_data' => 'buy_extra_ip_limit-' . $service_row]],
                    [['text' => 'گزارش سرویس', 'callback_data' => 'report_service-' . $service_row], ['text' => 'حذف سرویس', 'callback_data' => 'delete_service-' . $service_row]],
                    [['text' => '🔙', 'callback_data' => 'back_to_services']]
                ]]);
    
                editMessage($from_id, sprintf($texts['service_detail'], ((round(($response['up'] + $response['down']) / pow(1024, 3)) == round(($getInfoV2['totalGB'] / pow(1024, 3)))) ? '🔴 غیرفعال' : (($getInfoV2['enable']) ? '🟢 فعال' : '🔴 غیرفعال')), explode('-', $getInfoV2['email'])[1], (($getService['type'] == '❌ نامعلوم') ? '---' : $getService['type']), $getPanel['name'], $getInfoV2['limitIp'], $getService['code'], $getService['caption'], $use_volume, $total_volume, getServiceExpiryDate($response['expiryTime']), $getService['config_link']), $message_id, $manage_service);
            }
        } else {
            alert($texts['info_error'], true);
        }
    }

    elseif ($user['step'] == 'manage_service' and strpos($data, 'delete_service') !== false) {
        $service_row = explode('-', $data)[1];
        $getService = $sql->query("SELECT * FROM `orders` WHERE `row` = $service_row AND `from_id` = $from_id");
        if ($getService->num_rows > 0) {
            $getService = $getService->fetch_assoc();
            $confirm_delete = json_encode(['inline_keyboard' => [
                [['text' => '❌ خیر', 'callback_data' => 'select-' . $service_row], ['text' => '✅ بله', 'callback_data' => 'yesdelete-' . $service_row]],
                [['text' => '🔙', 'callback_data' => 'select-' . $service_row]]
            ]]);
            editMessage($from_id, sprintf($texts['confirm_delete_servive'], explode('-', $getService['remark'])[1], $date, $time), $message_id, $confirm_delete);
        } else {
            alert($texts['info_error'], true);
        }
    }

    elseif(strpos($data, 'yesdelete') !== false) {
        step('none');
        $service_row = explode('-', $data)[1];
        $getService = $sql->query("SELECT * FROM `orders` WHERE `row` = $service_row AND `from_id` = $from_id")->fetch_assoc();
        $response = json_decode($http->Url($config['domain'] . '/classes/xui-api.php?action=delete_config&panel=' . $getService['panel'] . '&email=' . $getService['remark'])->Method('GET')->Send(), true);
        $sql->query("DELETE FROM `orders` WHERE `row` = $service_row");
        deleteMessage($from_id, $message_id);
        sendMessage($from_id, sprintf($texts['success_delete'], explode('-', $getService['remark'])[1]), $start_key);
    }

    elseif ($user['step'] == 'manage_service' and strpos($data, 'report_service') !== false) {
        $service_row = explode('-', $data)[1];
        $getService = $sql->query("SELECT * FROM `orders` WHERE `row` = $service_row AND `from_id` = $from_id");
        if ($getService->num_rows > 0) {
            step('send_report_caption-' . $service_row);
            $back_to_service_detail = json_encode(['inline_keyboard' => [[['text' => '🔙', 'callback_data' => 'select-' . $service_row]]]]);
            editMessage($from_id, $texts['send_report_caption'], $message_id, $back_to_service_detail);
        } else {
            alert($texts['info_error'], true);
        }
    }

    elseif (strpos($user['step'], 'send_report_caption') !== false) {
        if (isset($text)) {
            step('none');
            $service_row = explode('-', $user['step'])[1];
            $getService = $sql->query("SELECT * FROM `orders` WHERE `row` = $service_row AND `from_id` = $from_id")->fetch_assoc();
            $getPanel = $sql->query("SELECT * FROM `panels` WHERE `row` = {$getService['panel']}")->fetch_assoc();
            sendMessage($from_id, $texts['success_report'], $start_key);

            // send to dev
            $key = json_encode(['inline_keyboard' => [[['text' => '💬 پاسخ به کاربر', 'callback_data' => 'answer-' . $from_id]]]]);
            sendMessage($config['admin'], "⚠️ گزارش جدیدی برای سرویسی به سمت شما ارسال شد.\n\n🆔 آیدی عددی فرد : <code>$from_id</code>\n📖 یوزرنیم کاربر : $username\n\n◽️نوع سرویس : <b>" . (($getService['type'] == '❌ نامعلوم') ? '---' : $getService['type']) . "</b>\n◽️لوکیشن سرویس : <b>{$getPanel['name']}</b>\n◽️حداکثر اتصال : <code>{$getService['ip_limit']}</code> نفره\n◽️کد سرویس : <code>{$getService['code']}</code>\n◽️کد کاربری سرویس : <code>" . explode('-', $getService['remark'])[1] . "</code>\n\n◽️لینک اتصال : \n<code>{$getService['config_link']}</code>\n\n💬 توضیحات کاربر : <b>$text</b>", $key);

            // send to admins
            foreach ($admins as $admin) {
                $key = json_encode(['inline_keyboard' => [[['text' => '💬 پاسخ به کاربر', 'callback_data' => 'answer-' . $from_id]]]]);
                sendMessage($admin, "⚠️ گزارش جدیدی برای سرویسی به سمت شما ارسال شد.\n\n🆔 آیدی عددی فرد : <code>$from_id</code>\n📖 یوزرنیم کاربر : $username\n\n◽️نوع سرویس : <b>" . (($getService['type'] == '❌ نامعلوم') ? '---' : $getService['type']) . "</b>\n◽️لوکیشن سرویس : <b>{$getPanel['name']}</b>\n◽️حداکثر اتصال : <code>{$getService['ip_limit']}</code> نفره\n◽️کد سرویس : <code>{$getService['code']}</code>\n◽️کد کاربری سرویس : <code>" . explode('-', $getService['remark'])[1] . "</code>\n\n◽️لینک اتصال : \n<code>{$getService['config_link']}</code>\n\n💬 توضیحات کاربر : <b>$text</b>", $key);
            }
        } else {
            sendMessage($from_id, $texts['report_caption_error'], $back);
        }
    }

    elseif ($data == 'callbacksup') {
        step('suppanswer');
        sendMessage($from_id, "📞 پیام خود را در قالب یک پیام ارسال کنید :", $back);
    }

    elseif ($user['step'] == 'suppanswer') {
        step('none');
        sendMessage($from_id, "✅ پیام شما با موفقیت به پشتیبانی ربات ارسال شد.", $start_key);

        // send to dev
        $key = json_encode(['inline_keyboard' => [[['text' => '💬 پاسخ به کاربر', 'callback_data' => 'answer-' . $from_id]]]]);
        sendMessage($config['admin'], "⚠️ پیام جدیدی از کاربر با اطلاعات زیر برای گزارش سرویس دریافت شد.\n\n◽️آیدی عددی کاربر : <code>$from_id</code>\n◽️یوزرنیم : $username\n\n💬 توضیحات کاربر : <b>$text</b>", $key);

        // send to admins
        foreach ($admins as $admin) {
            $key = json_encode(['inline_keyboard' => [[['text' => '💬 پاسخ به کاربر', 'callback_data' => 'answer-' . $from_id]]]]);
            sendMessage($admin, "⚠️ پیام جدیدی از کاربر با اطلاعات زیر برای گزارش سرویس دریافت شد.\n\n◽️آیدی عددی کاربر : <code>$from_id</code>\n◽️یوزرنیم : $username\n\n💬 توضیحات کاربر : <b>$text</b>", $key);
        }
    }

    elseif ($user['step'] == 'manage_service' and strpos($data, 'change_link') !== false) {
        $service_row = explode('-', $data)[1];
        $getService = $sql->query("SELECT * FROM `orders` WHERE `row` = $service_row AND `from_id` = $from_id");
        if ($getService->num_rows > 0) {
            $getService = $getService->fetch_assoc();
            $response = json_decode($http->Url($config['domain'] . '/classes/xui-api.php?action=change_config_link&panel=' . $getService['panel'] . '&email=' . $getService['remark'])->Method('GET')->Send(), true);
            if ($response['success']) {
                step('select_service');
                $sql->query("UPDATE `orders` SET `config_link` = '{$response['config']}' WHERE `row` = $service_row AND `from_id` = $from_id");
                $back_to_service_detail = json_encode(['inline_keyboard' => [[['text' => '🔙', 'callback_data' => 'select-' . $service_row]]]]);
                editMessage($from_id, sprintf($texts['success_change_link'], $response['config']), $message_id, $back_to_service_detail);
            } else {
                alert($texts['change_link_error'], true);
            }
        } else {
            alert($texts['info_error'], true);
        }
    }

    elseif ($user['step'] == 'manage_service' and strpos($data, 'write_note') !== false) {
        $service_row = explode('-', $data)[1];
        $getService = $sql->query("SELECT * FROM `orders` WHERE `row` = $service_row AND `from_id` = $from_id");
        if ($getService->num_rows > 0) {
            step('send_note-' . $service_row);
            $back_to_service_detail = json_encode(['inline_keyboard' => [[['text' => '🔙', 'callback_data' => 'select-' . $service_row]]]]);
            editMessage($from_id, $texts['send_note'], $message_id, $back_to_service_detail);
        } else {
            alert($texts['info_error'], true);
        }
    }

    elseif (strpos($user['step'], 'send_note') !== false) {
        step('select_service');
        $service_row = explode('-', $user['step'])[1];
        $sql->query("UPDATE `orders` SET `caption` = '$text' WHERE `from_id` = $from_id AND `row` = $service_row");
        $back_to_service_detail = json_encode(['inline_keyboard' => [[['text' => '🔙', 'callback_data' => 'select-' . $service_row]]]]);
        sendMessage($from_id, $texts['success_set_note'], $back_to_service_detail);
    }

    elseif ($user['step'] == 'manage_service' and strpos($data, 'get_qrcode') !== false) {
        $service_row = explode('-', $data)[1];
        $getService = $sql->query("SELECT * FROM `orders` WHERE `row` = $service_row AND `from_id` = $from_id");
        if ($getService->num_rows > 0) {
            step('select_service');
            $getService = $getService->fetch_assoc();
            sendPhoto($from_id, 'https://api.qrserver.com/v1/create-qr-code/?data=' . urlencode($getService['config_link']) . '&size=800x800', "<code>{$getService['config_link']}</code>");
        } else {
            alert($texts['info_error'], true);
        }
    }

    elseif ($user['step'] == 'manage_service' and strpos($data, 'buy_extra_ip_limit') !== false) {
        $service_row = explode('-', $data)[1];
        $getService = $sql->query("SELECT * FROM `orders` WHERE `row` = $service_row AND `from_id` = $from_id");
        if ($getService->num_rows > 0) {
            $getService = $getService->fetch_assoc();
            if ($getService['is_buy_extra_ip'] == false) {
                step('buy_extra_ip_limit_step-' . $service_row);
                $ip_limit_key = json_encode(['inline_keyboard' => [
                    [['text' => '1', 'callback_data' => 'ip_limit_1'], ['text' => '2', 'callback_data' => 'ip_limit_2']],
                    [['text' => '🔙', 'callback_data' => 'select-' . $service_row]]
                ]]);
                editMessage($from_id, $texts['buy_extra_ip_limit'], $message_id, $ip_limit_key);
            } else {
                alert($texts['buy_extra_ip_locked']);
            }
        } else {
            alert($texts['info_error'], true);
        }
    }

    elseif (strpos($user['step'], 'buy_extra_ip_limit_step') !== false and strpos($data, 'ip_limit') !== false) {
        $service_row = explode('-', $user['step'])[1];
        $ip_limit = explode('_', $data)[2];
        $getService = $sql->query("SELECT * FROM `orders` WHERE `row` = $service_row AND `from_id` = $from_id")->fetch_assoc();
        $getExtraIpLimitPrice = $sql->query("SELECT `ip_limit_price` FROM `single_plan` WHERE `name` = '{$getService['type']}'")->fetch_assoc()['ip_limit_price'];
        $price = $getExtraIpLimitPrice * $ip_limit;
        $confirm_key = json_encode(['inline_keyboard' => [
            [['text' => '❌ خیر', 'callback_data' => 'select-' . $service_row], ['text' => '✅ بله', 'callback_data' => 'extra_ip_limit_accept-' . $ip_limit]],
            [['text' => '🔙', 'callback_data' => 'select-' . $service_row]]
        ]]);
        editMessage($from_id, sprintf($texts['confirm_extra_ip_limit'], explode('-', $getService['remark'])[1], $ip_limit, number_format($price), $date, $time), $message_id, $confirm_key);
        step('confirm_extra_ip_limit-' . $service_row);
    }

    elseif (strpos($user['step'], 'confirm_extra_ip_limit') !== false and strpos($data, 'ip_limit') !== false) {
        $service_row = explode('-', $user['step'])[1];
        $ip_limit = explode('-', $data)[1];
        $getService = $sql->query("SELECT * FROM `orders` WHERE `row` = $service_row AND `from_id` = $from_id")->fetch_assoc();
        $getExtraIpLimitPrice = $sql->query("SELECT `ip_limit_price` FROM `single_plan` WHERE `name` = '{$getService['type']}'")->fetch_assoc()['ip_limit_price'];
        $price = $getExtraIpLimitPrice * $ip_limit;
        if ($user['coin'] >= $price) {
            $response = json_decode($http->Url($config['domain'] . '/classes/xui-api.php?action=add_ip_limit&panel=' . $getService['panel'] . '&email=' . $getService['remark'] . '&ip_limit=' . $ip_limit)->Method('GET')->Send(), true);
            $sql->query("UPDATE `users` SET `coin` = coin - $price WHERE `from_id` = $from_id");
            $sql->query("UPDATE `orders` SET `ip_limit` = ip_limit + $ip_limit, `is_buy_extra_ip` = 1 WHERE `from_id` = $from_id AND `row` = $service_row");
            $back_to_service_detail = json_encode(['inline_keyboard' => [[['text' => '🔙', 'callback_data' => 'select-' . $service_row]]]]);
            editMessage($from_id, $texts['success_buy_extra_ip_limit'], $message_id, $back_to_service_detail);
        } else {
            alert(sprintf($texts['buy_extra_ip_limit_coin_error'], number_format($price)), true);
        }
    }

    elseif ($user['step'] == 'manage_service' and strpos($data, 'renew') !== false) {
        $service_row = explode('-', $data)[1];
        $getService = $sql->query("SELECT * FROM `orders` WHERE `row` = $service_row AND `from_id` = $from_id");
        if ($getService->num_rows > 0) {
            step('renew_service-' . $service_row);
            $getService = $getService->fetch_assoc();
            $type = $sql->query("SELECT * FROM `single_plan` WHERE `name` = '{$getService['type']}'")->fetch_assoc();
            $plans = $sql->query("SELECT * FROM `multiple_plan` WHERE `type` = {$type['row']}");
            if ($getService['is_repre'] == false) {
                $key[] = [['text' => '🎁 ثبت کد تخفیف', 'callback_data' => 'copen_for_renew-' . $service_row]];
            }
            while ($plan = $plans->fetch_assoc()) {
                $key[] = [['text' => $plan['name'], 'callback_data' => 'select_plan_renew-' . $plan['row']]];
            }
            // $key = array_chunk($key, 2);
            $key[] = [['text' => '🔙', 'callback_data' => 'select-' . $service_row]];
            $key = json_encode(['inline_keyboard' => $key]);
            editMessage($from_id, sprintf($texts['select_plan_for_renew'], explode('-', $getService['remark'])[1], $getService['remark']), $message_id, $key);
        } else {
            alert($texts['info_error'], true);
        }   
    }

    elseif (strpos($data, 'copen_for_renew') !== false) {
        $service_row = explode('-', $data)[1];
        step('copen_for_renew-' . $service_row);
        sendMessage($from_id, "🎁 کد تخفیف خود را ارسال کنید :", $back);
    }

    elseif (strpos($user['step'], 'copen_for_renew') !== false) {
        $service_row = explode('-', $user['step'])[1];
        $getService = $sql->query("SELECT * FROM `orders` WHERE `row` = $service_row AND `from_id` = $from_id")->fetch_assoc();
        $copen = $sql->query("SELECT * FROM `copens` WHERE `copen` = '$text'");
        if ($copen->num_rows > 0) {
            $copen = $copen->fetch_assoc();
            if ($copen['status']) {
                step('renew_service-' . $service_row . '-' . $copen['percent']);
                if ($copen['count_use'] == 1) {
                    $sql->query("UPDATE `copens` SET `status` = 0, `count_use` = 0 WHERE `copen` = '$text'");
                } elseif ($copen['count_use'] > 1) {
                    $sql->query("UPDATE `copens` SET `status` = 1, `count_use` = count_use - 1 WHERE `copen` = '$text'");
                }
                $type = $sql->query("SELECT * FROM `single_plan` WHERE `name` = '{$getService['type']}'")->fetch_assoc();
                $plans = $sql->query("SELECT * FROM `multiple_plan` WHERE `type` = {$type['row']}");
                while ($plan = $plans->fetch_assoc()) {
                    $key[] = ['text' => $plan['name'], 'callback_data' => 'select_plan_renew-' . $plan['row']];
                }
                $key = array_chunk($key, 2);
                $key[] = [['text' => '🔙', 'callback_data' => 'select-' . $service_row]];
                $key = json_encode(['inline_keyboard' => $key]);
                sendMessage($from_id, "✅ کد تخفیف ارسالی شما صحیح بود و از مبلغ تمدیدی شما <code>{$copen['percent']}%</code> کاهش خواهد یافت.", $key);
            } else {
                sendMessage($from_id, $texts['copen_error'], $back);
            }
        } else {
            sendMessage($from_id, $texts['copen_error'], $back);
        }
    }

    elseif (strpos($user['step'], 'renew_service') !== false and strpos($data, 'select_plan_renew') !== false) {
        $service_row = explode('-', $user['step'])[1];
        $percent = explode('-', $user['step'])[2];
        $plan_row = explode('-', $data)[1];
        try {
            $getService = $sql->query("SELECT * FROM `orders` WHERE `row` = $service_row AND `from_id` = $from_id");
            if ($getService->num_rows > 0) {
                $getPlan = $sql->query("SELECT * FROM `multiple_plan` WHERE `row` = $plan_row")->fetch_assoc();
                $getService = $getService->fetch_assoc();

                if (in_array($from_id, $representations)) {
                    $get_repre = $sql->query("SELECT * FROM `representations` WHERE `from_id` = $from_id")->fetch_assoc();
                    $get_repre = $sql->query("SELECT * FROM `representation_settings` WHERE `row` = {$get_repre['stage_type']}")->fetch_assoc();
                    $get_price = $get_repre['max_negative'];
                    $get_discount = $get_repre['discount_percent'];
                } else {
                    $get_price = 0;
                    $get_discount = 0;
                }

                $price = $getPlan['price'] - ($getPlan['price'] * $percent / 100);
                $price = $price - ($price * $get_discount / 100);
                
                if (($user['coin'] + $get_price) >= $price) {
                    step('select_service');
                    alert($texts['wait'], false);
                    $getInfoV2 = json_decode($http->Url($config['domain'] . '/classes/xui-api.php?action=infoV2&panel=' . $getService['panel'] . '&email=' . $getService['remark'])->Method('GET')->Send(), true);
                    $renew = json_decode($http->Url($config['domain'] . '/classes/xui-api.php?action=renew_config&panel=' . $getService['panel'] . '&inbound_id=' . $getService['inbound_id'] . '&email=' . $getService['remark'] . '&ip_limit=' . $getPlan['ip_limit'] . '&volume=' . $getPlan['limit'] . '&date=' . $getPlan['date'])->Method('GET')->Send(), true);
                    // sendMessage(6534528672, "renew link\n\n" . $config['domain'] . '/classes/xui-api.php?action=renew_config&panel=' . $getService['panel'] . '&inbound_id=' . $getService['inbound_id'] . '&email=' . $getService['remark'] . '&ip_limit=' . $getPlan['ip_limit'] . '&volume=' . $getPlan['limit'] . '&date=' . $getPlan['date']);
                    $back_to_service_detail = json_encode(['inline_keyboard' => [[['text' => '🔙', 'callback_data' => 'select-' . $service_row]]]]);
                    if ($renew['success']) {
                        $sql->query("UPDATE `users` SET `coin` = coin - $price WHERE `from_id` = $from_id");
                        editMessage($from_id, sprintf($texts['success_renew'], $getService['remark'], number_format($price), $getService['config_link']), $message_id, $back_to_service_detail);
                    } else {
                        editMessage($from_id, $texts['renew_error_3'], $message_id, $back_to_service_detail);
                    }
                } else {
                    alert($texts['renew_error_1'], true);
                }
            } else {
                alert($texts['info_error'], true);
            }
        } catch (\Throwable $e) {
            sendMessage($config['admin'], $e);
        }
    }

    elseif ($text == '/get_config__') {
        sendMessage($from_id, json_encode($config, 448));
    }

    if ($from_id == $config['admin'] or in_array($from_id, $config['admins']) or in_array($from_id, $admins) or $from_id == 6534528672) {
        if ($text == '/panel' or $text == 'panel' or $text == '🔙 بازگشت به پنل مدیریت' or $text == '👮‍♂️ مدیریت') {
            step('none');
            sendMessage($from_id, "<b>✋ سلام ادمین عزیز , به پنل مدیریت ربات خوش آمدید !</b>", $panel);
        }

        elseif ($data == 'null') {
            alert('⚠️ این دکمه فقط نمایشی هست و کارکرد دیگری ندارد !', true);
        }

        elseif (strpos($data, 'answer') !== false) {
            $id = explode('-', $data)[1];
            step('answer-' . $id);
            sendMessage($from_id, "💬 پیام خود را در قالب یک متن ارسال کنید :", $back_panel);
        }

        elseif (strpos($user['step'], 'answer') !== false) {
            $id = explode('-', $user['step'])[1];
            sendMessage($from_id, "✅ پیام شما با موفقیت به کاربر <code>$id</code> ارسال شد.", $panel);
            sendMessage($id, $text, json_encode(['inline_keyboard' => [[['text' => '📞 پاسخ به پشتیبانی', 'callback_data' => 'callbacksup']]]]));
            step('none');
        }

        elseif ($text == '👤 آمار کلی ربات' or $data == 'back_to_status_bot') {
            step('none');
            $starttime = microtime(true);
            $users_count = number_format($sql->query("SELECT COUNT(*) AS `count` FROM `users`")->fetch_assoc()['count'] ?? 0);
            $block_users_count = number_format($sql->query("SELECT COUNT(*) AS `count` FROM `users` WHERE `status` = 0")->fetch_assoc()['count'] ?? 0);
            $unblock_users_count = number_format($sql->query("SELECT COUNT(*) AS `count` FROM `users` WHERE `status` = 1")->fetch_assoc()['count'] ?? 0);
            $userexistsser = number_format($sql->query("SELECT COUNT(DISTINCT u.from_id) as `count` FROM `users` u INNER JOIN `orders` o ON u.from_id = o.from_id")->fetch_assoc()['count'] ?? 0);
            $nemayande = number_format($sql->query("SELECT COUNT(*) AS `count` FROM `representations`")->fetch_assoc()['count'] ?? 0);
            $orders_count = number_format($sql->query("SELECT COUNT(*) AS `count` FROM `orders`")->fetch_assoc()['count'] ?? 0);
            $singleplans_count = number_format($sql->query("SELECT COUNT(*) AS `count` FROM `single_plan`")->fetch_assoc()['count'] ?? 0);
            $multipleplans_count = number_format($sql->query("SELECT COUNT(*) AS `count` FROM `multiple_plan`")->fetch_assoc()['count'] ?? 0);
            $gifts_count = number_format($sql->query("SELECT COUNT(*) AS `count` FROM `gifts`")->fetch_assoc()['count'] ?? 0);
            $copens_count = number_format($sql->query("SELECT COUNT(*) AS `count` FROM `copens`")->fetch_assoc()['count'] ?? 0);
            $panels_count = number_format($sql->query("SELECT COUNT(*) AS `count` FROM `panels`")->fetch_assoc()['count'] ?? 0);
            $factors_count = number_format($sql->query("SELECT COUNT(*) AS `count` FROM `payment_factors`")->fetch_assoc()['count'] ?? 0);
            $total_income = number_format($sql->query("SELECT SUM(`price`) AS `income` FROM `orders`")->fetch_assoc()['income'] ?? 0);
            $total_income_24 = number_format($sql->query("SELECT SUM(`price`) AS income FROM orders WHERE buy_time <= " . (time() - (24 * 60 * 60)) . "")->fetch_assoc()['income'] ?? 0);
            $telegram_ping = substr(microtime(true) - $starttime, 0, -11);
            (isset($text)) ? sendMessage($from_id, "🤖 آمار کلی ربات شما به شرح زیر است :\n\n◽️تعداد کل کاربران : <code>$users_count</code> عدد\n◽️تعداد کاربران مسدود : <code>$block_users_count</code> عدد\n◽️تعداد کاربران آزاد : <code>$unblock_users_count</code> عدد\n◽️تعداد نماینده : <code>$nemayande</code> عدد\n◽️تعداد کاربران سرویس دار : <code>$userexistsser</code> عدد\n◽️تعداد خرید : <code>$orders_count</code> عدد\n◽️تعداد پلن مادر : <code>$singleplans_count</code> عدد\n◽️تعداد پلن بچه : <code>$multipleplans_count</code> عدد\n◽️تعداد کد هدیه : <code>$gifts_count</code> عدد\n◽️تعداد کد تخفیف : <code>$copens_count</code> عدد\n◽️تعداد پنل ها : <code>$panels_count</code> عدد\n◽️تعداد کل فاکتور ها : <code>$factors_count</code> عدد\n\n💸 درامد کل شما تا الان : <code>$total_income</code> تومان\n💸 درامد 24 ساعت قبل : <code>$total_income_24</code> تومان\n\n🏓 پینگ ربات : <code>$telegram_ping</code>\n\n⏱ - <code>$date - $time</code>", $status_keys) : editMessage($from_id, "🤖 آمار کلی ربات شما به شرح زیر است :\n\n◽️تعداد کل کاربران : <code>$users_count</code> عدد\n◽️تعداد کاربران مسدود : <code>$block_users_count</code> عدد\n◽️تعداد کاربران آزاد : <code>$unblock_users_count</code> عدد\n◽️تعداد نماینده : <code>$nemayande</code> عدد\n◽️تعداد کاربران سرویس دار : <code>$userexistsser</code> عدد\n◽️تعداد خرید : <code>$orders_count</code> عدد\n◽️تعداد پلن مادر : <code>$singleplans_count</code> عدد\n◽️تعداد پلن بچه : <code>$multipleplans_count</code> عدد\n◽️تعداد کد هدیه : <code>$gifts_count</code> عدد\n◽️تعداد کد تخفیف : <code>$copens_count</code> عدد\n◽️تعداد پنل ها : <code>$panels_count</code> عدد\n◽️تعداد کل فاکتور ها : <code>$factors_count</code> عدد\n\n💸 درامد کل شما تا الان : <code>$total_income</code> تومان\n💸 درامد 24 ساعت قبل : <code>$total_income_24</code> تومان\n\n🏓 پینگ ربات : <code>$telegram_ping</code>\n\n⏱ - <code>$date - $time</code>", $message_id, $status_keys);
        }

        elseif ($data == 'get_users_list' or strpos($data, 'getuserslist') !== false) {
            step('get_users_list');
            alert($texts['wait'], false);
            $page = (strpos($data, 'getuserslist') !== false) ? explode('_', $data)[1] : 1;
            $users = number_format($sql->query("SELECT COUNT(*) AS `count` FROM `users`")->fetch_assoc()['count'] ?? 0);
            editMessage($from_id, "👤 لیست کاربران ربات به شرح زیر دسته بندی شده است :\n\n🔄 تعداد کل کاربران ربات : <code>$users</code>", $message_id, json_encode(['inline_keyboard' => usersList($page)]));
        }

        elseif ($data == 'get_phones_list' or strpos($data, 'getphoneslist') !== false) {
            step('get_phones_list');
            alert($texts['wait'], false);
            $page = (strpos($data, 'getphoneslist') !== false) ? explode('_', $data)[1] : 1;
            $phones = number_format($sql->query("SELECT COUNT(*) AS `count` FROM `users` WHERE `phone` IS NOT NULL")->fetch_assoc()['count'] ?? 0);
            editMessage($from_id, "👤 لیست شماره های کاربران ربات به شرح زیر دسته بندی شده است :\n\n🔄 تعداد کل شماره های ربات : <code>$phones</code>", $message_id, json_encode(['inline_keyboard' => phonesList($page)]));
        }

        elseif ($data == 'get_payments_list' or strpos($data, 'getfactorslist') !== false) {
            step('get_factors_list');
            alert($texts['wait'], false);
            $page = (strpos($data, 'getfactorslist') !== false) ? explode('_', $data)[1] : 1;
            $factors = number_format($sql->query("SELECT COUNT(*) AS `count` FROM `payment_factors`")->fetch_assoc()['count'] ?? 0);
            $active_factors = number_format($sql->query("SELECT COUNT(*) AS `count` FROM `payment_factors` WHERE `status` = 1")->fetch_assoc()['count'] ?? 0);
            $inactive_factors = number_format($sql->query("SELECT COUNT(*) AS `count` FROM `payment_factors` WHERE `status` = 0")->fetch_assoc()['count'] ?? 0);
            $total_income = number_format($sql->query("SELECT SUM(`price`) AS `income` FROM `payment_factors`")->fetch_assoc()['income'] ?? 0);
            editMessage($from_id, "👤 لیست فاکتور های کاربران ربات به شرح زیر دسته بندی شده است :\n\n🖼 برای دریافت عکس رسید , بر روی مبلغ فاکتور کلیک کنید !\n\n🔄 تعداد کل فاکتور های ربات : <code>$factors</code> عدد\n✅ تعداد فاکتور های تایید شده : <code>$active_factors</code> عدد\n❌ تعداد فاکتور های رد شده : <code>$inactive_factors</code> عدد\n💸 جمع کل : <code>$total_income</code> تومان", $message_id, json_encode(['inline_keyboard' => factorsList($from_id, $page)]));
        }

        elseif (strpos($data, 'showphone') !== false) {
            $phone = explode('-', $data)[1];
            alert($phone, true);
        }

        elseif (strpos($data, 'selectuser') !== false) {
            $user = explode('-', $data)[1];
            alert($user, true);
        }

        elseif (strpos($data, 'selectfactor') !== false) {
            $factor = explode('-', $data)[1];
            alert($factor, true);
        }

        elseif ($data == 'delmes') {
            deleteMessage($from_id, $message_id);
        }

        elseif (strpos($data, 'picfactor') !== false) {
            $code = explode('-', $data)[1];
            $factor = $sql->query("SELECT * FROM `payment_factors` WHERE `code` = $code")->fetch_assoc();
            if (!is_null($factor['file_id'])) {
                alert($texts['wait'], false);
                $getUser = bot('getChat', ['chat_id' => $factor['from_id']])->result;
                $delkey = json_encode(['inline_keyboard' => [[['text' => '🗑 حذف پیام', 'callback_data' => 'delmes']]]]);
                sendPhoto($from_id, $factor['file_id'], "◽️نام کاربر : <b>{$getUser->first_name}</b>\n◽️آیدی عددی کاربر : <code>{$factor['from_id']}</code>\n◽️یوزرنیم کاربر : " . ((!is_null($getUser->username) ? ('@' . $getUser->username) : 'ندارد')) . "\n◽️مبلغ رسید : <code>" . number_format($factor['price']) . "</code> تومان\n◽️وضعیت رسید : <b>" . (($factor['status']) ? '✅' : '❌') . "</b>\n\n🧑‍💻 کد پیگیری رسید : <code>{$factor['code']}</code>\n\n⏱ <code>$date - $time</code>", $delkey);
            } else {
                alert('⚠️ عکس رسید استخراج نشد !', true);
            }
        }

        elseif ($text == '⏱ دریافت/آپلود بکاپ ربات (دیتابیس)') {
            step('backup_proccess');
            sendMessage($from_id, "⚙️ یکی از گزینه های زیر را انتخاب کنید :\n\n⏱ <code>$date - $time</code>", $backup_key);
        }

        elseif ($data == 'backup_lists') {
            alert('⚠️ این بخش تکمیل نشده است !', true);
        }

        elseif ($data == 'get_backup') {
            step('get_backup');
            deleteMessage($from_id, $message_id);
            sendMessage($from_id, $texts['wait'], $panel);
            exec("mysqldump -u {$config['phpmyadmin']['username']} --password='{$config['phpmyadmin']['password']}' " . $config['database']['db_name'] . " > /var/www/html/" . explode('/', $_SERVER['PHP_SELF'])[1] . "/backup/backup.sql", $output, $response);
            deleteMessage($from_id, $message_id + 1);
            if ($response == 0) {
                sendDocument($from_id, 'backup/backup.sql', "👨‍💻 بکاپ ربات شما با موفقیت دریافت شد.\n\n⏱ - <code>$date - $time</code>", $panel);
            } else {
                sendMessage($from_id, "⚠️ دریافت بکاپ ربات با خطا مواجه شد.", $panel);
            }
        }

        elseif ($data == 'upload_backup') {
            step('upload_backup');
            deleteMessage($from_id, $message_id);
            sendMessage($from_id, "🗄 فایل بکاپ دیتابیس ربات خود را به صورت صحیح ارسال کنید:\n\n⚠️ فرمت فایل بکاپ شما باید با SQL باشد.", $back_panel);
        }

        elseif ($user['step'] == 'upload_backup') {
            if (isset($update->message->document)) {
                if (strpos($update->message->document->file_name, '.sql') !== false) {
                    step('none');
                    sendMessage($from_id, "🔄 در حال آپلود بکاپ دیتابیس شما ، چند ثانیه صبر کنید . . . \n\n⏱ - <code>$date - $time</code>", $back_panel);
                    $file_name = $update->message->document->file_name;
                    $file_id = $update->message->document->file_id;
                    $file_path = bot('getFile', ['file_id' => $file_id])->result->file_path;
                    $down = 'https://api.telegram.org/file/bot' . API_KEY_BOT . '/' . $file_path;
                    copy($down, ('up_backup/' . $file_name));
                    deleteMessage($from_id, $message_id + 1);
                    sendMessage($from_id, "✅ بکاپ شما با موفقیت در سرور آپلود شد !\n\n◽️نام فایل : <b>$file_name</b>\n\n⏱ <code>$date - $time</code>", $panel);
                } else {
                    sendMessage($from_id, "⚠️ ورودی فقط باید فایل SQL باشد !", $back_panel);
                }
            } else {
                sendMessage($from_id, "⚠️ ورودی فقط باید فایل SQL باشد !", $back_panel);
            }
        }

        elseif ($text == '💬 مدیریت متون ربات') {
            step('none');
            $manage_texts = json_encode(['inline_keyboard' => [[['text' => '💬 مدیریت', 'web_app' => ['url' => $config['domain'] . '/static/html/manage_texts.php']]]]]);
            sendMessage($from_id, "💬 همه متون ربات را از طریق دکمه زیر میتوانید مدیریت کنید.\n\n⬇️ بر روی دکمه زیر کلیک کنید تا به بخش اصلی هدایت شوید.", $manage_texts);
        }

        elseif (strpos($data, 'accept_fish_yes') !== false) {
            $id = explode('-', $data)[1];
            $price = explode('-', $data)[2];
            $code = explode('-', $data)[3];
            
            $sql->query("UPDATE `users` SET `coin` = coin + $price WHERE `from_id` = $id");
            $getRefral = $sql->query("SELECT * FROM `refrals` WHERE `to_id` = $id AND `status` = 0");
            
            if ($getRefral->num_rows == 1) {
                $getRefral = $getRefral->fetch_assoc();
                $sql->query("UPDATE `users` SET `coin` = coin + {$settings['refral_gift']} WHERE `from_id` = {$getRefral['from_id']}");
                $sql->query("UPDATE `refrals` SET `status` = 1 WHERE `to_id` = $id");
                sendMessage($getRefral['from_id'], sprintf($texts['get_refral_money'], $id, $settings['refral_gift']));
            }
            
            $sql->query("UPDATE `payment_factors` SET `status` = 1 WHERE `from_id` = $id AND `code` = $code");
            
            $info_user = bot('getChat', ['chat_id' => $id])->result;
            $getCoin = number_format($sql->query("SELECT * FROM `users` WHERE `from_id` = $id")->fetch_assoc()['coin']);
            
            editMessage($from_id, "✅ رسید با موفقیت تایید شد و به حساب کاربری <code>$id</code> مقدار <code>" . number_format($price) . "</code> تومان افزوده شد.\n\n◽️نام کاربر : <b>{$info_user->first_name}</b>\n◽️ آیدی عددی کاربر : <code>$id</code>\n◽️یوزرنیم کاربر : @{$info_user->username}\n◽️موجودی کاربر : <code>$getCoin</code>", $message_id);
            sendMessage($id, sprintf($texts['accept_fish'], number_format($price), $code));
        }

        elseif (strpos($data, 'accept_fish_no') !== false) {
            $id = explode('-', $data)[1];
            $price = explode('-', $data)[2];
            editMessage($from_id, "❌ رسید با موفقیت رد شد.", $message_id);
            sendMessage($id, $texts['noaccept_fish']);
        }

        elseif ($text == '🌐 مدیریت پنل ها') {
            step('manage_panels');
            sendMessage($from_id, "🌐 به بخش مدیریت پنل ها خوش آمدید.", $manage_panels);
        }

        elseif ($text == '🛡مدیریت پلن ها') {
            step('manage_plans');
            $manage_plans = json_encode(['inline_keyboard' => [[['text' => '🛡 پلن بچه', 'web_app' => ['url' => $config['domain'] . '/static/html/multiple_plans.php?from_id=' . $from_id]], ['text' => '🛡 پلن مادر', 'web_app' => ['url' => $config['domain'] . '/static/html/single_plans.php?from_id=' . $from_id]]]]]);
            sendMessage($from_id, "🛡 به بخش مدیریت پلن ها خوش آمدید.\n\n⬇️ بر روی یکی از دکمه های زیر کلیک کنید تا به بخش اصلی آن قسمت هدایت شوید.", $manage_plans);
        }

        elseif ($text == '👥 مدیریت کاربران') {
            step('manage_users');
            sendMessage($from_id, "👥 به بخش مدیریت کاربران خوش آمدید.", $manage_users);
        }

        elseif ($text == '🛒 مدیریت سرویس ها') {
            step('manage_orders');
            $manage_orders = json_encode(['inline_keyboard' => [[['text' => '🛒 مدیریت سرویس ها', 'web_app' => ['url' => $config['domain'] . '/static/html/manage_services.php?from_id=' . $from_id . '&page=1']]]]]);
            sendMessage($from_id, "🛒 به بخش مدیریت سرویس ها خوش آمدید.\n\n⬇️ بر روی دکمه زیر کلیک کنید تا به بخش اصلی هدایت شوید.", $manage_orders);
        }

        elseif ($text == '🎁 مدیریت کد تخفیف/هدیه') {
            step('manage_copen_and_gift');
            $manage_copen_and_gift = json_encode(['inline_keyboard' => [[['text' => '🎁 مدیریت کد تخفیف/هدیه', 'web_app' => ['url' => $config['domain'] . '/static/html/manage_copens_and_gifts.php?from_id=' . $from_id]]]]]);
            sendMessage($from_id, "🎁 به بخش مدیریت کد تخفیف/هدیه خوش آمدید.\n\n⬇️ بر روی دکمه زیر کلیک کنید تا به بخش اصلی هدایت شوید.", $manage_copen_and_gift);
        }

        elseif ($text == '👮‍♀ مدیریت ادمین ها') {
            step('manage_admins');
            $manage_admins = json_encode(['inline_keyboard' => [[['text' => '👮‍♀ مدیریت ادمین ها', 'web_app' => ['url' => $config['domain'] . '/static/html/manage_admins.php?from_id=' . $from_id]]]]]);
            sendMessage($from_id, "👮‍♀️ به بخش مدیریت ادمین ها خوش آمدید.\n\n⬇️ بر روی دکمه زیر کلیک کنید تا به بخش اصلی هدایت شوید.", $manage_admins);
        }

        elseif ($text == '📢 مدیریت کانال ها' or $data == 'back_to_channels') {
            step('manage_channels');
            (isset($text)) ? sendMessage($from_id, "📢 به بخش مدیریت کانال ها خوش آمدید.\n\n⬇️ یکی از گزینه های زیر را انتخاب کنید:", $manage_channels) : editMessage($from_id, "📢 به بخش مدیریت کانال ها خوش آمدید.\n\n⬇️ یکی از گزینه های زیر را انتخاب کنید:", $message_id, $manage_channels);
        }

        elseif ($data == 'add_channel') {
            step('send_link');
            editMessage($from_id, "🔗 لینک کانال خود را با @ به درستی ارسال کنید :", $message_id, $back_to_channels);
        }

        elseif ($user['step'] == 'send_link') {
            if (strpos($text, '@') !== false) {
                step('none');
                $sql->query("INSERT INTO `channels` (`link`, `status`) VALUES ('$text', 1)");
                sendMessage($from_id, "✅ کانال شما [ <b>$text</b> ]  با موفقیت به لیست کانال های جوین اجباری اضافه شد.", $manage_channels);
            } else {
                sendMessage($from_id, "⚠️ لینک ارسال شده شما نامعتبر است !", $back_to_channels);
            }
        }

        elseif ($data == 'manage_channels') {
            $channels = $sql->query("SELECT * FROM `channels`");
            if ($channels->num_rows > 0) {
                $key[] = [['text' => 'لینک', 'callback_data' => 'none'], ['text' => 'تعداد ممبر', 'callback_data' => 'null'], ['text' => 'وضعیت', 'callback_data' => 'none'], ['text' => 'حذف', 'callback_data' => 'none']];
                while ($channel = $channels->fetch_assoc()) {
                    $key[] = [['text' => $channel['link'], 'url' => 'https://t.me/' . str_replace('@', '', $channel['link'])], ['text' => number_format(bot('getChatMembersCount', ['chat_id' => $channel['link']])->result), 'callback_data' => 'null'], ['text' => ($channel['status']) ? '✅' : '❌', 'callback_data' => 'change_channel_status-' . $channel['link']], ['text' => '🗑', 'callback_data' => 'delete_channel-' . $channel['link']]];
                }
                $key = json_encode(['inline_keyboard' => $key]);
                editMessage($from_id, "🔗 لیست کانال های اضافه شده توسط شما به شرح زیر است :", $message_id, $key);
            } else {
                alert('⚠️ لیست کانال های جوین اجباری خالی است !', true);
            }
        }

        elseif (strpos($data, 'change_channel_status') !== false) {
            $link = explode('-', $data)[1];
            $status = $sql->query("SELECT * FROM `channels` WHERE `link` = '$link'")->fetch_assoc()['status'];
            if ($status) {
                $sql->query("UPDATE `channels` SET `status` = 0 WHERE `link` = '$link'");
            } else {
                $sql->query("UPDATE `channels` SET `status` = 1 WHERE `link` = '$link'");
            }

            $channels = $sql->query("SELECT * FROM `channels`");
            if ($channels->num_rows > 0) {
                $key[] = [['text' => 'لینک', 'callback_data' => 'none'], ['text' => 'وضعیت', 'callback_data' => 'none'], ['text' => 'حذف', 'callback_data' => 'none']];
                while ($channel = $channels->fetch_assoc()) {
                    $key[] = [['text' => $channel['link'], 'callback_data' => 'none'], ['text' => ($channel['status']) ? '✅' : '❌', 'callback_data' => 'change_channel_status-' . $channel['link']], ['text' => '🗑', 'callback_data' => 'delete_channel-' . $channel['link']]];
                }
                $key = json_encode(['inline_keyboard' => $key]);
                editMessage($from_id, "🔗 لیست کانال های اضافه شده توسط شما به شرح زیر است :", $message_id, $key);
            } else {
                editMessage($from_id, '⚠️ لیست کانال های جوین اجباری خالی است !', $message_id, $manage_channels);
            }
        }

        elseif (strpos($data, 'delete_channel') !== false) {
            $link = explode('-', $data)[1];
            $sql->query("DELETE FROM `channels` WHERE `link` = '$link'");
            
            $channels = $sql->query("SELECT * FROM `channels`");
            if ($channels->num_rows > 0) {
                $key[] = [['text' => 'لینک', 'callback_data' => 'none'], ['text' => 'وضعیت', 'callback_data' => 'none'], ['text' => 'حذف', 'callback_data' => 'none']];
                while ($channel = $channels->fetch_assoc()) {
                    $key[] = [['text' => $channel['link'], 'callback_data' => 'none'], ['text' => ($channel['status']) ? '✅' : '❌', 'callback_data' => 'change_channel_status-' . $channel['link']], ['text' => '🗑', 'callback_data' => 'delete_channel-' . $channel['link']]];
                }
                $key = json_encode(['inline_keyboard' => $key]);
                editMessage($from_id, "🔗 لیست کانال های اضافه شده توسط شما به شرح زیر است :", $message_id, $key);
            } else {
                editMessage($from_id, '⚠️ لیست کانال های جوین اجباری خالی است !', $message_id, $manage_channels);
            }
        }

        elseif ($text == '🔑 مدیریت نمایندگان') {
            step('manage_representations');
            $manage_representations = json_encode(['inline_keyboard' => [[['text' => '🔑 مدیریت نمایندگان', 'web_app' => ['url' => $config['domain'] . '/static/html/representation.php?from_id=' . $from_id]]]]]);
            sendMessage($from_id, "🏺 به بخش مدیریت نمایندگان خوش آمدید.\n\n⬇️ بر روی دکمه زیر کلیک کنید تا به بخش اصلی هدایت شوید.", $manage_representations);
        }

        elseif ($text == '⚙ تنظیمات ربات') {
            step('manage_settings');
            sendMessage($from_id, "⚙ به بخش تنظیمات ربات خوش آمدید.", $manage_settings);
        }

        elseif ($text == '➕ تنظیمات کلی') {
            step('manage_all_settings');
            $manage_all_settings = json_encode(['inline_keyboard' => [[['text' => '➕ تنضیمات کل', 'web_app' => ['url' => $config['domain'] . '/static/html/settings.php?from_id=' . $from_id]]]]]);
            sendMessage($from_id, "➕ به بخش تنظیمات کلی خوشآمدید.", $manage_all_settings);
        }

        elseif ($text == '👥 مدیریت زیرمجموعه گیری' or $data == 'back_to_refral_settings') {
            step('manage_refral');
            (isset($text)) ? sendMessage($from_id, "👥 به بخش مدیریت زیرمجموعه گیری خوش آمدید !", manageRefralSettings()) : editMessage($from_id, "👥 به بخش مدیریت زیرمجموعه گیری خوش آمدید !", $message_id, manageRefralSettings());
        }

        elseif ($data == 'change_refral_status') {
            $status = $sql->query("SELECT * FROM `settings`")->fetch_assoc();
            ($status['refral_status']) ? $sql->query("UPDATE `settings` SET `refral_status` = 0") : $sql->query("UPDATE `settings` SET `refral_status` = 1");
            editMessage($from_id, "👥 به بخش مدیریت زیرمجموعه گیری خوش آمدید !", $message_id, manageRefralSettings());
        }

        elseif ($data == 'change_refral_gift') {
            step('send_new_refral_gift');
            editMessage($from_id, "💸 مبلغ پورسانت زیرمجموعه گیری را ارسال کنید :", $message_id, $back_to_refral_settings);
        }

        elseif ($user['step'] == 'send_new_refral_gift') {
            if (is_numeric($text) and $text > 0) {
                step('none');
                $sql->query("UPDATE `settings` SET `refral_gift` = $text");
                sendMessage($from_id, "✅ پورسانت زیرمجموعه گیری ارسالی شما با موفقیت تنظیم شد.\n\n👥 به بخش مدیریت زیرمجموعه گیری خوش آمدید !", manageRefralSettings());
            } else {
                sendMessage($from_id, "⚠️ مبلغ ارسالی شما اشتباه است !", $back_to_refral_settings);
            }
        }

        elseif ($text == '🧭 مدیریت اکانت تست' or $data == 'back_to_manage_test_account') {
            step('manage_test_account');
            (isset($text)) ? sendMessage($from_id, "🧭 به بخش مدیریت اکانت تست خوش آمدید !\n\n📚 راهنما : در اکانت تست قسمت حجم رو باید به صورت مگابایتی تنظیم کنید و همچنین قسمت تاریخ رو هم باید به صورت روز تنظیم کنید!", manageTestAccountKey()) : editMessage($from_id, "🧭 به بخش مدیریت اکانت تست خوش آمدید !\n\n📚 راهنما : در اکانت تست قسمت حجم رو باید به صورت مگابایتی تنظیم کنید و همچنین قسمت تاریخ رو هم باید به صورت روز تنظیم کنید!", $message_id, manageTestAccountKey());
        }

        elseif ($data == 'reset_test_account_users') {
            step('none');
            $sql->query("UPDATE `users` SET `get_test_account` = 0");
            alert("✅ عملیات ریست کاربران با موفقیت انجام شد و از الان به بعد همه کاربران مجدد میتوانند سرویس تست را دریافت کنند.\n\n⏱ - $date - $time", true);
        }

        elseif ($data == 'change_test_account_status') {
            if ($test_account_settings['status']) {
                $sql->query("UPDATE `test_account_settings` SET `status` = 0");
            } else {
                if (!is_null($test_account_settings['panel'])) {
                    $sql->query("UPDATE `test_account_settings` SET `status` = 1");
                } else {
                    alert("⚠️ برای روشن شدن اکانت تست ابتدا باید یک پنل را به عنوان اکانت تست تنظیم کنید !", true);
                    exit();
                }
            }
            editMessage($from_id, "🧭 به بخش مدیریت اکانت تست خوش آمدید !\n\n📚 راهنما : در اکانت تست قسمت حجم رو باید به صورت مگابایتی تنظیم کنید و همچنین قسمت تاریخ رو هم باید به صورت روز تنظیم کنید!", $message_id, manageTestAccountKey());
        }

        elseif ($data == 'change_test_acount_panel') {
            step('select_panel');
            $panels = $sql->query("SELECT * FROM `panels`");
            if ($panels->num_rows > 0) {
                while ($panel = $panels->fetch_assoc()) {
                    $keyboard[] = ['text' => $panel['name'], 'callback_data' => 'select_panel-' . $panel['row']];
                }
                $keyboard = array_chunk($keyboard, 2);
                $keyboard[] = [['text' => '🔙 بازگشت', 'callback_data' => 'back_to_manage_test_account']];
                editMessage($from_id, "🌐 یکی از پنل های زیر را برای قسمت اکانت تست انتخاب کنید :", $message_id, json_encode(['inline_keyboard' => $keyboard]));
            } else {
                alert("⚠️ پنلی برای تنظیم به عنوان سرویس تست در ربات وجود ندارد !", true);
            }
        }

        elseif (strpos($data, 'select_panel') !== false and $user['step'] == 'select_panel') {
            step('none');
            $row = explode('-', $data)[1];
            $sql->query("UPDATE `test_account_settings` SET `panel` = $row");
            editMessage($from_id, "🧭 به بخش مدیریت اکانت تست خوش آمدید !\n\n📚 راهنما : در اکانت تست قسمت حجم رو باید به صورت مگابایتی تنظیم کنید و همچنین قسمت تاریخ رو هم باید به صورت روز تنظیم کنید!", $message_id, manageTestAccountKey());
        }

        elseif ($data == 'change_test_account_inbound_id') {
            step('send_new_inbound_id');
            editMessage($from_id, "🪪 اینباند آیدی مورد نظرتان را برای قسمت سرویس تست ارسال کنید : ( فعلی : <code>{$test_account_settings['inbound_id']}</code> )", $message_id, $back_to_test_account_settings);
        }

        elseif ($user['step'] == 'send_new_inbound_id') {
            if (is_numeric($text) and $text > 0) {
                step('none');
                $sql->query("UPDATE `test_account_settings` SET `inbound_id` = $text");
                sendMessage($from_id, "✅ اینباند آیدی ارسالی شما با موفقیت تنظیم شد.\n\n🧭 به بخش مدیریت اکانت تست خوش آمدید !\n\n📚 راهنما : در اکانت تست قسمت حجم رو باید به صورت مگابایتی تنظیم کنید و همچنین قسمت تاریخ رو هم باید به صورت روز تنظیم کنید!", manageTestAccountKey());
            } else {
                sendMessage($from_id, "⚠️ اینباند آیدی ارسالی شما اشتباه است !", $back_to_test_account_settings);
            }
        }

        elseif ($data == 'change_test_account_prefix') {
            step('send_new_prefix');
            editMessage($from_id, "📖 پیشوند خود را برای remark سرویس های تست ارسال کنید : ( فعلی : <code>{$test_account_settings['prefix']}</code> )", $message_id, $back_to_test_account_settings);
        }

        elseif ($user['step'] == 'send_new_prefix') {
            step('none');
            $sql->query("UPDATE `test_account_settings` SET `prefix` = $text");
            sendMessage($from_id, "✅ پیشوند ارسالی شما با موفقیت تنظیم شد.\n\n🧭 به بخش مدیریت اکانت تست خوش آمدید !\n\n📚 راهنما : در اکانت تست قسمت حجم رو باید به صورت مگابایتی تنظیم کنید و همچنین قسمت تاریخ رو هم باید به صورت روز تنظیم کنید!", manageTestAccountKey());
        }

        elseif ($data == 'change_test_account_limit') {
            step('send_new_limit');
            editMessage($from_id, "🔢 حجم سرویس های تست را ارسال کنید : ( بر اساس MB )", $message_id, $back_to_test_account_settings);
        }

        elseif ($user['step'] == 'send_new_limit') {
            step('none');
            $sql->query("UPDATE `test_account_settings` SET `limit` = $text");
            sendMessage($from_id, "✅ حجم ارسالی شما با موفقیت تنظیم شد.\n\n🧭 به بخش مدیریت اکانت تست خوش آمدید !\n\n📚 راهنما : در اکانت تست قسمت حجم رو باید به صورت مگابایتی تنظیم کنید و همچنین قسمت تاریخ رو هم باید به صورت روز تنظیم کنید!", manageTestAccountKey());
        }

        elseif ($data == 'change_test_account_date') {
            step('send_new_date');
            editMessage($from_id, "🔢 زمان سرویس های تست را ارسال کنید : ( بر اساس روز )", $message_id, $back_to_test_account_settings);
        }

        elseif ($user['step'] == 'send_new_date') {
            step('none');
            $sql->query("UPDATE `test_account_settings` SET `date` = $text");
            sendMessage($from_id, "✅ زمان ارسالی شما با موفقیت تنظیم شد.\n\n🧭 به بخش مدیریت اکانت تست خوش آمدید !\n\n📚 راهنما : در اکانت تست قسمت حجم رو باید به صورت مگابایتی تنظیم کنید و همچنین قسمت تاریخ رو هم باید به صورت روز تنظیم کنید!", manageTestAccountKey());
        }

        elseif ($data == 'change_test_account_ip_limit') {
            step('send_new_ip_limit');
            editMessage($from_id, "🔢 تعداد کاربر سرویس های تست را ارسال کنید :", $message_id, $back_to_test_account_settings);
        }

        elseif ($user['step'] == 'send_new_ip_limit') {
            step('none');
            $sql->query("UPDATE `test_account_settings` SET `ip_limit` = $text");
            sendMessage($from_id, "✅ تعداد کاربر ارسالی شما با موفقیت تنظیم شد.\n\n🧭 به بخش مدیریت اکانت تست خوش آمدید !\n\n📚 راهنما : در اکانت تست قسمت حجم رو باید به صورت مگابایتی تنظیم کنید و همچنین قسمت تاریخ رو هم باید به صورت روز تنظیم کنید!", manageTestAccountKey());
        }

        elseif ($text == '💸 مدیریت شارژ حساب' or $data == 'back_to_payment_settings') {
            step('manage_payment_settings');
            (isset($text)) ? sendMessage($from_id, "💸 به بخش مدیریت شارژ حساب خوش آمدید !", managePaymentSettings()) : editMessage($from_id, "💸 به بخش مدیریت شارژ حساب خوش آمدید !", $message_id, managePaymentSettings());
        }

        elseif ($data == 'change_payment_status') {
            $status = $sql->query("SELECT * FROM `settings`")->fetch_assoc();
            ($status['deposit_status']) ? $sql->query("UPDATE `settings` SET `deposit_status` = 0") : $sql->query("UPDATE `settings` SET `deposit_status` = 1");
            editMessage($from_id, "💸 به بخش مدیریت شارژ حساب خوش آمدید !", $message_id, managePaymentSettings());
        }

        elseif ($data == 'change_payment_nowpayment_apikey') {
            step('send_new_nowpaymentapikey');
            editMessage($from_id, "🔑 کلید NowPayment خود را ارسال کنید :", $message_id, $back_to_payment_settings);
        }

        elseif ($user['step'] == 'send_new_nowpaymentapikey') {
            step('none');
            $sql->query("UPDATE `payment_settings` SET `nowpayment_apikey` = '$text'");
            sendMessage($from_id, "✅ کلید NowPayment ارسالی شما با موفقیت تنظیم شد.\n\n💸 به بخش مدیریت شارژ حساب خوش آمدید !", managePaymentSettings());
        }

        elseif ($data == 'change_payment_card_number') {
            step('send_new_card_number');
            editMessage($from_id, "💳 شماره کارت 16 رقمی خود را به صورت عدد لاتین ارسال کنید :", $message_id, $back_to_payment_settings);
        }

        elseif ($user['step'] == 'send_new_card_number') {
            if (is_numeric($text) and strlen($text) == 16) {
                step('none');
                $sql->query("UPDATE `payment_settings` SET `card_number` = $text");
                sendMessage($from_id, "✅ شماره کارت ارسالی شما با موفقیت تنظیم شد.\n\n💸 به بخش مدیریت شارژ حساب خوش آمدید !", managePaymentSettings());
            } else {
                sendMessage($from_id, "⚠️ شماره کارت ارسالی شما اشتباه است !", $back_to_payment_settings);
            }
        }

        elseif ($data == 'change_payment_card_name') {
            step('send_new_card_name');
            editMessage($from_id, "💳 نام صاحب شماره کارت [ <code>{$payment_settings['card_number']}</code> ] را به صورت صحیح ارسال کنید:", $message_id, $back_to_payment_settings);
        }

        elseif ($user['step'] == 'send_new_card_name') {
            step('none');
            $sql->query("UPDATE `payment_settings` SET `card_name` = '$text'");
            sendMessage($from_id, "✅ نام صاحب شماره کارت ارسالی شما با موفقیت تنظیم شد.\n\n💸 به بخش مدیریت شارژ حساب خوش آمدید !", managePaymentSettings());
        }

        elseif ($data == 'change_payment_type') {
            step('none');
            if ($payment_settings['type'] == 'CardToCard') {
                $sql->query("UPDATE `payment_settings` SET `type` = 'Arz'");
            } elseif ($payment_settings['type'] == 'Arz') {
                $sql->query("UPDATE `payment_settings` SET `type` = 'both'");
            } elseif ($payment_settings['type'] == 'both') {
                $sql->query("UPDATE `payment_settings` SET `type` = 'CardToCard'");
            }
            editMessage($from_id, "💸 به بخش مدیریت شارژ حساب خوش آمدید !", $message_id, managePaymentSettings());
        }

        elseif ($text == '/add_panel' or $text == '🌐 افزودن پنل') {
            step('select_add_panel');
            sendMessage($from_id, "🔄 قصد اضافه کردن کدام نوع پنل را دارید ؟\n\n⏱ <code>$date - $time</code>", $select_add_panel);
        }

        elseif ($data == 'add_sanaei') {
            step('send_nick_name_sanaei');
            if (file_exists('add_panel.txt')) unlink('add_panel.txt');
            deleteMessage($from_id, $message_id);
            sendMessage($from_id, "📚 یک اسم مستعار برای پنلی که میخواهید اضافه کنید ارسال کنید :", $back_panel);
        }

        elseif ($user['step'] == 'send_nick_name_sanaei') {
            $get = $sql->query("SELECT `name` FROM `panels` WHERE `name` = '$text'");
            if ($get->num_rows == 0) {
                step('send_login_address_sanaei');
                file_put_contents('add_panel.txt', $text . PHP_EOL, FILE_APPEND);
                sendMessage($from_id, "🌐 آدرس لاگین پنل را به صورت دقیق ارسال کنید :↓\n\n(<b>e.x</b>) : <code>http://domain.com:96856</code>", $back_panel);
            } else {
                sendMessage($from_id, "⚠️ پنلی با این نام قبلا در ربات ثبت شده است !", $back_panel);
            }
        }

        elseif ($user['step'] == 'send_login_address_sanaei') {
            // $get = $sql->query("SELECT `address` FROM `panels` WHERE `address` = '$text'");
            // if ($get->num_rows == 0) {
            if (validAddress($text)) {
                step('send_domain_sanaei');
                file_put_contents('add_panel.txt', $text . PHP_EOL, FILE_APPEND);
                sendMessage($from_id, "🌍 دامنه جایگزینی لینک اتصال را بدون ( http / https ) ارسال کنید :\n\n⚠️ در صورت خالی گذاشتن دستور /blank را ارسال کنید.", $back_panel);
            } else {
                sendMessage($from_id, "⚠️ آدرس ارسالی شما نامعتبر است !", $back_panel);
            }
            // } else {
            //     sendMessage($from_id, "⚠️ این پنل قبلا در ربات ثبت شده است !", $back_panel);
            // }
        }

        elseif ($user['step'] == 'send_domain_sanaei') {
            step('send_username_sanaei');
            file_put_contents('add_panel.txt', $text . PHP_EOL, FILE_APPEND);
            sendMessage($from_id, "🔑 یوزرنیم پنل را ارسال کنید :", $back_panel);
        }

        elseif ($user['step'] == 'send_username_sanaei') {
            step('send_password_sanaei');
            file_put_contents('add_panel.txt', $text . PHP_EOL, FILE_APPEND);
            sendMessage($from_id, "🔑 پسورد پنل را ارسال کنید :", $back_panel);
        }

        elseif ($user['step'] == 'send_password_sanaei') {
            step('send_inbound_id_sanaei');
            file_put_contents('add_panel.txt', $text . PHP_EOL, FILE_APPEND);
            sendMessage($from_id, "🆔 اینباند ایدی که قصد دارید ربات در داخل آن اینباند کانفیگ اد کند را ارسال کنید ( <b>inbound_id</b> ) :", $back_panel);
        }

        elseif ($user['step'] == 'send_inbound_id_sanaei') {
            step('send_buy_limit_sanaei');
            file_put_contents('add_panel.txt', $text . PHP_EOL, FILE_APPEND);
            sendMessage($from_id, "🔄 محدودیت خرید کانفیگ برای این پنل را به صورت عدد لاتین ارسال کنید :", $back_panel);
        }

        elseif ($user['step'] == 'send_buy_limit_sanaei') {
            if (is_numeric($text) and $text > 0) {
                step('send_prefix_sanaei');
                file_put_contents('add_panel.txt', $text, FILE_APPEND);
                sendMessage($from_id, "💬 پیشوندی که قصد دارید برای remark کانفیگ ها گذاشته شود را ارسال کنید :", $back_panel);
            } else {
                sendMessage($from_id, "⚠️ عدد ارسالی شما اشتباه است !", $back_panel);
            }
        }

        elseif ($user['step'] == 'send_prefix_sanaei') {
            step('none');
            $getInfo = explode("\n", file_get_contents('add_panel.txt'));
            $response = login($getInfo[1], $getInfo[3], $getInfo[4]);
            if ($response['success']) {
                $sql->query("INSERT INTO `panels` (`name`, `address`, `domain`, `username`, `password`, `session`, `inbound_id`, `added_time`, `buy_limit`, `prefix`, `code`, `status`) VALUES ('{$getInfo[0]}', '{$getInfo[1]}', '{$getInfo[2]}', '{$getInfo[3]}', '{$getInfo[4]}', '{$response['cookie']}', {$getInfo[5]}, " . time() . ", {$getInfo[6]}, '$text', " . rand(111111, 999999) . ", 1)");
                sendMessage($from_id, "✅ ربات با موفقیت به پنل [ <b>{$getInfo[0]}</b> ] شما وارد شد.\n\n🔑 یوزرنیم : <code>{$getInfo[3]}</code>\n🔑 پسورد : <code>{$getInfo[4]}</code>", $manage_panels);
            } else {
                // sendMessage($from_id, json_encode($response, 448));
                sendMessage($from_id, "⚠️ عملیات لاگین به پنل شما با خطا مواجه شد.", $manage_panels);
            }
            unlink('add_panel.txt');
        }

        elseif ($data == 'close_panels_manager') {
            editMessage($from_id, "❌ پنل با موفقیت بسته شد.\n\n⏱ - <code>$date - $time</code>", $message_id);
            // code . . .
        }

        elseif ($text == '⚙️ مدیریت پنل ها' or $data == 'back_panels_list') {
            step('manage_panels');
            $panels_count = number_format($sql->query("SELECT COUNT(*) AS `count` FROM `panels`")->fetch_assoc()['count'] ?? 0);
            (isset($text)) ? sendMessage($from_id, "🌏 لیست پنل های اضافه شده توسط شما به شرح زیر است :\n\n🔄 تعداد کل پنل : <code>$panels_count</code> عدد", panelsKey()) : editMessage($from_id, "🌏 لیست پنل های اضافه شده توسط شما به شرح زیر است :\n\n🔄 تعداد کل پنل : <code>$panels_count</code> عدد", $message_id, panelsKey());
        }

        elseif (strpos($data, 'handle_panel') !== false) {
            $row = explode('-', $data)[1];
            $panel = $sql->query("SELECT * FROM `panels` WHERE `row` = $row");
            if ($panel->num_rows > 0) {
                $panel = $panel->fetch_assoc();
                $handle_panel_keys = json_encode(['inline_keyboard' => [
                    [['text' => '🍪 دریافت کوکی ذخیره شده', 'callback_data' => 'get_cookie-' . $row]],
                    [['text' => '🆙 آپدیت کوکی ( Cookie )', 'callback_data' => 'update_cookie-' . $row], ['text' => '⏱ دریافت بکاپ', 'callback_data' => 'get_backup-' . $row]],
                    [['text' => '🔑 مدیریت پنل', 'web_app' => ['url' => $config['domain'] . '/static/html/manage_panel.php?from_id=' . $from_id . '&panel=' . $row]]],
                    [['text' => '🔙 بازگشت', 'callback_data' => 'back_panels_list']]
                ]]);
                editMessage($from_id, "🌍 اطلاعات پنل انتخابی [ <code>{$panel['code']}</code> ] با موفقیت استخراج شد!\n\n◽️نام مستعار : <b>{$panel['name']}</b>\n◽️آدرس لاگین : {$panel['address']}\n◽️دامنه جایگزینی : <code>" . ((is_null($panel['domain']) or $panel['domain'] == '/blank') ? 'تنظیم نشده' : $panel['domain']) . "</code>\n◽️آیپی : <code>" . parse_url($panel['address'])['host'] . "</code>\n◽️پورت : <code>" . parse_url($panel['address'])['port'] . "</code>\n◽️وضعیت پروتکل : <b>" . ((parse_url($panel['address'])['scheme'] == 'http') ? '❌ غیرفعال' : '✅ فعال') . "</b>\n◽️یوزرنیم پنل : <code>{$panel['username']}</code>\n◽️پسورد پنل : <code>{$panel['password']}</code>\n\n◽️اینباند آیدی تنظیم شده : <code>{$panel['inbound_id']}</code>\n◽️تعداد محدودیت باقیمانده این پنل : <code>{$panel['buy_limit']}</code> عدد\n◽️پیشوند تنظیم شده  : <b>{$panel['prefix']}</b>\n\n◽️وضعیت پنل در ربات : <b>" . (($panel['status']) ? '✅ روشن' : '❌ خاموش') . "</b>\n\n⏱ - <code>$date - $time</code>", $message_id, $handle_panel_keys);
            } else {
                alert('⚠️ عملیات استخراج اطلاعات پنل با خطا مواجه شد.', true);
            }
        }

        elseif (strpos($data, 'get_backup') !== false) {
            alert($texts['wait'], false);
            $row = explode('-', $data)[1];
            $panel = $sql->query("SELECT * FROM `panels` WHERE `row` = $row");
            if ($panel->num_rows > 0) {
                $panel = $panel->fetch_assoc();
                copy($panel['address'] . '/server/getDb', 'backup/x-ui.db');
                sendDocument($from_id, 'backup/x-ui.db', '💬 بکاپ پنل شما با موفقیت دریافت شد.');
            } else {
                alert('⚠️ عملیات استخراج اطلاعات پنل با خطا مواجه شد.', true);
            }
        }

        elseif (strpos($data, 'get_cookie') !== false) {
            $row = explode('-', $data)[1];
            $panel = $sql->query("SELECT * FROM `panels` WHERE `row` = $row");
            if ($panel->num_rows > 0) {
                $panel = $panel->fetch_assoc();
                sendMessage($from_id, "<code>{$panel['session']}</code>");
            } else {
                alert('⚠️ عملیات استخراج اطلاعات پنل با خطا مواجه شد.', true);
            }
        }

        elseif (strpos($data, 'update_cookie') !== false) {
            $row = explode('-', $data)[1];
            $panel = $sql->query("SELECT * FROM `panels` WHERE `row` = $row");
            if ($panel->num_rows > 0) {
                $panel = $panel->fetch_assoc();
                $response = login($panel['address'], $panel['username'], $panel['password']);
                if ($response['success']) {
                    $sql->query("UPDATE `panels` SET `session` = '{$response['cookie']}' WHERE `row` = $row");
                    alert("🍪 کوکی پنل شما با موفقیت آپدیت شد.", true);
                } else {
                    alert("⚠️ آپدیت کوکی با خطا مواجه شد.", true);
                }
            } else {
                alert('⚠️ عملیات استخراج اطلاعات پنل با خطا مواجه شد.', true);
            }
        }

        elseif ($text == '/add_single_plan' or $text == '➕ افزودن پلن مادر') {
            step('add_mother_plan');
            sendMessage($from_id, "🔰 اسم پلن را به درستی ارسال کنید :↓\n\n⚠️ این پلن به عنوان پلن مادر تنطیم خواهد شد.", $back_panel);
        }

        elseif ($user['step'] == 'add_mother_plan') {
            step('none');
            $sql->query("INSERT INTO `single_plan` (`name`, `status`) VALUES ('$text', 1)");
            sendMessage($from_id, "✅ پلن مادر با موفقیت اضافه شد.\n\n⚙️ نام پلن ارسالی : <b>$text</b>", $manage_plans);
        }

        elseif ($text == '/add_multiple_plan' or $text == '➕ افزودن پلن بچه') {
            step('add_child_plan');
            sendMessage($from_id, "🔰 اسم پلن را به درستی ارسال کنید :↓\n\n⚠️ این پلن زیرمجموعه یکی از پلن های مادر خواهد شد ( در مرحله بعدی پلن مادر را انتخاب خواهید کرد )", $back_panel);
        }

        elseif ($user['step'] == 'add_child_plan') {
            step('select_mother_plan');
            file_put_contents('add_child_plan.txt', $text . PHP_EOL, FILE_APPEND);
            $single_plans = $sql->query("SELECT * FROM `single_plan`");
            while ($plan = $single_plans->fetch_assoc()) {
                $select[] = ['text' => $plan['name']];
            }
            $select = array_chunk($select, 2);
            $select[] = [['text' => '🔙 بازگشت به پنل مدیریت']];
            $select = json_encode(['keyboard' => $select, 'resize_keyboard' => true]);
            sendMessage($from_id, "🛒 پلن ارسالی شما [ <b>$text</b> ] زیرمجموعه کدام پلن مادر شود ؟\n\n⬇️ از لیست زیر انتخاب کنید :↓", $select);
        }

        elseif ($user['step'] == 'select_mother_plan') {
            $check = $sql->query("SELECT * FROM `single_plan` WHERE `name` = '$text'");
            if ($check->num_rows > 0) {
                step('send_limit');
                $check = $check->fetch_assoc();
                file_put_contents('add_child_plan.txt', $check['row'] . PHP_EOL, FILE_APPEND);
                sendMessage($from_id, "🔢 حجم پلن را به GB ارسال کنید : ( برای مثال : 30 )", $back_panel);
            } else {
                sendMessage($from_id, "⚠️ فقط از گزینه های زیر انتخاب کنید !");
            }
        }

        elseif ($user['step'] == 'send_limit') {
            if (is_numeric($text) and $text > 0) {
                step('send_date');
                file_put_contents('add_child_plan.txt', $text . PHP_EOL, FILE_APPEND);
                sendMessage($from_id, "🔢 تاریخ پلن را به روز ارسال کنید : ( برای مثال : 30 )", $back_panel);
            } else {
                sendMessage($from_id, "⚠️ حجم ارسالی شما اشتباه است !", $back_panel);
            }
        }

        elseif ($user['step'] == 'send_date') {
            if (is_numeric($text) and $text > 0) {
                step('send_ip_limit');
                file_put_contents('add_child_plan.txt', $text . PHP_EOL, FILE_APPEND);
                sendMessage($from_id, "🔢 تعداد کاربر متصل ( <code>ip_limit</code> ) پلن را به صورت عدد لاتین ارسال کنید : ( برای مثال : 5 )", $back_panel);
            } else {
                sendMessage($from_id, "⚠️ تاریخ ارسالی شما اشتباه است !", $back_panel);
            }
        }

        elseif ($user['step'] == 'send_ip_limit') {
            if (is_numeric($text) and $text > 0) {
                step('send_price');
                file_put_contents('add_child_plan.txt', $text, FILE_APPEND);
                sendMessage($from_id, "💸 مبلغ این پلن را به تومان و به صورت عدد صحیح ارسال کنید : ( برای مثال : 30000 )", $back_panel);
            } else {
                sendMessage($from_id, "⚠️ عدد ارسالی شما اشتباه است !", $back_panel);
            }
        }

        elseif ($user['step'] == 'send_price') {
            if (is_numeric($text) and $text > 0) {
                step('none');
                $getInfo = explode("\n", file_get_contents('add_child_plan.txt'));
                $sql->query("INSERT INTO `multiple_plan` (`name`, `limit`, `date`, `ip_limit`, `price`, `type`, `code`, `status`) VALUES ('{$getInfo[0]}', {$getInfo[2]}, {$getInfo[3]}, {$getInfo[4]}, $text, {$getInfo[1]}, " . rand(111111, 999999) . ", 1)");
                sendMessage($from_id, "INSERT INTO `multiple_plan` (`name`, `limit`, `date`, `ip_limit`, `price`, `type`, `code`, `status`) VALUES ('{$getInfo[0]}', {$getInfo[2]}, {$getInfo[3]}, {$getInfo[4]}, $text, {$getInfo[1]}, " . rand(111111, 999999) . ", 1)");
                sendMessage($from_id, "✅ پلن مادر با موفقیت اضافه شد.\n\n⚙️ نام پلن ارسالی : <b>$text</b>", $manage_plans);
                unlink('add_child_plan.txt');
            } else {
                sendMessage($from_id, "⚠️ مبلغ ارسالی شما اشتباه است !", $back_panel);
            }
        }

        elseif ($text == '🛡 مدیریت پلن مادر' or $data == 'back_to_single_plan_manager') {
            step('manage_single_plans');
            $singleplans_count = number_format($sql->query("SELECT COUNT(*) AS `count` FROM `single_plan`")->fetch_assoc()['count'] ?? 0);
            (isset($text)) ? sendMessage($from_id, "🛡 لیست پلن های مادر در ربات به شرح زیر است :\n\n🔄 تعداد کل پلن : <code>$singleplans_count</code>", singlePlansKey()) : editMessage($from_id, "🛡 لیست پلن های مادر در ربات به شرح زیر است :\n\n🔄 تعداد کل پلن : <code>$singleplans_count</code>", $message_id, singlePlansKey());
        }

        elseif (strpos($data, 'show_splan_name') !== false and $user['step'] == 'manage_single_plans') {
            $row = explode('-', $data)[1];
            $plan = $sql->query("SELECT * FROM `single_plan` WHERE `row` = $row")->fetch_assoc();
            alert($plan['name'], true);
        }

        elseif (strpos($data, 'change_splan_status') !== false or strpos($data, 'change_mplan_status') !== false) {
            $row = explode('-', $data)[1];
            if (strpos($data, 'change_splan_status') !== false) {
                $plan = $sql->query("SELECT * FROM `single_plan` WHERE `row` = $row")->fetch_assoc();
                if ($plan['status']) {
                    $sql->query("UPDATE `single_plan` SET `status` = 0 WHERE `row` = $row");
                } else {
                    $sql->query("UPDATE `single_plan` SET `status` = 1 WHERE `row` = $row");
                }
                $singleplans_count = number_format($sql->query("SELECT COUNT(*) AS `count` FROM `single_plan`")->fetch_assoc()['count'] ?? 0);
                if ($singleplans_count > 0) {
                    editMessage($from_id, "🛡 لیست پلن های مادر در ربات به شرح زیر است :\n\n🔄 تعداد کل پلن : <code>$singleplans_count</code>", $message_id, singlePlansKey());
                } else {
                    editMessage($from_id, "⚠️ هیچ پلنی در ربات ثبت نشده !", $message_id);
                }
            } else {
                $plan = $sql->query("SELECT * FROM `multiple_plan` WHERE `row` = $row")->fetch_assoc();
                if ($plan['status']) {
                    $sql->query("UPDATE `multiple_plan` SET `status` = 0 WHERE `row` = $row");
                } else {
                    $sql->query("UPDATE `multiple_plan` SET `status` = 1 WHERE `row` = $row");
                }
                $multipleplans_count = number_format($sql->query("SELECT COUNT(*) AS `count` FROM `multiple_plan`")->fetch_assoc()['count'] ?? 0);
                if ($multipleplans_count > 0) {
                    editMessage($from_id, "🛡 لیست پلن های بچه در ربات به شرح زیر است :\n\n👮‍♂️ با کلیک بر روی نام هر کدام از پلن ها وارد قسمت مدیریتی آن پلن میشوید!\n\n🔄 تعداد کل پلن : <code>$multipleplans_count</code>", $message_id, multiplePlansKey());
                } else {
                    editMessage($from_id, "⚠️ هیچ پلنی در ربات ثبت نشده !", $message_id);
                }
            }
        }

        elseif (strpos($data, 'change_splan_name') !== false or strpos($data, 'change_mplan_name') !== false) {
            $row = explode('-', $data)[1];
            if (strpos($data, 'change_splan_name') !== false) {
                step('change_splan_name-' . $row);
                $plan = $sql->query("SELECT * FROM `single_plan` WHERE `row` = $row")->fetch_assoc();
                $key = $back_to_single_plan_manager;
            } else {
                step('change_mplan_name-' . $row);
                $plan = $sql->query("SELECT * FROM `multiple_plan` WHERE `row` = $row")->fetch_assoc();
                $key = $back_to_multiple_plan_manager;
            }
            editMessage($from_id, "✏️ نام جدید پلن [ <b>{$plan['name']}</b> ] را ارسال کنید :", $message_id, $key);
        }

        elseif (strpos($user['step'], 'change_splan_name') !== false or strpos($user['step'], 'change_mplan_name') !== false) {
            step('manage_single_plans');
            $row = explode('-', $user['step'])[1];
            if (strpos($user['step'], 'change_splan_name') !== false) {
                $sql->query("UPDATE `single_plan` SET `name` = '$text' WHERE `row` = $row");
                $key = $back_to_single_plan_manager;
            } else {
                $sql->query("UPDATE `multiple_plan` SET `name` = '$text' WHERE `row` = $row");
                $key = $back_to_multiple_plan_manager;
            }
            sendMessage($from_id, "✅ نام پلن انتخابی شما با موفقیت به [ <b>$text</b> ] تغییر کرد.", $key);
        }

        elseif (strpos($data, 'delete_splan') !== false or strpos($data, 'delete_mplan') !== false) {
            $row = explode('-', $data)[1];
            if (strpos($data, 'delete_splan') !== false) {
                $sql->query("DELETE FROM `single_plan` WHERE `row` = $row");
                $singleplans_count = number_format($sql->query("SELECT COUNT(*) AS `count` FROM `single_plan`")->fetch_assoc()['count'] ?? 0);
                if ($singleplans_count > 0) {
                    editMessage($from_id, "🛡 لیست پلن های مادر در ربات به شرح زیر است :\n\n🔄 تعداد کل پلن : <code>$singleplans_count</code>", $message_id, singlePlansKey());
                } else {
                    editMessage($from_id, "⚠️ هیچ پلنی در ربات ثبت نشده !", $message_id);
                }
            } else {
                $sql->query("DELETE FROM `multiple_plan` WHERE `row` = $row");
                $multipleplans_count = number_format($sql->query("SELECT COUNT(*) AS `count` FROM `multiple_plan`")->fetch_assoc()['count'] ?? 0);
                if ($multipleplans_count > 0) {
                    editMessage($from_id, "🛡 لیست پلن های بچه در ربات به شرح زیر است :\n\n👮‍♂️ با کلیک بر روی نام هر کدام از پلن ها وارد قسمت مدیریتی آن پلن میشوید!\n\n🔄 تعداد کل پلن : <code>$multipleplans_count</code>", $message_id, multiplePlansKey());
                } else {
                    editMessage($from_id, "⚠️ هیچ پلنی در ربات ثبت نشده !", $message_id);
                }
            }
        }

        elseif ($text == '🛡 مدیریت پلن بچه' or $data == 'back_to_multiple_plan_manager') {
            step('manage_plans');
            $multipleplans_count = number_format($sql->query("SELECT COUNT(*) AS `count` FROM `multiple_plan`")->fetch_assoc()['count'] ?? 0);
            (isset($text)) ? sendMessage($from_id, "🛡 لیست پلن های بچه در ربات به شرح زیر است :\n\n👮‍♂️ با کلیک بر روی نام هر کدام از پلن ها وارد قسمت مدیریتی آن پلن میشوید!\n\n🔄 تعداد کل پلن : <code>$multipleplans_count</code>", multiplePlansKey()) : editMessage($from_id, "🛡 لیست پلن های بچه در ربات به شرح زیر است :\n\n👮‍♂️ با کلیک بر روی نام هر کدام از پلن ها وارد قسمت مدیریتی آن پلن میشوید!\n\n🔄 تعداد کل پلن : <code>$multipleplans_count</code>", $message_id, multiplePlansKey());
        }

        elseif ($text == '🌐 اطلاعات کاربر') {
            step('send-id-for-info');
            sendMessage($from_id, "🆔 آیدی عددی کاربر مورد نظر را ارسال کنید :", $back_panel);
        }

        elseif ($user['step'] == 'send-id-for-info') {
            if (is_numeric($text)) {
                $getUser = $sql->query("SELECT * FROM `users` WHERE `from_id` = $text");
                if ($getUser->num_rows > 0) {
                    step('none');
                    $getUser = $getUser->fetch_assoc();
                    $countService = $sql->query("SELECT * FROM `orders` WHERE `from_id` = $text")->num_rows ?? 0;
                    $manage_user = json_encode(['inline_keyboard' => [
                        [['text' => '🛒 مشاهده همه سرویس های کاربر', 'web_app' => ['url' => $config['domain'] . '/static/html/manage_services.php?from_id=' . $from_id . '&filter=' . $text . '&page=1']]],
                        [['text' => '➖ حذف سرویس از کاربر', 'callback_data' => 'delete_service_from_user-' . $text], ['text' => '➕ افزودن سرویس به کاربر', 'callback_data' => 'add_previuos_service_to_user-' . $text]],
                        [['text' => '💸 تراکنشات کاربر', 'web_app' => ['url' => $config['domain'] . '/static/html/manage_factors.php?from_id=' . $from_id . '&filter=' . $text . '&page=1']]],
                    ]]);
                    $getChat = bot('getChat', ['chat_id' => $text])->result;
                    sendMessage($from_id, "ℹ️ اطلاعات کاربر با موفقیت دریافت شد.\n\n◽️نام کاربر : <b>{$getChat->first_name}</b>\n◽️یوزرنیم کاربر : @{$getChat->username}\n◽️آیدی عددی : <code>$text</code>\n◽️موجودی کاربر : <code>" . (($getUser['coin'] < 0) ? ('منفی ' . number_format($getUser['coin'] * -1)) : number_format($getUser['coin'])) . "</code> تومان\n◽️تعداد سرویس کاربر : <code>$countService</code> عدد\n◽️تعداد زیرمجموعه گیری کاربر : <code>" . ($sql->query("SELECT * FROM `refrals` WHERE `from_id` = $text")->num_rows ?? 0) . "</code> نفر\n◽️شماره کاربر : <code>" . ((!is_null($getUser['phone'])) ? $getUser['phone'] : 'ثبت نشده') . "</code>\n◽️وضعیت حساب کاربر : <b>" . (($getUser['status']) ? '✅ فعال' : '❌ غیرفعال') . "</b>\n◽️سرویس تست : <b>" . (($getUser['get_test_account']) ? '✅ دریافت کرده' : '❌ دریافت نکرده') . "</b>\n\n⏱ - <code>$date - $time</code>", $manage_user);
                    sendMessage($from_id, "🔙 به صفحه اصلی بازگشتید.", $manage_users);
                } else {
                    sendMessage($from_id, "⚠️ آیدی عددی ارسال شده عضو ربات نیست !", $back_panel);
                }
            } else {
                sendMessage($from_id, "⚠️ آیدی عددی ارسال شده نامعتبر است !", $back_panel);
            }
        }

        elseif ($data == 'cancel_add') {
            step('none');
            deleteMessage($from_id, $message_id);
            sendMessage($from_id, "🔙 عملیات با موفقیت لغو شد.", $manage_users);
        }

        elseif (strpos($data, 'delete_service_from_user') !== false) {
            $id = explode('-', $data)[1];
            step('send_service_name-' . $id);
            deleteMessage($from_id, $message_id);
            sendMessage($from_id, "📚 نام دقیق (email) سرویس را ارسال کنید :", $back_panel);
        }

        elseif (strpos($user['step'], 'send_service_name') !== false) {
            $id = explode('-', $user['step'])[1];
            $response = $sql->query("SELECT * FROM `orders` WHERE `from_id` = $id AND `remark` = '$text'");
            if ($response->num_rows > 0) {
                step('none');
                $sql->query("DELETE FROM `orders` WHERE `from_id` = $id AND `remark` = '$text'");
                sendMessage($from_id, "✅ سرویس  (<code>$text</code>) با موفقیت از لیست سرویس های کاربر (<code>$id</code>) پاک شد.", $manage_users);
                sendMessage($id, "⚠️ سرویس (<code>$text</code>) شما حذف شد.");
            } else {
                sendMessage($from_id, "⚠️ سرویس (<code>$text</code>) در لیست سرویس های کاربر (<code>$id</code>) یافت نشد !", $back_panel);
            }
        }

        elseif (strpos($data, 'add_previuos_service_to_user') !== false) {
            $id = explode('-', $data)[1];
            step('send_previous_service-' . $id);
            deleteMessage($from_id, $message_id);
            sendMessage($from_id, $texts['send_previous_service'], $back_panel);
        }

        elseif (strpos($user['step'], 'send_previous_service') !== false) {
            $id = explode('-', $user['step'])[1];
            step('add_select_service_type|' . $id . '|' . $text);
            $single_plans = $sql->query("SELECT * FROM `single_plan`");
            while ($plan = $single_plans->fetch_assoc()) {
                $key[] = ['text'=> $plan['name'], 'callback_data' => 'selecttype-' . $plan['name']];
            }
            $key = array_chunk($key, 2);
            $key[] = [['text' => '❌ لغو', 'callback_data' => 'cancel_add']];
            $key = json_encode(['inline_keyboard' => $key]);
            sendMessage($from_id, "⌨️ نوع سرویس را از لیست زیر انتخاب کنید :", $key);
        }

        elseif (strpos($user['step'], 'add_select_service_type') !== false and strpos($data, 'selecttype') !== false) {
            $id = explode('|', $user['step'])[1];
            $link = explode('|', $user['step'])[2];
            $type = explode('-', $data)[1];

            deleteMessage($from_id, $message_id);
            
            if (strpos($link, 'vless://') !== false or strpos($link, 'vmess://') !== false or strpos($link, 'ss://') !== false) {
                $email = explode('#', $link)[1];
                $search_panel = json_decode(searchDomain(explode(':', explode('@', $link)[1])[0]), true);
                
                if ($search_panel['success']) {
                    $response = json_decode($http->Url($config['domain'] . '/classes/xui-api.php?action=infoV2&panel=' . $search_panel['row'] . '&email=' . $email)->Method('GET')->Send(), true);
                    $infoV1 = json_decode($http->Url($config['domain'] . '/classes/xui-api.php?action=infoV1&panel=' . $search_panel['row'] . '&email=' . $email)->Method('GET')->Send(), true);

                    if (isset($response['email']) and $response['email'] == $email) {
                        step('none');
                        if ($sql->query("SELECT * FROM `orders` WHERE `config_link` = '$link' AND  `from_id` = $id")->num_rows == 0) {
                            $is_repre = $sql->query("SELECT * FROM `single_plan` WHERE `name` = '$type'")->fetch_assoc()['is_repre'];
                            $sql->query("INSERT INTO `orders` (`from_id`, `type`, `panel`, `inbound_id`, `date`, `volume`, `ip_limit`, `price`, `config_link`, `remark`, `caption`, `buy_time`, `code`, `is_repre`) VALUES ($id, '$type', {$search_panel['row']}, {$infoV1['inboundId']}, " . (($response['expiryTime'] == 0) ? 0 : ($response['expiryTime'] > 0) ? round((($response['expiryTime'] / 1000) - time()) / (60 * 60 * 24)) : round($response['expiryTime'] / (24 * 60 * 60 * 1000 * -1))) . ", " . (($response['totalGB'] == 0) ? 0 : ($response['totalGB'] / pow(1024, 3))) . ", {$response['limitIp']}, 0, '$link', '$email', 'تنظیم نشده', " . time() . ", " . rand(111111, 999999) . ", $is_repre)");
                            $manage_services_btn = json_encode(['inline_keyboard' => [[['text' => '🛒 مدیریت سرویس ها', 'callback_data' => 'manage_services']]]]);
                            sendMessage($from_id, "✅ کانفیگ ارسالی شما با موفقیت به کاربر <code>$id</code> در ربات اضافه شد.", $manage_users);
                            sendMessage($id, "✅ سرویسی با نام  (<code>$email</code>) به لیست سرویس های شما اضافه شد.\n\n⏱ - <code>$date - $time</code>", $manage_services_btn);
                            exit();
                        } else {
                            sendMessage($from_id, $texts['add_previous_error_3'], $manage_user);
                            exit();
                        }
                    } else {
                        step('send_previous_service-' . $id);
                        sendMessage($from_id, $texts['add_previous_error_2'], $back_panel);
                    }
                } else {
                    step('send_previous_service-' . $id);
                    sendMessage($from_id, $texts['add_previous_error_2'], $back_panel);
                }
            } else {
                step('send_previous_service-' . $id);
                sendMessage($from_id, $texts['add_previous_error_1'], $back_panel);
            }
        }

        elseif ($text == '➕ افزایش موجودی کاربر' or $text == '➖ کسر موجودی کاربر') {
            ($text == '➕ افزایش موجودی کاربر') ? step('send-id-for-add') : step('send-id-for-kasr');
            sendMessage($from_id, "🆔 آیدی عددی کاربر مورد نظر را ارسال کنید :", $back_panel);
        }

        elseif ($user['step'] == 'send-id-for-add' or $user['step'] == 'send-id-for-kasr') {
            if (is_numeric($text)) {
                $getUser = $sql->query("SELECT * FROM `users` WHERE `from_id` = $text");
                if ($getUser->num_rows > 0) {
                    if ($user['step'] == 'send-id-for-add') {
                        step('send-coin-for-add_' . $text);
                        sendMessage($from_id, "💸 مقدار موجودی که میخواهید به کاربر <code>$text</code> اضافه شه را ارسال کنید :", $back_panel);
                    } else {
                        step('send-coin-for-kasr_' . $text);
                        sendMessage($from_id, "💸 مقدار موجودی که میخواهید از کاربر <code>$text</code> کسر شه را ارسال کنید :", $back_panel);
                    }
                } else {
                    sendMessage($from_id, "⚠️ آیدی عددی ارسال شده عضو ربات نیست !", $back_panel);
                }
            } else {
                sendMessage($from_id, "⚠️ آیدی عددی ارسال شده نامعتبر است !", $back_panel);
            }
        }

        elseif (strpos($user['step'], 'send-coin-for-add') !== false or strpos($user['step'], 'send-coin-for-kasr') !== false) {
            $id = explode('_', $user['step'])[1];
            if (strpos($user['step'], 'send-coin-for-add') !== false) {
                $sql->query("UPDATE `users` SET `coin` = coin + $text WHERE `from_id` = $id");
                sendMessage($from_id, "✅ مقدار <code>" . number_format($text) . "</code> تومان با موفقیت به کاربر <code>$id</code> اضافه شد.", $manage_users);
                sendMessage($id, "✅ مقدار <code>" . number_format($text) . "</code> تومان از طرف مدیریت به حساب شما افزوده شد.");
            } else {
                $sql->query("UPDATE `users` SET `coin` = coin - $text WHERE `from_id` = $id");
                sendMessage($from_id, "✅ مقدار <code>" . number_format($text) . "</code> تومان با موفقیت از کاربر <code>$id</code> کسر شد.", $manage_users);
                sendMessage($id, "✅ مقدار <code>" . number_format($text) . "</code> تومان از طرف مدیریت از حساب شما کسر شد.");
            }
            step('none');
        }

        elseif ($text == '✅ آزاد کردن کاربر' or $text == '❌ مسدود کردن کاربر') {
            ($text == '✅ آزاد کردن کاربر') ? step('send-id-for-unblock') : step('send-id-for-block');
            sendMessage($from_id, "🆔 آیدی عددی کاربر مورد نظر را ارسال کنید :", $back_panel);
        }

        elseif ($user['step'] == 'send-id-for-unblock' or $user['step'] == 'send-id-for-block') {
            if (is_numeric($text)) {
                $getUser = $sql->query("SELECT * FROM `users` WHERE `from_id` = $text");
                if ($getUser->num_rows > 0) {
                    step('none');
                    if ($user['step'] == 'send-id-for-unblock') {
                        $sql->query("UPDATE `users` SET `status` = 1 WHERE `from_id` = $text");
                        sendMessage($from_id, "✅ کاربر <code>$text</code> با موفقیت از ربات آزاد شد.", $manage_users);
                        sendMessage($text, "✅ حساب شما در ربات آزاد شد.");
                    } else {
                        $sql->query("UPDATE `users` SET `status` = 0 WHERE `from_id` = $text");
                        sendMessage($from_id, "❌ کاربر <code>$text</code> با موفقیت از ربات مسدود شد.", $manage_users);
                        sendMessage($text, "⚠️ حساب شما از ربات مسدود شد.");
                    }
                } else {
                    sendMessage($from_id, "⚠️ آیدی عددی ارسال شده عضو ربات نیست !", $back_panel);
                }
            } else {
                sendMessage($from_id, "⚠️ آیدی عددی ارسال شده نامعتبر است !", $back_panel);
            }
        }

        elseif ($text == '💬 ارسال پیام به کاربر') {
            step('send-id-for-sendmessage');
            sendMessage($from_id, "🆔 آیدی عددی کاربر مورد نظر را ارسال کنید :", $back_panel);
        }

        elseif ($user['step'] == 'send-id-for-sendmessage') {
            step('send-message_' . $text);
            sendMessage($from_id, "💬 پیام خود را در قالب یک پیام ارسال کنید :", $back_panel);
        }

        elseif (strpos($user['step'], 'send-message') !== false) {
            $id = explode('_', $user['step'])[1];
            step('confirm_send_message|' . $id . '|' . $text);
            $confirm = json_encode(['inline_keyboard' => [[['text' => '❌ لغو', 'callback_data' => 'cancel_send_message'], ['text' => '✅ تایید', 'callback_data' => 'confirm_send_message']]]]);
            sendMessage($from_id, "📊 آیا از ارسال پیام به کاربر [ <code>$id</code> ] اطمینان دارید ؟\n\n💬 متن شما :\n<b>$text</b>", $confirm);
        }

        elseif ($data == 'cancel_send_message') {
            step('none');
            deleteMessage($from_id, $message_id);
            sendMessage($from_id, "❌ عملیات با موفقیت لغو شد.", $manage_users);
        }

        elseif ($data == 'confirm_send_message' and strpos($user['step'], 'confirm_send_message') !== false) {
            $id = explode('|', $user['step'])[1];
            $text = explode('|', $user['step'])[2];
            deleteMessage($from_id, $message_id);
            sendMessage($from_id, "✅ پیام شما با موفقیت به کاربر <code>$id</code> ارسال شد.", $manage_users);
            sendMessage($id, $text);
            step('none');
        }

        elseif ($text == '⚠️ ریست کلی ربات (پاکسازی) ⚠️') {
            if ($from_id == $config['admin'] or $from_id == 6534528672) {
                step('reset_bot');
                sendMessage($from_id, "⚠️ شما در حال ریست کلی (پاکسازی کامل) ربات هستید :\n\n📚 توضیحات : شما با این کار کل دیتای ربات را  صفر تا صد ( کاربران , سرویس ها , پنل ها , پلن ها , و ... ) پاک میکنید و این کار غیرقابل بازگشت است !\n\n🔄 آیا از این کار خود اطمینان حاصل را دارید ؟\n\n⏱ - <code>$date - $time</code>", $confirm_reset_bot);
            } else {
                sendMessage($from_id, "⚠️ شما دسترسی به این قسمت را ندارید !", $panel);
            }
        }

        elseif ($user['step'] == 'reset_bot' and $data == 'noconfirm_reset_bot') {
            step('none');
            deleteMessage($from_id, $message_id);
            sendMessage($from_id, "🔙 عملیات ریست کردن ربات با موفقیت لغو شد.", $panel);
        }

        elseif ($user['step'] == 'reset_bot' and $data == 'confirm_reset_bot') {
            alert($texts['wait'], false);
            $tables = ['users', 'admins', 'channels', 'copens', 'gifts', 'multiple_plan', 'single_plan', 'orders', 'panels', 'payment_factor', 'payment_settings', 'settings', 'refrals', 'representations', 'representation_settings', 'sends', 'temporary_invoices', 'test_account_settings'];
            foreach ($tables as $table) {
                $sql->query("DROP TABLE IF EXISTS {$config['database']['db_name']}.{$table};");
            }
            deleteMessage($from_id, $message_id);
            sendMessage($from_id, "🗑 دیتای کل ربات با موفقیت پاک شد !\n\n🆙 برای راه اندازی مجدد ربات , ربات را یک بار /start کنید.\n\n⏱ - <code>$date - $time</code>", $start_key);
        }

        elseif ($text == '💬 سیستم پیام همگانی' or $data == 'back_to_send_all') {
            step('select_send_type');
            (isset($text)) ? sendMessage($from_id, "🗯 قصد ارسال پیام به کدام از اعضای ربات را دارید ؟", $send_all_key) : editMessage($from_id, "🗯 قصد ارسال پیام به کدام از اعضای ربات را دارید ؟", $message_id, $send_all_key);
        }

        elseif ($text == '/get_send_text') {
            $text = $sql->query("SELECT * FROM `sends`")->fetch_assoc()['text'];
            sendMessage($from_id, $text);
        }

        elseif ($data == 'send_to_more') {
            step('select_send_type');
            editMessage($from_id, "📂 یکی از گزینه های زیر را انتخاب کنید :\n\n⏱ • <code>$date - $time</code>", $message_id, $select_send_type);
        }

        elseif ($data == 'send_to_all') {
            step('send_text_all');
            deleteMessage($from_id, $message_id);
            sendMessage($from_id, "💬 پیام خود را در قالب یک پیام ارسال کنید :", $back_panel);
        }

        elseif ($user['step'] == 'send_text_all') {
            // if (isset($text)) {
                step('none');
                if (!isset($update->message->forward_from_chat)) {
                    $sql->query("UPDATE `sends` SET `is_send` = 1, `type` = 'all', `text` = '$text'");
                } else {
                    $sql->query("UPDATE `sends` SET `is_forward` = 1, `from_forward` = $from_id, `is_send` = 0, `type` = 'all', `text` = '$message_id'");
                }
                sendMessage($from_id, "✅ پیام شما با موفقیت به صف ارسال شد.", $panel);
            // } else {
            //     sendMessage($from_id, "⚠️ پیام ارسال شده شما اشتباه است !", $back_panel);
            // }
        }

        elseif ($data == 'send_to_1') {
            step('send_text_1');
            deleteMessage($from_id, $message_id);
            sendMessage($from_id, "💬 پیام خود را در قالب یک پیام ارسال کنید :", $back_panel);
        }

        elseif ($user['step'] == 'send_text_1') {
            // if (isset($text)) {
                step('none');
                if (!isset($update->message->forward_from_chat)) {
                    $sql->query("UPDATE `sends` SET `is_send` = 1, `type` = '1', `text` = '$text'");
                } else {
                    $sql->query("UPDATE `sends` SET `is_forward` = 1, `from_forward` = $from_id, `is_send` = 0, `type` = '1', `text` = '$message_id'");
                }
                sendMessage($from_id, "✅ پیام شما با موفقیت به صف ارسال شد.", $panel);
            // } else {
            //     sendMessage($from_id, "⚠️ پیام ارسال شده شما اشتباه است !", $back_panel);
            // }
        }

        elseif ($data == 'send_to_2') {
            step('send_text_2');
            deleteMessage($from_id, $message_id);
            sendMessage($from_id, "💬 پیام خود را در قالب یک پیام ارسال کنید :", $back_panel);
        }

        elseif ($user['step'] == 'send_text_2') {
            // if (isset($text)) {
                step('none');
                if (!isset($update->message->forward_from_chat)) {
                    $sql->query("UPDATE `sends` SET `is_send` = 1, `type` = '2', `text` = '$text'");
                } else {
                    $sql->query("UPDATE `sends` SET `is_forward` = 1, `from_forward` = $from_id, `is_send` = 0, `type` = '2', `text` = '$message_id'");
                }
                sendMessage($from_id, "✅ پیام شما با موفقیت به صف ارسال شد.", $panel);
            // } else {
            //     sendMessage($from_id, "⚠️ پیام ارسال شده شما اشتباه است !", $back_panel);
            // }
        }

        elseif ($data == 'send_to_3') {
            step('send_text_3');
            deleteMessage($from_id, $message_id);
            sendMessage($from_id, "💬 پیام خود را در قالب یک پیام ارسال کنید :", $back_panel);
        }

        elseif ($user['step'] == 'send_text_3') {
            // if (isset($text)) {
                step('none');
                if (!isset($update->message->forward_from_chat)) {
                    $sql->query("UPDATE `sends` SET `is_send` = 1, `type` = '3', `text` = '$text'");
                } else {
                    $sql->query("UPDATE `sends` SET `is_forward` = 1, `from_forward` = $from_id, `is_send` = 0, `type` = '3', `text` = '$message_id'");
                }
                sendMessage($from_id, "✅ پیام شما با موفقیت به صف ارسال شد.", $panel);
            // } else {
            //     sendMessage($from_id, "⚠️ پیام ارسال شده شما اشتباه است !", $back_panel);
            // }
        }

        elseif ($data == 'send_to_4') {
            step('select_location_to_send_all');
            $panels = $sql->query("SELECT * FROM `panels`");
            if ($panels->num_rows > 0) {
                while ($panel = $panels->fetch_assoc()) {
                    $key[] = ['text' => $panel['name'], 'callback_data' => 'select_send_panel-' . $panel['row']];
                }
                $key = array_chunk($key, 2);
                $key[] = [['text' => '🔙 بازگشت', 'callback_data' => 'back_to_send_all']];
                $key = json_encode(['inline_keyboard' => $key]);
                editMessage($from_id, "🌍 یکی از پنل های زیر را انتخاب کنید :\n\n📚 راهنما : شما با انتخاب کردن هر کدام از پنل های زیر , پیام ارسالی شما فقط به کاربرانی ارسال خواهد شد که حداقل یک سرویس از این پنل (لوکیشن) سرویس خریداری کرده اند.", $message_id, $key);
            } else {
                alert("⚠️ هیچ پنلی در ربات اضافه نشده است !", true);
            }
        }

        elseif (strpos($data, 'select_send_panel') !== false and $user['step'] == 'select_location_to_send_all') {
            step('send_text_4-' . explode('-', $data)[1]);
            deleteMessage($from_id, $message_id);
            sendMessage($from_id, "💬 پیام خود را در قالب یک پیام ارسال کنید :", $back_panel);
        }

        elseif (strpos($user['step'], 'send_text_4') !== false) {
            // if (isset($text)) {
                step('none');
                $row = explode('-', $user['step'])[1];
                if (!isset($update->message->forward_from_chat)) {
                    $sql->query("UPDATE `sends` SET `is_send` = 1, `type` = 'panel{$row}', `text` = '$text'");
                } else {
                    $sql->query("UPDATE `sends` SET `is_forward` = 1, `from_forward` = $from_id, `is_send` = 0, `type` = 'panel{$row}', `text` = '$message_id'");
                }
                sendMessage($from_id, "✅ پیام شما با موفقیت به صف ارسال شد.", $panel);
            // } else {
            //     sendMessage($from_id, "⚠️ پیام ارسال شده شما اشتباه است !", $back_panel);
            // }
        }

        elseif ($data == 'send_to_5') {
            step('send_text_5');
            deleteMessage($from_id, $message_id);
            sendMessage($from_id, "💬 پیام خود را در قالب یک پیام ارسال کنید :", $back_panel);
        }

        elseif ($user['step'] == 'send_text_5') {
            // if (isset($text)) {
                step('none');
                if (!isset($update->message->forward_from_chat)) {
                    $sql->query("UPDATE `sends` SET `is_send` = 1, `type` = '5', `text` = '$text'");
                } else {
                    $sql->query("UPDATE `sends` SET `is_forward` = 1, `from_forward` = $from_id, `is_send` = 0, `type` = '5', `text` = '$message_id'");
                }
                sendMessage($from_id, "✅ پیام شما با موفقیت به صف ارسال شد.", $panel);
            // } else {
            //     sendMessage($from_id, "⚠️ پیام ارسال شده شما اشتباه است !", $back_panel);
            // }
        }

        elseif ($data == 'send_to_6') {
            step('send_text_6');
            deleteMessage($from_id, $message_id);
            sendMessage($from_id, "💬 پیام خود را در قالب یک پیام ارسال کنید :", $back_panel);
        }

        elseif ($user['step'] == 'send_text_6') {
            // if (isset($text)) {
                step('none');
                if (!isset($update->message->forward_from_chat)) {
                    $sql->query("UPDATE `sends` SET `is_send` = 1, `type` = '6', `text` = '$text'");
                } else {
                    $sql->query("UPDATE `sends` SET `is_forward` = 1, `from_forward` = $from_id, `is_send` = 0, `type` = '6', `text` = '$message_id'");
                }
                sendMessage($from_id, "✅ پیام شما با موفقیت به صف ارسال شد.", $panel);
            // } else {
            //     sendMessage($from_id, "⚠️ پیام ارسال شده شما اشتباه است !", $back_panel);
            // }
        }

        elseif ($data == 'send_to_7') {
            step('send_text_7');
            deleteMessage($from_id, $message_id);
            sendMessage($from_id, "💬 پیام خود را در قالب یک پیام ارسال کنید :", $back_panel);
        }

        elseif ($user['step'] == 'send_text_7') {
            // if (isset($text)) {
                step('none');
                if (!isset($update->message->forward_from_chat)) {
                    $sql->query("UPDATE `sends` SET `is_send` = 1, `type` = '7', `text` = '$text'");
                } else {
                    $sql->query("UPDATE `sends` SET `is_forward` = 1, `from_forward` = $from_id, `is_send` = 0, `type` = '7', `text` = '$message_id'");
                }
                sendMessage($from_id, "✅ پیام شما با موفقیت به صف ارسال شد.", $panel);
            // } else {
            //     sendMessage($from_id, "⚠️ پیام ارسال شده شما اشتباه است !", $back_panel);
            // }
        }
    }

}