<?php
namespace Home\Model;

class AlertsetModel extends CommonModel
{
	//可重写数据库  表名
	//protected $tableName;
	//protected $dbName;
	//字段
	protected $field = array('alertsetid','name','devid','libid','score','alerttype','btime','etime','state');

	//无差别获取数据库数据
	public function listAll()
	{
		$data = $this->field($this->field)->select();
		return $data;
	}
}