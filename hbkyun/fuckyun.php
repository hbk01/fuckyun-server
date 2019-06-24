<?php
    require_once "SqlHelper.php";
    require_once "Info.php";
    header('Content-type: text/json; charset=utf-8');
    Info::init();

    // 判断user-agent，排除非法请求
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    if (empty($userAgent) or $userAgent != "FuckYun") {
        show(102, "Dangerous Request");
    }

    // 判断init id
    $initID = $_POST['initID'];
    if (empty($initID) or !in_array($initID, Info::$init_ids)) {
        show(101, "Initialize ID Failure");
    }

    $helper = new SqlHelper(Info::$host, Info::$username, Info::$password, Info::$dbname);
    $method = $_POST['method'];
    $methods = array(
        "create_table", "insert", "update",
        "delete", "delete_all_data", "delete_table",
        "query"
    );

    switch ($method) {
        case $methods[0]:
            // 创建表
            $tableName = $_POST['table'];
            $keys = $_POST['keys'];
            $array = fetchArray($keys);
            $result = $helper->createTable($tableName, $array);
            if ($result) {
                showSuccess();
            } else {
                showError($helper);
            }
            break;
        case $methods[1]:
            // 插入数据
            $tableName = $_POST['table'];
            $keys = $_POST['keys'];
            $values = $_POST['values'];
            $array_keys = fetchArray($keys);
            $array_values = fetchArray($values);
            $result = $helper->insertData($tableName, $array_keys, $array_values);
            if ($result) {
                showSuccess();
            } else {
                showError($helper);
            }
            break;
        case $methods[2]:
            // 更新数据，需要的参数：
            // $tableName, array $keys, array $values, $where
            $table = $_POST['table'];
            $keys = $_POST['keys'];
            $values = $_POST['values'];
            $where = $_POST['where'];
            $array_keys = fetchArray($keys);
            $array_values = fetchArray($values);
            $result = $helper->updateData($table, $array_keys, $array_values, $where);
            if ($result) {
                showSuccess();
            } else {
                showError($helper);
            }
            break;
        case $methods[3]:
            // 删除数据
            // $tableName, $where
            $table = $_POST['table'];
            $where = $_POST['where'];
            if (empty($where)){
                show(100, "Unsafe Operation.");
            } else {
                $result = $helper->deleteData($table, $where);
                if ($result){
                    showSuccess();
                } else {
                    showError($helper);
                }
            }
            break;
        case $methods[4]:
            // 删除表中所有数据
            // $tableName
            $table = $_POST['table'];
            $result = $helper->deleteAllData($table);
            if ($result){
                showSuccess();
            } else {
                showError($helper);
            }
            break;
        case $methods[5]:
            // 删除表
            // $tableName
            $table = $_POST['table'];
            $result = $helper->dropTable($table);
            if ($result){
                showSuccess();
            } else {
                showError($helper);
            }
            break;
        case $methods[6]:
            // 查询数据
            // $tableName, $where
            $table = $_POST['table'];
            $where = $_POST['where'];
            $limit = $_POST['limit'];
            $result = $helper->queryTable($table, $where, $limit);
            if ($result){
                echo json_encode($result);
            } else {
                showError($helper);
            }
            break;
        default:
            show(103, "Unknown Method");
    }

    /**
     * 将java端传过来的Array.toString转换成PHP的Array
     * @param $arr
     *
     * @return array[]|false|string[]
     */
    function fetchArray($arr)
    {
        // 去掉头尾的[]
        $arr = substr($arr, 1, strlen($arr));
        $arr = substr($arr, 0, strlen($arr) - 1);
        $array = preg_split("/,\s+/", $arr);
        return $array;
    }

    function show($code, $msg)
    {
        $array = array(
            "code" => $code,
            "msg" => $msg
        );
        echo json_encode($array);
    }

    function showSuccess()
    {
        show(0, "Success");
    }

    function showError(SqlHelper $helper)
    {
        show($helper->getErrorNo(), empty($helper->getErrorMsg()) ? "Unknown Error" : $helper->getErrorMsg());
    }

