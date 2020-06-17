<?php
namespace Admin\Controller;
use Think\Controller;
use \Org\Net\Http;

class SubGroupController extends SubBaseController {
      
    /**
     * 设置调度员
     */
    public $ddygid;
    public function ddy_edit(){
        $dp = session("dp");
        $subLogUser = session("subLogUser");
        $iLevel = session("subLogLevel");
        $opid = session("subLogOpid");
        $group = M("group");
        //$urlp = I('p');
        
        //dump($urlp);exit;
        if (IS_POST){
            $gid = I("post.gid");
            $urlp = I('post.urlp');
            if (empty($urlp)) {
                $urlp = 1;
            }
        }else {
            $gid = I("get.gid");
        }        
        $where['submanid'] = session("subLogOpid");
        $where['gid'] = $gid;
        if (!empty($gid)) {
            $upList = $group ->where($where) ->field('gid,gname,dispatcher') ->find();  //当前修改记录数据
            //dump($upList);
            //echo $group->getLastSql();exit();
            if (empty($upList)){
                $this -> showtips("没有找到该用户！！");
                echo $this-> back_url; 
                exit();
            }
            //查询原调度员
            $dispatcherid = $group ->where($where) ->field('dispatcher') ->find();  
            $dispatcherid = $dispatcherid['dispatcher'];
            if (!empty($dispatcherid) && $dispatcherid !== 0){                
                $dispaterRow = M("user") ->where(array('uid'=>$dispatcherid)) ->field('uid,username,account') ->find();
                //dump($dispatcherid);exit();
            } 
            
            //当前群组成员
            $gmembers = M('user u') 
                ->where(array('ug.gid'=> $gid)) 
                ->join('inner join tab_user_group ug on u.uid = ug.uid') 
                ->field('u.uid, username, account') 
                ->select();
        }   
        
        if (IS_POST){
            //dump(I("post."));exit();      
            $dispatcher = I("post.dispatcher");
            $dispatcherid = $group ->where($where) ->field('dispatcher') ->find();
            $dispatcherid = $dispatcherid['dispatcher'];
            if ($dispatcher == $dispatcherid){
                $this -> showtips("请选择原调度员以外的账号设置调度员！");
                echo $this-> back_url; 
                exit();
            }
            if ($dispatcher){
                $data['dispatcher'] = $dispatcher;
                $where['gid'] = $gid;
                $reslut = M("group") -> where($where) -> save($data);
                //dump($data);    
                //echo $group->getLastSql();exit();
                if ($reslut){
                    UDP_MakePack(8, $gid, 0, 0);
                    $this -> showtips("设置成功！");
                    $this->ddygid = $gid; 
                    header("refresh:0;url=$this->gp_li_url?p=$urlp");
                    //header("refresh:0;url=$this->gp_uMembers?gid=$urlgid&p=$urlp");
                    //header("refresh:0;url={$this->gp_li_url}"); //跳转至指定页面
                    exit();
                }else{
                    $this -> showtips("设置失败！");
                    echo $this-> back_url;
                    exit();
                }
            }
        }
        
        $this -> assign('dispaterRow',$dispaterRow);
        $this -> assign('gmembers',$gmembers);
        $this -> assign('subLogUser',$subLogUser);
        $this -> assign('iLevel',$iLevel);
        $this -> assign('page',$show);
        $this -> assign('upList',$upList);
        $this->display();
    }
    
    /**
     * 调度员设置 账号搜索
     */
    
    public function ddy_msearch(){
        $gid = I('gid'); //48049; //
        $ddy = I("ddy");
        $ddy = trim($ddy);
        //搜索当前群组下的所有用户
        if (!empty($ddy))
        {
            $maper['account'] = array('like',"%{$ddy}%");
            $maper['username'] = array('like',"%{$ddy}%");
            $maper['_logic'] = 'or';
            $map['gid'] = $gid;
            $map['_complex'] = $maper;
            $listData = M('user u') 
                ->where($map) 
                ->join('inner join tab_user_group ug on u.uid = ug.uid') 
                ->field('u.uid, username, account') 
                ->select();
            $this->ajaxReturn($listData);
            //echo M("group") -> getLastSql(); 
            //echo json_encode($list);
        }else{
            $map['gid'] = $gid;
            $listData = M('user u')
            ->where($map)
            ->join('inner join tab_user_group ug on u.uid = ug.uid')
            ->field('u.uid, username, account')
            ->select();
            $this->ajaxReturn($listData);
        }
        
    }
    
 

