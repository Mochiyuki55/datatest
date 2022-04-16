<?php
require_once('config.php');
require_once('functions.php');
// レイアウト関連の変数
$page_title = 'データリスト';

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

  // クエリ取得
  $search_query = $_GET['q'];


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
  $user = getUserbyUserId($user['id'], $pdo);
  $offset = $user['item_num'] * ($page -1);

  // ソートに関するホワイトリスト照合(セキュリティ関係)
  $s = $_GET['s']; // どのカラムのソートなのか
  $o = $_GET['o']; // 順か逆順か
  $sort_whitelist = array('column1' => 'column1',
                          'column2' => 'column2',
                          'column3' => 'column3',
                          'column4' => 'column4',
                          'column5' => 'column5',
                          'column6' => 'column6',
                          'column7' => 'column7',
                          'column8' => 'column8',
                          'column9' => 'column9',
                          'column10' => 'column10');
  $sort_safe = isset($sort_whitelist[$s]) ? $sort_whitelist[$s] : $sort_whitelist['column1'];
  $order_whitelist = array('asc' => 'asc', 'desc' => 'desc');
  $order_safe = isset($order_whitelist[$o]) ? $order_whitelist[$o] : $order_whitelist['asc'];

  // データリストを取得する
  $data_list = array();
  $sql = "select * from item
          where
          column1 like :query or
          column2 like :query or
          column3 like :query or
          column4 like :query or
          column5 like :query or
          column6 like :query or
          column7 like :query or
          column8 like :query or
          column9 like :query or
          column10 like :query
          order by $sort_safe $order_safe limit :offset, :count";
  $stmt = $pdo->prepare($sql);
  $stmt->bindValue(':query', '%'.$search_query.'%', PDO::PARAM_STR);
  $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
  $stmt->bindValue(':count', $user['item_num'], PDO::PARAM_INT);
  $stmt->execute();
  foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
      array_push($data_list,$row);
  }

  // 総データ件数を取得
  $sql2 = "select count(*) from item
           where
           column1 like :query or
           column2 like :query or
           column3 like :query or
           column4 like :query or
           column5 like :query or
           column6 like :query or
           column7 like :query or
           column8 like :query or
           column9 like :query or
           column10 like :query";
  $stmt2 = $pdo->prepare($sql2);
  $stmt2->bindValue(':query', '%'.$search_query.'%', PDO::PARAM_STR);
  $stmt2->execute();
  $total = $stmt2->fetchColumn();
  $total_page = ceil($total / $user['item_num']);


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
        <p>登録されているデータリストです。</p>

        <div class="row">
            <div class="col-md-6">
                <form class="form" action="" method="get">
                    <div class="form-group">
                        <input type="text" class="form-controll" name="q" value="">
                        <input class="btn btn-secondary" type="submit" name="" value="検索">
                    </div>

                </form>
            </div>
            <div class="col-md-6">
                <a class="btn btn-primary"  href="register.php">新規登録</a>　
                <a class="btn btn-warning"  href="register_all.php">データ一括登録</a>　
            </div>
        </div>

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
                ?>

                <?php if($_GET['q']): ?>
                <tr>
                    <?php foreach ($sort_whitelist as $column): ?>
                        <!-- ソートに選択されているカラムについて -->
                        <?php if ($column == $s): ?>
                            <th><a href="?s=<?php echo h($column); ?>&o=<?php echo h($order); ?>&q=<?php echo h($search_query); ?>"><?php echo h($column); ?><span class="glyphicon <?php echo h($arrow_icon); ?>"></span></a></th>

                        <!-- ソートに選択されていないカラムについて -->
                        <?php else: ?>
                            <th><a href="?s=<?php echo h($column); ?>&o=<?php echo h($order); ?>&q=<?php echo h($search_query); ?>"><?php echo h($column); ?></a></th>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tr>
                <?php else: ?>
                <!-- クエリがなければ普通に表示 -->
                <tr>
                    <th>column1</th>
                    <th>column2</th>
                    <th>column3</th>
                    <th>column4</th>
                    <th>column5</th>
                    <th>column6</th>
                    <th>column7</th>
                    <th>column8</th>
                    <th>column9</th>
                    <th>column10</th>
                </tr>
                <?php endif; ?>
			</thead>

			<?php foreach ($data_list as $data): ?>
				<tr>
					<td><?php echo h($data['column1']);?></td>
                    <td><?php echo h($data['column2']);?></td>
                    <td><?php echo h($data['column3']);?></td>
                    <td><?php echo h($data['column4']);?></td>
                    <td><?php echo h($data['column5']);?></td>
                    <td><?php echo h($data['column6']);?></td>
                    <td><?php echo h($data['column7']);?></td>
                    <td><?php echo h($data['column8']);?></td>
                    <td><?php echo h($data['column9']);?></td>
                    <td><?php echo h($data['column10']);?></td>
					<td><a href="./item_edit.php?id=<?php echo h($data['id']); ?>" class="btn btn-secondary">編集</a>　
                        <a href="javascript:void(0);" class="btn btn-danger" onclick="var ok=confirm('削除しても宜しいですか?'); if (ok) location.href='item_delete.php?id=<?php echo h($data['id']); ?>'; return false;">削除</a>
				</tr>
			<?php endforeach;?>
		</table>
        <div class="row">
            <div class="col-md-6">
                <nav aria-label="Page navigation example">
                    <ul class="pagination">
                        <?php if ($page > 1): ?>
                          <!-- ページパラメーターを指定し、前のページに戻れるようにする -->
                          <li class="page-item"><a class="page-link" href="?page=<?php echo $page-1; ?>&q=<?php echo h($search_query); ?>">&laquo;</a></li>
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
                              <li class="page-item"><a class="page-link" href="?page=<?php echo $i; ?>&q=<?php echo h($search_query); ?>"><?php echo $i; ?></a></li>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($page < $total_page): ?>
                          <!-- ページパラメーターを指定し、次のページに進めるようにする -->
                          <li class="page-item"><a class="page-link" href="?page=<?php echo $page+1; ?>&q=<?php echo h($search_query); ?>">&raquo;</a></li>
                        <?php else: ?>
                          <!-- 最終ページを表示している場合は非アクティブ（class="disabled"）にする -->
                          <li class="disabled page-item"><a class="page-link" href="#">&raquo;</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
            <div class="col-md-6 text-right">
                <a class="btn btn-success" href="javascript:void(0);" onclick="var ok=confirm('全データをダウンロードします。宜しいですか?');if (ok) location.href='item_download.php'; return false;">データダウンロード</a>
            </div>
        </div>

    </div>

    <?php include 'layouts/footer.php'; ?>

</body>
</html>
