<?php
require_once('config.php');
require_once('functions.php');

// レイアウト関連の変数
$page_title = 'item_list';

$pdo = connectDB();

// item一覧を取得
$sql = 'SELECT * FROM item';
$stmt = $pdo->query($sql);
$data_list = $stmt->fetchAll();

?>
<?php include 'layouts/head.php'; ?>

<body id="main" class="bg-dark text-light">

    <?php include 'layouts/header.php'; ?>

    <div class="container">
        <h1><?php echo h($page_title); ?></h1>

		<table class="table table-bordered table-striped bg-light">
			<thead>
				<tr>
					<th>ID</th>
					<th>Name</th>
					<th>Pass</th>
                    <th>updated_at</th>
                    <th>created_at</th>
				</tr>
			</thead>
			<?php foreach ($data_list as $data): ?>
				<tr>
					<td><?php echo h($data['id']);?></td>
					<td><?php echo h($data['column1']);?></td>
                    <td><?php echo h($data['column2']);?></td>
                    <td><?php echo h($data['created_at']);?></td>
                    <td><?php echo h($data['updated_at']);?></td>
					<td><a href="./item_edit.php?id=<?php echo h($data['id']); ?>">編集</a></td>
				</tr>
			<?php endforeach;?>
		</table>

    </div>

    <?php include 'layouts/footer.php'; ?>

</body>
</html>
