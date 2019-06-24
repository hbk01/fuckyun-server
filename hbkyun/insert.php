<?php
    require_once 'SqlHelper.php';
    $tableName = $_POST['table'];
    $keys = $_POST['key'];
    $values = $_POST['value'];
    $helper = new SqlHelper("39.108.149.133", "hbk", "hbk@qq.com", "hbk");
    $helper->insertData($tableName, $keys, $values);