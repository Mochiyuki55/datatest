<?php
require_once('config.php');
require_once('functions.php');

// レイアウト関連の変数
$page_title = 'データ一括登録';
// 認証処理
session_start();
if (!isset($_SESSION['USER'])) {
    header('Location: '.SITE_URL.'login.php');
    exit;
}
$user = $_SESSION['USER'];
$pdo = connectDB();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // -----------------------------------------------------
    //      CSVファイルの読み込み処理
    // -----------------------------------------------------
    // 入力チェック用配列
    $err = array();
    // 完了メッセージ用変数
    $complete_msg = "";
    // 完了メッセージに登録データ数を表示するためのカウント
    $data_count = 0;

    // CSVファイル未選択チェック
    if (!$_FILES['upload_file']['tmp_name']) {
        $err['upload_file'] = 'アップロードするCSVファイルを選択して下さい。';
    }
    if (empty($err)) {

        // アップロードされたファイルを文字列として読み込みます。
        $data = file_get_contents($_FILES['upload_file']['tmp_name']);

        // 日本語の文字コードをUTF-8に変換します。
        mb_language("Japanese");
        $data = mb_convert_encoding($data, 'UTF-8', 'auto');

        // 文字コード変換したデータを再度CSVファイルとして書き出します。
        $temp = tmpfile();
        fwrite($temp, $data);
        rewind($temp);

        // CSVファイルをfgetcsvを使って配列に書き出します。
        $data_array = array();
        while (($data = fgetcsv($temp, 0, ",")) !== FALSE) {
        	$data_array[] = $data;
        }

        // CSVファイルをクローズします。
        fclose($temp);

        // ヘッダー削除処理
        if(isset($_POST['csv_header'])){
            // 一番最初の行（ヘッダー）
            unset($data_array[0]);
        }
        // カラムが複数の場合
        if (count($data_array) >= 2) {
            for ($i = 0; $i < count($data_array); $i++) {
                // 文字数チェック
                if (strlen(strval(mb_convert_encoding($data_array[$i], 'SJIS', 'UTF-8'))) > 200) {
                    // 文字数オーバーの場合は空で登録
                    $column_data[$i+1] = "";
                } else {
                    $column_data[$i+1] = $data_array[$i];
                }
            }

                // データ新規登録
            foreach ($data_array as $data) {
                // 各データのカラムデータを取得
                $column1 = $data[0];
                $column2 = $data[1];
                $column3 = $data[2];
                $column4 = $data[3];
                $column5 = $data[4];
                $column6 = $data[5];
                $column7 = $data[6];
                $column8 = $data[7];
                $column9 = $data[8];
                $column10 = $data[9];

                $sql = 'INSERT INTO
                        item (column1, column2, column3, column4, column5, column6, column7, column8, column9, column10, created_at, created_by, updated_at, updated_by)
                        VALUES
                        (:column1, :column2, :column3, :column4, :column5, :column6, :column7, :column8, :column9, :column10, now(), :created_by, now(), :updated_by)';
                $stmt = $pdo->prepare($sql);
                $params = array(":column1" => $column1,
                                ":column2" => $column2,
                                ":column3" => $column3,
                                ":column4" => $column4,
                                ":column5" => $column5,
                                ":column6" => $column6,
                                ":column7" => $column7,
                                ":column8" => $column8,
                                ":column9" => $column9,
                                ":column10" => $column10,
                                ":created_by" => 1,
                                ":updated_by" => 1,);
                $stmt->execute($params);
                $result = $stmt->errorInfo();
            }

            // 操作ログを記録
            $action = $user['user_name'].'がデータを一括登録しました。';
            $sql = 'INSERT INTO history
                    (user_id, action, created_at, updated_at)
                    VALUES
                    (:user_id, :action, now(), now())';

            $stmt = $pdo->prepare($sql);
            $stmt->execute(array(":user_id" => $user['id'], ':action' => $action));

            // 完了メッセージを設定
           $complete_msg = count($data_array)."件のデータが登録されました。";
        }
    }
}

?>

<?php include 'layouts/head.php'; ?>

<body id="main" class="bg-dark text-light">

    <?php include 'layouts/header.php'; ?>

    <div class="container">
        <h1><?php echo h($page_title); ?></h1>

        <?php if($complete_msg): ?>
            <div class="row alert alert-success">
                <p> <?php echo $complete_msg; ?></p>
            </div>
        <?php endif; ?>
        <?php if(!empty($err)): ?>
            <div class="row alert alert-warning">
                <p> <?php echo $err['upload_file']; ?></p>
            </div>
        <?php endif; ?>

        <div class="row">

            <form class="form" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="MAX_FILE_SIZE" value="1048576" />
                <div class="form-group">
                    <label>CSVファイルを指定して下さい。</label> <br>
                    <input type="file" name="upload_file" />
                </div>
                <div class="form-group">
                    <input id= 'input' type="checkbox" name="csv_header" value="1">
                    <label for="input">１行目をヘッダ行として処理する</label>

                </div>

                <!-- CSRF対策 -->
                <input type="hidden" name="token" value="<?php echo h($_SESSION['sstoken']); ?>" />

                <div class="form-group">
                    <input class="btn btn-primary" type="submit" value="アップロード">
                </div>
            </form>

        </div>
        <div class="">
            <?php echo print_r($data_array); ?>
        </div>
    </div>
    <?php include 'layouts/footer.php'; ?>

</body>
</html>
