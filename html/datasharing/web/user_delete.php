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

$sql = "DELETE FROM user WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(array(":id" => $id));


unset($pdo);

// item_list.phpに画面遷移する。
header('Location: '.SITE_URL.'./user_list.php?s=user_name&o=desc');

?>
