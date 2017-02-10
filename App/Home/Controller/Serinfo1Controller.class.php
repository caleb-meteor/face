<?php
namespace Home\Controller;

class SerinfoController extends CommonController
{
	//表的表名-自增主键
	protected $tab_id = 'serid';
	protected $tab = 'serinfo';
	//展示
	public function index()
	{
		$ucTab = ucwords($this->tab);
		$url['datagridUrl'] = U($ucTab.'/dataList');
		$url['addUrl'] = U($ucTab.'/dataAdd');
		$url['editUrl'] = U($ucTab.'/dataEdit');
		$url['removeUrl'] = U($ucTab.'/dataRemove');
		$this->assign('url',$url);
		$this->assignInfo();
		$this->display($this->tab);
	}
	//数据获取
	public function dataList()
	{
		$request = I();
		$page = I('page');
		$rows = I('rows');
		unset($request['page'],$request['rows'],$request['rand']);
		if(!empty($request)){
			foreach($request as $key=>$value){
				if($key!='serid')
					$check[$key] = array('like','%'.$value.'%');
			}
		}
		$db = D($this->tab);

		/*$areaid = $request['serid'];
		//初始数据展示限制只显示自身和下级角色
		$where['serid'] = $areaid;
		$data = $db->where($where)->select();
		$l_arr = [0=>'serid',1=>'pserid'];
		$info_f = $this->getChData($data,$this->tab,$l_arr);
		$info_f= array_merge($data,$info_f);
		$all_list = array();
		foreach ($info_f as  $info_c) {
			$all_list[] = $info_c['serid'];
		}
		$check['serid'] = array('in',$all_list);*/

		$data = $db->getTableList($check,$page,$rows);
		$this->ajaxReturn($data);
	}
	//增加事件
	public function dataAdd()
	{
		$request = I();
		unset($request[$this->tab_id]);
		$db = D($this->tab);
		$where['sername'] = $request['sername'];
		if($db->checkExistence($where)){
			$result['message'] = '服务名已存在！换一个吧';
            $result['status']  = true;
		}else{
			$result = $db->getTableAdd($request);
		}
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
	public function assignInfo()
	{
        $db = D('Devtype');
        $info['devtype'] = $db->listAll();
        $info['devtypeJson'] = json_encode($info['devtype']);

        $db = D($this->tab);
        $info['serinfo'] = $db->listAll();
        $info['serinfoJson'] = json_encode($info['serinfo']);
		$this->assign('info',$info);
	}
	public function data_tree_list()
	{
		$db = D($this->tab);

		$data = $db->listAll();
		$ids = array(0);
		//$l_arr 保存菜单的一些信息  0-id  1-text 2-iconCls 3-fid 4-odr
		$l_arr = ['serid','sername','pserid','serid'];
		//$L_attributes 额外需要保存的信息
		$L_attributes = ['typeid'];
		$icons = ['icon-computer_go','icon-computer'];
		$data_tree = $this->formatTree($ids,$data,$l_arr,$L_attributes,'',$icons);
		echo json_encode($data_tree);
	}
}