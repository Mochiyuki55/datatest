<?php
require_once('config.php');
require_once('functions.php');

// レイアウト関連の変数
$page_title = 'ログイン画面';

session_start();
$pdo = connectDb();

// ユーザーリストを配列に取得
$user_array = array('99' => 'ユーザー名');
$sql = "SELECT * FROM user";
$stmt = $pdo->prepare($sql);
$stmt->execute();
foreach ($stmt->fetchall(PDO::FETCH_ASSOC) as $row) {
    $user_array[$row['user_name']] = $row['user_name'];
    // array_push($user_array, $value['user_name']);
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  // ログイン画面を表示する前にまずCookieがあるかをチェックする。
  if(isset($_COOKIE['DATASHARING'])){ // Cookieがある場合
    $auto_login_key = $_COOKIE['DATASHARING'];

    $sql = "SELECT * FROM auto_login WHERE c_key = :c_key AND expire >= :expire LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(":c_key" => $auto_login_key, ":expire" => date('Y-m-d H:i:s')));
    $row = $stmt->fetch();

  // DBにも存在しており、かつ有効期間内であれば認証OKとみなして自動ログインさせる。
    if ($row){
      // 照合成功、セッションにユーザー情報を入れる(自動ログイン)
      $user = getUserbyUserId($row['user_id'], $pdo);
      // セッションハイジャック対策
      session_regenerate_id(true);
      // 登録したユーザー情報をセッションに保存
      $_SESSION['USER'] = $user;
      unset($pdo);
      header('Location:'.SITE_URL.'index.php');
      exit;
    }
  }

  setToken(); // CSRF 対策

} else {
  checkToken(); // CSRF 対策

  // 入力データを変数に格納する
  $user_name = $_POST['user_name'];         // メールアドレス
  $user_password = $_POST['user_password'];   // パスワード
  $auto_login = $_POST['auto_login'];         // 自動ログイン

  // DBに接続する
  $pdo = connectDb();

  // エラーチェック
  $err = array();

  // [ユーザー名]未入力チェック
  if ($user_name == '99') {
      $err['user_name'] = 'ユーザーを選択して下さい。';
  }else{
      // パスワード不正チェック
      $sql = "SELECT * FROM user WHERE user_name = :user_name AND user_password = :user_password LIMIT 1";
      $stmt = $pdo->prepare($sql);
      $stmt->execute(array(":user_name" => $user_name, ":user_password" => $user_password));
      $user = $stmt->fetch();
      if(!$user){
          $err['user_name'] = 'パスワードが間違っています。';
      }
  }
  // [パスワード]未入力チェック
  if ($user_password == '') {
      $err['user_password'] = 'パスワードを入力して下さい。';
  }


    // もし$err配列に何もエラーメッセージが保存されていなかったら
    if (empty($err)) {
      // セッション変数にログイン状態を書き込む前に、セッションハイジャック対策
      session_regenerate_id(true);

      //
      $sql = "SELECT * FROM user WHERE user_name = :user_name AND user_password = :user_password LIMIT 1";
      $stmt = $pdo->prepare($sql);
      $stmt->execute(array(":user_name" => $user_name, ":user_password" => $user_password));
      $user = $stmt->fetch(PDO::FETCH_ASSOC);

      // ログインに成功したのでセッションにユーザデータを保存する
      $_SESSION['USER'] = $user;

      // 自動ログイン情報を一度クリアする。
      if (isset($_COOKIE['DATASHARING'])) {
          $auto_login_key = $_COOKIE['DATASHARING'];

          // Cookie情報をクリア
          setcookie('DATASHARING', '', time()-86400, COOKIE_PATH);

          // DB情報をクリア
          $sql = "DELETE FROM auto_login WHERE c_key = :c_key";
          $stmt = $pdo->prepare($sql);
          $stmt->execute(array(":c_key" => $auto_login_key));
      }

              // チェックボックスにチェックが入っていた場合
              if ($auto_login) {

                  // 自動ログインキーを生成
                  $auto_login_key = sha1(uniqid(mt_rand(), true));

                  // Cookie登録処理
                  setcookie('DATASHARING', $auto_login_key, time()+3600*24*365, COOKIE_PATH);
                  // DB登録処理
                  $sql = "INSERT INTO auto_login (user_id, c_key, expire, created_at, updated_at)
                  VALUES (:user_id, :c_key, :expire, now(), now())";
                  $stmt = $pdo->prepare($sql);
                  $params = array(":user_id" => $user['id'], ":c_key" => $auto_login_key, ":expire" => date('Y-m-d H:i:s', time()+3600*24*365));
                  $stmt->execute($params);
              }

              // HOME画面に遷移する。
              unset($pdo);
              header('Location:'.SITE_URL.'./index.php');
              exit;
          }

          unset($pdo);
      }
?>

<?php include 'layouts/head.php'; ?>

  <body class="bg-dark text-center">

    <div class="container pt-5">
        <h1 class="mt-5 font-weight-bold text-white"> <?php echo TITLE; ?> </h1>
        <h2 class="text-light">みんなのデータをブラウザで共有</h2>

            <form class="form" method="POST" >
              <div class="form-group">

                <select class="form-control" name="user_name">
                    <?php foreach ($user_array as $name): ?>
                        <option value="<?php echo h($name); ?>"><?php echo h($name); ?></option>
                    <?php endforeach; ?>

                </select>

                <span class="text-danger"><?php echo h($err['user_name']); ?></span>
              </div>

              <div class="form-group">
                <input type="password" class="form-control" name="user_password" value="" placeholder="パスワード">
                <span class="text-danger"><?php echo h($err['user_password']); ?></span>
              </div>

              <div class="form-group text-center text-white">
                <label for="auto_login">
                  <input id="auto_login" type="checkbox" name="auto_login"> 次回から自動でログイン
                </label>
              </div>

              <div class="form-group">
                <input type="submit" value="ログイン" class="btn btn-primary btn-block">
              </div>
                            
              <!-- CSRF対策：index.phpがPOSTされて遷移してきた場合、次のphpにPOSTする際はトークンを引き継ぐ必要がある-->
              <input type="hidden" name="token" value="<?php echo h($_SESSION['sstoken']); ?>" />

            </form>

    </div><!-- container -->

    <?php include 'layouts/footer.php'; ?>

  </body>
</html>
