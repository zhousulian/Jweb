<?php
//  加载checkedbox 处理函数文件

/**
 * 设备用户功能打钩存储
 * @POC_FUNC_FRIEND 好友  以此类推 对应的复选框内容
 */
define(POC_FUNC_FRIEND ,    (1<<0));
define(POC_FUNC_QUERY_LOC,  (1<<1)); //查位
define(POC_FUNC_RECORD,     (1<<2));  //记录
define(POC_FUNC_LISTEN,     (1<<3));
define(POC_FUNC_RE_SHUT,    (1<<4)); //遥闭
define(POC_FUNC_GROUP_CALL, (1<<5));  //群呼
define(POC_FUNC_NO_CALL,    (1<<6));
define(POC_FUNC_INVATE,     (1<<7));  //单呼
define(POC_FUNC_CHG_GROUP,  (1<<8)); //换组
define(POC_FUNC_LOCATION,   (1<<9));
define(POC_FUNC_AUD_RECORD, (1<<10));  //录音
define(POC_FUNC_DIS_GROUP,  (1<<11));
define(POC_FUNC_LAST_GROUP, (1<<12));
define(POC_FUNC_NOT_INVATE, (1<<13)); //单呼勿扰


function SetFuncCode($iFlag, $iFuncCode)
{
    return $iFlag|$iFuncCode;
}

function CheckFuncCode($iFlag, $iFuncCode)
{
    if($iFlag & $iFuncCode) return 1;
    else return 0;
}

/*function getUperLevel($manid)
{
    $row = M("admain") -> where(array('opid'=> $manid)) -> field('level') -> find();
    return $nLevel = $row['level'];
}
*/


/**
 * 查询续约订单的账号个数
 */
function xyUserCount($blid){
    echo $xuzLisr = M("bk_item")-> where(array('biblid' => $blid) ) ->count(); 
}
/**
 * 群组成员列表排序
 * @param  [array] $arrays     [排序的二维数组]
 * @param  [string] $sort_key   [排序的键]
 * @param  [string] $sort_order [排序升降类型]
 * @param  [string] $sort_type  [以什么类型排序]
 * @return [array]             [返回排序后的数组]
 */
function umember_sort($arrays,$sort_key,$sort_order=SORT_DESC,$sort_type=SORT_NUMERIC ){   
    if(is_array($arrays)){   
        foreach ($arrays as $array){   
            if(is_array($array)){   
                $key_arrays[] = $array[$sort_key];   
            }else{   
                return false;   
            }   
        }   
    }else{   
        return false;   
    }  
    array_multisort($key_arrays,$sort_order,$sort_type,$arrays);   
    return $arrays;   
} 

/**
 * 导入excel 检测设备密码格式
 */
function icheckedpwd($pwd){
    if (!empty($pwd)) {  //第一次输入密码
        $pattern = "/^[a-zA-Z0-9]\w{5,17}$/";   //至少要包含大小写字母/数字/_的其中2种才匹配通过,不能包含 其他特殊字符
        $patternlet= "/^[a-z]+$/";
        $patternLet= "/^[A-Z]+$/";
        $patternN= "/^[0-9]+$/";
        if(preg_match($patternlet,$pwd))   //如果只包含小写不允许
        {
            return false;
        }
        elseif(preg_match($patternLet,$pwd))   //如果只包含写一种不允许
        {
            return false;
        }
        elseif(preg_match($patternN,$pwd))   //如果只包含数字一种不允许
        {
            return false;
        }
        elseif(!preg_match($pattern,$pwd))    //至少要包含大小写字母/数字/_的其中2种才匹配通过
        {
            return false;
        }else{
            return true;
        }
    }
}


/**
 * 计算未到期终端数数
 */
