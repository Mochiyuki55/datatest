<?php

$test_array = array(
    '0' => array('1','1','1','1','1','1','1','1','1','1','1',),
    '1' => array('2','2','2','2','2','2','2','2','2','2','2',)
);

echo print_r($test_array);

echo '<hr>';

unset($test_array[0]);
echo print_r($test_array);

?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
    <head>
        <meta charset="utf-8">
        <title></title>
    </head>
    <body>

        <p><?php if(isset($_POST['check'])){echo 'pushed';}; ?></p>
        <form class="" action="" method="post">
            <input type="checkbox" name="check" value="push">
            <input type="submit" name="" value="push">
        </form>
    </body>
</html>
