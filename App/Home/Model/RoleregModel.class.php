<?php
namespace Home\Model;

class RoleregModel extends CommonModel
{
	//功能菜单图标
	//protected $tableName = 'userreg';
	//protected $dbName  = 'face';
	protected $field =  array('roleid','rolename','remark','functionlist');

	public function listAll()
	{
		$data = $this->field($this->field)->select();
		return $data;
	}

}