    /**
     * 添加群组-右侧账号搜索
     */
    
    public function gidSearch(){
        //验证request数据
        $sear = I('sear');
        if (empty($sear) && $sear != 0)
        {
            $map['submanid'] = I('gopid');
            $list = M('group') -> where($map) -> order('gid desc') -> field('gid, gname') -> select();
            $this->ajaxReturn($list);
            //echo M("group") -> getLastSql(); 
            //echo json_encode($list);
        }
        else
        {
            $sear = I('post.sear');
            $maper['gid'] = array('like',"%{$sear}%");
            $maper['gname'] = array('like',"%{$sear}%");
            $maper['_logic'] = 'or';
            $map['submanid'] = I('post.Opid');
            $map['_complex'] = $maper;
            $listData = M("group") -> where($map) -> order('gid desc') -> field('gid, gname') -> select();
            //echo M("group") -> getLastSql(); 
            $this->ajaxReturn($listData);
            
        }
    }

    /**
     * 群组列表
     */
    public function group_list(){
        $subLogUser = session("subLogUser");
        $iLevel = session("subLogLevel");
        $where = array();
        $where['submanid'] = session("subLogOpid");
        //搜索
        $serType = 0;
        $searchc = I('get.searchc');
        if(!empty($searchc))
        {
            $map['gid'] = array('like', "%{$searchc}%");
            $map['gname'] = array('like', "%{$searchc}%");
            $map['_logic'] = 'or';
            $where['_complex'] = $map; //组合查询条件
            $this -> assign('searchc',$searchc);
        }   


        //数据分页
        $total = M('group') -> where($where) -> count();// 查询当前登录管理员用户的总记录数 
        $prows = 13;
        $showpn = ceil($total/$prows);
        $Page = new \Think\Page($total,$prows);// 实例化分页类 传入总记录数和每页显示的记录数
        pagelist($Page);  //调用分页公共函数   
        $show = $Page->show();// 分页显示输出

        $groupData = M('group') -> where($where)->order('gid desc') -> limit($Page->firstRow.','.$Page->listRows)->select();
        // dump($groupData);
        // echo M('group') -> getLastSql();exit;
        //记住当前页，用于返回
        $gp = I('get.p');
        

        $this -> assign('showpn',$showpn);
        $this -> assign('subLogUser',$subLogUser); 
        $this -> assign('searchc',$searchc);
        $this -> assign('iLevel',$iLevel);
        $this -> assign('groupData',$groupData);
        $this -> assign('total',$total);
        $this -> assign('page',$show);
        $this -> display();
    }
    
