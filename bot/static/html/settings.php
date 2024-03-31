<?php

include_once '../../config.php';
include_once '../../includes/functions.php';
include_once '../../classes/request.php';

$http = new HTTPRequest();

$alert_text = ''; // DEFAULT NULL
$alert_type = 'info'; // DEFAULT info (blud)

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if ($_GET['from_id'] == $config['admin'] or in_array($_GET['from_id'], $config['admins'])) {
        $settings = $sql->query("SELECT * FROM `settings`")->fetch_assoc();
        $test_settings = $sql->query("SELECT * FROM `test_account_settings`")->fetch_assoc();
    } else {
        exit('Access denied!');
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $settings = $sql->query("SELECT * FROM `settings`")->fetch_assoc();
    $test_settings = $sql->query("SELECT * FROM `test_account_settings`")->fetch_assoc();

    if (isset($_POST['bot-status-name'])) {
        if ($_POST['bot-status-name'] == 'turn-on-bot') {
            $sql->query("UPDATE `settings` SET `bot_status` = 1");
            $alert_text = 'ربات با موفقیت روشن شد.';
            $alert_type = 'success';
        } elseif ($_POST['bot-status-name'] == 'turn-off-bot') {
            $sql->query("UPDATE `settings` SET `bot_status` = 0");
            $alert_text = 'ربات با موفقیت خاموش شد.';
            $alert_type = 'success';
        }
    }

    if (isset($_POST['refral-status-name'])) {
        if ($_POST['refral-status-name'] == 'turn-on-refral') {
            $sql->query("UPDATE `settings` SET `refral_status` = 1");
            $alert_text = 'زیرمجموعه گیری با موفقیت روشن شد.';
            $alert_type = 'success';
        } elseif ($_POST['refral-status-name'] == 'turn-off-refral') {
            $sql->query("UPDATE `settings` SET `refral_status` = 0");
            $alert_text = 'زیرمجموعه گیری با موفقیت خاموش شد.';
            $alert_type = 'success';
        }
    }

    if (isset($_POST['deposit-status-name'])) {
        if ($_POST['deposit-status-name'] == 'turn-on-deposit') {
            $sql->query("UPDATE `settings` SET `deposit_status` = 1");
            $alert_text = 'شارژ حساب با موفقیت روشن شد.';
            $alert_type = 'success';
        } elseif ($_POST['refral-status-name'] == 'turn-off-deposit') {
            $sql->query("UPDATE `settings` SET `deposit_status` = 0");
            $alert_text = 'شارژ حساب با موفقیت خاموش شد.';
            $alert_type = 'success';
        }
    }

    if (isset($_POST['buy-status-name'])) {
        if ($_POST['buy-status-name'] == 'turn-on-buy') {
            $sql->query("UPDATE `settings` SET `buy_config_status` = 1");
            $alert_text = 'خرید سرویس با موفقیت روشن شد.';
            $alert_type = 'success';
        } elseif ($_POST['buy-status-name'] == 'turn-off-buy') {
            $sql->query("UPDATE `settings` SET `buy_config_status` = 0");
            $alert_text = 'خرید سرویس با موفقیت خاموش شد.';
            $alert_type = 'success';
        }
    }

    if (isset($_POST['test-status-name'])) {
        if ($_POST['test-status-name'] == 'turn-on-test') {
            $sql->query("UPDATE `test_account_settings` SET `status` = 1");
            $alert_text = 'سرویس تست با موفقیت روشن شد.';
            $alert_type = 'success';
        } elseif ($_POST['test-status-name'] == 'turn-off-test') {
            $sql->query("UPDATE `test_account_settings` SET `status` = 0");
            $alert_text = 'سرویس تست با موفقیت خاموش شد.';
            $alert_type = 'success';
        }
    }

    if (isset($_POST['representation-status-name'])) {
        if ($_POST['representation-status-name'] == 'turn-on-representation') {
            $sql->query("UPDATE `settings` SET `representation_status` = 1");
            $alert_text = 'بخش نمایندگی با موفقیت روشن شد.';
            $alert_type = 'success';
        } elseif ($_POST['representation-status-name'] == 'turn-off-representation') {
            $sql->query("UPDATE `settings` SET `representation_status` = 0");
            $alert_text = 'بخش نمایندگی با موفقیت خاموش شد.';
            $alert_type = 'success';
        }
    }

    if (isset($_POST['baseic-after-use-name'])) {
        if ($_POST['baseic-after-use-name'] == 'turn-on-baseic-after-use') {
            $sql->query("UPDATE `settings` SET `basic_after_first_use` = 1");
            $alert_text = 'تیک پس از اولین اتصال برای قسمت ساده با موفقیت روشن شد.';
            $alert_type = 'success';
        } elseif ($_POST['baseic-after-use-name'] == 'turn-off-baseic-after-use') {
            $sql->query("UPDATE `settings` SET `basic_after_first_use` = 0");
            $alert_text = 'تیک پس از اولین اتصال برای قسمت ساده با موفقیت خاموش شد.';
            $alert_type = 'success';
        }
    }

    if (isset($_POST['representation-after-use-name'])) {
        if ($_POST['representation-after-use-name'] == 'turn-on-representation-after-use') {
            $sql->query("UPDATE `settings` SET `representation_after_first_use` = 1");
            $alert_text = 'تیک پس از اولین اتصال برای قسمت نمایندکان با موفقیت روشن شد.';
            $alert_type = 'success';
        } elseif ($_POST['representation-after-use-name'] == 'turn-off-representation-after-use') {
            $sql->query("UPDATE `settings` SET `representation_after_first_use` = 0");
            $alert_text = 'تیک پس از اولین اتصال برای قسمت نمایندگان با موفقیت خاموش شد.';
            $alert_type = 'success';
        }
    }

    if (isset($_POST['phone-name'])) {
        if ($_POST['phone-name'] == 'turn-on-phone') {
            $sql->query("UPDATE `settings` SET `phone_status` = 1");
            $alert_text = 'قفل شماره موبایل فعال شد.';
            $alert_type = 'success';
        } elseif ($_POST['phone-name'] == 'turn-off-phone') {
            $sql->query("UPDATE `settings` SET `phone_status` = 0");
            $alert_text = 'قفل شماره موبایل غیرفعال است.';
            $alert_type = 'success';
        }
    }

    if (isset($_POST['phone-country'])) {
        $sql->query("UPDATE `settings` SET `phone_country` = '{$_POST['phone-country']}'");
        $alert_text = 'قفل شماره بر روی کشور (<code>' . $_POST['phone-country'] . '</code>) فعال شد.';
        $alert_type = 'success';
    }

    if (isset($_POST['add-previous-name'])) {
        if ($_POST['add-previous-name'] == 'turn-on-add-previous') {
            $sql->query("UPDATE `settings` SET `add_previous_status` = 1");
            $alert_text = 'افزودن سرویس قبلی با موفقیت برای کاربر فعال شد.';
            $alert_type = 'success';
        } elseif ($_POST['add-previous-name'] == 'turn-off-add-previous') {
            $sql->query("UPDATE `settings` SET `add_previous_status` = 0");
            $alert_text = 'افزودن سرویس قبلی با موفقیت برای کاربر غیرفعال شد.';
            $alert_type = 'success';
        }
    }

    if (isset($_POST['main_prefix'])) {
        if ($_POST['main_prefix'] != $settings['main_prefix']) {
            if (strlen($_POST['main_prefix']) > 1) {
                $sql->query("UPDATE `settings` SET `main_prefix` = '{$_POST['main_prefix']}'");
                $alert_text = 'پیشوند شما (<code>' . $_POST['main_prefix'] . '</code>) با موفقیت تنظیم شد.';
                $alert_type = 'success';
            } else {
                $alert_text = 'پیشوند ارسالی شما اشتباه است.';
                $alert_type = 'danger';
            }
        }
    }

    if (isset($_POST['support'])) {
        if ($_POST['support'] != $settings['support']) {
            if (strlen($_POST['support']) > 1) {
                $sql->query("UPDATE `settings` SET `support` = '{$_POST['support']}'");
                $alert_text = 'یوزرنیم پشتیبانی شما (<code>' . $_POST['support'] . '</code>) با موفقیت تنظیم شد.';
                $alert_type = 'success';
            } else {
                $alert_text = ' یوزرنیم پشتیبانی ارسالی شما اشتباه است.';
                $alert_type = 'danger';
            }
        }
    }

    if (isset($_POST['education_channel'])) {
        if ($_POST['education_channel'] != $settings['education_channel']) {
            if (strlen($_POST['education_channel']) > 1 and strpos($_POST['education_channel'], 'https://t.me/') !== false) {
                $sql->query("UPDATE `settings` SET `education_channel` = '{$_POST['education_channel']}'");
                $alert_text = 'یوزرنیم کانال آموزش شما (<code>' . $_POST['education_channel'] . '</code>) با موفقیت تنظیم شد.';
                $alert_type = 'success';
            } else {
                $alert_text = ' یوزرنیم کانال ارسالی شما اشتباه است.';
                $alert_type = 'danger';
            }
        }
    }

    // reload informations . . .
    $settings = $sql->query("SELECT * FROM `settings`")->fetch_assoc();
    $test_settings = $sql->query("SELECT * FROM `test_account_settings`")->fetch_assoc();
}

