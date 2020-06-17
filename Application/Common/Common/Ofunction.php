<?php
/**
 * 分页设置
 */
function pagelist($Page){
	$Page->setConfig('header', '<li class="rows">共<b>%TOTAL_ROW%</b>条记录&nbsp;第<b>%NOW_PAGE%</b>页/共<b>%TOTAL_PAGE%</b>页</li>');
    $Page->setConfig('prev', '上一页');    
    $Page->setConfig('next', '下一页');
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

/**
 * 计算用户数量
 */
function usedNum($opid){
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
                echo $usednum = M('user') -> where(array('manid' => $opid)) -> count();
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
                    foreach ($tNumList as $v)
                    {
                        $unum = M('user') -> where(array('manid' => $v)) -> count();
                        $usednum += $unum;
                    }
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
                    foreach ($tNumList as $v)
                    {
                        $unum = M('user') -> where(array('manid' => $v)) -> count();
                        $usednum += $unum;
                    }
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
                    foreach ($tNumList as $v)
                    {
                        $unum = M('user') -> where(array('manid' => $v)) -> count();
                        $usednum += $unum;
                    }
                }
                echo ($usednum);
                break;
        }

	}

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

function getNums($params)  //二级管理员添加订单列表的未注册终端数
{
    extract($params);

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
        echo $undbution = $maxuser - $dbution;
    }
    else
    {
        echo $undbution = 0;
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
                $x = 1; 
                
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
            echo $usednum = M('user') -> where(array('manid' => $params)) -> field('maxuser') -> count();       
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
                echo $usednum = M('user') -> where($map) -> count();
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
                echo $usednum = M('user') -> where($map) -> count();        
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
                echo $usednum = M('user') -> where($map) -> count();
            }
            break;
        }
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