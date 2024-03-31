<?php

include_once '../../config.php';
include_once '../../includes/functions.php';
include_once '../../classes/request.php';

$http = new HTTPRequest();

$alert_text = ''; // DEFAULT NULL
$alert_type = 'info'; // DEFAULT info (blud)

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if ($_GET['from_id'] == $config['admin'] or in_array($_GET['from_id'], $config['admins']) or in_array($_GET['from_id'], $admins)) {
        if (isset($_GET['filter'])) {
            if ($sql->query("SELECT * FROM `payment_factors` WHERE `from_id` = {$_GET['filter']}")->num_rows > 0) {
                $factors = $sql->query("SELECT * FROM `payment_factors` WHERE `from_id` = {$_GET['filter']}");
            } else {
                $alert_text = ('کاربر (<code>' . $_GET['filter'] . '</code>) عضو ربات نیست.');
                $alert_type = 'danger';
            }
        } else {
            $offset = $_GET['page'] * 6 - 6; $addpage = $_GET['page'] + 1; $menpage = $_GET['page'] - 1;
            $factors = $sql->query("SELECT * FROM `payment_factors` ORDER BY `row` DESC LIMIT 6 OFFSET $offset");
        }
    } else {
        exit('Access denied');
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_POST['search-type'] == 'based-on-userid') {
        if ($sql->query("SELECT * FROM `payment_factors` WHERE `from_id` = {$_POST['search-keyword']}")->num_rows > 0) {
            $factors = $sql->query("SELECT * FROM `payment_factors` WHERE `from_id` = {$_POST['search-keyword']}");
            if ($factors->num_rows == 0) {
                $alert_text = ('کاربر (<code>' . $_POST['search-keyword'] . '</code>) هیچ فاکتوری (رسید) در ربات ندارد.');
                $alert_type = 'danger';
            } else {
                $alert_text = ('لیست همه فاکتور (رسید) های کاربر (<code>' . $_POST['search-keyword'] . '</code>) به شرح زیر است.');
            }
        } else {
            $alert_text = 'آیدی عددی ارسال شده اشتباه است !';
            $alert_type = 'danger';
        }
    }

    if ($_POST['search-type'] == 'based-on-code') {
        if ($sql->query("SELECT * FROM `payment_factors` WHERE `code` = {$_POST['search-keyword']}")->num_rows > 0) {
            $factors = $sql->query("SELECT * FROM `payment_factors` WHERE `code` = {$_POST['search-keyword']}");
            if ($factors->num_rows == 0) {
                $alert_text = ('فاکتوری با کد پیگیری (' . $_POST['search-keyword'] . ') در ربات یافت نشد.');
                $alert_type = 'danger';
            } else {
                $alert_text = ('فاکتور با کد پیگیری (' . $_POST['search-keyword'] . ') به شرح زیر است !');
            }
        } else {
            $alert_text = 'آیدی عددی ارسال شده اشتباه است !';
            $alert_type = 'danger';
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
    <title>لیست همه فاکتور (رسید) های ربات</title>
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

            <form action="manage_factors.php?from_id=<?php echo $_GET['from_id']; ?>&page=1" method="post">
                <div class="row pt-4 gy-2 gx-2 mb-2 align-items-center">

                    <div class="col-12">
                        <?php if ($alert_text != '') { ?>
                            <div class="alert alert-<?php echo $alert_type; ?> text-center"
                                style="padding: 0.5rem !important;">
                                <?php echo $alert_text; ?>
                            </div>
                        <?php } else { ?>
                            <div class="alert alert-info text-center" style="padding: 0.5rem !important;">لیست همه فاکتور
                                (رسید) های ربات به شرح زیر است</div>
                        <?php } ?>
                        <div class="alert alert-warning text-center" style="padding: 0.5rem !important;">تعداد کل فاکتور
                            (رسید) ها :
                            <code><?php echo $sql->query("SELECT * FROM `payment_factors` ORDER BY `row` DESC")->num_rows ?? 0; ?></code>
                            عدد
                        </div>
                    </div>

                    <div class="col-12">
                        <input type="hidden" name="page" value="1">
                        <input type="text" name="search-keyword" class="form-control" id="search-box" value=""
                            placeholder="جستجو ..." required>
                    </div>


                    <div class="col-8">
                        <select name="search-type" class="form-select" id="specificSizeSelect">
                            <option value="based-on-userid" selected>بر اساس آیدی عددی</option>
                            <option value="based-on-code" selected>بر اساس کد پیگیری</option>
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

            <div class="tab-content mt-3" id="pills-tabContent">

                <div class="tab-pane fade show active" role="tabpanel" tabindex="0">

                    <div class="row gx-2 gy-0 justify-content-around">

                        <?php while ($factor = $factors->fetch_assoc()) { ?>
                            <div class="card" style="width: 25rem; margin-bottom: 20px;">
                                <!-- <img class="card-img-top" src="<?php echo 'https://api.qrserver.com/v1/create-qr-code/?data=' . urlencode($service['config_link']) . '&size=800x800' ?>" alt="qr-code" height="370px"> -->
                                <div class="card-body" id="get-<?php echo $service['row']; ?>">
                                    <b>آیدی (row) فاکتور</b><code style="float: left;"><?php echo $factor['row']; ?></code>
                                    <hr>
                                    <b>وضعیت فاکتور</b>
                                    <p style="float: left; color: <?php echo ($factor['status']) ? 'green' : 'red'; ?>;">
                                        <?php echo ($factor['status']) ? 'تایید شده' : 'رد شده'; ?>
                                    </p>
                                    <hr>
                                    <b>اسم کاربر</b><code
                                        style="float: left;"><?php echo bot('getChat', ['chat_id' => $factor['from_id']])->result->first_name; ?></code>
                                    <hr>
                                    <b>یوزرنیم کاربر</b><code
                                        style="float: left;"><?php echo bot('getChat', ['chat_id' => $factor['from_id']])->result->username; ?></code>
                                    <hr>
                                    <b>آیدی عددی</b><code style="float: left;"><?php echo $factor['from_id']; ?></code>
                                    <hr>
                                    <b>مبلغ فاکتور</b><code style="float: left;"><?php echo number_format($factor['price']); ?></code>
                                    <hr>
                                    <b>کد پیگیری فاکتور</b><code style="float: left;"><?php echo $factor['code']; ?></code>

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
                                                    <a class="page-link" href="manage_factors.php?from_id=<?php echo $_GET['from_id']; ?>&page=<?php echo $menpage ?>">Previous</a>
                                                </li>
                                            <?php } ?>
                                            <?php if ($_GET['page'] > 1) { ?>
                                                <li class="page-item"><a class="page-link"
                                                        href="manage_factors.php?from_id=<?php echo $_GET['from_id']; ?>&page=<?php echo $_GET['page'] - 1; ?>">
                                                        <?php echo $_GET['page'] - 1; ?>
                                                    </a></li>
                                            <?php } ?>
                                            <li class="page-item active">
                                                <a class="page-link"
                                                    href="manage_factors.php?from_id=<?php echo $_GET['from_id']; ?>&page=<?php echo $_GET['page']; ?>">
                                                    <?php echo $_GET['page']; ?>
                                                </a>
                                            </li>
                                            <li class="page-item"><a class="page-link"
                                                    href="manage_factors.php?from_id=<?php echo $_GET['from_id']; ?>&page=<?php echo $_GET['page'] + 1; ?>">
                                                    <?php echo $_GET['page'] + 1; ?>
                                                </a></li>
                                            <?php if ($page * 7 < $sql->query("SELECT * FROM `payment_factors` ORDER BY `row` DESC")->num_rows) { ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="manage_factors.php?from_id=<?php echo $_GET['from_id']; ?>&page=<?php echo $addpage ?>">Next</a>
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