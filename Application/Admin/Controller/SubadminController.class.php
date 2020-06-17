<?php
namespace Admin\Controller;
use Think\Controller;
class SubadminController extends BaseController {

    /**
     * 级别为3 管理员列表
     */
    public function subad_list(){
        $LogUser = session("LogUser");
        $iLevel = session("LogLevel");
        $upLevel = session("upLevel");
        $gopid = I('get.opid');
        $gp = I('get.p');
        $dp = isset($gp) ? intval($gp) : 1;
        $_SESSION["dp"] = $dp;
        if (!empty($gopid))
        {
            $opid = $gopid;
        }
        else
        {
            $LogOpid = $_SESSION["LogOpid"];
            $opid = intval($LogOpid);
        }
        //取当前额度
        //$totalMax = $_SESSION["MaxUser"];
        $totalMax = M("admain") -> where(array('opid'=> $opid)) -> field('maxuser') -> find();
        $totalMax = $totalMax['maxuser'];
        $maxData = M("admain") -> where(array('manid'=> $opid)) -> field('maxuser') -> select();
        $totalUsed = 0;
        foreach ($maxData as $key => $v) {
            $everymax = $v['maxuser'];
            $totalUsed += $everymax;
        }

        if(intval($totalMax) >= intval($totalUsed)){
            $totalLeft = intval($totalMax) - intval($totalUsed);
        }else{
            $totalLeft = 0;
        }

        $_SESSION["TotalLeft"] = $totalLeft;

    
        // //搜索管理员
        $getSearchc = I('get.searchc');

        if (!empty($getSearchc))
        {
            $_SESSION['searchc'] = $getSearchc;
        }
        //$searchc = session("searchc");
        if (!empty($getSearchc))  //带搜索条件
        {   
            $getp =  $_GET['getup'];
            if (!empty($getp)) {
                $this-> assign("getp", $getp);
            }        
            $map['opid'] = array('like',"%{$getSearchc}%");
            $map['opname'] = array('like',"%{$getSearchc}%");
            $map['_logic'] = 'or';
            $where['_complex'] = $map;  //组合条件查询
            $where['manid'] = $opid;
            $where['level'] = 10;
        }
        else  //不搜索
        {
            $where['manid'] = $opid;  
            $where['level'] = 10;
        }

        $total = M("admain") -> where($where) -> count();
        $prows = 10;
        $showpn = ceil($total/$prows);
        $Page = new \Think\Page($total,$prows);// 实例化分页类 传入总记录数和每页显示的记录数
        pagelist($Page);  //调用分页公共函数
        $list = M("admain") -> where($where) -> order('opid desc') -> limit($Page->firstRow.','.$Page->listRows) -> select();
        $show = $Page->show();// 分页显示输出
        //dump($list);
        //dump( M("admain")->getLastSql() ); exit;
        


        $this-> assign("showpn", $showpn);
        $this-> assign("totalUsed", $totalUsed);
        $this-> assign("totalMax", $totalMax);
        $this-> assign("totalLeft", $totalLeft);
        $this-> assign("list", $list);
        $this-> assign("opid", $opid);
        $this-> assign("total", $total);
        $this -> assign('LogUser',$LogUser);
        $this -> assign('iLevel',$iLevel);
        $this-> assign("page", $show);
        $this-> assign("searchc", $getSearchc);
        $this-> assign("upLevel", $upLevel);
        $this -> display();
    }
    

