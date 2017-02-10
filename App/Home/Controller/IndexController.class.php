<?php
namespace Home\Controller;

class IndexController extends CommonController {

    public function index()
    {
        if(!(session('?role'))){
            $this->redirect('Index/login');
            exit;
        }
        $name = M('functionreg')->getField('funname');
        $action = A('Functionreg');
    	$menus = $action->getFunList();
    	$this->assign('menus',$menus);
        $this->display();
    }

    public function login()
    {
        $info = '';
        if(IS_POST){
            $request = I();
            $db = D('Userreg');
            $result = $db->check_exist($request);
            if($result){
                $data['state'] = date('Y-m-d H:i:s');
                $where['userid'] = $result['userid'];
                $db->getTableEdit($where,$data);
                $roleDb = D('Rolereg');
                $where['roleid'] = $result['roleid'];
                $roleData = $roleDb->where($where)->field('rolename,functionlist')->find();
                if(!empty($roleData)){
                    session('menu',$roleData['functionlist']);
                    session('role',$roleData['rolename']);
                    session('user',$result['username']);
                    session('roleid',$result['roleid']);
                    session('userid',$result['userid']);
                    session('userarea',$result['userarea']);
                    $this->redirect('Index/index');
                    exit;
                }else{
                    $info = '用户名，密码错误';
                }
            }else{
                $info = '用户名，密码错误';
            }
        }
        $this->assign('info',$info);
        $this->display();
    }

    public function loginOut()
    {
        session(null);
        $this->redirect('Index/login');
    }

    public function home()
    {
        $this->display();
    }
}