function getTotalValidNum($opid){
    $nLevel = M("admain") -> where(array('opid'=> $opid)) -> field('level') -> find();
    $nLevel = $nLevel['level'];
    //dump($nLevel);
    $where = array();
    /*$dataList = M('admain') ->where(array('manid' => $opid))  -> field('opid') -> select();
    $sNumList = array();
    $tNumList = array();
    if ($nLevel == 3) {  //如果是系统管理员，先查询分控管理员
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
                        $uToal = M('user') ->where(array('manid' => $tOpid)) -> count();
                        if ($uToal > 0) {
                            $tNumList[] = $tOpid;
                        }
  
                    }
                }
            }
        }
    }

    if ($nLevel == 2) {  //分控管理员
        if (!empty($dataList))
        {
            foreach ($dataList as $key => $v) {
                $sOpid = $v['opid'];
                $tNumList[] = $sOpid;   
            }
        }
    }*/
    $dataList = M('admain') ->where(array('manid' => $opid))  -> field('opid') -> select();
    $sNumList = array();
    $zNumList = array();
    $mNumList = array();
    $txNumList = array();
    $tNumList = array();
    if ($nLevel == 3) {  //如果是系统管理员，先查询分控管理员
        //分控管理员ID
        if (!empty($dataList))
        {
            foreach ($dataList as $key => $v) {
                $sOpid = $v['opid'];
                $sNumList[] = $sOpid;
            }
        }
        
        //1级域管理员ID
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
                        $zNumList[] = $tOpid;
                    }
                }
            }
        }

        //0级管理员
        if (!empty($zNumList)) {
            foreach ($zNumList as $v)
            {
                //dump($v);
                $adList = M('admain') ->where(array('manid' => $v))  -> field('opid') -> select();
                if (!empty($adList))
                {
                    foreach ($adList as $key => $val) {
                        $tOpid = $val['opid'];
                        $mNumList[] = $tOpid;
                    }
                }
            }
        }

        //-1级管理员
        if (!empty($mNumList)) {
            foreach ($mNumList as $v)
            {
                //dump($v);
                $adList = M('admain') ->where(array('manid' => $v))  -> field('opid') -> select();
                if (!empty($adList))
                {
                    foreach ($adList as $key => $val) {
                        $tOpid = $val['opid'];
                        $txNumList[] = $tOpid;
                    }
                }
            }
        }
        
        $rNumList = array_merge($txNumList,$zNumList); // 包括1级0级本身的设备和名下管理员的设备
        $tNumList = array_merge($rNumList,$mNumList);
        
        
    }

        //dump($tNumList);
        

    if ($nLevel == 2) {  
        //1级管理员
        if (!empty($dataList))
        {
            foreach ($dataList as $key => $v) {
                $sOpid = $v['opid'];
                $zNumList[] = $sOpid;
            }
        }
    
        //0级管理员
        if (!empty($zNumList)) {
            foreach ($zNumList as $v)
            {
                //dump($v);
                $adList = M('admain') ->where(array('manid' => $v))  -> field('opid') -> select();
                if (!empty($adList))
                {
                    foreach ($adList as $key => $val) {
                        $tOpid = $val['opid'];
                        $mNumList[] = $tOpid;
                    }
                }
            }
        }
        //-1级管理员
        if (!empty($mNumList)) {
            foreach ($mNumList as $v)
            {
                //dump($v);
                $adList = M('admain') ->where(array('manid' => $v))  -> field('opid') -> select();
                if (!empty($adList))
                {
                    foreach ($adList as $key => $val) {
                        $tOpid = $val['opid'];
                        $txNumList[] = $tOpid;
                    }
                }
            }
        }

        $rNumList = array_merge($txNumList,$zNumList); // 包括1级0级本身的设备和名下管理员的设备
        $tNumList = array_merge($rNumList,$mNumList); 

    }

    
    if ($nLevel == 1) {  
        //0级管理员
        if (!empty($dataList))
        {
            foreach ($dataList as $key => $v) {
                $sOpid = $v['opid'];
                $mNumList[] = $sOpid;
            }
        }
        
    
        //-1级管理员
        if (!empty($mNumList)) {
            foreach ($mNumList as $v)
            {
                //dump($v);
                $adList = M('admain') ->where(array('manid' => $v))  -> field('opid') -> select();
                if (!empty($adList))
                {
                    foreach ($adList as $key => $val) {
                        $tOpid = $val['opid'];
                        $txNumList[] = $tOpid;
                    }
                }
            }
        }

        //$rNumList = array_merge($txNumList,$zNumList); // 包括1级0级本身的设备和名下管理员的设备
        $tNumList = array_merge($txNumList,$mNumList); 
        $tNumList[] = $opid;   //加上1级本身的设备

    }

    if ($nLevel == 0) {  
        //-1级管理员
        if (!empty($dataList))
        {
            foreach ($dataList as $key => $v) {
                $sOpid = $v['opid'];
                $mNumList[] = $sOpid;
            }
        }
        
    
        //-1级管理员
        if (!empty($mNumList)) {
            foreach ($mNumList as $v)
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

        //$rNumList = array_merge($txNumList,$zNumList); // 包括1级0级本身的设备和名下管理员的设备
        $tNumList[] = $opid; 

    }
    //dump($rNumList);
    //dump($tNumList);
    if ($nLevel == 1 || $nLevel == 0) {  //域管理员
        if (!empty($dataList))
        {
            foreach ($dataList as $key => $v) {
                $sOpid = $v['opid'];
                $tNumList[] = $sOpid;
            }
        }
        $now = date("Y-m-d");
        $where['validtime'] = array(array('GT', $now));
        //$where['manid'] = $opid;
        if (!empty($tNumList)){
            $where['manid'] = array('in',$tNumList);
        }else{
            $where['manid'] = $opid;
        }
        $totalVlidNum = M('user') ->where($where) -> count();
       //dump( M("user")  ->getLastSql());
       //dump($totalVlidNum);

    }else{
        //设备
        if (!empty($tNumList)){
            $now = date("Y-m-d");
            $where['validtime'] = array(array('GT', $now));
            $where['manid'] = array('in',$tNumList);
            $totalVlidNum = M('user') ->where($where)  -> count();
        }
    }

    
    if ($totalVlidNum > 0) {
        return  $totalVlidNum;
    }else{
        return  0;
    }

}

/**
 * 读取设备管理员名称
 * $manid string  设备的manid
 */
function getAdName($manid)
{
    $Data = M("admain") -> where(array('opid'=> $manid)) -> field('opname') -> find();
    if(!empty($Data)){
        return $opname = $Data['opname'];
    }else{
        return $opname = '';
    }                                                       
}
/**
 * 计算已注册管理员数
 */
function getAdUseNum($opid){
    $nLevel = M("admain") -> where(array('opid'=> $opid)) -> field('level') -> find();
    $nLevel = $nLevel['level'];

    if ($nLevel == 1){
        $totalUsed = M("user") -> where(array('manid'=> $opid)) -> count();
    }else {
        $totalUsed = 0;
        $maxData = M("admain") -> where(array('manid'=> $opid)) -> field('maxuser') -> select();
        foreach ($maxData as $key => $v) {
            $everymax = $v['maxuser'];
            $totalUsed += $everymax;
        }
    }
    
    return $totalUsed;
}

/**
 * 查询群组列表的调度员账号
 */
