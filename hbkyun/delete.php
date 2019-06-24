<?php
    require_once 'SqlHelper.php';
    $tableName = $_POST['table'];
    $where = $_POST['where'];
    $helper = new SqlHelper("39.108.149.133", "hbk", "hbk@qq.com", "hbk");
    $helper->deleteData($tableName, $where);