?>

<!DOCTYPE html>
<html lang="en" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta name="format-detection" content="telephone=no" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="MobileOptimized" content="176" />
    <meta name="HandheldFriendly" content="True" />
    <meta name="robots" content="noindex,nofollow" />
    <title>تنظیمات کلی ربات</title>
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <style>
        body {
            font-family: 'Vazirmatn' !important;
        }
    </style>

</head>

<body class="bg-light">

    <div class="container px-4">

        <div class="row mt-1 justify-content-around">

            <div class="row pt-4 gy-2 gx-2 mb-2 align-items-center">

                <div class="col-12">
                    <?php if ($alert_text != '') { ?>
                        <div class="alert alert-<?php echo $alert_type; ?> text-center" style="padding: 0.5rem !important;"><?php echo $alert_text; ?></div>
                    <?php } else { ?>
                        <div class="alert alert-info text-center" style="padding: 0.5rem !important;"><a href="settings.php?from_id=<?php echo $_GET['from_id']; ?>" style="text-decoration: none; color: black;">تنظیمات ربات شما به شرح زیر است.</a></div>
                    <?php } ?>
                </div>

            </div>

            <div class="col-12">
                <hr style="margin: 0.65rem 0;">
            </div>

            <div class="tab-content mt-3" id="pills-tabContent">

                <div class="tab-pane fade show active" role="tabpanel" tabindex="0">

                    <form action="settings.php?from_id=<?php echo $_GET['from_id']; ?>" method="post">

                        <div class="row gx-2 gy-0 justify-content-around">
                            <div class="col-6">
                                <label for="bot-status" class="label-control">وضعیت ربات</label>
                                <select name="bot-status-name" class="form-select" id="bot-status">
                                    <option value="disabeled" disabled selected><?php echo ($settings['bot_status']) ? 'روشن است' : 'خاموش است'; ?></option>
                                    <option value="turn-on-bot">روشن کردن</option>
                                    <option value="turn-off-bot">خاموش کردن</option>
                                </select>
                            </div>

                            <div class="col-6">
                                <label for="refral-status" class="label-control">وضعیت زیرمجموعه گیری</label>
                                <select name="refral-status-name" class="form-select" id="refral-status">
                                    <option value="disabeled" disabled selected><?php echo ($settings['refral_status']) ? 'روشن است' : 'خاموش است'; ?></option>
                                    <option value="turn-on-refral">روشن کردن</option>
                                    <option value="turn-off-refral">خاموش کردن</option>
                                </select>
                            </div>
                        </div>

                        <div class="row gx-2 gy-0 justify-content-around mt-3">
                            <div class="col-6">
                                <label for="deposit-status" class="label-control">وضعیت شارژ حساب</label>
                                <select name="deposit-status-name" class="form-select" id="deposit-status">
                                    <option value="disabeled" disabled selected><?php echo ($settings['deposit_status']) ? 'روشن است' : 'خاموش است'; ?></option>
                                    <option value="turn-on-deposit">روشن کردن</option>
                                    <option value="turn-off-deposit">خاموش کردن</option>
                                </select>
                            </div>

                            <div class="col-6">
                                <label for="buy-status" class="label-control">وضعیت خرید سرویس</label>
                                <select name="buy-status-name" class="form-select" id="refral-status">
                                    <option value="disabeled" disabled selected><?php echo ($settings['buy_config_status']) ? 'روشن است' : 'خاموش است'; ?></option>
                                    <option value="turn-on-buy">روشن کردن</option>
                                    <option value="turn-off-buy">خاموش کردن</option>
                                </select>
                            </div>
                        </div>

                        <div class="row gx-2 gy-0 justify-content-around mt-3">
                            <div class="col-6">
                                <label for="test-status" class="label-control">وضعیت اکانت تست</label>
                                <select name="test-status-name" class="form-select" id="test-status">
                                    <option value="disabeled" disabled selected><?php echo ($test_settings['status']) ? 'روشن است' : 'خاموش است'; ?></option>
                                    <option value="turn-on-test">روشن کردن</option>
                                    <option value="turn-off-test">خاموش کردن</option>
                                </select>
                            </div>

                            <div class="col-6">
                                <label for="representation-status" class="label-control">وضعیت نمایندگی</label>
                                <select name="representation-status-name" class="form-select" id="representation-status">
                                    <option value="disabeled" disabled selected><?php echo ($settings['representation_status']) ? 'روشن است' : 'خاموش است'; ?></option>
                                    <option value="turn-on-representation">روشن کردن</option>
                                    <option value="turn-off-representation">خاموش کردن</option>
                                </select>
                            </div>
                        </div>

                        <div class="row gx-2 gy-0 justify-content-around mt-3">
                            <div class="col-6">
                                <label for="baseic-after-use-status" class="label-control">اولین اتصال (ساده)</label>
                                <select name="baseic-after-use-name" class="form-select" id="baseic-after-use-status">
                                    <option value="disabeled" disabled selected><?php echo ($settings['basic_after_first_use']) ? 'روشن است' : 'خاموش است'; ?></option>
                                    <option value="turn-on-baseic-after-use">روشن کردن</option>
                                    <option value="turn-off-baseic-after-use">خاموش کردن</option>
                                </select>
                            </div>

                            <div class="col-6">
                                <label for="representation-after-use-status" class="label-control">اولین اتصال (نماینده)</label>
                                <select name="representation-after-use-name" class="form-select" id="representation-after-use-status">
                                    <option value="disabeled" disabled selected><?php echo ($settings['representation_after_first_use']) ? 'روشن است' : 'خاموش است'; ?></option>
                                    <option value="turn-on-representation">روشن کردن</option>
                                    <option value="turn-off-representation">خاموش کردن</option>
                                </select>
                            </div>
                        </div>

                        <div class="row gx-2 gy-0 justify-content-around mt-3">
                            <div class="col-6">
                                <label for="phone-status" class="label-control">وضعیت قفل شماره</label>
                                <select name="phone-name" class="form-select" id="phone-status">
                                    <option value="disabeled" disabled selected><?php echo ($settings['phone_status']) ? 'روشن است' : 'خاموش است'; ?></option>
                                    <option value="turn-on-phone">روشن کردن</option>
                                    <option value="turn-off-phone">خاموش کردن</option>
                                </select>
                            </div>

                            <div class="col-6">
                                <label for="phone-country" class="label-control">فعال برای کشور</label>
                                <select name="phone-country" class="form-select" id="phone-country">
                                    <option value="disabeled" disabled selected><?php echo $settings['phone_country']; ?></option>
                                    <option value="all">all ( همه )</option>
                                    <option value="iran">iran ( ایران )</option>
                                    <option value="usa">usa ( آمریکا )</option>
                                    <option value="india">india ( هند )</option>
                                    <option value="germany">germany ( آلمان )</option>
                                </select>
                            </div>
                        </div>

                        <div class="row gx-2 gy-0 justify-content-around mt-3">
                            <div class="col-12">
                                <label for="add-previous-status" class="label-control">وضعیت افزودن سرویس قبلی ( برای کاربر )</label>
                                <select name="add-previous-name" class="form-select" id="add-previous-status">
                                    <option value="disabeled" disabled selected><?php echo ($settings['add_previous_status']) ? 'روشن است' : 'خاموش است'; ?></option>
                                    <option value="turn-on-add-previous">روشن کردن</option>
                                    <option value="turn-off-add-previous">خاموش کردن</option>
                                </select>
                            </div>
                        </div>

                        <div class="row gx-2 gy-0 justify-content-around mt-3">
                            <div class="col-12">
                                <label for="main-prefix" class="label-control">پیشوند اصلی (<code class="text-danger">remark</code>) سرویس ها:</label>
                                <input type="text" class="form-control" name="main_prefix" id="main-prefix" value="<?php echo $settings['main_prefix']; ?>">
                            </div>
                        </div>

                        <div class="row gx-2 gy-0 justify-content-around mt-3">
                            <div class="col-12">
                                <label for="support-username" class="label-control">یوزرنیم پشتیبانی:</label>
                                <input type="text" class="form-control" name="support" id="support-username" value="<?php echo $settings['support']; ?>">
                            </div>
                        </div>

                        <div class="row gx-2 gy-0 justify-content-around mt-3">
                            <div class="col-12">
                                <label for="education-channel" class="label-control">یوزرنیم کانال آموزش ها:</label>
                                <input type="text" class="form-control" name="education_channel" id="education-channel" value="<?php echo $settings['education_channel']; ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">ثبت تغییرات</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha2/js/bootstrap.bundle.min.js"></script>
</body>

</html>