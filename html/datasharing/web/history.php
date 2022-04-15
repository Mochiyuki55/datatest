<?php
require_once('config.php');
require_once('functions.php');

// レイアウト関連の変数
$page_title = '操作ログ';

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

  // ページネーション処理
  $page = $_GET['page'];
  // 正規表現でパラメーターが数値かどうかのチェックを行う
  if (preg_match('/^[1-9][0-9]*$/', $_GET['page'])) {
  	// 正規表現にマッチしたらパラメーターをそのまま受け取る
  	$page = $_GET['page'];
  } else {
  	// 数値以外のパラメーターが渡されたら強制的に1にする
  	$page = 1;
  }
  $offset = PAGE_COUNT * ($page -1);

  // ソートに関するホワイトリスト照合(セキュリティ関係)
  $s = $_GET['s']; // どのカラムのソートなのか
  $o = $_GET['o']; // 順か逆順か
  $sort_whitelist = array('action' => 'action',
                          'actor' => 'actor',
                          'created_at' => 'created_at');
  $sort_safe = isset($sort_whitelist[$s]) ? $sort_whitelist[$s] : $sort_whitelist['created_at'];
  $order_whitelist = array('asc' => 'asc', 'desc' => 'desc');
  $order_safe = isset($order_whitelist[$o]) ? $order_whitelist[$o] : $order_whitelist['asc'];

  // データリストを取得する
  $log_array = array();
  $sql = "select * from history order by $sort_safe $order_safe limit :offset, :count";
  $stmt = $pdo->prepare($sql);
  $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
  $stmt->bindValue(':count', PAGE_COUNT, PDO::PARAM_INT);
  $stmt->execute();
  foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
      array_push($log_array,$row);
  }

  // 総データ件数を取得
  $sql2 = "select count(*) from history";
  $stmt2 = $pdo->prepare($sql2);
  $stmt2->execute();
  $total = $stmt2->fetchColumn();

  $total_page = ceil($total / PAGE_COUNT);

} else {
  checkToken(); // CSRF 対策
  // POSTされるのは実質、データダウンロードボタンを押した場合のみ

  // バリデーション
  $err = array();
  // エラーパターン

  // エラーがない場合、更新処理を行い、セッションに保存する
  if(empty($err)){
  }
}
unset($pdo);

?>

<?php include 'layouts/head.php'; ?>

<body id="main" class="bg-dark text-light">

    <?php include 'layouts/header.php'; ?>

    <div class="container">
        <h1><?php echo h($page_title); ?></h1>
        <p>操作ログリストです。</p>

		<table class="table table-bordered table-striped bg-light">
			<thead>
                <?php
                if ($o == "desc") {
                    $arrow_icon = "glyphicon-arrow-down";
                    $order = "asc";
                } else {
                    $arrow_icon = "glyphicon-arrow-up";
                    $order = "desc";
                }
                $thead_array = array('action' => '操作内容', 'actor' => '操作者', 'created_at' => '操作日時');
                ?>

                <?php if($_GET['o'] || $_GET['s']): ?>
                <tr>
                    <?php foreach ($sort_whitelist as $column): ?>
                        <!-- ソートに選択されているカラムについて -->
                        <?php if ($column == $s): ?>
                            <th><a href="?s=<?php echo h($column); ?>&o=<?php echo h($order); ?>"><?php echo h($thead_array[$column]); ?><span class="glyphicon <?php echo h($arrow_icon); ?>"></span></a></th>
                        <!-- ソートに選択されていないカラムについて -->
                        <?php else: ?>
                            <th><a href="?s=<?php echo h($column); ?>&o=<?php echo h($order); ?>"><?php echo h($thead_array[$column]); ?></a></th>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tr>
                <?php else: ?>
                <!-- クエリがなければ普通に表示 -->
                <tr>
                    <th>操作内容</th>
                    <th>操作者</th>
                    <th>操作日時</th>
                </tr>
                <?php endif; ?>

			</thead>
			<?php foreach ($log_array as $log): ?>
				<tr>
					<td><?php echo h($log['action']);?></td>
                    <td><?php if($log['actor'] == '0'){echo '一般';}else{echo '管理者';}?></td>
                    <td><?php echo h($log['created_at']);?></td>
				</tr>
			<?php endforeach;?>

		</table>
        <div class="row">
            <div class="col-md-6">
                <nav aria-label="Page navigation example">
                    <ul class="pagination">
                        <?php if ($page > 1): ?>
                          <!-- ページパラメーターを指定し、前のページに戻れるようにする -->
                          <li class="page-item"><a class="page-link" href="?page=<?php echo $page-1; ?>">&laquo;</a></li>
                        <?php else: ?>
                          <!-- 1ページ目を表示している場合は非アクティブ（class="disabled"）にする -->
                          <li class="disabled page-item"><a class="page-link" href="#">&laquo;</a></li>
                        <?php endif; ?>

                        <!-- 存在するページ番号のみを表示する -->
                        <?php for ($i = 1; $i <= $total_page; $i++) : ?>
                            <?php if ($page == $i): ?>
                              <!-- 表示しているページと同じ番号の場合は強調表示（class="active"）する -->
                              <li class="active page-item"><a class="page-link" href="#"><?php echo $i; ?></a></li>
                            <?php else: ?>
                              <!-- ページパラメーターを指定し、指定ページに遷移出来るようにする -->
                              <li class="page-item"><a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($page < $total_page): ?>
                          <!-- ページパラメーターを指定し、次のページに進めるようにする -->
                          <li class="page-item"><a class="page-link" href="?page=<?php echo $page+1; ?>">&raquo;</a></li>
                        <?php else: ?>
                          <!-- 最終ページを表示している場合は非アクティブ（class="disabled"）にする -->
                          <li class="disabled page-item"><a class="page-link" href="#">&raquo;</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>

    </div>

    <?php include 'layouts/footer.php'; ?>

</body>
</html>
