/*
Navicat MySQL Data Transfer

Source Server         : ctolog.com
Source Server Version : 50559
Source Host           : ctolog.com:3306
Source Database       : service

Target Server Type    : MYSQL
Target Server Version : 50559
File Encoding         : 65001

Date: 2018-03-13 19:23:39
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for system_auth
-- ----------------------------
DROP TABLE IF EXISTS `system_auth`;
CREATE TABLE `system_auth` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(20) NOT NULL COMMENT '权限名称',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '状态(1:禁用,2:启用)',
  `sort` smallint(6) unsigned DEFAULT '0' COMMENT '排序权重',
  `desc` varchar(255) DEFAULT NULL COMMENT '备注说明',
  `create_by` bigint(11) unsigned DEFAULT '0' COMMENT '创建人',
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `index_system_auth_title` (`title`) USING BTREE,
  KEY `index_system_auth_status` (`status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='系统权限表';

-- ----------------------------
-- Records of system_auth
-- ----------------------------

-- ----------------------------
-- Table structure for system_auth_node
-- ----------------------------
DROP TABLE IF EXISTS `system_auth_node`;
CREATE TABLE `system_auth_node` (
  `auth` bigint(20) unsigned DEFAULT NULL COMMENT '角色ID',
  `node` varchar(200) DEFAULT NULL COMMENT '节点路径',
  KEY `index_system_auth_auth` (`auth`) USING BTREE,
  KEY `index_system_auth_node` (`node`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='角色与节点关系表';

-- ----------------------------
-- Records of system_auth_node
-- ----------------------------

-- ----------------------------
-- Table structure for system_config
-- ----------------------------
DROP TABLE IF EXISTS `system_config`;
CREATE TABLE `system_config` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL COMMENT '配置编码',
  `value` varchar(500) DEFAULT NULL COMMENT '配置值',
  PRIMARY KEY (`id`),
  KEY `index_system_config_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='系统参数配置';

-- ----------------------------
-- Records of system_config
-- ----------------------------
INSERT INTO `system_config` VALUES ('1', 'app_name', 'ThinkService');
INSERT INTO `system_config` VALUES ('2', 'site_name', 'ThinkService');
INSERT INTO `system_config` VALUES ('3', 'app_version', '3.0 dev');
INSERT INTO `system_config` VALUES ('4', 'site_copy', '&copy;版权所有 2014-2018 楚才科技');
INSERT INTO `system_config` VALUES ('5', 'browser_icon', 'http://service.thinkadmin.top/static/upload/f47b8fe06e38ae99/08e8398da45583b9.png');
INSERT INTO `system_config` VALUES ('6', 'tongji_baidu_key', '');
INSERT INTO `system_config` VALUES ('7', 'miitbeian', '粤ICP备16006642号-2');
INSERT INTO `system_config` VALUES ('8', 'storage_type', 'local');
INSERT INTO `system_config` VALUES ('9', 'storage_local_exts', 'png,jpg,rar,doc,icon');
INSERT INTO `system_config` VALUES ('10', 'storage_qiniu_bucket', '');
INSERT INTO `system_config` VALUES ('11', 'storage_qiniu_domain', '');
INSERT INTO `system_config` VALUES ('12', 'storage_qiniu_access_key', '');
INSERT INTO `system_config` VALUES ('13', 'storage_qiniu_secret_key', '');
INSERT INTO `system_config` VALUES ('14', 'storage_oss_bucket', '');
INSERT INTO `system_config` VALUES ('15', 'storage_oss_endpoint', '');
INSERT INTO `system_config` VALUES ('16', 'storage_oss_domain', '');
INSERT INTO `system_config` VALUES ('17', 'storage_oss_keyid', '');
INSERT INTO `system_config` VALUES ('18', 'storage_oss_secret', '');
INSERT INTO `system_config` VALUES ('19', 'component_appid', 'wx1b8278fa121d8dc6');
INSERT INTO `system_config` VALUES ('20', 'component_appsecret', 'cf5af39408fb3b977584a40d399d298c');
INSERT INTO `system_config` VALUES ('21', 'component_token', 'P8QHTIxpBEq88IrxatqhgpBm2OAQROkI');
INSERT INTO `system_config` VALUES ('22', 'component_encodingaeskey', 'L5uFIa0U6KLalPyXckyqoVIJYLhsfrg8k9YzybZIHsx');

-- ----------------------------
-- Table structure for system_log
-- ----------------------------
DROP TABLE IF EXISTS `system_log`;
CREATE TABLE `system_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ip` char(15) NOT NULL DEFAULT '' COMMENT '操作者IP地址',
  `node` char(200) NOT NULL DEFAULT '' COMMENT '当前操作节点',
  `username` varchar(32) NOT NULL DEFAULT '' COMMENT '操作人用户名',
  `action` varchar(200) NOT NULL DEFAULT '' COMMENT '操作行为',
  `content` text NOT NULL COMMENT '操作内容描述',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='系统操作日志表';

-- ----------------------------
-- Records of system_log
-- ----------------------------

-- ----------------------------
-- Table structure for system_menu
-- ----------------------------
DROP TABLE IF EXISTS `system_menu`;
CREATE TABLE `system_menu` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pid` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '父id',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '名称',
  `node` varchar(200) NOT NULL DEFAULT '' COMMENT '节点代码',
  `icon` varchar(100) NOT NULL DEFAULT '' COMMENT '菜单图标',
  `url` varchar(400) NOT NULL DEFAULT '' COMMENT '链接',
  `params` varchar(500) DEFAULT '' COMMENT '链接参数',
  `target` varchar(20) NOT NULL DEFAULT '_self' COMMENT '链接打开方式',
  `sort` int(11) unsigned DEFAULT '0' COMMENT '菜单排序',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态(0:禁用,1:启用)',
  `create_by` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '创建人',
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `index_system_menu_node` (`node`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COMMENT='系统菜单表';

-- ----------------------------
-- Records of system_menu
-- ----------------------------
INSERT INTO `system_menu` VALUES ('1', '0', '系统设置', '', '', '#', '', '_self', '3000', '1', '10000', '2018-01-19 15:27:00');
INSERT INTO `system_menu` VALUES ('2', '10', '后台菜单', '', 'fa fa-leaf', 'admin/menu/index', '', '_self', '10', '1', '10000', '2018-01-19 15:27:17');
INSERT INTO `system_menu` VALUES ('3', '10', '系统参数', '', 'fa fa-modx', 'admin/config/index', '', '_self', '20', '1', '10000', '2018-01-19 15:27:57');
INSERT INTO `system_menu` VALUES ('4', '11', '访问授权', '', 'fa fa-group', 'admin/auth/index', '', '_self', '20', '1', '10000', '2018-01-22 11:13:02');
INSERT INTO `system_menu` VALUES ('5', '11', '用户管理', '', 'fa fa-user', 'admin/user/index', '', '_self', '10', '1', '0', '2018-01-23 12:15:12');
INSERT INTO `system_menu` VALUES ('6', '11', '访问节点', '', 'fa fa-fort-awesome', 'admin/node/index', '', '_self', '30', '1', '0', '2018-01-23 12:36:54');
INSERT INTO `system_menu` VALUES ('7', '0', '后台首页', '', '', 'admin/index/main', '', '_self', '1000', '1', '0', '2018-01-23 13:42:30');
INSERT INTO `system_menu` VALUES ('8', '16', '系统日志', '', 'fa fa-code', '/admin/log/index', '', '_self', '0', '1', '0', '2018-01-24 13:52:58');
INSERT INTO `system_menu` VALUES ('9', '10', '文件存储', '', 'fa fa-stop-circle', 'admin/config/file', '', '_self', '30', '1', '0', '2018-01-25 10:54:28');
INSERT INTO `system_menu` VALUES ('10', '1', '系统管理', '', '', '#', '', '_self', '100', '1', '0', '2018-01-25 18:14:28');
INSERT INTO `system_menu` VALUES ('11', '1', '访问权限', '', '', '#', '', '_self', '100', '1', '0', '2018-01-25 18:15:08');
INSERT INTO `system_menu` VALUES ('13', '1', '公众号服务', '', '', '#', '', '_self', '20', '1', '0', '2018-02-06 11:54:30');
INSERT INTO `system_menu` VALUES ('14', '13', '开放平台配置', '', 'fa fa-cogs', 'wechat/config/index', '', '_self', '0', '1', '0', '2018-02-06 11:54:58');
INSERT INTO `system_menu` VALUES ('15', '13', '公众号管理', '', 'fa fa-wechat', 'wechat/index/index', '', '_self', '0', '1', '0', '2018-02-07 17:10:31');
INSERT INTO `system_menu` VALUES ('16', '1', '日志管理', '', '', '#', '', '_self', '300', '1', '0', '2018-02-10 16:31:15');

-- ----------------------------
-- Table structure for system_node
-- ----------------------------
DROP TABLE IF EXISTS `system_node`;
CREATE TABLE `system_node` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `node` varchar(100) DEFAULT NULL COMMENT '节点代码',
  `title` varchar(500) DEFAULT NULL COMMENT '节点标题',
  `is_menu` tinyint(1) unsigned DEFAULT '0' COMMENT '是否可设置为菜单',
  `is_auth` tinyint(1) unsigned DEFAULT '1' COMMENT '是否启动RBAC权限控制',
  `is_login` tinyint(1) unsigned DEFAULT '1' COMMENT '是否启动登录控制',
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `index_system_node_node` (`node`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='系统节点表';

-- ----------------------------
-- Records of system_node
-- ----------------------------
INSERT INTO `system_node` VALUES ('1', 'admin', '系统管理', '0', '1', '1', '2018-01-23 12:39:13');
INSERT INTO `system_node` VALUES ('2', 'admin/auth', '权限', '0', '1', '1', '2018-01-23 12:39:14');
INSERT INTO `system_node` VALUES ('3', 'admin/auth/index', '列表', '1', '1', '1', '2018-01-23 12:39:14');
INSERT INTO `system_node` VALUES ('4', 'admin/auth/apply', '授权', '0', '1', '1', '2018-01-23 12:39:19');
INSERT INTO `system_node` VALUES ('5', 'admin/auth/add', '添加', '0', '1', '1', '2018-01-23 12:39:22');
INSERT INTO `system_node` VALUES ('6', 'admin/auth/edit', '编辑', '0', '1', '1', '2018-01-23 12:39:23');
INSERT INTO `system_node` VALUES ('7', 'admin/auth/forbid', '禁用', '0', '1', '1', '2018-01-23 12:39:23');
INSERT INTO `system_node` VALUES ('8', 'admin/auth/resume', '启用', '0', '1', '1', '2018-01-23 12:39:24');
INSERT INTO `system_node` VALUES ('9', 'admin/auth/del', '删除', '0', '1', '1', '2018-01-23 12:39:25');
INSERT INTO `system_node` VALUES ('10', 'admin/config/index', '系统', '1', '1', '1', '2018-01-23 12:39:25');
INSERT INTO `system_node` VALUES ('11', 'admin/config/file', '文件', '0', '1', '1', '2018-01-23 12:39:26');
INSERT INTO `system_node` VALUES ('12', 'admin/config/sms', '短信', '0', '1', '1', '2018-01-23 12:39:28');
INSERT INTO `system_node` VALUES ('13', 'admin/log/index', '列表', '1', '1', '1', '2018-01-23 12:39:28');
INSERT INTO `system_node` VALUES ('14', 'admin/log/sms', '短信', '0', '1', '1', '2018-01-23 12:39:29');
INSERT INTO `system_node` VALUES ('15', 'admin/log/del', '删除', '0', '1', '1', '2018-01-23 12:39:29');
INSERT INTO `system_node` VALUES ('16', 'admin/menu/index', '列表', '1', '1', '1', '2018-01-23 12:39:31');
INSERT INTO `system_node` VALUES ('17', 'admin/menu/add', '添加', '0', '1', '1', '2018-01-23 12:39:31');
INSERT INTO `system_node` VALUES ('18', 'admin/menu/edit', '编辑', '0', '1', '1', '2018-01-23 12:39:32');
INSERT INTO `system_node` VALUES ('19', 'admin/menu/del', '删除', '0', '1', '1', '2018-01-23 12:39:33');
INSERT INTO `system_node` VALUES ('20', 'admin/menu/forbid', '禁用', '0', '1', '1', '2018-01-23 12:39:33');
INSERT INTO `system_node` VALUES ('21', 'admin/menu/resume', '启用', '0', '1', '1', '2018-01-23 12:39:34');
INSERT INTO `system_node` VALUES ('22', 'admin/node/index', '列表', '1', '1', '1', '2018-01-23 12:39:36');
INSERT INTO `system_node` VALUES ('23', 'admin/node/save', '保存', '0', '1', '1', '2018-01-23 12:39:36');
INSERT INTO `system_node` VALUES ('24', 'admin/user/index', '列表', '1', '1', '1', '2018-01-23 12:39:37');
INSERT INTO `system_node` VALUES ('25', 'admin/user/auth', '授权', '0', '1', '1', '2018-01-23 12:39:38');
INSERT INTO `system_node` VALUES ('26', 'admin/user/add', '添加', '0', '1', '1', '2018-01-23 12:39:39');
INSERT INTO `system_node` VALUES ('27', 'admin/user/edit', '编辑', '0', '1', '1', '2018-01-23 12:39:39');
INSERT INTO `system_node` VALUES ('28', 'admin/user/pass', '密码', '0', '1', '1', '2018-01-23 12:39:40');
INSERT INTO `system_node` VALUES ('29', 'admin/user/del', '删除', '0', '1', '1', '2018-01-23 12:39:41');
INSERT INTO `system_node` VALUES ('30', 'admin/user/forbid', '禁用', '0', '1', '1', '2018-01-23 12:39:41');
INSERT INTO `system_node` VALUES ('31', 'admin/user/resume', '启用', '0', '1', '1', '2018-01-23 12:39:42');
INSERT INTO `system_node` VALUES ('32', 'admin/config', '配置', '0', '1', '1', '2018-01-23 13:34:37');
INSERT INTO `system_node` VALUES ('33', 'admin/log', '日志', '0', '1', '1', '2018-01-23 13:34:48');
INSERT INTO `system_node` VALUES ('34', 'admin/menu', '菜单', '0', '1', '1', '2018-01-23 13:34:58');
INSERT INTO `system_node` VALUES ('35', 'admin/node', '节点', '0', '1', '1', '2018-01-23 13:35:17');
INSERT INTO `system_node` VALUES ('36', 'admin/user', '用户', '0', '1', '1', '2018-01-23 13:35:26');
INSERT INTO `system_node` VALUES ('37', 'wechat', '微信管理', '0', '1', '1', '2018-02-06 11:53:21');
INSERT INTO `system_node` VALUES ('38', 'wechat/config', '平台配置', '0', '1', '1', '2018-02-06 11:53:32');
INSERT INTO `system_node` VALUES ('39', 'wechat/config/index', '配置参数', '1', '1', '1', '2018-02-06 11:53:32');
INSERT INTO `system_node` VALUES ('40', 'wechat/index', '公众号管理', '0', '1', '1', '2018-02-06 11:53:50');
INSERT INTO `system_node` VALUES ('41', 'wechat/index/index', '公众号列表', '1', '1', '1', '2018-02-06 11:53:53');
INSERT INTO `system_node` VALUES ('42', 'wechat/index/del', '删除公众号', '0', '1', '1', '2018-02-06 11:53:59');
INSERT INTO `system_node` VALUES ('43', 'wechat/index/forbid', '禁用公众号', '0', '1', '1', '2018-02-06 11:54:06');
INSERT INTO `system_node` VALUES ('44', 'wechat/index/resume', '启用公众号', '0', '1', '1', '2018-02-06 11:54:10');

-- ----------------------------
-- Table structure for system_sequence
-- ----------------------------
DROP TABLE IF EXISTS `system_sequence`;
CREATE TABLE `system_sequence` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `type` varchar(20) DEFAULT NULL COMMENT '序号类型',
  `sequence` char(50) NOT NULL COMMENT '序号值',
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `index_system_sequence_unique` (`type`,`sequence`) USING BTREE,
  KEY `index_system_sequence_type` (`type`),
  KEY `index_system_sequence_number` (`sequence`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='系统序号表';

-- ----------------------------
-- Records of system_sequence
-- ----------------------------

-- ----------------------------
-- Table structure for system_user
-- ----------------------------
DROP TABLE IF EXISTS `system_user`;
CREATE TABLE `system_user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL DEFAULT '' COMMENT '用户登录名',
  `password` char(32) NOT NULL DEFAULT '' COMMENT '用户登录密码',
  `qq` varchar(16) DEFAULT NULL COMMENT '联系QQ',
  `mail` varchar(32) DEFAULT NULL COMMENT '联系邮箱',
  `phone` varchar(16) DEFAULT NULL COMMENT '联系手机号',
  `desc` varchar(255) DEFAULT '' COMMENT '备注说明',
  `login_num` bigint(20) unsigned DEFAULT '0' COMMENT '登录次数',
  `login_at` datetime DEFAULT NULL,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态(0:禁用,1:启用)',
  `authorize` varchar(255) DEFAULT NULL,
  `is_deleted` tinyint(1) unsigned DEFAULT '0' COMMENT '删除状态(1:删除,0:未删)',
  `create_by` bigint(20) unsigned DEFAULT NULL COMMENT '创建人',
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `index_system_user_username` (`username`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=10001 DEFAULT CHARSET=utf8 COMMENT='系统用户表';

-- ----------------------------
-- Records of system_user
-- ----------------------------
INSERT INTO `system_user` VALUES ('10000', 'admin', '21232f297a57a5a743894a0e4a801fc3', '22222222', '', '', '', '22306', '2018-03-13 19:11:46', '1', '1', '0', null, '2015-11-13 15:14:22');

-- ----------------------------
-- Table structure for wechat_config
-- ----------------------------
DROP TABLE IF EXISTS `wechat_config`;
CREATE TABLE `wechat_config` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `authorizer_appid` varchar(100) DEFAULT NULL COMMENT '公众号APPID',
  `authorizer_access_token` varchar(200) DEFAULT NULL COMMENT '公众号Token',
  `authorizer_refresh_token` varchar(200) DEFAULT NULL COMMENT '公众号刷新Token',
  `func_info` varchar(100) DEFAULT NULL COMMENT '公众号集权',
  `nick_name` varchar(50) DEFAULT NULL COMMENT '公众号昵称',
  `head_img` varchar(200) DEFAULT NULL COMMENT '公众号头像',
  `expires_in` bigint(20) DEFAULT NULL COMMENT 'Token有效时间',
  `service_type` tinyint(2) DEFAULT NULL COMMENT '公众号实际类型',
  `service_type_info` tinyint(2) DEFAULT NULL COMMENT '服务类型信息',
  `verify_type` tinyint(2) DEFAULT NULL COMMENT '公众号实际认证类型',
  `verify_type_info` tinyint(2) DEFAULT NULL COMMENT '公众号认证类型',
  `user_name` varchar(100) DEFAULT NULL COMMENT '众众号原始账号',
  `alias` varchar(100) DEFAULT NULL COMMENT '公众号别名',
  `qrcode_url` varchar(200) DEFAULT NULL COMMENT '公众号二维码地址',
  `business_info` varchar(255) DEFAULT NULL,
  `principal_name` varchar(255) DEFAULT NULL COMMENT '公司名称',
  `idc` tinyint(1) unsigned DEFAULT NULL,
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '状态(1正常授权,0取消授权)',
  `total` bigint(20) unsigned DEFAULT '0' COMMENT '统计调用次数',
  `appkey` char(32) DEFAULT NULL COMMENT '接口KEY',
  `appuri` varchar(255) DEFAULT NULL COMMENT '响应接口APP',
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `index_wechat_config_authorizer_appid` (`authorizer_appid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='公众号授权参数';

-- ----------------------------
-- Records of wechat_config
-- ----------------------------
INSERT INTO `wechat_config` VALUES ('1', 'wx60a43dd8161666d4', '7_My9K6rdS9lZlQRZNT6_4lddG1KNWSFQJ5Es7iZb5JIEgbx6kf3mwJKmepw_0nvy9RJj-3gJhm6FA98XM4nny0Xojq9EXYtiaVa7Lsvf9eTRqLUxXoFiDKWt99XkCzrrCTOFvJHaWZIDX_IhFNJJgAMDUQQ', 'refreshtoken@@@LqYZOmAH-vBev3tiiTRwBBzz7KwxVLu6Ta62PLmcFGc', '1,15,4,7,2,3,11,6,5,8,13,9,10,12,22,23,26', '思过崖思过', 'http://wx.qlogo.cn/mmopen/M9DHRlKhrAFmu0ljGmbwZ78ZHlUuPp8LsRgojFl1VU7Z0SEPniczLI3U5DwhBoyauGhpfUzAeDA40rugUEfyicIx7AGCoIYOXy/0', '1520396768', '2', '2', '0', '0', 'gh_e1083c8e9ef6', 'CUCIONE', 'http://mmbiz.qpic.cn/mmbiz_jpg/nMCGwywCQYKPj7Zf9yib2VgNJPw3Q269Du8WvUbKUONMicCey7p3cHQm2OYjViccZJq6lzzwvicZsBWXNZ0ZRibE2VQ/0', '{\"open_pay\":1,\"open_shake\":1,\"open_scan\":0,\"open_card\":0,\"open_store\":1}', '广州楚才信息科技有限公司', '1', '1', '2147', '67b0056909f8ac5f42add03323d1faa0', 'http://thinkagent.data.cuci.cc/wechat/api.push.html', '2017-12-20 13:34:52');
INSERT INTO `wechat_config` VALUES ('2', 'wx9f8095b66bc88bfb', '7_p_RmDcGDoNpAUN3qHRj3aG4GXauxxhm4sNguetuQNzpqCYXQBuPnMB7zGBOKyGhVvZN0R4OBGtt1vATXr6hKgkXYCVi54PmMna2HVgFaAWa6-r2EVRsEwO6bsjZwTY5pT9zoSWR3WtSLDzWbBKNaALDXGJ', 'refreshtoken@@@YrWo6eHsXX__IymCIQy7x8uT3fseYqvkodLoQn6rEu8', '1,15,4,7,2,3,11,6,5,8,13,9,10,12,22,23,24,26', '微商岁月', 'http://wx.qlogo.cn/mmopen/M9DHRlKhrAFmu0ljGmbwZ41I1Orsw19xKsMIJvTHic7g1tboVMHJ0Beo14o4LexNPGldXiauVb65GntXnE9e4zet02uAPobyTic/0', '1517219435', '2', '2', '0', '0', 'gh_a02cca438218', 'sancewenhua', 'http://mmbiz.qpic.cn/mmbiz_jpg/HYFianMIDA6jtibYibCxh0x8hMjWGBuBddII4NpZ7icW87bFOhofooLDnDeY85icDujrRaKicAfmvwT81dv4ZA1p8JHQ/0', '{\"open_pay\":1,\"open_shake\":0,\"open_scan\":0,\"open_card\":0,\"open_store\":0}', '义乌市三策文化传播有限公司', '1', '1', '2782', '45b328708494c468ca546288165c0352', 'http://wuma-client.data.cuci.cc/wechat/api.html', '2018-01-29 15:52:55');

-- ----------------------------
-- Table structure for wechat_fans
-- ----------------------------
DROP TABLE IF EXISTS `wechat_fans`;
CREATE TABLE `wechat_fans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '粉丝表ID',
  `appid` char(50) DEFAULT NULL COMMENT '公众号Appid',
  `unionid` char(100) DEFAULT NULL COMMENT 'unionid',
  `openid` char(100) NOT NULL DEFAULT '' COMMENT '用户的标识,对当前公众号唯一',
  `spread_openid` char(100) DEFAULT NULL COMMENT '推荐人OPENID',
  `tagid_list` varchar(100) DEFAULT '' COMMENT '标签id',
  `is_black` tinyint(1) unsigned DEFAULT '0' COMMENT '是否为黑名单用户',
  `subscribe` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '用户是否订阅该公众号,0：未关注,1：已关注',
  `spread_at` datetime DEFAULT NULL,
  `nickname` varchar(20) DEFAULT NULL COMMENT '用户的昵称',
  `sex` tinyint(1) unsigned DEFAULT NULL COMMENT '用户的性别,值为1时是男性,值为2时是女性,值为0时是未知',
  `country` varchar(50) DEFAULT NULL COMMENT '用户所在国家',
  `province` varchar(50) DEFAULT NULL COMMENT '用户所在省份',
  `city` varchar(50) DEFAULT NULL COMMENT '用户所在城市',
  `language` varchar(50) DEFAULT NULL COMMENT '用户的语言,简体中文为zh_CN',
  `headimgurl` varchar(500) DEFAULT NULL COMMENT '用户头像',
  `subscribe_time` bigint(20) unsigned DEFAULT NULL COMMENT '用户关注时间',
  `subscribe_at` datetime DEFAULT NULL COMMENT '关注时间',
  `remark` varchar(50) DEFAULT NULL COMMENT '备注',
  `expires_in` bigint(20) unsigned DEFAULT '0' COMMENT '有效时间',
  `refresh_token` varchar(200) DEFAULT NULL COMMENT '刷新token',
  `access_token` varchar(200) DEFAULT NULL COMMENT '访问token',
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `index_wechat_fans_spread_openid` (`spread_openid`) USING BTREE,
  KEY `index_wechat_fans_openid` (`openid`) USING BTREE,
  KEY `index_wechat_fans_unionid` (`unionid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微信粉丝';

-- ----------------------------
-- Records of wechat_fans
-- ----------------------------

-- ----------------------------
-- Table structure for wechat_fans_tags
-- ----------------------------
DROP TABLE IF EXISTS `wechat_fans_tags`;
CREATE TABLE `wechat_fans_tags` (
  `id` bigint(20) unsigned NOT NULL COMMENT '标签ID',
  `appid` char(50) DEFAULT NULL COMMENT '公众号APPID',
  `name` varchar(35) DEFAULT NULL COMMENT '标签名称',
  `count` int(11) unsigned DEFAULT NULL COMMENT '总数',
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建日期',
  KEY `index_wechat_fans_tags_id` (`id`) USING BTREE,
  KEY `index_wechat_fans_tags_appid` (`appid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微信会员标签';

-- ----------------------------
-- Records of wechat_fans_tags
-- ----------------------------
