# 操蛋云 - 使用文档

## 一、搭建环境
#### 安装服务器端程序
* 下载程序压缩包
* 解压到网站根目录
#### 配置服务器端程序
* 修改数据库连接信息
    * 配置host
    * 配置数据库用户名
    * 配置数据库密码
    * 配置默认数据库
* 修改初始化ID池
#### 配置开发环境
* 下载开发包
* 解压
* 将`FuckYun.jar`放入`libs`目录
* 构建刷新

## 二、新手上路
#### 1. 初始化 `FuckYun` 对象
```
FuckYun fuckYun = new FuckYun(initID, url, table);
```
注意： `initID`必须在ID池中存在，`url`填`FuckYun.php`的链接。
#### 2. 创建表
```
fuckYun.createTable(User.class, ((code, msg) -> {
    System.out.println(code + ": " + msg);
}));
```
#### 3. 添加数据
```
fuckYun.save(user, ((code, msg) -> {
    System.out.println(code + ": " + msg);
}));
```
或者
```
user.save(table, ((code, msg)-> {
    System.out.println(code + ": " + msg);
}});
```
#### 4. 删除数据
##### 4.1 删除满足条件的数据
```
Where where = new Where();
// 删除所有username以00开头的数据
where.add("username", "00%", Where.Type.LIKE);
fuckYun.delete(where, ((code, msg) -> {
    System.out.println(code + ": " + msg);
}));
```
##### 4.2 删除所有数据
```java
fuckYun.deleteAllData(((code, msg) -> {
    System.out.println(code + ": " + msg);
}));
```

##### 4.3 删除表
```java
fuckYun.deleteTable(((code, msg) -> {
    System.out.println(code + ": " + msg);
}));
```

#### 5. 修改数据
```
// 将所有username为00开头的数据修改为user的数据
User user = new User("username", "password");
Where where = new Where();
where.add("username", "00%", Where.Type.LIKE);
fuckYun.update(user, where, ((code, msg) -> {
    System.out.println(code + ": " + msg);
}));
```
#### 6. 查找数据
```
Where where = new Where();
where.add("username", "admin", Where.Type.EQUAL);
Limit limit = new Limit(1);
fuckYun.query(where, limit, User.class, new DownListener<User>(){
    @Override
    public void onDown(Result<User> result) {
        int code = result.getCode();
        String message = result.getMessage();
        System.out.println(code + ": " + msg);
        ArrayList<User> data = result.getData();
        for (User user : data) {
            System.out.println(user);
        }
    }
});
```

## 三、状态码列表
#### 操蛋云状态码
*   `0` 正常
* `-1` 作者懒得搞错误码就用它，具体错误看msg
* `100` 不安全的操作，该操作可能导致数据被意外删除
* `101` 初始化id错误
* `102` 服务器判断该请求非法
* `103` 未知方法（服务器不知道要干啥）
#### 数据库错误码
* `1050` 创建表时该表已经存在
* `1046` 忘了