    /**
     * 管理员添加
     */
    public function subad_add(){
        $upLevel = $_SESSION["upLevel"];
        $LogUser = session("LogUser");
        $iLevel = $_SESSION["LogLevel"];
        $opid = $_SESSION["LogOpid"];
        $admain = D('admain');
        if (IS_POST) {
            $data = I('post.');
            if (!empty($data['subopname'])) {
                if (empty($data['subopname_res'])) {  //以防前端拦截无效时拦截名称重复
                    $subopnames = M("admain") -> where(array('opname'=> $data['subopname'])) -> select();
                    if (!empty($subopnames)) {
                        $this -> showtips("二级管理名称已被使用，请重新填写！");
                        echo $this-> back_url;
                        exit();
                    }
                }
                $pwkey = session("sadchkedkey");  //在输入密码时提交ajax设置session
                $repwkey = session("resadchkedkey");
                $repassword = I('post.repassword');
                $password = I('post.password');
                if (empty($pwkey) || $pwkey !== '3') {
                    $this -> showtips("密码格式不正确！");
                    echo $this-> back_url;
                    exit();
                }
                if ($repwkey !== '103') {
                    $this -> showtips("密码两次输入不一致！");
                    echo $this-> back_url;
                    exit();
                }
                if ($repassword !== $password) {
                    $this -> showtips("密码两次输入不一致！");
                    echo $this-> back_url;
                    exit();
                }

                $date = date("Y-m-d H:i:s");
                $addnew['opid'] = null;
                $addnew['opname'] = I('post.subopname');
                $addnew['level'] = 10;
                $addnew['createtime'] = $date;
                $addnew['enable'] = '1';
                $addnew['password'] = I('post.password');
                $addnew['notes'] = I('post.notes');
                $addnew['manid'] = $_SESSION["LogOpid"];
                $addnew['maxuser'] = 0;
                $result = $admain -> add($addnew);
                if ($result != false) {
                    $this -> showtips("添加成功！");
                    header("refresh:0;url={$this->subad_li_url}"); //跳转至指定页面
                    exit();
                }else{
                    $this -> showtips("添加失败！");
                    echo $this-> back_url;
                    exit();
                }
            }else{
                $this -> showtips("二级管理名称必填项！");
                echo $this-> back_url;
                exit();
            }
                       
        }

        $this -> assign('totalLeft',$totalLeft);
        $this -> assign('LogUser',$LogUser);
        $this -> assign("upLevel", $upLevel);
        $this -> assign('iLevel',$iLevel);
        $this -> display();
    }

    /**
     * 查询二级管理员名称是否重复
     */
    public function subopname_select(){
        $subopname = I("subopname"); 
        $subopid = I("subopid");
        if (!empty($subopname) && empty($subopid)) {
            $len = strlen($subopname);
            if ($len > 30){
                $this->ajaxReturn(5);
                exit();
            }
            $subopnames = M("admain") -> where(array('opname'=> $subopname)) -> select();
            if (!empty($subopnames)){
                $this->ajaxReturn(4);
                exit();
            }else{
                $this->ajaxReturn(3);
                exit();
            }
        }
        elseif(!empty($subopname) && !empty($subopid)){  //修改二级管理时以提交opid区分
            $len = strlen($subopname);
            if ($len > 30){
                $this->ajaxReturn(5);
                exit();
            }
            $subopnames = M("admain") -> where(array('opname'=> $subopname)) -> find();
            if (!empty($subopnames) && $subopnames['opid'] !== $subopid) {
                $this->ajaxReturn(4);
                exit();
            }else{
                $this->ajaxReturn(3);
                exit();
            }
        }
        else{
            $this->ajaxReturn(n);
            exit();
        }
    }


    /**
     * 删除管理员
     */
    public function subad_delete(){
        $opid = I('get.subopid');
        $where['opid'] = $opid;
        $where['manid'] = $_SESSION["LogOpid"];
        $dp  = session("dp");
        $result = M('admain') -> where($where) -> find();
        if (empty($result)) {
            
            $this -> showtips("删除失败！");
            echo $this-> back_url;
            exit();
        }else{
            //增加防删功能 
            $res1 = M('user') -> where(array('submanid' => $opid)) -> select();
            if (!empty($res1)) {
                $this -> showtips("该账户下有用户请勿删除！");
                echo $this-> back_url;
                exit();
            }
            // $res2 = M('admain') -> where(array('manid' => I('get.opid'))) -> select();
            // if (!empty($res2)) {
            //     $this -> showtips("该账户下有子账户请勿删除！");
            //     echo $this-> back_url;
            //     exit();
            // }
            else{
                //修改管理员额度
                $res4 = M('admain') -> where(array('opid' => $opid)) -> delete();                          
                if ($res4 != false) {
                    $this -> showtips("删除成功！");
                    header("refresh:0;url={$this->subad_li_url}");
                    exit();
                }else{
                    $this -> showtips("删除失败！");
                    echo $this-> back_url;
                    exit();
                }      
                    
            }
        }
        
    }