function getDispatcherData($uid){
    $opid = session("LogOpid");
    $map['manid'] = $opid;
    $map['uid'] = $uid;
    if ($uid != 0){
        $row = M("user") ->where($map) -> field('account') ->find();
        if (!empty($row)){
            return $disUid = $row['account'];
        }
    }
}

    /**
     * 编辑好友后修改用户友功能
     */
    function confirmChecked($checked){

        if(CheckFuncCode(POC_FUNC_QUERY_LOC, intval($checked))!==0)
        {
            $iFuncCode = SetFuncCode(POC_FUNC_QUERY_LOC, intval($iFuncCode));  
        }
        if(CheckFuncCode(POC_FUNC_RECORD, intval($checked))!==0)
        {
            $iFuncCode = SetFuncCode(POC_FUNC_RECORD, intval($iFuncCode)); 
        }
        if(CheckFuncCode(POC_FUNC_LISTEN, intval($checked))!==0)
        {
            $iFuncCode = SetFuncCode(POC_FUNC_LISTEN, intval($iFuncCode));
        }
        if(CheckFuncCode(POC_FUNC_RE_SHUT, intval($checked))!==0)
        {
            $iFuncCode = SetFuncCode(POC_FUNC_RE_SHUT, intval($iFuncCode));
        }
        if(CheckFuncCode(POC_FUNC_GROUP_CALL, intval($checked))!==0)
        {
            $iFuncCode = SetFuncCode(POC_FUNC_GROUP_CALL, intval($iFuncCode));
        }
        if(CheckFuncCode(POC_FUNC_NO_CALL, intval($checked))!==0)
        {
            $iFuncCode = SetFuncCode(POC_FUNC_NO_CALL, intval($iFuncCode));
        }
        if(CheckFuncCode(POC_FUNC_INVATE, intval($checked))!==0)
        {
            $iFuncCode = SetFuncCode(POC_FUNC_INVATE, intval($iFuncCode));
        }
        if(CheckFuncCode(POC_FUNC_CHG_GROUP, intval($checked))!==0)
        {
            $iFuncCode = SetFuncCode(POC_FUNC_CHG_GROUP, intval($iFuncCode));
        }
        if(CheckFuncCode(POC_FUNC_LOCATION, intval($checked))!==0)
        {
            $iFuncCode = SetFuncCode(POC_FUNC_LOCATION, intval($iFuncCode));
        }
        if(CheckFuncCode(POC_FUNC_AUD_RECORD, intval($checked))!==0)
        {
            $iFuncCode = SetFuncCode(POC_FUNC_AUD_RECORD, intval($iFuncCode));
        }
        if(CheckFuncCode(POC_FUNC_DIS_GROUP, intval($checked))!==0)
        {
            $iFuncCode = SetFuncCode(POC_FUNC_DIS_GROUP, intval($iFuncCode));
        }
        if(CheckFuncCode(POC_FUNC_LAST_GROUP, intval($checked))!==0)
        {
            $iFuncCode = SetFuncCode(POC_FUNC_LAST_GROUP, intval($iFuncCode));
        }
        if(CheckFuncCode(POC_FUNC_NOT_INVATE, intval($checked))!==0)
        {
            $iFuncCode = SetFuncCode(POC_FUNC_NOT_INVATE, intval($iFuncCode));
        }
        return $iFuncCode;
    }
    

/**
 * 用户功能复选框的选定查询分解
 */
