<?php
require_once('config.php');
require_once('functions.php');
session_start();

// ログインチェック
if (!isset($_SESSION['USER'])) {
  header('Location: '.SITE_URL.'login.php');
  exit;
}
// セッションからユーザ情報を取得
$user = $_SESSION['USER'];

$pdo = connectDB();
$items = array();

// itemテーブルから全データを取得
$sql = "select * from item";
$stmt = $pdo->query($sql);
foreach ($stmt->fetchAll() as $row) {
    array_push($items, $row);
}

// 操作ログを記録
$action = $user['user_name'].'がデータをエクスポートしました。';
$sql = 'INSERT INTO history
        (user_id, action, created_at, updated_at)
        VALUES
        (:user_id, :action, now(), now())';

$stmt = $pdo->prepare($sql);
$stmt->execute(array(":user_id" => $user['id'], ':action' => $action));

// CSVデータ書き出し用の一時ファイルを準備
$temp = tmpfile();

// 取得したデータをループ
foreach ($items as $key => $item) {
    // 出力するデータの配列を作成
    $array = array($item['column1'], $item['column2'],
                $item['column3'], $item['column4'],
                $item['column5'], $item['column6'],
                $item['column7'], $item['column8'],
                $item['column9'], $item['column10']);

    // 作成した配列をCSV形式で一時ファイルに出力
    fputcsv($temp, $array);
}


unset($pdo);

// レスポンスヘッダー（MIMEタイプ）の設定
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=data.csv");

// 一時ファイルの情報を取得
$meta = stream_get_meta_data($temp);

// 一時ファイルの内容を出力
readfile($meta['uri']);

// 一時ファイルクローズ
fclose($temp);


?>
