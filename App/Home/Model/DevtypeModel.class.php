<?php
namespace Home\Model;

class DevtypeModel extends CommonModel
{
	//功能菜单图标
	//protected $tableName;
	//protected $dbName;
	protected $field = array('typeid','typename');

	public function listAll()
	{
		$data = $this->field($this->field)->select();
		return $data;
	}
}