    /**
     * 嵌套组定义
     */
    public function group_subedit(){
        $subLogUser = session("subLogUser");
        $iLevel = session("subLogLevel");
        $opid = session("subLogOpid");
        $map['submanid'] = $opid;
    
        //下拉列表显示的第一个群组 的现有子群组信息
        $maxGid = M('group') -> where($map) -> max('gid');
        $gtype = M('group') -> where(array('gid'=>$maxGid)) -> field('type') -> find();
        $curgData = M('subgroup sg')
        -> join('inner join tab_group g on g.gid = sg.subgid')
        -> where(array('sg.gid' => $maxGid))
        -> field('sg.subgid,gname')
        -> select();
        $cursubgidstr = "";  //将该字符串传至页面以便检测阻止提交条件
        foreach ($curgData as $v){
            $cursubgidstr .= $v['subgid']."|";
        }
    
    
        //总父组
        $parGroup = M('group') -> where($map) -> order('gid DESC') -> field('gid,gname,type') -> select();
        $map['type'] = array('neq',2);
        $groupData = M('group') -> where($map) -> order('gid DESC') -> field('gid,gname,type') -> select(); //可选子组
    
        if (IS_POST){
            $gid = I("post.select_dis");
            $selVal = trim(I("post.selVal"));
            //防止子组未修改提交
            if (!empty($selVal)){
                if ($selVal == $cursubgidstr){
                    return false;
                    exit();
                }
            
                //当前子群组列表
                $subGroupList = M('subgroup sg')
                -> join('inner join tab_group g on g.gid = sg.subgid')
                -> where(array('sg.gid' => $gid))
                -> field('sg.subgid,gname')
                -> select();
                //$this->ajaxReturn($subGroupList); exit();
                $cursubgids = "";
                foreach ($subGroupList as $v){
                    $cursubgids .= $v['subgid']."|";
                }
                if ($selVal == $cursubgids){
                    return false;
                    exit();
                }
            }
            $lstUser = explode('|', $selVal);
            //dump($lstUser);echo count($lstUser); exit();
    
            //删除所有子群组的情况
            if (!empty($gid) && count($lstUser) < 2){
                $subgData = M("subgroup") -> where(array('gid' => $gid)) ->select();
                if (!empty($subgData)){
                    foreach ($subgData as $v){
                        $arr = M("subgroup") -> where(array('subgid' => $v['subgid'])) ->select();
                        if (count($arr) == 1 && $arr[0]['gid'] == $gid){
                            $upData['type'] = 0;
                            $res = M("group") -> where(array('gid' => $v['subgid'])) ->save($upData);
                            //echo M("group") -> getLastSql();exit();
                            if ($res == false){
                                $arr = array('status'=> 0,'msg'=>"子群组类型修改失败！",'subg'=>$subGroupList);
                                $this->ajaxReturn($arr);
                                exit();
                            }
                        }
                    }
                    $delete = M('subgroup')->where(array('gid' => $gid))->delete();  //先将原来的子组gid删除
                    if ($delete !== false){
                        $updata['type'] = 0;
                        $res = M('group') -> where(array('gid'=>$gid)) -> save($updata);  //将原来的父组修改为普通组
                        if ($res !== false){
                            UDP_MakePack(9, $gid, 0, 0);
                            //当前子群组列表
                            $subGroupList = M('subgroup sg')
                            -> join('inner join tab_group g on g.gid = sg.subgid')
                            -> where(array('sg.gid' => $gid))
                            -> field('sg.subgid,gname')
                            -> select();
                            $arr = array('status'=> 1,'msg'=>"修改子群组成功！",'subg'=>$subGroupList);
                            $this->ajaxReturn($arr);
                            exit();
                        }else {
                            $arr = array('status'=> 1,'msg'=>"修改父组状态失败！");
                            $this->ajaxReturn($arr);
                            exit();
                        }
                    }else {
                        $this->ajaxReturn(array('status'=> 0,"msg"=>"修改子群组失败！"));
                        exit();
                    }
                }else {
                    $this->ajaxReturn(array('status'=> 0,"msg"=>"请选择子群组！"));
                    exit();
                }
            }
            //在原有的情况下修改子群组
            if (!empty($gid) && count($lstUser) > 0){
                array_pop($lstUser);  //将最后一个元素删除
    
                $subgData = M("subgroup") -> where(array('gid' => $gid)) ->select();
                if (!empty($subgData)){
                    foreach ($subgData as $v){
                        $arr = M("subgroup") -> where(array('subgid' => $v['subgid'])) ->select();
                        if (count($arr) == 1 && $arr[0]['gid'] == $gid){
                            $upData['type'] = 0;
                            $res = M("group") -> where(array('gid' => $v['subgid'])) ->save($upData);
                            //echo M("group") -> getLastSql();exit();
                            if ($res == false){
                                $arr = array('status'=> 0,'msg'=>"子群组类型修改失败！",'subg'=>$subGroupList);
                                $this->ajaxReturn($arr);
                                exit();
                            }
                        }
                    }
                    $delete = M('subgroup')->where(array('gid' => $gid))->delete();  //先将原来的分组gID删除
                    //echo M("subgroup") -> getLastSql(); exit();
                    if ($delete !== false){
                        for ($i = 0; $i < count($lstUser); $i++){
                            $data['gid'] = $gid;
                            $data['subgid'] = $lstUser[$i];
                            $result = M("subgroup")->add($data);
                        }
    
                        if ($result !== false){
                            for ($i = 0; $i < count($lstUser); $i++){
                                $upd['type'] = 1;
                                $result1 = M("group") -> where(array('gid'=>$lstUser[$i])) ->save($upd); //将子群组的type标记为1
                                //echo M("subgroup") -> getLastSql();
                            }
                        }
                        if ($result !== false && $result1 !== false){
                            $updata['type'] = 2;
                            $res = M('group') -> where(array('gid'=>$gid)) -> save($updata);  //将包含子组的群组设置为父组
                            if ($res !== false){
                                UDP_MakePack(9, $gid, 0, 0);
                                //当前子群组列表
                                $subGroupList = M('subgroup sg')
                                -> join('inner join tab_group g on g.gid = sg.subgid')
                                -> where(array('sg.gid' => $gid))
                                -> field('sg.subgid,gname')
                                -> select();
    
                                $arr = array('status'=> 1,'msg'=>"修改子群组成功！",'subg'=>$subGroupList);
                                $this->ajaxReturn($arr);
                            }else {
                                $this->ajaxReturn(array('status'=> 0,"msg"=>"修改子群组失败！"));
                                exit();
                            }
                        }
    
    
                    }else {
                        $this->ajaxReturn(array('status'=> 0,"msg"=>"修改子群组失败！"));
                        exit();
                    }
    
                }else {//添加新子群组
                    if (!empty($gid) && count($lstUser) > 0){
                        //dump($lstUser);exit();
                        for ($i = 0; $i < count($lstUser); $i++){
                            $data['gid'] = $gid;
                            $data['subgid'] = $lstUser[$i];
                            $result = M("subgroup")->add($data);
                        }
    
                        if ($result !== false){
                            for ($i = 0; $i < count($lstUser); $i++){
                                $upd['type'] = 1;
                                $result1 = M("group") -> where(array('gid'=>$lstUser[$i])) ->save($upd); //将子群组的type标记为1
                            }
                        }
                        if ($result !== false && $result1 !== false){
                            $updata['type'] = 2;
                            $res = M('group') -> where(array('gid'=>$gid)) -> save($updata);  //将包含子组的群组设置为父组
                            if ($res !== false){
                                UDP_MakePack(9, $gid, 0, 0);
                                //当前子群组列表
                                $subGroupList = M('subgroup sg')
                                -> join('inner join tab_group g on g.gid = sg.subgid')
                                -> where(array('sg.gid' => $gid))
                                -> field('sg.subgid,gname')
                                -> select();
    
                                $arr = array('status'=> 1,'msg'=>"添加子群组成功！",'subg'=>$subGroupList);
                                $this->ajaxReturn($arr);
                                exit();
                            }else {
                                $this->ajaxReturn(array('status'=> 0,"msg"=>"添加子群组失败！"));
                                exit();
                            }
                        }else {
                            $this->ajaxReturn(array('status'=> 0,"msg"=>"添加子群组失败！"));
                            exit();
                        }
                    }else {
                        $this->ajaxReturn(array('status'=> 0,"msg"=>"请选择子群组！"));
                        exit();
                    }
    
                }
            }
        }
        $this -> assign('dp',$dp);
        $this -> assign('opid',$opid);
        $this -> assign('cursubgidstr',$cursubgidstr);
        $this -> assign('gtype',$gtype);
        $this -> assign('curgData',$curgData);
        $this -> assign('parGroup',$parGroup);
        $this -> assign('groupData',$groupData);
        $this -> assign('gid',$gid);
        $this -> assign('total',$total);
        $this -> assign('subLogUser',$subLogUser);
        $this -> assign('iLevel',$iLevel);
        $this->display();
    }
    
