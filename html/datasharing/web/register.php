<?php
require_once('config.php');
require_once('functions.php');

// レイアウト関連の変数
$page_title = 'データ登録';
// 認証処理
session_start();
if (!isset($_SESSION['USER'])) {
    header('Location: '.SITE_URL.'login.php');
    exit;
}

$user = $_SESSION['USER'];
$pdo = connectDB();


if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    // CSRF対策
    setToken();

} else {
    // CSRF対策
    checkToken();

    $item_column1 = $_POST['item_column1'];
    $item_column2 = $_POST['item_column2'];
    $item_column3 = $_POST['item_column3'];
    $item_column4 = $_POST['item_column4'];
    $item_column5 = $_POST['item_column5'];
    $item_column6 = $_POST['item_column6'];
    $item_column7 = $_POST['item_column7'];
    $item_column8 = $_POST['item_column8'];
    $item_column9 = $_POST['item_column9'];
    $item_column10 = $_POST['item_column10'];

    $sql = 'INSERT INTO item
            (column1, column2, column3,column4, column5, column6, column7, column8, column9, column10, created_at, created_by, updated_at, updated_by)
            VALUES
            (:column1, :column2, :column3, :column4, :column5, :column6, :column7, :column8, :column9, :column10, now(), :created_by, now(), :updated_by)';

    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(":column1" => $item_column1, ':column2' => $item_column2,
                        ":column3" => $item_column3, ':column4' => $item_column4,
                        ":column5" => $item_column5, ':column6' => $item_column6,
                        ":column7" => $item_column7, ':column8' => $item_column8,
                        ":column9" => $item_column9, ':column10' => $item_column10,
                        "created_by" => $user['id'], "updated_by" => $user['id']));

    header('Location:'.SITE_URL.'index.php');
}
?>


<?php include 'layouts/head.php'; ?>

<body id="main" class="bg-dark text-light">

    <?php include 'layouts/header.php'; ?>

    <div class="container">
        <h1><?php echo h($page_title); ?></h1>

        <div class="panel panel-default">
            <div class="panel-body">
                <form method="POST">

                    <!-- ユーザー情報 -->
                    <div class="row mt-2 border rounded text-light">

                            <div class="col-md-6 form-group">
                                <label for="">column1</label>
                                <input type="text" class="form-control" name="item_column1" value="">
                                <span class="text-danger"><?php echo h($err['item_column1']); ?></span>
                            </div>

                            <div class="col-md-6 form-group">
                                <label for="">column2</label>
                                <input type="text" class="form-control" name="item_column2" value="">
                                <span class="text-danger"><?php echo h($err['item_column2']); ?></span>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="">column3</label>
                                <input type="text" class="form-control" name="item_column3" value="">
                                <span class="text-danger"><?php echo h($err['item_column3']); ?></span>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="">column4</label>
                                <input type="text" class="form-control" name="item_column4" value="">
                                <span class="text-danger"><?php echo h($err['item_column4']); ?></span>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="">column5</label>
                                <input type="text" class="form-control" name="item_column5" value="">
                                <span class="text-danger"><?php echo h($err['item_column5']); ?></span>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="">column6</label>
                                <input type="text" class="form-control" name="item_column6" value="">
                                <span class="text-danger"><?php echo h($err['item_column6']); ?></span>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="">column7</label>
                                <input type="text" class="form-control" name="item_column7" value="">
                                <span class="text-danger"><?php echo h($err['item_column7']); ?></span>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="">column8</label>
                                <input type="text" class="form-control" name="item_column8" value="">
                                <span class="text-danger"><?php echo h($err['item_column8']); ?></span>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="">column9</label>
                                <input type="text" class="form-control" name="item_column9" value="">
                                <span class="text-danger"><?php echo h($err['item_column9']); ?></span>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="">column10</label>
                                <input type="text" class="form-control" name="item_column10" value="">
                                <span class="text-danger"><?php echo h($err['item_column10']); ?></span>
                            </div>

                    </div><!-- row -->


                    <!-- CSRF対策 -->
                    <input type="hidden" name="token" value="<?php echo h($_SESSION['sstoken']); ?>" />

                    <div class="form-group mt-3">
                        <input type="submit" value="登録" class="btn btn-primary btn-block">
                    </div>

                    <a class="btn btn-secondary" href="./index.php">戻る</a>　

                </form>
            </div>
        </div>

    </div>

    <?php include 'layouts/footer.php'; ?>

</body>
</html>
