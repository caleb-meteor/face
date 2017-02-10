<?php
namespace Home\Model;

class FunctionregModel extends CommonModel
{
	//功能菜单
	//protected $tableName = 'functionreg';
	//protected $dbName  = 'face';
	protected $fields = array('funid','prefunid','funname','url','ordernum','iconcls');
	
	public function funListAll()
	{
		$data = $this->field($this->fields)->select();
		return $data;
	}
}