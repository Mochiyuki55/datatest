<?php
require_once('config.php');
require_once('functions.php');

// レイアウト関連の変数
$page_title = 'item_upload';
$pdo = connectDB();
$items = array();

// itemテーブルから全データを取得
$sql = "select * from item";
$stmt = $pdo->query($sql);
foreach ($stmt->fetchAll() as $row) {
    array_push($items, $row);
}

// CSVデータ書き出し用の一時ファイルを準備
$temp = tmpfile();

// 取得したデータをループ
foreach ($items as $key => $item) {
    // 出力するデータの配列を作成
    $array = array($item['column1'], $item['column2']);

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
