<?php
namespace Home\Model;

class ArearegModel extends CommonModel
{
	//功能菜单图标
	//protected $tableName;
	//protected $dbName;
	protected $field = array('areaid','proid','fatherareaid','areaname','areacode','rperson','rphone');

	public function listAll()
	{
		$data = $this->field($this->field)->select();
		return $data;
	}
}