    /**
     * 管理员编辑
     */
    public function subad_edit(){
        $totalLeft = $_SESSION["TotalLeft"];
        $LogUser = session("LogUser");
        $iLevel = $_SESSION["LogLevel"];
        $upLevel = $_SESSION["upLevel"];
        $opid = $_SESSION["LogOpid"];
        $admain = D('admain');
        $uopid = I('get.subopid');
        $map['opid'] = $uopid;
        $map['manid'] = $_SESSION["LogOpid"];
        $row = $admain->where($map) -> field('opid, opname, level,maxuser,notes') -> find();
        if (IS_POST) {            
            $data = I("post."); //dump($data);exit;
            if (!empty($data['subopname'])) {
                if (empty($data['subopname_res'])) {  //以防前端拦截无效时拦截名称重复
                    $subopnames = M("admain") -> where(array('opname'=> $data['subopname'])) -> find();
                    if (!empty($subopnames) && $subopnames['opid'] !== $data['subopid']){
                        $this -> showtips("二级管理名称已被使用，请重新填写！");
                        echo $this-> back_url;
                        exit();
                    }
                }
                $addnew['opname'] = I('post.subopname');
                // $addnew['password'] = I('post.password');
                $addnew['notes'] = I('post.notes');
                $map['opid'] = I('post.subopid');
                $map['manid'] = $_SESSION["LogOpid"];
                $result = $admain -> where($map) -> save($addnew);
                if ($result !== false) {
                    $p = $data['p'];
                    $this -> showtips("修改成功！");
                    header("refresh:0;url=$this->subad_li_url?p={$p}");
                    exit();
                }else{
                    $this -> showtips("修改失败！");
                    echo $this-> back_url;
                    exit();
                }
            }else{
                $this -> showtips("请填写二级管理名称!");
                echo $this-> back_url;
                exit();
            }
        }
        $this -> assign('row',$row);
        $this -> assign('dp',$dp);
        $this -> assign('upLevel',$upLevel);
        $this -> assign('iLevel',$iLevel);
        $this -> assign('LogUser',$LogUser);
        $this -> assign('totalLeft',$totalLeft);
        $this -> display();

    }

    /**
     * 检验二级管理员密码格式
     */
    public  function pregSubadPass(){
        $pwd = I("post.password");
        $repwd = I("post.repassword");
        if (!empty($pwd)) {  //第一次输入密码
            $pattern = "/^[a-zA-Z0-9]\w{5,17}$/";   //至少要包含大小写字母/数字/_的其中2种才匹配通过,不能包含 其他特殊字符
            //$pattern='/^(?![A-Z]+$)(?![a-z]+$)(?!\d+$)(?![\W_]+$)\S{6,18}$/';  //至少要包含大小写字母/数字/字符的其中2种才匹配通过;
            $patternlet= "/^[a-z]+$/";
            $patternLet= "/^[A-Z]+$/";
            $patternN= "/^[0-9]+$/";
            if(preg_match($patternlet,$pwd))   //如果只包含小写不允许
            {
                session("sadchkedkey","44");
                $msg = "4";
                $this->ajaxReturn($msg);
                exit();
            }
            elseif(preg_match($patternLet,$pwd))   //如果只包含写一种不允许
            {
                session("sadchkedkey","44");
                $msg = "4";
                $this->ajaxReturn($msg);
                exit();
            }
            elseif(preg_match($patternN,$pwd))   //如果只包含数字一种不允许
            {
                session("sadchkedkey","44");
                $msg = "4";
                $this->ajaxReturn($msg);
                exit();
            }
            elseif(!preg_match($pattern,$pwd))    //至少要包含大小写字母/数字/_的其中2种才匹配通过
            {
                session("sadchkedkey","44");
                $msg = "4";
                $this->ajaxReturn($msg);
                exit();
            }else{
                session("subadPwd",$pwd);
                session("sadchkedkey","3");
                $msg = "3";
                $this->ajaxReturn($msg);
                exit();
            }
        }


        if (!empty($repwd)) {  //确认密码
            $pwd1 = session("subadPwd");
            if ($repwd !== $pwd1) {
                session("resadchkedkey","114");  
                $msg = "4";
                $this->ajaxReturn($msg);
            }else{
                session("resadchkedkey","103");
                $msg = "3";
                $this->ajaxReturn($msg);
            }
        }

    }

