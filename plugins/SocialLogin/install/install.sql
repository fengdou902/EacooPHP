DROP TABLE IF EXISTS `eacoo_plugin_social_login`;

CREATE TABLE `eacoo_plugin_social_login` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `uid` int(11) unsigned NOT NULL COMMENT '用户ID',
  `type` varchar(15) NOT NULL DEFAULT '' COMMENT '类别',
  `openid` varchar(64) NOT NULL DEFAULT '' COMMENT 'OpenID',
  `access_token` varchar(64) NOT NULL DEFAULT '' COMMENT 'AccessToken',
  `refresh_token` varchar(64) NOT NULL DEFAULT '' COMMENT 'RefreshToken',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `sort` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='第三方登陆插件表';
