<?php

include_once '../../config.php';
include_once '../../includes/functions.php';
include_once '../../classes/request.php';

$http = new HTTPRequest();

$alert_text = ''; // DEFAULT NULL
$alert_type = 'info'; // DEFAULT info (blud)

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if ($_GET['from_id'] == $config['admin'] or in_array($_GET['from_id'], $config['admins'])) {
        if (isset($_GET['panel'])) {
            $count = $sql->query("SELECT * FROM `panels` WHERE `row` = {$_GET['panel']}")->num_rows ?? 0;
            $get_panel = $sql->query("SELECT * FROM `panels` WHERE `row` = {$_GET['panel']}")->fetch_assoc();
        } else {
            return;
        }
    } else {
        return;
    }
} 

elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['deletePanel']) and $_POST['deletePanel'] == 'deletePanel') {
        if ($sql->query("SELECT * FROM `panels` WHERE `row` = {$_POST['row']}")->num_rows > 0) {
            $sql->query("DELETE FROM `panels` WHERE `row` = {$_POST['row']}");
            $alert_text = 'پنل شما با موفقیت حذف شد.';
            $alert_type = 'success';
        } else {
            $alert_text = 'عملیات حذف پنل با خطا مواجه شد.';
            $alert_type = 'danger';
        }
    }

    if (isset($_POST['changeStatusPanel']) and $_POST['changeStatusPanel'] == 'changeStatusPanel') {
        if ($sql->query("SELECT * FROM `panels` WHERE `row` = {$_POST['row']}")->num_rows > 0) {
            $sql->query("UPDATE `panels` SET `status` = {$_POST['status']} WHERE `row` = {$_POST['row']}");
            $alert_text = 'عملیات تغییر وضعیت پنل شما با موفقیت انجام شد.';
            $alert_type = 'success';
        } else {
            $alert_text = 'عملیات تغییر وضعیت پنل با خطا مواجه شد.';
            $alert_type = 'danger';
        }
    }
    
    elseif (isset($_POST['editPanel']) and $_POST['editPanel'] == 'editPanel') {
        $sql->query("UPDATE `panels` SET `name` = '{$_POST['panel_name']}', `status` = {$_POST['status']}, `domain` = '{$_POST['domain']}', `status` = {$_POST['status']}, `inbound_id` = {$_POST['inbound_id']}, `buy_limit` = {$_POST['buy_limit']}, `prefix` = '{$_POST['prefix']}' WHERE `row` = {$_POST['row']}");
        $alert_text = 'تغییرات با موفقیت انجام شد.';
        $alert_type = 'success';
    }

    $count = $sql->query("SELECT * FROM `panels` WHERE `row` = {$_GET['panel']}")->num_rows ?? 0;
    $get_panel = $sql->query("SELECT * FROM `panels` WHERE `row` = {$_GET['panel']}")->fetch_assoc();
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
    <title>مدیریت پنل [ <?php echo $get_panel['name']; ?> ]</title>
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
                    <div class="row">
                        <div class="col-12 mb-3">
                            <div class="d-grid">
                                <a href="manage_panel.php?from_id=<?php echo $_GET['from_id']; ?>&panel=<?php echo $_GET['panel']; ?>"><button type="button" class="btn btn-info" style="width: 100%; margin-top: -20px;">آپدیت</button></a>
                            </div>
                        </div>
                    </div>

                    <?php if ($alert_text != '') { ?>
                        <div class="alert alert-<?php echo $alert_type; ?> text-center" style="padding: 0.5rem !important;"><?php echo $alert_text; ?></div>
                    <?php } else { ?>
                        <?php if ($count > 0) {?>
                            <div class="alert alert-info text-center" style="padding: 0.5rem !important;">اطلاعات پنل [ <b><?php echo $get_panel['name']; ?></b> ] به شرح زیر است.</div>
                        <?php } else { ?>
                            <div class="alert alert-danger text-center" style="padding: 0.5rem !important;">پنل یافت نشد.</div>
                        <?php }?>
                    <?php } ?>

                </div>
            </div>

            <div class="col-12">
                <hr style="margin: 0.2rem 0 0;">
            </div>

            <div class="tab-content mt-3" id="pills-tabContent">

                <div class="tab-pane fade show active" role="tabpanel" tabindex="0">

                    <div class="row gx-2 gy-0 justify-content-around">

                        <?php if ($count > 0) {?>
                        <div class="card" style="width: 25rem; margin-bottom: 20px;">
                            <div class="card-body" id="get-<?php echo $get_panel['row']; ?>">
                                <b>آیدی (row) پنل</b>
                                <code style="float: left;"><?php echo $get_panel['row']; ?></code>
                                <hr>
                                <b>وضعیت پنل</b>
                                <p style="float: left; color: <?php echo ($get_panel['status']) ? 'green' : 'red'; ?>;"><?php echo ($get_panel['status']) ? 'فعال' : 'غیرفعال'; ?></p>
                                <hr>
                                <b>اسم پنل</b>
                                <p style="float: left;"><?php echo $get_panel['name']; ?></p>
                                <hr>
                                <b>کد پیگیری 6 رقمی</b>
                                <code style="float: left;"><?php echo $get_panel['code']; ?></code>
                                <hr>
                                <b>آدرس</b>
                                <a style="float: left; text-decoration: none;" href="<?php echo $get_panel['address']; ?>"><?php echo $get_panel['address']; ?></a>
                                <hr>
                                <b>دامنه جایگزینی</b>
                                <?php 
                                if ($get_panel['domain'] == '/blank' or is_null($get_panel['domain'])) {
                                ?>
                                    <p style="float: left;">تنظیم نشده</p>
                                <?php
                                } else {
                                ?>
                                <a style="float: left; text-decoration: none;" href="<?php echo $get_panel['domain']; ?>"><?php echo $get_panel['domain']; ?></a>
                                <?php
                                }
                                ?>
                                <hr>
                                <b>آیپی</b>
                                <code style="float: left;"><?php echo parse_url($get_panel['address'])['host']; ?></code>
                                <hr>
                                <b>پورت</b>
                                <code style="float: left;"><?php echo parse_url($get_panel['address'])['port']; ?></code>
                                <hr>
                                <b>وضعیت پروتکل SSL</b>
                                <p style="float: left; color: <?php echo (parse_url($get_panel['address'])['scheme'] == 'http') ? 'red' : 'green'; ?>"><?php echo (parse_url($get_panel['address'])['scheme'] == 'http') ? 'غیرفعال' : 'فعال'; ?></p>
                                <hr>
                                <b>یوزرنیم</b>
                                <code style="float: left;"><?php echo $get_panel['username']; ?></code>
                                <hr>
                                <b>پسورد</b>
                                <code style="float: left;"><?php echo $get_panel['password']; ?></code>
                                <hr>
                                <b>اینباند آیدی تنظیم شده</b>
                                <code style="float: left;"><?php echo $get_panel['inbound_id']; ?></code>
                                <hr>
                                <b>تعداد محدودیت باقیمانده این پنل</b>
                                <code style="float: left;"><?php echo $get_panel['buy_limit']; ?></code>
                                <hr>
                                <b>پیشوند تنظیم شده</b>
                                <p style="float: left; color: red"><?php echo $get_panel['prefix']; ?></p>
                                <hr>
                                <b>تاریخ اضافه شدن</b>
                                <code style="float: left; color: blue;"><?php echo convertNumber(jdate('H-i-s - Y/m/d', $get_panel['added_time'])); ?></code>
                                <hr>

                                <div class="row">
                                    <div class="d-grid gap-2">

                                        <form action="manage_panel.php?from_id=<?php echo $_GET['from_id']; ?>&panel=<?php echo $_GET['panel']; ?>" method="post">
                                            <input type="hidden" name="changeStatusPanel" value="changeStatusPanel">
                                            <input type="hidden" name="row" value="<?php echo $get_panel['row']; ?>">
                                            <input type="hidden" name="status" value="<?php echo ($get_panel['status']) ? 0 : 1; ?>">
                                            <button type="submit" style="width: 100%;" class="btn btn-<?php echo ($get_panel['status']) ? 'danger' : 'success'; ?>"><?php echo ($get_panel['status']) ? 'غیرفعال کردن پنل' : 'فعال کردن پنل'; ?></button>
                                        </form>

                                        <form action="manage_panel.php?from_id=<?php echo $_GET['from_id']; ?>&panel=<?php echo $_GET['panel']; ?>" method="post">
                                            <input type="hidden" name="deletePanel" value="deletePanel">
                                            <input type="hidden" name="row" value="<?php echo $get_panel['row']; ?>">
                                            <button type="submit" style="width: 100%;" class="btn btn-secondary">حذف پنل</button>
                                        </form>

                                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#editPanel-<?php echo $get_panel['row']; ?>">ویرایش پنل</button>

                                        <div class="modal fade" id="editPanel-<?php echo $get_panel['row']; ?>" tabindex="-1" role="dialog" aria-labelledby="editPanelModalLabel-<?php echo $get_panel['row']; ?>" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="editPlanModalLabel-<?php echo $get_panel['row']; ?>">ویرایش پنل [ <b><?php echo $get_panel['name']; ?></b> ]</h5>
                                                        <button type="button" class="close btn btn-danger" data-dismiss="modal" aria-label="Close">&times;</button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="manage_panel.php?from_id=<?php echo $_GET['from_id']; ?>&panel=<?php echo $_GET['panel']; ?>" method="post">
                                                            <input type="hidden" name="editPanel" value="editPanel">
                                                            <input type="hidden" name="row" value="<?php echo $get_panel['row']; ?>">

                                                            <div class="form-group mb-2">
                                                                <label for="panel_name" class="col-form-label">اسم پنل:</label>
                                                                <input type="text" class="form-control" id="panel_name" name="panel_name" placeholder="اسم پنل را وارد کنید ..." value="<?php echo $get_panel['name']; ?>" required>
                                                            </div>

                                                            <div class="form-group mb-2">
                                                                <label for="address" class="col-form-label">آدرس لاگین:</label>
                                                                <input type="text" class="form-control" id="address" name="address" placeholder="آدرس لاگین پنل را وارد کنید ..." value="<?php echo $get_panel['address']; ?>" disabled>
                                                            </div>

                                                            <div class="form-group mb-2">
                                                                <label for="domain" class="col-form-label">دامنه جایگزینی:</label>
                                                                <input type="text" class="form-control" id="domain" name="domain" placeholder="دامنه جایگزینی را وارد کنید ..." value="<?php echo $get_panel['domain'];  ?>">
                                                            </div>

                                                            <div class="form-group mb-2">
                                                                <label for="username" class="col-form-label">یوزرنیم:</label>
                                                                <input type="text" class="form-control" id="username" name="username" placeholder="یوزرنیم را وارد کنید ..." value="<?php echo $get_panel['username']; ?>" disabled>
                                                            </div>

                                                            <div class="form-group mb-2">
                                                                <label for="password" class="col-form-label">پسورد:</label>
                                                                <input type="text" class="form-control" id="password" name="password" placeholder="پسورد را وارد کنید ..." value="<?php echo $get_panel['password']; ?>" disabled>
                                                            </div>

                                                            <div class="form-group mb-2">
                                                                <label for="inbound_id" class="col-form-label">اینباند آیدی:</label>
                                                                <input type="number" class="form-control" id="inbound_id" name="inbound_id" placeholder="اینباند آیدی را وارد کنید ..." value="<?php echo $get_panel['inbound_id']; ?>" min="1" required>
                                                            </div>

                                                            <div class="form-group mb-2">
                                                                <label for="buy_limit" class="col-form-label">محدودیت خرید از این پنل:</label>
                                                                <input type="number" class="form-control" id="buy_limit" name="buy_limit" placeholder="محدودیت خرید این پنل را وارد کنید ..." value="<?php echo $get_panel['buy_limit']; ?>" min="1" required>
                                                            </div>

                                                            <div class="form-group mb-2">
                                                                <label for="prefix" class="col-form-label">پیشوند:</label>
                                                                <input type="text" class="form-control" id="prefix" name="prefix" placeholder="پیشوند پنل را وارد کنید ..." value="<?php echo $get_panel['prefix']; ?>" required>
                                                            </div>

                                                            <div class="form-group mb-3">
                                                                <label for="status" class="col-form-label">وضعیت نمایش:</label>
                                                                <select name="status" class="form-select" id="status" required>
                                                                    <option value="1" <?php echo ($get_panel['status']) ? 'unselected' : 'selected'; ?>>فعال</option>
                                                                    <option value="0" <?php echo ($get_panel['status']) ? 'unselected' : 'selected'; ?>>غیرفعال</option>
                                                                </select>
                                                            </div>

                                                            <div class="modal-footer" dir="ltr">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">لغو</button>
                                                                <button type="submit" class="btn btn-primary">ثبت تغییرات</button>
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
                        <?php }?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha2/js/bootstrap.bundle.min.js"></script>
</body>

</html>