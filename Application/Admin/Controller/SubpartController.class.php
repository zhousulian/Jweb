<?php
namespace Admin\Controller;
use Think\Controller;
use \Org\Net\Http;
header("Content-type: text/html; charset=utf-8");
class SubpartController extends SubBaseController {
     
    /**
     * 下载设备添加excel模板
     */
    public function dloadUserTpl(){
        //$res = "addDeviceTpl.xls";
        $url = './Public/download/addDeviceTpl.xls'; //下载文件的路径
        $dload = Http::download($url);  //导入下载类后实例化下载方法
        if (!$info = $dload->download()) {
            $this->error($dload->getError());
            exit();
        }
    }


    /**
     * excel导入添加设备
     */
    
    public function  import_excel(){
        $dp = session("dp");
        $subLogUser = session("subLogUser");
        $iLevel = session("subLogLevel");
        $opid = session("subLogOpid");
        $manid = session("manid");
        //dump($_SESSION);exit();
        if (!empty($_FILES)) {
            $config = array(
                'exts' => array('xlsx','xls'),  //文件支持类型
                'maxSize' => 3145728000,
                'rootPath' =>"./Public/",
                'savePath' => 'Uploads/',   //文件保存路径
                'subName' => array('date','Ymd'), //目录名称
            );
            $upload = new \Think\Upload($config);   //先将文件上传
            if (!$info = $upload->upload()) {
                $this->error($upload->getError());
            }
            vendor("PHPExcel.PHPExcel");
            $file_name = $upload->rootPath.$info['file_stu']['savepath'].$info['file_stu']['savename'];  //读取文件名
            $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));//判断导入表格后缀格式
            if ($extension == 'xlsx') {
                $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
                $objPHPExcel = $objReader->load($file_name, $encode = 'utf-8');  //设置字符
            } else if ($extension == 'xls'){
                $objReader = \PHPExcel_IOFactory::createReader('Excel5');
                $objPHPExcel = $objReader->load($file_name, $encode = 'utf-8');
            }
            $sheet = $objPHPExcel->getSheet(0);
            $highestRow = $sheet->getHighestRow();//取得总行数
            $highestColumn = $sheet->getHighestColumn(); //取得总列数
    
            $adRow = M('admain') -> where(array('opid' => $manid)) -> field('maxuser,level') -> find();
            //最大
            if (!empty($adRow))
            {
                $maxuser = $adRow['maxuser'];
            }
            //已分配
            $usednum = M('user') -> where(array('manid' => $manid)) -> field('maxuser') -> count();
            $vuser = $maxuser - $usednum;
    
            //先获取excel的所有内容
            $dataArr = array();  //提交的所有数据
            $accArr = array();  //提交的所有账号

            for ($i = 2; $i <= $highestRow; $i++) {  //从第二行开始  大写字母 ABCD~ 为excel表头对应的列
                $rowData = array();
                $username = (string)$objPHPExcel->getActiveSheet()->getCell("A". $i)->getValue();                             
                $addAcc = (string)$objPHPExcel->getActiveSheet()->getCell("B" . $i)->getValue();    //账号 
                $password = (string)$objPHPExcel->getActiveSheet()->getCell("C" .$i)->getValue();               
                $curgid = (string)$objPHPExcel->getActiveSheet()->getCell("D" .$i)->getValue();  //默认群组ID               
                $group_ids = (string)$objPHPExcel->getActiveSheet()->getCell("E" .$i)->getValue();  //成员所在群组ID
                $user_func = (string)$objPHPExcel->getActiveSheet()->getCell("F" .$i)->getValue();  //用户功能 多个用|线隔开                
                $note = (string)$objPHPExcel->getActiveSheet()->getCell("G" .$i)->getValue();  //备注
                if ($addAcc != '') {
                   $accArr[] = $addAcc;
                }
                //防止账号重复
                $accN = array_count_values($accArr); //计算重复账号的个数
                foreach ($accN as $k => $v) {
                    if ($v > 1) {
                        $this -> showtips($k.'账号填写重复！');
                        echo $this-> back_url;
                        exit;
                    }
                }
                //防止提交空账号
                if ($addAcc != '') {
                    $rowData['username'] = $username; 
                    $rowData['account'] = $addAcc;
                    $rowData['password'] = $password;
                    $rowData['curgid'] = $curgid;
                    $rowData['group_ids'] = $group_ids;
                    $rowData['note'] = $note;
                    if ($user_func != ''){
                        $funcCodeRow = explode('|', $user_func);
                    }else{
                        $funcCodeRow = "";
                    }
                    $rowData['funccode'] = $funcCodeRow;
                    $dataArr[] = $rowData;                   
                    
                }

            }
            //dump($dataArr); exit;

            $n = count($dataArr);
            if ($vuser < $n)
            {
                unlink($file_name);
                $this -> showtips("当前额度不够添加账号，请先申请额度！");
                echo $this-> back_url;
                exit();
            }

