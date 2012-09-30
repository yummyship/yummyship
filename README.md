# Yummyship

当前版本 1.0.0 beta

最后更新时间 2012-09-30

## 简要说明
```
admin     			--- 后台
data      			--- 图片存放路径（包括封面图、步骤图、缩略图、用户头像等）
gather    			--- 原来开发用于在线采集封面，后期可能废弃
themes    			--- 前台主题目录
var       			--- 内核文件（注意类的命名方式 ：文件夹名_类名 ，第一个字母要大写）
var/init.php		--- 初始化入口
var/Byends 			--- Widget 基类
var/Widget			--- 扩展类，这里的类一般需要 继承 Abstract 数据抽象类
var/OAuth			--- 第三方登录

.htaccess			--- rewrite 规则
config.inc.php		--- 数据库配置
index.php			--- 首页入口
install_.php		--- 安装程序
```

## 约定书写规范

1、变量命名：普通变量驼蜂写法，第二个单词首字母大写，以此类推
```
eg: $instanceUser
```

预定义常量 ：一律大写加 下划线
```
eg: define( 'BYENDS_ADMIN_URL',				BYENDS_BASE_URL.__BYENDS_ADMIN_DIR__ );
```

布尔值常量：一律大写
```
eg: true false null
```
 
2、条件语句
```
if () {
	//
}
elseif () {
	//
}
else {
	//
}
```
