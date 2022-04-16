<?php
require_once('config.php');
require_once('functions.php');

// レイアウト関連の変数
$page_title = 'ユーザー情報変更';

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
    $sql = 'SELECT * FROM user WHERE id = :id LIMIT 1';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(':id' => $id));
    $user2 = $stmt->fetch(PDO::FETCH_ASSOC);

    $user_name = $user2['user_name'];
    $user_password = $user2['user_password'];
    $user_auth = $user2['user_auth'];
    $user_item_num = $user2['item_num'];

} else {
    // CSRF対策
    checkToken();


        $complete_msg = '';

        // 入力情報チェック
        $user_name = $_POST['user_name'];
        $user_password = $_POST['user_password'];
        $user_auth = $_POST['user_auth'];
        $user_item_num = $_POST['user_item_num'];

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
            $sql = 'UPDATE user
                    SET user_name = :user_name ,user_password = :user_password,
                    user_auth = :user_auth, item_num = :item_num, updated_at = now()
                    where id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array(":user_name" => $user_name, ':user_password' => $user_password,
                                ":user_auth" => $user_auth, 'item_num' => $user_item_num, ':id' => $id));
            // $result = $stmt->errorInfo();

            // 操作ログを記録
            $action = $user['user_name'].'がユーザー「'.$user_name.'」の情報を編集しました。';
            $sql = 'INSERT INTO history
                    (user_id, action, created_at, updated_at)
                    VALUES
                    (:user_id, :action, now(), now())';

            $stmt = $pdo->prepare($sql);
            $stmt->execute(array(":user_id" => $user['id'], ':action' => $action));

            $complete_msg =  'ユーザー変更が完了しました。';
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
                                <input type="text" class="form-control" name="user_name" value="<?php echo h($user_name); ?>">
                                <span class="text-danger"><?php echo h($err['user_name']); ?></span>
                            </div>
                        </div>

                        <div class="row mt-2 text-light">
                            <div class="col form-group <?php if ($err['user_password'] != '') echo 'has-error'; ?>">
                                <label for="">パスワード</label>
                                <input type="text" class="form-control" name="user_password" value="<?php echo h($user_password); ?>">
                                <span class="text-danger"><?php echo h($err['user_password']); ?></span>
                            </div>
                        </div>

                        <div class="row mt-2 text-light">
                            <div class="col form-group <?php if ($err['user_auth'] != '') echo 'has-error'; ?>">
                                <label for="">アクセス権限</label>
                                <select class="form-control" name="user_auth">
                                    <option value="0">一般</option>
                                    <option value="1">管理者</option>
                                </select>
                                <span class="text-danger"><?php echo h($err['user_auth']); ?></span>
                            </div>
                        </div>

                        <div class="row mt-2 text-light">
                            <div class="col form-group <?php if ($err['user_item_num'] != '') echo 'has-error'; ?>">
                                <label for="">表示件数</label>
                                <select class="form-control" name="user_item_num">
                                    <option value="5" <?php if($user_item_num == 5){echo "selected";} ?>>5件</option>
                                    <option value="10" <?php if($user_item_num == 10){echo  "selected";} ?>>10件</option>
                                    <option value="15" <?php if($user_item_num == 15){echo  "selected";} ?>>15件</option>
                                    <option value="20" <?php if($user_item_num == 20){echo  "selected";} ?>>20件</option>
                                    <option value="25" <?php if($user_item_num == 25){echo  "selected";} ?>>25件</option>
                                    <option value="30" <?php if($user_item_num == 30){echo  "selected";} ?>>30件</option>
                                </select>
                                <span class="text-danger"><?php echo h($err['user_item_num']); ?></span>
                            </div>
                        </div>

                    <!-- CSRF対策 -->
                    <input type="hidden" name="token" value="<?php echo h($_SESSION['sstoken']); ?>" />

                    <div class="form-group mt-3">
                        <input type="submit" value="変更" class="btn btn-success btn-block">
                    </div>

                    <a class="btn btn-secondary" href="./user_list.php?s=user_name&o=desc">戻る</a>　

                </form>
            </div>
        </div>

    </div>

    <?php include 'layouts/footer.php'; ?>

</body>
</html>
