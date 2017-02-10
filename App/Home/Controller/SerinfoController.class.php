<?php
namespace Home\Controller;

class SerinfoController extends CommonController
{
	//表的表名-自增主键
	protected $tab_id = 'serid';
	protected $tab = 'Serinfo';
	protected $link_tab = 'Preser';
	//展示
	public function index()
	{
		$ucTab = ucwords($this->tab);
		$url['datagridUrl'] = U($ucTab.'/dataList');
		$url['addUrl'] = U($ucTab.'/dataAdd');
		$url['editUrl'] = U($ucTab.'/dataEdit');
		$url['removeUrl'] = U($ucTab.'/dataRemove');
		$url['load_commboxUrl'] = U($ucTab.'/load_commbox');
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
		$link_db = D($this->link_tab);
		if(!empty($data['rows'])){
			foreach ($data['rows'] as  $key => $value) {
				$where['serid'] = $value['serid'];
				$pserid = $link_db->where($where)->getField('pserid',true);
				$data['rows'][$key]['pserid'] = implode(',', $pserid);
			}
		}
		$this->ajaxReturn($data);
	}
	//增加事件
	public function dataAdd()
	{
		$request = I();
		$psers = $request['pserid'];
		unset($request['pserid']);
		unset($request[$this->tab_id]);

		$db = D($this->tab);
		$result = $db->getTableAdd($request);
		$add_id = $result['add_id'];

		$db = D($this->link_tab);
		$insert_data['serid'] = $add_id;
		$db->where($insert_data)->delete();
		foreach ($psers as $value) {
			$insert_data['pserid'] = $value;
			$db->data($insert_data)->add();
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
		$link_db = D($this->link_tab);
		$link_db->where($where)->delete();
		$link_db->where('pserid in('.$request[$this->tab_id].')')->delete();
		$this->ajaxReturn($result);
	}
	//编辑事件
	public function dataEdit()
	{
		$request = I();
		$db = D($this->tab);
		$where[$this->tab_id] = $request[$this->tab_id];

		//更新关联表
		$psers = $request['pserid'];
		unset($request['pserid']);
		unset($request[$this->tab_id]);
		$link_db = D($this->link_tab);
		$link_db->where($where)->delete();
		$insert_data['serid'] = $where[$this->tab_id];
		foreach ($psers as $value) {
			$insert_data['pserid'] = $value;
			$link_db->data($insert_data)->add();
		}
		$result = $db->getTableEdit($where,$request);
		$result['message'] = '更新完成';
		$this->ajaxReturn($result);
	}
	//模板传值
	public function assignInfo()
	{
        $db = D('Devtype');
        $where['typeid'] = array('in',[1,2,3,6]);
        $info['devtype'] = $db->where($where)->select();
        $info['devtypeJson'] = json_encode($info['devtype']);
		$this->assign('info',$info);
	}

	public function load_commbox()
	{
		$db = D($this->tab);

		$sertype = $_GET['sertype'];
		switch ($sertype) {
			//算法服务
			case '1':
				$where['typeid'] = 6;
				break;
			//视频解析
			case '2':
				$where['typeid'] = 6;
				break;
			//比对服务
			case '3':
				$where['typeid'] = 6;
				break;
			//应用服务
			case '6':
				$where['typeid'] = 3;
				break;
		}
		$data = $db->where($where)->select();
		$this->ajaxReturn($data);
	}
}