
# Dump of table eacoo_postmeta
# ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `eacoo_postmeta` (
  `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `post_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `meta_key` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_value` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`meta_id`),
  KEY `idx_postid_metakey` (`post_id`,`meta_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `eacoo_posts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `title` varchar(255) NOT NULL DEFAULT '0' COMMENT '标题',
  `slug` varchar(200) NOT NULL DEFAULT '',
  `type` varchar(20) NOT NULL DEFAULT '' COMMENT '文章类型,post,page,product',
  `source` varchar(100) DEFAULT NULL COMMENT '来源',
  `excerpt` text COMMENT '摘要',
  `content` longtext NOT NULL COMMENT '内容',
  `author_id` int(11) unsigned NOT NULL COMMENT '作者',
  `seo_keywords` tinytext COMMENT 'seo_keywords',
  `img` int(11) unsigned DEFAULT '0' COMMENT '封面图片',
  `views` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '浏览数',
  `collection` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '收藏量',
  `comment_count` int(11) unsigned DEFAULT '0',
  `parent` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'post的父级post id,表示post层级关系',
  `password` varchar(32) DEFAULT NULL,
  `fields` varchar(300) DEFAULT NULL COMMENT 'post的扩展字段，保存相关扩展属性，如缩略图；格式为json',
  `istop` tinyint(1) unsigned DEFAULT '0' COMMENT '置顶 1置顶； 0不置顶',
  `recommended` tinyint(1) DEFAULT '0' COMMENT '推荐 1推荐 0不推荐，大于1的数字可设定为不同推荐区',
  `publish_time` int(10) unsigned DEFAULT '0' COMMENT '发布时间',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `sort` int(10) unsigned DEFAULT '99' COMMENT '排序号',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 -1 删除 0审核 1为已发布',
  PRIMARY KEY (`id`),
  KEY `idx_type` (`type`),
  KEY `idx_author_id` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='文章表';

INSERT INTO `eacoo_posts` (`id`, `title`, `slug`, `type`, `source`, `excerpt`, `content`, `author_id`, `seo_keywords`, `img`, `views`, `collection`, `comment_count`, `parent`, `password`, `fields`, `istop`, `recommended`, `publish_time`, `create_time`, `update_time`, `sort`, `status`) VALUES
(1, '揭秘eBay四大系统 从行为数据中寻找价值', '', 'post', NULL, '', '<p style="box-sizing: border-box; margin-top: 0px; margin-bottom: 16px; color: rgb(102, 102, 102); font-family: \'Microsoft Yahei\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; white-space: normal;">喜欢海淘的朋友应该对eBay并不陌生，如果你还不了解，可以把eBay+PayPal理解为淘宝+支付宝的组合，当然eBay不仅有C2C还有B2C的模式，甚至还有二手卖家。</p><p style="box-sizing: border-box; margin-top: 0px; margin-bottom: 16px; color: rgb(102, 102, 102); font-family: \'Microsoft Yahei\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; white-space: normal;">铺垫了一些背景，我们再来说说电子商务，现在还有没网购过的同学请举手，1、2、3……可能没有几个。虽然大家都在各种电子商务网站上购过物，但是你是否知道你在网上的一切行为都已经被记录并进行分析。</p><p style="box-sizing: border-box; margin-top: 0px; margin-bottom: 16px; color: rgb(102, 102, 102); font-family: \'Microsoft Yahei\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; white-space: normal;">不论国外还是国内的电子商务企业，他们的相同点都是以业务为导向。eBay的做法是用数据驱动商业，其上所有的数据产品都是针对业务而生，数据部门需要对不断变化的用户需求找到解决之法，也就是从客户的行为数据中来寻找价值。</p><h3 style="box-sizing: border-box; font-family: \'Microsoft Yahei\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-weight: normal; line-height: 1.1; color: rgb(68, 68, 68); margin-top: 20px; margin-bottom: 16px; font-size: 16px; border-bottom-color: rgb(238, 238, 238); border-bottom-width: 1px; border-bottom-style: solid; padding-bottom: 0px; white-space: normal;"><strong style="box-sizing: border-box;">行为数据用混合的手段来处理</strong></h3><p style="box-sizing: border-box; margin-top: 0px; margin-bottom: 16px; color: rgb(102, 102, 102); font-family: \'Microsoft Yahei\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; white-space: normal;">数据是eBay发展的基础和价值所在，所以eBay数据服务和解决方案团队从eBay成立的第一天就已经存在，从数据仓库到数据分析再到数据服务，部门的名字一直随着发展在不断变化。但万变不离其宗，数据服务和解决方案团队就是一个针对数据展开想象的部门。</p><p style="box-sizing: border-box; margin-top: 0px; margin-bottom: 16px; color: rgb(102, 102, 102); font-family: \'Microsoft Yahei\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; white-space: normal;">eBay数据服务和解决方案团队分布在美国西雅图、圣何塞以及中国上海，而中国团队全职和外包人员总共将近有100人，其中有不同的职位和分工，包括数据科学家、数据工程师、商业需求分析师、产品经理四大类。两个区域的团队互相协作，共同开发核心数据的同时也支持不同的业务部门。</p><p><br/></p>', 1, '', 68, 0, 0, 0, 0, NULL, '', 0, 0, 1508063880, 1464081408, 1508063929, 99, 1),
(2, '谷歌数据中心安全及设计的最佳实践', '', 'post', NULL, '', '<p>在首次云端平台使用者大会(Google Cloud Platform Global User Conference)上，谷歌的两位领导者——数据中心的运营副总裁Joe Kava和安全隐私方面的优秀工程师Niels Provos向与会者分享了谷歌在全球范围内设计、构建、运行和保护数据中心的实践方式，其中包含一些令谷歌的数据中心独一无二的秘诀，及其对于谷歌云端平台用户的意义。\r\n\r\n安全性和数据保护sdf\r\n\r\n谷歌一直以来将重心放在数据的安全和保护上，这也是我们的关键设计准则之一。在物理安全方面，我们以分层安全模型为特色，使用了如定制的电子访问卡、警报器、车辆进出限制、围栏架设、金属探测器及生物识别技术等保障措施。数据中心的地板配备了激光束入侵探测器，并安装了高清晰度的内外监视器，全天候检测追踪入侵行为。此外为以防万一，可随时调用访问日志、活动记录以及监控录像。\r\n\r\n同时数据中心还安排了经验丰富的保安人员每日例行巡逻，他们已接受过背景调查与严格的培训(可以点击查看数据中心的360度视频)。越靠近数据中心，安全措施系数就越高，只有一条安全通道能进入数据中心，通过安全徽章和生物识别技术来实现多重访问控制，只有特定职位的员工才有权进入。在整个谷歌公司，只有不到1%的员工曾踏足此区域。\r\n\r\n我们还采用了非常严格的点对点监管链，用于储存、追踪全过程——从第一次HD输入机器直至证实其已被销毁或清除。同时，我们采用了信息安全和物理安全双管齐下的方式，由于数据通过网络传输的特性，若未经授权可随意访问的话就会非常危险。有鉴于此，谷歌将数据传输过程中的信息保护摆在优先位置上，用户设备与谷歌间的数据传输通常都是利用HTTPS/TLS(安全传输层协议)来进行加密输送。谷歌是第一个默认启用HTTPS/TLS的主要云服务提供商。</p>', 1, '', 93, 0, 0, 0, 0, NULL, '', 0, 1, 1508063820, 1464081797, 1508063874, 99, 1),
(3, '机器学习专家带你实践LSTM语言模型', '', 'post', NULL, '', '<p>测试</p><p><br></p>', 1, '', 94, 0, 0, 0, 0, NULL, '', 0, 0, 1508064480, 1464081899, 1508064489, 99, 1),
(4, '大撒发送大撒发送', '', 'page', NULL, '', '<p style="text-align:center"><br/></p><p>这是编辑的内容就gsadfasdfasfd</p><p></p>', 1, '', 1164, 0, 0, 0, 0, NULL, '', 0, 0, 0, 1464153628, 1506823903, 99, -1),
(5, '贝恩：企业大数据战略指南', '', 'post', NULL, '这是摘要dgs', '<p>企业大数据战略指南</p><p><br></p><p><img class="" src="http://localhost/ZhaoCMF/Uploads/Picture/2016-09-26/57e8ddc3e1455.jpeg" data-id="363"></p><p>fsafsaf</p><p><br></p>', 1, '关键字1', 88, 0, 0, 0, 0, NULL, NULL, 1, 0, 1499913000, 1464791552, 1508071031, 99, 1),
(6, '发撒范德萨', '', 'post', NULL, '', '<p>撒发达范德萨发送</p>', 1, '', 27, 0, 0, 0, 0, NULL, NULL, 0, 0, 1508064000, 0, 1508064154, 99, 1),
(7, '关于我们', '', 'page', NULL, NULL, '<p>这是关于我们的内容，测试</p>', 1, '发达啊撒旦法撒发撒旦法按时', NULL, 0, 0, 0, 0, NULL, NULL, 0, 0, NULL, 1467857339, 1506824231, 99, 1);

# 安装关联分类
INSERT INTO `eacoo_term_relationships` ( `object_id`, `term_id`, `table`, `uid`, `sort`, `status`)
VALUES
  (3, 5, 'posts', 0, 0, 1),
  (5, 6, 'posts', 0, 0, 1),
  (2, 6, 'posts', 0, 0, 1),
  (1, 6, 'posts', 0, 0, 1),
  (4, 1, 'posts', 0, 0, 1),
  (6, 1, 'posts', 0, 0, 1);
