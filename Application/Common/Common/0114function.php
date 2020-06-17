<?php
//  ¼ÓÔØcheckedbox ´¦Àíº¯ÊýÎÄ¼þ

/**
 * Éè±¸ÓÃ»§¹¦ÄÜ´ò¹³´æ´¢
 * @POC_FUNC_FRIEND ºÃÓÑ  ÒÔ´ËÀàÍÆ ¶ÔÓ¦µÄ¸´Ñ¡¿òÄÚÈÝ
 */
define(POC_FUNC_FRIEND ,    (1<<0));
define(POC_FUNC_QUERY_LOC,  (1<<1)); //²éÎ»
define(POC_FUNC_RECORD,     (1<<2));  //¼ÇÂ¼
define(POC_FUNC_LISTEN,     (1<<3));
define(POC_FUNC_RE_SHUT,    (1<<4)); //Ò£±Õ
define(POC_FUNC_GROUP_CALL, (1<<5));  //Èººô
define(POC_FUNC_NO_CALL,    (1<<6));
define(POC_FUNC_INVATE,     (1<<7));  //µ¥ºô
define(POC_FUNC_CHG_GROUP,  (1<<8)); //»»×é
define(POC_FUNC_LOCATION,   (1<<9));
define(POC_FUNC_AUD_RECORD, (1<<10));  //Â¼Òô
define(POC_FUNC_DIS_GROUP,  (1<<11));
define(POC_FUNC_LAST_GROUP, (1<<12));
define(POC_FUNC_NOT_INVATE, (1<<13)); //µ¥ºôÎðÈÅ


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
 * ²éÑ¯ÐøÔ¼¶©µ¥µÄÕËºÅ¸öÊý
 */
function xyUserCount($blid){
    echo $xuzLisr = M("bk_item")-> where(array('biblid' => $blid) ) ->count(); 
}
/**
 * Èº×é³ÉÔ±ÁÐ±íÅÅÐò
 * @param  [array] $arrays     [ÅÅÐòµÄ¶þÎ¬Êý×é]
 * @param  [string] $sort_key   [ÅÅÐòµÄ¼ü]
 * @param  [string] $sort_order [ÅÅÐòÉý½µÀàÐÍ]
 * @param  [string] $sort_type  [ÒÔÊ²Ã´ÀàÐÍÅÅÐò]
 * @return [array]             [·µ»ØÅÅÐòºóµÄÊý×é]
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
 * µ¼Èëexcel ¼ì²âÉè±¸ÃÜÂë¸ñÊ½
 */
function icheckedpwd($pwd){
    if (!empty($pwd)) {  //µÚÒ»´ÎÊäÈëÃÜÂë
        $pattern = "/^[a-zA-Z0-9]\w{5,17}$/";   //ÖÁÉÙÒª°üº¬´óÐ¡Ð´×ÖÄ¸/Êý×Ö/_µÄÆäÖÐ2ÖÖ²ÅÆ¥ÅäÍ¨¹ý,²»ÄÜ°üº¬ ÆäËûÌØÊâ×Ö·û
        $patternlet= "/^[a-z]+$/";
        $patternLet= "/^[A-Z]+$/";
        $patternN= "/^[0-9]+$/";
        if(preg_match($patternlet,$pwd))   //Èç¹ûÖ»°üº¬Ð¡Ð´²»ÔÊÐí
        {
            return false;
        }
        elseif(preg_match($patternLet,$pwd))   //Èç¹ûÖ»°üº¬Ð´Ò»ÖÖ²»ÔÊÐí
        {
            return false;
        }
        elseif(preg_match($patternN,$pwd))   //Èç¹ûÖ»°üº¬Êý×ÖÒ»ÖÖ²»ÔÊÐí
        {
            return false;
        }
        elseif(!preg_match($pattern,$pwd))    //ÖÁÉÙÒª°üº¬´óÐ¡Ð´×ÖÄ¸/Êý×Ö/_µÄÆäÖÐ2ÖÖ²ÅÆ¥ÅäÍ¨¹ý
        {
            return false;
        }else{
            return true;
        }
    }
}


/**
 * ¼ÆËãÎ´µ½ÆÚÖÕ¶ËÊýÊý
 */
