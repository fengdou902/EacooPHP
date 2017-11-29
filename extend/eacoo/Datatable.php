<?php
namespace eacoo;
use think\Db;

/**
 * 数据库管理类
 */
class Datatable{
	
	protected $table;							/*数据库操作的表*/
	protected $fields = array();				/*数据库操作字段*/
	protected $charset = 'utf8';				/*数据库操作字符集*/
	public $prefix = '';						/*数据库操作表前缀*/
	protected $model_table_prefix = '';			/*模型默认创建的表前缀*/
	protected $engine_type = 'InnoDB';			/*数据库引擎*/
	protected $key = 'id';						/*数据库主键*/
	public $sql = '';							/*最后生成的sql语句*/

	/**
	 * 初始化数据库信息
	 */
	public function __construct(){
		//创建DB对象
		$this->prefix = config('database.prefix');
		$this->model_table_prefix = config('model_table_prefix');
	}

	/**
	 * 开始创建表
	 * @var $table 表名
	 */
	public function start_table($table){
		$this->table = $this->getTablename($table,true);
		$this->sql .= "CREATE TABLE IF NOT EXISTS `".$this->table."`(";
		return $this;
	}

	/**
	 * 创建字段
	 * @var $sql 要执行的字段sql语句可以为array()或者strubf
	 */
	public function create_field($sql){
		$this->sql .= $sql.',';
		return $this;
	}

	/**
	 * 快速创建ID字段
	 * @var length 字段的长度
	 * @var comment 字段的描述
	 */
	public function create_id($key = 'id', $length = 11 , $comment = '主键' , $is_auto_increment = true){
		$auto_increment = $is_auto_increment ? 'AUTO_INCREMENT' : '';
		$this->sql .= "`{$key}` int({$length}) unsigned NOT NULL $auto_increment COMMENT '{$comment}',";
		return $this;
	}
	/**
	 * 快速创建ID字段
	 * @var length 字段的长度
	 * @var comment 字段的描述
	 */
	public function create_uid(){
		$this->sql .= "`uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户uid',";
		return $this;
	}

	/**
	 * 追加字段
	 * @var $table 追加字段的表名 
	 * @var $attr 属性列表
	 * @var $is_more 是否为多条同时插入
	 */
	public function colum_field($table,$attr = array()){
		$field_attr['table'] = $table ? $this->getTablename($table,true) : $this->table;
		$field_attr['field'] = $attr['field'];
		$field_attr['type'] = $attr['type'] ? $attr['type'] : 'varchar';
		if (intval($attr['length']) && $attr['length']) {
			$field_attr['length'] = "(".$attr['length'].")";
		}else{
			$field_attr['length'] = "";
		}
		$field_attr['is_null'] = $attr['is_null'] ? 'NOT NULL' : 'null';
		$field_attr['default'] = $attr['default'] != '' ? 'default "'.$attr['default'].'"' : 'default null';
		if($field_attr['is_null'] == 'null'){
			$field_attr['default'] = $field_attr['default'];
		}else{
			$field_attr['default'] = '';
		}
		$field_attr['comment'] = (isset($attr['comment']) && $attr['comment']) ? $attr['comment'] : '';
		$field_attr['oldname'] = (isset($attr['oldname']) && $attr['oldname']) ? $attr['oldname'] : '';
		$field_attr['newname'] = (isset($attr['newname']) && $attr['newname']) ? $attr['newname'] : $field_attr['field'];
		$field_attr['after'] = (isset($attr['after']) && $attr['after']) ? ' AFTER `'.$attr['after'].'`' : '';
		$field_attr['action'] = (isset($attr['action']) && $attr['action']) ? $attr['action'] : 'ADD';
		//确认表是否存在

		if($field_attr['action'] == 'ADD'){
			$this->sql = "ALTER TABLE `{$field_attr['table']}` ADD `{$field_attr['field']}` {$field_attr['type']}{$field_attr['length']} {$field_attr['is_null']} {$field_attr['default']} COMMENT '{$field_attr['comment']}'";
		}elseif($field_attr['action'] == 'CHANGE'){
			$this->sql = "ALTER TABLE `{$field_attr['table']}` CHANGE `{$field_attr['oldname']}` `{$field_attr['newname']}` {$field_attr['type']}{$field_attr['length']} {$field_attr['is_null']} {$field_attr['default']} COMMENT '{$field_attr['comment']}'";
		}
		return $this;
	}

