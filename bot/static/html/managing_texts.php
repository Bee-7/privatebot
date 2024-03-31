<?php
$texts = json_decode(file_get_contents('../../includes/texts.json'), true);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach ($_POST['text'] as $key => $text) {
        $texts[$key] = $text;
    }
    file_put_contents('../../includes/texts.json', json_encode($texts));
    header('Location: managing_texts.php');
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
    <title>مدیریت متن های ربات</title>
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.rtl.min.css" integrity="sha384-gXt9imSW0VcJVHezoNQsP+TNrjYXoGcrqBZJpry9zJt8PCQjobwmhMGaDHTASo9N" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet" type="text/css" />

    <style>
        body {
            font-family: 'Vazirmatn' !important;
        }
    </style>

</head>

<body class="bg-light">

    <div class="container px-4">

        <div class="row mt-4 justify-content-around">

            <div class="col-12">
                <div class="alert alert-info text-center" style="padding: 0.5rem !important;">در این قسمت میتوانید متن های ربات را تغییر دهید.</div>
            </div>

            <div class="col-12"><hr style="margin: 0.65rem 0;"></div>

            <div class="tab-content mt-2" id="pills-tabContent">

                <div class="tab-pane fade show active" role="tabpanel" tabindex="0">

                    <div class="row gx-2 gy-0 justify-content-around">

                        <form action="managing_texts.php" method="post">
                            <?php foreach ($texts as $key => $text) {?>
                                <div class="row">
                                    <div class="mb-3">
                                        <label for="" class="form-label">متن مربوط به : ( <?php echo $key;?> )</label>
                                        <textarea class="form-control" placeholder="متن خود را در این قسمت وارد کنید . . ." id="text-<?php echo $key;?>" name="text[<?php echo $key;?>]" rows="3"><?php echo $texts[$key];?></textarea>
                                    </div>
                                </div>
                            <?php }?>
    
                            <div class="row">
                                <div class="d-grid gap-2">
                                    <button type="submit" id="submit-btn-text" class="btn btn-success">ثبت تغییرات</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Error Box -->
                    <div class="row mx-1 mt-3">
                        <div id="error-alert" class="alert alert-danger" role="alert" style="display: none;"></div>
                        <div id="success-alert" class="alert alert-success" role="alert" style="display: none;"></div>
                    </div>

                </div>

            </div>

        </div>

</body>

</html>