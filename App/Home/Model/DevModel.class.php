<?php
namespace Home\Model;

class DevModel extends CommonModel
{
	//可重写数据库  表名
	//protected $tableName;
	//protected $dbName;
	//字段
	protected $field = array('devid','devname','typeid','areaid','devip','port','rtspurl','serid','state','remark');

	//无差别获取数据库数据
	public function listAll()
	{
		$data = $this->field($this->field)->select();
		return $data;
	}
}