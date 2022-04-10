<?php
require_once('config.php');
require_once('functions.php');

// レイアウト関連の変数
$page_title = 'item_edit';

$pdo = connectDB();
$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    // 対象ユーザーのデータを取得
    $sql = 'SELECT * FROM item WHERE id = :id LIMIT 1';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(':id' => $id));
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    $item_column1 = $item['column1'];
    $item_column2 = $item['column2'];
    $item_created = $item['created_at'];
    $item_updated = $item['updated_at'];

}else{

    $item_column1 = $_POST['item_column1'];
    $item_column2 = $_POST['item_column2'];

    $sql = 'UPDATE item
            set column1 = :column1, column2 = :column2, updated_at = now()
            where id = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(":column1" => $item_column1, ':column2' => $item_column2, ':id' => $id));

    header('Location:'.SITE_URL.'item_list.php');
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
                        <div class="col pt-3">

                            <div class="form-group">
                                <label for="">column1</label>
                                <input type="text" class="form-control" name="item_column1" value="<?php echo h($item_column1); ?>">
                                <span class="text-danger"><?php echo h($err['item_column1']); ?></span>
                            </div>

                            <div class="form-group">
                                <label for="">column2</label>
                                <input type="text" class="form-control" name="item_column2" value="<?php echo h($item_column2); ?>">
                                <span class="text-danger"><?php echo h($err['item_column2']); ?></span>
                            </div>

                            <div class="form-group">
                                <label for="">created_at</label>
                                <p><?php echo h($item_created); ?></p>
                            </div>

                            <div class="form-group">
                                <label for="">updated_at</label>
                                <p><?php echo h($item_updated); ?></p>
                            </div>

                        </div>
                    </div><!-- row -->



                    <div class="form-group mt-3">
                        <input type="submit" value="登録" class="btn btn-success btn-block">
                    </div>

                    <a class="btn btn-secondary" href="./item_list.php">戻る</a>　

                </form>
            </div>
        </div>



    </div>

    <?php include 'layouts/footer.php'; ?>

</body>
</html>
