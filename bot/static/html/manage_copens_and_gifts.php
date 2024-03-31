<?php

include_once '../../config.php';
include_once '../../includes/functions.php';
include_once '../../classes/request.php';

$http = new HTTPRequest();

$alert_text = ''; // DEFAULT NULL
$alert_type = 'info'; // DEFAULT info (blud)

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if ($_GET['from_id'] == $config['admin'] or in_array($_GET['from_id'], $config['admins'])) {
        $copens = $sql->query("SELECT * FROM `copens`");
        $gifts = $sql->query("SELECT * FROM `gifts`");
    } else {
        return;
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['addCopen']) and $_POST['addCopen'] == 'addCopen') {
        $sql->query("INSERT INTO `copens` (`copen`, `percent`, `count_use`, `status`) VALUES ('{$_POST['copen']}', {$_POST['percent']}, {$_POST['count_use']}, {$_POST['status']})");
        $alert_text = 'کد تخفیف شما (<code>' . $_POST['copen'] . '</code>) با موفقیت به ربات اضافه شد.';
        $alert_type = 'success';
    } elseif (isset($_POST['addGift']) and $_POST['addGift'] == 'addGift') {
        $sql->query("INSERT INTO `gifts` (`gift`, `price`, `count_use`, `status`) VALUES ('{$_POST['gift']}', {$_POST['price']}, {$_POST['count_use']}, {$_POST['status']})");
        $alert_text = 'کد هدیه شما (<code>' . $_POST['gift'] . '</code>) با موفقیت به ربات اضافه شد.';
        $alert_type = 'success';
    } elseif (isset($_POST['deleteCopen']) and $_POST['deleteCopen'] == 'deleteCopen') {
        if ($sql->query("SELECT * FROM `copens` WHERE `row` = {$_POST['row']}")->num_rows > 0) {
            $sql->query("DELETE FROM `copens` WHERE `row` = {$_POST['row']}");
            $alert_text = 'کد تخفیف شما با موفقیت حذف شد.';
            $alert_type = 'success';
        } else {
            $alert_text = 'عملیات حذف کد تخفیف با خطا مواجه شد.';
            $alert_type = 'danger';
        }
    } elseif (isset($_POST['deleteGift']) and $_POST['deleteGift'] == 'deleteGift') {
        if ($sql->query("SELECT * FROM `gifts` WHERE `row` = {$_POST['row']}")->num_rows > 0) {
            $sql->query("DELETE FROM `gifts` WHERE `row` = {$_POST['row']}");
            $alert_text = 'کد هدیه شما با موفقیت حذف شد.';
            $alert_type = 'success';
        } else {
            $alert_text = 'عملیات حذف کد هدیه با خطا مواجه شد.';
            $alert_type = 'danger';
        }
    } elseif (isset($_POST['editCopen']) and $_POST['editCopen'] == 'editCopen') {
        $sql->query("UPDATE `copens` SET `copen` = '{$_POST['copen']}', `percent` = {$_POST['percent']}, `count_use` = {$_POST['count_use']}, `status` = {$_POST['status']} WHERE `row` = {$_POST['row']}");
        $alert_text = 'تغییرات با موفقیت انجام شد.';
        $alert_type = 'success';
    } elseif (isset($_POST['editGift']) and $_POST['editGift'] == 'editGift') {
        $sql->query("UPDATE `gifts` SET `gift` = '{$_POST['gift']}', `price` = {$_POST['price']}, `count_use` = {$_POST['count_use']}, `status` = {$_POST['status']} WHERE `row` = {$_POST['row']}");
        $alert_text = 'تغییرات با موفقیت انجام شد.';
        $alert_type = 'success';
    }

    $copens = $sql->query("SELECT * FROM `copens`");
    $gifts = $sql->query("SELECT * FROM `gifts`");
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
    <title>مدیریت کد تخفیف/هدیه ها</title>
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
                                <a href="manage_copens_and_gifts.php?from_id=<?php echo $_GET['from_id']; ?>"><button type="button" class="btn btn-info" style="width: 100%; margin-top: -20px;">آپدیت</button></a>
                            </div>
                        </div>
                    </div>

                    <?php if ($alert_text != '') { ?>
                        <div class="alert alert-<?php echo $alert_type; ?> text-center" style="padding: 0.5rem !important;"><?php echo $alert_text; ?></div>
                    <?php } else { ?>
                        <div class="alert alert-info text-center" style="padding: 0.5rem !important;">تنظیمات کد تخفیف/هدیه ها به شرح زیر است.</div>
                    <?php } ?>

                    <div class="alert alert-warning text-center" style="padding: 0.5rem !important;">تعداد کل کد تخفیف/هدیه ها : <code><?php echo ($copens->num_rows + $gifts->num_rows) ?? 0; ?></code> عدد</div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <div class="d-grid">
                                <button type="button" class="btn btn-primary" style="width: 100%;" data-toggle="modal" data-target="#addCopen">افزودن کد تخفیف</button>
                                <div class="modal fade" id="addCopen" tabindex="-1" role="dialog" aria-labelledby="addCopenModalLabel" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">

                                            <div class="modal-header">
                                                <h5 class="modal-title" id="addCopenModalLabel">افزودن کد تخفیف جدید</h5>
                                                <button type="button" class="close btn btn-danger" data-dismiss="modal" aria-label="Close">&times;</button>
                                            </div>

                                            <div class="modal-body">
                                                <form action="manage_copens_and_gifts.php?from_id=<?php echo $_GET['from_id']; ?>" method="post">
                                                    <input type="hidden" name="addCopen" value="addCopen">

                                                    <div class="form-group mb-3">
                                                        <label for="copen" class="col-form-label">کد تخفیف:</label>
                                                        <input type="text" class="form-control" id="copen" name="copen" placeholder="کد تخفیف را وارد کنید ..." required>
                                                    </div>

                                                    <div class="form-group mb-3">
                                                        <label for="percent" class="col-form-label">درصد تخفیف:</label>
                                                        <input type="number" class="form-control" id="percent" name="percent" placeholder="درصد کد تخفیف را وارد کنید ..." min="1" required>
                                                    </div>

                                                    <div class="form-group mb-3">
                                                        <label for="count_use" class="col-form-label">تعداد قابل استفاده:</label>
                                                        <input type="number" class="form-control" id="count_use" name="count_use" placeholder="تعداد قابل استفاده را وارد کنید ..." min="1" value="1" required>
                                                    </div>

                                                    <div class="form-group mb-3">
                                                        <label for="status" class="col-form-label">وضعیت:</label>
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

                        <div class="col-6 mb-3">
                            <div class="d-grid">
                                <button type="button" class="btn btn-primary" style="width: 100%;" data-toggle="modal" data-target="#addGift">افزودن کد هدیه</button>
                                <div class="modal fade" id="addGift" tabindex="-1" role="dialog" aria-labelledby="addGiftModalLabel" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">

                                            <div class="modal-header">
                                                <h5 class="modal-title" id="addGiftModalLabel">افزودن کد هدیه جدید</h5>
                                                <button type="button" class="close btn btn-danger" data-dismiss="modal" aria-label="Close">&times;</button>
                                            </div>

                                            <div class="modal-body">
                                                <form action="manage_copens_and_gifts.php?from_id=<?php echo $_GET['from_id']; ?>" method="post">
                                                    <input type="hidden" name="addGift" value="addGift">

                                                    <div class="form-group mb-3">
                                                        <label for="gift" class="col-form-label">کد هدیه:</label>
                                                        <input type="text" class="form-control" id="gift" name="gift" placeholder="کد هدیه را وارد کنید ..." required>
                                                    </div>

                                                    <div class="form-group mb-3">
                                                        <label for="price" class="col-form-label">مبلغ کد هدیه:</label>
                                                        <input type="number" class="form-control" id="price" name="price" placeholder="مبلغ کد هدیه را وارد کنید ..." min="100" required>
                                                    </div>

                                                    <div class="form-group mb-3">
                                                        <label for="count_use" class="col-form-label">تعداد قابل استفاده:</label>
                                                        <input type="number" class="form-control" id="count_use" name="count_use" placeholder="تعداد قابل استفاده را وارد کنید ..." min="1" value="1" required>
                                                    </div>

                                                    <div class="form-group mb-3">
                                                        <label for="status" class="col-form-label">وضعیت:</label>
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
                    </div>

                </div>
            </div>


            <div class="col-12">
                <hr style="margin: 1rem 0 0;">
            </div>

            <div class="tab-content mt-3" id="pills-tabContent">

                <div class="tab-pane fade show active" role="tabpanel" tabindex="0">

                    <div class="row gx-2 gy-0 justify-content-around">

                        <?php if ($copens->num_rows > 0) { ?>
                            <?php while ($copen = $copens->fetch_assoc()) { ?>
                                <div class="card" style="width: 25rem; margin-bottom: 20px;">
                                    <div class="card-body" id="get-<?php echo $copen['row']; ?>">
                                        <b>آیدی (row) کد</b><code style="float: left;"><?php echo $copen['row']; ?></code>
                                        <hr>
                                        <b>وضعیت</b>
                                        <p style="float: left; color: <?php echo ($copen['status']) ? 'green' : 'red'; ?>;"><?php echo ($copen['status']) ? 'فعال' : 'غیرفعال'; ?></p>
                                        <hr>
                                        <b>کد تخفیف</b>
                                        <code style="float: left;"><?php echo $copen['copen']; ?></code>
                                        <hr>
                                        <b>درصد تخفیف</b>
                                        <code style="float: left;"><?php echo $copen['percent']; ?></code>
                                        <hr>
                                        <b>تعداد قابل استفاده</b>
                                        <code style="float: left;"><?php echo $copen['count_use']; ?></code>
                                        <hr>

                                        <div class="row">
                                            <div class="d-grid gap-2">

                                                <form action="manage_copens_and_gifts.php?from_id=<?php echo $_GET['from_id']; ?>" method="post">
                                                    <input type="hidden" name="deleteCopen" value="deleteCopen">
                                                    <input type="hidden" name="row" value="<?php echo $copen['row']; ?>">
                                                    <button type="submit" style="width: 100%;" class="btn btn-danger">حذف کد تخفیف</button>
                                                </form>

                                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#editCopen-<?php echo $copen['row']; ?>">ویرایش کد تخفیف</button>

                                                <div class="modal fade" id="editCopen-<?php echo $copen['row']; ?>" tabindex="-1" role="dialog" aria-labelledby="editCopenModalLabel-<?php echo $copen['row']; ?>" aria-hidden="true">
                                                    <div class="modal-dialog" role="document">
                                                        <div class="modal-content">

                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="editPlanModalLabel-<?php echo $item['row']; ?>">ویرایش کد تخفغیف [ <b><?php echo $copen['copen']; ?></b> ]</h5>
                                                                <button type="button" class="close btn btn-danger" data-dismiss="modal" aria-label="Close">&times;</button>
                                                            </div>

                                                            <div class="modal-body">
                                                                <form action="manage_copens_and_gifts.php?from_id=<?php echo $_GET['from_id']; ?>" method="post">
                                                                    <input type="hidden" name="editCopen" value="editCopen">
                                                                    <input type="hidden" name="row" value="<?php echo $copen['row']; ?>">

                                                                    <div class="form-group">
                                                                        <label for="copen" class="col-form-label">کد تخفیف:</label>
                                                                        <input type="text" class="form-control" id="copen" name="copen" placeholder="کد تخفیف را وارد کنید ..." value="<?php echo $copen['copen']; ?>" required>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label for="percent" class="col-form-label">درصد تخفیف:</label>
                                                                        <input type="number" class="form-control" id="percent" name="percent" placeholder="درصد تخفیف کد تخفیف را وارد کنید ..." value="<?php echo $copen['percent']; ?>" min="1" required>
                                                                    </div>

                                                                    <div class="form-group mb-3">
                                                                        <label for="count_use" class="col-form-label">تعداد قابل استفاده:</label>
                                                                        <input type="number" class="form-control" id="count_use" name="count_use" placeholder="تعداد قابل استفاده را وارد کنید ..." min="1" value="<?php echo $copen['count_use']; ?>" required>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label for="status" class="col-form-label">وضعیت:</label>
                                                                        <select name="status" class="form-select" id="status" required>
                                                                            <option value="1" <?php echo ($copen['status']) ? 'unselected' : 'selected' ?>>فعال</option>
                                                                            <option value="0" <?php echo ($copen['status']) ? 'unselected' : 'selected' ?>>غیرفعال</option>
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
                        <?php } ?>

                        <?php if ($gifts->num_rows > 0) { ?>
                            <?php while ($gift = $gifts->fetch_assoc()) { ?>
                                <div class="card" style="width: 25rem; margin-bottom: 20px;">
                                    <div class="card-body" id="get-<?php echo $gift['row']; ?>">
                                        <b>آیدی (row) کد</b><code style="float: left;"><?php echo $gift['row']; ?></code>
                                        <hr>
                                        <b>وضعیت</b>
                                        <p style="float: left; color: <?php echo ($gift['status']) ? 'green' : 'red'; ?>;"><?php echo ($gift['status']) ? 'فعال' : 'غیرفعال'; ?></p>
                                        <hr>
                                        <b>کد هدیه</b>
                                        <code style="float: left;"><?php echo $gift['gift']; ?></code>
                                        <hr>
                                        <b>مبلغ کد هدیه</b>
                                        <code style="float: left;"><?php echo $gift['price']; ?></code>
                                        <hr>
                                        <b>تعداد قابل استفاده</b>
                                        <code style="float: left;"><?php echo $gift['count_use']; ?></code>
                                        <hr>

                                        <div class="row">
                                            <div class="d-grid gap-2">

                                                <form action="manage_copens_and_gifts.php?from_id=<?php echo $_GET['from_id']; ?>" method="post">
                                                    <input type="hidden" name="deleteGift" value="deleteGift">
                                                    <input type="hidden" name="row" value="<?php echo $gift['row']; ?>">
                                                    <button type="submit" style="width: 100%;" class="btn btn-danger">حذف کد هدیه</button>
                                                </form>

                                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#editGift-<?php echo $gift['row']; ?>">ویرایش کد هدیه</button>

                                                <div class="modal fade" id="editGift-<?php echo $gift['row']; ?>" tabindex="-1" role="dialog" aria-labelledby="editGiftModalLabel-<?php echo $gift['row']; ?>" aria-hidden="true">
                                                    <div class="modal-dialog" role="document">
                                                        <div class="modal-content">

                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="editGiftModalLabel-<?php echo $gift['row']; ?>">ویرایش کد هدیه [ <b><?php echo $gift['gift']; ?></b> ]</h5>
                                                                <button type="button" class="close btn btn-danger" data-dismiss="modal" aria-label="Close">&times;</button>
                                                            </div>

                                                            <div class="modal-body">
                                                                <form action="manage_copens_and_gifts.php?from_id=<?php echo $_GET['from_id']; ?>" method="post">
                                                                    <input type="hidden" name="editGift" value="editGift">
                                                                    <input type="hidden" name="row" value="<?php echo $gift['row']; ?>">

                                                                    <div class="form-group">
                                                                        <label for="gift" class="col-form-label">کد هدیه:</label>
                                                                        <input type="text" class="form-control" id="gift" name="gift" placeholder="کد هدیه را وارد کنید ..." value="<?php echo $gift['gift']; ?>" required>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label for="price" class="col-form-label">مبلغ کد هدیه:</label>
                                                                        <input type="number" class="form-control" id="price" name="price" placeholder="مبلغ کد هدیه را وارد کنید ..." value="<?php echo $gift['price']; ?>" min="1" required>
                                                                    </div>

                                                                    <div class="form-group mb-3">
                                                                        <label for="count_use" class="col-form-label">تعداد قابل استفاده:</label>
                                                                        <input type="number" class="form-control" id="count_use" name="count_use" placeholder="تعداد قابل استفاده را وارد کنید ..." min="1" value="<?php echo $copen['count_use']; ?>" required>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label for="status" class="col-form-label">وضعیت:</label>
                                                                        <select name="status" class="form-select" id="status" required>
                                                                            <option value="1" <?php echo ($gift['status']) ? 'unselected' : 'selected' ?>>فعال</option>
                                                                            <option value="0" <?php echo ($gift['status']) ? 'unselected' : 'selected' ?>>غیرفعال</option>
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