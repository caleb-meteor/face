<?php
namespace Home\Controller;

use Think\Controller;
use Think\Model;

class CommonController extends Controller {
	/**
	 * 格式化easyui-tree数据格式
	 * @param  array $ids   父级菜单id
	 * @param  array $datas 修要处理的菜单
	 * @param  array $l_arr 保存菜单的一些信息  0-id  1-text 2-iconCls 3-fid 4-odr
	 * @param  array $L_attributes 额外需要保存的信息
     * @param  array $check_arr 需要被勾选的数组
     * @param  array $icons 需要添加的图标 icon-remove 0-父图标 1-子图标
	 * @return array
	 */
    public function formatTree($ids,$datas,$l_arr,$L_attributes,$check_arr,$icons)
    {
    	$formatTree = array();
        foreach ($ids as $id) {
            $odrData = array();
            foreach ($datas as $data) {
                if($id == $data[$l_arr[2]]){
                    $odrData[] = $data[$l_arr[3]];
                    $nextIds[] = $data[$l_arr[0]];
                    $doTree['id'] = $data[$l_arr[0]];
                    if(!empty($check_arr)){
                        if(in_array($doTree['id'],$check_arr)){
                            $doTree['checked'] = true;
                        }
                    }
                    $doTree['text'] = $data[$l_arr[1]];
                    foreach ($L_attributes as $L_attribute) {
                    	$doTree[$L_attribute] = $data[$L_attribute];
                    }
                    $children = $this->formatTree($nextIds,$datas,$l_arr,$L_attributes,$check_arr,$icons);
                    $nextIds = '';
                    if(!empty($children)){
                        $doTree['state'] = 'closed';
                        $doTree['children'] = $children;
                        $doTree['iconCls'] = $icons[0];
                    }else{
                        $doTree['iconCls'] = $icons[1];
                    }
                    $formatTree[]=$doTree;
                    //对于生成的菜单在进行排序
                    array_multisort($odrData,SORT_DESC,$formatTree);
                    $doTree = '';
                }
            }
        }
        //$formatMenu = json_encode($formatMenu);
        return  $formatTree;
    }
    /**
     * 获取所有子联级
     * @param  array $finfos 根元素
     * @param  string $dbc     模型
     * @param  array $l_arr  0-id  1-fid
     * @return array         所有子集
     */
    public function getChData($finfos,$dbc,$l_arr)
    {
    	if(empty($finfos)) return false;

        $db = D($dbc);
    	$infos = array();
    	foreach ($finfos as $key => $finfo) {
    		$id = $finfo[$l_arr[0]];


    		$where[$l_arr[1]] = $id;
    		$infoc = $db->where($where)->select();
    		$info = array();
    		if(!empty($infoc)){
    			$info = $this->getChData($infoc,$dbc,$l_arr);
    		}
            $infos = array_merge($info,$infos,$infoc);
    	}
    	return $infos;
    }
    /**
     * 获取父级数据
     * @param  array $finfos 所有子集
     * @param  string $dbc     模型
     * @param  array $l_arr  0-id  1-fid
     */
    public function getParentData($finfos,$dbc,$l_arr)
    {
        if(empty($finfos)) return false;

        $db = D($dbc);
        $datas = array();
        static $pids = array();
        foreach ($finfos as $value) {
            $pids[] = $value[$l_arr[0]];
        }
        foreach ($finfos as $finfo) {
            if(!in_array($finfo[$l_arr[1]],$pids)){
                $where[$l_arr[0]] = $finfo[$l_arr[1]];
                $data = $db->where($where)->find();
                if($data!='' && !in_array($data[$l_arr[0]],$pids)){
                    $datas[] = $data;
                }
                $pids[] = $finfo[$l_arr[1]];
            }
        }
        $datac = $this->getParentData($datas,$dbc,$l_arr);
        if($datac){
            $datas = array_merge($datas,$datac);
        }
        return $datas;
    }
    /**
     * 获取数据库中的所有表名
     * @param  array $config 数据库的配置信息
     * @return array        数据库一维数组
     */
    public function get_dbTables($config)
    {
        $tables = array();
        try {
            $initdb = M('','',$config);
            $tablesArr = $initdb->query('show tables');
            $tables = array();
            foreach ($tablesArr as  $table) {
                $tables[] = array_pop($table);
            }
            return $tables;
        } catch (Exception $e) {
            return $tables;
        }


    }
    /**
     * 获取两个日期之间的所有日期 包含自身
     * @param  Date $smallDate 较小的日期
     * @param  Date $bigDate   较大的日期
     * @param  string $format  生成日期的格式
     * @return array
     */
    public function get_twoMonthsDates($smallDate,$bigDate,$format)
    {
        $time1 = strtotime($smallDate); // 自动为00:00:00 时分秒 两个时间之间的年和月份
        $time2 = strtotime($bigDate);
        $datearr = array();
        $datearr[] = date($format,$time1);
        while( ($time1 = strtotime('+1 day', $time1)) <= $time2){
              $datearr[] = date($format,$time1); // 取得递增月;
        }
        return $datearr;
    }
}