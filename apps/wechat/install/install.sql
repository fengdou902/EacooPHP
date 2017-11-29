
CREATE TABLE IF NOT EXISTS `eacoo_wechat` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `name` varchar(50) NOT NULL COMMENT '公众号名称',
  `login_name` varchar(50) DEFAULT NULL COMMENT '公众号登录名',
  `type` int(1) unsigned NOT NULL DEFAULT '0' COMMENT '公众号类型（1：普通订阅号；2：认证订阅号；3：普通服务号；4：认证服务号',
  `origin_id` varchar(50) NOT NULL COMMENT '公众号原始ID',
  `weixin_number` varchar(50) DEFAULT NULL COMMENT '微信号',
  `valid_token` varchar(40) DEFAULT NULL COMMENT '接口验证Token',
  `token` varchar(50) DEFAULT NULL COMMENT '公众号标识',
  `mch_id` varchar(50) DEFAULT NULL COMMENT '商户号',
  `mch_key` varchar(60) DEFAULT NULL,
  `encodingaeskey` varchar(50) DEFAULT NULL COMMENT '消息加解密秘钥',
  `appid` varchar(50) NOT NULL DEFAULT '' COMMENT 'AppId',
  `appsecret` varchar(50) NOT NULL DEFAULT '' COMMENT 'AppSecret',
  `headimg` int(11) unsigned DEFAULT NULL COMMENT '头像',
  `qrcode` int(11) unsigned DEFAULT NULL COMMENT '二维码',
  `description` text COMMENT '描述',
  `create_time` int(10) unsigned NOT NULL COMMENT '创建时间',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态（0：禁用，1：正常，2：审核中）',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='公众号表';


CREATE TABLE IF NOT EXISTS `eacoo_wechat_material` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `wxid` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '公众号标识',
  `wx_media_id` varchar(100) DEFAULT '0' COMMENT '微信端素材的media_id',
  `type` varchar(50) DEFAULT NULL COMMENT '素材类型',
  `description` text COMMENT '素材描述',
  `content` text COMMENT '文本素材内容',
  `attachment_id` int(11) unsigned DEFAULT '0' COMMENT '图片素材路径',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '素材标题',
  `url` varchar(255) DEFAULT NULL COMMENT '素材链接',
  `news_content` longtext COMMENT '图文素材描述',
  `group_id` int(10) unsigned DEFAULT '0' COMMENT '多图文组的ID',
  `fields` text COMMENT '扩展字段',
  `create_time` int(10) unsigned DEFAULT '0' COMMENT '素材创建时间',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '状态',
  PRIMARY KEY (`id`),
  KEY `wxid` (`wxid`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='公众号素材表';


CREATE TABLE IF NOT EXISTS `eacoo_wechat_reply` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `wxid` tinyint(3) unsigned NOT NULL COMMENT '公众号标识',
  `type` varchar(50) DEFAULT NULL COMMENT '回复场景',
  `reply_type` varchar(50) DEFAULT NULL COMMENT '回复类型',
  `material_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '回复素材ID',
  `keyword` varchar(50) DEFAULT NULL COMMENT '绑定的关键词',
  `addon` varchar(50) DEFAULT NULL COMMENT '处理消息的插件',
  `status` tinyint(3) DEFAULT '1' COMMENT '状态',
  PRIMARY KEY (`id`),
  KEY `idx_keyword` (`keyword`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='公众号自动回复';


CREATE TABLE IF NOT EXISTS `eacoo_wechat_user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `openid1` varchar(30) DEFAULT '' COMMENT 'openid',
  `uid` bigint(20) unsigned NOT NULL,
  `nickname` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '昵称',
  `subscribe` tinyint(1) NOT NULL DEFAULT '1',
  `subscribe_time` int(10) unsigned NOT NULL,
  `sex` tinyint(1) NOT NULL,
  `city` varchar(255) NOT NULL DEFAULT '' COMMENT '城市',
  `country` varchar(255) NOT NULL DEFAULT '' COMMENT '国家',
  `province` varchar(255) NOT NULL DEFAULT '' COMMENT '省份',
  `headimgurl` varchar(255) NOT NULL DEFAULT '' COMMENT '头像',
  `unionid` varchar(30) NOT NULL,
  `last_update` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `unionid` (`unionid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