function getTotalValidNum($opid){
    $nLevel = M("admain") -> where(array('opid'=> $opid)) -> field('level') -> find();
    $nLevel = $nLevel['level'];
    $where = array();
    /*$dataList = M('admain') ->where(array('manid' => $opid))  -> field('opid') -> select();
    $yNumList = array();
    $zNumList = array();
    $mNumList = array();
    $sNumList = array();
    $tNumList = array();

    if ($nLevel == 3) {  //Èç¹ûÊÇÏµÍ³¹ÜÀíÔ±£¬ÏÈ²éÑ¯·Ö¿Ø¹ÜÀíÔ±
        //·Ö¿Ø¹ÜÀíÔ±ID
        if (!empty($dataList))
        {
            //dump($dataList);//exit;
            foreach ($dataList as $key => $v) {
                $sOpid = $v['opid'];
                $sNumList[] = $sOpid;
            }
        }
    
        //Óò¹ÜÀíÔ±ID
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
                        $yNumList[] = $tOpid;
                    }

                }

            }
        }
        //dump($yNumList);
        //4¼¶¹ÜÀíÔ±
        if (!empty($yNumList))
        {
            //dump($tNumList);
            foreach ($yNumList as $key => $v) {
                //$tzOpid = $val['opid'];
                //dump($v);//exit;
                $yadList = M('admain') ->where(array('manid' => $v))  -> field('opid') -> select();
                if (!empty($yadList)) {
                    foreach ($yadList as $va) {
                        //dump($va);
                        $zNumList[] = $va['opid'];
                    }
                }
            }

        }

        //dump($zNumList);
        //Îå¼¶¹ÜÀíÔ±
        if (!empty($zNumList))
        {
            //dump($tNumList);
            foreach ($zNumList as $key => $v) {
                //$tzOpid = $val['opid'];
                //dump($v);//exit;
                $zadList = M('admain') ->where(array('manid' => $v))  -> field('opid') -> select();
                if (!empty($zadList)) {
                    foreach ($zadList as $key => $v) {
                        //dump($v);
                        $mNumList[] = $v['opid'];
                    }
                }
            }

        }

        $yzNumList = array_merge($yNumList,$zNumList);
        $tNumList = array_merge($yzNumList,$mNumList);
        //dump($yzNumList);

    }

    if ($nLevel == 2) {  //·Ö¿Ø¹ÜÀíÔ±
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
            if ($nLevel == 3) {  //Èç¹ûÊÇÏµÍ³¹ÜÀíÔ±£¬ÏÈ²éÑ¯·Ö¿Ø¹ÜÀíÔ±
                //·Ö¿Ø¹ÜÀíÔ±ID
                if (!empty($dataList))
                {
                    foreach ($dataList as $key => $v) {
                        $sOpid = $v['opid'];
                        $sNumList[] = $sOpid;
                    }
                }
            
                //1¼¶Óò¹ÜÀíÔ±ID
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
                //0¼¶¹ÜÀíÔ±
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
                //-1¼¶¹ÜÀíÔ±
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

                $rNumList = array_merge($txNumList,$zNumList); // °üÀ¨1¼¶0¼¶±¾ÉíµÄÉè±¸ºÍÃûÏÂ¹ÜÀíÔ±µÄÉè±¸
                $tNumList = array_merge($rNumList,$mNumList); 

            }

            if ($nLevel == 2) {  
                //1¼¶¹ÜÀíÔ±
                if (!empty($dataList))
                {
                    foreach ($dataList as $key => $v) {
                        $sOpid = $v['opid'];
                        $zNumList[] = $sOpid;
                    }
                }
            
                //0¼¶¹ÜÀíÔ±
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
                //-1¼¶¹ÜÀíÔ±
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

                $rNumList = array_merge($txNumList,$zNumList); // °üÀ¨1¼¶0¼¶±¾ÉíµÄÉè±¸ºÍÃûÏÂ¹ÜÀíÔ±µÄÉè±¸
                $tNumList = array_merge($rNumList,$mNumList); 

            }

            
            if ($nLevel == 1) {  
                //0¼¶¹ÜÀíÔ±
                if (!empty($dataList))
                {
                    foreach ($dataList as $key => $v) {
                        $sOpid = $v['opid'];
                        $mNumList[] = $sOpid;
                    }
                }
                
            
                //-1¼¶¹ÜÀíÔ±
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

                //$rNumList = array_merge($txNumList,$zNumList); // °üÀ¨1¼¶0¼¶±¾ÉíµÄÉè±¸ºÍÃûÏÂ¹ÜÀíÔ±µÄÉè±¸
                $tNumList = array_merge($txNumList,$mNumList); 
                $tNumList[] = $opid;   //¼ÓÉÏ1¼¶±¾ÉíµÄÉè±¸

            }

            if ($nLevel == 0) {  
                //-1¼¶¹ÜÀíÔ±
                if (!empty($dataList))
                {
                    foreach ($dataList as $key => $v) {
                        $sOpid = $v['opid'];
                        $mNumList[] = $sOpid;
                    }
                }
                
            
                //-1¼¶¹ÜÀíÔ±
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

                //$rNumList = array_merge($txNumList,$zNumList); // °üÀ¨1¼¶0¼¶±¾ÉíµÄÉè±¸ºÍÃûÏÂ¹ÜÀíÔ±µÄÉè±¸
                $tNumList[] = $opid; 
            }
            

    if ($nLevel == 1 || $nLevel == 0) {  //Óò¹ÜÀíÔ±
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
            $tNumList[] = $opid;
            $where['manid'] = array('in',$tNumList);
        }else{
            $where['manid'] = $opid;
        }
        $totalVlidNum = M('user') ->where($where) -> count();

    }else{
        //Éè±¸
        if (!empty($tNumList)){
            $now = date("Y-m-d");
            $where['validtime'] = array(array('GT', $now));
            $where['manid'] = array('in',$tNumList);
            $totalVlidNum = M('user') ->where($where)  -> count();
        }
    }

    echo M("user") -> getLastSql();
    if ($totalVlidNum > 0) {
        return  $totalVlidNum;
    }else{
        return  0;
    }

}

/**
 * ¶ÁÈ¡Éè±¸¹ÜÀíÔ±Ãû³Æ
 * $manid string  Éè±¸µÄmanid
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
 * ¼ÆËãÒÑ×¢²á¹ÜÀíÔ±Êý
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
 * ²éÑ¯Èº×éÁÐ±íµÄµ÷¶ÈÔ±ÕËºÅ
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
     * ±à¼­ºÃÓÑºóÐÞ¸ÄÓÃ»§ÓÑ¹¦ÄÜ
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
 * ÓÃ»§¹¦ÄÜ¸´Ñ¡¿òµÄÑ¡¶¨²éÑ¯·Ö½â
 */
function diliptChecked($checked){
    /*$checkedNum = array();
    if(CheckFuncCode(POC_FUNC_FRIEND, intval($checked))!==0)
    {
        echo "ºÃÓÑ&nbsp;&nbsp;";
        $checkedNum[] = 1;
        if(count($checkedNum) ==7){  //µ±Ñ¡Ôñ7¸öÒÔÉÏ»»ÐÐ
            echo "<br/>";
        }
    }
    if(CheckFuncCode(POC_FUNC_QUERY_LOC, intval($checked))!==0)
    {
        echo "²éÎ»&nbsp;&nbsp;";
        $checkedNum[] = 1;
        if(count($checkedNum) ==7){
            echo "<br/>";
        }
    }
    if(CheckFuncCode(POC_FUNC_RECORD, intval($checked))!==0)
    {
        echo "¼ÇÂ¼&nbsp;&nbsp;";
        $checkedNum[] = 1;
        if(count($checkedNum) ==7){
            echo "<br/>";
        }
    }
    if(CheckFuncCode(POC_FUNC_LISTEN, intval($checked))!==0)
    {
        echo "¼àÌý&nbsp;&nbsp;";
        $checkedNum[] = 1;
        if(count($checkedNum) ==7){
            echo "<br/>";
        }
    }  
    if(CheckFuncCode(POC_FUNC_RE_SHUT, intval($checked))!==0)
    {
        echo "Ò£±Õ&nbsp;&nbsp;";
        $checkedNum[] = 1;
        if(count($checkedNum) ==7){
            echo "<br/>";
        }
    }
    if(CheckFuncCode(POC_FUNC_GROUP_CALL, intval($checked))!==0)
    {
        echo "Èººô&nbsp;&nbsp;";
        $checkedNum[] = 1;
        if(count($checkedNum) ==7){
            echo "<br/>";
        }
    }
    if(CheckFuncCode(POC_FUNC_NO_CALL, intval($checked))!==0)
    {
        echo "½ûºô&nbsp;&nbsp;";
        $checkedNum[] = 1;
        if(count($checkedNum) ==7){
            echo "<br/>";
        }
    }
    if(CheckFuncCode(POC_FUNC_INVATE, intval($checked))!==0)
    {
        echo "µ¥ºô&nbsp;&nbsp;";
        $checkedNum[] = 1;
        if(count($checkedNum) ==7){
            echo "<br/>";
        }
    }
    if(CheckFuncCode(POC_FUNC_CHG_GROUP, intval($checked))!==0)
    {
        echo "»»×é&nbsp;&nbsp;";
        $checkedNum[] = 1;
        if(count($checkedNum) ==7){
            echo "<br/>";
        }
    }
    if(CheckFuncCode(POC_FUNC_LOCATION, intval($checked))!==0)
    {
        echo "¶¨Î»&nbsp;&nbsp;";
        $checkedNum[] = 1;
        if(count($checkedNum) ==7){
            echo "<br/>";
        }
    }
    if(CheckFuncCode(POC_FUNC_AUD_RECORD, intval($checked))!==0)
    {
        echo "Â¼Òô&nbsp;&nbsp;";
        $checkedNum[] = 1;
        if(count($checkedNum) ==7){
            echo "<br/>";
        }
    }
    if(CheckFuncCode(POC_FUNC_DIS_GROUP, intval($checked))!==0)
    {
        echo "ÏÔ×é&nbsp;&nbsp;";
        $checkedNum[] = 1;
        if(count($checkedNum) ==7){
            echo "<br/>";
        }
    }
    if(CheckFuncCode(POC_FUNC_LAST_GROUP, intval($checked))!==0)
    {
        echo "×îºó×é&nbsp;&nbsp;";
        $checkedNum[] = 1;
        if(count($checkedNum) ==7){
            echo "<br/>";
        }
    }
    if(CheckFuncCode(POC_FUNC_NOT_INVATE, intval($checked))!==0)
    {
        echo "µ¥ºôÎðÈÅ";
        $checkedNum[] = 1;
        if(count($checkedNum) ==7){
            echo "<br/>";
        }
    } */
    
    $strArr = array();
    if(CheckFuncCode(POC_FUNC_FRIEND, intval($checked))!==0)
    {
        $strArr['fri'] = "ºÃÓÑ";
    }
    if(CheckFuncCode(POC_FUNC_QUERY_LOC, intval($checked))!==0)
    {
        $strArr['qloc'] = "²éÎ»";
    }
    if(CheckFuncCode(POC_FUNC_RECORD, intval($checked))!==0)
    {
        $strArr['rec'] = "¼ÇÂ¼";
    }
    if(CheckFuncCode(POC_FUNC_LISTEN, intval($checked))!==0)
    {
        $strArr['list'] = "¼àÌý";
    }  
    if(CheckFuncCode(POC_FUNC_RE_SHUT, intval($checked))!==0)
    {
        $strArr['reshut'] = "Ò£±Ð";
    }
    if(CheckFuncCode(POC_FUNC_GROUP_CALL, intval($checked))!==0)
    {
        $strArr['gcall'] = "Èººô";        
    }
    if(CheckFuncCode(POC_FUNC_NO_CALL, intval($checked))!==0)
    {
        $strArr['nocall'] = "½ûºô";
    }
    if(CheckFuncCode(POC_FUNC_INVATE, intval($checked))!==0)
    {
        $strArr['invate'] = "µ¥ºô"; 
        
    }
    if(CheckFuncCode(POC_FUNC_CHG_GROUP, intval($checked))!==0)
    {
        $strArr['change_g'] = "»»×é";
    }
    if(CheckFuncCode(POC_FUNC_LOCATION, intval($checked))!==0)
    {
        $strArr['loc'] = "¶¨Î»";
    }
    if(CheckFuncCode(POC_FUNC_AUD_RECORD, intval($checked))!==0)
    {
        
        $strArr['vrecd'] = "Â¼Òô";
    }
    if(CheckFuncCode(POC_FUNC_DIS_GROUP, intval($checked))!==0)
    {
        $strArr['disg'] = "ÏÔ×é";        
    }
    if(CheckFuncCode(POC_FUNC_LAST_GROUP, intval($checked))!==0)
    {
        $strArr['lastg'] = "×îºó×é";       
    }
    if(CheckFuncCode(POC_FUNC_NOT_INVATE, intval($checked))!==0)
    {
        $strArr['ninvate'] = "µ¥ºôÎðÈÅ";
    }
    
    $strArr = implode(",",$strArr);  //½«Êý×é×ªÎª×Ö·û´®
    return $strArr;
    
}
/**
 * ·ÖÒ³ÉèÖÃ
 */
function pagelist($Page){
	$Page->setConfig('header', '<li class="rows">&nbsp;¹²<b>%TOTAL_ROW%</b>Ìõ¼ÇÂ¼&nbsp;µÚ<b>%NOW_PAGE%</b>Ò³/¹²<b>%TOTAL_PAGE%</b>Ò³&nbsp; µ½ <input  id="npage_num" style="" type="text" /> Ò³ </li> <p class="qd" ><a id="npage_qd" >È·¶¨</a></p>');
    $Page->setConfig('prev', 'ÉÏÒ»Ò³');    
    $Page->setConfig('next', 'ÏÂÒ»Ò³');
    $Page->setConfig('last', 'Î²Ò³');
    $Page->setConfig('first', 'Ê×Ò³');    
    $Page->setConfig('theme', '%FIRST%%UP_PAGE%%LINK_PAGE%%DOWN_PAGE%%END%%HEADER%');
    $Page->lastSuffix = false;//×îºóÒ»Ò³²»ÏÔÊ¾Îª×ÜÒ³Êý
} 

/**
 * ½ØÈ¡¹ÜÀíÔ±ÁÐ±í±¸×¢×Ö·û´®
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
 * udp·¢ËÍÊý¾Ý
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

function getNums($params)  //¶þ¼¶¹ÜÀíÔ±Ìí¼Ó¶©µ¥ÁÐ±íµÄÎ´×¢²áÖÕ¶ËÊý
{
    extract($params);

    $validuser = 0;
    $usednum = 0;
    $dbution = 0;
    $x = 0;
    $adRow = M('admain') -> where(array('opid' => $params)) -> field('maxuser,level') -> find();
    //×î´ó
    if (!empty($adRow))
    {
        $maxuser = $adRow['maxuser'];
        $nLevel = $adRow['level'];
    }
        
    //ÒÑ·ÖÅä
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
    

    //Î´·ÖÅä
    if ($maxuser >= $dbution1)
    {
        echo $undbution = $maxuser - $dbution1;
    }
    else
    {
        echo $undbution = 0;
    }


}

/**
 * ¼ÆËãÃ¿Ìõ¹ÜÀíÔ±¼ÇÂ¼µÄ¶î¶È
 */
function adRowNums($opid)  //½«opid´«½ø
{
    $validuser = 0;
    $usednum = 0;
    $dbution = 0;
    $x = 0;
    $adRow = M('admain') -> where(array('opid' => $opid)) -> field('maxuser,level') -> find();
    //×î´ó
    if (!empty($adRow))
    {
        $maxuser = $adRow['maxuser'];
        $nLevel = $adRow['level'];
    }
        
    //ÒÑ·ÖÅä
    $usedList = M('admain') -> where(array('manid' => $opid)) -> field('maxuser') -> select();

    if (!empty($usedList))
    {
        $dbution = 0;
        foreach ($usedList as $v) {
            $dbution += $v['maxuser'];
        }
    }

    //Î´·ÖÅä
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
            $usedcnt = M('user') -> where(array('manid' => $opid)) -> field('maxuser') -> count();
            $opidNum = M('admain') -> where(array('manid' => $opid,"level"=>0)) -> field('maxuser') -> select();
            //Óò¹ÜÀíÔ±ID
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
            $usedcnt = M('user') -> where(array('manid' => $opid)) -> field('maxuser') -> count();
            $opidNum = M('admain') -> where(array('manid' => $opid,"level"=>0)) -> field('maxuser') -> select();
            //Óò¹ÜÀíÔ±ID
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

            //Óò¹ÜÀíÔ±ID
            if (!empty($opidNum))
            {
                $usednum = 0;
                foreach ($opidNum as $v) {
                    $usednum += $v['maxuser'];
                }
            }
            break;
        
    }

    //  Î´×¢²áÖÕ¶ËÊý
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
 * ¼ÆËãÓÃ»§ÊýÁ¿
 */
/*function usedNum($opid){
	    $validuser = 0;
        $usednum = 0;
        $dbution = 0;
        $nData = M('admain') -> where(array('opid' => $opid)) -> field('maxuser,level') -> find();

        //×î´ó
        if (!empty($nData))
        {
            $maxuser = $nData['maxuser'];
            $nLevel = $nData['level'];
        }
        //ÒÑ·ÖÅä
        $muserData = M('admain') -> where(array('manid' => $opid)) -> field('maxuser') -> select();
        //echo M('admain') -> getLastSql(); 

        if (!empty($muserData))
        {
            foreach ($muserData as  $value) {
                $dbution += $value['maxuser'];
            }
        }

        //Î´·ÖÅä
        $validuser = $maxuser - $dbution;
        switch ($nLevel)
        {
            case 0:
            case 1:
                $unum = 0;
                $curnum = M('user') -> where(array('manid' => $opid)) -> count();
                $userList = M('admain') -> where(array('manid' => $opid)) -> field('opid') -> select();

                $tNumList = array();
                //Óò¹ÜÀíÔ±ID
                if (!empty($userList))
                {
                    foreach ($userList as  $value) {
                        $tOpid = $value['opid'];
                        $tNumList[] = $tOpid;
                    }
                }
                //ÒÑ×¢²á
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
                //Óò¹ÜÀíÔ±ID
                if (!empty($userList))
                {
                    foreach ($userList as  $value) {
                        $tOpid = $value['opid'];
                        $tNumList[] = $tOpid;
                    }
                }
                //ÒÑ×¢²á
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
                //·Ö¿Ø¹ÜÀíÔ±ID
                if (!empty($adList))
                {
                   foreach ($adList as  $value) {
                        $sOpid = $value['opid'];
                        $sNumList[] = $sOpid;
                    }
                }
                //Óò¹ÜÀíÔ±ID
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
                //ÒÑ×¢²áÊýÁ¿
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
                //ÏµÍ³¹ÜÀíÔ±ID
                if (!empty($adList1))
                {
                    foreach ($adList1 as  $val) {
                        $rOpid = $val['opid'];
                        $rNumList[] = $rOpid;
                    }
                }
                //·Ö¿Ø¹ÜÀíÔ±ID
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
                //Óò¹ÜÀíÔ±ID
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
                //ÒÑ×¢²áÊýÁ¿
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
 * ²éÑ¯¼ÇÂ¼µÄ¶î¶È
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
 * ¼ÆËã¶î¶ÈÊýÁ¿
 * @param  $params 
 */
function Nums($params)  //½«opid´«½ø
{
    $validuser = 0;
    $usednum = 0;
    $dbution = 0;
    $x = 0;
    $adRow = M('admain') -> where(array('opid' => $params)) -> field('maxuser,level') -> find();
    //×î´ó
    if (!empty($adRow))
    {
        $maxuser = $adRow['maxuser'];
        $nLevel = $adRow['level'];
    }
        
    //ÒÑ·ÖÅä
   /* $usedList = M('admain') -> where(array('manid' => $params)) -> field('maxuser') -> select();

    if (!empty($usedList))
    {
        $dbution = 0;
        foreach ($usedList as $v) {
            $dbution += $v['maxuser'];
        }
    }

    //Î´·ÖÅä
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
            $usednum = $curnum + $subnum; //±¾ÉíµÄÉè±¸¼ÓÉÏ¹ÜÀíÔ±ÏÂµÄÉè±¸
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
            //Óò¹ÜÀíÔ±ID
            if (!empty($opidNum))
            {
                $tNumList = array();
                foreach ($opidNum as $v) {
                    $tOpid = $v['opid'];
                    $tNumList[] = $tOpid;
                }
            }

            //ÒÑ×¢²á
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
            //·Ö¿Ø¹ÜÀíÔ±ID
            if (!empty($opidList))
            {
                foreach ($opidList as $v) {
                    $sOpid = $v['opid'];
                    $sNumList[] = $sOpid;
                }
            }
            //Óò¹ÜÀíÔ±ID
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
            //ÒÑ×¢²áÊýÁ¿
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
            //ÏµÍ³¹ÜÀíÔ±ID
            if (!empty($opidList))
            {
                foreach ($opidList as $v) {
                    $rOpid = $v['opid'];
                    $rNumList[] = $rOpid;
                }
            }
            //·Ö¿Ø¹ÜÀíÔ±ID
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
            //Óò¹ÜÀíÔ±ID
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
            //ÒÑ×¢²áÊýÁ¿
            if (!empty($tNumList))
            {
                $map['manid'] = array('in',$tNumList);
                $usednum = M('user') -> where($map) -> count();
                $x = 1; 
                
            }
            break;
    }
    $x = 2; 
    //  Î´×¢²áÖÕ¶ËÊý
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
 * ¼ÆËã¶î¶ÈÊýÁ¿
 * @param  $params 
 */
function dbtailsNums($params)  //½«opid´«½ø
{
    $validuser = 0;
    $usednum = 0;
    $dbution = 0;
    $x = 0;
    $adRow = M('admain') -> where(array('opid' => $params)) -> field('maxuser,level') -> find();
    //×î´ó
    if (!empty($adRow))
    {
        $maxuser = $adRow['maxuser'];
        $nLevel = $adRow['level'];
    }
        
    //ÒÑ·ÖÅä
    $usedList = M('admain') -> where(array('manid' => $params)) -> field('maxuser') -> select();

    if (!empty($usedList))
    {
        $dbution = 0;
        foreach ($usedList as $v) {
            $dbution += $v['maxuser'];
        }
    }

    //Î´·ÖÅä
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
            //Óò¹ÜÀíÔ±ID
            if (!empty($opidNum))
            {
                $tNumList = array();
                foreach ($opidNum as $v) {
                    $tOpid = $v['opid'];
                    $tNumList[] = $tOpid;
                }
            }

            //ÒÑ×¢²á
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
            //·Ö¿Ø¹ÜÀíÔ±ID
            if (!empty($opidList))
            {
                foreach ($opidList as $v) {
                    $sOpid = $v['opid'];
                    $sNumList[] = $sOpid;
                }
            }
            //Óò¹ÜÀíÔ±ID
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
            //ÒÑ×¢²áÊýÁ¿
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
            //ÏµÍ³¹ÜÀíÔ±ID
            if (!empty($opidList))
            {
                foreach ($opidList as $v) {
                    $rOpid = $v['opid'];
                    $rNumList[] = $rOpid;
                }
            }
            //·Ö¿Ø¹ÜÀíÔ±ID
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
            //Óò¹ÜÀíÔ±ID
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
            //ÒÑ×¢²áÊýÁ¿
            if (!empty($tNumList))
            {
                $map['manid'] = array('in',$tNumList);
                $usednum = M('user') -> where($map) -> count();
                
            }
            break;
    }
    //  Ê¹ÓÃ°Ù·ÖÊý
    if ($usednum != 0)
    {
        echo $pt = ceil(( $usednum / $maxuser ) * 100);

    }
    else
    {
        echo $pt = 0;
    }

}

function heaerNums($params)  //½«opid´«½ø
{
    $validuser = 0;
    $usednum = 0;
    $dbution = 0;
    $adRow = M('admain') -> where(array('opid' => $params)) -> field('maxuser,level') -> find();
    //×î´ó
    if (!empty($adRow))
    {
        $maxuser = $adRow['maxuser'];
        $nLevel = $adRow['level'];
    }
        
    //ÒÑ·ÖÅä
    /*$usedList = M('admain') -> where(array('manid' => $params)) -> field('maxuser') -> select();

    if (!empty($usedList))
    {
        $dbution = 0;
        foreach ($usedList as $v) {
            $dbution += $v['maxuser'];
        }
    }

    //Î´·ÖÅä
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
            $usednum = $curnum + $subnum; //±¾ÉíµÄÉè±¸¼ÓÉÏ¹ÜÀíÔ±ÏÂµÄÉè±¸
            //return $ndata['usednum'] = $usednum;
            break;
        case 1:
            $data = M('max') -> where(array('opid' => $params)) -> field('quotaed') -> find(); //±¾ÉíµÇÂ¼µÄÓò¹ÜÀíÔ±ÏÂµÄÉè±¸
            if(!empty($data)){
                $curnum = $data['quotaed'];
            }else{ 
                $curnum = M('user') -> where(array('manid' => $params))  -> count(); //±¾ÉíµÇÂ¼µÄÓò¹ÜÀíÔ±ÏÂµÄÉè±¸
            }
            //dump( M("max")->getLastSql() );   
            //dump($curnum);
            $subnum = 0;
            $datasub = M('max') -> where(array('fmanid' => $params)) -> field('quotaed') -> select(); //±¾ÉíµÇÂ¼µÄÓò¹ÜÀíÔ±ÏÂµÄÉè±¸
            if(!empty($datasub)){
                foreach ($datasub as $v) {
                    $subnum += $v['quotaed'];
                }
            }
            $usednum = $curnum + $subnum; //±¾ÉíµÄÉè±¸¼ÓÉÏ¹ÜÀíÔ±ÏÂµÄÉè±¸
            //return $ndata['usednum'] = $usednum;
            break;
        case 2:
            $opidNum = M('admain') -> where(array('manid' => $params)) -> field('opid') -> select(); // ²éÑ¯Ò»¼¶µÄ¹ÜÀíÔ±
            //Óò¹ÜÀíÔ±ID
            if (!empty($opidNum))
            {
                $tNumList = array();
                //$subNumList = array();
                foreach ($opidNum as $v) {
                    $tOpid = $v['opid'];
                    $tNumList[] = $tOpid;
                }
            }

            //ÒÑ×¢²á
            if (!empty($tNumList))
            {
                $map['manid'] = array('in',$tNumList);
                $curnum = M('user') -> where($map) -> count(); //1¼¶×ÔÉí×¢²áµÄÊýÁ¿
                //dump( M("user")->getLastSql() ); 
                //dump($curnum);//exit
                $where['fmanid'] = array('in',$tNumList);  //²éÑ¯ËùÓÐ0¼¶ÏÂµÄÉè±¸
                $datasub = M('max') -> where($where) -> field('opid,quotaed') -> select(); //±¾ÉíµÇÂ¼µÄÓò¹ÜÀíÔ±ÏÂµÄÉè±¸
                //dump($datasub);
                $subnum = 0;
                if(!empty($datasub)){
                    foreach ($datasub as $v) {
                        $subnum += $v['quotaed'];
                    }
                }
                //dump($curnum);
                //dump($subnum);

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
            //·Ö¿Ø¹ÜÀíÔ±ID
            if (!empty($opidList))
            {
                foreach ($opidList as $v) {
                    $sOpid = $v['opid'];
                    $sNumList[] = $sOpid;
                }
            }
            //Óò¹ÜÀíÔ±ID
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
            //ÒÑ×¢²áÊýÁ¿
            if (!empty($tNumList))
            {
                $map['manid'] = array('in',$tNumList);
                $curnum = M('user') -> where($map) -> count();  
                $subnum = 0;
                $where['fmanid'] = array('in',$tNumList);  //²éÑ¯ËùÓÐ0¼¶ÏÂµÄÉè±¸
                $datasub = M('max') -> where($where) -> field('quotaed') -> select(); //±¾ÉíµÇÂ¼µÄÓò¹ÜÀíÔ±ÏÂµÄÉè±¸
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
            //ÏµÍ³¹ÜÀíÔ±ID
            if (!empty($opidList))
            {
                foreach ($opidList as $v) {
                    $rOpid = $v['opid'];
                    $rNumList[] = $rOpid;
                }
            }
            //·Ö¿Ø¹ÜÀíÔ±ID
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
            //Óò¹ÜÀíÔ±ID
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
            //ÒÑ×¢²áÊýÁ¿
            if (!empty($tNumList))
            {
                $map['manid'] = array('in',$tNumList);
                $curnum = M('user') -> where($map) -> count();

                $where['fmanid'] = array('in',$tNumList);  //²éÑ¯ËùÓÐ0¼¶ÏÂµÄÉè±¸
                $datasub = M('max') -> where($where) -> field('quotaed') -> select(); //±¾ÉíµÇÂ¼µÄÓò¹ÜÀíÔ±ÏÂµÄÉè±¸
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
 * ²éÑ¯¼ÇÂ¼µÄ·Ö¿ØÒÔ¼°Óò¹ÜÀíÔ±Ãû³Æ
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
 * Ê¹ÓÃ¶î¶ÈµÄ½ø¶ÈÌõÊýÁ¿
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
 * Î´Ê¹ÓÃ¶î¶ÈÊýÁ¿
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
 * ×Ü¶î¶ÈÊýÁ¿
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
 * ÒÑÊ¹ÓÃ¶î¶ÈÊýÁ¿
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
 * ÊÚÈ¨ÖÕ¶ËÊý
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