    /**
     * 修改二级管理员密码
     */
    public  function subadPass(){
        $opid = session("LogOpid");
        $subopid = I("post.subopid");
        $opwd = I("post.opassword");
        $pwd = I("post.password");
        $repwd = I("post.repassword");
        $type = I("post.type"); 
        $where['opid'] = $subopid;
        $where['manid'] = $opid;
        
        //匹配旧密码是否正确
        if ($type == "1") { 
            if (!empty($opwd) && !empty($subopid) && !empty($opid)) { 
                $Info = M("admain") ->where($where) ->field("password")-> find();
                //echo M('admain')->getLastSql();
                if (!empty($Info)) {
                    $opasswd = $Info['password'];
                    if ($opwd !== $opasswd) {
                        $msg = "4";
                        $this->ajaxReturn($msg);
                        exit();
                    }else{
                        session("getSubadPwKey","1133");
                        $msg = "3";
                        $this->ajaxReturn($msg);
                        exit();
                    }
                }else{
                    $msg = "4";
                    $this->ajaxReturn($msg);
                    exit();
                }
            }else{
                $msg = "4";
                $this->ajaxReturn($msg);
                exit();
            }
        }

        //修改新密码
        if ($type == "2") {
            $getSubadPwKey = session("getSubadPwKey"); //原密码正确
            $resadchkedkey = session("resadchkedkey"); //两次输入一致
            $sadchkedkey = session("sadchkedkey"); 
            if (!empty($pwd) && !empty($repwd) && !empty($subopid) && !empty($opid)) {
                if ($sadchkedkey !== "3") {  //密码格式不正确
                    $msg = "4";
                    $this->ajaxReturn($msg);
                    exit();
                }else{
                    if ($getSubadPwKey == "1133" && $resadchkedkey == "103") {  //判断原密码及两次输入密码是否一致
                        $res = M("admain") ->where($where) -> save(array("password"=>$pwd)); 
                        if ($res !== false) {
                            $msg = "3";
                            $this->ajaxReturn($msg);
                            exit();
                        }else{
                            $msg = "4";  //修改失败
                            $this->ajaxReturn($msg);
                            exit();
                        }
                        
                    }else{
                        $msg = "4";
                        $this->ajaxReturn($msg);
                        exit();
                    }
                }
                
            }else{
                $msg = "4";
                $this->ajaxReturn($msg);
                exit();
            }
        }


    }


 
    
    
    /**
     * 管理员下发额度
     */
    public function limitdown(){
        $dp = session("dp");
        $LogUser = session("LogUser");
        $iLevel = session("LogLevel");
        $opid = session("LogOpid");
        $map['manid'] = $opid;
        if ($iLevel >= 2)
        {
            $yuid = 0;
            $fkid = $opid;
        }
        else
        {
            $yuid = $opid;
            $fkid = 0;
        }
        $bladid = 0;
    
        if (IS_POST) {
            $map['opid'] = I("post.opid");
        }else{
            $map['opid'] = I("get.opid");
             
        }
        //取当前管理员额度
        $maxData = M("admain") -> where(array('opid'=> $opid)) -> field('maxuser') -> find();
        $totalMax = $maxData["maxuser"];
        $totalUsed = 0;
        $maxData = M("admain") -> where(array('manid'=> $opid)) -> field('maxuser') -> select();
        foreach ($maxData as $key => $v) {
            $everymax = $v['maxuser'];
            $totalUsed += $everymax;
        }
    
        if(intval($totalMax) >= intval($totalUsed)){
            $totalLeft = intval($totalMax) - intval($totalUsed);
        }else{
            $totalLeft = 0;
        }
    
    
        //dump(I("get.opid"));
        $row = M('admain') -> where($map) -> field('opid,opname,maxuser') -> find();
        if (empty($row)) {
            $this -> showtips("该账户不存在！");
            echo $this-> back_url;
            exit();
        }
        if (IS_POST) {
            $blnum = I("post.blnum");
            $blnum = intval($blnum);
            if ($blnum > $totalLeft) {
                //$this->error("你当前额度值不够下发额度,请联系上级管理员！");
                $this -> showtips("你当前额度值不够下发额度,请联系上级管理员！");
                echo $this-> back_url;
                exit();
            }
            //dump($blnum);
            if (!empty($blnum)) {
                //取分控管理员ID
                if ($fkid == 0)   //level < 2
                {
                    $fkData = M("admain") -> where(array('opid'=>$yuid)) -> field('manid') -> find();
                    //dump( M('admain') -> getLastSql() );
                    $fkid = $fkData['manid'];
                }
                //取系统管理员ID
                $xtData = M("admain") -> where(array('opid'=>$fkid)) -> field('manid') -> find();
                $bladid = $xtData['manid'];
                //dump( M('admain') -> getLastSql() );
                if($bladid == NUll) {
                    $bladid = $opid;
                }
    
                $date = date("Y-m-d H:i:s");
    
                //如果是添加账号在分控提交时会变成增加预售账号
                $bkType = 0;
                if ($bkType == 0)  //添加账号
                {
                    if ($iLevel >= 3)  //级别>= 2
                    {
                        $bkType = 2;  //增加预售账号
                    }
                    $blnum = I("post.blnum");
                }
    
    
                $maxData = M("admain") -> where($map) -> field('maxuser') -> find();
                $maxuser = $maxData['maxuser'];
                $save['maxuser'] = $maxuser + $blnum;
                $res = M("admain") -> where($map) -> save($save);
                if ($res) {
                    $date = date("Y-m-d H:i:s");
                    //$sql = "INSERT INTO tab_bk_list(bltype, blfkid, blst, bldate, blnum, bladid) VALUES(1, {$_POST["opid"]}, 1, '$date', $quota, $iOpid)";
                    $data['bltype'] = $bkType;  //添加账号
                    if ($iLevel == 3) {
                        $data['blfkid'] = I("post.opid");;  //分控管理员ID
                        $data['blyuid'] = 0; //域管理员ID
                        $data['bladid'] = $bladid;  //系统管理员ID
                    }else{
                        $data['blfkid'] = $opid;  //分控管理员ID
                        $data['blyuid'] = I("post.opid"); //域管理员ID
                        $data['bladid'] = $bladid;  //系统管理员ID
                    }
                    $data['blst'] = 2;
                    $data['bldate'] = $date;
                    $data['blnum'] = $blnum;
                    //dump($data);exit;
                    $res = M("bk_list") -> add($data);
                    if ($res) {
                        $this -> showtips("下发额度成功！");
                        header("refresh:0;url={$this->ad_li_url}");
                        exit();
                    }else{
                        $this -> showtips("添加下发记录失败！");
                        echo $this-> back_url;
                        exit();
                    }
                }else{
                    $this -> showtips("下发额度失败！");
                    echo $this-> back_url;
                    exit();
                }
            }
        }
    
    
        $this -> assign('row',$row);
        $this -> assign('totalLeft',$totalLeft);
        $this -> assign('dp',$dp);
        $this -> assign('LogUser',$LogUser);
        $this -> assign('opid',$opid);
        $this -> assign('iLevel',$iLevel);
        $this->display();
    }
    
