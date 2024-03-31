<?php

include_once 'config.php';
include_once 'includes/keyboards.php';
include_once 'includes/functions.php';

$sends = $sql->query("SELECT * FROM `sends`")->fetch_assoc();

if ($sends['is_send'] or $sends['is_forward']) {
    $representations = [];
    $users = $sql->query("SELECT * FROM `users`");
    $fetch_representations = $sql->query("SELECT * FROM `representations`");
    while ($rep = $fetch_representations->fetch_assoc()) {
        $representations[] = $rep['from_id'];
    }

    if ($sends['type'] == 'all') {
        if (!is_null($sends['text'])) {
            $user_sends = [];
            while ($user = $users->fetch_assoc()) {
                if (!in_array($user['from_id'], $user_sends)) {
                    if ($sends['is_send']) {
                        sendMessage($user['from_id'], $sends['text']);
                        $user_sends[] = $user['from_id'];
                    } elseif ($sends['is_forward']) {
                        forwardMessage($sends['from_forward'], $user['from_id'], $sends['text']);
                        $user_sends[] = $user['from_id'];
                    }
                }
            }
            echo json_encode(['success' => true, 'message' => 'Your text has been successfully sent to all bot users.'], 448);
            $sql->query("UPDATE `sends` SET `is_send` = 0, `is_forward` = 0");
        }
    }
    
    elseif ($sends['type'] == '1') {
        if (!is_null($sends['text'])) {
            $user_sends = [];
            while ($user = $users->fetch_assoc()) {
                if (!in_array($user['from_id'], $representations)) {
                    if (!in_array($user['from_id'], $user_sends)) {
                        if ($sends['is_send']) {
                            sendMessage($user['from_id'], $sends['text']);
                            $user_sends[] = $user['from_id'];
                        } elseif ($sends['is_forward']) {
                            forwardMessage($sends['from_forward'], $user['from_id'], $sends['text']);
                            $user_sends[] = $user['from_id'];
                        }
                    }
                }
            }
            echo json_encode(['success' => true, 'message' => 'Your text has been successfully sent to all bot users.'], 448);
            $sql->query("UPDATE `sends` SET `is_send` = 0, `is_forward` = 0");
        }
    } elseif ($sends['type'] == '2') {
        if (!is_null($sends['text'])) {
            foreach ($representations as $rep) {
                if ($sends['is_send']) {
                    sendMessage($rep, $sends['text']);
                } elseif ($sends['is_forward']) {
                    forwardMessage($sends['from_forward'], $rep, $sends['text']);
                }
            }
            echo json_encode(['success' => true, 'message' => 'Your text has been successfully sent to all bot users.'], 448);
            $sql->query("UPDATE `sends` SET `is_send` = 0, `is_forward` = 0");
        }
    } elseif ($sends['type'] == '3') {
        if (!is_null($sends['text'])) {
            $user_sends = [];
            $users = $sql->query("SELECT * FROM `users` u INNER JOIN `orders` o ON u.from_id = o.from_id");
            while ($user = $users->fetch_assoc()) {
                if (!in_array($user['from_id'], $user_sends)) {
                    if ($sends['is_send']) {
                        sendMessage($user['from_id'], $sends['text']);
                    } elseif ($sends['is_forward']) {
                        forwardMessage($sends['from_forward'], $user['from_id'], $sends['text']);
                    }
                    $user_sends[] = $user['from_id'];
                }
            }
            echo json_encode(['success' => true, 'message' => 'Your text has been successfully sent to all bot users.', 'type' => 3, 'count' => count($user_sends)], 448);
            $sql->query("UPDATE `sends` SET `is_send` = 0, `is_forward` = 0");
        }
    } elseif (strpos($sends['type'], 'panel') !== false) {
        if (!is_null($sends['text'])) {
            $row = explode('panel', $sends['type'])[1];
            $panel = $sql->query("SELECT * FROM `panels` WHERE `row` = $row")->fetch_assoc();
            $domain = ($panel['domain'] == '/blank') ? parse_url($panel['address'])['host'] : $panel['domain'];
            $orders = $sql->query("SELECT * FROM `orders`");
            $user_sends = [];
            while ($order = $orders->fetch_assoc()) {
                if (strpos($order['config_link'], $domain) !== false and !in_array($order['from_id'], $user_sends)) {
                    if ($sends['is_send']) {
                        sendMessage($order['from_id'], $sends['text']);
                    } elseif ($sends['is_forward']) {
                        forwardMessage($sends['from_forward'], $order['from_id'], $sends['text']);
                    }
                    $user_sends[] = $order['from_id'];
                }
            }
            $sql->query("UPDATE `sends` SET `is_send` = 0, `is_forward` = 0");
            echo json_encode(['success' => true, 'message' => 'Your text has been successfully sent to all bot users.', 'type' => 'panel', 'panel' => $row, 'count' => count($user_sends)], 448);
        }
    } elseif ($sends['type'] == '5') {
        if (!is_null($sends['text'])) {
            $users = $sql->query("SELECT * FROM `users` WHERE `status` = 0");
            while ($user = $users->fetch_assoc()) {
                if ($sends['is_send']) {
                    sendMessage($user['from_id'], $sends['text']);
                } elseif ($sends['is_forward']) {
                    forwardMessage($sends['from_forward'], $user['from_id'], $sends['text']);
                }
            }
            echo json_encode(['success' => true, 'message' => 'Your text has been successfully sent to all bot users.', 'type' => 5], 448);
            $sql->query("UPDATE `sends` SET `is_send` = 0, `is_forward` = 0");
        }
    } elseif ($sends['type'] == '6') {
        if (!is_null($sends['text'])) {
            $users = $sql->query("SELECT * FROM `users` WHERE `status` = 1");
            while ($user = $users->fetch_assoc()) {
                if ($sends['is_send']) {
                    sendMessage($user['from_id'], $sends['text']);
                } elseif ($sends['is_forward']) {
                    forwardMessage($sends['from_forward'], $user['from_id'], $sends['text']);
                }
            }
            echo json_encode(['success' => true, 'message' => 'Your text has been successfully sent to all bot users.', 'type' => 6], 448);
            $sql->query("UPDATE `sends` SET `is_send` = 0, `is_forward` = 0");
        }
    } elseif ($sends['type'] == '7') {
        if (!is_null($sends['text'])) {
            $count = 0;
            while ($user = $users->fetch_assoc()) {
                $order = $sql->query("SELECT * FROM `orders` WHERE `from_id` = {$user['from_id']}");
                if ($order->num_rows == 0) {
                    if ($sends['is_send']) {
                        sendMessage($user['from_id'], $sends['text']);
                    } elseif ($sends['is_forward']) {
                        forwardMessage($sends['from_forward'], $user['from_id'], $sends['text']);
                    }
                    $count++;
                }
            }
            echo json_encode(['success' => true, 'message' => 'Your text has been successfully sent to all bot users.', 'type' => 7, 'count' => $count], 448);
            $sql->query("UPDATE `sends` SET `is_send` = 0, `is_forward` = 0");
        }
    }
} else {
    echo json_encode(['response' => 'Access denied!'], 448);
}
