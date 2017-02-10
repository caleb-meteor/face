<?php
namespace Home\Model;

class AreaproModel extends CommonModel
{
	//功能菜单图标
	//protected $tableName;
	//protected $dbName;
	protected $field = array('proid','proname');

	public function listAll()
	{
		$data = $this->field($this->field)->select();
		return $data;
	}
}