    /**
     * 回收额度
     */
    public function takeback(){
        $dp = session("dp");
        $LogUser = session("LogUser");
        $iLevel = session("LogLevel");
        $opid = session("LogOpid");
    
        if ($iLevel >= 2)
        {
            $yuid = 0;
            $fkid = $opid;
        }
        else
        {
            $yuid = $opid;
            $fkid = 0;
        }
        $bladid = 0;
    
        if (IS_POST) {
            $map['opid'] = I("post.opid");
        }else{
            $map['opid'] = I("get.opid");
             
        }

        $row = M('admain') -> where($map) -> field('opid,opname,maxuser,level ') -> find();  //检测记录是否为当前管理员下用户
        if (empty($row)) {
            $this -> showtips("该用户不存在！");
            echo $this-> back_url;
            exit();
        }
    
    
        if (IS_POST) {
            $blnum = I("post.blnum");
            $blnum = intval($blnum);
            $validuser = I("post.validuser");
            $validuser = intval($validuser);
            //dump(I("post."));exit;
            if ($blnum > $validuser) {
                $this -> showtips("回收额度不能超过未使用额度！");
                echo $this-> back_url;
                exit();
            }
    
            //取系统管理员ID
            $xtData = M("admain") -> where(array('opid'=>$fkid)) -> field('manid') -> find();
            $bladid = $xtData['manid'];
            if($bladid == NUll) {
                $bladid = $opid;
            }
    
            $bkType == 3;   //上交空闲账号
            if ($yuid == 0)
            {
                $bkType = 4;    //回收账号
            }
            //dump($blnum);
            if (!empty($blnum)) {
                $maxData = M("admain") -> where($map) -> field('maxuser') -> find();
                $maxuser = $maxData['maxuser'];
                $save['maxuser'] = $maxuser - $blnum;
                $res = M("admain") -> where($map) -> save($save);
                if ($res) {
                    $date = date("Y-m-d H:i:s");
                    //$sql = "INSERT INTO tab_bk_list(bltype, blfkid, blst, bldate, blnum, bladid) VALUES(1, {$_POST["opid"]}, 1, '$date', $quota, $iOpid)";
                    $data['bltype'] = $bkType;  //添加账号
                    if ($iLevel == 3) {
                        $data['blfkid'] = I("post.opid");;  //分控管理员ID
                        $data['blyuid'] = 0; //域管理员ID
                        $data['bladid'] = $bladid;  //系统管理员ID
                    }else{
                        $data['blfkid'] = $opid;  //分控管理员ID
                        $data['blyuid'] = I("post.opid"); //域管理员ID
                        $data['bladid'] = $bladid;  //系统管理员ID
                    }
                    $data['blst'] = 2;
                    $data['bldate'] = $date;
                    $data['blnum'] = $blnum;
    
                    $res = M("bk_list") -> add($data);
                    if ($res) {
                        $this -> showtips("回收额度成功！");
                        header("refresh:0;url={$this->ad_li_url}");
                        exit();
                    }else{
                        $this -> showtips("添加回收记录失败！");
                        echo $this-> back_url;
                        exit();
                    }
                }else{
                    $this -> showtips("额度回收失败！");
                    echo $this-> back_url;
                    exit();
                }
            }
        }
    
    
        $this -> assign('row',$row);
        $this -> assign('dp',$dp);
        $this -> assign('LogUser',$LogUser);
        $this -> assign('opid',$opid);
        $this -> assign('iLevel',$iLevel);
        $this->display();
    }


