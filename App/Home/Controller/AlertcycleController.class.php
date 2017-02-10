<?php
namespace Home\Controller;

class AlertcycleController extends CommonController
{
	//表的表名-自增主键
	protected $tab_id = 'alertcycleid';
	protected $tab = 'Alertcycle';
	protected $link_tab = 'Alertsettocycle';
	//展示
	public function index()
	{
		$alertsetid = I('alertsetid');
		$ucTab = ucwords($this->tab);
		$url['datagridUrl'] = U($ucTab.'/dataList');
		$url['addUrl'] = U($ucTab.'/dataAdd');
		$url['editUrl'] = U($ucTab.'/dataEdit');
		$url['removeUrl'] = U($ucTab.'/dataRemove');
		$url['selectedDataUrl'] = U($ucTab.'/selectedData');
		$this->assign('url',$url);
		$this->assignInfo($alertsetid);
		$this->display(strtolower($this->tab));
	}
	//数据获取
	public function dataList()
	{
		$request = I();
		$page = I('page');
		$rows = I('rows');
		unset($request['page'],$request['rows'],$request['rand']);
		$check = null;
		if(!empty($request)){
			foreach($request as $key=>$value){
				$check[$key] = array('like','%'.$value.'%');
			}
		}
		$db = D($this->tab);
		$data = $db->getTableList($check,$page,$rows);
		$this->ajaxReturn($data);
	}
	//增加事件
	public function dataAdd()
	{
		$request = I();
		unset($request[$this->tab_id]);
		$db = D($this->tab);
		$result = $db->getTableAdd($request);
		$this->ajaxReturn($result);
	}
	//删除事件
	public function dataRemove()
	{
		$request = I();
		$db = D($this->tab);
		$where = $this->tab_id.' in('.$request[$this->tab_id].')';
		$result = $db->getTableDel($where);
		$this->ajaxReturn($result);
	}
	//编辑事件
	public function dataEdit()
	{
		$request = I();
		$db = D($this->tab);
		$where[$this->tab_id] = $request[$this->tab_id];
		unset($request[$this->tab_id]);
		$result = $db->getTableEdit($where,$request);
		$this->ajaxReturn($result);
	}
	//模板传值
	public function assignInfo($alertsetid)
	{
		$this->assign('info',$info);
	}
	public function selectedData()
	{
		$alertsetid = I('alertsetid');
		$where['alertsetid'] = $alertsetid;
		$link_db = D($this->link_tab);
		$info = $link_db->where($where)->getField('alertcycleid',true);
		$this->ajaxReturn($info);
	}
}