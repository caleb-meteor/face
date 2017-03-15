<?php
namespace Home\Controller;

class CapturerecordController extends CommonController
{
    //表的表名-自增主键
    protected $tab_id = 'caprecid';
    protected $tab = 'Capturerecord';
    //展示
    public function index()
    {
        $ucTab = ucwords($this->tab);
        $url['datagridUrl'] = U($ucTab.'/dataList');
        $url['addUrl'] = U($ucTab.'/dataAdd');
        $url['editUrl'] = U($ucTab.'/dataEdit');
        $url['removeUrl'] = U($ucTab.'/dataRemove');
        $url['difftotalUrl'] = U($ucTab.'/difftotal');
        $this->assign('url',$url);
        $this->assignInfo();
        $this->display(strtolower($this->tab));
    }
    //数据获取
    public function dataList()
    {
        $page = I('page');
        $rows = I('rows');
        $btime = I('btime');
        $etime = I('etime');
        $devs = I('devs');
        $ip = I('devip');
        $minquality = I('minquality');
        $maxquality = I('maxquality');
        //$data = $this->get_diffTotal($btime, $etime, $minquality, $maxquality, $devs, $page, $rows);
        $data = $this->search_union_data($btime, $etime, $minquality, $maxquality, $devs, $page, $rows, $ip);
        //echo $data;
        $this->ajaxReturn($data);
        return false;
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
        $this->assign('info',$info);
    }

