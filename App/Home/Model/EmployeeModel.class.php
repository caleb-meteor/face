<?php
namespace Home\Model;

class EmployeeModel extends CommonModel
{
	//可重写数据库  表名
	//protected $tableName;
	//protected $dbName;
	//字段
	protected $field = array('empid','name','code','sex','phone','email','libnum','remark','areaid');

	//无差别获取数据库数据
	public function listAll()
	{
		$data = $this->field($this->field)->select();
		return $data;
	}
}