<?php
require_once('config.php');
require_once('functions.php');

// レイアウト関連の変数
$page_title = 'item_upload';

$pdo = connectDB();

if ($_SERVER['REQUEST_METHOD'] = 'POST') {
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

        // カラムが複数の場合
        if (count($data_array) >= 2) {
            for ($i = 0; $i < count($row); $i++) {
                // 文字数チェック
                if (strlen(mb_convert_encoding($data_array[$i], 'SJIS', 'UTF-8')) > 200) {
                    // 文字数オーバーの場合は空で登録
                    $column_data[$i+1] = "";
                } else {
                    $column_data[$i+1] = $data_array[$i];
                }
            }

                // データ新規登録
            foreach ($data_array as $data) {
                $column1 = $data[1];
                $column2 = $data[2];
                $sql = 'INSERT INTO
                        item (column1, column2, created_at, updated_at)
                        VALUES
                        (:column1, :column2,  now(),  now())';
                $stmt = $pdo->prepare($sql);
                $params = array(":column1" => $column1, ":column2" => $column2);
                $stmt->execute($params);
            }
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

        <form method="POST" enctype="multipart/form-data">
        	<input type="hidden" name="MAX_FILE_SIZE" value="1048576" />
        	<label>CSVファイルを指定して下さい。</label><br>
        	<input type="file" name="upload_file" /> <br>
        	<input type="submit" value="アップロード">
        </form>


    </div>
    <?php include 'layouts/footer.php'; ?>

</body>
</html>
