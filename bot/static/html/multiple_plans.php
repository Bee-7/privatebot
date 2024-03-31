<?php

include_once '../../config.php';
include_once '../../includes/functions.php';
include_once '../../includes/keyboards.php';
include_once '../../classes/request.php';

$http = new HTTPRequest();

$alert_text = ''; // DEFAULT NULL
$alert_type = 'info'; // DEFAULT info (blud)

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['update']) and $_GET['update'] == 'true') {
        $multiple_plans = $sql->query("SELECT * FROM `multiple_plan` ORDER BY `row` DESC");
    } else {
        if ($_GET['from_id'] == $config['admin'] or in_array($_GET['from_id'], $config['admins'])) {
            $multiple_plans = $sql->query("SELECT * FROM `multiple_plan` ORDER BY `row` DESC");
        } else {
            exit();
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['addPlan']) and $_POST['addPlan'] == 'addPlan') {
        $code = rand(11111, 99999);
        $sql->query("INSERT INTO `multiple_plan` (`name`, `limit`, `date`, `ip_limit`, `price`, `code`, `type`, `status`) VALUES ('{$_POST['plan_name']}', {$_POST['limit']}, {$_POST['date']}, {$_POST['ip_limit']}, {$_POST['price']}, $code, {$_POST['plan_type']}, {$_POST['status']})");
        $alert_text = 'پلن (<code>' . $_POST['plan_name'] . '</code>) با کد پیگیری (<code>' . $code . '</code>) با موفقیت به ربات اضافه شد.';
        $alert_type = 'success';
    } elseif (isset($_POST['deletePlan']) and $_POST['deletePlan'] == 'deletePlan') {
        if ($sql->query("SELECT * FROM `multiple_plan` WHERE `row` = {$_POST['row']}")->num_rows > 0) {
            $sql->query("DELETE FROM `multiple_plan` WHERE `row` = {$_POST['row']}");
            $alert_text = 'پلن انتخابی شما با موفقیت حذف شد.';
            $alert_type = 'success';
        } else {
            $alert_text = 'عملیات حذف پلن با خطا مواجه شد.';
            $alert_type = 'danger';
        }
    } elseif (isset($_POST['editPlan']) and $_POST['editPlan'] == 'editPlan') {
        $sql->query("UPDATE `multiple_plan` SET `name` = '{$_POST['plan_name']}', `limit` = {$_POST['limit']}, `date` = {$_POST['date']}, `ip_limit` = {$_POST['ip_limit']}, `price` = {$_POST['price']}, `type` = {$_POST['plan_type']}, `status` = {$_POST['status']} WHERE `row` = {$_POST['row']}");
        $alert_text = 'تغییرات با موفقیت انجام شد.';
        $alert_type = 'success';
    } elseif (isset($_POST['changeStatusPlan']) and $_POST['changeStatusPlan'] == 'changeStatusPlan') {
        $sql->query("UPDATE `multiple_plan` SET `status` = {$_POST['status']} WHERE `row` = {$_POST['row']}");
        $alert_text = 'تغییرات با موفقیت انجام شد.';
        $alert_type = 'success';
    }

    $multiple_plans = $sql->query("SELECT * FROM `multiple_plan` ORDER BY `row` DESC");
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
    <title>پلن های بچه</title>
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

        <div class="row justify-content-around">

            <div class="row pt-4 gy-2 gx-2 mb-2 align-items-center">
                <div class="col-12">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <div class="d-grid">
                                <a href="multiple_plans.php?from_id=<?php echo $_GET['from_id']; ?>"><button type="button" class="btn btn-info" style="width: 100%; margin-top: -20px;">آپدیت</button></a>
                            </div>
                        </div>
                    </div>

                    <?php if ($alert_text != '') { ?>
                        <div class="alert alert-<?php echo $alert_type; ?> text-center" style="padding: 0.5rem !important;"><?php echo $alert_text; ?></div>
                    <?php } else { ?>
                        <div class="alert alert-info text-center" style="padding: 0.5rem !important;">لیست پلن های بچه به شرح زیر است..</div>
                    <?php } ?>

                    <div class="alert alert-warning text-center" style="padding: 0.5rem !important;">تعداد کل پلن ها : <code><?php echo $multiple_plans->num_rows ?? 0; ?></code> عدد</div>

                    <button type="button" class="btn btn-primary" style="width: 100%;" data-toggle="modal" data-target="#addPlan">افزودن پلن جدید</button>

                    <div class="modal fade" id="addPlan" tabindex="-1" role="dialog" aria-labelledby="addPlanModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addPlanModalLabel">افزودن پلن بچه جدید</h5>
                                    <button type="button" class="close btn btn-danger" data-dismiss="modal" aria-label="Close">&times;</button>
                                </div>
                                <div class="modal-body">
                                    <form action="multiple_plans.php?from_id=<?php echo $_GET['from_id']; ?>" method="post">
                                        <input type="hidden" name="addPlan" value="addPlan">

                                        <div class="form-group mb-3">
                                            <label for="plan_name" class="col-form-label">اسم پلن:</label>
                                            <input type="text" class="form-control" id="plan_name" name="plan_name" placeholder="اسم پلن را وارد کنید ..." required>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="limit" class="col-form-label">حجم (بر اساس GB):</label>
                                            <input type="number" class="form-control" id="limit" name="limit" placeholder="حجم پلن را بر اساس GB وارد کنید ..." required>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="date" class="col-form-label">زمان (بر اساس روز):</label>
                                            <input type="number" class="form-control" id="date" name="date" placeholder="زمان پلن را بر اساس روز وارد کنید ..." required>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="ip_limit" class="col-form-label">تعداد کاربر (ip_limit):</label>
                                            <input type="number" class="form-control" id="ip_limit" name="ip_limit" placeholder="تعداد کاربر (ip_limit) پلن  وارد کنید ..." required>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="price" class="col-form-label">قیمت (بر اساس تومان):</label>
                                            <input type="number" class="form-control" id="price" name="price" placeholder="قیمت پلن را بر اساس تومان وارد کنید ..." required>
                                        </div>


                                        <div class="form-group mb-3">
                                            <label for="plan_type" class="col-form-label">زیرمجموعه کدام پلن مادر شود:</label>
                                            <select name="plan_type" class="form-select" id="plan_type" required>
                                                <?php
                                                $single_plans = $sql->query("SELECT * FROM `single_plan`");
                                                while ($plan = $single_plans->fetch_assoc()) {
                                                ?>
                                                    <option value="<?php echo $plan['row']; ?>"><?php echo $plan['name']; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="status" class="col-form-label">وضعیت نمایش:</label>
                                            <select name="status" class="form-select" id="status" required>
                                                <option value="1" selected>فعال</option>
                                                <option value="0">غیرفعال</option>
                                            </select>
                                        </div>

                                        <div class="modal-footer" dir="ltr">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">لغو</button>
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

                        <?php while ($item = $multiple_plans->fetch_assoc()) { ?>
                            <div class="card" style="width: 25rem; margin-bottom: 20px;">
                                <div class="card-body" id="get-<?php echo $item['row']; ?>">
                                    <b>آیدی (row) پلن</b>
                                    <code style="float: left;"><?php echo $item['row']; ?></code>
                                    <hr>
                                    <b>وضعیت پلن</b>
                                    <p style="float: left; color: <?php echo ($item['status']) ? 'green' : 'red'; ?>;"><?php echo ($item['status']) ? 'فعال' : 'غیرفعال'; ?></p>
                                    <hr>
                                    <b>کد پیگیری</b>
                                    <code style="float: left;"><?php echo $item['code'] ?></code>
                                    <hr>
                                    <b>اسم پلن</b>
                                    <p style="float: left;"><?php echo $item['name']; ?></p>
                                    <hr>
                                    <b>حجم</b>
                                    <code style="float: left;"><?php echo number_format($item['limit']) ?> <b class="text-black">گیگابایت</b></code>
                                    <hr>
                                    <b>زمان</b>
                                    <code style="float: left;"><?php echo number_format($item['date']) ?> <b class="text-black">روزه</b></code>
                                    <hr>
                                    <b>تعداد کاربر (ip_limit)</b>
                                    <code style="float: left;"><?php echo number_format($item['ip_limit']) ?> <b class="text-black">کاربره</b></code>
                                    <hr>
                                    <b>قیمت</b>
                                    <code style="float: left;"><?php echo number_format($item['price']) ?> <b class="text-black">تومان</b></code>
                                    <hr>
                                    <b>متصل شده به پلن مادر</b>
                                    <p style="float: left;"><?php echo $sql->query("SELECT * FROM `single_plan` WHERE `row` = {$item['type']}")->fetch_assoc()['name']; ?></p>
                                    <hr>

                                    <div class="row">
                                        <div class="d-grid gap-2">

                                            <form action="multiple_plans.php?from_id=<?php echo $_GET['from_id']; ?>" method="post">
                                                <input type="hidden" name="changeStatusPlan" value="changeStatusPlan">
                                                <input type="hidden" name="row" value="<?php echo $item['row']; ?>">
                                                <input type="hidden" name="status" value="<?php echo ($item['status']) ? 0 : 1; ?>">
                                                <button type="submit" style="width: 100%;" class="btn btn-<?php echo ($item['status']) ? 'danger' : 'success'; ?>"><?php echo ($item['status']) ? 'غیرفعال کردن پلن' : 'فعال کردن پلن'; ?></button>
                                            </form>

                                            <form action="multiple_plans.php?from_id=<?php echo $_GET['from_id']; ?>" method="post">
                                                <input type="hidden" name="deletePlan" value="deletePlan">
                                                <input type="hidden" name="row" value="<?php echo $item['row']; ?>">
                                                <button type="submit" style="width: 100%;" class="btn btn-secondary">حذف پلن</button>
                                            </form>

                                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#editPlan-<?php echo $item['row']; ?>">ویرایش پلن</button>

                                            <div class="modal fade" id="editPlan-<?php echo $item['row']; ?>" tabindex="-1" role="dialog" aria-labelledby="editPlanModalLabel-<?php echo $item['row']; ?>" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">

                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="editPlanModalLabel-<?php echo $item['row']; ?>">ویرایش پلن بچه [ <b><?php echo $item['name']; ?></b> ]</h5>
                                                            <button type="button" class="close btn btn-danger" data-dismiss="modal" aria-label="Close">&times;</button>
                                                        </div>

                                                        <div class="modal-body">
                                                            <form action="multiple_plans.php?from_id=<?php echo $_GET['from_id']; ?>" method="post">
                                                                <input type="hidden" name="editPlan" value="editPlan">
                                                                <input type="hidden" name="row" value="<?php echo $item['row']; ?>">

                                                                <div class="form-group mb-3">
                                                                    <label for="plan_name" class="col-form-label">اسم پلن:</label>
                                                                    <input type="text" class="form-control" id="plan_name" name="plan_name" placeholder="اسم پلن را وارد کنید ..." value="<?php echo $item['name']; ?>" required>
                                                                </div>

                                                                <div class="form-group mb-3">
                                                                    <label for="limit" class="col-form-label">حجم (بر اساس GB):</label>
                                                                    <input type="number" class="form-control" id="limit" name="limit" placeholder="حجم پلن را بر اساس GB وارد کنید ..." value="<?php echo $item['limit']; ?>" min="1" required>
                                                                </div>

                                                                <div class="form-group mb-3">
                                                                    <label for="date" class="col-form-label">زمان (بر اساس روز):</label>
                                                                    <input type="number" class="form-control" id="date" name="date" placeholder="زمان پلن را بر اساس روز وارد کنید ..." value="<?php echo $item['date']; ?>" min="1" required>
                                                                </div>

                                                                <div class="form-group mb-3">
                                                                    <label for="ip_limit" class="col-form-label">تعداد کاربر (ip_limit):</label>
                                                                    <input type="number" class="form-control" id="ip_limit" name="ip_limit" placeholder="تعداد کاربر (ip_limit) پلن  وارد کنید ..." value="<?php echo $item['ip_limit']; ?>" min="1" required>
                                                                </div>

                                                                <div class="form-group mb-3">
                                                                    <label for="price" class="col-form-label">قیمت (بر اساس تومان):</label>
                                                                    <input type="number" class="form-control" id="price" name="price" placeholder="قیمت پلن را بر اساس تومان وارد کنید ..." value="<?php echo $item['price']; ?>" min="500" required>
                                                                </div>


                                                                <div class="form-group mb-3">
                                                                    <label for="plan_type" class="col-form-label">زیرمجموعه کدام پلن مادر شود:</label>
                                                                    <select name="plan_type" class="form-select" id="plan_type" required>
                                                                        <?php
                                                                        $single_plans = $sql->query("SELECT * FROM `single_plan`");
                                                                        while ($plan = $single_plans->fetch_assoc()) {
                                                                        ?>
                                                                            <option value="<?php echo $plan['row']; ?>" <?php echo ($item['type'] == $plan['row']) ? 'selected' : 'unselected'?>><?php echo $plan['name']; ?></option>
                                                                        <?php } ?>
                                                                    </select>
                                                                </div>

                                                                <div class="form-group mb-3">
                                                                    <label for="status" class="col-form-label">وضعیت نمایش:</label>
                                                                    <select name="status" class="form-select" id="status" required>
                                                                        <option value="1" <?php echo ($item['status']) ? 'unselected' : 'selected'; ?>>فعال</option>
                                                                        <option value="0" <?php echo ($item['status']) ? 'unselected' : 'selected'; ?>>غیرفعال</option>
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