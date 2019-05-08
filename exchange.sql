# Host: 47.106.95.190  (Version: 5.7.25-0ubuntu0.16.04.2)
# Date: 2019-05-08 14:26:37
# Generator: MySQL-Front 5.3  (Build 4.234)

/*!40101 SET NAMES utf8 */;

#
# Structure for table "g_admin"
#

DROP TABLE IF EXISTS `g_admin`;
CREATE TABLE `g_admin` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `admin_name` varchar(45) NOT NULL DEFAULT '',
  `login_name` varchar(45) NOT NULL DEFAULT '' COMMENT '用户登录名',
  `password` varchar(60) NOT NULL DEFAULT '' COMMENT '密码',
  `login_time` datetime DEFAULT NULL COMMENT '登录时间',
  `group` tinyint(3) NOT NULL DEFAULT '1' COMMENT '管理员昵称',
  PRIMARY KEY (`id`),
  UNIQUE KEY `login_name_UNIQUE` (`login_name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='管理员列表';

#
# Data for table "g_admin"
#

INSERT INTO `g_admin` VALUES (1,'admin','admin','1f003f5857c060dbe5ca0476ab45caa084c39e5a:sm1j','2019-05-08 15:23:36',1);

#
# Structure for table "g_goods"
#

DROP TABLE IF EXISTS `g_goods`;
CREATE TABLE `g_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '置换商品名称',
  `price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '原价格',
  `describe` varchar(800) NOT NULL DEFAULT '' COMMENT '描述',
  `photo` varchar(255) NOT NULL DEFAULT '' COMMENT '图片',
  `want_to_goods` varchar(500) NOT NULL DEFAULT '' COMMENT '意向商品，多个商品用“,”隔开',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态0待发布，1发布中，2已匹配',
  `isdel` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0正常，1已取消',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='置换商品列表';

#
# Data for table "g_goods"
#

INSERT INTO `g_goods` VALUES (1,'三星显示器',1000.00,'111','/uploads/goods/c67c0e264b931a8058bf908b8975e31b.jpg','机械键盘',2,0,2,'2019-04-29 10:22:01'),(2,'鼠标',200.00,'发士大夫','/uploads/goods/9500c775f136d90d16e2a97a5975f88c.jpg','手机',1,1,3,'2019-04-29 10:22:01'),(3,'鼠标',78.00,'好用','/uploads/goods/1729f4d9e49f63d302c2ec940609bdb9.png','键盘',1,0,1,'2019-04-30 12:17:33'),(4,'手机',1000.00,'可以用来做备用机','/uploads/goods/856149efa68f89c4af1bd618d982ff61.png','游戏机',1,0,3,'2019-05-01 16:01:12');

#
# Structure for table "g_order"
#

DROP TABLE IF EXISTS `g_order`;
CREATE TABLE `g_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '发起置换人',
  `changer_id` int(11) NOT NULL DEFAULT '0' COMMENT '置换对方id',
  `goods_id` int(11) NOT NULL DEFAULT '0' COMMENT '发起置换人商品id',
  `request_id` int(11) NOT NULL DEFAULT '0' COMMENT '置换请求id',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态：0处理中，1交易成功，2交易失败/取消',
  `user_address` varchar(255) NOT NULL DEFAULT '' COMMENT '发起人地址',
  `changer_address` varchar(255) NOT NULL DEFAULT '' COMMENT '请求人地址',
  `user_addressee` varchar(45) NOT NULL DEFAULT '',
  `changer_addressee` varchar(45) NOT NULL DEFAULT '',
  `user_mobile` varchar(11) NOT NULL DEFAULT '',
  `changer_mobile` varchar(11) NOT NULL DEFAULT '',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_report` int(11) NOT NULL DEFAULT '0' COMMENT '举报id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='置换订单列表';

#
# Data for table "g_order"
#

INSERT INTO `g_order` VALUES (1,2,3,1,1,1,'集美大学第五社区4号楼','华侨大学','海边人','东方云','18030016446','18030016104','2019-04-29 10:21:06',5);

#
# Structure for table "g_report"
#

DROP TABLE IF EXISTS `g_report`;
CREATE TABLE `g_report` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL DEFAULT '0' COMMENT '订单号',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '举报人',
  `report_user_id` int(11) NOT NULL DEFAULT '0' COMMENT '被举报人',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '举报类型0普通1交易举报2信誉举报',
  `reason` varchar(500) DEFAULT '',
  `is_deal` tinyint(1) NOT NULL DEFAULT '0' COMMENT '管理员是否已处理',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='举报表';

#
# Data for table "g_report"
#

INSERT INTO `g_report` VALUES (1,0,3,2,0,'普通测试',1,'2019-04-29 22:59:27'),(2,0,3,2,0,'普通测试',1,'2019-04-29 22:59:30'),(3,1,3,2,1,'111',1,'2019-04-29 23:00:39'),(4,1,2,3,0,'1111',1,'2019-05-07 16:35:59'),(5,1,2,3,0,'222',0,'2019-05-07 16:36:04');

#
# Structure for table "g_request"
#

DROP TABLE IF EXISTS `g_request`;
CREATE TABLE `g_request` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '请求发起人',
  `goods_id` int(11) NOT NULL DEFAULT '0' COMMENT '对应置换商品的id',
  `goods_user_id` int(11) NOT NULL DEFAULT '0' COMMENT '置换商品的所属者',
  `change_goods_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发布匹配的商品id',
  `change_goods_name` varchar(255) NOT NULL DEFAULT '' COMMENT '匹配的物品',
  `describe` varchar(800) NOT NULL DEFAULT '' COMMENT '匹配的物品描述',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0待回复1成功2失败',
  `reason` varchar(255) NOT NULL DEFAULT '' COMMENT '拒绝原因',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='置换请求表';

#
# Data for table "g_request"
#

INSERT INTO `g_request` VALUES (1,3,1,2,2,'鼠标','发士大夫',2,'','2019-04-29 10:22:36'),(2,1,2,3,3,'鼠标','好用',0,'','2019-04-30 12:18:24');

#
# Structure for table "g_system_msg"
#

DROP TABLE IF EXISTS `g_system_msg`;
CREATE TABLE `g_system_msg` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `content` varchar(800) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `is_issue` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `weight` int(3) unsigned NOT NULL DEFAULT '0' COMMENT '公告权重',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='系统公告';

#
# Data for table "g_system_msg"
#

INSERT INTO `g_system_msg` VALUES (1,'测试11','哈哈哈哈11',0,10,'2019-05-08 01:29:33'),(3,'测试2','111111111',1,2,'2019-05-08 11:14:02');

#
# Structure for table "g_user_address"
#

DROP TABLE IF EXISTS `g_user_address`;
CREATE TABLE `g_user_address` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '地址',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  `is_default` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否默认地址',
  `isdel` varchar(45) NOT NULL DEFAULT '0' COMMENT '是否删除',
  `addressee` varchar(20) NOT NULL DEFAULT '' COMMENT '收件人',
  `mobile` varchar(11) NOT NULL DEFAULT '' COMMENT '收件人手机号',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='用户收货地址表';

#
# Data for table "g_user_address"
#

INSERT INTO `g_user_address` VALUES (1,'集美大学第五社区4号楼',2,0,'1','海边人','18030016446'),(2,'集美大学第五社区4号楼',2,0,'0','海边人','18030016446'),(3,'集美大学教师教育学院1',2,0,'0','蓝色1','18030016122'),(4,'华侨大学',4,0,'0','人工智障','18030016441'),(5,'华侨大学',3,0,'0','东方云','18030016104');

#
# Structure for table "g_users"
#

DROP TABLE IF EXISTS `g_users`;
CREATE TABLE `g_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL DEFAULT '' COMMENT '邮箱 /登录帐号',
  `password` varchar(60) NOT NULL DEFAULT '' COMMENT '密码',
  `nick_name` varchar(45) NOT NULL DEFAULT '' COMMENT '昵称',
  `shoole` varchar(100) NOT NULL DEFAULT '' COMMENT '所在学校',
  `major` varchar(100) NOT NULL DEFAULT '' COMMENT '专业',
  `sex` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '性别0男1女',
  `is_effect` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否审核0未审核1已审核',
  `credit` int(3) NOT NULL DEFAULT '80' COMMENT '信誉度80,根据交换次数的结果提升或降低',
  `isdel` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否被冻结0正常1冻结',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='用户表';

#
# Data for table "g_users"
#

INSERT INTO `g_users` VALUES (1,'913294974@qq.com','1f003f5857c060dbe5ca0476ab45caa084c39e5a:sm1j','','','',0,1,80,0,'2019-03-02 10:19:00'),(2,'hzr19960121@163.com','a371b8024ca70cd5ea6c9cc44a0fb4db44d524ba:hztk','灯塔','集美大学','计算机科学与技术',0,1,80,0,'2019-03-03 02:45:35'),(3,'616955718@qq.com','484335d3c9457b66879e3a8ddda70f17e25c79ed:75w7','东方红','集美大学','计算机科学与技术',1,1,80,0,'2019-03-03 15:13:28');
