<?php
require_once('config.php');
require_once('functions.php');

// レイアウト関連の変数
$page_title = 'バージョン変更';

// 認証処理
session_start();
if (!isset($_SESSION['USER'])) {
    header('Location: '.SITE_URL.'login.php');
    exit;
}
$user = $_SESSION['USER'];

$pdo = connectDB();
$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    // CSRF対策
    setToken();
    // 対象ユーザーのデータを取得
    $sql = 'SELECT * FROM version WHERE id = :id LIMIT 1';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(':id' => $id));
    $version = $stmt->fetch(PDO::FETCH_ASSOC);

    $version_num = $version['version'];
    $log_content = $version['log_content'];

} else {
    // CSRF対策
    checkToken();


        $complete_msg = '';

        // 入力情報チェック
        $version_num = $_POST['version_num'];
        $log_content = $_POST['log_content'];

        // 入力チェックを行う。
        $err = array();

        // [氏名]未入力チェック
        if ($version_num== '') {
            $err['version_num'] = 'バージョン番号を入力して下さい。';
        }

        // [パスワード]未入力チェック
        if ($log_content == '') {
            $err['log_content'] = '変更内容を入力して下さい。';
        }

        if (empty($err)) {
            $sql = 'UPDATE version
                    SET version = :version ,log_content = :log_content, updated_at = now()
                    where id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array(":version" => $version_num, ':log_content' => $log_content, ':id' => $id));
            // $result = $stmt->errorInfo();

            // 操作ログを記録
            $action = $user['user_name'].'がバージョン「'.$version_num.'」を編集しました。';
            $sql = 'INSERT INTO history
                    (user_id, action, created_at, updated_at)
                    VALUES
                    (:user_id, :action, now(), now())';

            $stmt = $pdo->prepare($sql);
            $stmt->execute(array(":user_id" => $user['id'], ':action' => $action));

            $complete_msg =  'バージョンの内容変更が完了しました。';
        }
}
?>

<?php include 'layouts/head.php'; ?>

<body id="main" class="bg-dark text-light">

    <?php include 'layouts/header.php'; ?>

    <div class="container">
        <h1><?php echo h($page_title); ?></h1>

        <div class="panel panel-default">
            <div class="panel-body">

                <?php if ($complete_msg): ?>
                    <div class="alert alert-success">
                        <?php echo $complete_msg; ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <!-- ユーザー情報 -->
                        <div class="row mt-2 text-light">
                            <div class="col form-group <?php if ($err['version_num'] != '') echo 'has-error'; ?>">
                                <label for="">バージョン番号</label>
                                <input type="text" class="form-control" name="version_num" value="<?php echo h($version_num); ?>">
                                <span class="text-danger"><?php echo h($err['version_num']); ?></span>
                            </div>
                        </div>

                        <div class="row mt-2 text-light">
                            <div class="col form-group <?php if ($err['log_content'] != '') echo 'has-error'; ?>">
                                <label for="">変更内容</label>
                                <input type="text" class="form-control" name="log_content" value="<?php echo h($log_content); ?>">
                                <span class="text-danger"><?php echo h($err['log_content']); ?></span>
                            </div>
                        </div>

                    <!-- CSRF対策 -->
                    <input type="hidden" name="token" value="<?php echo h($_SESSION['sstoken']); ?>" />

                    <div class="form-group mt-3">
                        <input type="submit" value="変更" class="btn btn-success btn-block">
                    </div>

                    <a class="btn btn-secondary" href="./version.php">戻る</a>　

                </form>
            </div>
        </div>

    </div>

    <?php include 'layouts/footer.php'; ?>

</body>
</html>
