<?php
error_reporting(E_ALL & ~E_NOTICE);

// プロジェクト名
$project_name = 'datasharing';

// ローカル環境
  define('HOST_NAME','mysql');
  define('DATABASE_USER_NAME','root'); //rootで固定
  define('DATABASE_PASSWORD','root'); //rootパスワード
  define('DATABASE_NAME',$project_name);
  define('SITE_URL', 'http://localhost:8080/datasharing/web/');

// サーバーが変わったときは以下の設定を変更するだけで良い
  // define('HOST_NAME','mysql57.limesnake4.sakura.ne.jp');
  // define('DATABASE_USER_NAME','limesnake4');
  // define('DATABASE_PASSWORD','Yaguchi88');
  // define('DATABASE_NAME','limesnake4_'.$project_name);
  // define('SITE_URL', 'https://limesnake4.sakura.ne.jp/'.$project_name.'/web/');


// メールフォーム
define('ADMIN_EMAIL', 'yaguchi1061@gmail.com');

// アプリタイトル
define('TITLE', 'DataSharing');

// コピーライト
define('COPY_RIGHT', '&copy; Mochiyuki55');

// ページカウント
define('PAGE_COUNT', 10);

// ソート
define('ARRAY_USER_LIST',array(
    '0' => 'ユーザー名',
    '1' => 'mochiyuki',
    '2' => 'bbbb',
    '3' => 'cccc',
    '4' => 'dddd',
));

?>
