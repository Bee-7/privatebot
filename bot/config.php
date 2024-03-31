<?php

date_default_timezone_set('Asia/Tehran');
error_reporting(E_ALL & E_NOTICE);

# includes
include_once 'includes/functions.php';
include_once 'includes/jdf.php';
include_once 'classes/db.php';

# informations
$config = [
    'token' => '7090557394:AAH6akU3lZghip3iEPSJVBmeNLhjsqlifXo', // bot token
    'admin' => 568725816, // dev user id
    'admins' => [568725816, 5389659340], // admins id
    'domain' => 'https' . '://' . $_SERVER['SSL_TLS_SNI'] . '/' . explode('/', $_SERVER['PHP_SELF'])[1], // dont touch
    'database' => ['db_name' => 'vpnbot_sh', 'db_username' => 'vpnbot_sh', 'db_password' => 'vpnbotpass_sh'], // database informations
    'phpmyadmin' => ['username' => 'phpmyadmin', 'password' => 'phpmyadminpass'] // phpmyadmin informations
];

# -------------------------------- #

$database = new ConnectDatabase('localhost', $config['database']['db_name'], $config['database']['db_username'], $config['database']['db_password']);
$connect = json_decode($database->connect(), true);
($connect['status']) ? $database->createTables() : die($status['message']);
$sql = $database->getSql();

$texts = json_decode(file_get_contents('includes/texts.json'), true);
$time = date('H:i:s');
$date = convertNumber(jdate('Y/m/d'));

define('API_KEY_BOT', $config['token']);

# -------------------------------- #

$update = json_decode(file_get_contents('php://input'));
if (isset($update->message)) {
    $message_id = $update->message->message_id;
    $first_name = isset($update->message->from->first_name) ? $update->message->from->first_name : '❌';
    $username = isset($update->message->from->username) ? '@' . $update->message->from->username : '❌';
    $from_id = $update->message->from->id;
    $chat_id = $update->message->chat->id;
    $text = $update->message->text;
} elseif (isset($update->callback_query)) {
    $from_id = $update->callback_query->from->id;
    $data = $update->callback_query->data;
    $query_id = $update->callback_query->id;
    $message_id = $update->callback_query->message->message_id;
    $first_name = isset($update->callback_query->from->first_name) ? $update->callback_query->from->first_name : '❌';
    $username = isset($update->callback_query->from->username) ? '@' . $update->callback_query->from->username : "❌";
}

# -------------------------------- #

if (isset($update)) {
    $user = $sql->query("SELECT * FROM `users` WHERE `from_id` = $from_id");
    if ($user->num_rows == 0 and strpos($text, '/start ') === false) {
        $sql->query("INSERT INTO `users` (`from_id`, `join_time`) VALUES ($from_id, " . time() . ")");
    } else {
        $user = $user->fetch_assoc();
    }

    $settings = $sql->query("SELECT * FROM `settings`");
    if ($settings->num_rows == 0) {
        $sql->query("INSERT INTO `settings` () VALUES ()");
    } else {
        $settings = $settings->fetch_assoc();
    }

    $payment_settings = $sql->query("SELECT * FROM `payment_settings`");
    if ($payment_settings->num_rows == 0) {
        $sql->query("INSERT INTO `payment_settings` () VALUES ()");
    } else {
        $payment_settings = $payment_settings->fetch_assoc();
    }

    $test_account_settings = $sql->query("SELECT * FROM `test_account_settings`");
    if ($test_account_settings->num_rows == 0) {
        $sql->query("INSERT INTO `test_account_settings` () VALUES ()");
    } else {
        $test_account_settings = $test_account_settings->fetch_assoc();
    }

    $sends = $sql->query("SELECT * FROM `sends`");
    if ($sends->num_rows == 0) {
        $sql->query("INSERT INTO `sends` () VALUES ()");
    }

    $representation_settings = $sql->query("SELECT * FROM `representation_settings`")->fetch_assoc();

    $representations = [];
    $fetch_representations = $sql->query("SELECT * FROM `representations`");
    while ($rep = $fetch_representations->fetch_assoc()) {
        $representations[] = $rep['from_id'];
    }

    $admins = [];
    $fetch_admins = $sql->query("SELECT * FROM `admins`");
    while ($admin = $fetch_admins->fetch_assoc()) {
        $admins[] = $admin['from_id'];
    }

    $channels = [];
    $fetch_channels = $sql->query("SELECT * FROM `channels`");
    while ($channel = $fetch_channels->fetch_assoc()) {
        $channels[] = $channel['link'];
    }
}
