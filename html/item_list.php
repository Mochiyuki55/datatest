<?php
require_once('config.php');
require_once('functions.php');

// レイアウト関連の変数
$page_title = 'item_list';

$pdo = connectDB();

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

// ホワイトリスト照合
$s = 'column1';
$o = 'asc';
$sort_whitelist = array('column1' => 'column1', 'column2' => 'column2');
$sort_safe = isset($sort_whitelist[$s]) ? $sort_whitelist[$s] : $sort_whitelist['column1'];
$order_whitelist = array('asc' => 'asc', 'desc' => 'desc');
$order_safe = isset($order_whitelist[$o]) ? $order_whitelist[$o] : $order_whitelist['asc'];

// item一覧を取得
$sql = 'SELECT * FROM item';
$stmt = $pdo->query($sql);
$data_list = $stmt->fetchAll();

// 検索された場合
if ($_GET['q']) {
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
    $offset = PAGE_COUNT * ($page -1);

    $s = $_GET['s'];
    $o = $_GET['o'];

    // ホワイトリスト照合
    $sort_whitelist = array('column1' => 'column1', 'column2' => 'column2');
    $sort_safe = isset($sort_whitelist[$s]) ? $sort_whitelist[$s] : $sort_whitelist['column1'];
    $order_whitelist = array('asc' => 'asc', 'desc' => 'desc');
    $order_safe = isset($order_whitelist[$o]) ? $order_whitelist[$o] : $order_whitelist['asc'];

    $data_list = array();
    $sql = "select * from item where column1 like :query or column2 like :query order by $sort_safe $order_safe limit :offset, :count";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':query', '%'.$search_query.'%', PDO::PARAM_STR);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':count', PAGE_COUNT, PDO::PARAM_INT);
    $stmt->execute();
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        array_push($data_list,$row);
    }

    // 総データ件数を取得
    $sql2 = "select count(*) from item where column1 like :query or column2 like :query";
    $stmt2 = $pdo->prepare($sql2);
    $stmt2->bindValue(':query', '%'.$search_query.'%', PDO::PARAM_STR);
    $stmt2->execute();
    $total = $stmt2->fetchColumn();

    $total_page = ceil($total / PAGE_COUNT);

}

?>
<?php include 'layouts/head.php'; ?>

<body id="main" class="bg-dark text-light">

    <?php include 'layouts/header.php'; ?>

    <div class="container">
        <h1><?php echo h($page_title); ?></h1>

        <div class="row">
            <div class="col-md-8">
                <form class="form" action="" method="get">
                    <div class="form-group">
                        <input type="text" class="form-controll" name="q" value="">
                        <input class="btn btn-secondary" type="submit" name="" value="search">
                    </div>

                </form>
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
                <tr>
                    <?php foreach ($sort_whitelist as $column): ?>
                        <?php if ($column == $s): ?>
                            <th><a href="?s=<?php echo h($column); ?>&o=<?php echo h($order); ?>&q=<?php echo h($search_query); ?>"><?php echo h($column); ?><span class="glyphicon <?php echo h($arrow_icon); ?>"></span></a></th>
                        <?php else: ?>
                            <th><a href="?s=<?php echo h($column); ?>&o=<?php echo h($order); ?>&q=<?php echo h($search_query); ?>"><?php echo h($column); ?></a></th>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tr>
			</thead>
			<?php foreach ($data_list as $data): ?>
				<tr>
					<td><?php echo h($data['column1']);?></td>
                    <td><?php echo h($data['column2']);?></td>
					<td><a href="./item_edit.php?id=<?php echo h($data['id']); ?>">編集</a></td>
				</tr>
			<?php endforeach;?>
		</table>
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

    <?php include 'layouts/footer.php'; ?>

</body>
</html>
