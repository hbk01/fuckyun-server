<?php
    class Info
    {
        // mysql的ip地址
        public static $host = "localhost";
        // mysql的用户名
        public static $username = "hbk";
        // mysql的密码
        public static $password = "hbk@qq.com";
        // 默认数据库名
        public static $dbname = "hbk";
        // id池
        public static $init_ids = array();

        static function init(){
            $file = "fuckyun.id";
            if (file_exists($file)){
                $file_handle = fopen($file, "r");
                while (!feof($file_handle)) {
                    $line = fgets($file_handle);
                    self::$init_ids[] = $line;
                }
                fclose($file_handle);
            }
        }
    }