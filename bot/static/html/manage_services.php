<?php

include_once '../../config.php';
include_once '../../includes/functions.php';
include_once '../../classes/request.php';

$http = new HTTPRequest();

$alert_text = '';
$alert_type = 'info';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if ($_GET['from_id'] == $config['admin'] or in_array($_GET['from_id'], $config['admins']) or in_array($_GET['from_id'], $admins)) {
        if (isset($_GET['filter'])) {
            if ($sql->query("SELECT * FROM `users` WHERE `from_id` = {$_GET['filter']}")->num_rows > 0) {
                $offset = $_GET['page'] * 5 - 5; $addpage = $_GET['page'] + 1; $menpage = $_GET['page'] - 1;
                $services = $sql->query("SELECT * FROM `orders` WHERE `from_id` = {$_GET['filter']} ORDER BY `buy_time` DESC LIMIT 5 OFFSET $offset");
            } else {
                $alert_text = ('کاربر (<code>' . $_GET['filter'] . '</code>) عضو ربات نیست.');
                $alert_type = 'danger';
            }
        } else {
            $offset = $_GET['page'] * 5 - 5;
            $addpage = $_GET['page'] + 1;
            $menpage = $_GET['page'] - 1;
            $services = $sql->query("SELECT * FROM `orders` ORDER BY `buy_time` DESC LIMIT 5 OFFSET $offset");
        }
    } else {
        exit('Access denied');
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_POST['search-type'] == 'based-on-userid') {
        if ($sql->query("SELECT * FROM `users` WHERE `from_id` = {$_POST['search-keyword']}")->num_rows > 0) {
            $services = $sql->query("SELECT * FROM `orders` WHERE `from_id` = {$_POST['search-keyword']}");
            if ($services->num_rows == 0) {
                $alert_text = ('کاربر (<code>' . $_POST['search-keyword'] . '</code>) هیچ سرویسی در ربات ندارد.');
                $alert_type = 'danger';
            } else {
                $alert_text = ('لیست همه سرویس های کاربر (<code>' . $_POST['search-keyword'] . '</code>) به شرح زیر است.');
            }
        } else {
            $alert_text = 'آیدی عددی ارسال شده اشتباه است !';
            $alert_type = 'danger';
        }
    } elseif ($_POST['search-type'] == 'based-on-remark') {
        if ($sql->query("SELECT * FROM `orders` WHERE `remark` = '{$_POST['search-keyword']}'")->num_rows > 0) {
            $services = $sql->query("SELECT * FROM `orders` WHERE `remark` = '{$_POST['search-keyword']}'");
            $alert_text = ('اطلاعات سرویس (<code>' . $_POST['search-keyword'] . '</code>) به شرح زیر است.');
        } else {
            $alert_text = ('سرویسی با نام (<code>' . $_POST['search-keyword'] . '</code>) در دیتابیس ربات یافت نشد.');
            $alert_type = 'danger';
        }
    } elseif ($_POST['search-type'] == 'based-on-usercode') {
        $settings = $sql->query("SELECT * FROM `settings`")->fetch_assoc();
        $result = ($settings['main_prefix'] . '-' . $_POST['search-keyword']);
        if ($sql->query("SELECT * FROM `orders` WHERE `remark` = '$result'")->num_rows > 0) {
            $services = $sql->query("SELECT * FROM `orders` WHERE `remark` = '$result'");
            $alert_text = ('اطلاعات سرویس با کد کاربری (<code>' . $_POST['search-keyword'] . '</code>) به شرح زیر است.');
        } else {
            $alert_text = ('سرویسی با کد کاربری (<code>' . $_POST['search-keyword'] . '</code>) در دیتابیس ربات یافت نشد.');
            $alert_type = 'danger';
        }
    } elseif ($_POST['search-type'] == 'based-on-6code') {
        if ($sql->query("SELECT * FROM `orders` WHERE `code` = {$_POST['search-keyword']}")->num_rows > 0) {
            $services = $sql->query("SELECT * FROM `orders` WHERE `code` = {$_POST['search-keyword']}");
            $alert_text = ('اطلاعات سرویس با کد 6 رقمی (<code>' . $_POST['search-keyword'] . '</code>) به شرح زیر است.');
        } else {
            $alert_text = ('سرویسی با کد 6 رقمی (<code>' . $_POST['search-keyword'] . '</code>) در دیتابیس ربات یافت نشد.');
            $alert_type = 'danger';
        }
    } elseif ($_POST['sendMessage'] == 'sendMessage') {
        if ($sql->query("SELECT * FROM `users` WHERE `from_id` = {$_POST['userid']}")->num_rows > 0) {
            $services = $sql->query("SELECT * FROM `orders` ORDER BY `buy_time` DESC LIMIT 5 OFFSET 0");
            sendMessage($_POST['userid'], $_POST['message']);
            $alert_text = ('پیام شما با موفقیت به کاربر  (<code>' . $_POST['userid'] . '</code>) ارسال شد.');
            $alert_type = 'success';
        } else {
            $alert_text = 'آیدی عددی ارسال شده اشتباه است !';
            $alert_type = 'danger';
        }
    } elseif ($_POST['changeServiceStatus'] == 'changeServiceStatus') {
        $services = $sql->query("SELECT * FROM `orders` ORDER BY `buy_time` DESC LIMIT 5 OFFSET 0");
        $response = $http->Url($config['domain'] . '/classes/xui-api.php?action=change_config_status&panel=' . $_POST['panel'] . '&email=' . $_POST['email'] . '&status=' . $_POST['status'])->Method('GET')->Option(CURLOPT_SSL_VERIFYHOST, false)->Option(CURLOPT_SSL_VERIFYPEER, false)->Send();
        $response = json_decode($response, true);
        if (isset($response['success']) and $response['success'] == false) {
            $alert_text = ('عملیات غیرفعال کردن سرویس (<code>' . $_POST['email'] . '</code>) با خطا مواجه شد.');
            $alert_type = 'danger';
        } else {
            $status = ($_POST['status'] == 'true') ? 1 : 0;
            $sql->query("UPDATE `orders` SET `status` = $status WHERE `remark` = '{$_POST['email']}'");
            $alert_text = ('عملیات غیرفعال کردن سرویس (<code>' . $_POST['email'] . '</code>) با موفقیت انجام شد.');
            $alert_type = 'success';
        }
    } elseif ($_POST['deleteService'] == 'deleteService') {
        $response = $http->Url($config['domain'] . '/classes/xui-api.php?action=delete_config&panel=' . $_POST['panel'] . '&email=' . $_POST['email'])->Method('GET')->Option(CURLOPT_SSL_VERIFYHOST, false)->Option(CURLOPT_SSL_VERIFYPEER, false)->Send();
        $response = json_decode($response, true);
        if (isset($response['success']) and $response['success'] == false) {
            $services = $sql->query("SELECT * FROM `orders` ORDER BY `buy_time` DESC LIMIT 5 OFFSET 0");
            $alert_text = ('عملیات حذف سرویس (<code>' . $_POST['email'] . '</code>) با خطا مواجه شد.');
            $alert_type = 'danger';
        } else {
            $status = ($_POST['status'] == 'true') ? 1 : 0;
            $sql->query("DELETE FROM `orders` WHERE `remark` = '{$_POST['email']}'");
            $services = $sql->query("SELECT * FROM `orders` ORDER BY `buy_time` DESC LIMIT 5 OFFSET 0");
            $alert_text = ('عملیات حذف سرویس (<code>' . $_POST['email'] . '</code>) با موفقیت انجام شد.');
            $alert_type = 'success';
        }
    }

}

