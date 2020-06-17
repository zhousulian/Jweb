<?php
/**
 *  测试 可删除
 * 
 */
function small_numbers()
{
    $num = array();
    $zero = 0;
    $one = 1;
    $two = 2;
    $num['zero'] = $zero;
    $num['one'] = $one;
    $num['two'] = $two;
    return $num;
}
/**
 *  测试 可删除
 *
 */
function  test($opid){
    $numData = array();
    $validuser = 0;
    $usednum = 0;
    $dbution = 0;
   
    $nData = M('admain') -> where(array('opid' => $opid)) -> field('maxuser,level') -> find();
    
    //最大
    if (!empty($nData))
    {
        $maxuser = $nData['maxuser'];
        $numData['maxuser'] = $maxuser; // 将要输出的数据放进一个数组里
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
        $numData['dbution'] = $dbution;
        
    }else {
        $numData['dbution'] = 0;
    }
   
    //echo $dbution;
    
    //未分配
    $validuser = $maxuser - $dbution;
    $numData['validuser'] = $validuser;
    switch ($nLevel)
    {
        case 0:
        case 1:
             $usednum = M('user') -> where(array('manid' => $opid)) -> count();
             $numData['usednum'] = $usednum;
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
            //echo $usednum;
            $numData['usednum'] = $usednum;
    
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
            //echo ($usednum);
            $numData['usednum'] = $usednum;
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
            //echo ($usednum);
            $numData['usednum'] = $usednum;   
            break;
    }
    
    return $numData;
    
}