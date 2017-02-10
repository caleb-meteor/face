<?php
namespace Home\Controller;

class ArearegController extends CommonController 
{
	protected $tab_id = 'areaid';
	protected $tab = 'areareg';
	protected $link_tab = 'Userreg';
	protected $remove_link_tabs = ['Dev','Employee'];

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

	public function dataList()
	{
		$request = I();
		$page = I('page');
		$rows = I('rows');
		unset($request['page'],$request['rows'],$request['rand']);
		if(!empty($request)){
			foreach($request as $key=>$value){
				if($key!='areaid')
					$check[$key] = array('like','%'.$value.'%');
			}
		}
		$db = D($this->tab);

		$userarea = $this->userarea();

		$areaid = $request['areaid'];
		//初始数据展示限制只显示自身和下级角色
		$all_list = $this->carea($areaid);
		//将不属于自身区域的数据排除
		$all_list = array_intersect($all_list, $userarea);

		$check['areaid'] = array('in',$all_list);
		if(!empty($all_list)){
			$data = $db->getTableList($check,$page,$rows);
		}else{
			$data['total'] = 0;
			$data['rows'] = array();
		}		
		$this->ajaxReturn($data);
	}

	public function dataAdd()
	{
		$request = I();
		$db = D($this->tab);
		$userarea = $this->userarea();
		$result = $db->getTableAdd($request);
		$add_area = $result['add_id'];
		//增加时将所有自身,父用户添加相关区域
		$link_db = D($this->link_tab);
		$userarea[] = $add_area;
		$data['userarea'] = implode(',', $userarea);
		$where['userid'] = session('userid');
		$link_db->getTableEdit($where,$data);
				
		$puserarea = $this->puserarea();
		foreach ($puserarea as $key => $value) {
			$value[] = $add_area;
			$data['userarea'] = implode(',', $value);
			$where['userid'] = $key;
			$link_db->getTableEdit($where,$data);
		}
		$this->ajaxReturn($result);
	}

	public function dataRemove()
	{
		$request = I();
		$db = D($this->tab);
		$userarea = $this->userarea();
		$removearea = explode(',', $request[$this->tab_id]);
		//算出用户管理的区域与要删除的区域的交集 得到真正能删除的区域
		$intersect = array_intersect($removearea,$userarea);
		if(!empty($intersect)){
			$link_db = D($this->link_tab);
			//删除时将所有自身,父,子用户包含相关区域一起删除 保留剩下的区域
			$holdArae = array_diff($userarea, $intersect);
			$data['userarea'] = implode(',', $holdArae);
			$check['userid'] = session('userid');
			$result = $link_db->getTableEdit($check,$data);	
			$puserarea = $this->puserarea();
			foreach ($puserarea as $key => $value) {
				$holdArae = array_diff($value, $intersect);
				$data['userarea'] = implode(',', $holdArae);
				$check_p['userid'] = $key;
				$result = $link_db->getTableEdit($check_p,$data);
			}	
			$cuserarea = $this->cuserarea();
			foreach ($cuserarea as $key => $value) {
				$holdArae = array_diff($value, $intersect);				
				$data['userarea'] = implode(',', $holdArae);
				$check_c['userid'] = $key;
				$result = $link_db->getTableEdit($check_c,$data);
			}
			$where[$this->tab_id] = array('in',$intersect);	
			$result = $db->getTableDel($where);
			//删除与区域相关表的数据
			foreach ($this->remove_link_tabs as $tab) {
				$db_rm = D($tab);
				$db_rm->getTableDel($where);
			}
		}else{
			$result['message'] = '对不起,你没有权限删除这些区域';
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

	public function assignInfo()
	{
		$db = D('Areapro');
		$info['areapro'] = $db->listAll();
		$info['proJson'] = json_encode($info['areapro']);
		$db = D('Areareg');
		$info['areareg'] = $db->listAll();
		$info['arearegJson'] = json_encode($info['areareg']);
		$this->assign('info',$info);
	}
	/**
	 * 获取当前用户管理区域
	 * @return array easyui-tree
	 */
	public function tree_list()
	{
		$db = D($this->tab);

		$userarea = $this->userarea();

		$data_f = array();
		$data_s = array();
		if(!empty($userarea)){
			$where['areaid'] = array('in',$userarea);
			$data_f = $db->where($where)->select();
		}
		if(!empty($data_f)){
			$lc=['areaid','fatherareaid'];
			$data_s = $this->getParentData($data_f,$this->tab,$lc);
		}
		if(!empty($data_s)){
			$data = array_merge($data_f,$data_s);
		}else{
			$data = $data_f;
		}
		
		$ids = array(0);
		//$l_arr 保存菜单的一些信息  0-id  1-text 2-iconCls 3-fid 4-odr
		$l_arr = ['areaid','areaname','fatherareaid','areaid'];
		//$L_attributes 额外需要保存的信息
		$L_attributes = ['arearcode','rperson','rphone'];
		$icons = ['icon-map_go','icon-map'];
		$data_tree = $this->formatTree($ids,$data,$l_arr,$L_attributes,'',$icons);
		return $data_tree;
	}

	public function data_tree_list()
	{
		$data_tree = $this->tree_list();
		echo json_encode($data_tree);
	}

	public function tree_list_all()
	{
		$userid = I('userid');
		$action = A('Userreg');
		$m_userarea = $action->m_userarea($userid);
		$m_userarea = explode(',', $m_userarea);
		$db = D($this->tab);
		$area_all = $db->select();
		$ids = array(0);
		//$l_arr 保存菜单的一些信息  0-id  1-text 2-iconCls 3-fid 4-odr
		$l_arr = ['areaid','areaname','fatherareaid','areaid'];
		//$L_attributes 额外需要保存的信息
		$L_attributes = [];
		$icons = ['icon-map_go','icon-map'];
		$data_tree = $this->formatTree($ids,$area_all,$l_arr,$L_attributes,$m_userarea,$icons);		
        echo json_encode($data_tree);
	}

	public function userarea()
	{
		$action = A('Userreg');
		return $action->s_userarea();
	}
	/**
	 * 子用户管理区域
	 * @return array 用户id=>管理区域
	 */
	public function cuserarea()
	{
		$action = A('Userreg');
		$cuser = $action->cuser(session('userid'));
		$cuserarea = array();
		foreach ($cuser as $value) {
			$area = explode(',', $value['userarea']);
			$cuserarea[$value['userid']] =  $area;
		}
		return $cuserarea;
	}
	/**
	 * 父用户管理区域
	 * @return array 用户id=>管理区域
	 */
	public function puserarea()
	{
		$action = A('Userreg');
		$cuser = $action->puser(session('userid'));
		$puserarea = array();
		foreach ($cuser as $value) {
			$area = explode(',', $value['userarea']);
			$puserarea[$value['userid']] =  $area;
		}
		return $puserarea;
	}
	/**
	 * 获取目标区域的子区域及自身
	 * @param  int $areaid 目标区域
	 * @return array         
	 */
	public function carea($areaid)
	{
		$db = D($this->tab);
		$where['areaid'] = $areaid;
		$data = $db->where($where)->select();
		$l_arr = [0=>'areaid',1=>'fatherareaid'];
		$info_f = $this->getChData($data,$this->tab,$l_arr);
		$info_f = array_merge($data,$info_f);
		$all_list = array();
		foreach ($info_f as  $info_c) {
			$all_list[] = $info_c['areaid'];
		}
		return $all_list;
	}
}