<?php
namespace Home\Controller;

use Think\Controller;

class TpmanagerController extends Controller {
    public function index()
    {
    	$info = '';
    	if(IS_POST) {
            $data = I();
            /*dump($data);
            exit;*/
            $info = $this->creatMVC($data);
    	}
    	$this->assign('info',$info);
    	$this->display('tpmanager');
    }
    public function creatMVC($info)
    {
        $is_link = false;
        $result = '';
        $table_id = '';
        $mo = trim($info['addname']);
        if($info['col'][0]['col_c']!='')
            $table_id = $info['col'][0]['col_c'];

        if($info['link'][0]['link_c']!='')
            $is_link = true;

        if($info['is_controller'] == '1'){
            if(file_exists(MODULE_PATH.'/Controller/'.$mo.'Controller.class.php')){
                $result .= $mo.'控制器已存在,';
            }else{
                $file = fopen(MODULE_PATH.'/Controller/'.$mo.'Controller.class.php', 'w+');
                $content = file_get_contents(MODULE_PATH.'View/Tpl/controller.tpl');
                $content = str_replace('{Addname}', $mo, $content);
                $content = str_replace('{table_id}', $table_id, $content);
                if($is_link){
                    $link = $this->parseLink($info['link']);
                    $content = str_replace('{link}', $link, $content);
                }else{
                    $content = str_replace('{link}', '', $content);
                }
                fwrite($file,$content);
                fclose($file);
                $result .=$mo.'控制器创建成功,';
            }
        }
        if($info['is_model'] == '1'){
            if(file_exists(MODULE_PATH.'/Model/'.$mo.'Model.class.php')){
                $result .= $mo.'模型已存在';
            }else{
                $file = fopen(MODULE_PATH.'/Model/'.$mo.'Model.class.php', 'w+');
                $content = file_get_contents(MODULE_PATH.'View/Tpl/model.tpl');
                $content = str_replace('{Addname}', $mo, $content);
                $model = "'*'";
                if($table_id!=''){
                    $model = $this->parseModel($info['col']);
                    $content = str_replace('{model}', $model, $content);
                }
                fwrite($file,$content);
                fclose($file);
            }
            $result .=$mo.'模型创建成功,';
        }
        if($info['is_view'] == '1'){
            if(file_exists(MODULE_PATH.'/View/'.$mo.'/'.strtolower($mo).'.html')){
                $result .= strtolower($mo).'视图已存在';
            }else{
                $col = '';
                $fileds = '';
                $fun = '';
                if($is_link){
                    $fun = $this->parseFun($info['link']);
                }
                $view = array();
                if($table_id!=''){
                    $col = $this->praseCol($info['col'],$info['link']);
                    $fields = $this->parseField($info['col'],$info['link']);
                }
                if($info['search'][0]['search_c']!=''){
                    $view = $this->parseSearch($info['search']);
                }
                mkdir(MODULE_PATH.'/View/'.$mo);
                $file = fopen(MODULE_PATH.'/View/'.$mo.'/'.strtolower($mo).'.html', 'w+');
                $content = file_get_contents(MODULE_PATH.'View/Tpl/view.tpl');
                $content = str_replace('{Addname}', $mo, $content);
                $content = str_replace('{col}', $col, $content);
                $content = str_replace('{fields}', $fields, $content);
                $content = str_replace('{table_id}', $table_id, $content);
                $content = str_replace('{function}', $fun, $content);
                if(!empty($view)){
                    $content = str_replace('{search}', $view['search'], $content);
                    $content = str_replace('{js_search}', $view['js_search'], $content);
                    $content = str_replace('{js_search_data}', $view['js_search_data'], $content);
                }
                fwrite($file,$content);
                fclose($file);
                $result .=$mo.'视图创建成功,';
            }
        }
        return $result;
    }
    /*<div class="form_m" style="display: none"><div for="name" class="form_label">角色id : </div>
                <input class="easyui-validatebox form_in easyui-textbox" type="text" name="roleid"  /></div>*/
/*<div class="form_m"><div for="name" class="form_label">用户性别 : </div>
                <select class="easyui-combobox form_in" type="text" name="sex">
                    <option value="1">男</option>
                    <option value="0">女</option>
                </select>
            </div>*/
    public function praseCol($colString,$links)
    {
        $content = '';
        foreach ($colString as $key => $col) {
            $type = $col['col_type'];
            $class = '';
            $require = '';
            $display = '';
            if($col['col_check']!='0'){
                $require = ' required="true"';
            }
            if($key == 0){
                $display = ' style="display: none"';
            }
            $for_link = ['link_tab'=>'','link_c'=>'','link_id'=>'','link_name'=>''];
            foreach ($links as $link) {
                if($link['link_c'] == $col['col_c']){
                    $for_link = $link;
                }
            }
            if($type!='select'){
                $content .= '            <div class="form_m"'.$display.'><div for="'.$col['col_c'].'" class="form_label">'.$col['col_b'].' : </div>
                <input class="form_in easyui-'.$type.'" type="text" name="'.$col['col_c'].'"'.$require.'/></div>'."\r\n";
            }else{
                $content .= '            <div class="form_m"><div for="'.$col['col_c'].'" class="form_label">'.$col['col_b'].' : </div>
                <select class="form_in easyui-combobox" name="'.$col['col_c'].'"'.$require.'>
                    <option value="">请选择</option>
                    <foreach name="info[\''.strtolower($for_link['link_tab']).'\']" item="'.strtolower($for_link['link_tab']).'">
                        <option value="{$'.strtolower($for_link['link_tab']).'[\''.$for_link['link_id'].'\']'.'}">{$'.strtolower($for_link['link_tab']).'[\''.$for_link['link_name'].'\']'.'}</option>
                    </foreach>
                </select>
            </div>'."\r\n";
            }
        }
        return '            '.trim($content);
    }
        /*{field:'roleid',title:'id',checkbox:true},
        {field:'rolename',title:'角色名',width:200,align:'center'},
        {field:'remark',title:'角色说明',width:200,align:'center'},
        {field:'functionlist',title:'权限分配',width:200,align:'center'} */
    public function parseField($colString,$links)
    {
        $content = '';
        foreach ($colString as $key => $filed) {
            $for_link = ['link_tab'=>'','link_c'=>'','link_id'=>'','link_name'=>''];
            foreach ($links as $link) {
                if($link['link_c'] == $filed['col_c']){
                    $for_link = $link;
                }
            }
            $other_info = '';
            $check = '';
            $other_info = $key == 0 ? 'checkbox:true' : "width:200,align:'center'";
            if($for_link['link_tab']!=''){
                $check = ',formatter:check'.$for_link['link_tab'];
            }
            $content .= "        {field:'".$filed['col_c']."',title:'".$filed['col_b']."',$other_info".$check."},"."\r\n";
        }
        $content = trim($content);
        $content = '        '.$content;
        return $content;
    }

    public function parseModel($colString)
    {
        $content .= "array('";
        //$strings = explode('|', $colString);
        $model = array();
        foreach ($colString as $string) {
            $model[] = $string['col_c'];
        }
        $modelString = implode("','", $model);
        $content .= $modelString."')";
        return $content;
    }
    /*区域名称: <input id='searchInput'  class="easyui-textbox" data-options="height:22"  style="width:168px"/>
    用户性别 : <select class="easyui-combobox form_in" type="text" name="sex">
                    <option value="1">男</option>
                    <option value="0">女</option>
                </select>
    var rolename = $('#searchInput').val();
    rolename = $.trim(rolename);
    rolename: rolename*/
    public function parseSearch($searchs)
    {
        $view = array();
        $content = '';
        $js_search = '';
        $js_search_data = '';
        foreach ($searchs as $search) {
            $type = $search['search_type'];
            switch ($type) {
                case 'text':
                    $content .= $search['search_b'].': <input id="'.$search['search_c'].'"  class="easyui-textbox" data-options="height:22"  style="width:168px"/>';
                    break;
                case 'search':
                    $content .= $search['search_b'].'<select class="easyui-combobox form_in" type="text" id="'.$search['search_c'].'">
                                                        <option value="1">数据</option>
                                                        <option value="0">模拟</option>
                                                    </select>';
                    break;
            }
            $js_search .="    var ".$search['search_c']." = $('#".$search['search_c']."').val();"."\r\n";
            $js_search .="    ".$search['search_c']." = $.trim(".$search['search_c'].");"."\r\n";
            $js_search_data .= "        ".$search['search_c'].':'.$search['search_c'].','."\r\n";
        }
        $view['search'] = '    '.trim($content);
        $view['js_search'] = '    '.trim($js_search);
        $view['js_search_data'] = '        '.trim($js_search_data);
        return $view;
    }
    /*$db = D('Demo');
    $info['demo'] = $db->listAll();
    $info['demoJson'] = json_encode($info['demo']);*/
    public function parseLink($links)
    {
        $content = '';
        foreach ($links as $link) {
            $content .= '        '.'$db = D(\''.$link['link_tab']."');"."\r\n";
            $content .= '        '.'$info[\''.strtolower($link['link_tab']).'\'] = $db->listAll();'."\r\n";
            $content .= '        '.'$info[\''.strtolower($link['link_tab']).'Json\'] = json_encode($info[\''.strtolower($link['link_tab']).'\']);'."\r\n";
        }
        $content = '        '.trim($content);
        return $content;
    }
   /* function(v,r,i){
            var areas = {:$info['areaJson']};
            var name;
            $.each(areas,function(n,m){
                if(m.areaid == v){
                    name = m.areaname;
                }
            });
            return name;
        }*/
    public function parseFun($links)
    {
        $content = '';
        foreach ($links as $link) {
            $content .= 'function check'.$link['link_tab'].'(v,r,i){'."\r\n";
            $content .= '    var fies = {:$info[\''.strtolower($link['link_tab']).'Json\']};'."\r\n";
            $content .= '    var name;'."\r\n";
            $content .= '    $.each(fies,function(n,m){'."\r\n";
            $content .= '        if(m.'.$link['link_id'].' == v){'."\r\n";
            $content .= '            name = m.'.$link['link_name'].';'."\r\n";
            $content .= '        }'."\r\n";
            $content .= '    });'."\r\n";
            $content .= '    return name;'."\r\n";
            $content .= '}'."\r\n";
        }
        $content = trim($content);
        return $content;
    }
    public function parseJson($data)
    {
        $dataArr = explode('&', $data);
        $info = array();
        $col_c = array();
        $col_b = array();
        $col_type = array();
        $col_check = array();
        $search_c = array();
        $search_b = array();
        $search_type = array();
        $is_controller = '';
        $is_model = '';
        $is_view = '';
        $col = array();
        $search = array();
        foreach ($dataArr as $value) {
            $tinfo = [];
            $ic = explode('=', $value);
            switch ($ic[0]) {
                case 'col_c':
                    $col_c[] = $ic[1];
                    break;
                case 'col_b':
                    $col_b[] = $ic[1];
                    break;
                case 'col_type':
                    $col_type[] = $ic[1];
                    break;
                case 'col_check':
                    $col_check[] = $ic[1];
                    break;
                case 'search_c':
                    $search_c[] = $ic[1];
                    break;
                case 'search_b':
                    $search_b[] = $ic[1];
                    break;
                case 'search_type':
                    $search_type[] = $ic[1];
                    break;
                case 'is_controller':
                    $is_controller = $ic[1];
                    break;
                case 'is_model':
                    $is_model = $ic[1];
                    break;
                case 'is_view':
                    $is_view = $ic[1];
                    break;
                case 'addname':
                    $addname = $ic[1];
                    break;
            }
        }
        foreach ($col_c as $key => $value) {
            $arr_col = array();
            $value = trim($value);
            if($value!=''){
                $arr_col['col_c'] = $value;
                $arr_col['col_b'] = $col_b[$key];
                $arr_col['col_type'] = $col_type[$key];
                $arr_col['col_check'] = $col_check[$key];
            }
            $col[] = $arr_col;
        }
        foreach ($search_c as $key => $value) {
            $arr_search = array();
            $value = trim($value);
            if($value!=''){
                $arr_search['search_c'] = $value;
                $arr_search['search_b'] = $search_b[$key];
                $arr_search['search_type'] = $search_type[$key];
            }
            $search[] = $arr_search;
        }
        $info['addname'] = $addname;
        $info['is_view'] = $is_view;
        $info['is_controller'] = $is_controller;
        $info['is_model'] = $is_model;
        $info['search'] = $search;
        $info['col'] = $col;
        $result = $this->creatMVC($info);
        return $result;
    }
}