            //先检测全部数据
            foreach ($dataArr as $key => $v) {
                $addAcc = $v['account'];
                $curgid = $v['curgid'];
                $group_ids = $v['group_ids'];                
                $accountData = M("user") ->where(array('account' => $addAcc)) -> count();
                $accountData = intval($accountData);
                $map1['submanid'] = $opid;
                $map1['gid'] = $curgid;
                $cgid = M("group") ->where($map1) -> count();

                $cgid = intval($cgid);
                $pattern = '/^[A-Za-z0-9]+$/';  //只能为数字和字母
                $acclen = strlen($addAcc);
                // echo M("user")  ->getLastSql();
                if ($accountData > 0) {
                    unlink($file_name);
                    $this -> showtips("帐号'".$addAcc."'已经存在,批量导入失败！");
                    echo $this-> back_url;
                    exit();
                }
                if (!preg_match($pattern, $addAcc) || $acclen >= 11 ) {
                    unlink($file_name);
                    $this -> showtips("帐号'".$addAcc."'不能有特殊字符或中文，长度不能大于11个字符！");
                    echo $this-> back_url;
                    exit();
                }
                if (!is_numeric($curgid) || $cgid < 1) {
                    unlink($file_name);
                    $this -> showtips("默认群组ID'".$curgid."'不存在,群组ID只能填写数字！");
                    echo $this-> back_url;
                    exit();
                } 
                //检测密码
                $res = icheckedpwd($pwd);
                if ($res == false) {
                    unlink($file_name);
                    $this -> showtips("密码'".$pwd."'的格式不正确！");
                    echo $this-> back_url;
                    exit();
                }

                $lstGroup = explode('|', $group_ids); //将群组分割成数组
                $count = count($lstGroup);
                for ($n = 0; $n < $count; $n++)
                {
                    $getgid = $lstGroup[$n];
                    $map['submanid'] = $opid;
                    $map['gid'] = $getgid;
                    $gruopid = M("group") ->where($map) -> count();
                    $gruopid = intval($gruopid);
                    if ($gruopid < 1) {
                        unlink($file_name);
                        $this -> showtips("所属群组ID'".$getgid."'不存在！,群组ID只能填写数字！");
                        echo $this-> back_url;
                        exit();
                    }
                }
    
                if (!in_array($curgid, $lstGroup)){
                    unlink($file_name);
                    $this -> showtips('默认群组ID必须为所属群组中的其中一个!');
                    echo $this-> back_url;
                    exit();
                }
                if ($v['funccode'] !== '') {  
                    $funArr = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14);
                    foreach ($v['funccode'] as $key => $val) {
                        if (!in_array($val,$funArr)) {
                            unlink($file_name);
                            $this -> showtips("用户功能'".$val."'填写有误！");
                            echo $this-> back_url;
                            exit();
                        }
                    }
                }
                        
            }

           // dump($dataArr);
            
            //写入数据库
            foreach ($dataArr as $key => $v) {                 
                $username = $v['username'];
                $password = $v['password'];
                $addAcc = $v['account'];    //账号
                $curgid = $v['curgid'];  //默认群组ID
                $group_ids = $v['group_ids'];  //成员所在群组ID 多个用|线隔开
                $note = $v['note'];  //备注
                $date = date("Y-m-d H:i:s");
                //收集用户功能
                $iFuncCode = 0;
                if ($v['funccode'] != ''){
                                        
                    if (in_array('1',$v['funccode'])){
                        $iFuncCode = SetFuncCode(POC_FUNC_FRIEND, intval($iFuncCode));  //好友
                    }
                    if (in_array('2',$v['funccode'])){
                        $iFuncCode = SetFuncCode(POC_FUNC_QUERY_LOC, intval($iFuncCode));  //查位                       
                    }
                    if (in_array('3',$v['funccode'])){
                        $iFuncCode = SetFuncCode(POC_FUNC_RECORD, intval($iFuncCode));  //记录
                    }
                    if (in_array('4',$v['funccode'])){
                        $iFuncCode = SetFuncCode(POC_FUNC_LISTEN, intval($iFuncCode)); //监听                        
                    }
                    if (in_array('5',$v['funccode'])){
                        $iFuncCode = SetFuncCode(POC_FUNC_RE_SHUT, intval($iFuncCode));  //遥毙
                    }
                    if (in_array('6',$v['funccode'])){
                        $iFuncCode = SetFuncCode(POC_FUNC_GROUP_CALL, intval($iFuncCode));  //群呼
                    }
                    if (in_array('7',$v['funccode'])){
                        $iFuncCode = SetFuncCode(POC_FUNC_NO_CALL, intval($iFuncCode));  //禁呼
                    }
                    if (in_array('8',$v['funccode'])){
                        $iFuncCode = SetFuncCode(POC_FUNC_INVATE, intval($iFuncCode)); //单呼
                    }
                    if (in_array('9',$v['funccode'])){
                        $iFuncCode = SetFuncCode(POC_FUNC_CHG_GROUP, intval($iFuncCode)); //换组
                    }
                    if (in_array('10',$v['funccode'])){
                        $iFuncCode = SetFuncCode(POC_FUNC_LOCATION, intval($iFuncCode)); //定位
                    }
                    if (in_array('11',$v['funccode'])){
                        $iFuncCode = SetFuncCode(POC_FUNC_AUD_RECORD, intval($iFuncCode));  //录音
                    }
                    if (in_array('12',$v['funccode'])){
                        $iFuncCode = SetFuncCode(POC_FUNC_DIS_GROUP, intval($iFuncCode)); //显组
                    }
                    if (in_array('13',$v['funccode'])){
                        $iFuncCode = SetFuncCode(POC_FUNC_LAST_GROUP, intval($iFuncCode));  //最后组
                    }
                    if (in_array('14',$v['funccode'])){
                        $iFuncCode = SetFuncCode(POC_FUNC_NOT_INVATE, intval($iFuncCode)); //单呼勿扰
                    } 
                
                }else{
                    //添加默认用户功能
                    $iFuncCode = SetFuncCode(POC_FUNC_INVATE, intval($iFuncCode)); //单呼
                    $iFuncCode = SetFuncCode(POC_FUNC_DIS_GROUP, intval($iFuncCode)); //显组
                    $iFuncCode = SetFuncCode(POC_FUNC_LAST_GROUP, intval($iFuncCode));  //最后组
                    $iFuncCode = SetFuncCode(POC_FUNC_NOT_INVATE, intval($iFuncCode)); //单呼勿扰
                }
                //dump($iFuncCode);
                $data['account'] = $addAcc;
                $data['password'] = $password;
                $data['username'] = $username;
                $data['level'] = 1;
                $data['heartbeat'] = 5;
                $data['gpscycle'] = 30;
                $data['curgid'] = $curgid;
                $data['createtime'] = $date;
                $data['enable'] = 1;
                $data['validtime'] = date('Y-m-d H:i:s', strtotime("+ 1 year"));  //有效期 默认从添加日起一年
                $data['manid'] = session("manid");
                $data['submanid'] = $opid;
                $data['funccode'] = $iFuncCode;
                
                $res = M('user')->add($data);

                if ($res !== false) {    
                    $lstGroup = explode('|', $group_ids);
                    $uid = $res;
                    $count = count($lstGroup);
                    for ($k = 0; $k < $count; $k++)
                    {
                        $adata['uid'] = $uid;
                        $adata['gid'] = $lstGroup[$k];
                        $result2 = M('user_group') ->add($adata);
                    }
    
                    if ($result2 == false) {
                        unlink($file_name);
                        $this -> showtips('添加相应群组失败！');
                        echo $this-> back_url;
                        exit();
                    }
                    $addsta['uid'] = $uid;
                    $addsta['online'] = '0';
                    $addsta['note'] = $note;
                    $result3 = M('user_status') -> add($addsta);
                    if ($result3 == false) {
                        unlink($file_name);
                        $this -> showtips('添加设备状态失败！');
                        echo $this-> back_url;
                        exit();
                    }
                    UDP_MakePack(0, $uid, 1, 0);
                }else{
                    $this -> showtips('导入失败！');
                    echo $this-> back_url;
                    exit();
                }
                //dump($data);
            } 
                //exit;

            if ($res !== false && $result2 !== false && $result3 !== false) {
                unlink($file_name);  //将上传的文件删除
                $this -> showtips("导入成功！");
                header("refresh:0;url={$this->de_li_url}"); //跳转至指定页面
                exit();
            }
        
        }

        $this -> assign('dp',$dp);
        $this -> assign('opid',$opid);
        $this -> assign('subLogUser',$subLogUser);
        $this -> assign('iLevel',$iLevel);
        $this->display();
    }
    
     /**
     * 设备列表
     */
    public function device_list(){

        $subLogUser = session("subLogUser");
        $iLevel = session("subLogLevel");
        $opid = session("subLogOpid");
        $getup = I("get.p");
        $where = array();
        //$opid = $subLogOpid;
        //搜索
        $guid = I("get.uid");
        $searchc = I("get.searchc");
        
        if(!empty($searchc))
        {
            $map['a.uid'] = array('like', "%{$searchc}%");
            $map['account'] = array('like', "%{$searchc}%");
            $map['username'] = array('like', "%{$searchc}%");
            $map['_logic'] = 'or';
            $where['_complex'] = $map;
            $this -> assign('searchc',$searchc);
        }
       
        //数据分页
        $where['a.submanid'] = $opid;

        $total = M('user a') -> join('LEFT JOIN tab_user_status AS b ON a.uid = b.uid left join tab_group c on a.curgid = c.gid ') -> where($where) -> count();// 查询当前登录管理员用户的总记录数 
        //echo M('user') -> getLastSql();
        $prows = 13;
        $showpn = ceil($total/$prows);  //方便页面输入框跳转判断
        $Page = new \Think\Page($total,$prows);// 实例化分页类 传入总记录数和每页显示的记录数 
        pagelist($Page);  //调用分页公共函数   
        $show = $Page->show();// 分页显示输出
        $data = M('user a') 
        -> join('LEFT JOIN tab_user_status AS b ON a.uid = b.uid left join tab_group c on a.curgid = c.gid ' ) 
        -> where($where) 
        -> field('a.*, c.gname,b.online,b.note,c.gname') 
        -> order('uid desc') 
        -> limit($Page->firstRow.','.$Page->listRows)
        -> select();

        //记住当前页，用于返回
        $gp = I('get.p');
        $p = isset($gp) ? intval($gp) : 1;
        session("dp",$p);
        $dp = session("dp");


        $this -> assign('getup',$getup);
        $this -> assign('subadData',$subadData);
        $this -> assign('showpn',$showpn);
        $this -> assign('opid',$opid);
        $this -> assign('quota',$quota);
        $this -> assign('quotaed',$quotaed);
        $this -> assign('vquota',$vquota);
        $this -> assign('serType',$serType);
        $this -> assign('maxuser',$maxuser);
        $this -> assign('usednum',$usednum);
        $this -> assign('validuser',$validuser);
        $this -> assign('searchc',$searchc);
        $this -> assign('total',$total);
        $this -> assign('subLogUser',$subLogUser);
        $this -> assign('iLevel',$iLevel);
        $this -> assign('subiLevel',$subiLevel);
        $this -> assign('page',$show);
        $this -> assign('data',$data);
        $this -> display();
    }

    /**
     * 修改设备
     */

    public function device_edit(){
        
        $subLogUser = session("subLogUser");
        $iLevel = session("subLogLevel");
        $opid = $_SESSION["subLogOpid"];
        $gsetdate = I("get.setdate");
        
        if(IS_POST){
            $uid = I('post.uid');
            $gp = I("post.gp");
        }
        if (IS_GET) {
            $uid = I('get.uid');
            $dp = I('get.p');
        }       


        $map['submanid'] = $opid;
        $map['uid'] = $uid;
        $upList = M('user') -> where($map) -> find();  //当前UID记录数据
        //echo M('user') -> getLastSql();
        if (empty($upList)) {
            $this -> showtips("没有找到该用户！");
            echo $this-> back_url;
            exit();
        }

        $note = M('user_status') -> where(array('uid' => I('get.uid'))) -> field('note') -> find();
        $gcurList = M('group g') -> join('inner join tab_user_group ug on g.gid = ug.gid') -> where(array('ug.uid' => I('get.uid'))) -> field('g.gid,gname') -> select();  //当前默认群组名称

        $glist = M('group') -> where(array('submanid' => $opid)) -> order('gid desc') -> select();  //右侧可选群组
        //echo M('group') -> getLastSql();
    
        
        if(IS_POST)
            {   
                //dump(I("post."));exit;
                $iFuncCode = 0;
                $friend = I("post.friend");
                $location = I("post.location");
                $record = I("post.record");
                $no_call = I("post.no_call");
                $search = I("post.search"); 
                $monitoring = I("post.monitoring");
                $closed = I("post.closed");
                $g_call = I("post.g_call");
                $single_call = I("post.single_call");
                $change_g = I("post.change_g");
                $s_recording = I("post.s_recording");
                $display_g= I("post.display_g");
                $final_g = I("post.final_g");
                $dnd = I("post.dnd");
                $urlp = I("post.urlp");
                $p = I("post.p");
                if (empty($urlp)) {
                    $urlp = 1;
                }
                if (empty($p)) {
                    $p = 1;
                }
                
                //将所有打钩的复选框的功能拼接成一个数值存到一个字段
                if (!empty($friend)){
                    $iFuncCode = SetFuncCode(POC_FUNC_FRIEND, intval($iFuncCode));  //好友
                }
                if (!empty($location)){
                    $iFuncCode = SetFuncCode(POC_FUNC_LOCATION, intval($iFuncCode)); //定位
                }
                if (!empty($record)){
                    $iFuncCode = SetFuncCode(POC_FUNC_RECORD, intval($iFuncCode));  //记录
                }
                if (!empty($no_call)){
                    $iFuncCode = SetFuncCode(POC_FUNC_NO_CALL, intval($iFuncCode));  //禁呼
                }
                if (!empty($search)){
                    $iFuncCode = SetFuncCode(POC_FUNC_QUERY_LOC, intval($iFuncCode));  //查位
                }
                if (!empty($monitoring)){
                    $iFuncCode = SetFuncCode(POC_FUNC_LISTEN, intval($iFuncCode)); //监听
                }
                if (!empty($closed)){
                    $iFuncCode = SetFuncCode(POC_FUNC_RE_SHUT, intval($iFuncCode));  //遥闭
                }
                if (!empty($g_call)){
                    $iFuncCode = SetFuncCode(POC_FUNC_GROUP_CALL, intval($iFuncCode));  //群呼
                }
                if (!empty($single_call)){
                    $iFuncCode = SetFuncCode(POC_FUNC_INVATE, intval($iFuncCode)); //单呼
                }
                if (!empty($change_g)){
                    $iFuncCode = SetFuncCode(POC_FUNC_CHG_GROUP, intval($iFuncCode)); //换组
                }
                if (!empty($s_recording)){
                    $iFuncCode = SetFuncCode(POC_FUNC_AUD_RECORD, intval($iFuncCode));  //录音
                }
                if (!empty($display_g)){
                    $iFuncCode = SetFuncCode(POC_FUNC_DIS_GROUP, intval($iFuncCode)); //显组
                }
                if (!empty($final_g)){
                    $iFuncCode = SetFuncCode(POC_FUNC_LAST_GROUP, intval($iFuncCode));  //最后组
                }
                if (!empty($dnd)){
                    $iFuncCode = SetFuncCode(POC_FUNC_NOT_INVATE, intval($iFuncCode)); //遥闭
                }
                
                
                $map['uid'] = I('post.uid');
                $map['submanid'] = $_SESSION["subLogOpid"];
                $addAcc = I('post.account');
                $arr = M('user') -> where($map) -> select();
                if (empty($arr)) {
                    $this -> showtips("修改失败！");
                    echo $this-> back_url;
                    exit();
                }else{
                    $data = M('user') -> where(array('account' => $addAcc)) -> select(); //查询账号是否重复             
                    if (!empty($data)) {
                        foreach ($data as $key => $v) {
                            $uid = $v['uid'];
                        }
                        if($uid != I('post.uid')){
                            $this -> showtips("帐号已存在！");
                            echo $this-> back_url;
                            exit();
                        }
                    }
                    
                    $pattern = '/^[A-Za-z0-9]+$/';  //只能为数字和字母
                    $acclen = strlen($addAcc);                    
                    if (!preg_match($pattern, $addAcc) || $acclen >= 11 ) {
                        $this -> showtips("帐号".$addAcc."不能有特殊字符或中文，长度不能大于11个字符！");
                        echo $this-> back_url;
                        exit();
                    }
                    
                    $where['uid'] = I('post.uid');
                    $udata['username'] = I('post.username');
                    $udata['password'] = I('post.password');
                    $udata['account'] = I('post.account');
                    $udata['gpscycle'] = I('post.gpscycle');
                    $udata['heartbeat'] = I('post.heartbeat');
                    $udata['validtime'] = I('post.validtime');
                    $udata['curgid'] = I('post.curgid');
                    $udata['level'] = I('post.level');
                    $udata['funccode'] = $iFuncCode;
                    $save = M('user')->where($where)->save($udata);
                    if ($save  !== fase) {   
                        $delete = M('user_group')->where($where)->delete();  //先将原来的分组UID删除，再将编辑的数据插入数据库
                        if ($delete !== false) {
                            $lstUser = explode('|', I("post.selVal"));
                            for ($i=0; $i < count($lstUser)-1; $i++) { 
                                $dataU['uid'] = $where['uid'];
                                $dataU['gid'] = $lstUser[$i];
                                $add = M('user_group')->add($dataU);
                            }
                            if ($add) { 
                                $us['num'] = I('post.num');
                                $us['note'] = I('post.note');
                                $saveus = M('user_status')->where(array('uid'=>I('post.uid')))->save($us);
                                if ($saveus !== false) {
                                    $uid = I('post.uid');
                                    $this->getuid = $uid;
                                    UDP_MakePack(1, $uid, 0, 0);
                                    
                                    $gu = $_SESSION['gu'];
                                    $this -> showtips("修改成功！");
                                    if(!empty($gp)) { 
                                        header("refresh:0;url=$this->gp_uMembers?p=$p&urlp=$urlp");
                                        //header("refresh:0;url=device_edit?uid=$uid&gid=$urlgid&p=$urlp");
                                    }else{ 
                                        header("refresh:0;url=device_list?p=$p");
                                    }
                                    exit();
                                }else{
                                    $this -> showtips("修改失败！");
                                    echo $this-> back_url;
                                    exit();
                                }
                                
                            }else{
                                $this -> showtips("修改失败！");
                                echo $this-> back_url;
                                exit();
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
        $this -> assign('iLevel',$iLevel);
        $this -> assign('gcurList',$gcurList);
        $this -> assign('glist',$glist);
        $this -> assign('setdate',$setdate);
        $this -> assign('upList',$upList);
        $this -> assign('note',$note);
        $this -> display();
    }

    /**
     * 修改设备密码
     */
    public  function devPass(){
        $opwd = I("post.opassword");
        $pwd = I("post.password");
        $repwd = I("post.repassword");
        $uid = I("post.uid");
        $opid = I("post.opid");
        $type = I("post.type"); 
        $where['uid'] = I("post.uid");
        $adInfo = M("admain") ->where(array("opid"=>$opid)) ->field("manid")-> find();
        if(!empty($adInfo)){
            $opmainid = $adInfo['manid'];
        }else{
            $msg = "4";
            $this->ajaxReturn($msg);
            exit();
        }
        $where['manid'] = $opmainid;
        //匹配旧密码是否正确
        if ($type == "1") { 
            //echo $type;
            if (!empty($opwd) && !empty($uid) && !empty($opmainid)) { 

                $Info = M("user") ->where($where) ->field("password")-> find();
                // echo M('user')->getLastSql();
                // dump($Info);exit;
                if (!empty($Info)) {
                    $opasswd = $Info['password'];
                    if ($opwd !== $opasswd) {
                        session("getPassKey","114");
                        $msg = "4";
                        $this->ajaxReturn($msg);
                        exit();
                    }else{
                        session("getPassKey","113");
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
        $getPassKey = session("getPassKey"); //原密码正确
        $recheckedkey = session("recheckedkey"); //两次输入一致
        $checkedkey = session("checkedkey"); //密码格式是否正确

        if ($type == "2") {
            if (!empty($getPassKey) && !empty($pwd) && !empty($repwd) && !empty($uid) && !empty($opid)) {
                if ($checkedkey !== "3") {
                    $msg = "4";
                    $this->ajaxReturn($msg);
                    exit();
                }else{
                    if ($getPassKey == "113" && $recheckedkey == "103") {  //判断原密码及两次输入密码是否一致
                        $res = M("user") ->where($where) -> save(array("password"=>$pwd));  //echo M('user')->getLastSql();exit();
                        if ($res !== false) {
                            $msg = "3";
                            $this->ajaxReturn($msg);
                            exit();
                        }else{
                            $msg = "41";  //修改失败
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
     * 检验设备密码格式
     */
    public  function pregPass(){
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
                session("checkedkey","44");
                $msg = "4";
                $this->ajaxReturn($msg);
                exit();
            }
            elseif(preg_match($patternLet,$pwd))   //如果只包含写一种不允许
            {
                session("checkedkey","44");
                $msg = "4";
                $this->ajaxReturn($msg);
                exit();
            }
            elseif(preg_match($patternN,$pwd))   //如果只包含数字一种不允许
            {
                session("checkedkey","44");
                $msg = "4";
                $this->ajaxReturn($msg);
                exit();
            }
            elseif(!preg_match($pattern,$pwd))    //至少要包含大小写字母/数字/_的其中2种才匹配通过
            {
                session("checkedkey","44");
                $msg = "4";
                $this->ajaxReturn($msg);
                exit();
            }else{
                session("DevPwd",$pwd);
                session("checkedkey","3");
                $msg = "3";
                $this->ajaxReturn($msg);
                exit();
            }
        }


        if (!empty($repwd)) {  //确认密码
            $pwd1 = session("DevPwd");
            if ($repwd !== $pwd1) {
                session("recheckedkey","104");  
                $msg = "4";
                $this->ajaxReturn($msg);
            }else{
                session("recheckedkey","103");
                $msg = "3";
                $this->ajaxReturn($msg);
            }
        }
        

    }

    /**
     * 修改二级管理
     */
    public function subadUpdate(){
        $data = I("post.");
        if (!empty($data)) {
            $map['manid'] = $data['opid'];
            $map['uid'] = $data['uid'];
            $udata['submanid'] = $data['subopid'];
            $res = M('user')->where($map)->save($udata);
            //echo M('user') -> getLastSql();
            if ($res !== false) {
                $this->ajaxReturn(3);
                exit();
            }else{
                $this->ajaxReturn(4);
                exit();
            }
        } 
    }   

    /** 设备添加
     */
    /**
    public function device_add(){
        $subLogUser = session("subLogUser");
        $iLevel = session("subLogLevel");
        $opid = session("subLogOpid");
    

    
        $manid = session("manid");
        //默认有效时间
        $validtime = date("Y-m-d H:i:s", strtotime("+1 year"));
        //右侧可选群组信息
        $groupData = M('group') -> where(array('submanid' => $opid)) -> order('gid DESC') -> field('gid,gname') -> select(); //

        $adRow = M('admain') -> where(array('opid' => $manid)) -> field('maxuser,level') -> find();
        //最大
        if (!empty($adRow))
        {
            $maxuser = $adRow['maxuser'];
        }            
        //已分配
        $usednum = M('user') -> where(array('manid' => $manid)) -> count();
        $vuser = $maxuser - $usednum;
        if (IS_POST) {    
            $iFuncCode = 0;
            $friend = I("post.friend");
            $location = I("post.location");
            $record = I("post.record");
            $no_call = I("post.no_call");
            $search = I("post.search");
            $monitoring = I("post.monitoring");
            $closed = I("post.closed");
            $g_call = I("post.g_call");
            $single_call = I("post.single_call");
            $change_g = I("post.change_g");
            $s_recording = I("post.s_recording");
            $display_g= I("post.display_g");
            $final_g = I("post.final_g");
            $dnd = I("post.dnd");
             
            
            //将所有打钩的复选框的功能拼接成一个数值存到一个字段
            if (!empty($friend)){
                $iFuncCode = SetFuncCode(POC_FUNC_FRIEND, intval($iFuncCode));  //好友
            }
            if (!empty($location)){
                $iFuncCode = SetFuncCode(POC_FUNC_LOCATION, intval($iFuncCode)); //定位
            }
            if (!empty($record)){
                $iFuncCode = SetFuncCode(POC_FUNC_RECORD, intval($iFuncCode));  //记录
            }
            if (!empty($no_call)){
                $iFuncCode = SetFuncCode(POC_FUNC_NO_CALL, intval($iFuncCode));  //禁呼
            }
            if (!empty($search)){
                $iFuncCode = SetFuncCode(POC_FUNC_QUERY_LOC, intval($iFuncCode));  //查位
            }
            if (!empty($monitoring)){
                $iFuncCode = SetFuncCode(POC_FUNC_LISTEN, intval($iFuncCode)); //监听
            }
            if (!empty($closed)){
                $iFuncCode = SetFuncCode(POC_FUNC_RE_SHUT, intval($iFuncCode));  //遥闭
            }
            if (!empty($g_call)){
                $iFuncCode = SetFuncCode(POC_FUNC_GROUP_CALL, intval($iFuncCode));  //群呼
            }
            if (!empty($single_call)){
                $iFuncCode = SetFuncCode(POC_FUNC_INVATE, intval($iFuncCode)); //单呼
            }
            if (!empty($change_g)){
                $iFuncCode = SetFuncCode(POC_FUNC_CHG_GROUP, intval($iFuncCode)); //换组
            }
            if (!empty($s_recording)){
                $iFuncCode = SetFuncCode(POC_FUNC_AUD_RECORD, intval($iFuncCode));  //录音
            }
            if (!empty($display_g)){
                $iFuncCode = SetFuncCode(POC_FUNC_DIS_GROUP, intval($iFuncCode)); //显组
            }
            if (!empty($final_g)){
                $iFuncCode = SetFuncCode(POC_FUNC_LAST_GROUP, intval($iFuncCode));  //最后组
            }
            if (!empty($dnd)){
                $iFuncCode = SetFuncCode(POC_FUNC_NOT_INVATE, intval($iFuncCode)); //遥闭
            }
            
            $addCnt = intval(I("post.num"));
            if ($vuser < $addCnt)
            {
                $this -> showtips("当前额度不够添加账号，请先申请额度！");
                echo $this-> back_url;
                exit();
            }else{
                //开始添加 + 验证
                $user = M('user');
                $addStart = I('post.start');
                $date = date("Y-m-d H:i:s");
                $validtime = I('post.validtime');
                if ($addCnt > 1) {  //批量添加账号
                    $isFinish = 1;
                    $MaxId = 0;
                    // 2016-3-11 将批量添加账号检测冲突放到循环内 
                    for ($i = 0; $i < $addCnt; $i++) { 
                        $addAcc = I('post.account');
                        $addAcc = $addAcc . ($i + $addStart);
                        $account = M("user") ->where(array('account' => $addAcc)) -> select();
                        //echo M('user')->getLastSql();
                        if (!empty($account)) {
                            $this -> showtips('帐号'.$addAcc.'已经存在,批量添加失败！');
                            echo $this-> back_url;
                            exit();
                        }
                        $pattern = '/^[A-Za-z0-9]+$/';  //只能为数字和字母
                        $acclen = strlen($addAcc);                    
                        if (!preg_match($pattern, $addAcc) || $acclen >= 11 ) {
                            $this -> showtips("帐号".$addAcc."不能有特殊字符或中文，长度不能大于11个字符！");
                            echo $this-> back_url;
                            exit();
                        }
                    }
                    //批量添加设备账号
                    for ($i = 0; $i < $addCnt; $i++) { 
                        $addAcc = I('post.account');
                        $addName = I('post.username');
                        $addAcc = $addAcc . ($i + $addStart);
                        $addName = $addName . ($i + $addStart);
                        $addData['uid'] = null;
                        $addData['account'] = $addAcc;
                        $addData['password'] = I("post.password");
                        $addData['username'] = $addName;
                        $addData['heartbeat'] = 5;
                        $addData['gpscycle'] = I('post.gpscycle');  
                        $addData['level'] = I('post.level');
                        $addData['curgid'] = I('post.curgid');
                        $addData['createtime'] = $date;
                        $addData['enable'] = 1;
                        $addData['manid'] = session("manid");
                        $addData['submanid'] = $opid;
                        $addData['validtime'] = $validtime;
                        $addData['funccode'] = $iFuncCode;
                        $result = M('user') -> add($addData);
                        //echo M('user')->getLastSql();
                        if ($result !== false) {
                            //$uid = M('user') -> max('uid');
                            $uid = $result;
                            if ($MaxId == 0)
                            {
                                $MaxId = $uid;  
                            }
                            $selVal = I("post.selVal");
                            $lstGroup = explode('|', $selVal);
                            for ($k = 0; $k < count($lstGroup) - 1; $k++)
                            {
                                $addGroup['uid'] = $uid;
                                $addGroup['gid'] = $lstGroup[$k];
                                $result2 = M('user_group') -> add($addGroup);
                                if ($result2 == false) {
                                    $this -> showtips('添加相应群组失败！');
                                    echo $this-> back_url;
                                    exit();
                                }                           
                            }
                            $addsta['uid'] = $uid;
                            $addsta['online'] = '0';
                            $addsta['note'] = I("post.note");
                            $result3 = M('user_status') -> add($addsta);
                            if ($result3 == false) {                                
                                $this -> showtips('添加设备失败！');
                                echo $this-> back_url;
                                exit();
                            }
                            UDP_MakePack(0, $uid, 1, 0);
                        }else{
                            $this -> showtips('添加设备失败！');
                            echo $this-> back_url;
                            exit();
                        }
                               
                    }
                    //如果设备添加成功
                    if ($result !== false && $result2 !== false && $result3 !== false) {
                        
                        $this -> showtips("添加设备成功！");
                        header("refresh:0;url={$this->de_li_url}");
                        exit();
                                                
                    }else{
                        $this -> showtips('添加设备失败！');
                        echo $this-> back_url;
                        exit();
                    }
                }
                else
                {  //添加个数为1时  
                    $addAcc = I('post.account');
                    $addName = I('post.username');
                    $addData['uid'] = null;
                    $addData['account'] = $addAcc;
                    $addData['password'] = I("post.password");
                    $addData['username'] = $addName;
                    $addData['heartbeat'] = 5;
                    $addData['gpscycle'] = I('post.gpscycle');
                    $addData['level'] = I('post.level');
                    $addData['curgid'] = I('post.curgid');
                    $addData['createtime'] = $date;
                    $addData['enable'] = 1;
                    $addData['manid'] = session("manid");
                    $addData['submanid'] = $opid;
                    $addData['validtime'] = $validtime;
                    $addData['funccode'] = $iFuncCode;
                    //dump($addData);exit();
                    $account = M("user") ->where(array('account' => $addAcc)) -> select();
                    //echo M('user')->getLastSql();
                    if (!empty($account)) {
                        $this -> showtips('帐号'.$addAcc.'已经存在,添加失败！');
                        echo $this-> back_url;
                        exit();
                    }
                    $pattern = '/^[A-Za-z0-9]+$/';  //只能为数字和字母
                    $acclen = strlen($addAcc);                    
                    if (!preg_match($pattern, $addAcc) || $acclen >= 11 ) {
                        $this -> showtips("帐号".$addAcc."不能有特殊字符或中文，长度不能大于11个字符！");
                        echo $this-> back_url;
                        exit();
                    }
                    $result = M('user') -> add($addData);
                    //echo M('user')->getLastSql();
                    if ($result !== false)
                    {
                        $uid = $result;
                        //echo M('user_status')->getLastSql(); 
                        if (!empty($uid))
                        {
                            $selVal = I("post.selVal");
                            $lstGroup = explode('|', $selVal);
                            for ($i = 0; $i < count($lstGroup) - 1; $i++)
                            {
                                $adata['uid'] = $uid;
                                $adata['gid'] = $lstGroup[$i];
                                M('user_group') ->add($adata);
                            }
                            $note = I("post.note");
                            $adata1['uid'] = $uid;
                            $adata1['online'] = 0;
                            $adata1['note'] = $note;

                            $rest = M('user_status') ->add($adata1);
                            //echo M('user_status')->getLastSql(); 
                            if ($rest) {
                                    UDP_MakePack(0, $uid, 1, 0);
                                    $this -> showtips("设备添加成功！");
                                    header("refresh:0;url={$this->de_li_url}");
                                    exit();
                                }
                                
                        }else{
                            
                            $this -> showtips("添加设备失败！");
                            echo $this-> back_url;
                            exit();
                        }
                    }
                }
            }
             
        }

        $this -> assign("vTime", $validtime);
        $this -> assign('opid',$opid);
        $this -> assign('subLogUser',$subLogUser);
        $this -> assign('iLevel',$iLevel);
        $this -> assign('vuser',$vuser);
        $this -> assign('validuser',$validuser);
        $this -> assign('groupData',$groupData);
        $this -> display();
    }  */

    /**
     * 编辑好友
     */
    public $getfduid;
    public function friend_edit(){
        $subLogUser = session("subLogUser");
        $iLevel = session("subLogLevel");
        $dp = session("dp");
        $opid = $_SESSION["subLogOpid"];
        
        /*if($iLevel >= 2){
            $opid = $_SESSION["SubOpid"];
        }else{
            $opid = $_SESSION["subLogOpid"];
        }*/
        $gsetdate = I("get.setdate");
        if($gsetdate != NULL){
            $setdate = I("get.setdate");
        }else{
            $setdate = 0;
        }

        if(IS_POST){
            $uid = I('post.uid');
        }
        if (IS_GET) {
            $uid = I('get.uid');
            $getup = I("get.p");
        }

        if (empty($uid)) {
           $uid = $this->getfduid;
        }

        $map['submanid'] = $opid;
        $map['uid'] = $uid;

        $upList = M('user') -> where($map) -> find();  //当前UID记录数据
        if (empty($upList)) {
            $this -> showtips("没有找到该用户！");
            echo $this-> back_url;
            exit();
        }
        
        $friendList = M('friends f') -> join('inner join tab_user u on f.fuid = u.uid') 
        -> where(array('f.uid' => I('get.uid'))) -> field('f.fuid,u.username,u.account')
        -> select();  //当前所属群组名称
        
        if (IS_POST){
            //dump(I("post."));exit();
            $selVal = I("post.selVal");
            $getup = I("post.getup");

            $lstGroup = explode('|', $selVal);
            $uid = I("post.uid");
            if (count($lstGroup) > 1){ 
                $fdelData = M('friends')->where(array('uid' => $uid))->select();
                if (!empty($fdelData)){ //如果原来已经有好友的情况
                    $delete = M('friends')->where(array('uid' => $uid))->delete();  //先将原来的分组UID删除，再将编辑的数据插入数据库
                    if ($delete){
                        for ($i = 0; $i < count($lstGroup) - 1; $i++)
                        {
                            $adata['uid'] = $uid;
                            $adata['fuid'] = $lstGroup[$i];
                            $res = M('friends') ->add($adata);
                        }
                        //echo M('user_status')->getLastSql();
                        if ($res) {
                                UDP_MakePack(1, $uid, 0, 0);
                                $this -> showtips("编辑好友成功！");
                                $this->getfduid = $uid;
                                header("refresh:0;url=$this->de_li_url?p={$getup}");
                                //header("refresh:0;url={friend_edit?uid=$uid}");
                                exit();                            
                    
                        }else {
                            $this -> showtips("编辑好友失败！");
                            echo $this-> back_url;
                            exit();
                        }
                    }
                }else { //如果原来没有好友的情况
                    for ($i = 0; $i < count($lstGroup) - 1; $i++)
                    {
                        $adata['uid'] = $uid;
                        $adata['fuid'] = $lstGroup[$i];
                        $res = M('friends') ->add($adata);
                    }
                    //echo M('user_status')->getLastSql();
                    if ($res) {
                        UDP_MakePack(7, $uid, 0, 0);
                        $funccode = M('user')->where($map)-> field('funccode') ->find($udata);
                        $funccode = intval($funccode['funccode']);
                        if ($funccode !==0){
                            $iFuncCode = confirmChecked($funccode);
                        }
                        $iFuncCode = SetFuncCode(POC_FUNC_FRIEND, intval($iFuncCode));  //好友 功能开启
                                      
                        $udata['funccode'] = $iFuncCode;
                        $save = M('user')->where($map)->save($udata);
                        
                        if ($save !== false){
                            UDP_MakePack(1, $uid, 0, 0);
                            $this->getfduid = $uid;
                            $this -> showtips("编辑好友成功！"); 
                            header("refresh:0;url=$this->de_li_url?p={$getup}");                           
                           // header("refresh:0;url={friend_edit?uid=$uid}");
                            exit();
                        }else {
                            
                            $this -> showtips("开启好友功能失败！");
                            echo $this-> back_url;
                        }
                    }else {
                        $this -> showtips("编辑好友失败！");
                        echo $this-> back_url;
                        exit();
                    }
                }
                
            }else {  //删除所有好友的情况
                if (count($lstGroup) < 2){
                    $fdelData = M('friends')->where(array('uid' => $uid))->select();
                    if (!empty($fdelData)){
                        $delete = M('friends')->where(array('uid' => $uid))->delete();
                        if ($delete !== false){
                            UDP_MakePack(7, $uid, 0, 0);
                            //默认用户功能  取消好友功能
                            $funccode = M('user')->where($map)-> field('funccode') ->find();
                            $funccode = intval($funccode['funccode']);
                            if ($funccode !== 0){
                                $iFuncCode = confirmChecked($funccode);
                            }
                            
                            $udata['funccode'] = $iFuncCode;
                            $save = M('user')->where($map)->save($udata);
                            if ($save !== false){
                                UDP_MakePack(1, $uid, 0, 0);
                                $this->getfduid = $uid;
                                $this -> showtips("编辑好友成功！");  
                                header("refresh:0;url=$this->de_li_url?p={$getup}");                              
                                //header("refresh:0;url={friend_edit?uid=$uid}");
                                exit();
                            }else {
                                //echo M('user')->getLastSql();exit();
                                $this -> showtips("开启好友功能失败！");
                                echo $this-> back_url;
                                exit();
                            }
                        }
                    }else{
                        $this -> showtips("请选择好友！");
                        echo $this-> back_url;
                        exit();
                    }
                }
                               
            }
    
        }
            
        $this -> assign('getup',$getup);
        $this -> assign('opid',$opid);
        $this -> assign('subLogUser',$subLogUser);
        $this -> assign('iLevel',$iLevel);
        $this -> assign('upList',$upList);
        $this -> assign('friendList',$friendList);
        $this->display();
    }
    
    /**
     * 通过名称/ID/账号搜索好友
     */
    public function friendSearch(){
        $data = I("search");
        $account = I('Account');
        $account = trim($account);
        //$this->ajaxReturn($account); exit();
        if (!empty($data)){
            $search = I('post.search');
            $maper['uid'] = array('like',"%{$search}%");
            $maper['account'] = array('like',"%{$search}%");
            $maper['username'] = array('like',"%{$search}%");
            $maper['_logic'] = 'or';
            $map['submanid'] = I('post.Opid');
            $map['_complex'] = $maper;
            $listData = M("user") -> where($map) -> order('uid desc') -> field('uid,account, username') -> select();
            foreach ($listData as $key => $val){  //不能自己加自己好友
                if (in_array($account,$val)){
                    unset($listData[$key]);
                }    
            }
            //echo M("group") -> getLastSql();
            $this->ajaxReturn($listData);
        }
    }


    /**
     *
     * 导出设备Excel
     */
    function export_excel(){ 
        $opid = session("LogOpid");
        $xlsName  = "设备列表数据"; //导出的文件名称
        $xlsCell  = array(     //设置Excel的表头对应字段
            array('uid','用户ID(*不要做任何修改)'),
            array('username','用户名'),
            array('password','用户密码'), 
            array('account','账号'),
            array('curgid','默认群组ID'),
            array('level','用户级别'),
            array('gpscycle','GPS周期'),
            array('note','备注'),
        );
        $xlsModel = M('user u');
        
        $action = I("get.action");
        if ($action == 'partial') {  //部分导出
            $uid_right = I("post.uid_right");
            $uid_left = I("post.uid_left");
            if (!empty($uid_right) && !empty($uid_left) && intval($uid_right) > intval($uid_left)) {  
                $uid_right = intval($uid_right);
                $uid_left = intval($uid_left);
                $map['u.uid'] = array('elt',$uid_right);
                $where['u.uid'] = array('egt',$uid_left);
                $where['_complex'] = $map;
            }else{
                $this -> showtips("请填写正确范围的用户ID！");
                echo $this-> back_url;
                exit();
            }
            
        }
        $where['manid'] = $opid;
        $xlsData  = $xlsModel -> join("tab_user_status us on us.uid = u.uid") ->Field('u.uid,username,password,account,curgid,gpscycle,level,note') -> where($where) -> order("uid desc") ->select();
        // dump($xlsModel->getLastSql());
        // dump($xlsData);
        // exit;
        foreach ($xlsData as $k => $v)
        {
            //$xlsData[$k]['username'] = Gettname($v['uid']);
            array_splice($xlsData[$k]['uid']);
        }
        $this->exportExcel($xlsName,$xlsCell,$xlsData);  //下一个调用函数
            
    }

    public function exportExcel($expTitle,$expCellName,$expTableData){
        $xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称 防止乱码
        $fileName = $expTitle.date('_YmdHis');//or $xlsTitle 导出的文件名称
        $cellNum = count($expCellName);
        $dataNum = count($expTableData);

        vendor("PHPExcel.PHPExcel");
            
        $objPHPExcel = new \PHPExcel();
        $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');  //excel的表头的对应列名

        $objPHPExcel->getActiveSheet(0)->mergeCells('A1:'.$cellName[$cellNum-1].'1');  //合并第一行单元格
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $expTitle.'  Export time:'.date('Y-m-d H:i:s'));  //将数据表头名称放进第一行
        for($i=0;$i<$cellNum;$i++){
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'2', $expCellName[$i][1]);
        }
        // Miscellaneous glyphs, UTF-8
        for($i=0;$i<$dataNum;$i++){
            for($j=0;$j<$cellNum;$j++){
                $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].($i+3), $expTableData[$i][$expCellName[$j][0]]);
            }
        }

        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    /**
     * excel批量修改设备
     */
    public function  update_device_import(){
        $dp = session("dp");
        $LogUser = session("LogUser");
        $iLevel = session("LogLevel");
        $opid = session("LogOpid");
        if (!empty($_FILES)) {
            $config = array(
            'exts' => array('xlsx','xls'),  //文件支持类型
            'maxSize' => 3145728000,
            'rootPath' =>"./Public/",
            'savePath' => 'Uploads/',   //文件保存路径
            'subName' => array('date','Ymd'), //目录名称
            );
            $upload = new \Think\Upload($config);   //先将文件上传
            if (!$info = $upload->upload()) {
                $this->error($upload->getError());
            }
            vendor("PHPExcel.PHPExcel");
            $file_name = $upload->rootPath.$info['file_stu']['savepath'].$info['file_stu']['savename'];  //读取文件名
            $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));//判断导入表格后缀格式
            if ($extension == 'xlsx') {
                $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
                $objPHPExcel = $objReader->load($file_name, $encode = 'utf-8');  //设置字符
            } else if ($extension == 'xls'){
                $objReader = \PHPExcel_IOFactory::createReader('Excel5');
                $objPHPExcel = $objReader->load($file_name, $encode = 'utf-8');
            }
            $sheet = $objPHPExcel->getSheet(0);
            $highestRow = $sheet->getHighestRow();//取得总行数
            $highestColumn = $sheet->getHighestColumn(); //取得总列数
            $MaxId = 0;
            //先检测所有修改的记录的是否存在及账号是否重复
            $Account = array();  //检测当前增加的账号是否有重复
            for ($i = 3; $i <= $highestRow; $i++) {                  
                $uids = $objPHPExcel->getActiveSheet()->getCell("A" . $i)->getValue();
                $account = (string)$objPHPExcel->getActiveSheet()->getCell("D". $i)->getValue();
                $Account[] = $account;
                $uids = intval($uids);
                $map['manid'] = $opid;
                $map['uid'] = $uids;
                //dump($map);exit;
                $arr = M('user') -> where($map) -> select();
                //echo M('user') -> getLastSql();
                if (empty($arr)) {
                    $this -> showtips("修改失败！");
                    echo $this-> back_url;
                    exit();
                }else{
                    $data = M('user') -> where(array('account' => $account)) -> select(); //查询账号是否重复 
                    //echo M('user') -> getLastSql();exit;          
                    if (!empty($data)) {  //不能与数据库已有的账号重复，也不能与当前准备添加的账号重复
                        foreach ($data as $key => $v) {
                            $uidn = $v['uid'];
                        }
                        if($uidn != $uids){
                            $this -> showtips('账号'.$account.'已经存在,请重新修改！');
                            echo $this-> back_url;
                            exit();
                        }
                    }
                    
                }

            }
            for ($i = 3; $i <= $highestRow; $i++) {  //从第三行获取execl表格内容
                //后面的大写A是excel中位置
                $uids = (string)$objPHPExcel->getActiveSheet()->getCell("A" . $i)->getValue();    //uid
                $username = (string)$objPHPExcel->getActiveSheet()->getCell("B" . $i)->getValue();    //username
                $password = (string)$objPHPExcel->getActiveSheet()->getCell("C" .$i)->getValue();   
                $account = (string)$objPHPExcel->getActiveSheet()->getCell("D". $i)->getValue();                
                $curgid = (string)$objPHPExcel->getActiveSheet()->getCell("E" .$i)->getValue();  //默认群组ID                
                $level = (string)$objPHPExcel->getActiveSheet()->getCell("F" .$i)->getValue();  //级别                
                $gpscycle = (string)$objPHPExcel->getActiveSheet()->getCell("G" .$i)->getValue();  //gpscycle
                $note = (string)$objPHPExcel->getActiveSheet()->getCell("H" .$i)->getValue();
                $where['uid'] = $uids;
                $udata['uid'] = $uids;
                $udata['username'] = $username;
                $udata['account'] = $account;
                $udata['password'] = $password;
                $udata['gpscycle'] = $gpscycle;
                $udata['curgid'] = $curgid;
                $udata['level'] = $level;
                $data = M('user') -> where(array('account' => $account)) -> select(); //查询账号是否重复 
                if (!empty($data)) {  //不能与数据库已有的账号重复，也不能与当前准备添加的账号重复
                    foreach ($data as $key => $v) {
                        $uidn = $v['uid'];
                    }
                    if($uidn != $uids){
                        $this -> showtips('账号'.$account.'已经存在,修改导入未完成！');
                        echo $this-> back_url;
                        exit();
                    }
                }
                $save = M('user')->where($where)->save($udata);
                //dump($save);exit;
                if ($save !== false) {   
                    $ugList = M('user_group')->where($where)->select();
                    if (!empty($ugList)) {
                        $delete = M('user_group')->where($where)->delete();  //先将原来的分组UID删除，再将编辑的数据插入数据库
                        if ($delete) {
                            $dataU['uid'] = $uids;
                            $dataU['gid'] = $curgid;
                            $add = M('user_group') -> add($dataU);
                             
                            if ($add) {   
                                $usRow = M('user_status') -> where(array('uid'=>$uids)) -> find();
 
                                if (!empty($usRow)) {
                                    $us['note'] = $note;
                                    $saveus = M('user_status') -> where(array('uid'=>$uids)) -> save($us);
                                    if ($saveus !== false) {
                                        $uid = $uids;
                                        UDP_MakePack(1, $uid, 0, 0);
                                    }else{
                                        //$this->error("修改失败!0",U("Admin/Index/device_list/p/$dp")); 
                                        $this -> showtips('修改失败！');
                                        echo $this-> back_url;
                                        exit;
                                    }
                                }else{
                                    $us['note'] = $note;
                                    $saveus = M('user_status')  -> add($us);
                                    if ($saveus) {
                                        $uid = $uids;
                                        UDP_MakePack(1, $uid, 0, 0);
                                    }
                                }
                                
                                
                            }else{
                                //$this->error("修改失败!1",U("Admin/Index/device_list/p/$dp"));  
                                $this -> showtips('修改失败！');
                                echo $this-> back_url;
                                exit;
                            }
                        }
                    } else{
                        $dataU['uid'] = $uids;
                        $dataU['gid'] = $curgid;
                        //dump($dataU);exit;
                        $add = M('user_group') -> add($dataU);
                            if ($add) { 
                                $usRow = M('user_status') -> where(array('uid'=>$uids)) -> find();
                                if (!empty($usRow)) {
                                    $us['note'] = $note;
                                    $saveus = M('user_status') -> where(array('uid'=>$uids)) -> save($us);
                                    if ($saveus !== false) {
                                        $uid = $uids;
                                        UDP_MakePack(1, $uid, 0, 0);
                                    }else{
                                        $this -> showtips('修改失败！');
                                        echo $this-> back_url;
                                        exit;
                                    }
                                }else{
                                    $us['note'] = $note;
                                    $saveus = M('user_status')  -> add($us);
                                    if ($saveus) {
                                        $uid = $uids;
                                        UDP_MakePack(1, $uid, 0, 0);
                                    }
                                }
                            }else{
                                $this -> showtips('修改失败！');
                                echo $this-> back_url;
                                exit;
                            } 
                        } 
                    
                }else{
                    $this -> showtips('修改失败！');
                    echo $this-> back_url;
                    exit();
                }
            }
            if ( $saveus !== false) {  //$save != false && $delete != false && $add != false &&
                unlink($file_name);  //将上传的文件删除
                $this -> showtips("修改成功！");
                header("refresh:0;url={$this->de_li_url}"); //跳转至指定页面
                exit();
            }
        }
        
        $this -> assign('dp',$dp);
        $this -> assign('opid',$opid);
        $this -> assign('LogUser',$LogUser);
        $this -> assign('iLevel',$iLevel);
        $this->display();
    }
    


    /**
     * 修改时间 级别为3时
     */
    public function edit_time(){
        $LogUser = session("LogUser");
        $iLevel = session("LogLevel");
        $dp = session("dp");
        $uid = I('get.uid');
        $rowData = M("user") -> where(array('uid'=> $uid)) -> find();
           

        
        //echo M("user") ->  getLastSql();exit;
        if (IS_POST) {
            $puid = I("post.uid");
            $save['validtime'] = I("post.endDate");
            $result = M("user") -> where(array('uid'=> $puid)) -> save($save);
            $rowData = M("user") -> where(array('uid'=> $puid)) -> find();
            $note = M("user_status") -> where(array('uid'=> $puid)) ->field('note') -> find();
            if (!empty($rowData)) {
                $userLog = array();
                $userLog['account'] = $rowData['account'];
                $userLog['uid'] = $rowData['uid'];
                $userLog['uname'] = $rowData['username'];
                $userLog['pw'] = $rowData['password'];
                $userLog['level'] = $rowData['level'];
                $userLog['gpscycle'] = $rowData['gpscycle'];
                $userLog['curgid'] = $rowData['curgid'];
                $userLog['manid'] = $rowData['manid'];
                $userLog['createtime'] = $rowData['createtime'];
                $userLog['validtime'] = $rowData['validtime'];
                $userLog['funccode'] = $rowData['funccode'];
                $userLog['operation_time'] = date("Y-m-d H:i:s");
                $userLog['operation_ip'] = $_SERVER['REMOTE_ADDR'];  //操作设备的ip 
                $userLog['operation_type'] = 1;  //1为修改
                if (!empty($note)) {
                    $userLog['note'] = $note['note'];
                }
            }

            echo M("user") ->  getLastSql();
            if ($result !== false)
            {
                
                $logaddres = M('user_log')->add($userLog); //添加设备日志
                UDP_MakePack(6, $puid, 0, 0);
                //echo M('user_log')->getLastSql();
                //exit; 
                if ($logaddres == false) {
                    $this -> showtips("添加设备修改日志失败！");
                    echo $this-> back_url;
                    exit();
                }
                
                $this -> showtips("有效期修改成功！");
                header("refresh:0;url={$this->d_li_url}"); //跳转至指定页面
                exit();
            }
            else
            {
                $this -> showtips("有效期修改失败！");
                echo $this-> back_url;
                exit();
            }

        }

        $this -> assign('LogUser',$LogUser);
        $this -> assign('iLevel',$iLevel);
        $this-> assign("dp", $dp);
        $this-> assign("rowData", $rowData);

        $this->display();
    }

    
    
    
    

    /**
     * 级别为3的设备列表
     */
    public function d_list(){
        $iLevel = $_SESSION["LogLevel"];
        $opid = $_SESSION["LogOpid"];
        $LogUser = session("LogUser");
        
        //dump($_GET);
        if (IS_GET) {
            $where = array();
            $dataList = M('admain') ->where(array('manid' => $opid))  -> field('opid') -> select();
            $sNumList = array();
            $tNumList = array();
            if ($iLevel == 3) {  //如果是系统管理员，先查询分控管理员
                //分控管理员ID
                if (!empty($dataList))
                {
                    foreach ($dataList as $key => $v) {
                        $sOpid = $v['opid'];
                        $sNumList[] = $sOpid;
                    }
                }
            
                //域管理员ID
                if (!empty($sNumList))
                {
                    foreach ($sNumList as $v)
                    {
                        //dump($v);
                        $adList = M('admain') ->where(array('manid' => $v))  -> field('opid') -> select();
                        if (!empty($adList))
                        {
                            foreach ($adList as $key => $val) {
                                $tOpid = $val['opid'];
                                $tNumList[] = $tOpid;
                            }
                        }
                    }
                }
            }

            if ($iLevel == 2) {  //分控管理员
                if (!empty($dataList))
                {
                    foreach ($dataList as $key => $v) {
                        $sOpid = $v['opid'];
                        $tNumList[] = $sOpid;
                    }
                }
            }

            //设备
            if (!empty($tNumList)){
                $where['manid'] = array('in',$tNumList);
            }
        
        
            //搜索
            $searchc = I('get.searchc');
            $searchc = trim($searchc);
            $beginDate = I('get.beginDate');
            $endDate = I('get.endDate');
            $endDate = date('Y-m-d',strtotime("$endDate +1 day"));  //搜索后包要含输入的结束时间这一天的内容
            $crbeginDate = I('get.crbeginDate');
            $crendDate = I('get.crendDate');
            $crendDate = date('Y-m-d',strtotime("$crendDate +1 day"));  //搜索后包要含输入的结束时间这一天的内容
        
        
            if (!empty($searchc))
            {
                $searType = 1;
                $map['uid'] = $searchc;
                $map['username'] = $searchc;
                $map['account'] = $searchc;
                $map['_logic'] = 'or';
                $where['_complex'] = $map;
                $order = 1;
                $this -> assign("searchc", $searchc);
        
            }
            if (!empty($beginDate)) //搜索时间
            {
                $searType = 1;
                $where['validtime'] = array('Between',array($beginDate,$endDate));
                if ($order == 1) {   //如果内容与时间都不为空
                    $where['_complex'] = $map;
                }
                $this -> assign("beginDate", $beginDate);
                $this -> assign("endDate", $endDate);
            }
        
            if (!empty($crbeginDate)) //搜索时间
            {
                $searType = 1;
                $where['createtime'] = array('Between',array($crbeginDate,$crendDate));
                if ($order == 1) {   //如果内容与时间都不为空
                    $where['_complex'] = $map;
                }
                $this -> assign("crbeginDate", $crbeginDate);
                $this -> assign("crendDate", $crendDate);
            }
        
            $total = M('user ') ->where($where)  -> count(); // -> join('LEFT JOIN tab_group AS b ON a.curgid = b.gid')
            $prows = 13;
            $showpn = ceil($total/$prows);
            $Page = new \Think\Page($total,$prows);// 实例化分页类 传入总记录数和每页显示的记录数
            $arrList = M('user a') -> where($where) -> order('uid desc') -> limit($Page->firstRow.','.$Page->listRows) -> select();
            pagelist($Page);
            $show = $Page->show();// 分页显示输出
            //dump($arrList);
            //dump( M('user a') -> getLastSql() ); //exit();
            $gp = I('get.p');
            $dp = isset($gp) ? intval($gp) : 1;
            $_SESSION['dp'] = $dp;
        }
        
    
    
        $this -> assign("showpn", $showpn);
        $this -> assign("beginDate", $beginDate);
        $this -> assign("endDate", $endDate);
        $this -> assign('opid',$opid);
        $this -> assign('LogUser',$LogUser);
        $this -> assign('data',$data);
        $this -> assign('total',$total);
        $this -> assign('arrList',$arrList);
        $this -> assign('searchc',$searchc);
        $this -> assign('searType',$searType);
        $this -> assign('page',$show);
        $this -> assign('total',$total);
        $this -> assign('iLevel',$iLevel);
        $this -> assign('p',$dp);
        $this -> display();
    }

    /**
     * 设备使用详情 折线图
     */
    
    public function d_linechart(){
        $iLevel = $_SESSION["LogLevel"];
        $opid = $_SESSION["LogOpid"];
        $LogUser = session("LogUser");
        $where = array();
        $dataList = M('admain') ->where(array('manid' => $opid))  -> field('opid') -> select();
        $sNumList = array();
        $tNumList = array();
    
        //分控管理员ID
        if (!empty($dataList))
        {
            foreach ($dataList as $key => $v) {
                $sOpid = $v['opid'];
                $sNumList[] = $sOpid;
            }
        }
        //dump($sNumList)；
    
        //域管理员ID
        if (!empty($sNumList))
        {
            foreach ($sNumList as $v)
            {
                //dump($v);
                $adList = M('admain') ->where(array('manid' => $v))  -> field('opid') -> select();
                if (!empty($adList))
                {
                    foreach ($adList as $key => $val) {
                        $tOpid = $val['opid'];
                        $tNumList[] = $tOpid;
                    }
                }
            }
        }
        //dump($tNumList);
    
        //已注册
        if (!empty($tNumList)){
            $where['manid'] = array('in',$tNumList);
        }  

        //取当前月的每天添加设备的数量
        $BeginDate=date('Y-m-01', strtotime(date("Y-m-d")));
        $AndDate = date('Y-m-d', strtotime("$BeginDate +1 month -1 day"));
        $where['createtime'] = array('Between',array($BeginDate,$AndDate));
        $arrList = M('user') -> where($where)  -> group("year(createtime),month(createtime),day(createtime)") -> order('createtime asc') -> field("createtime,count(*) as ctime") -> select();
        //echo M('user') -> getLastSql();
        //每个日期添加的设备个数 
        $ctimeList = array();   
        foreach ($arrList as $v) {
            $ctimeList[] = $v['ctime'];
        }

        $daysData = array();
        foreach ($arrList as $key => $v) {
            $daysData[] = substr($v['createtime'], 8, 2); //截取日期为 只取日
        }

        $this->assign('ctimeList',$ctimeList);  
        $this->assign('daysData',$daysData);    
        $this -> assign('opid',$opid);
        $this -> assign('LogUser',$LogUser);
        $this -> assign('iLevel',$iLevel);
        $this -> display();
    }

    
    
    /**
     * 删除设备
     */
    public function device_delete(){
        $iLevel = session("LogLevel");
        $dp = session("dp");
        $opid = session("LogOpid");
        $uid = I('get.uid');
        if ($iLevel == 1) {
            $map['uid'] = $uid;
            $map['manid'] = $opid;            
        }else{
            $map['uid'] = $uid;
        }
        $userData = M('user') ->where($map) -> find();
        if (empty($userData)) {
            $this -> showtips("删除失败！");
            echo $this-> back_url;
            exit();
        }
        $usData = M('user_status') -> where(array('uid' => $uid)) -> select();
        // dump($delip);
        // dump($userData);exit;
        $userLog['account'] = $userData['account'];
        $userLog['uid'] = $userData['uid'];
        $userLog['uname'] = $userData['username'];
        $userLog['pw'] = $userData['password'];
        $userLog['level'] = $userData['level'];
        $userLog['gpscycle'] = $userData['gpscycle'];
        $userLog['curgid'] = $userData['curgid'];
        $userLog['manid'] = $userData['manid'];
        $userLog['createtime'] = $userData['createtime'];
        $userLog['validtime'] = $userData['validtime'];
        $userLog['funccode'] = $userData['funccode'];
        $userLog['operation_time'] = date("Y-m-d H:i:s");
        $userLog['operation_ip'] = $_SERVER['REMOTE_ADDR'];  //删除设备的ip
        $userLog['operation_type'] = 0;  //0为删除
        $userLog['note'] = $usData['note'];
        $result1 = M('user') ->where($map) -> delete();
        if ($result1 !== false) {
            $ugData = M('user_group') -> where(array('uid' => $uid)) -> select();           
            $fData = M('friends') -> where(array('uid' => $uid)) -> select();
            if (!empty($ugData) || !empty($usData) || !empty($fData)) {
                $result2 = M('user_group') -> where(array('uid' => $uid)) -> delete();
                $result3 = M('user_status') -> where(array('uid' => $uid)) -> delete();
                $result4 = M('friends') -> where(array('uid' => $uid)) -> delete();
                if ($result2 !== false || $result3 !== false || $result4 !== false) {
                    UDP_MakePack(2, $uid, 0, 0);
                    $logaddres = M('user_log') ->add($userLog); //添加设备删除日志
                    if ($logaddres == false) {
                        $this -> showtips("添加设备删除日志失败！");
                        echo $this-> back_url;
                        exit();
                    }
                    
                    if ($iLevel == 1) {
                       $this -> showtips("删除成功！");
                       header("refresh:0;url={$this->de_li_url}");
                       exit();
                    }else{
                        $this -> showtips("删除成功！");
                        header("refresh:0;url={$this->d_li_url}");
                        exit();
                    }
                }else{
                    $this -> showtips("删除失败！");
                    echo $this-> back_url;
                    exit();
                }                                     
            }else{
                UDP_MakePack(2, $uid, 0, 0);
                $logaddres = M('user_log') ->add($userLog); //添加设备删除日志
                if ($logaddres == false) {
                    $this -> showtips("添加设备删除日志失败！");
                    echo $this-> back_url;
                    exit();
                }

                if ($iLevel == 1) {
                   $this -> showtips("删除成功！");
                   header("refresh:0;url={$this->de_li_url}");
                   exit();
                }else{
                    $this -> showtips("删除成功！");
                    header("refresh:0;url={$this->d_li_url}");
                   exit();
                }
            }
        }else{
            $this -> showtips("删除失败！");
            echo $this-> back_url;
            exit();
        }        
    }

    /**
     * 设备删除日志
     */
    
    public function device_del_log(){
        $iLevel = session("LogLevel");
        $LogUser = session("LogUser");
        $dp = session("dp");
        $opid = session("LogOpid");

        //$data = M("user_del_log")->select();
        //搜索
        $guid = I("get.uid");
        $searchc = I("get.searchc");
        
        if(!empty($searchc))
        {
            $map['a.uid'] = array('like', "%{$searchc}%");
            $map['a.account'] = array('like', "%{$searchc}%");
            $map['a.uname'] = array('like', "%{$searchc}%");
            $map['_logic'] = 'or';
            $where['_complex'] = $map;
        }
       
        //数据分页
        $where['a.operation_type'] = 0;

        $total = M('user_log a') -> join('left join tab_group c on a.curgid = c.gid ') -> where($where) -> count();// 查询当前登录管理员用户的总记录数 
        //echo M('user') -> getLastSql();
        $prows = 10;
        $showpn = ceil($total/$prows);  //方便页面输入框跳转判断
        $Page = new \Think\Page($total,$prows);// 实例化分页类 传入总记录数和每页显示的记录数 
        pagelist($Page);  //调用分页公共函数   
        $show = $Page->show();// 分页显示输出
        $data = M('user_log a') 
        -> join('left join tab_group c on a.curgid = c.gid ' ) 
        -> where($where) 
        -> field('a.*, c.gname,c.gname') 
        -> order('operation_time desc') 
        -> limit($Page->firstRow.','.$Page->listRows)
        ->select();
        //echo M('user a') -> getLastSql();
        //dump($data);



        $this -> assign('searchc',$searchc);
        $this -> assign('total',$total);
        $this -> assign('data',$data);
        $this -> assign('opid',$opid);
        $this -> assign('LogUser',$LogUser);
        $this -> assign('iLevel',$iLevel);
        $this -> assign('page',$show);
        $this->display();
    }

    
    /**
     * 设备修改日志
     */
    
    public function device_edit_log(){
        $iLevel = session("LogLevel");
        $LogUser = session("LogUser");
        $dp = session("dp");
        $opid = session("LogOpid");

        //$data = M("user_del_log")->select();
        //搜索
        $guid = I("get.uid");
        $searchc = I("get.searchc");
        
        if(!empty($searchc))
        {
            $map['a.uid'] = array('like', "%{$searchc}%");
            $map['account'] = array('like', "%{$searchc}%");
            $map['uname'] = array('like', "%{$searchc}%");
            $map['_logic'] = 'or';
            $where['_complex'] = $map;
        }
       
        //数据分页
        $where['a.operation_type'] = 1;

        $total = M('user_log a') -> join('left join tab_group c on a.curgid = c.gid ') -> where($where) -> count();// 查询当前登录管理员用户的总记录数 
        //echo M('user') -> getLastSql();
        $prows = 10;
        $showpn = ceil($total/$prows);  //方便页面输入框跳转判断
        $Page = new \Think\Page($total,$prows);// 实例化分页类 传入总记录数和每页显示的记录数 
        pagelist($Page);  //调用分页公共函数   
        $show = $Page->show();// 分页显示输出
        $data = M('user_log a') 
        -> join('left join tab_group c on a.curgid = c.gid ' ) 
        -> where($where) 
        -> field('a.*, c.gname,c.gname') 
        -> order('operation_time desc') 
        -> limit($Page->firstRow.','.$Page->listRows)
        ->select();
        //echo M('user a') -> getLastSql();
        //dump($data);




        $this -> assign('showpn',$showpn);
        $this -> assign('searchc',$searchc);
        $this -> assign('page',$show);
        $this -> assign('total',$total);
        $this -> assign('data',$data);
        $this -> assign('opid',$opid);
        $this -> assign('LogUser',$LogUser);
        $this -> assign('iLevel',$iLevel);
        $this->display();
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
            //echo json_encode($listdevice_add);        
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
     * 续费
     */
    public function device_vtime(){
        $iLevel = $_SESSION["LogLevel"];
        $opid = $_SESSION["LogOpid"];
        //计算使用额度
        $maxRow = M('max') -> where(array('opid' => $opid)) -> field('quota,quotaed') -> find();
        if (!empty($maxRow)) {
            $quota = $maxRow['quota'];
            $quotaed = $maxRow['quotaed'];
            $vquota = $quota - $quotaed;
        }else{
            $addData['opid'] = $opid;
            $addData['quota'] = '0';
            $addData['quotaed'] = '0';
            M('max') -> add($addData);
            $vquota = 0;
        }
        //取设备信息
        $map['uid'] = I('get.uid');
        $map['manid'] = $opid;  
        $row = M('user') ->where($map) -> field('account,createtime,validtime,uid') -> find();
        //dump($row); 
        if (IS_POST) {
            $rquota = I('post.rquota');
            if ($vquota < $rquota) {
                $this -> showtips("当前额度不够续费！");
                echo $this-> back_url;
                exit();
            }else{
                $map['uid'] = I('post.uid');
                $map['manid'] = $opid;
                $udata = M('user') -> where($map) -> find();
                if (empty($udata)) {
                    $this -> showtips("续费失败！");
                    echo $this-> back_url;
                    exit();
                }else{
                    $validtime = M('user') -> where(array('uid' => I('post.uid'))) -> field('validtime') -> find();
                    $vtime = $validtime['validtime'];
                    $year = intval(I('post.rquota'));
                    //判断是否过期，没过期直接加，过期现在时间加
                    $date = date("Y-m-d H:i:s");
                    if (strtotime($vtime) > strtotime($date)) {             
                        $validtime = date("Y-m-d H:i:s", strtotime("$vtime + $year year"));
                    }else{
                        $validtime = date("Y-m-d H:i:s" , strtotime("+$year year"));
                    }
                    $vtimeSave['validtime'] = $validtime;
                    $result = M('user') -> where(array('uid' => I('post.uid'))) -> save($vtimeSave);

                    //增加本集团年费卡额度
                    $quaed = M('max') -> where(array('opid' => $opid)) -> field('quotaed') -> find();
                    $quotedSave['quotaed'] = $quaed['quotaed'] + $year;
                    $result1 = M('max') -> where(array('opid' => $opid)) -> save($quotedSave);
                    if ($result1 != false && $result != false) {
                        //增加记录
                        $bkadd['bltype'] = 4;
                        $bkadd['blfkid'] = I('post.uid');
                        $bkadd['blst'] = 1;
                        $bkadd['bldate'] = '$date';
                        $bkadd['blnum'] = $year;
                        $bkadd['bladid'] = $opid;
                        $result3 = M('bk_list') -> add($bkadd);
                        if ($result3 != false) {
                            $this -> showtips("续费成功！");
                            header("refresh:0;url={$this->de_li_url}");
                            exit();
                        }
                    }else{
                        $this -> showtips("续费失败！");
                        echo $this-> back_url;
                        exit();
                    }
                }
            }    
            
        }

        $this->assign('row',$row);
        $this->assign('vquota',$vquota);
        $this->assign('iLevel',$iLevel);
        $this -> display();

    }


    /**
     * 添加/修改设备-右侧群组名称搜索群组成员
     */
    
    public function accSearch(){
        $name = I('Name');
        if (empty($name) && $name != 0)
        {
            $map['manid'] = I('gopid');
            $listData = M("user") -> where($map) -> order('uid desc') -> field('uid, username, account') -> select();
            $this->ajaxReturn($list);
        }
        else
        {
            $Account = I('post.Account');
            $maper['account'] = array('like',"%{$Account}%");
            $maper['username'] = array('like',"%{$Account}%");
            $maper['_logic'] = 'or';
            $map['manid'] = I('post.Opid');
            $map['_complex'] = $maper;
            $listData = M("user") -> where($map) -> order('uid desc') -> field('uid, username, account') -> select();
            //echo M("user") -> getLastSql(); 
            $this->ajaxReturn($listData);
            //echo json_encode($listData);

        }
    }

    /**
     * 编辑好友页面 通过群组搜索对应成员
     */
    public function group_Members(){
        $subLogUser = session("subLogUser");
        $iLevel = session("subLogLevel");
        $gid = I("gid");
        $userid = I('Uid');
        $userid = trim($userid);
        $uids = M('user_group') -> where(array('gid' => $gid)) -> field('uid') -> select();  //根据gid 查询出对应的组成员uid
        $data = array();
        foreach ($uids as $key => $v) {
            if (in_array($userid,$v)){
                unset($v);  //防止添加自己为好友
            }
            $uid = $v['uid'];
            $row = M('user u') ->  where(array('u.uid' => $uid))  -> find();
            $account = $row['account'];
            if(!empty($account)){  //防止user表不存在的账号输出到页面
                $data[] = $row;
            }
        }

        $this->ajaxReturn($data);     
    }



    /**
     * 设备使用说明
     */
    public function Instructions_qz(){
        $subLogUser = session("subLogUser");
        $iLevel = session("subLogLevel");
    

        $this->assign('subLogUser',$subLogUser);
        $this->assign('iLevel',$iLevel);
        $this-> display();
    }

     /**
     * 群组使用说明
     */
    public function Instructions_dd(){
        $subLogUser = session("subLogUser");
        $iLevel = session("subLogLevel");
    

        $this->assign('subLogUser',$subLogUser);
        $this->assign('iLevel',$iLevel);
        $this-> display();
    }
    /**
     * 订单使用说明
     */
    public function Instructions_sb(){
        $subLogUser = session("subLogUser");
        $iLevel = session("subLogLevel");
    

        $this->assign('subLogUser',$subLogUser);
        $this->assign('iLevel',$iLevel);
        $this-> display();
    }
    


    
    








}