<?php
namespace Home\Controller;

class AlertsetController extends CommonController
{
	//表的表名-自增主键
	protected $tab_id = 'alertsetid';
	protected $tab = 'Alertset';
	protected $link_tab = 'alertsettocycle';
	//展示
	public function index()
	{
		$ucTab = ucwords($this->tab);
		$url['datagridUrl'] = U($ucTab.'/dataList');
		$url['addUrl'] = U($ucTab.'/dataAdd');
		$url['editUrl'] = U($ucTab.'/dataEdit');
		$url['removeUrl'] = U($ucTab.'/dataRemove');
		$url['save_cycle_dataUrl'] = U($ucTab.'/save_cycle_data');
		$this->assign('url',$url);
		$this->assignInfo();
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
	public function assignInfo()
	{
        $db = D('Dev');
        $info['dev'] = $db->listAll();
        $info['devJson'] = json_encode($info['dev']);
        $db = D('Photolib');
        $info['photolib'] = $db->listAll();
        $info['photolibJson'] = json_encode($info['photolib']);
		$this->assign('info',$info);
	}
	public function save_cycle_data()
	{
		$data = I();
		$alertcycles = explode(',',$data['alertcycles']);
		$db = D($this->link_tab);
		$insert_data['alertsetid'] = $data['alertsetid'];
		$db->where($insert_data)->delete();
		foreach ($alertcycles as $value) {
			$insert_data['alertcycleid'] = $value;
			$db->data($insert_data)->add();
		}
		$where['alertsetid'] = $insert_data['alertsetid'];
		$request['alerttype'] = 1;
		$db = D($this->tab);
		$db->getTableEdit($where,$request);
		$result['message'] = '保存成功';
		$this->ajaxReturn($result);
	}
}