	/**
	 * 删除字段
	 * @var $table 追加字段的表名 
	 * @var $field 字段名
	 */
	public function del_field($table,$field){
		$table = $table ? $this->getTablename($table,true) : $this->table;
		$this->sql = "ALTER TABLE `$table` DROP `$field`";
		return $this;
	}

	/**
	 * 删除数据表
	 * @var $table 追加字段的表名 
	 */
	public function del_table($table){
		$table = $table ? $this->getTablename($table,true) : $this->table;
		$this->sql = "DROP TABLE `$table`";
		return $this;
	}

	
	/**
	 * 主键设置
	 * @var $key 要被设置主键的字段
	 */
	public function create_key($key = null){
		if(null != $key){
			$this->key = $key;
		}
		$this->sql .= "PRIMARY KEY (`".$this->key."`)";
		return $this;
	}

	/**
	 * 结束表
	 * @var $engine_type 数据库引擎
	 * @var $comment 表注释
	 * @var $charset 数据库编码
	 */
	public function end_table($comment,$engine_type = null,$charset = null){
		if(null != $charset){
			$this->charset = $charset;
		}
		if(null != $engine_type){
			$this->engine_type = $engine_type;
		}
		$end = "ENGINE=".$this->engine_type." AUTO_INCREMENT=1 DEFAULT CHARSET=".$this->charset." ROW_FORMAT=DYNAMIC COMMENT='".$comment."';";
		$this->sql .= ")".$end;
		return $this;
	}

	/**
	 * 创建动作
	 * @return int 0
	 */
	public function create(){
		$res = Db::execute($this->sql);
		return $res !== false;
	}

	/**
	 * create的别名
	 * @return int 0
	 */
	public function query(){
		return $this->create();
	}

	/**
	 * 获取最后生成的sql语句
	 */
	public function getLastSql(){
		return $this->sql;
	}

	/**
	 * 获取指定的表名
	 * @var $table 要获取名字的表名
	 * @var $prefix 获取表前缀？ 默认为不获取 false
	 */
	public function getTablename($table , $prefix = false){
		if(false == $prefix){
			$this->table = $this->model_table_prefix.$table;
		}else{
			$this->table = $this->prefix.$this->model_table_prefix.$table;
		}
		return $this->table;
	}

	/**
	 * 获取指定表名的所有字段及详细信息
	 * @var $table 要获取名字的表名 可以为sent_tengsu_photo、tengsu_photo、photo
	 */
	public function getFields($table){
		if(false == $table){
			$table = $this->table;//为空调用当前table
		}else{
			$table = $table;
		}
		$patten = "/\./";
		if(!preg_match_all($patten,$table)){
			//匹配_
			$patten = "/_+/";
			if(!preg_match_all($patten, $table)){
				$table = $this->prefix.$this->model_table_prefix.$table;
			}else{
				//匹配是否包含表前缀，如果是 那么就是手动输入
				$patten = "/$this->prefix/";
				if(!preg_match_all($patten,$table)){
					$table = $this->prefix.$table;
				}
			}
		}
		$sql = "SHOW FULL FIELDS FROM $table";
		return Db::query($sql);
	}

	/**
	 * 确认表是否存在
	 * @var $table 表名 可以为sent_tengsu_photo、tengsu_photo、photo
	 * @return boolen
	 */
	public function CheckTable($table){
		//获取表名
		$this->table = $this->getTablename($table,true);
		$result = Db::execute("SHOW TABLES LIKE '%$this->table%'");
		return $result;
	}

	/**
	 * 确认字段是否存在
	 * @var $table 表名 可以为sent_tengsu_photo、tengsu_photo、photo
	 * @var $field 字段名 要检查的字段名
	 * @return boolen
	 */
	public function CheckField($table,$field){
		//检查字段是否存在
		$table = $this->getTablename($table,true);
		if(!Db::query("Describe $table $field")){
			return false;
		}else{
			return true;
		}
	}
}