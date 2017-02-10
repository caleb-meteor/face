<?php
namespace Home\Controller;

class UserregController extends CommonController 
{
	protected $tab_id = 'userid';
	protected $tab = 'userreg';

	public function index()
	{
		$ucTab = ucwords($this->tab);
		$url['datagridUrl'] = U($ucTab.'/dataList');
		$url['addUrl'] = U($ucTab.'/dataAdd');
		$url['editUrl'] = U($ucTab.'/dataEdit');
		$url['removeUrl'] = U($ucTab.'/dataRemove');
		$url['saveareaUrl'] = U($ucTab.'/savearea');
		$url['userareaUrl'] = U($ucTab.'/userarea');
		$this->assign('url',$url);

		$this->assignInfo();
		$this->display($this->tab);
	}

	public function dataList()
	{
		$request = I();
		$page = I('page');
		$rows = I('rows');		
		$db = D($this->tab);
		if($request['username']){
			$check['username'] = array('like','%'.$request['username'].'%');
		}
		if($request['truename']){
			$check['truename'] = array('like','%'.$request['truename'].'%');
		}
		//初始数据展示限制
		$info = $this->cuser(session('userid'));
		$all_list = array();
		foreach ($info as  $value) {
			$all_list[] = $value['userid'];
		}
		$all_list[] = session('userid');
		$check['userid'] = array('in',$all_list);

		$data = $db->getTableList($check,$page,$rows);
		$this->ajaxReturn($data);
	}

	public function dataAdd()
	{
		$request = I();
		$db = D($this->tab);
		$request['fatherid'] = session('userid');
		$where['username'] = $request['username'];
		if($db->checkExistence($where)){
			$result['message'] = '用户已存在！换一个吧';
            $result['status']  = true;
		}else{
			$result = $db->getTableAdd($request);
		}
		$this->ajaxReturn($result);
	}

	public function dataRemove()
	{
		$request = I();
		$db = D($this->tab);
		$where = $this->tab_id.' in('.$request[$this->tab_id].')';
		$result = $db->getTableDel($where);
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
		$dbc = 'Rolereg';
		$db = D($dbc);
		$where['roleid'] = session('roleid');
		$data = $db->where($where)->select();
		$l_arr = [0=>'roleid',1=>'proleid'];
		$info_f = $this->getChData($data,$dbc,$l_arr);
		//添加修改时没有本级
		
		$info['role'] = $info_f;
		//用与显示
		$info_f= array_merge($info_f,$data);
		$info['roleJson'] = json_encode($info_f);
		/*dump($info_f);
		exit;*/
		$this->assign('info',$info);
	}

	public function savearea()
	{
		$request = I();
		$db = D($this->tab);
		$action = A('Areareg');
		$userarea = $action->userarea();
		$putarea = explode(',', $request['userarea']);
		//计算出区域赋值与当前用户实际拥有权限的交集
		$intersect = array_intersect($putarea,$userarea);
		$savearea['userarea'] = implode(',', $intersect);
		$where[$this->tab_id] = $request[$this->tab_id];
		unset($request[$this->tab_id]);
		$result = $db->getTableEdit($where,$savearea);
		$this->ajaxReturn($result);
	}

	public function userarea()
	{
		$request = I();
		echo $this->m_userarea($request[$this->tab_id]);
	}
	/**
	 * 获取当前用户的管理区域
	 * @return string
	 */
	public function s_userarea()
	{

		$s_userarea = $this->m_userarea(session('userid'));
		if($s_userarea!=''){
			$s_userarea = explode(',', $s_userarea);
		}
		return $s_userarea;
	}
	/**
	 * 获取目标用户的管理区域
	 * @param  int $userid 目标用户id
	 * @return string         目标用户管理区域用 , 隔开
	 */
	public function m_userarea($userid)
	{
		$db = D($this->tab);
		$where[$this->tab_id] = $userid;
		$userarea = $db->where($where)->field('userarea')->find();
		return $userarea ? $userarea['userarea'] : '';
	}
	/**
	 * 获取目标用户的子用户
	 * @param  int $userid 目标用户
	 * @return array         
	 */
	public function cuser($userid)
	{
		$db = D($this->tab);
		$where['userid'] = $userid;
		$data = $db->where($where)->select();
		$l_arr = [0=>'userid',1=>'fatherid'];
		$info_f = $this->getChData($data,$this->tab,$l_arr);
		return $info_f;
	}
	/**
	 * 获取目标用户的父用户
	 * @param  int $userid 目标用户
	 * @return array         
	 */
	public function puser($userid)
	{
		$db = D($this->tab);
		$where['userid'] = $userid;
		$data = $db->where($where)->select();
		$l_arr = ['userid','fatherid'];
		$info_f = $this->getParentData($data,$this->tab,$l_arr);
		return $info_f;
	}
}