    /**
     * 嵌套组定义 -- 查询已设置子群组信息
     */
    public function subGroupSelect(){
        $gid = I('post.gid'); //选中的gid
        $opid = I('post.opid');
        $map['submanid'] = $opid;
        $map['type'] = array('neq',2);
        //选中的群组类型
        $gtype = M('group') -> where(array('gid'=>$gid)) -> field('type') -> find();
        $gtype = $gtype['type'];
        //当前子群组列表
        $subGroupList = M('subgroup sg')
        -> join('inner join tab_group g on g.gid = sg.subgid')
        -> where(array('sg.gid' => $gid))
        -> field('sg.subgid,gname')
        -> select();
        //过滤与可选子群组冲突的群组
        $subGroupListData = array();
        foreach ($subGroupList as $k => $v){
            $subGroupListData[] = $v['subgid'];
        }
    
        //可选子群组
        $groupData = M('group') -> where($map) -> order('gid desc') -> field('gid,gname,type') -> select();
        // echo M("group") -> getLastSql();
        // dump($groupData);
        // exit;
        foreach ($groupData as $k => $v){
            if (in_array($gid, $v)){
                unset($groupData[$k]);
            }
            if (in_array($v['gid'], $subGroupListData)){
                unset($groupData[$k]);
            }
        }
        
        
        $return_arr = array('subg' => $subGroupList, 'gd' => $groupData,'gt'=> $gtype); // 返回查询结果
        $this->ajaxReturn($return_arr);
        exit();

    
    }
    
    
    /**
     * 添加子群组-右侧右边群组搜索
     */
    
