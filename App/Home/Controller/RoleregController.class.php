<?php
namespace Home\Controller;

class RoleregController extends CommonController 
{
	protected $tab_id = 'roleid';
	protected $tab = 'rolereg';
	protected $link_tab = 'Userreg';

	public function index()
	{
		$ucTab = ucwords($this->tab);
		$url['datagridUrl'] = U($ucTab.'/dataList');
		$url['addUrl'] = U($ucTab.'/dataAdd');
		$url['editUrl'] = U($ucTab.'/dataEdit');
		$url['removeUrl'] = U($ucTab.'/dataRemove');
		$url['roleMenuUrl'] = U($ucTab.'/roleMenu');
		$url['saveMenuUrl'] = U($ucTab.'/saveMenu');
		$url['menuListUrl'] = U('Functionreg/dataList');		
		$this->assign('url',$url);
		$this->display($this->tab);
	}

	public function dataList()
	{
		$request = I();
		$page = I('page');
		$rows = I('rows');
		//$where = $request['rolename'] ? 'rolename like \'%'.$request['rolename'].'%\'' : null;
		if($request['rolename']){
			$check['rolename'] = array('like','%'.$request['rolename'].'%');
		}
		$db = D('rolereg');

		//初始数据展示限制只显示自身和下级角色
		$where['roleid'] = session('roleid');
		$data = $db->where($where)->select();
		$l_arr = [0=>'roleid',1=>'proleid'];
		$info_f = $this->getChData($data,$this->tab,$l_arr);
		$info_f= array_merge($data,$info_f);
		$all_list = array();
		foreach ($info_f as  $info_c) {
			$all_list[] = $info_c['roleid'];
		}
		$check['roleid'] = array('in',$all_list);

		$data = $db->getTableList($check,$page,$rows);
		$this->ajaxReturn($data);
	}

	public function dataAdd()
	{
		$request = I();
		$db = D($this->tab);
		$where['rolename'] = $request['rolename'];
		if($db->checkExistence($where)){
			$result['message'] = '角色已存在！换一个吧';
            $result['status']  = true;
		}else{
			$request['proleid'] = session('roleid');
			$result = $db->getTableAdd($request);
		}		
		$this->ajaxReturn($result);
	}

	public function dataRemove()
	{
		$request = I();
		$db = D($this->tab);
		//检查外键表是否存在删除的角色
		$link_db = D($this->link_tab);
		$check = $link_db->where("roleid in('".$request[$this->tab_id]."')")->select();
		if(!empty($check)){
			$result['message'] = '删除失败，请先删除分配有该角色的用户！';
            $result['status']  = true;
		}else{
			$where = $this->tab_id.' in('.$request[$this->tab_id].')';
			$result = $db->getTableDel($where);		
		}
		$this->ajaxReturn($result);
	}

	public function dataEdit()
	{
		$request = I();
		$db = D($this->tab);
		$where[$this->tab_id] = $request[$this->tab_id];
		unset($request[$this->tab_id]);
		$result = $db->getTableEdit($where,$request);
		$this->ajaxReturn($result);
	}

	public function roleMenu()
	{
		$request = I();
		$db = D($this->tab);
		$where[$this->tab_id] = $request[$this->tab_id];
		$menu = $db->where($where)->field('functionlist')->find();
		echo $menu ? $menu['functionlist'] : '';
	}

	public function saveMenu()
	{
		$request = I();
		$db = D($this->tab);
		/*$check['roleid'] = session('roleid');
		$fun = $db->where($check)->find();*/
		$userfun = explode(',', session('menu'));
		$putfun = explode(',', $request['functionlist']);
		//计算出权限赋值与当前用户实际拥有权限的交集
		$intersect = array_intersect($putfun,$userfun);
		$savefun['functionlist'] = implode(',', $intersect);
		$where[$this->tab_id] = $request[$this->tab_id];
		unset($request[$this->tab_id]);
		$result = $db->getTableEdit($where,$savefun);
		$this->ajaxReturn($result);
	}
}