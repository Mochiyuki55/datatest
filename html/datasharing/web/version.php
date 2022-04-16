<?php
require_once('config.php');
require_once('functions.php');

// レイアウト関連の変数
$page_title = 'バージョン管理';

// 認証処理
session_start();
if (!isset($_SESSION['USER'])) {
    header('Location: '.SITE_URL.'login.php');
    exit;
}

$user = $_SESSION['USER'];
$pdo = connectDb();

// 検索、ページネーション、ソートのパラメータがGETされた場合
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  setToken(); // CSRF 対策

  $version_array = array();
  $sql = "SELECT * from version ORDER BY created_at DESC";
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
      array_push($version_array, $row);
  }

} else {
  checkToken(); // CSRF 対策
  // 新規登録時
  $complete_msg = '';

  // 入力情報チェック
  $version = $_POST['version'];
  $log_content = $_POST['log_content'];
  // バリデーション
  $err = array();
  // エラーパターン
  if ($version == '') {
      $err['version'] = 'バージョン番号を入力して下さい。';
  }
  if ($log_content == '') {
      $err['log_content'] = '変更内容を入力して下さい。';
  }

  // エラーがない場合、更新処理を行い、セッションに保存する
  if(empty($err)){
      $sql = 'INSERT INTO version
              (version, log_content, created_at, updated_at)
              VALUES
              (:version, :log_content, now(), now())';
      $stmt = $pdo->prepare($sql);
      $stmt->execute(array(":version" => $version, ':log_content' => $log_content));

      // 操作ログを記録
      $action = $user['user_name'].'がバージョンを'.$version.'に更新しました。';
      $sql = 'INSERT INTO history
              (user_id, action, created_at, updated_at)
              VALUES
              (:user_id, :action, now(), now())';
      $stmt = $pdo->prepare($sql);
      $stmt->execute(array(":user_id" => $user['id'], ':action' => $action));

      $complete_msg =  'バージョンを更新しました。';

      header('Location: '.SITE_URL.'./version.php');
  }
}
unset($pdo);

?>

<?php include 'layouts/head.php'; ?>

<body id="main" class="bg-dark text-light">

    <?php include 'layouts/header.php'; ?>

    <div class="container">
        <h1><?php echo h($page_title); ?></h1>

        <?php if($user['user_auth'] == 1): ?>
        <form method="POST">
            <!-- ユーザー情報 -->
            <div class="row mt-2 text-light">
                <div class="col-md-6 form-group <?php if ($err['version'] != '') echo 'has-error'; ?>">
                    <label for="">バージョン番号</label>
                    <input type="text" class="form-control" name="version" value="">
                    <span class="text-danger"><?php echo h($err['version']); ?></span>
                </div>
                <div class="col-md-6 form-group <?php if ($err['log_content'] != '') echo 'has-error'; ?>">
                    <label for="">変更履歴</label>
                    <input type="text" class="form-control" name="log_content" value="">
                    <span class="text-danger"><?php echo h($err['log_content']); ?></span>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-6">
                </div>
                <div class="col-md-6 text-right">
                    <!-- CSRF対策 -->
                    <input type="hidden" name="token" value="<?php echo h($_SESSION['sstoken']); ?>" />
                    <input class="btn btn-primary" type="submit" name="" value="新規登録">
                </div>
            </div>
        </form>
        <?php endif; ?>

        <div class=" mt-5 panel panel-default">
            <div class="panel-body">

                <?php if($version_array): ?>
                <table class="table table-bordered table-striped bg-light">
        			<thead>
                        <tr>
                            <th>バージョン番号</th>
                            <th>変更履歴</th>
                            <th>変更日時</th>
                        </tr>
        			</thead>

        			<?php foreach ($version_array as $version): ?>
        				<tr>
        					<td><?php echo h($version['version']);?></td>
                            <td><?php echo h($version['log_content']);?></td>
                            <td><?php echo h($version['created_at']);?></td>

                            <?php if($user['user_auth'] == 1): ?>
        					<td><a href="./version_edit.php?id=<?php echo h($version['id']); ?>" class="btn btn-secondary">編集</a>　
                                <a href="javascript:void(0);" class="btn btn-danger" onclick="var ok=confirm('削除しても宜しいですか?'); if (ok) location.href='version_delete.php?id=<?php echo h($version['id']); ?>'; return false;">削除</a>
                            </td>
                            <?php endif; ?>
        				</tr>
        			<?php endforeach;?>
        		</table>
                <?php endif; ?>

            </div>
        </div>

    </div>

    <?php include 'layouts/footer.php'; ?>

</body>
</html>
