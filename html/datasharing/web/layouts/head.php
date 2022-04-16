<?php
    // バージョンを取得する
    $pdo = connectDb();
    $sql3 = "SELECT * from version ORDER BY created_at DESC limit 1";
    $stmt3 = $pdo->prepare($sql3);
    $stmt3->execute();
    $version = $stmt3->fetch();
    unset($pdo);
?>
<!DOCTYPE html>
<html lang="jp" dir="ltr">
  <head>
    <title><?php echo TITLE.$version['version']; ?> | <?php echo $page_title; ?></title>
    <meta name="description" content="データ共有システム" />
    <meta name="keywords" content="データ共有,システム" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Favicon-->
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
    <!-- Font Awesome icons (free version)-->
    <script src="https://use.fontawesome.com/releases/v5.15.4/js/all.js" crossorigin="anonymous"></script>
    <!-- Google fonts-->
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" type="text/css" />
    <link href="https://fonts.googleapis.com/css?family=Roboto+Slab:400,100,300,700" rel="stylesheet" type="text/css" />

    <!-- CSS only -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link href="css/style.css" rel="stylesheet">
  </head>
