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
        $stages = $sql->query("SELECT * FROM `representation_settings`");
        $representations = $sql->query("SELECT * FROM `representations`");
    } else {
        if ($_GET['from_id'] == $config['admin'] or in_array($_GET['from_id'], $config['admins'])) {
            $stages = $sql->query("SELECT * FROM `representation_settings`");
            $representations = $sql->query("SELECT * FROM `representations`");
        } else {
            exit();
        }
    }
}

elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['addUser']) and $_POST['addUser'] == 'addUser') {
        if ($sql->query("SELECT * FROM `users` WHERE `from_id` = {$_POST['userid']}")->num_rows > 0) {
            $stage_type = $sql->query("SELECT * FROM `representation_settings` WHERE `stage_type` = '{$_POST['stage_name']}'")->fetch_assoc();
            $sql->query("INSERT INTO `representations` (`from_id`, `nick_name`, `stage_type`, `status`) VALUES ({$_POST['userid']}, '{$_POST['nick_name']}', {$stage_type['row']}, 1)");
            
            if (isset($_POST['addusermessage'])) {
                $key = json_encode(['inline_keyboard' => [[['text' => '🔄 شروع', 'url' => 'https://t.me/' . bot('getMe')->result->username . '?start=' . $_POST['userid']]]]]);
                sendMessage($_POST['userid'], $_POST['addusermessage'], $key);
            }

            $alert_text = 'کاربر (<code>' . $_POST['userid'] . '</code>) با موفقیت به لیست نمایندگان اضافه شد.';
            $alert_type = 'success';
            $stages = $sql->query("SELECT * FROM `representation_settings`");
            $representations = $sql->query("SELECT * FROM `representations`");
        } else {
            $alert_text = ('آیدی عددی (<code>' . $_POST['userid'] . '</code>) عضو ربات نیست.');
            $alert_type = 'danger';
            $stages = $sql->query("SELECT * FROM `representation_settings`");
            $representations = $sql->query("SELECT * FROM `representations`");
        }
    }

    elseif (isset($_POST['deleteService']) and $_POST['deleteService'] == 'deleteService') {
        $sql->query("DELETE FROM `representations` WHERE `from_id` = {$_POST['from_id']}");
        $alert_text = ('کاربر (<code>' . $_POST['from_id'] . '</code>) با موفقیت از لیست نمایندگان ربات حذف شد.');
        $alert_type = 'success';
        $stages = $sql->query("SELECT * FROM `representation_settings`");
        $representations = $sql->query("SELECT * FROM `representations`");
    }

    elseif (isset($_POST['sendMessage']) and $_POST['sendMessage'] == 'sendMessage') {
        if ($sql->query("SELECT * FROM `users` WHERE `from_id` = {$_POST['userid']}")->num_rows > 0) {
            sendMessage($_POST['userid'], $_POST['message']);
            $alert_text = ('پیام شما با موفقیت به کاربر  (<code>' . $_POST['userid'] . '</code>) ارسال شد.');
            $alert_type = 'success';
            $stages = $sql->query("SELECT * FROM `representation_settings`");
            $representations = $sql->query("SELECT * FROM `representations`");
        } else {
            $alert_text = 'آیدی عددی ارسال شده اشتباه است !';
            $alert_type = 'danger';
            $stages = $sql->query("SELECT * FROM `representation_settings`");
            $representations = $sql->query("SELECT * FROM `representations`");
        }
    }

    elseif (isset($_POST['changeUserStatus']) and $_POST['changeUserStatus'] == 'changeUserStatus') {
        $sql->query("UPDATE `representations` SET `status` = {$_POST['status']} WHERE `from_id` = {$_POST['from_id']}");
        $alert_text = 'عملیات تغییر وضعیت با موفقیت انجام شد.';
        $alert_type = 'success';
        $stages = $sql->query("SELECT * FROM `representation_settings`");
        $representations = $sql->query("SELECT * FROM `representations`");
    }
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
    <title>لیست همه نمایندگان</title>
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

            <form action="all_services.php" method="post">
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
                                    <a href="representation_users.php?from_id=<?php echo $_GET['from_id']; ?>"><button type="button" class="btn btn-info" style="width: 100%; margin-top: -20px;">آپدیت</button></a>
                                </div>
                            </div>
                        </div>

                        <?php if ($alert_text != '') { ?>
                            <div class="alert alert-<?php echo $alert_type; ?> text-center" style="padding: 0.5rem !important;"><?php echo $alert_text; ?></div>
                        <?php } else { ?>
                            <div class="alert alert-info text-center" style="padding: 0.5rem !important;">لیست همه نمایندگان ربات به شرح زیر است.</div>
                        <?php } ?>

                        <div class="alert alert-warning text-center" style="padding: 0.5rem !important;">تعداد کل نماینده ها : <code><?php echo $representations->num_rows ?? 0; ?></code> عدد</div>
                    </div>

                    <div class="col-12">
                        <input type="hidden" name="page" value="1">
                        <input type="text" name="search-keyword" class="form-control" id="search-box" value="" placeholder="جستجو ..." required>
                    </div>


                    <div class="col-8">
                        <select name="search-type" class="form-select" id="specificSizeSelect">
                            <option value="based-on-userid" selected>بر اساس آیدی عددی</option>
                            <option value="based-on-6code">بر اساس نام مستعار</option>
                            <option value="based-on-6code">بر اساس یوزرنیم</option>
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
                <hr style="margin: 1rem 0;">
            </div>

            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addUser">افزودن نماینده جدید</button>
            <div class="modal fade" id="addUser" tabindex="-1" role="dialog" aria-labelledby="addUserModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addUserModalLabel">افزودن نماینده جدید</h5>
                            <button type="button" class="close btn btn-danger" data-dismiss="modal" aria-label="Close">&times;</button>
                        </div>
                        <div class="modal-body">
                            <form action="representation_users.php?from_id=<?php echo $_GET['from_id']; ?>" method="post">
                                <input type="hidden" name="addUser" value="addUser">

                                <div class="form-group mb-3">
                                    <label for="userid" class="col-form-label">آیدی عددی کاربر:</label>
                                    <input type="number" class="form-control" id="userid" name="userid" placeholder="آیدی عددی کاربر را در این قسمت وارد کنید ..." value="<?php echo $item['from_id']; ?>" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="nickname" class="col-form-label">اسم مستعار:</label>
                                    <input type="text" class="form-control" id="nickname" name="nick_name" placeholder="یک نام مستعار برای کاربر بزارید ..." required>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="stage_select" class="label-control">نوع پلن (Stage):</label>
                                    <select name="stage_name" class="form-select" id="stage_select" required>
                                        <?php while ($stage = $stages->fetch_assoc()) { ?>
                                            <option value="<?php echo $stage['stage_type']; ?>"><?php echo $stage['stage_type']; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="addusermessage" class="col-form-label">پیام (در صورت نیاز):</label>
                                    <textarea class="form-control" id="addusermessage" name="addusermessage" placeholder="در صورتی که میخواهید بعد اضافه شدن کاربر, پیامی هم ارسال شود ..."></textarea>
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

            <div class="col-12">
                <hr style="margin: 1.5rem 0 0;">
            </div>

            <div class="tab-content mt-3" id="pills-tabContent">

                <div class="tab-pane fade show active" role="tabpanel" tabindex="0">

                    <div class="row gx-2 gy-0 justify-content-around">

                        <?php while ($item = $representations->fetch_assoc()) { ?>
                            <div class="card" style="width: 25rem; margin-bottom: 20px;">
                                <div class="card-body" id="get-<?php echo $item['row']; ?>">
                                    <b>آیدی (row) سرویس</b><code style="float: left;"><?php echo $item['row']; ?></code>
                                    <hr>
                                    <b>وضعیت نماینده</b>
                                    <p style="float: left; color: <?php echo ($item['status']) ? 'green' : 'red'; ?>;"><?php echo ($item['status']) ? 'فعال' : 'غیرفعال'; ?></p>
                                    <hr>
                                    <b>آیدی عددی</b><code style="float: left;"><?php echo $item['from_id']; ?></code>
                                    <hr>
                                    <b>نام مستعار</b><code style="float: left;"><?php echo $item['nick_name']; ?></code>
                                    <hr>
                                    <b>پلن (Stage) کاربر</b><code style="float: left;"><?php echo $sql->query("SELECT * FROM `representation_settings` WHERE `row` = {$item['stage_type']}")->fetch_assoc()['stage_type']; ?></code>
                                    <hr>

                                    <div class="row">
                                        <div class="d-grid gap-2">

                                            <form action="representation_users.php?from_id=<?php echo $_GET['from_id']; ?>" method="post">
                                                <input type="hidden" name="changeUserStatus" value="changeUserStatus">
                                                <input type="hidden" name="from_id" value="<?php echo $item['from_id']; ?>">
                                                <input type="hidden" name="status" value="<?php echo (($item['status']) ? 'false' : 'true'); ?>">
                                                <input type="hidden" name="row" value="<?php echo $item['row']; ?>">
                                                <button type="submit" style="width: 100%;" class="btn btn-<?php echo ($item['status']) ? 'danger' : 'success'; ?>"><?php echo ($item['status']) ? 'غیرفعال کردن کاربر' : 'فعال کردن کاربر'; ?></button>
                                            </form>

                                            <form action="representation_users.php?from_id=<?php echo $_GET['from_id']; ?>" method="post">
                                                <input type="hidden" name="deleteService" value="deleteService">
                                                <input type="hidden" name="from_id" value="<?php echo $item['from_id']; ?>">
                                                <input type="hidden" name="row" value="<?php echo $item['row']; ?>">
                                                <button type="submit" style="width: 100%;" class="btn btn-secondary">حذف از نمایندگی</button>
                                            </form>

                                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#sendMessage-<?php echo $item['from_id']; ?>">ارسال پیام به کاربر</button>

                                            <div class="modal fade" id="sendMessage-<?php echo $item['from_id']; ?>" tabindex="-1" role="dialog" aria-labelledby="sendMessageModalLabel-<?php echo $item['from_id']; ?>" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="sendMessageModalLabel-<?php echo $item['from_id']; ?>">ارسال پیام به کاربر <code><?php echo $item['from_id']; ?></code></h5>
                                                            <button type="button" class="close btn btn-danger" data-dismiss="modal" aria-label="Close">&times;</button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <form action="representation_users.php?from_id=<?php echo $_GET['from_id']; ?>" method="post">
                                                                <input type="hidden" name="sendMessage" value="sendMessage">

                                                                <div class="form-group">
                                                                    <label for="userid" class="col-form-label">کاربر:</label>
                                                                    <input type="number" class="form-control" id="userid" name="userid" placeholder="آیدی عددی فرد را در این قسمت وارد کنید ..." value="<?php echo $item['from_id']; ?>" required>
                                                                </div>

                                                                <div class="form-group">
                                                                    <label for="message" class="col-form-label">پیام:</label>
                                                                    <textarea class="form-control" id="message" name="message" placeholder="پیام خود را در این قسمت وارد کنید ..." required></textarea>
                                                                </div>

                                                                <div class="modal-footer" dir="ltr">
                                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">لغو</button>
                                                                    <button type="submit" class="btn btn-primary">ارسال پیام</button>
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