    public function subGroupSearch(){
        //验证request数据
        $sear = I('sear');
        if (empty($sear) && $sear != 0)
        {
            $map['submanid'] = I('Opid');
            $map['type'] = array('neq',2);
            $list = M('group') -> where($map) -> order('gid desc') -> field('gid, gname,type') -> select();
            $this->ajaxReturn($list);
            //echo M("group") -> getLastSql();
            //echo json_encode($list);
            exit();
        }
        else
        {
            $sear = I('post.sear');
            $maper['gid'] = array('like',"%{$sear}%");
            $maper['gname'] = array('like',"%{$sear}%");
            $maper['_logic'] = 'or';
            $map['submanid'] = I('post.Opid');
            $map['type'] = array('neq',2);
            $map['_complex'] = $maper;
            $listData = M("group") -> where($map) -> order('gid desc') -> field('gid, gname,type') -> select();
            //echo M("group") -> getLastSql();
            //dump($listData);exit;
    
            $this->ajaxReturn($listData);
            exit();
    
        }
    }
    
    
    
    
    /**
     * 点击群组名称查看成员状态
     */
    public function uMembers(){
        $urlp = I("get.p"); 
        //dump($urlp);exit;
        $subLogUser = session("subLogUser");
        $iLevel = session("subLogLevel");
        $gid = I("get.gid");
        session("gid",$gid);
        $gid = session("gid");
        $glist = M('group') -> where(array('gid' => $gid))  -> find();
        $uids = M('user_group') -> where(array('gid' => $gid)) -> field('uid') ->order('uid desc') -> select();  //根据gid 查询出对应的组成员uid

        $data = array();
        foreach ($uids as $key => $v) {
            $uid = $v['uid'];            
            $row = M('user u') -> join("inner join tab_user_status us on u.uid = us.uid") -> where(array('u.uid' => $uid)) -> find();
            $account = $row['account'];  
            if(!empty($account)){  //防止user表不存在的账号输出到页面
                $data[] = $row;
            }                      
        }

        //数据分页
        $total = count($data);
        $prows = 13;
        $showpn = ceil($total/$prows);
        $Page = new \Think\Page($total,$prows);// 实例化分页类 传入总记录数和每页显示的记录数
        $lists = array_slice($data, $Page->firstRow,$Page->listRows);  //针对已取出数据分页
        pagelist($Page);  //调用分页公共函数   
        $show = $Page->show();// 分页显示输出
        //记住当前页，用于返回
        $gp = I('get.p');
        $p = isset($gp) ? intval($gp) : 1;
        $_SESSION['dp'] = $p;
        $dp = session("dp");


        $this -> assign('showpn',$showpn);
        $this -> assign('dp',$dp);
        $this -> assign('glist',$glist);
        $this -> assign('gid',$gid);
        $this -> assign('total',$total);
        $this -> assign('subLogUser',$subLogUser);
        $this -> assign('iLevel',$iLevel);
        $this -> assign('page',$show);
        $this -> assign('data',$lists);
        $this -> display();
    }
    
