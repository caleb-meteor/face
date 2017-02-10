<?php
namespace Home\Controller;

class FunctionregController extends CommonController 
{

    public function dataList()
    {
       /* $data = D('Functionreg')->funListAll();
        $ids = array(0);
        $menuData = $this->formatMenu($ids,$data);
        $this->ajaxReturn($menuData);*/
        $this->ajaxReturn($this->getFunList());
    }

    /**
     * 获取菜单列表
     * @return array
     */
    public function getFunList()
    {
        $ids = array(0);
        $db = D('Functionreg');
        $menuData= session('menu');
        $where['funid'] = array('in',explode(',', $menuData));
        $data = $db->where($where)->order('ordernum desc')->select();      
        $datac = $this->getParentMenu($data);
        $data = array_merge($data,$datac);
        $menu = $this->formatMenu($ids,$data);
        return $menu;
    }

    public function indexMenuList()
    {
        $menus = $this->getUnflgParentMenu($menus);
    }
    /**
     * 格式化菜单
     * @param  array $ids   父级菜单id
     * @param  array $menus 修要处理的菜单
     * @return array        easyui的tree类型
     */
    public function formatMenu($ids,$menus)
    {
        $formatMenu = array();
        foreach ($ids as $id) {
            $odrMenu = array();
            foreach ($menus as $menu) {                
                if($id == $menu['prefunid']){                    
                    $odrMenu[] = $menu['ordernum'];
                    $doMenu['ordernum'] = $menu['ordernum'];
                    $nextIds[] = $menu['funid'];
                    $doMenu['id'] = $menu['funid'];
                    $doMenu['text'] = $menu['funname'];
                    $doMenu['iconCls'] = $menu['iconcls'];
                    if($menu['url']){
                        //判断是否为批处理文件 如果有的话 原样输出菜单  否则U方法输出菜单
                        if(strstr($menu['url'], '.bat')){
                            $attributes['url'] = $menu['url'];
                        }else{                            
                            $attributes['url'] = U($menu['url']);                           
                        }
                        $doMenu['attributes'] = $attributes;                        
                    }
                    $children = $this->formatMenu($nextIds,$menus);
                    $nextIds = '';
                    if(!empty($children)){
                        $doMenu['state'] = 'closed';
                        $doMenu['children'] = $children;
                    }        
                    $formatMenu[]=$doMenu;
                    //对于生成的菜单在进行排序
                    array_multisort($odrMenu,SORT_DESC,$formatMenu);
                    $doMenu = '';
                }             
            } 
        }        
        //$formatMenu = json_encode($formatMenu);  
        return  $formatMenu;
    }
    /**
     * 获取未勾选的父级菜单数据
     * @param  array $menus 所有菜单
     * @return array  所有菜单及父级菜单 一维数组
     */
    public function getParentMenu($menus)
    {
        if(empty($menus)) return false;

        $datas = array();
        static $pids = array();
        foreach ($menus as $value) {
            $pids[] = $value['funid'];
        }
        foreach ($menus as $menu) {
            if(!in_array($menu['prefunid'],$pids)){                
                $menuDb = D('Functionreg');
                $where['funid'] = $menu['prefunid'];
                $data = $menuDb->where($where)->find();              
                if($data!=''  && !in_array($data['funid'],$pids)){                    
                    $datas[] = $data;
                }
                $pids[] = $menu['prefunid'];  
            }                    
        }        
        $datac = $this->getParentMenu($datas);
        if($datac){
            $datas = array_merge($datas,$datac);
        }        
        return $datas;
    }
}