    /**
     * 用户分配详情  级别为5
     */
    public function dbution_tails(){
        $rOpid = I("get.opid");
        if (!empty($rOpid)) {
            $opid =  $rOpid; //I("opid");
        } else {
            $opid = session("LogOpid");
        }
        $iLevel = $_SESSION["LogLevel"];
        $gp = I('get.p');
        $dp = isset($gp) ? intval($gp) : 1;
        $_SESSION['dp'] = $dp;

        //计算使用额度  将Nums.php的算法合并
        $validuser = 0;
        $usednum = 0;
        
        $adrow = M("admain") -> where(array('opid'=> $opid)) -> field('level, manid, opname,maxuser') -> find();
        //dump( M("admain")->getLastSql() );

        if (!empty($adrow))
        {
            //最大授权数  
            $maxuser = $adrow['maxuser'];
            $manid = $adrow['manid']; 
            $manName = $adrow["opname"];
            $NowLevel = $adrow['level'];
        }
            
        //已分配
        $usedList = M('admain') -> where(array('manid' => $opid)) -> field('maxuser') -> select();

        $dbution = 0;
        if (!empty($usedList))
        {
            $dbution = 0;
            foreach ($usedList as $v) {
                $dbution += $v['maxuser'];
            }
        }

        //未分配
        if ($maxuser >= $dbution)
        {
            $undbution = $maxuser - $dbution;
        }
        else
        {
            $undbution = 0;
        }

        $LogUser = session("LogUser");
        $iLevel = session("LogLevel");  

        //查询列表数据 及使用额度
        switch ($NowLevel) {
            case 1:
                $searchc = I('get.searchc');

                if (!empty($searchc)){
                    //$where = "AND (username like '%{$searchc}%' OR account like '%{$searchc}%')";
                    $map['username'] = array("like","%{$searchc}%");
                    $map['account'] = array("like","%{$searchc}%");
                    $map['_logic'] = 'or';       //定义组合关系
                    $where['_complex'] = $map;  //组合条件查询
                    $where['manid'] = $opid;
                }else{
                    $where['manid'] = $opid;
                }

                //数据分页
                $total = M("user") -> where($where) -> count();
                $Page = new \Think\Page($total,8);// 实例化分页类 传入总记录数和每页显示的记录数
                pagelist($Page);  //调用分页公共函数
                $list = M("user") -> where($where) -> order('uid desc') -> limit($Page->firstRow.','.$Page->listRows) -> select();
                $show = $Page->show();// 分页显示输出

                //已注册终端数
                $usednum = M('user') -> where(array('manid' => $opid)) -> field('maxuser') -> count();
                break;
            case 2:  
                //级别为2时

                $searchc = I('get.searchc');
                if (!empty($searchc)) {
                    $map['opid'] = array("like","%{$searchc}%");
                    $map['opname'] = array("like","%{$searchc}%");
                    $map['_logic'] = 'or';      
                    $where['_complex'] = $map;  //组合条件查询
                    $where['manid'] = $opid;
                }else{
                    $where['manid'] = $opid;
                }
                //计算行数total                
                $total = M("admain") -> where($where) -> count();
                $Page = new \Think\Page($total,8); // 实例化分页类 传入总记录数和每页显示的记录数
                pagelist($Page);  //调用分页公共函数
                $list = M("admain") -> where($where) -> order('opid desc') -> limit($Page->firstRow.','.$Page->listRows) -> select();
                $show = $Page->show();// 分页显示输出

                

            case 3:
                $opidList = M('admain') -> where(array('manid' => $opid)) -> field('opid') -> select();
                $sNumList = array();
                $tNumList = array();
                //分控管理员ID
                if (!empty($opidList))
                {
                    foreach ($opidList as $v) {
                        $sOpid = $v['opid'];
                        $sNumList[] = $sOpid;
                    }
                }
                //域管理员ID
                if (!empty($sNumList))
                {
                    foreach ($sNumList as $v)
                    {
                        $tOpData = M('admain') -> where(array('manid' => $v)) -> field('opid') -> select();
                        if (!empty($tOpData))
                        {
                            foreach ($tOpData as $v) {
                                $tOpid = $v['opid'];
                                $tNumList[] = $tOpid;
                            }
                        }
                    }
                }
                //已注册数量
                if (!empty($tNumList))
                {
                    $map['manid'] = array('in',$tNumList);
                    $usednum = M('user') -> where($map) -> count();        
                }
            case 4:
                $searchc = I('get.searchc');
                if (!empty($searchc)) {
                    $map['opid'] = array("like","%{$searchc}%");
                    $map['opname'] = array("like","%{$searchc}%");
                    $map['_logic'] = 'or';      
                    $where['_complex'] = $map;  //组合条件查询
                    $where['manid'] = $opid;
                }else{
                    $where['manid'] = $opid;
                }
                //计算行数total                
                $total = M("admain") -> where($where) -> count();
                $prows = 8;
                $showpn = ceil($total/$prows);  //方便页面输入框跳转判断
                $Page = new \Think\Page($total,$prows); // 实例化分页类 传入总记录数和每页显示的记录数
                pagelist($Page);  //调用分页公共函数
                $list = M("admain") -> where($where) -> order('opid desc') -> limit($Page->firstRow.','.$Page->listRows) -> select();
                $show = $Page->show();// 分页显示输出

                //计算已注册终端数
                $opidList = M('admain') -> where(array('manid' => $opid)) -> field('opid') -> select();
                $rNumList = array();
                $sNumList = array();
                $tNumList = array();
                //系统管理员ID
                if (!empty($opidList))
                {
                    foreach ($opidList as $v) {
                        $rOpid = $v['opid'];
                        $rNumList[] = $rOpid;
                    }
                }
                //分控管理员ID
                if (!empty($rNumList))
                {
                    foreach ($rNumList as $v)
                    {
                        $sOpData = M('admain') -> where(array('manid' => $v)) -> field('opid') -> select();
                        if (!empty($sOpData))
                        {
                            foreach ($sOpData as $v) {
                                $sOpid = $v['opid'];
                                $sNumList[] = $sOpid;
                            }
                        }
                    }
                }
                //域管理员ID
                if (!empty($sNumList))
                {
                    foreach ($sNumList as $v)
                    {
                        $tOpData = M('admain') -> where(array('manid' => $v)) -> field('opid') -> select();
                        if (!empty($tOpData))
                        {
                            foreach ($tOpData as $v) {
                                $tOpid = $v['opid'];
                                $tNumList[] = $tOpid;
                            }
                        }
                    }
                }
                //已注册数量
                if (!empty($tNumList))
                {
                    $map['manid'] = array('in',$tNumList);
                    $usednum = M('user') -> where($map) -> count();
                }

                break;
            case 5:
                $searchc = I('get.searchc');
                if (!empty($searchc)) {
                    $map['opid'] = array("like","%{$searchc}%");
                    $map['opname'] = array("like","%{$searchc}%");
                    $map['_logic'] = 'or';

                    //计算行数total
                    $where['_complex'] = $map;
                    $where['level'] = array("lt","5");
                    $total = M("admain") -> where($where) -> count();
                    $Page = new \Think\Page($total,8);// 实例化分页类 传入总记录数和每页显示的记录数
                    pagelist($Page);  //调用分页公共函数
                    $list = M("admain") -> where($where) -> order('opid desc') -> limit($Page->firstRow.','.$Page->listRows) -> select();
                    $show = $Page->show();// 分页显示输出
                    //dump($list);
                    //dump( M("admain")->getLastSql() );

                }else {
                    //计算行数total
                    $where['level'] = array("eq","3");
                     //echo  M("admain") -> getLastSql();
                    $total = M("admain") -> where($where) -> count();
                    $Page = new \Think\Page($total,8);// 实例化分页类 传入总记录数和每页显示的记录数
                    pagelist($Page);  //调用分页公共函数
                    $list = M("admain") -> where($where) -> order('opid desc') -> limit($Page->firstRow.','.$Page->listRows) -> select();
                    $show = $Page->show();// 分页显示输出   
                }
                break;
        }
        


        $this -> assign('showpn',$showpn);
        $this -> assign('validuser',$validuser);
        $this -> assign('maxuser',$maxuser); 
        $this -> assign('dbution',$dbution);
        $this -> assign('undbution',$undbution);
        $this -> assign('opid',$opid);
        $this -> assign('total',$total); 
        $this -> assign('searchc',$searchc);
        $this -> assign('LogUser',$LogUser);
        $this -> assign('NowLevel',$NowLevel);
        $this -> assign('manid',$manid);
        $this -> assign('manName',$manName);
        $this -> assign('page',$show);
        $this -> assign('iLevel',$iLevel);
        $this -> assign('list',$list);
        $this -> assign('dp',$dp);
        $this -> display();
    }

