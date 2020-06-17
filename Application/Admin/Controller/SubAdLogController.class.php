<?php
namespace Admin\Controller;
use Think\Controller;
class SubAdLogController extends Controller {
    /**
     * 管理员登录
     * 
     */
    public function pttlogin(){
        $scheme = $_SERVER['REQUEST_SCHEME'];
        $servername = $_SERVER['SERVER_NAME'];
        if (IS_POST) {
            $verify = I('post.vertify');
            if ($this->check_verify($verify)) {
                $name = I('post.name');
                $pass = I('post.pass');
                $admain = D('admain');
                if (!empty($name) && !empty($pass)) {
                    if ($admain -> sublogin($name,$pass)) {
                        //$this->success("登录成功!",U("Admin/Index/index"));
                        $this->redirect('Admin/Subpart/device_list');
                        exit();
                    }else{
                        echo  "<script>window.alert(\"用户或密码错误！\");</script>";
                        echo  "<script>Javascript:window.history.back(-1);</script>";
                        exit;
                    }
                }else{
                    echo  "<script>window.alert(\"用户名和密码不能为空！\");</script>";
                    echo  "<script>Javascript:window.history.back(-1);</script>";
                    exit();
                }
            }else{
                echo  "<script>window.alert(\"验证码错误！\");</script>";
                echo  "<script>Javascript:window.history.back(-1);</script>";
                exit();
            }
        }
        $this-> assign('scheme',$scheme);
        $this-> assign('servername',$servername);
        $this->display();
    }


    /**
     * 获取验证码
     */
    public function vertify(){
    	$Verify = new \Think\Verify();
        $Verify->fontttf = '4.ttf';
        //$Verify->fontttf = '0123456789';
		$Verify->fontSize = 30;
		$Verify->length   = 4;
		$Verify->useNoise = true;
		$Verify->entry();
    }

    /**
     * 检测输入的验证码是否正确
     */
    function check_verify($verify, $id = ''){
        $verify1 = new \Think\Verify();
        return $verify1->check($verify,$id);
    }


    /**
     *管理员退出登录
     * 
     */  
    public function sublogout(){
        // unset($_SESSION['ValidUser']);
        // dump($_SESSION);exit;
        unset($_SESSION['subLogUser']);
        unset($_SESSION['subLogOpid']);
        unset($_SESSION['subLogLevel']);
        unset($_SESSION['submanid']);
        $this->redirect('Admin/SubAdLog/pttlogin');
        //$this->success('退出成功',U('Admin/Admin/krelogin'));
    }


    


    


}