function diliptChecked($checked){
    /*$checkedNum = array();
    if(CheckFuncCode(POC_FUNC_FRIEND, intval($checked))!==0)
    {
        echo "好友&nbsp;&nbsp;";
        $checkedNum[] = 1;
        if(count($checkedNum) ==7){  //当选择7个以上换行
            echo "<br/>";
        }
    }
    if(CheckFuncCode(POC_FUNC_QUERY_LOC, intval($checked))!==0)
    {
        echo "查位&nbsp;&nbsp;";
        $checkedNum[] = 1;
        if(count($checkedNum) ==7){
            echo "<br/>";
        }
    }
    if(CheckFuncCode(POC_FUNC_RECORD, intval($checked))!==0)
    {
        echo "记录&nbsp;&nbsp;";
        $checkedNum[] = 1;
        if(count($checkedNum) ==7){
            echo "<br/>";
        }
    }
    if(CheckFuncCode(POC_FUNC_LISTEN, intval($checked))!==0)
    {
        echo "监听&nbsp;&nbsp;";
        $checkedNum[] = 1;
        if(count($checkedNum) ==7){
            echo "<br/>";
        }
    }  
    if(CheckFuncCode(POC_FUNC_RE_SHUT, intval($checked))!==0)
    {
        echo "遥闭&nbsp;&nbsp;";
        $checkedNum[] = 1;
        if(count($checkedNum) ==7){
            echo "<br/>";
        }
    }
    if(CheckFuncCode(POC_FUNC_GROUP_CALL, intval($checked))!==0)
    {
        echo "群呼&nbsp;&nbsp;";
        $checkedNum[] = 1;
        if(count($checkedNum) ==7){
            echo "<br/>";
        }
    }
    if(CheckFuncCode(POC_FUNC_NO_CALL, intval($checked))!==0)
    {
        echo "禁呼&nbsp;&nbsp;";
        $checkedNum[] = 1;
        if(count($checkedNum) ==7){
            echo "<br/>";
        }
    }
    if(CheckFuncCode(POC_FUNC_INVATE, intval($checked))!==0)
    {
        echo "单呼&nbsp;&nbsp;";
        $checkedNum[] = 1;
        if(count($checkedNum) ==7){
            echo "<br/>";
        }
    }
    if(CheckFuncCode(POC_FUNC_CHG_GROUP, intval($checked))!==0)
    {
        echo "换组&nbsp;&nbsp;";
        $checkedNum[] = 1;
        if(count($checkedNum) ==7){
            echo "<br/>";
        }
    }
    if(CheckFuncCode(POC_FUNC_LOCATION, intval($checked))!==0)
    {
        echo "定位&nbsp;&nbsp;";
        $checkedNum[] = 1;
        if(count($checkedNum) ==7){
            echo "<br/>";
        }
    }
    if(CheckFuncCode(POC_FUNC_AUD_RECORD, intval($checked))!==0)
    {
        echo "录音&nbsp;&nbsp;";
        $checkedNum[] = 1;
        if(count($checkedNum) ==7){
            echo "<br/>";
        }
    }
    if(CheckFuncCode(POC_FUNC_DIS_GROUP, intval($checked))!==0)
    {
        echo "显组&nbsp;&nbsp;";
        $checkedNum[] = 1;
        if(count($checkedNum) ==7){
            echo "<br/>";
        }
    }
    if(CheckFuncCode(POC_FUNC_LAST_GROUP, intval($checked))!==0)
    {
        echo "最后组&nbsp;&nbsp;";
        $checkedNum[] = 1;
        if(count($checkedNum) ==7){
            echo "<br/>";
        }
    }
    if(CheckFuncCode(POC_FUNC_NOT_INVATE, intval($checked))!==0)
    {
        echo "单呼勿扰";
        $checkedNum[] = 1;
        if(count($checkedNum) ==7){
            echo "<br/>";
        }
    } */
    
    $strArr = array();
    if(CheckFuncCode(POC_FUNC_FRIEND, intval($checked))!==0)
    {
        $strArr['fri'] = "好友";
    }
    if(CheckFuncCode(POC_FUNC_QUERY_LOC, intval($checked))!==0)
    {
        $strArr['qloc'] = "查位";
    }
    if(CheckFuncCode(POC_FUNC_RECORD, intval($checked))!==0)
    {
        $strArr['rec'] = "记录";
    }
    if(CheckFuncCode(POC_FUNC_LISTEN, intval($checked))!==0)
    {
        $strArr['list'] = "监听";
    }  
    if(CheckFuncCode(POC_FUNC_RE_SHUT, intval($checked))!==0)
    {
        $strArr['reshut'] = "遥毙";
    }
    if(CheckFuncCode(POC_FUNC_GROUP_CALL, intval($checked))!==0)
    {
        $strArr['gcall'] = "群呼";        
    }
    if(CheckFuncCode(POC_FUNC_NO_CALL, intval($checked))!==0)
    {
        $strArr['nocall'] = "禁呼";
    }
    if(CheckFuncCode(POC_FUNC_INVATE, intval($checked))!==0)
    {
        $strArr['invate'] = "单呼"; 
        
    }
    if(CheckFuncCode(POC_FUNC_CHG_GROUP, intval($checked))!==0)
    {
        $strArr['change_g'] = "换组";
    }
    if(CheckFuncCode(POC_FUNC_LOCATION, intval($checked))!==0)
    {
        $strArr['loc'] = "定位";
    }
    if(CheckFuncCode(POC_FUNC_AUD_RECORD, intval($checked))!==0)
    {
        
        $strArr['vrecd'] = "录音";
    }
    if(CheckFuncCode(POC_FUNC_DIS_GROUP, intval($checked))!==0)
    {
        $strArr['disg'] = "显组";        
    }
    if(CheckFuncCode(POC_FUNC_LAST_GROUP, intval($checked))!==0)
    {
        $strArr['lastg'] = "最后组";       
    }
    if(CheckFuncCode(POC_FUNC_NOT_INVATE, intval($checked))!==0)
    {
        $strArr['ninvate'] = "单呼勿扰";
    }
    
    $strArr = implode(",",$strArr);  //将数组转为字符串
    return $strArr;
    
}
/**
 * 分页设置
 */
function pagelist($Page){
	$Page->setConfig('header', '<li class="rows">&nbsp;共<b>%TOTAL_ROW%</b>条记录&nbsp;第<b>%NOW_PAGE%</b>页/共<b>%TOTAL_PAGE%</b>页&nbsp; 到 <input  id="npage_num" style="" type="text" /> 页 </li> <p class="qd" ><a id="npage_qd" >确定</a></p>');
    $Page->setConfig('prev', '上一页');    
    $Page->setConfig('next', '下一页');
    $Page->setConfig('last', '尾页');
    $Page->setConfig('first', '首页');    
    $Page->setConfig('theme', '%FIRST%%UP_PAGE%%LINK_PAGE%%DOWN_PAGE%%END%%HEADER%');
    $Page->lastSuffix = false;//最后一页不显示为总页数
} 

function vpagelist($Page){
    $Page->setConfig('header', '<li class="rows">&nbsp;');
    $Page->setConfig('prev', '<<');    
    $Page->setConfig('next', '>>');
    $Page->setConfig('last', '尾页');
    $Page->setConfig('first', '首页');    
    $Page->setConfig('theme', '%FIRST%%UP_PAGE%%LINK_PAGE%%DOWN_PAGE%%END%%HEADER%');
    $Page->lastSuffix = false;//最后一页不显示为总页数
} 

/**
 * 截取管理员列表备注字符串
 */
function cubstr($string, $beginIndex, $length)
{
    if (strlen($string) < $length)
    {
        return substr($string, $beginIndex);
    }

    $char = ord($string[$beginIndex + $length - 1]);
    if ($char >= 224 && $char <= 239)
    {
        $str = substr($string, $beginIndex, $length - 1);
        return $str;
    }

    $char = ord($string[$beginIndex + $length - 2]);
    if ($char >= 224 && $char <= 239)
    {
        $str = substr($string, $beginIndex, $length - 2);
        return $str;
    }
    return substr($string, $beginIndex, $length);
}

/**
 * udp发送数据
 */

