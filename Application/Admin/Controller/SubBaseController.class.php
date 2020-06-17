<?php
namespace Admin\Controller;
use Think\Controller;
//header("Content-type:text/html; charset=utf-8");
class SubBaseController extends Controller {

	public $book_li_url;   //=  "/tyt_en/Admin/Book/booking_list";
    public $d_li_url;  //=  "/tyt_en/Admin/Index/d_list";
    public $de_li_url; // =  "/tyt_en/Admin/Index/device_list";
    public $ad_li_url; //   =  "/tyt_en/Admin/Admin/adminer_list";  
    public $gp_li_url;   //=  "/tyt_en/Admin/Group/group_list";
    public $gp_add_url;
    public $fri_ed_url;  
    public $de_add_url;
    public $de_edit_url;
    public $ser_dir_url;
    public $gp_uMembers;
    public $subad_li_url;


    /**
     * 成功或错误提示后跳转路径
     * @var string
     */
    
    public $back_url   =  "<script>Javascript:window.history.back(-1);</script>";

    /**
     * 提示信息
     * @param $tips string
     */
    public function showtips($tips){
        echo  "<script>window.alert(\"$tips\");</script>";
    }

	public function _initialize(){
        
		//初始化跳转路径
		$url = dirname($_SERVER['SCRIPT_FILENAME']); //根目录文件夹部分
        $arr = parse_url($url);
        $arr = explode("/",$arr['path']);
        $dirurl = end($arr); //取得最后的根目录名
        $this->ser_dir_url = '/'.$dirurl;
        $this ->book_li_url = '/'.$dirurl.'/Admin/Book/booking_list';
        $this ->book_add_url = '/'.$dirurl.'/Admin/Book/booking_add';
        $this ->d_li_url = '/'.$dirurl.'/Admin/Subpart/d_list';
        $this ->de_li_url = '/'.$dirurl.'/Admin/Subpart/device_list';
        $this ->de_add_url = '/'.$dirurl.'/Admin/Subpart/device_add';
        $this ->de_edit_url = '/'.$dirurl.'/Admin/Subpart/device_edit';
        $this ->ad_li_url = '/'.$dirurl.'/Admin/Admin/adminer_list';
        $this ->gp_li_url = '/'.$dirurl.'/Admin/SubGroup/group_list';
        $this ->gp_add_url = '/'.$dirurl.'/Admin/SubGroup/group_add';
        $this ->fri_ed_url = '/'.$dirurl.'/Admin/Subpart/friend_edit';
        $this ->gp_uMembers = '/'.$dirurl.'/Admin/SubGroup/uMembers';
        $this ->subad_li_url = '/'.$dirurl.'/Admin/Subadmin/subad_list';
        


		//判断是否已经正常登录
		$admin = D('admain');
		if (!$admin->issublogin()) {	
		    $url = '/'.$dirurl."/Admin/SubAdLog/pttlogin";
		    echo  "<script>window.alert(\"请先登录！\");</script>";
		    header("refresh:0;url={$url}");
			//$this->error('请先登录',U('Home/Index/Index'));
			exit();
	    }
	}

}