    /**
     * 群组编辑
     */
    public $setgid;
    public function group_edit(){
        $subLogUser = session("subLogUser");
        $iLevel = session("subLogLevel");
        $opid = session("subLogOpid");
        if (IS_GET) {
            $p = I('get.p'); 
            $gid = I('get.gid');
            session('pn',$p);
        }
               
        $where['gid'] = $gid;
        $where['submanid'] = session("subLogOpid");   
        $group = D('group');
        if (!empty($gid)) {
            $row = $group ->where($where) ->field('gid,gname,gnote') ->find();  //当前修改记录数据
            //dump($row);
            //echo $group->getLastSql();exit();
            if (empty($row)){
                $this -> showtips("没有找到该用户！");
                echo $this-> back_url;
                exit;
            }
        }
        
        //当前群组成员
        $gmembers = M('user u') ->where(array('ug.gid'=> I('get.gid'))) ->join('inner join tab_user_group ug on u.uid = ug.uid') ->field('u.uid, username, account') ->select();

        //当前管理员下的所有用户
        $usermber = M('user') ->where(array('submanid'=> session("subLogOpid"))) ->order('username desc') ->select();
        $dp = session('dp');

        if (IS_POST) {
            //dump($dp);exit();
            $p = session('pn');
            $postgid = I('post.gid');
            $map['gid'] = $postgid;
            $map['submanid'] = session("subLogOpid");
            $gData = $group ->where($map) ->select();       
            if (empty($gData)) {
                //$this->error('修改记录失败！');
                $this -> showtips("修改记录失败！");
                echo $this-> back_url;
                exit();
            }else{
                $gdata['gname'] = I('post.gname');
                $gdata['gnote'] = I('post.gnote');
                $sresult = $group ->where(array('gid'=> $postgid)) -> save($gdata);
                //echo $group->getLastSql(); //exit();
                //dump($sresult);
                if ($sresult !== false) {
                    $map1['gid'] = $postgid;
                    $result = M("user_group") -> where($map1) ->delete();
                    if ($result !== false) {
                        $lstUser = explode('|', I("post.selVal") );
                        //dump($lstUser);exit;
                        if (count($lstUser) < 2) { //将组成员清空时
                            $gid = I('post.gid');
                            UDP_MakePack(4, $gid, 0, 0);
                            $this -> showtips("修改成功！");
                            $this->setgid = $gid; 
                            header("refresh:0;url=$this->gp_li_url?p=$p");
                            //header("refresh:0;url=group_edit?gid=$gid");
                            exit();
                        }else{
                            
                            for ($i = 0; $i < count($lstUser) - 1; $i++)
                            {
                                $data['uid'] = $lstUser[$i];
                                $data['gid'] = $postgid;
                                $add = M('user_group') -> add($data);
                            }
                            if ($add !== false) {
                                $gid = I('post.gid');
                                UDP_MakePack(4, $gid, 0, 0);
                                //$this->success('修改成功！',U("Admin/Index/group_list/p/$dp"));  
                                $this->setgid = $gid;                              
                                $this -> showtips("修改成功！");
                                header("refresh:0;url=$this->gp_li_url?p=$p");
                                //header("refresh:0;url={$this->gp_li_url}");
                                exit();
                            }else{
                                $this -> showtips("修改失败！");
                                echo $this-> back_url;
                                exit();
                            }
                        }                        
                    }
                }else{
                    $this -> showtips("修改失败！");
                    echo $this-> back_url;
                    exit();
                }
            }
        }

        $this -> assign('dp',$dp);
        $this -> assign('opid',$opid);
        $this -> assign('subLogUser',$subLogUser);
        $this->assign('usermber',$usermber);
        $this->assign('gmembers',$gmembers);
        $this->assign('row',$row);
        $this->assign('iLevel',$iLevel);
        $this -> display();
    }