function UDP_SendPacket($input)
{
    //echo "Enter SndPacket</br>";
    $server = '127.0.0.1';
    $port = 10031;
    
    if(!($sock = socket_create(AF_INET, SOCK_DGRAM, 0))) 
    {
        $errorcode = socket_last_error();
        $errormsg = socket_strerror($errorcode);
        die("Couldn't create socket: [$errorcode] $errormsg \n");
    }
    
    //echo "Socket created </br>";
    //echo $input.'</br>';
    
    $sendStrArray = str_split($input, 2); 
    
    for ($j = 0; $j < count($sendStrArray); $j++) 
    {
        //echo $sendStrArray[$j].' ';
        $arrChr = $arrChr.chr(hexdec($sendStrArray[$j]));
    }
    
    if(!socket_sendto($sock, $arrChr , count($sendStrArray)*2, 0 , $server , $port)) 
    { 
        $errorcode = socket_last_error();
        $errormsg = socket_strerror($errorcode);
        die("Could not send data: [$errorcode] $errormsg \n");
    }
    
    socket_close($sock); 
}
    
function UDP_MakePack($PACK_TYPE, $id, $par1, $par2)
{
    //echo "Enter Case 0</br>";
    $input = "7e";
    $arrType = sprintf("%02s", dechex($PACK_TYPE));
    $arrId = sprintf("%08s", dechex($id));
    $arrPar1 = sprintf("%04s", dechex($par1));
    $arrPar2 = sprintf("%04s", dechex($par2));
    
    $sendStrArray = str_split($arrId, 2); 
    $arrPar1Array = str_split($arrPar1, 2); 
    $arrPar2Array = str_split($arrPar2, 2); 
    
    $input = $input.$arrType.$sendStrArray[3].$sendStrArray[2].$sendStrArray[1].$sendStrArray[0].
                $arrPar1Array[1].$arrPar1Array[0].$arrPar2Array[1].$arrPar2Array[0]."7f";
    UDP_SendPacket($input);
}
//广播提醒设备账号续费
function strtoascii($str)
{
        $change_after='';
        for($i=0;$i<strlen($str);$i++)
        {
            //echo ord($str[$i])." ";
            $temp_str=dechex(ord($str[$i]));
            $change_after.=$temp_str[0].$temp_str[1];
        }
        return $change_after;
}


function UDP_SendBroadcast($id, $msg)
{
    $input = "7e0b";
    $arrLeng = sprintf("%02s", dechex(strlen($msg)));
    $arrId = sprintf("%08s", dechex($id));
    $sendStrArray = str_split($arrId, 2);
    $input = $input.$sendStrArray[3].$sendStrArray[2].$sendStrArray[1].$sendStrArray[0].$arrLeng;
    $st1 = strtoascii($msg);
    $input=$input.$st1."7f";
    //echo $input;
    UDP_SendPacket($input);
}






function getNums($params)  //二级管理员添加订单列表的未注册终端数
{
    extract($params);
    $iLevel = session("LogLevel");
    $validuser = 0;
    $usednum = 0;
    $dbution = 0;
    $x = 0;
    $adRow = M('admain') -> where(array('opid' => $params)) -> field('maxuser,level') -> find();
    //最大
    if (!empty($adRow))
    {
        $maxuser = $adRow['maxuser'];
        $nLevel = $adRow['level'];
    }
        
    //已分配
    $usedList = M('admain') -> where(array('manid' => $params)) -> field('maxuser') -> select();

    if (!empty($usedList))
    {
        $dbution = 0;
        foreach ($usedList as $v) {
            $dbution += $v['maxuser'];
        }
    }
    //echo $dbution;

    if ($iLevel <= 1) {
        $devListNum = M('max') -> where(array('opid' => $params)) -> field('quotaed') -> find();
        $dbution1 = $dbution + $devListNum['quotaed'];

    }else {
        $dbution1 = $dbution;
    }
    

    //未分配
    if ($maxuser >= $dbution1)
    {
        $undbution = $maxuser - $dbution1;
        return $undbution;
    }
    else
    {
        return $undbution = 0;
    }


}

/**
 * 计算每条管理员记录的额度
 */
function adRowNums($opid)  //将opid传进
{
    $validuser = 0;
    $usednum = 0;
    $dbution = 0;
    $x = 0;
    $adRow = M('admain') -> where(array('opid' => $opid)) -> field('maxuser,level') -> find();
    //最大
    if (!empty($adRow))
    {
        $maxuser = $adRow['maxuser'];
        $nLevel = $adRow['level'];
    }
        
    /*//已分配
    $usedList = M('admain') -> where(array('manid' => $opid)) -> field('maxuser') -> select();

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
    }*/

    switch ($nLevel)
    {
        case 0:
            $usedcnt = M('user') -> where(array('manid' => $opid)) -> count();
            $opidNum = M('admain') -> where(array('manid' => $opid,"level"=>0)) -> field('maxuser') -> select();
            //域管理员ID
            if (!empty($opidNum))
            {
                $subnum = 0;
                foreach ($opidNum as $v) {
                    $subnum += $v['maxuser'];
                }
            }
            $usednum = $subnum + $usedcnt;
            break;

        case 1:
            $usedcnt = M('user') -> where(array('manid' => $opid)) -> count();
            $opidNum = M('admain') -> where(array('manid' => $opid,"level"=>0)) -> field('maxuser') -> select();
            //域管理员ID
            if (!empty($opidNum))
            {
                $subnum = 0;
                foreach ($opidNum as $v) {
                    $subnum += $v['maxuser'];
                }
            }
            $usednum = $subnum + $usedcnt;
            break;
        case 2:
            $opidNum = M('admain') -> where(array('manid' => $opid)) -> field('maxuser') -> select();

            //域管理员ID
            if (!empty($opidNum))
            {
                $usednum = 0;
                foreach ($opidNum as $v) {
                    $usednum += $v['maxuser'];
                }
            }
            break;
        
    }

    //  未注册终端数
    if ($maxuser >= $usednum)
    {
        echo $validuser = $maxuser - $usednum;

    }
    else
    {
        echo $validuser = 0;
    }
}