    public function lastImage()
    {
        $dev = I('devid','');
        $devDb = D('Dev');
        $where['devid'] = $dev;
        $devip = $devDb->where($where)->getField('devip');
        $date = date('Ymd');
        $connection = 'mysql://'.C('DB_USER').':'.C('DB_PWD').'@'.$devip.':'.C('DB_PORT').'/'.C('DB_NAME').'#'.C('DB_CHARSET');
        $db = M('capturerecord_'.$date,'',$connection);
        $check['dev_devid'] = $dev;
        $datas = $db->where($check)->order('captime desc')->page(1,5)->select();
        $this->ajaxReturn($datas);
    }
    /**
     * 搜索数据(将数据给php处理 将极大的消耗内存 大数据会直接崩溃 也是个渣)
     * @param  date $btime      开始时间
     * @param  date $etime      结束时间
     * @param  int $minquality 质量最低值
     * @param  int $maxquality 质量最高值
     * @param  string $devs       设备  1,2,3
     * @param  int $page       页码
     * @param  int $rows       行数
     * @return array             所有符合条件的数据
     */
    public function search_data($btime,$etime,$minquality,$maxquality,$devs,$page,$rows)
    {
        //解析出时间段内的所有表记录
        $bdate = explode(' ', $btime)[0];
        $edate = explode(' ', $etime)[0];
        $datearr = $this->get_twoMonthsDates($bdate,$edate,'Ymd');

        $devs = explode(',', $devs);
        $devDb = D('Dev');

        $data = array();
        $data['total'] = 0;
        $data['rows'] = array();
        //删选条件
        $check['quality'] = array(array('EGT',$minquality),array('ELT',$maxquality));
        $check['captime'] = array(array('EGT',$btime),array('ELT',$etime));
        //获取设备对应ip
        foreach ($devs as $dev) {
            $where['devid'] = $dev;
            $devIps[$dev] = $devDb->where($where)->getField('devip');
        }
        foreach ($datearr as $date) {
            foreach ($devIps as $key => $devip) {
                $connection = 'mysql://'.C('DB_USER').':'.C('DB_PWD').'@'.$devip.':'.C('DB_PORT').'/'.C('DB_NAME').'#'.C('DB_CHARSET');
                $tables = $this->get_dbTables($connection);
                if(in_array('capturerecord_'.$date, $tables)){
                    $db = M('capturerecord_'.$date,'',$connection);
                    $check['dev_devid'] = $key;
                    $count = $db->where($check)->count('caprecid');
                    if($count > 0){
                        $datas = $db->where($check)->order('captime desc')->select();
                        if(!empty($datas)){
                            $data['rows'] = array_merge((array)$datas,(array)$data['rows']);
                        }
                    }
                    $data['total'] = $count + $data['total'];
                }
            }
        }
        $data['rows'] = array_slice($data['rows'], ($page-1)*$rows,$rows);
        return $data;
    }
    public function difftotal()
    {
        $btime = I('btime');
        $etime = I('etime');
        $devs = I('devs');
        $minquality = I('minquality');
        $maxquality = I('maxquality');
        $result = $this->get_diffTotal($btime, $etime, $minquality, $maxquality, $devs);
        $data = array();
        foreach ($result as $key => $value) {
            $info = array();
            $devinfos = explode('-',$key);
            $info['devid'] = $devinfos[0];
            $info['name'] = $devinfos[1];
            $info['devip'] = $devinfos[2];
            $info['num'] = array_sum($value);
            $data[] = $info;
        }
        $this->ajaxReturn($data);
    }
    /**
     * 获取所有路口的数据总量
     * @param  date $btime      开始时间
     * @param  date $etime      结束时间
     * @param  int $minquality 质量最低值
     * @param  int $maxquality 质量最高值
     * @param  string $devs       设备  1,2,3
     * @return array             所有符合条件的数据
     */
    public function get_diffTotal($btime,$etime,$minquality,$maxquality,$devs)
    {
        //解析出时间段内的所有表记录
        $bdate = explode(' ', $btime)[0];
        $edate = explode(' ', $etime)[0];
        $datearr = $this->get_twoMonthsDates($bdate,$edate,'Ymd');
        $devs = explode(',', $devs);
        $devDb = D('Dev');

        $data = array();
        $total = 0;
        //删选条件
        $check['quality'] = array(array('EGT',$minquality),array('ELT',$maxquality));
        $check['captime'] = array(array('EGT',$btime),array('ELT',$etime));
        //获取设备对应ip
        foreach ($devs as $dev) {
            $where['devid'] = $dev;
            $devInfoinfo = $devDb->where($where)->getField('devname,devip');
            foreach ($devInfoinfo as $name => $ip) {
                $devIps[$dev.'-'.$name] = $ip;
            }
            unset($name,$ip);
        }
        //print_r($devIps);
        $allTotal = array();
        //allTotal['total'] = 0;
        foreach ($devIps as $key => $devip) {
            $connection = 'mysql://'.C('DB_USER').':'.C('DB_PWD').'@'.$devip.':'.C('DB_PORT').'/'.C('DB_NAME').'#'.C('DB_CHARSET');
            foreach ($datearr as $date) {
                $tables = $this->get_dbTables($connection);
                if(in_array('capturerecord_'.$date, $tables)){
                    $db = M('capturerecord_'.$date,'',$connection);
                    $check['dev_devid'] = $key;
                    $count = $db->where($check)->count('caprecid');
                    if($count > 0 ){
                        $allTotal[$key.'-'.$devip][$date] = $count;
                        $total = $count + $total;
                    }
                }
            }
        }
        return $allTotal;
        /*$allTotal['total'] = $total;
        $returnIfo = array();
        $returnIfo['totals'] = $total;
        $allTotal = array_reverse($allTotal,TRUE);
        //return $allTotal;
        //所有的总数没有需要的数据那么大
        if($allTotal['total'] <= $rows){
            return $allTotal;
        }else{
            $needTotal = 0;
            foreach ($allTotal as $key => $dateTotal) {
                if($key != 'total'){
                    $needTotal = array_sum($dateTotal) + $needTotal;
                    $diffTotal = $needTotal - $search_total;
                    //若目前的差值绝对值在$rows之内的需保留 返回需要查询的日期 不return
                    if($diffTotal < 0 && abs($diffTotal) < $rows){
                        $returnIfo[$key] = $allTotal[$key];
                        $returnIfo[$key]['total'] = $needTotal;
                    }
                    //当目前的差值大于所需总数那么返回需要查询的日期 直接return
                    if($diffTotal >= 0){
                        $returnIfo[$key] = $allTotal[$key];
                        $returnIfo[$key]['total'] = $needTotal;
                        return $returnIfo;
                    }
                }
            }
        }*/
    }
    /**
     * 搜索数据 使用merge引擎 (目前来说大数据这就是个渣，完全没有使用的必要)
     * @param  date $btime      开始时间
     * @param  date $etime      结束时间
     * @param  int $minquality 质量最低值
     * @param  int $maxquality 质量最高值
     * @param  string $devs       设备  1,2,3
     * @param  int $page       页码
     * @param  int $rows       行数
     * @return array             所有符合条件的数据
     */
    public function search_merage_data($btime,$etime,$minquality,$maxquality,$devs,$page,$rows)
    {
        //建立merge表进行联合信息
        //解析出时间段内的所有表记录
        $bdate = explode(' ', $btime)[0];
        $edate = explode(' ', $etime)[0];
        $datearr = $this->get_twoMonthsDates($bdate,$edate,'Ymd');
        $devs = explode(',', $devs);
        $devDb = D('Dev');

        //$data = array();
        $data['total'] = 0;
        $data['rows'] = array();
        //删选条件
        $check['quality'] = array(array('EGT',$minquality),array('ELT',$maxquality));
        $check['captime'] = array(array('EGT',$btime),array('ELT',$etime));
        //获取设备对应ip
        foreach ($devs as $dev) {
            $where['devid'] = $dev;
            $devIps[$dev] = $devDb->where($where)->getField('devip');
        }
        $devTotal = count($devIps);
        //目前只适用于单路口查询
        foreach ($devIps as $key => $devip) {
            //查询条件
            $check['dev_devid'] = $key;
            $connection = 'mysql://'.C('DB_USER').':'.C('DB_PWD').'@'.$devip.':'.C('DB_PORT').'/'.C('DB_NAME').'#'.C('DB_CHARSET');
            $tabs = $this->get_dbTables($connection);
            foreach ($datearr as $date) {
                if(in_array('capturerecord_'.$date, $tabs)){
                    $table[] = 'capturerecord_'.$date;
                }
            }
            $tables = implode(',',$table);

            //如建表包含的数据相同那么将使用上一次的数据表 merge引擎
            if($tables != session('tables') && $tables != '' ){
                $db = M('','',$connection);
                $drop_mergeSql = 'DROP TABLE IF EXISTS `capturerecord`';
                $creat_mergeSql = 'CREATE TABLE `capturerecord` (
                      `caprecid` int(11) NOT NULL AUTO_INCREMENT,
                      `captime` datetime DEFAULT NULL,
                      `bodypicurl` varchar(128) DEFAULT NULL,
                      `facepicurl` varchar(128) DEFAULT NULL,
                      `quality` float DEFAULT NULL,
                      `dev_devid` int(11) DEFAULT NULL,
                      PRIMARY KEY (`caprecid`)
                    ) ENGINE=MERGE UNION=('.$tables.') INSERT_METHOD=LAST DEFAULT CHARSET=utf8';
                $db->query($drop_mergeSql);
                $db->query($creat_mergeSql);
            }
            session('tables',$tables);
            $mergeDb = M('capturerecord','',$connection);
            $total = $mergeDb->where($check)->count('caprecid');
            $data['total'] = $total + $data['total'];
            $dev_data =  $mergeDb->where($check)
                            ->page($page,(int)($rows/$devTotal))
                            ->order('captime desc')->select();
            $data['rows'] = array_merge((array)$data['rows'],(array)$dev_data);
        }
        return($data);
    }
    /**
     * 搜索数据 联合查询union(单路口)
     * @param  date $btime      开始时间
     * @param  date $etime      结束时间
     * @param  int $minquality 质量最低值
     * @param  int $maxquality 质量最高值
     * @param  array $dev       设备id=>ip
     * @param  int $page       页码
     * @param  int $rows       行数
     * @param  string $ip       设备ip
     * @return array             所有符合条件的数据
     */
    public function search_union_data($btime,$etime,$minquality,$maxquality,$dev,$page,$rows,$ip)
    {
        //解析出时间段内的所有表记录
        $bdate = explode(' ', $btime)[0];
        $edate = explode(' ', $etime)[0];
        $datearr = $this->get_twoMonthsDates($bdate,$edate,'Ymd');
        //$data = array();
        $data['total'] = 0;
        $data['rows'] = array();
        //删选条件
        $check['quality'] = array(array('EGT',$minquality),array('ELT',$maxquality));
        $check['captime'] = array(array('EGT',$btime),array('ELT',$etime));
        //写出原生sql语句 方便之后的union查询
        $sql = "captime >= '$btime' AND captime <= '$etime' AND quality >= $minquality AND quality <= $maxquality";
        //查询条件
        $check['dev_devid'] = $dev;
        $connection = 'mysql://'.C('DB_USER').':'.C('DB_PWD').'@'.$ip.':'.C('DB_PORT').'/'.C('DB_NAME').'#'.C('DB_CHARSET');
        $tabs = $this->get_dbTables($connection);
        foreach ($datearr as $date) {
            if(in_array('capturerecord_'.$date, $tabs)){
                $table[] = 'capturerecord_'.$date;
            }
        }
        if(!empty($table)){
            $db = M($table[0],'',$connection);
            $db->where($check);
        }else{
            return $data;
        }
        foreach ($table as $key => $tab) {
            if($key != 0) $db->union('select * from '.$tab.' where '.$sql.' AND dev_devid = '.$dev);
            $total = M($tab,'',$connection)->where($check)->count();
            $data['total'] = $data['total'] + $total;
        }
        $subQuery = $db->select(false);
        $ipdb = M('','',$connection);
        $data['rows'] = $ipdb->table('('.$subQuery.') as a')
                             ->where($check)->page($page,$rows)
                             ->order('captime desc')->select();
        //$sqls = $ipdb->getLastSql();
        return($data);
    }
        //插入数据到指定表
    public function insertAllData()
    {
        G('begin');
        $btime = I('btime','2017-03-14 00:00:00');
        $etime = I('etime','2017-03-15 23:00:00');
        $minquality = I('minquality',10);
        $maxquality = I('maxquality',100);
        $dev = I('dev','4');
        $ip = I('ip','localhost');
        $insertTable = I('insertTable','a1489539532');
         //解析出时间段内的所有表记录
        $bdate = explode(' ', $btime)[0];
        $edate = explode(' ', $etime)[0];
        $datearr = $this->get_twoMonthsDates($bdate,$edate,'Ymd');
        //$data = array();
        $data['total'] = 0;
        $data['rows'] = array();
        //删选条件
        $check['quality'] = array(array('EGT',$minquality),array('ELT',$maxquality));
        $check['captime'] = array(array('EGT',$btime),array('ELT',$etime));
        //写出原生sql语句 方便之后的union查询
        $sql = "captime >= '$btime' AND captime <= '$etime' AND quality >= $minquality AND quality <= $maxquality";
        //查询条件
        $check['dev_devid'] = $dev;
        $connection = 'mysql://'.C('DB_USER').':'.C('DB_PWD').'@'.$ip.':'.C('DB_PORT').'/'.C('DB_NAME').'#'.C('DB_CHARSET');
        $tabs = $this->get_dbTables($connection);
        foreach ($datearr as $date) {
            if(in_array('capturerecord_'.$date, $tabs)){
                $table[] = 'capturerecord_'.$date;
            }
        }
        if(!empty($table)){
            $db = M($table[0],'',$connection);
            $db->where($check);
        }else{
            return $data;
        }
        foreach ($table as $key => $tab) {
            if($key != 0) $db->union('select * from '.$tab.' where '.$sql.' AND dev_devid = '.$dev);
        }
        $allData = $db->where($check)->select();
        $allDb  = M($insertTable,'',$connection);
        $allDb->addAll($allData);
        //dump($allData);
        unset($allData);
        G('end');
        echo G('begin','end',6).'s';
    }
    //curl实现多路口查询
    public function curlMuti()
    {
        G('begin');
        //创建临时表
        $tab = 'a'.time();
        //创建表
        $sql = 'DROP TABLE IF EXISTS `'.$tab.'`;CREATE TABLE `'.$tab.'` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `caprecid` int(11) NOT NULL,
          `captime` datetime DEFAULT NULL,
          `bodypicurl` varchar(128) DEFAULT NULL,
          `facepicurl` varchar(128) DEFAULT NULL,
          `quality` float DEFAULT NULL,
          `dev_devid` int(11) DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8';
        $connection = 'mysql://'.C('DB_USER').':'.C('DB_PWD').'@'.C('DB_HOST').':'.C('DB_PORT').'/'.C('DB_NAME').'#'.C('DB_CHARSET');
        $db = M('','',$connection);
        $db->query($sql);
        $devs = range(3,6);
        $urls = [];
        foreach ($devs as $dev) {
            $urls[] = 'http://localhost/face/index.php/Home/Capturerecord/insertAllData?dev='.$dev.'&insertTable='.$tab;
        }
        $mh = curl_multi_init();
        foreach ($urls as $i => $url) {
            $chs[$i] = curl_init();
            curl_setopt($chs[$i], CURLOPT_URL, $url);
            curl_setopt($chs[$i], CURLOPT_HEADER, 0);
            curl_setopt($chs[$i], CURLOPT_RETURNTRANSFER, 1);
            curl_multi_add_handle($mh,$chs[$i]);
        }
        $running = null;
        // 执行批处理句柄
        do {
            curl_multi_exec($mh, $running);
        } while ($running > 0);
        $res = array();
        foreach ($chs as $ch) {
            $res[] = curl_multi_getcontent($ch);
        }
        foreach ($chs as $ch) {
            curl_multi_remove_handle($ch);
        }
        curl_multi_close($mh);
        echo $tab;
        dump($devs);
        dump($urls);
        dump($res);
        G('end');
        echo G('begin','end',6).'s';
    }
}