    /**
     * 群组添加
     */
     public function group_add(){
        $subLogUser = session("subLogUser");
        $iLevel = $_SESSION["subLogLevel"];
        $opid = $_SESSION["subLogOpid"];
        $user = M('user');
        //右侧成员选择信息
        $userData = $user -> where(array('submanid' => $opid)) -> order('uid desc') -> field('uid, username, account') -> select();
        if (IS_POST) {
            $manid = session("manid");
            $group = M('group');
            $date = date("Y-m-d H:i:s");
            $addData['gname'] = I('post.gname');
            $addData['gnote'] = I('post.gnote');
            $addData['createtime'] = $date;
            $addData['enable'] = '1';
            $addData['manid'] = $manid;
            $addData['submanid'] = $opid;
            
            $result = $group -> add($addData);
            if($result !== false){
                //$gid = $group -> max('gid');
                $gid = $result;
                $lstUser = explode('|',I('post.selVal'));
                $result = array();
                for ($i = 0; $i < count($lstUser) - 1; $i++) { 
                    $addData1['uid'] = $lstUser[$i];
                    $addData1['gid'] = $gid;
                    $res = M('user_group') -> add($addData1);
                    $result[] = $res;       
                }
                if (count($result) == (count($lstUser) - 1) ){
                    UDP_MakePack(3, $gid, 0, 0);
                    $this -> showtips("添加群组成功！");
                    header("refresh:0;url={$this->gp_li_url}");
                    exit();
                }else{
                    $this -> showtips("添加群组失败！");
                    echo $this-> back_url;
                    exit();
                }
            }
        }
        
        $this -> assign('opid',$opid);
        $this -> assign('subLogUser',$subLogUser);
        $this->assign('userData',$userData);
        $this->assign('iLevel',$iLevel);
        $this -> display();
    }



    /**
     * 群组搜索查看成员状态
     */
    public function searGroup(){
        $LogUser = session("LogUser");
        $iLevel = session("LogLevel");
        $where = array();
        if($iLevel >= 2)
        {
            $SubOpid = I("get.SubOpid");   
            if(empty($SubOpid)){
                $opid = $_SESSION["SubOpid"];
            }else{              
                $opid = I("get.SubOpid");   
                $_SESSION["SubOpid"] = $opid;
                $maxuserData = M('admain') -> where(array('opid' => $opid)) -> field('maxuser') -> find();
                $maxuser = $maxuserData['maxuser'];      
            }
        }else{
            $opid = $_SESSION["LogOpid"];
            $maxuser = $_SESSION["MaxUser"];
        }

        //计算额度
        $usednum = M('user') -> where(array('manid' => $opid)) -> count();

        if($maxuser >= $usednum){
            $validuser = $maxuser - $usednum;
        }else{
            $validuser = 0;
        }
        $_SESSION["ValidUser"] = $validuser;

       

        //搜索
        $gname = I('get.gname');
        if (!empty($gname)) {
            $gidData = M('group') -> where(array('gname' => $gname)) -> field('gid') -> find();
            $gid = $gidData['gid'];
            $where['ug.gid'] = $gid ; //array('like', "%{$gid}%");
            
        }
        $searchc = I("get.searchc");       
        if(!empty($searchc))
        {
            $where['ug.gid'] =  $searchc; //array('like', "%{$searchc}%");

        }

        //数据分页
        $where['a.manid'] = $opid;

        $total = M('user_group ug') -> join("LEFT JOIN tab_user a  ON a.uid = ug.uid LEFT JOIN tab_group c ON c.gid = ug.gid LEFT JOIN tab_user_status AS b ON a.uid = b.uid" ) -> where($where) -> count();// 查询当前登录管理员用户的总记录数 
        
        $Page = new \Think\Page($total,16);// 实例化分页类 传入总记录数和每页显示的记录数 
        pagelist($Page);  //调用分页公共函数   
        $show = $Page->show();// 分页显示输出
        //SELECT * FROM tab_user_group ug LEFT JOIN tab_user u  ON u.uid = ug.uid LEFT JOIN tab_group g ON g.gid = ug.gid WHERE ug.gid = '52'  LIMIT 0,12

        $data = M('user_group ug') -> join("LEFT JOIN tab_user a  ON a.uid = ug.uid LEFT JOIN tab_group c ON c.gid = ug.gid LEFT JOIN tab_user_status AS b ON a.uid = b.uid" ) -> where($where) -> field('a.account, a.uid, a.password, a.username, a.heartbeat, a.gpscycle, a.level, a.curgid, a.createtime, a.enable, a.manid, a.validtime, c.gname,b.online,b.note,c.gid,c.gname') -> order('uid asc') -> limit($Page->firstRow.','.$Page->listRows)->select();
        //echo M('user_group ug') -> getLastSql();
        
        
        //记住当前页，用于返回
        // $gp = I('get.p');
        // $p = isset($gp) ? intval($gp) : 1;
        // $_SESSION['dp'] = $p;
        $dp = session("dp");


        $this -> assign('quota',$quota);
        $this -> assign('quotaed',$quotaed);
        $this -> assign('vquota',$vquota);
        $this -> assign('serType',$serType);
        $this -> assign('maxuser',$maxuser);
        $this -> assign('usednum',$usednum);
        $this -> assign('validuser',$validuser);
        $this -> assign('dp',$dp);
        $this -> assign('searchc',$searchc);
        $this -> assign('gname',$gname);
        $this -> assign('total',$total);
        $this -> assign('LogUser',$LogUser);
        $this -> assign('iLevel',$iLevel);
        $this -> assign('page',$show);
        $this -> assign('data',$data);
        $this -> display();
    }