/**
 * 计算用户数量
 */
/*function usedNum($opid){
	    $validuser = 0;
        $usednum = 0;
        $dbution = 0;
        $nData = M('admain') -> where(array('opid' => $opid)) -> field('maxuser,level') -> find();

        //最大
        if (!empty($nData))
        {
            $maxuser = $nData['maxuser'];
            $nLevel = $nData['level'];
        }
        //已分配
        $muserData = M('admain') -> where(array('manid' => $opid)) -> field('maxuser') -> select();
        //echo M('admain') -> getLastSql(); 

        if (!empty($muserData))
        {
            foreach ($muserData as  $value) {
                $dbution += $value['maxuser'];
            }
        }

        //未分配
        $validuser = $maxuser - $dbution;
        switch ($nLevel)
        {
            case 0:
            case 1:
                $unum = 0;
                $curnum = M('user') -> where(array('manid' => $opid)) -> count();
                $userList = M('admain') -> where(array('manid' => $opid)) -> field('opid') -> select();

                $tNumList = array();
                //域管理员ID
                if (!empty($userList))
                {
                    foreach ($userList as  $value) {
                        $tOpid = $value['opid'];
                        $tNumList[] = $tOpid;
                    }
                }
                //已注册
                if (!empty($tNumList))
                {
                    // foreach ($tNumList as $v)
                    // {
                    //     $unum = M('user') -> where(array('manid' => $v)) -> count();
                    //     $usednum += $unum;
                    // }
                    $where['manid'] = array("in",$tNumList);
                    $unum = M('user') -> where($where) -> count();
                    // echo M("user") -> getLastSql();

                }

                echo $usednum = $curnum + $unum;
                break;
            case 2:
                $userList = M('admain') -> where(array('manid' => $opid)) -> field('opid') -> select();
                $tNumList = array();
                //域管理员ID
                if (!empty($userList))
                {
                    foreach ($userList as  $value) {
                        $tOpid = $value['opid'];
                        $tNumList[] = $tOpid;
                    }
                }
                //已注册
                if (!empty($tNumList))
                {
                    //foreach ($tNumList as $v)
                    //{
                    //    $unum = M('user') -> where(array('manid' => $v)) -> count();
                    //    $usednum += $unum;
                    //}

                    $where['manid'] = array("in",$tNumList);
                    $usednum = M('user') -> where($where) -> count();
                }
                echo $usednum;
                
                break;
            case 3:
                $adList = M('admain') -> where(array('manid' => $opid)) -> field('opid') -> select();
                $sNumList = array();
                $tNumList = array();
                //分控管理员ID
                if (!empty($adList))
                {
                   foreach ($adList as  $value) {
                        $sOpid = $value['opid'];
                        $sNumList[] = $sOpid;
                    }
                }
                //域管理员ID
                if (!empty($sNumList))
                {
                    foreach ($sNumList as $v)
                    {
                        $usedArr = M('admain') -> where(array('manid' => $v)) -> field('opid') -> select();
                        if (!empty($usedArr))
                        {
                            foreach ($usedArr as  $val) {
                                $tOpid = $val['opid'];
                                $tNumList[] = $tOpid;
                            }
                        }
                    }
                }
                //已注册数量
                if (!empty($tNumList))
                {
                    // foreach ($tNumList as $v)
                    // {
                    //     $unum = M('user') -> where(array('manid' => $v)) -> count();
                    //     $usednum += $unum;
                    // }
                    $where['manid'] = array("in",$tNumList);
                    $usednum = M('user') -> where($where) -> count();
                }
                echo ($usednum);
                break;
            case 4:
                $adList1 = M('admain') -> where(array('manid' => $opid)) -> field('opid') -> select();
                $rNumList = array();
                $sNumList = array();
                $tNumList = array();
                //系统管理员ID
                if (!empty($adList1))
                {
                    foreach ($adList1 as  $val) {
                        $rOpid = $val['opid'];
                        $rNumList[] = $rOpid;
                    }
                }
                //分控管理员ID
                if (!empty($rNumList))
                {
                    foreach ($rNumList as $v)
                    {
                        $adList2 = M('admain') -> where(array('manid' => $v)) -> field('opid') -> select();
                        if (!empty($adList2))
                        {
                            foreach ($adList2 as  $val) {
                                $sOpid = $val['opid'];
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
                        $adList3 = M('admain') -> where(array('manid' => $v)) -> field('opid') -> select();
                        if (!empty($adList3))
                        {
                            foreach ($adList3 as  $val) {
                                $tOpid = $val['opid'];
                                $tNumList[] = $tOpid;
                            }
                        }
                    }
                }
                //已注册数量
                if (!empty($tNumList))
                {
                    // foreach ($tNumList as $v)
                    // {
                    //     $unum = M('user') -> where(array('manid' => $v)) -> count();
                    //     $usednum += $unum;
                    // }

                    $where['manid'] = array("in",$tNumList);
                    $usednum = M('user') -> where($where) -> count();

                }
                echo ($usednum);
                break;
        }

	}*/

/**
 * 查询记录的额度
 */
function getmaxData (){
	$maxRow = M('max') -> where(array('opid' => $vo['opid'])) -> field('quota,quotaed') -> find();
    if (!empty($maxRow))
    {
        $quota = $maxRow['quota'];
        $quotaed = $maxRow['quotaed'];
        $vquota = $quota - $quotaed;
    }
    else
    {
        $quota = 0;
        $quotaed = 0;
    }

}

/**
 * 计算额度数量
 * @param  $params 
 */
