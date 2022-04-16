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

$pdo = connectDb();
$id = $_GET['id'];

$sql = "SELECT * FROM version WHERE id = :id LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute(array(":id" => $id));
$version = $stmt->fetch();

$sql = "DELETE FROM version WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(array(":id" => $id));

// 操作ログを記録
$action = $user['user_name'].'がユーザー「'.$version['version'].'」の情報を削除しました。';
$sql = 'INSERT INTO history
        (user_id, action, created_at, updated_at)
        VALUES
        (:user_id, :action, now(), now())';
$stmt = $pdo->prepare($sql);
$stmt->execute(array(":user_id" => $user['id'], ':action' => $action));

unset($pdo);

// item_list.phpに画面遷移する。
header('Location: '.SITE_URL.'./version.php');

?>
