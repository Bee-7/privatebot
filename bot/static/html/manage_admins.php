<?php

include_once '../../config.php';
include_once '../../includes/functions.php';
include_once '../../classes/request.php';

$http = new HTTPRequest();

$alert_text = ''; // DEFAULT NULL
$alert_type = 'info'; // DEFAULT info (blud)

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if ($_GET['from_id'] == $config['admin'] or in_array($_GET['from_id'], $config['admins'])) {
        $admins = $sql->query("SELECT * FROM `admins`");
    } else {
        exit();
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['addAdmin']) and $_POST['addAdmin'] == 'addAdmin') {
        if ($sql->query("SELECT * FROM `users` WHERE `from_id` = {$_POST['from_id']}")->num_rows > 0) {
            $sql->query("INSERT INTO `admins` (`from_id`, `is_accept_fish`, `status`) VALUES ({$_POST['from_id']}, {$_POST['is_accept_fish']}, 1)");
            $alert_text = 'ادمین شما (<code>' . $_POST['from_id'] . '</code>) با موفقیت به ربات اضافه شد.';
            $alert_type = 'success';
        } else {
            $alert_text = ('کاربر (<code>' . $_POST['from_id'] . '</code>) عضو ربات نیست.');
            $alert_type = 'danger';
        }
    } elseif (isset($_POST['deleteAdmin']) and $_POST['deleteAdmin'] == 'deleteAdmin') {
        if ($sql->query("SELECT * FROM `admins` WHERE `from_id` = {$_POST['from_id']}")->num_rows > 0) {
            $sql->query("DELETE FROM `admins` WHERE `from_id` = {$_POST['from_id']}");
            $alert_text = 'ادمین (<code>' . $_POST['from_id'] . '</code>) با موفقیت حذف شد.';
            $alert_type = 'success';
        } else {
            $alert_text = 'عملیات حذف ادمین با خطا مواجه شد.';
            $alert_type = 'danger';
        }
    } elseif (isset($_POST['changeAdminStatus']) and $_POST['changeAdminStatus'] == 'changeAdminStatus') {
        if ($sql->query("SELECT * FROM `admins` WHERE `from_id` = {$_POST['from_id']}")->num_rows > 0) {
            $sql->query("UPDATE `admins` SET `status` = {$_POST['status']} WHERE `from_id` = {$_POST['from_id']}");
            $alert_text = 'تغییرات با موفقیت انجام شد.';
            $alert_type = 'success';
        } else {
            $alert_text = 'عملیات تغییرات ادمین با خطا مواجه شد.';
            $alert_type = 'danger';
        }
    } elseif (isset($_POST['changeAdminAccepter']) and $_POST['changeAdminAccepter'] == 'changeAdminAccepter') {
        if ($sql->query("SELECT * FROM `admins` WHERE `from_id` = {$_POST['from_id']}")->num_rows > 0) {
            $sql->query("UPDATE `admins` SET `is_accept_fish` = {$_POST['is_accept_fish']} WHERE `from_id` = {$_POST['from_id']}");
            $alert_text = 'تغییرات با موفقیت انجام شد.';
            $alert_type = 'success';
        } else {
            $alert_text = 'عملیات تغییرات ادمین با خطا مواجه شد.';
            $alert_type = 'danger';
        }
    }

    $admins = $sql->query("SELECT * FROM `admins`");
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
    <title>مدیریت ادمین ها</title>
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

        <div class="row mt-1 justify-content-around">
            <div class="row pt-4 gy-2 gx-2 mb-2 align-items-center">
                <div class="col-12">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <div class="d-grid">
                                <a href="manage_admins.php?from_id=<?php echo $_GET['from_id']; ?>"><button
                                        type="button" class="btn btn-info"
                                        style="width: 100%; margin-top: -20px;">آپدیت</button></a>
                            </div>
                        </div>
                    </div>

                    <?php if ($alert_text != '') { ?>
                        <div class="alert alert-<?php echo $alert_type; ?> text-center" style="padding: 0.5rem !important;">
                            <?php echo $alert_text; ?>
                        </div>
                    <?php } else { ?>
                        <div class="alert alert-info text-center" style="padding: 0.5rem !important;">لیست ادمین های ربات به
                            شرح زیر است.</div>
                    <?php } ?>

                    <div class="alert alert-warning text-center" style="padding: 0.5rem !important;">تعداد کل ادمین ها :
                        <code><?php echo $admins->num_rows ?? 0; ?></code> عدد</div>

                    <button type="button" class="btn btn-primary" style="width: 100%;" data-toggle="modal"
                        data-target="#addAdmin">افزودن ادمین جدید</button>

                    <div class="modal fade" id="addAdmin" tabindex="-1" role="dialog"
                        aria-labelledby="addAdminModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">

                                <div class="modal-header">
                                    <h5 class="modal-title" id="addAdminModalLabel">افزودن ادمین جدید</h5>
                                    <button type="button" class="close btn btn-danger" data-dismiss="modal"
                                        aria-label="Close">&times;</button>
                                </div>

                                <div class="modal-body">
                                    <form action="manage_admins.php?from_id=<?php echo $_GET['from_id']; ?>"
                                        method="post">
                                        <input type="hidden" name="addAdmin" value="addAdmin">

                                        <div class="form-group mb-3">
                                            <label for="from_id" class="col-form-label">آیدی عددی کاربر:</label>
                                            <input type="number" class="form-control" id="from_id" name="from_id"
                                                placeholder="آیدی عددی کاربر را وارد کنید ..." required>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="is_accept_fish" class="col-form-label">تنظیم به عنوان تایید
                                                کننده رسید:</label>
                                            <select name="is_accept_fish" class="form-select" id="is_accept_fish"
                                                required>
                                                <option value="1">فعال</option>
                                                <option value="0" selected>غیرفعال</option>
                                            </select>
                                        </div>

                                        <div class="modal-footer" dir="ltr">
                                            <button type="button" class="btn btn-secondary"
                                                data-dismiss="modal">لغو</button>
                                            <button type="submit" class="btn btn-primary">افزودن</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="col-12">
                <hr style="margin: 1rem 0 0;">
            </div>

            <div class="tab-content mt-3" id="pills-tabContent">

                <div class="tab-pane fade show active" role="tabpanel" tabindex="0">

                    <div class="row gx-2 gy-0 justify-content-around">

                        <?php while ($admin = $admins->fetch_assoc()) { ?>
                            <div class="card" style="width: 25rem; margin-bottom: 20px;">
                                <div class="card-body" id="get-<?php echo $admin['row']; ?>">
                                    <b>نام ادمین</b>
                                    <p style="float: left;">
                                        <?php echo bot('getChat', ['chat_id' => $admin['from_id']])->result->first_name; ?>
                                    </p>
                                    <hr>
                                    <b>یوزرنیم ادمین</b>
                                    <p style="float: left;">
                                        <?php echo bot('getChat', ['chat_id' => $admin['from_id']])->result->username ?? 'ندارد'; ?>
                                    </p>
                                    <hr>
                                    <b>آیدی عددی</b>
                                    <code style="float: left;"><?php echo $admin['from_id']; ?></code>
                                    <hr>
                                    <b>وضعیت</b>
                                    <p style="float: left; color: <?php echo ($admin['status']) ? 'green' : 'red'; ?>;">
                                        <?php echo ($admin['status']) ? 'فعال' : 'غیرفعال'; ?>
                                    </p>
                                    <hr>
                                    <b>وضعیت تایید رسید</b>
                                    <p
                                        style="float: left; color: <?php echo ($admin['is_accept_fish']) ? 'green' : 'red'; ?>;">
                                        <?php echo ($admin['is_accept_fish']) ? 'فعال' : 'غیرفعال'; ?>
                                    </p>
                                    <hr>

                                    <div class="row mb-2">
                                        <div class="d-grid gap-2">

                                            <form action="manage_admins.php?from_id=<?php echo $_GET['from_id']; ?>"
                                                method="post">
                                                <input type="hidden" name="deleteAdmin" value="deleteAdmin">
                                                <input type="hidden" name="from_id"
                                                    value="<?php echo $admin['from_id']; ?>">
                                                <button type="submit" style="width: 100%;" class="btn btn-secondary">حذف
                                                    ادمین</button>
                                            </form>

                                        </div>
                                    </div>

                                    <div class="row mb-2">
                                        <div class="d-grid gap-2">

                                            <form action="manage_admins.php?from_id=<?php echo $_GET['from_id']; ?>"
                                                method="post">
                                                <input type="hidden" name="changeAdminAccepter" value="changeAdminAccepter">
                                                <input type="hidden" name="from_id"
                                                    value="<?php echo $admin['from_id']; ?>">
                                                <input type="hidden" name="is_accept_fish"
                                                    value="<?php echo ($admin['is_accept_fish']) ? 0 : 1; ?>">
                                                <button type="submit" style="width: 100%;"
                                                    class="btn btn-<?php echo ($admin['is_accept_fish']) ? 'danger' : 'success'; ?>">
                                                    <?php echo ($admin['is_accept_fish']) ? 'غیرفعال کردن تایید رسید' : 'فعال کردن تایید رسید'; ?>
                                                </button>
                                            </form>

                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="d-grid gap-2">

                                            <form action="manage_admins.php?from_id=<?php echo $_GET['from_id']; ?>"
                                                method="post">
                                                <input type="hidden" name="changeAdminStatus" value="changeAdminStatus">
                                                <input type="hidden" name="from_id"
                                                    value="<?php echo $admin['from_id']; ?>">
                                                <input type="hidden" name="status"
                                                    value="<?php echo ($admin['status']) ? 0 : 1; ?>">
                                                <button type="submit" style="width: 100%;"
                                                    class="btn btn-<?php echo ($admin['status']) ? 'danger' : 'success'; ?>">
                                                    <?php echo ($admin['status']) ? 'غیرفعال کردن ادمین' : 'فعال کردن ادمین'; ?>
                                                </button>
                                            </form>

                                        </div>
                                    </div>

                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha2/js/bootstrap.bundle.min.js"></script>
</body>

</html>