function Nums($params)  //将opid传进
{
    $validuser = 0;
    $usednum = 0;
    $dbution = 0;
    $x = 0;
    $adRow = M('admain') -> where(array('opid' => $params)) -> field('maxuser,level') -> find();
    //最大
    if (!empty($adRow))
    {
        $maxuser = $adRow['maxuser'];
        $nLevel = $adRow['level'];
    }
        
    //已分配
   /* $usedList = M('admain') -> where(array('manid' => $params)) -> field('maxuser') -> select();

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
    }*/

    switch ($nLevel)
    {
        case 0:
            $curnum = M('user') -> where(array('manid' => $params))-> count();       
            $subusedarr= M('max') -> where(array('manid' => $params)) -> field('quotaed') -> select(); 
            $subnum = 0;  
            if(!empty($subusedarr)){
                foreach ($subusedarr as $v) {
                    $subnum += $v['quotaed'];
                }
            }   
            $usednum = $curnum + $subnum; //本身的设备加上管理员下的设备
        case 1:
            $usednum = M('user') -> where(array('manid' => $params)) -> count();
            $subusedarr= M('max') -> where(array('fmanid' => $params)) -> field('quotaed') -> select(); 
            $subnum = 0;  
            if(!empty($subusedarr)){
                foreach ($subusedarr as $v) {
                    $subnum += $v['quotaed'];
                }
            } 
            $usednum = $curnum + $subnum; 
            break;
        case 2:
            $opidNum = M('admain') -> where(array('manid' => $params)) -> field('opid') -> select();
            //域管理员ID
            if (!empty($opidNum))
            {
                $tNumList = array();
                foreach ($opidNum as $v) {
                    $tOpid = $v['opid'];
                    $tNumList[] = $tOpid;
                }
            }

            //已注册
            if (!empty($tNumList))
            {
                $map['manid'] = array('in',$tNumList);
                $usednum = M('user') -> where($map) -> count();
                //dump( M("user")->getLastSql() );   
                //$opidNum;        
            }
            break;
        case 3:
            $opidList = M('admain') -> where(array('manid' => $params)) -> field('opid') -> select();
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

            break;
        case 4:
            $opidList = M('admain') -> where(array('manid' => $params)) -> field('opid') -> select();
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
                $x = 1; 
                
            }
            break;
    }
    $x = 2; 
    //  未注册终端数
    if ($maxuser >= $usednum)
    {
        return $validuser = $maxuser - $usednum;

    }
    else
    {
        return $validuser = 0;
    }
}

/**
 * 计算额度数量
 * @param  $params 
 */
function dbtailsNums($params)  //将opid传进
{
    $validuser = 0;
    $usednum = 0;
    $dbution = 0;
    $x = 0;
    $adRow = M('admain') -> where(array('opid' => $params)) -> field('maxuser,level') -> find();
    //最大
    if (!empty($adRow))
    {
        $maxuser = $adRow['maxuser'];
        $nLevel = $adRow['level'];
    }
        
    //已分配
    $usedList = M('admain') -> where(array('manid' => $params)) -> field('maxuser') -> select();

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

    switch ($nLevel)
    {
        case 0:
        case 1:
            $usednum = M('user') -> where(array('manid' => $params)) -> field('maxuser') -> count();
            $x = 1;       
            break;
        case 2:
            $opidNum = M('admain') -> where(array('manid' => $params)) -> field('opid') -> select();
            //域管理员ID
            if (!empty($opidNum))
            {
                $tNumList = array();
                foreach ($opidNum as $v) {
                    $tOpid = $v['opid'];
                    $tNumList[] = $tOpid;
                }
            }

            //已注册
            if (!empty($tNumList))
            {
                $map['manid'] = array('in',$tNumList);
                $usednum = M('user') -> where($map) -> count();
                $x = 1; 
                //dump( M("user")->getLastSql() );   
                //$opidNum;        
            }
            break;
        case 3:
            $opidList = M('admain') -> where(array('manid' => $params)) -> field('opid') -> select();
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
                $x = 1;        
            }

            break;
        case 4:
            $opidList = M('admain') -> where(array('manid' => $params)) -> field('opid') -> select();
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
    }
    //  使用百分数
    if ($usednum != 0)
    {
        echo $pt = ceil(( $usednum / $maxuser ) * 100);

    }
    else
    {
        echo $pt = 0;
    }

}

