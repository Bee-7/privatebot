<?php

# includes
include_once 'xui.php';
include_once '../config.php';

if (isset($_GET['panel']) and isset($_GET['action'])) {
    $getPanel = $sql->query("SELECT * FROM `panels` WHERE `row` = {$_GET['panel']}");
    if ($getPanel->num_rows == 1) {
        $getPanel = $getPanel->fetch_assoc();
        $getTestAccount = $sql->query("SELECT * FROM `test_account_settings`")->fetch_assoc();
        $xui = new Xui(parse_url($getPanel['address'])['scheme'] . '://', parse_url($getPanel['address'])['host'], parse_url($getPanel['address'])['port'], $getPanel['session']);
    
        if ($_GET['action'] == 'create') {
            $inbound_id = (($_GET['type'] == 'test_account') ? $getTestAccount['inbound_id'] : $getPanel['inbound_id']);
            $response = $xui->addCli($inbound_id, $_GET['email'], $_GET['ip_limit'], $_GET['volume'], $_GET['date'], $_GET['start_after_use']);
            if (json_decode($response, true)['success']) {
                echo json_encode(['success' => true, 'config' => $xui->getCliConfig($_GET['email']), 'message' => 'Your config was created successfully.', 'data' => $_GET], 448);
            } else {
                echo $response;
            }
        }

        elseif ($_GET['action'] == 'infoV1') {
            $response = $xui->getCliStatusV1($_GET['email']);
            echo json_encode($response, 448);
        }

        elseif ($_GET['action'] == 'infoV2') {
            $response = $xui->getCliStatusV2($_GET['email']);
            echo json_encode($response, 448);
        }

        elseif ($_GET['action'] == 'renew_config') {
            $response = $xui->renewConfig($_GET['inbound_id'], $_GET['email'], $_GET['ip_limit'], $_GET['volume'], $_GET['date']);
            if (json_decode($response, true)['success']) {
                echo json_encode(['success' => true, 'config' => $xui->getCliConfig($_GET['email']), 'message' => 'Client updatedSuccess.'], 448);
            } else {
                echo $response;
            }
        }

        elseif ($_GET['action'] == 'get_config_link') {
            $response = $xui->getCliConfig($_GET['email']);
            if (json_decode($response, true)['success']) {
                echo json_encode(['success' => true, 'config' => $xui->getCliConfig($_GET['email'])], 448);
            } else {
                echo $response;
            }
        }

        elseif ($_GET['action'] == 'change_config_link') {
            $response = $xui->changeConfigLink($getPanel['inbound_id'], $_GET['email']);
            if (json_decode($response, true)['success']) {
                echo json_encode(['success' => true, 'config' => $xui->getCliConfig($_GET['email']), 'message' => 'Client updatedSuccess.'], 448);
            } else {
                echo $response;
            }
        }

        elseif ($_GET['action'] == 'change_config_status') {
            $response = $xui->changeConfigStatus($getPanel['inbound_id'], $_GET['email'], $_GET['status']);
            if (json_decode($response, true)['success']) {
                echo json_encode(['success' => true, 'config' => $xui->getCliConfig($_GET['email']), 'message' => 'Client updatedSuccess.'], 448);
            } else {
                echo $response;
            }
        }

        elseif ($_GET['action'] == 'delete_config') {
            $response = $xui->deleteConfig($getPanel['inbound_id'], $_GET['email']);
            if (json_decode($response, true)['success']) {
                echo json_encode(['success' => true, 'message' => 'Config deleted.'], 448);
            } else {
                echo $response;
            }
        }

        elseif ($_GET['action'] == 'add_ip_limit') {
            $response = $xui->addIpLimit($getPanel['inbound_id'], $_GET['email'], $_GET['ip_limit']);
            if (json_decode($response, true)['success']) {
                echo json_encode(['success' => true, 'config' => $xui->getCliConfig($_GET['email']), 'message' => 'Client updatedSuccess.', 'ip_limit' => $_GET['ip_limit']], 448);
            } else {
                echo $response;
            }
        }

    } else {
        echo json_encode(['success' => false, 'message' => 'Panel not found.'], 448);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No mandatory parameters sent.'], 448);
}
