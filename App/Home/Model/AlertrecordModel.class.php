<?php
namespace Home\Model;

class AlertrecordModel extends CommonModel
{
	//可重写数据库  表名
	//protected $tableName;
	//protected $dbName;
	//字段
	protected $field = array('alertrecordid','alerttime','bodypicurl','facepicurl','employee_empid','alertpiccul','score','dev_devid','serinfo_serid','capresultid');

	//无差别获取数据库数据
	public function listAll()
	{
		$data = $this->field($this->field)->select();
		return $data;
	}
}