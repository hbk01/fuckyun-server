<?php
    require_once 'SqlHelper.php';
    $tableName = $_POST['table'];
    $fields = $_POST['field'];
    $helper = new SqlHelper("39.108.149.133", "hbk", "hbk@qq.com", "hbk");
    if ($helper->createTable($tableName, $fields)){
        echo "创建成功~";
    } else {
        echo "失败了唷";
    }