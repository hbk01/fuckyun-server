<?php

    class SqlHelper
    {
        public $host;
        public $username;
        public $password;
        public $dbname;
        private $connection;

        /**
         * SqlHelper 构造器
         *
         * @param $host string 主机地址
         * @param $username string 用户名
         * @param $password string 密码
         * @param $dbname string 数据库名
         */
        public function __construct($host, $username, $password, $dbname)
        {
            $this->host = $host;
            $this->username = $username;
            $this->password = $password;
            $this->dbname = $dbname;
            $this->connection = @new mysqli($host, $username, $password, $dbname);
            if (!$this->connection) {
                die("Error：" . mysqli_connect_error());
            }
        }

        /**
         * 创建一个数据表，编码utf8
         *
         * @param $tableName string 表名
         * @param $keys array 表需要的键，所有的键都将为text类型进行存储
         *
         * @return bool 创建成功/失败
         */
        public function createTable($tableName, array $keys)
        {
            $sql = "CREATE TABLE `$tableName` ( ";
            foreach ($keys as $key) {
                $sql .= "`$key` text,";
            }
            $sql = substr($sql, 0, strlen($sql) - 1);
            $sql .= " ) DEFAULT CHARSET=utf8;";
            if (@mysqli_query($this->connection, $sql)) {
                return true;
            } else {
                $this->error();
                return false;
            }
        }

        /**
         * 查询表并打印数据。相当于showResult($tableName, queryTable($tableName, $where))
         *
         * @param $tableName string 表名
         * @param $where string 限定
         */
        public function queryTableToShow($tableName, $where)
        {
            $result = $this->queryTable($tableName, $where);
            $this->showResult($tableName, $result);
        }

        /**
         * 查询表中数据，$where和$limit不是必须的。将会执行：
         * SELECT * FROM $tableName WHERE $where LIMIT $limit;
         *
         * @param $tableName string 表名
         * @param $where string 选择器
         * @param $limit string 限定数量
         *
         * @return array|bool 成功为true且为array，否则为false
         */
        public function queryTable($tableName, $where, $limit)
        {
            $sql = "SELECT * FROM $tableName";
            if (!empty($where)) {
                $sql .= " WHERE $where";
            }
            if (!empty($limit)){
                $sql .= " $limit";
            }
            $sql .= ";";
            $result = @mysqli_query($this->connection, $sql);
            if (!$result) {
                $this->error();
                return false;
            }
            return $this->fetchResultToArray($tableName, $result);
        }

        /**
         * 插入数据。将会执行：INSERT INTO $tableName ($keys[0], $keys[1], ...) VALUES ($values[0], $values[1], ...);
         *
         * @param $tableName string 表名
         * @param $keys array 键
         * @param $values array 值
         *
         * @return bool 删除成功/失败
         */
        public function insertData($tableName, array $keys, array $values)
        {
            $sql = "INSERT INTO `$tableName` ( ";
            foreach ($keys as $key) {
                $sql .= "`$key`,";
            }
            // 去掉最后一个符号
            $sql = substr($sql, 0, strlen($sql) - 1);
            $sql .= " ) VALUES ( ";
            foreach ($values as $value) {
                $sql .= "\"$value\",";
            }
            // 去掉最后一个符号
            $sql = substr($sql, 0, strlen($sql) - 1);
            $sql .= ");";

            if (@mysqli_query($this->connection, $sql)) {
                return true;
            } else {
                $this->error();
                return false;
            }
        }

        /**
         * 删除数据。将会执行：DELETE FROM $tableName WHERE $where;
         *
         * @param $tableName string 表名
         * @param $where string 限定删除，该参数为空不会执行删除操作。若想删除全部数据，请使用deleteAllData()
         *
         * @return bool 删除成功/失败
         */
        public function deleteData($tableName, $where)
        {
            if (!empty($where)) {
                $sql = "DELETE FROM $tableName WHERE $where;";
                $result = $this->connection->query($sql);
                if ($result) {
                    return true;
                } else {
                    $this->error();
                    return false;
                }
            } else {
//                echo "危险操作：没有where子句进行限定，将会删除表中的全部数据！";
                return false;
            }
        }

        /**
         * 删除表中的所有数据
         *
         * @param $tableName string 表名
         *
         * @return bool 删除成功/失败
         */
        public function deleteAllData($tableName)
        {
            $sql = "DELETE FROM $tableName;";
            $result = $this->connection->query($sql);
            if ($result) {
                return true;
            } else {
                $this->error();
                return false;
            }
        }

        /**
         * 删除表。注意，是删除表而不是删除表中的数据。
         *
         * @param $tableName string 表名
         *
         * @return bool 删除成功/失败
         */
        public function dropTable($tableName)
        {
            $sql = "DROP TABLE $tableName;";
            $result = $this->connection->query($sql);
            if ($result) {
                return true;
            } else {
                $this->error();
                return false;
            }
        }

        /**
         * 修改数据。将会执行UPDATE $tableName SET $keys[0]=$values[0], $keys[1]=$values[1], ... WHERE $where;
         *
         * @param $tableName string 表名
         * @param $keys array 要修改的键
         * @param $values array 要修改的值
         * @param $where string 限定修改范围
         *
         * @return bool 修改成功/失败
         */
        public function updateData($tableName, array $keys, array $values, $where)
        {
            $sql = "UPDATE $tableName SET ";
            if (empty($keys) or empty($values)) {
                return false;
            }
            for ($index = 0; $index < count($keys); $index++) {
                $sql .= $keys[$index] . "=" . $values[$index] . ",";
            }
            $sql = substr($sql, 0, strlen($sql) - 1);

            if (empty($where)) {
                $sql .= ";";
            } else {
                $sql .= " WHERE $where;";
            }
            $result = $this->connection->query($sql);
            if ($result) {
                return true;
            } else {
                $this->error();
                return false;
            }
        }

        function fetchResultToArray($tableName, $result)
        {
            // 查询表，获取所有字段名作为json的key
            $fields = $this->queryColumns($tableName);

            // 填充数据
            $returns = array(
                "code"=> 0,
                "msg"=> "success",
            );
            $data = array();
            while ($array = mysqli_fetch_array($result)) {
                $row = array();
                foreach ($fields as $field) {
                    // 将每个字段的值存入每行的数组中
                    $row[$field] = $array[$field];
                }
                // 将每一行的数组存入总返回数组中
                $data[] = $row;
            }
            $returns['data'] = $data;
            return $returns;
        }

        /**
         * 将查询的结果打印出来
         *
         * @param $tableName string 表名
         * @param $result string 查询的结果
         */
        public function showResult($tableName, $result)
        {
            if ($result) {
                echo "查询到 " . mysqli_num_rows($result) . " 条数据。<br>";

                // 显示style
                echo "<style>
                    table,table tr th, table tr td {
                        border:1px solid #3498db;
                    }
                    table {
                        width: 100%; 
                        min-height: 25px; 
                        line-height: 25px; 
                        text-align: center; 
                        border-collapse: collapse; 
                        padding:2px;
                    }
                </style>";

                // 查询表，获取字段
                $fields = $this->queryColumns($tableName);

                // 显示表头
                $tableTitle = "<table><tr>";
                foreach ($fields as $field) {
                    $tableTitle .= "<th>" . $field . "</th>";
                }
                $tableTitle .= "</tr>";
                echo $tableTitle;

                // 显示数据
                while ($array = mysqli_fetch_array($result)) {
                    $item = "<tr>";
                    foreach ($fields as $field) {
                        $item .= "<td>" . $array[$field] . "</td>";
                    }
                    $item .= "</tr>";
                    echo $item;
                }
                echo "</table>";
            } else {
                $this->error();
            }
        }

        /**
         * 查询表的keys
         *
         * @param $tableName string 表名
         *
         * @return array 表的所有键
         */
        public function queryColumns($tableName)
        {
            $fields = array();
            $sql = "SHOW FULL COLUMNS FROM `" . $tableName . "`;";
            $columns = @mysqli_query($this->connection, $sql);
            while ($row = mysqli_fetch_array($columns)) {
//                array_push($fields, $row['Field']);
                $fields[] = $row['Field'];
            }
            return $fields;
        }

        /**
         * 关闭链接
         * @return bool 关闭成功/失败
         */
        public function close()
        {
            return $this->connection->close();
        }

        /**
         * 获取错误码
         * @return int 错误码
         */
        public function getErrorNo()
        {
            return mysqli_errno($this->connection);
        }

        /**
         * 获取错误信息
         * @return string 错误信息
         */
        public function getErrorMsg()
        {
            return mysqli_error($this->connection);
        }

        /**
         * 打印错误信息
         */
        public function error()
        {
            printf("Error %d : %s<br>",
                $this->getErrorNo(), $this->getErrorMsg());
        }
    }