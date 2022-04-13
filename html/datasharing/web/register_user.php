<?php
require_once('config.php');
require_once('functions.php');

// レイアウト関連の変数
$page_title = 'ユーザー登録';
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

    $complete_msg = '';

    $user_name = $_POST['user_name'];
    $user_password = $_POST['user_password'];
    $user_auth = $_POST['user_auth'];


    // 入力チェックを行う。
    $err = array();

    // [氏名]未入力チェック
    if ($user_name == '') {
        $err['user_name'] = 'ユーザー名を入力して下さい。';
    }

    if (strlen(mb_convert_encoding($user_name, 'SJIS', 'UTF-8')) > 30) {
        $err['user_name'] = 'ユーザー名は30バイト以内で入力して下さい。';
    }

    // [パスワード]未入力チェック
    if ($user_password == '') {
        $err['user_password'] = 'パスワードを入力して下さい。';
    }

    if (strlen(mb_convert_encoding($user_password, 'SJIS', 'UTF-8')) > 30) {
        $err['user_password'] = 'パスワードは30バイト以内で入力して下さい。';
    }

    if (empty($err)) {
        $sql = 'INSERT INTO user
                (user_name, user_password, user_auth, created_at, updated_at)
                VALUES
                (:user_name, :user_password, :user_auth, now(), now())';
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(":user_name" => $user_name, ':user_password' => $user_password, ":user_auth" => $user_auth));
        // $result = $stmt->errorInfo();

        $complete_msg =  'ユーザー登録が完了しました。';
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
                            <div class="col form-group <?php if ($err['user_name'] != '') echo 'has-error'; ?>">
                                <label for="">ユーザー名</label>
                                <input type="text" class="form-control" name="user_name" value="">
                                <span class="text-danger"><?php echo h($err['user_name']); ?></span>
                            </div>
                        </div>

                        <div class="row mt-2 text-light">
                            <div class="col form-group <?php if ($err['user_password'] != '') echo 'has-error'; ?>">
                                <label for="">パスワード</label>
                                <input type="text" class="form-control" name="user_password" value="">
                                <span class="text-danger"><?php echo h($err['user_password']); ?></span>
                            </div>
                        </div>

                        <div class="row mt-2 text-light">
                            <div class="col form-group <?php if ($err['user_auth'] != '') echo 'has-error'; ?>">
                                <label for="">権限</label>
                                <select class="form-control" name="user_auth">
                                    <option value="0">一般</option>
                                    <option value="1">管理者</option>
                                </select>
                                <span class="text-danger"><?php echo h($err['user_auth']); ?></span>
                            </div>
                        </div>


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
