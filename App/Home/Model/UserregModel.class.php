<?php
namespace Home\Model;

class UserregModel extends CommonModel
{
	//功能菜单图标
	//protected $tableName = 'userreg';
	//protected $dbName  = 'face';
	protected $field =  array('userid','areaid','username','userpassword','roleid','bindingip','clientip','truename','sex','mobile','email','usertag','fatherid','state');

	public function check_exist($where)
	{
		$data = $this->where($where)->find();
		if(!empty($data)){
			return $data;
		}else{
			return false;
		}
	}
	public function listAll()
	{
		$data = $this->field($this->field)->select();
		return $data;
	}
}