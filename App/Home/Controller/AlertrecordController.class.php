<?php
namespace Home\Controller;

class AlertrecordController extends CommonController
{
	//表的表名-自增主键
	protected $tab_id = 'alertreocrdid';
	protected $tab = 'alertrecord';
	protected $link_tab = 'employee';
	protected $linkser_tab = 'serinfo';
	protected $linkdev_tab = 'dev';
	protected $linkpho_tab = 'employeepho';
	//展示
	public function index()
	{
		$ucTab = ucwords($this->tab);
		$url['datagridUrl'] = U($ucTab.'/dataList');
		$url['addUrl'] = U($ucTab.'/dataAdd');
		$url['editUrl'] = U($ucTab.'/dataEdit');
		$url['removeUrl'] = U($ucTab.'/dataRemove');
		$url['getemppicUrl'] = U($this->link_tab.'/get_empImages');
		$this->assign('url',$url);
		$this->assignInfo();
		$this->display(strtolower($this->tab));
	}
	//数据获取
	public function dataList()
	{
		$page = I('page',1);
		$rows = I('rows',7);
		$minscore = I('minscore','');
		$maxscore = I('maxscore','');
		$btime = I('btime','');
		$etime = I('etime','');
		$devs = I('devs','');
		$employee_name = I('employee_name','');
		$db = D($this->tab);
		$join[] = 'LEFT JOIN'.' '.$this->link_tab.' ON '.$this->tab.'.employee_empid = '.$this->link_tab.'	.empid';
		$join[] = 'LEFT JOIN'.' '.$this->linkser_tab.' ON '.$this->tab.'.serinfo_serid = '.$this->linkser_tab.'.serid';
		$join[] = 'LEFT JOIN'.' '.$this->linkdev_tab.' ON '.$this->tab.'.dev_devid = '.$this->linkdev_tab.'.devid';
		//$requests = I();
		//监测是否搜索具体告警人员
		if($employee_name != ''){
			$link_db = D($this->link_tab);
			//$search['name'] = $employee_name;
			$search['name'] = array('like',"%$employee_name%");
			$employee_empids = $link_db->where($search)->getField('empid',true);
			$lastsql = $link_db->getLastSql();
			//为空结束
			if(empty($employee_empids)){
				$re['total'] = 0;
				$re['rows'] = array();
				$this->ajaxReturn($re);
				exit;
			}
		}
		//首页请求
		if($minscore == '' && $maxscore == '' && $btime == '' && $etime == ''){
			$data = $db->getTableList($check,$page,$rows,'alerttime desc',null,$join);
		}else{
			$check['score'] = array(array('EGT',$minscore),array('ELT',$maxscore));
			$check['alerttime'] = array(array('EGT',$btime),array('ELT',$etime));
			if($devs!=''){
				$check['dev_devid'] = array('in',$devs);
			}
			if(!empty($employee_empids)){
				$check['employee_empid'] = array('in',$employee_empids);
			}
			$data = $db->getTableList($check,$page,$rows,'alerttime desc',null,$join);
		}
		$phodb = D($this->linkpho_tab);
		foreach ($data['rows'] as $key => $value) {
			$where['empid'] = $value['employee_empid'];
			$photo = $phodb->where($where)->getField('photo');
			$data['rows'][$key]['photo'] = $photo ? $photo : '';
		}
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
        $db = D('Serinfo');
        $info['serinfo'] = $db->listAll();
        $info['serinfoJson'] = json_encode($info['serinfo']);
		$this->assign('info',$info);
	}
}