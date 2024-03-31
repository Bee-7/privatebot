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
        $plans = $sql->query("SELECT * FROM `representation_settings`");
    } else {
        if ($_GET['from_id'] == $config['admin'] or in_array($_GET['from_id'], $config['admins'])) {
            $plans = $sql->query("SELECT * FROM `representation_settings`");
        } else {
            exit();
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['addPlan']) and $_POST['addPlan'] == 'addPlan') {
        $sql->query("INSERT INTO `representation_settings` (`stage_type`, `discount_percent`, `max_negative`, `plan`) VALUES ('{$_POST['plan_name']}', {$_POST['discount']}, {$_POST['balanace']}, {$_POST['single_plan']})");
        $alert_text = 'پلن شما (<code>' . $_POST['plan_name'] . '</code>) با موفقیت به ربات اضافه شد.';
        $alert_type = 'success';
    }

    elseif (isset($_POST['deletePlan']) and $_POST['deletePlan'] == 'deletePlan') {
        if ($sql->query("SELECT * FROM `representation_settings` WHERE `row` = {$_POST['row']}")->num_rows > 0) {
            $sql->query("DELETE FROM `representation_settings` WHERE `row` = {$_POST['row']}");
            $alert_text = 'پلن انتخابی شما با موفقیت حذف شد.';
            $alert_type = 'success';
        } else {
            $alert_text = 'عملیات حذف پلن با خطا مواجه شد.';
            $alert_type = 'danger';
        }
    }

    elseif (isset($_POST['editPlan']) and $_POST['editPlan'] == 'editPlan') {
        $sql->query("UPDATE `representation_settings` SET `stage_type` = '{$_POST['plan_name']}', `plan` = {$_POST['single_plan']}, `discount_percent` = {$_POST['discount']}, `max_negative` = {$_POST['max_negative']} WHERE `row` = {$_POST['row']}");
        $alert_text = 'تغییرات با موفقیت انجام شد.';
        $alert_type = 'success';
    }
    $plans = $sql->query("SELECT * FROM `representation_settings`");
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
    <title>مدیریت نمایندگی</title>
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

        <div class="row mt-4 justify-content-around">
                <div class="row pt-4 gy-2 gx-2 mb-2 align-items-center">
                    <div class="col-12">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <div class="d-grid">
                                    <a href="representation.php?from_id=<?php echo $_GET['from_id']; ?>"><button type="button" class="btn btn-info" style="width: 100%; margin-top: -20px;">بازگشت به قبل</button></a>
                                </div>
                            </div>

                            <div class="col-6 mb-3">
                                <div class="d-grid">
                                    <a href="representation_settings.php?from_id=<?php echo $_GET['from_id']; ?>"><button type="button" class="btn btn-info" style="width: 100%; margin-top: -20px;">آپدیت</button></a>
                                </div>
                            </div>
                        </div>

                        <?php if ($alert_text != '') { ?>
                            <div class="alert alert-<?php echo $alert_type; ?> text-center" style="padding: 0.5rem !important;"><?php echo $alert_text; ?></div>
                        <?php } else { ?>
                            <div class="alert alert-info text-center" style="padding: 0.5rem !important;">تنظیمات نمایندگی به شرح زیر است.</div>
                        <?php } ?>

                        <div class="alert alert-warning text-center" style="padding: 0.5rem !important;">تعداد کل پلن ها : <code><?php echo $plans->num_rows ?? 0; ?></code> عدد</div>

                        <button type="button" class="btn btn-primary" style="width: 100%;" data-toggle="modal" data-target="#addPlan">افزودن پلن جدید</button>

                        <div class="modal fade" id="addPlan" tabindex="-1" role="dialog" aria-labelledby="addPlanModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="addPlanModalLabel">افزودن پلن نمایندگی جدید</h5>
                                        <button type="button" class="close btn btn-danger" data-dismiss="modal" aria-label="Close">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="representation_settings.php?from_id=<?php echo $_GET['from_id']; ?>" method="post">
                                            <input type="hidden" name="addPlan" value="addPlan">

                                            <div class="form-group mb-3">
                                                <label for="plan_name" class="col-form-label">اسم پلن:</label>
                                                <input type="text" class="form-control" id="plan_name" name="plan_name" placeholder="اسم پلن را وارد کنید ..." required>
                                            </div>

                                            <div class="form-group mb-3">
                                                <label for="single_plan" class="col-form-label">پلن مادر برای خرید سرویس:</label>
                                                <select name="single_plan" class="form-select" id="single_plan" required>
                                                    <?php $single_plans = $sql->query("SELECT * FROM `single_plan`"); ?>
                                                    <?php while ($single_plan = $single_plans->fetch_assoc()) { ?>
                                                        <option value="<?php echo $single_plan['row']; ?>"><?php echo $single_plan['name']; ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <div class="form-group mb-3">
                                                <label for="discount" class="col-form-label">مقدار تخفیف:</label>
                                                <input type="number" class="form-control" id="discount" name="discount" placeholder="مقدار تخفیف این پلن را وارد کنید ..." required>
                                            </div>

                                            <div class="form-group mb-3">
                                                <label for="balanace" class="col-form-label">مقدار max موجودی این پلن:</label>
                                                <input type="number" class="form-control" id="balanace" name="balanace" placeholder="مقدار max موجودی این پلن را وارد کنید ..." required>
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

                        <?php while ($item = $plans->fetch_assoc()) { ?>
                            <div class="card" style="width: 25rem; margin-bottom: 20px;">
                                <div class="card-body" id="get-<?php echo $item['row']; ?>">
                                    <b>آیدی (row) سرویس</b><code style="float: left;"><?php echo $item['row']; ?></code>
                                    <hr>
                                    <b>اسم پلن نمایندگی</b>
                                    <p style="float: left;"><?php echo $item['stage_type']; ?></p>
                                    <hr>
                                    <b>پلن مادر برای خرید سرویس</b>
                                    <p style="float: left;"><?php echo $sql->query("SELECT * FROM `single_plan` WHERE `row` = {$item['plan']}")->fetch_assoc()['name']; ?></p>
                                    <hr>
                                    <b>مقدار تخفیف</b><code style="float: left;"><?php echo $item['discount_percent'] . '%'; ?></code>
                                    <hr>
                                    <b>مقدار max موجودی</b><code style="float: left;"><?php echo number_format($item['max_negative']); ?></code>
                                    <hr>
                                    <b>تعداد کاربر فعال روی این پلن</b><code style="float: left;"><?php echo number_format($sql->query("SELECT * FROM `representations` WHERE `stage_type` = {$item['row']}")->num_rows); ?></code>
                                    <hr>

                                    <div class="row">
                                        <div class="d-grid gap-2">

                                            <form action="representation_settings.php?from_id=<?php echo $_GET['from_id']; ?>" method="post">
                                                <input type="hidden" name="deletePlan" value="deletePlan">
                                                <input type="hidden" name="row" value="<?php echo $item['row']; ?>">
                                                <button type="submit" style="width: 100%;" class="btn btn-danger">حذف پلن</button>
                                            </form>

                                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#editPlan-<?php echo $item['row']; ?>">ویرایش پلن</button>

                                            <div class="modal fade" id="editPlan-<?php echo $item['row']; ?>" tabindex="-1" role="dialog" aria-labelledby="editPlanModalLabel-<?php echo $item['row']; ?>" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="editPlanModalLabel-<?php echo $item['row']; ?>">ویرایش پلن نمایندگی [ <b><?php echo $item['stage_type']; ?></b> ]</h5>
                                                            <button type="button" class="close btn btn-danger" data-dismiss="modal" aria-label="Close">&times;</button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <form action="representation_settings.php?from_id=<?php echo $_GET['from_id']; ?>" method="post">
                                                                <input type="hidden" name="editPlan" value="editPlan">
                                                                <input type="hidden" name="row" value="<?php echo $item['row']; ?>">

                                                                <div class="form-group">
                                                                    <label for="plan_name" class="col-form-label">اسم پلن نمایندگی:</label>
                                                                    <input type="text" class="form-control" id="plan_name" name="plan_name" placeholder="اسم پلن نمایندگی را وارد کنید ..." value="<?php echo $item['stage_type']; ?>" required>
                                                                </div>

                                                                <div class="form-group">
                                                                    <label for="single_plan" class="col-form-label">پلن مادر برای خرید سرویس:</label>
                                                                    <select name="single_plan" class="form-select" id="single_plan" required>
                                                                        <?php $single_plans = $sql->query("SELECT * FROM `single_plan`"); ?>
                                                                        <?php while ($single_plan = $single_plans->fetch_assoc()) { ?>
                                                                            <option value="<?php echo $single_plan['row']; ?>" <?php echo ($item['plan'] == $single_plan['row']) ? 'selected' : 'unselected' ?>><?php echo $single_plan['name']; ?></option>
                                                                        <?php } ?>
                                                                    </select>
                                                                </div>

                                                                <div class="form-group">
                                                                    <label for="discount" class="col-form-label">مقدار تخفیف:</label>
                                                                    <input type="number" class="form-control" id="discount" name="discount" placeholder="مقدار تخفیف را وارد کنید ..." value="<?php echo $item['discount_percent']; ?>" min="1" required>
                                                                </div>

                                                                <div class="form-group">
                                                                    <label for="max_negative" class="col-form-label">مقدار max موجودی:</label>
                                                                    <input type="number" class="form-control" id="max_negative" name="max_negative" placeholder="مقدار max موجودی را وارد کنید ..." value="<?php echo $item['max_negative']; ?>" min="500" required>
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