?>

<!DOCTYPE html>
<html lang="en" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta name="format-detection" content="telephone=no" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="MobileOptimized" content="176" />
    <meta name="HandheldFriendly" content="True" />
    <meta name="robots" content="noindex,nofollow" />
    <title>لیست همه سرویس های ربات</title>
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet"
        type="text/css" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
        crossorigin="anonymous"></script>
    <style>
        body {
            font-family: 'Vazirmatn' !important;
        }
    </style>

</head>

<body class="bg-light">

    <div class="container px-4">

        <div class="row mt-0 justify-content-around">

            <form action="manage_services.php?from_id=<?php echo $_GET['from_id']; ?>" method="post">
                <div class="row pt-4 gy-2 gx-2 mb-2 align-items-center">

                    <div class="col-12">
                        <?php if ($alert_text != '') { ?>
                            <div class="alert alert-<?php echo $alert_type; ?> text-center"
                                style="padding: 0.5rem !important;">
                                <?php echo $alert_text; ?>
                            </div>
                        <?php } else { ?>
                            <div class="alert alert-info text-center" style="padding: 0.5rem !important;">لیست همه سرویس های
                                خریداری شده در ربات به شرح زیر است.</div>
                        <?php } ?>
                        <div class="alert alert-warning text-center" style="padding: 0.5rem !important;">تعداد کل سرویس
                            ها : <code><?php echo $sql->query("SELECT * FROM `orders`")->num_rows ?? 0; ?></code> عدد
                        </div>
                    </div>

                    <div class="col-12">
                        <input type="hidden" name="page" value="1">
                        <input type="text" name="search-keyword" class="form-control" id="search-box" value=""
                            placeholder="جستجو نام سرویس یا ایدی عددی فرد یا ..." required>
                    </div>


                    <div class="col-8">
                        <select name="search-type" class="form-select" id="specificSizeSelect">
                            <option value="based-on-userid">بر اساس آیدی عددی</option>
                            <option value="based-on-remark">بر اساس نام سرویس</option>
                            <option value="based-on-usercode" selected>بر اساس کد کاربری</option>
                            <option value="based-on-6code">بر اساس کد 6 رقمی</option>
                        </select>
                    </div>

                    <div class="col-4">
                        <div class="d-grid">
                            <input type="submit" class="btn btn-secondary" value="جستجو">
                        </div>
                    </div>

                </div>
            </form>

            <div class="col-12">
                <hr style="margin: 0.65rem 0;">
            </div>

            <!-- <div class="row gy-2 gx-2 mb-2 align-items-center">
                <?php
                $backpage = ($_GET['page'] > 1) ? 'صفحه قبلی' : null;
                if ($page * 7 < $sql->query("SELECT * FROM `orders`")->num_rows) {
                    $nextpage = 'صفحه بعدی';
                } else {
                    $nextpage = null;
                }
                ?>
                <div class="col-12">
                    <div class="d-grid">
                        <?php if (!is_null($nextpage)) { ?>
                            <a href="manage_services.php?from_id=<?php echo $_GET['from_id']; ?>&page=<?php echo $addpage ?>" class="btn btn-success"><?php echo $nextpage ?></a>
                        <?php } ?>
                        <?php if (!is_null($backpage)) { ?>
                            <a href="manage_services.php?from_id=<?php echo $_GET['from_id']; ?>&page=<?php echo $menpage ?>" class="btn btn-success mt-2"><?php echo $backpage ?></a>
                        <?php } ?>
                    </div>
                </div>
            </div> -->

            <div class="tab-content mt-3" id="pills-tabContent">

                <div class="tab-pane fade show active" role="tabpanel" tabindex="0">

                    <div class="row gx-2 gy-0 justify-content-around">

                        <?php while ($service = $services->fetch_assoc()) { ?>
                            <div class="card" style="width: 25rem; margin-bottom: 20px;">
                                <div class="card-body" id="get-<?php echo $service['row']; ?>">

                                    <?php
                                    $search = json_decode(searchDomain(explode(':', explode('@', $service['config_link'])[1])[0]), true);
                                    $infoV1 = json_decode($http->Url($config['domain'] . '/classes/xui-api.php?action=infoV2&panel=' . $search['row'] . '&email=' . $service['remark'])->Method('GET')->Send(), true);
                                    ?>

                                    <b>آیدی (row) سرویس</b><code style="float: left;"><?php echo $service['row']; ?></code>
                                    <hr>
                                    <b>وضعیت سرویس</b>
                                    <p style="float: left; color: <?php echo ($service['status']) ? 'green' : 'red'; ?>;"> <?php echo ($service['status']) ? 'فعال' : 'غیرفعال'; ?>
                                    </p>
                                    <hr>
                                    <b>اسم کاربر</b><code style="float: left;"><?php echo bot('getChat', ['chat_id' => $service['from_id']])->result->first_name; ?></code>
                                    <hr>
                                    <b>یوزرنیم کاربر</b><code style="float: left;"><?php echo bot('getChat', ['chat_id' => $service['from_id']])->result->username ?? 'ندارد'; ?></code>
                                    <hr>
                                    <b>آیدی عددی</b><code style="float: left;"><?php echo $service['from_id']; ?></code>
                                    <hr>
                                    <b>نام سرویس</b><code style="float: left;"><?php echo $service['remark']; ?></code>
                                    <hr>
                                    <b>کد کاربری</b><code style="float: left;"><?php echo explode('-', $service['remark'])[1] ?? 'ERROR'; ?></code>
                                    <hr>
                                    <b>کد 6 رقمی</b><code style="float: left;"><?php echo $service['code']; ?></code>
                                    <hr>
                                    <b>نوع پلن</b><b style="float: left; color: green;"> <?php echo $service['type']; ?>
                                    </b>
                                    <hr>
                                    <b>لوکیشن</b><b style="float: left; color: green;"><?php echo $search['name']; ?>
                                    </b>
                                    <hr>
                                    <b>زمان</b><code style="float: left;"><?php echo getServiceExpiryDate($infoV1['expiryTime']); ?></b></code>
                                    <hr>
                                    <b>حجم</b><code style="float: left;"><?php echo $infoV1['totalGB'] / pow(1024, 3); ?> <b style="color: black;">گیگ</b></code>
                                    <hr>
                                    <b>تعداد کاربر (ip_limit)</b><code style="float: left;"><?php echo $service['ip_limit']; ?> <b style="color: black;">نفره</b></code>
                                    <hr>
                                    <b>قیمت</b><code style="float: left;"><?php echo number_format($service['price']); ?><b style="color: black;">تومان</b>
                                    </code>
                                    <hr>
                                    <b>تاریخ خرید</b><code style="float: left;"><?php echo convertNumber(jdate('H:i:s - Y/m/d', $service['buy_time'])); ?></code>
                                    <hr>
                                    <b>تاریخ انقضا</b><code style="float: left;"><?php echo convertNumber(jdate('H:i:s - Y/m/d', ($infoV1['expiryTime'] / 1000))); ?></code>
                                    <hr>
                                    <b>کانفیگ</b><textarea class="form-control" rows="5" style="direction: ltr;"><?php echo $service['config_link']; ?></textarea>
                                    <hr>

                                    <div class="row">
                                        <div class="d-grid gap-2">

                                            <form action="manage_services.php?from_id=<?php echo $_GET['from_id']; ?>"
                                                method="post">
                                                <input type="hidden" name="changeServiceStatus" value="changeServiceStatus">
                                                <input type="hidden" name="email" value="<?php echo $service['remark']; ?>">
                                                <input type="hidden" name="status"
                                                    value="<?php echo (($service['status']) ? 'false' : 'true'); ?>">
                                                <input type="hidden" name="panel" value="<?php echo $service['panel']; ?>">
                                                <input type="hidden" name="row" value="<?php echo $service['row']; ?>">
                                                <button type="submit" style="width: 100%;"
                                                    class="btn btn-<?php echo ($service['status']) ? 'danger' : 'success'; ?>">
                                                    <?php echo ($service['status']) ? 'غیرفعال کردن سرویس' : 'فعال کردن سرویس'; ?>
                                                </button>
                                            </form>

                                            <form action="manage_services.php?from_id=<?php echo $_GET['from_id']; ?>"
                                                method="post">
                                                <input type="hidden" name="deleteService" value="deleteService">
                                                <input type="hidden" name="email" value="<?php echo $service['remark']; ?>">
                                                <input type="hidden" name="panel" value="<?php echo $service['panel']; ?>">
                                                <input type="hidden" name="row" value="<?php echo $service['row']; ?>">
                                                <button type="submit" style="width: 100%;" class="btn btn-secondary">حذف
                                                    سرویس (به صورت کلی)</button>
                                            </form>

                                            <button type="button" class="btn btn-warning" data-toggle="modal"
                                                data-target="#getQrCode-<?php echo $service['code']; ?>">دریافت
                                                QrCode</button>
                                            <button type="button" class="btn btn-primary" data-toggle="modal"
                                                data-target="#sendMessage-<?php echo $service['from_id']; ?>">ارسال پیام به
                                                کاربر</button>

                                            <div class="modal fade" id="getQrCode-<?php echo $service['code']; ?>"
                                                tabindex="-1" role="dialog" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">
                                                                <code><?php echo $service['remark']; ?></code>
                                                            </h5>
                                                            <button type="button" class="close btn btn-danger"
                                                                data-dismiss="modal" aria-label="Close">&times;</button>
                                                        </div>
                                                        <div class="modal-body" style="text-align: center;">
                                                            <img src="<?php echo 'https://api.qrserver.com/v1/create-qr-code/?data=' . urlencode($service['config_link']) . '&size=800x800' ?>"
                                                                alt="qr-code" height="236px" width="236px">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="modal fade" id="sendMessage-<?php echo $service['from_id']; ?>"
                                                tabindex="-1" role="dialog"
                                                aria-labelledby="sendMessageModalLabel-<?php echo $service['from_id']; ?>"
                                                aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title"
                                                                id="sendMessageModalLabel-<?php echo $service['from_id']; ?>">
                                                                ارسال پیام به کاربر
                                                                <code><?php echo $service['from_id']; ?></code>
                                                            </h5>
                                                            <button type="button" class="close btn btn-danger"
                                                                data-dismiss="modal" aria-label="Close">&times;</button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <form
                                                                action="manage_services.php?from_id=<?php echo $_GET['from_id']; ?>"
                                                                method="post">
                                                                <input type="hidden" name="sendMessage" value="sendMessage">

                                                                <div class="form-group">
                                                                    <label for="userid"
                                                                        class="col-form-label">کاربر:</label>
                                                                    <input type="number" class="form-control" id="userid"
                                                                        name="userid"
                                                                        placeholder="آیدی عددی فرد را در این قسمت وارد کنید ..."
                                                                        value="<?php echo $service['from_id']; ?>" required>
                                                                </div>

                                                                <div class="form-group">
                                                                    <label for="message"
                                                                        class="col-form-label">پیام:</label>
                                                                    <textarea class="form-control" id="message"
                                                                        name="message"
                                                                        placeholder="پیام خود را در این قسمت وارد کنید ..."
                                                                        required></textarea>
                                                                </div>

                                                                <div class="modal-footer" dir="ltr">
                                                                    <button type="button" class="btn btn-secondary"
                                                                        data-dismiss="modal">لغو</button>
                                                                    <button type="submit" class="btn btn-primary">ارسال
                                                                        پیام</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        <?php } ?>

                        <div class="row gy-2 gx-2 mb-2 align-items-center">
                            <div class="col-12">
                                <div class="d-grid">
                                    <nav aria-label="..." dir="ltr">
                                        <ul class="pagination">
                                            <?php if ($_GET['page'] > 1) { ?>
                                                <li class="page-item">
                                                    <a class="page-link"
                                                        href="manage_services.php?from_id=<?php echo $_GET['from_id']; ?><?php echo (isset($_GET['filter']) ? ('&filter=' . $_GET['filter']) : ''); ?>&page=<?php echo $menpage ?>">Previous</a>
                                                </li>
                                            <?php } ?>
                                            <?php if ($_GET['page'] > 1) { ?>
                                                <li class="page-item"><a class="page-link"
                                                        href="manage_services.php?from_id=<?php echo $_GET['from_id']; ?><?php echo (isset($_GET['filter']) ? ('&filter=' . $_GET['filter']) : ''); ?>&page=<?php echo $_GET['page'] - 1; ?>">
                                                        <?php echo $_GET['page'] - 1; ?>
                                                    </a></li>
                                            <?php } ?>
                                            <li class="page-item active">
                                                <a class="page-link"
                                                    href="manage_services.php?from_id=<?php echo $_GET['from_id']; ?><?php echo (isset($_GET['filter']) ? ('&filter=' . $_GET['filter']) : ''); ?>&page=<?php echo $_GET['page']; ?>">
                                                    <?php echo $_GET['page']; ?>
                                                </a>
                                            </li>
                                            <li class="page-item"><a class="page-link"
                                                    href="manage_services.php?from_id=<?php echo $_GET['from_id']; ?><?php echo (isset($_GET['filter']) ? ('&filter=' . $_GET['filter']) : ''); ?>&page=<?php echo $_GET['page'] + 1; ?>">
                                                    <?php echo $_GET['page'] + 1; ?>
                                                </a></li>
                                            <?php if ($page * 7 < $sql->query("SELECT * FROM `payment_factors` ORDER BY `row` DESC")->num_rows) { ?>
                                                <li class="page-item">
                                                    <a class="page-link"
                                                        href="manage_services.php?from_id=<?php echo $_GET['from_id']; ?><?php echo (isset($_GET['filter']) ? ('&filter=' . $_GET['filter']) : ''); ?>&page=<?php echo $addpage ?>">Next</a>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha2/js/bootstrap.bundle.min.js"></script>
</body>

</html>