    /**
     * 管理员密码修改
     */
    public function adpass_edit(){
        $LogUser = session("LogUser");
        $iLevel = $_SESSION["LogLevel"];
        $opid = $_SESSION["LogOpid"];
        $oldpass = trim(I('post.oldpass'));
        $newpass = trim(I('post.newpass'));
        $confirm_pass = trim(I('post.confirm_pass'));   
        if (!empty($oldpass)){
            $row = M("admain") -> where(array('opid'=>$opid)) -> field('password') ->find();
            if ($oldpass !== $row['password']){
                $this -> showtips("旧密码不正确！");
                echo $this-> back_url;
                exit();
            }
        }
        if (!empty($newpass) && !empty($confirm_pass)){
            if ($newpass !== $confirm_pass){
                $this -> showtips("新密码两次输入不一致,请重新输入！");
                echo $this-> back_url;
                exit();
            }else {
                $data['password'] = $confirm_pass;
                $res = M("admain") ->where(array('opid'=> $opid)) ->save($data);    
                if ($res){                   
                    if ($iLevel ==1){                       
                        $this -> showtips("密码修改成功！");
                        header("refresh:0;url={$this->de_li_url}"); //跳转至指定页面
                        exit();
                    }else {
                        $this -> showtips("密码修改成功！");
                        header("refresh:0;url={$this->ad_li_url}"); //跳转至指定页面
                        exit();
                    }
                    
                }else {
                    $this -> showtips("密码修改失败！");
                    echo $this-> back_url;
                    exit();
                }
            }
        }
    
        $this-> assign("opid", $opid);
        $this-> assign("total", $total);
        $this -> assign('LogUser',$LogUser);
        $this -> assign('iLevel',$iLevel);
        $this->display();
    }

    /**
     * 参数设置
     */
    public function sset(){
        $LogUser = session("LogUser");
        $iLevel = session("LogLevel");

        $this->assign('LogUser',$LogUser);
        $this->assign('iLevel',$iLevel);
        $this-> display();
    }






}