<?php
namespace Home\Controller;

class DevController extends CommonController
{
	//表的表名-自增主键
	protected $tab_id = 'devid';
	protected $tab = 'dev';
	protected $link_tab = 'Areareg';
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
		$check['devname'] = array('like','%'.$request['devname'].'%');
		$db = D($this->tab);

		$dbc = D($this->link_tab);
		$areaid = $request['areaid'];
		//初始数据展示限制只显示自身和下级角色
		$where['areaid'] = $areaid;
		$data = $dbc->where($where)->select();
		$l_arr = [0=>'areaid',1=>'fatherareaid'];
		$info_f = $this->getChData($data,$this->link_tab,$l_arr);
		$info_f= array_merge($data,$info_f);
		$all_list = array();
		foreach ($info_f as  $info_c) {
			$all_list[] = $info_c['areaid'];
		}
		//实际管理区域
		$action = A('Userreg');
		$c_area = $action->s_userarea();
		$all_list = array_intersect($c_area, $all_list);

		$check['areaid'] = array('in',$all_list);

		$data = $db->getTableList($check,$page,$rows);
		$this->ajaxReturn($data);
	}
	//增加事件
	public function dataAdd()
	{
		$request = I();
		$db = D($this->tab);
		$action = A('Userreg');
		$c_area = $action->s_userarea();
		if(in_array($request['areaid'],$c_area)){
			$result = $db->getTableAdd($request);
		}else{
			$result['message'] = '对不起，你无法向该区域添加设备！因为该区域不在你的管辖范围，或者不全在管辖范围';
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
		$db = D('Areareg');
		$info['area'] = $db->listAll();
		$info['areaJson'] = json_encode($info['area']);

		$db = D('Devtype');
		$info['type'] = $db->listAll();
		$info['typeJson'] = json_encode($info['type']);

		$db = D('Serinfo');
        $info['serinfo'] = $db->where('typeid in(1,2)')->select();
        $info['serinfoJson'] = json_encode($info['serinfo']);
		$this->assign('info',$info);
	}

	public function show_dev()
	{
		$action = A('Areareg');
		$areas = $action->tree_list();
		$data = $this->area_dev($areas);
		//dump($data);
		echo json_encode($data);
	}
	/**
	 * 将管理区域添加上设备
	 * @param  array $areas 管理区域
	 * @return array
	 */
	public function area_dev($areas)
	{
		$db = D($this->tab);
		$area_dev = array();
		$action = A('Userreg');
		$userarea = $action->s_userarea();
		foreach ($areas as  $key => $area) {
			$where['areaid'] = $area['id'];
			$area_dev[$key]['id'] = $area['id'];
			$area_dev[$key]['text'] = $area['text'];
			$area_dev[$key]['iconCls'] = $area['iconCls'];
			if(isset($area['children'])){
				$c_dev = $this->area_dev($area['children']);
				$area_dev[$key]['children'] = $c_dev;
			}
			if(in_array($area['id'],$userarea)){
				$devs = $db->where($where)->select();
				$format_dev = array();
				//没有设备的区域显示
				if(!empty($devs)){
					foreach ($devs as $dev) {
						$format_dev['id'] = $dev['devid'];
						$format_dev['text'] = $dev['devname'];
						$format_dev['rtspurl'] = $dev['rtspurl'];
						$format_dev['state'] = $dev['state'];
						$format_dev['iconCls']	= 'icon-camera';
						$dev_arr = array($format_dev);
						if(!empty($area_dev[$key]['children'])){
							$area_dev[$key]['children'] =array_merge($dev_arr,$area_dev[$key]['children']);
						}else{
							$area_dev[$key]['children'][] = $format_dev;
						}
					}
				}else{
					unset($area_dev[$key]);
				}
			}
			if(isset($area_dev[$key]['children'])) $area_dev[$key]['state'] = 'closed';
		}
		return $area_dev;
	}
}