function heaerNums($params)  //将opid传进
{
    $validuser = 0;
    $usednum = 0;
    $dbution = 0;
    $adRow = M('admain') -> where(array('opid' => $params)) -> field('maxuser,level') -> find();
    //最大
    if (!empty($adRow))
    {
        $maxuser = $adRow['maxuser'];
        $nLevel = $adRow['level'];
    }
        
    //已分配
    /*$usedList = M('admain') -> where(array('manid' => $params)) -> field('maxuser') -> select();

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
    }*/

    $ndata = array();

    switch ($nLevel)
    {
        case 0:
            $curnum = M('user') -> where(array('manid' => $params))-> count();       
            $subusedarr= M('max') -> where(array('manid' => $params)) -> field('quotaed') -> select(); 
            $subnum = 0;  
            if(!empty($subusedarr)){
                foreach ($subusedarr as $v) {
                    $subnum += $v['quotaed'];
                }
            }   
            $usednum = $curnum + $subnum; //本身的设备加上管理员下的设备
            //return $ndata['usednum'] = $usednum;
            break;
        case 1:
            $data = M('max') -> where(array('opid' => $params)) -> field('quotaed') -> find(); //本身登录的域管理员下的设备
            if(!empty($data)){
                $curnum = $data['quotaed'];
            }else{ 
                $curnum = M('user') -> where(array('manid' => $params))  -> count(); //本身登录的域管理员下的设备
            }
            //dump( M("max")->getLastSql() );   
            //dump($curnum);
            $subnum = 0;
            $datasub = M('max') -> where(array('fmanid' => $params)) -> field('quotaed') -> select(); //本身登录的域管理员下的设备
            if(!empty($datasub)){
                foreach ($datasub as $v) {
                    $subnum += $v['quotaed'];
                }
            }
            $usednum = $curnum + $subnum; //本身的设备加上管理员下的设备
            //return $ndata['usednum'] = $usednum;
            break;
        case 2:
            $opidNum = M('admain') -> where(array('manid' => $params)) -> field('opid') -> select(); // 查询一级的管理员
            //域管理员ID
            if (!empty($opidNum))
            {
                $tNumList = array();
                //$subNumList = array();
                foreach ($opidNum as $v) {
                    $tOpid = $v['opid'];
                    $tNumList[] = $tOpid;
                }
            }
            echo "t";
            dump($tNumList);
            //已注册
            if (!empty($tNumList))
            {
                $map['manid'] = array('in',$tNumList);
                $curnum = M('user') -> where($map) -> count(); //1级自身注册的数量
                //dump( M("user")->getLastSql() ); 
                //dump($curnum);//exit
                $where['fmanid'] = array('in',$tNumList);  //查询所有0级下的设备
                $datasub = M('max') -> where($where) -> field('opid,quotaed') -> select(); //本身登录的域管理员下的设备
                dump($datasub);
                $subnum = 0;
                if(!empty($datasub)){
                    foreach ($datasub as $v) {
                        $subnum += $v['quotaed'];
                    }
                }
                dump($curnum);
                dump($subnum);

                $usednum = $curnum + $subnum;

                //$ndata['usednum'] = $usednum;
                //dump( M("max")->getLastSql() );   
                //$opidNum;        
            }
            break;
        case 3:
            $opidList = M('admain') -> where(array('manid' => $params)) -> field('opid') -> select();
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
                $curnum = M('user') -> where($map) -> count();  
                $subnum = 0;
                $where['fmanid'] = array('in',$tNumList);  //查询所有0级下的设备
                $datasub = M('max') -> where($where) -> field('quotaed') -> select(); //本身登录的域管理员下的设备
                if(!empty($datasub)){
                    foreach ($datasub as $v) {
                        $subnum += $v['quotaed'];
                    }
                }else{
                    $subnum = 0;
                }
                $usednum = $curnum + $subnum;  
                //$ndata['usednum'] = $usednum;    
            }

            break;
        case 4:
            $opidList = M('admain') -> where(array('manid' => $params)) -> field('opid') -> select();
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
                $curnum = M('user') -> where($map) -> count();

                $where['fmanid'] = array('in',$tNumList);  //查询所有0级下的设备
                $datasub = M('max') -> where($where) -> field('quotaed') -> select(); //本身登录的域管理员下的设备
                $subnum = 0;
                if(!empty($datasub)){
                    foreach ($datasub as $v) {
                        $subnum += $v['quotaed'];
                    }
                }
                $usednum = $curnum + $subnum;
            }
            break;
        }

        if ($maxuser >= $usednum)
        {
            $validuser = $maxuser - $usednum;

        }
        else
        {
            $validuser = 0;
        }
        //dump($ndata);
        $ndata['maxuser'] = $maxuser;  
        $ndata['usednum'] = $usednum;  
        $ndata['validuser'] = $validuser; 
        return $ndata;
    }

/**
 * 查询记录的分控以及域管理员名称
 * @param [string] $blAd 
 */
function Names($params) {
    extract($params);
    $resAd = M('admain') -> where(array('opid' => $params)) -> field('opname') -> find();
    if (!empty($resAd)) {
        $blAd = $resAd['opname'];
    }
    echo $blAd;
}


/**
 * 使用额度的进度条数量
 */
function quoteNums($params) {
    extract($params);
    $ptData = M('max') -> where(array('opid' => $params)) -> field('quota, quotaed') -> find();
    if(!empty($ptData)){
        $quota = $ptData['quota'];
        $quotaed = $ptData['quotaed'];
        echo $pt = ceil(( $quotaed / $quota ) * 100);
    }else{
        $quota = 0;
        $quotaed = 0;
        echo $pt = 0;
    }
}

/**
 * 未使用额度数量
 */
function vquote($params) {
    extract($params);
    $ptData = M('max') -> where(array('opid' => $params)) -> field('quota, quotaed') -> find();
    if(!empty($ptData)){
        $quota = $ptData['quota'];
        $quotaed = $ptData['quotaed'];
        echo $vquote = $quota - $quotaed;
    }else{
        $quota = 0;
        $quotaed = 0;
        echo $vquote = 0;
    }
}

/**
 * 总额度数量
 */
function quote($params) {
    extract($params);
    $ptData = M('max') -> where(array('opid' => $params)) -> field('quota, quotaed') -> find();
    if(!empty($ptData)){
        echo $quota = $ptData['quota'];
    }else{
        echo $quota = 0;

    }
}
/**
 * 已使用额度数量
 */
function quotaed($params) {
    extract($params);
    $ptData = M('max') -> where(array('opid' => $params)) -> field('quota, quotaed') -> find();
    if(!empty($ptData)){
        echo $quotaed = $ptData['quotaed'];
    }else{
        echo  $quotaed = 0;
    }
}

/**
 * 授权终端数
 */
function maxuser($params) {
    extract($params);
    $List = M('admain') -> where(array('opid' => $params)) -> field('maxuser') -> find();
    //$ptData = M('max') -> where(array('opid' => $params)) -> field('quota, quotaed') -> find();
    if(!empty($List)){
        echo $maxuser = $List['maxuser'];
    }else{
        echo $maxuser = 0;
    }
}



?>