    /**
     * 群组删除
     */
    public function group_delete(){
        $dp = session('dp');
        $opid = session("subLogOpid");
        $gmembers = M('user u') ->where(array('ug.gid'=> I('get.gid'))) ->join('inner join tab_user_group ug on u.uid = ug.uid') ->count();
        if ($gmembers >0) {
            $this -> showtips("该群组还有成员，不允许删除！");
            echo $this-> back_url;
            exit();
        }
        $gid = I('get.gid');
        $p= I('get.p');
        $map['gid'] = I('get.gid');
        $map['submanid'] = $opid;
        $result1 = M('group') ->where($map) -> delete();
        if ($result1 !== false) {
            $ugData = M('user_group') ->where(array('gid' => $gid)) -> select();
            if (!empty($ugData)) {
                $result2 = M('user_group') ->where(array('gid' => $gid)) -> delete();
                if ($result2 !== false) {
                    UDP_MakePack(5, $gid, 0, 0);
                    $this -> showtips("删除成功！");
                    header("refresh:0;url=$this->gp_li_urlp={$p}");
                    exit();
                }else{
                    $this -> showtips("删除群组失败！");
                    echo $this-> back_url;
                    exit();
                }
            }else{
                UDP_MakePack(5, $gid, 0, 0);
                $this -> showtips("删除成功！");
                header("refresh:0;url=$this->gp_li_url?p={$p}");
                exit();
            }
        }else{
            $this -> showtips("删除群组失败！");
            echo $this-> back_url;
            exit();
        }
    }

     

    /**
     * 添加/修改设备-右侧群组名称搜索
     */
    
    public function accSearch(){
        //验证request数据
        $name = I('Name');
        if (empty($name) && $name != 0)
        {
            $map['submanid'] = I('gopid');
            $listData = M("user") -> where($map) -> order('uid desc') -> field('uid, username, account') -> select();
            $this->ajaxReturn($list);
        }
        else
        {
            $Account = I('post.Account');
            $maper['account'] = array('like',"%{$Account}%");
            $maper['username'] = array('like',"%{$Account}%");
            $maper['_logic'] = 'or';
            $map['submanid'] = I('post.Opid');
            $map['_complex'] = $maper;
            $listData = M("user") -> where($map) -> order('uid desc') -> field('uid, username, account') -> select();
            //echo M("user") -> getLastSql(); 
            $this->ajaxReturn($listData);
            //echo json_encode($listData);

        }
    }

    /**
     * 添加群组--通过输入群组ID搜索群成员全组成员
     */
    
    public function gidmemberSearch(){
        $gid = I("gid");
        $map['submanid'] = I('post.opid');
        if (empty($gid)) {           
            $listData = M("user") -> where($map) -> order('uid desc') -> field('uid, username, account') -> select();
            $this->ajaxReturn($listData);
        }else{            
            $uids = M('user_group') -> where(array('gid' => $gid)) -> field('uid') -> select();  //根据gid 查询出对应的组成员uid
            $data = array();
            foreach ($uids as $key => $v) {
                $uid = $v['uid'];
                $map['uid'] = $uid;
                $row = M('user') -> where($map) -> field('uid, username, account') -> find();
                $data[] = $row;
            }
            
            $this->ajaxReturn($data);
        }
    }





}