<?php

include_once '../../config.php';
include_once '../../includes/functions.php';
include_once '../../classes/request.php';

$http = new HTTPRequest();

$alert_text = ''; // DEFAULT NULL
$alert_type = 'info'; // DEFAULT info (blud)

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if ($_GET['from_id'] == $config['admin'] or in_array($_GET['from_id'], $config['admins']) or in_array($_GET['from_id'], $admins)) {
        $settings = $sql->query("SELECT * FROM `settings`")->fetch_assoc();
        $representation = $sql->query("SELECT * FROM `representation_settings`");
    } else {
        exit('Access denied!');
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
    <title>تنظیمات نمایندگان</title>
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
                        <div class="alert alert-info text-center" style="padding: 0.5rem !important;">یکی از گزینه های زیر را انتخاب کنید:</div>
                    </div>

                </div>
            </form>

            <div class="col-12">
                <hr style="margin: 0.65rem 0;">
            </div>

            <div class="tab-content mt-3" id="pills-tabContent">

                <div class="tab-pane fade show active" role="tabpanel" tabindex="0">

                    <div class="row gx-2 gy-0 justify-content-around">
                        <div class="col-6">
                            <a href="representation_users.php?from_id=<?php echo $_GET['from_id']; ?>"><button type="button" class="btn btn-success" style="width: 100%; height: 50px;">لیست نمایندگان</button></a>
                        </div>

                        <div class="col-6">
                            <a href="representation_settings.php?from_id=<?php echo $_GET['from_id']; ?>"><button type="button" class="btn btn-success" style="width: 100%; height: 50px;">تنظیمات نمایندگی</button></a>
                        </div>
                    </div>

                </div>

                <div class="col-12">
                    <hr style="margin: 2rem 0;">
                </div>

                <?php if ($representation->num_rows > 0) {?>
                    <div class="col-12" style="margin-top: 30px;">
                        <div class="alert alert-warning text-center" style="padding: 0.5rem !important;">پلن های (استیج ها) نمایندگی به شرح زیر است:</div>
                    </div>

                    <table class="table" style="margin-top: 30px;" dir="ltr">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">تخفیف</th>
                                <th scope="col">ماکسیموم موجودی</th>
                                <th scope="col">نام</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($item = $representation->fetch_assoc()) {?>
                            <tr>
                                <th scope="row"><?php echo $item['row']; ?></th>
                                <td><?php echo $item['discount_percent']; ?>%</td>
                                <td><?php echo number_format($item['max_negative']); ?> تومان</td>
                                <td><?php echo $item['stage_type']; ?></td>
                            </tr>
                            <?php }?>
                        </tbody>
                    </table>
                <?php }?>

            </div>

        </div>
    </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha2/js/bootstrap.bundle.min.js"></script>
</body>

</html>