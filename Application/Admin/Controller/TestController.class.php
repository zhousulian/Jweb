<?php
namespace Admin\Controller;
use Think\Controller;
class TestController extends BaseController {

    public function group_add(){
        $opid = 19;
        for ($i=1; $i <= 660; $i++) { 
            $group = M('group');
            $date = date("Y-m-d H:i:s");
            $addData['gname'] = "0402测试组".$i;
            $addData['gnote'] = "测试多群组添加速度";
            $addData['createtime'] = $date;
            $addData['enable'] = '1';
            $addData['manid'] = $opid;
            //dump($addData);
            $gid = $group -> add($addData);
            $selVal = "471876|471875|471874|471873|471872|471871|471870|471869|471868|471867|471866|471865|471864|471863|471862|471861|471860|471859|471858|471857|471856|471855|471854|471853|471852|471851|471850|471849|471848|471847|471846|471845|471844|471843|471842|471841|471840|471839|471838|471837|471836|471835|471834|471833|471832|471831|471830|471829|471828|471827|471826|471825|471824|471823|471822|471821|471820|471819|471818|471817|471816|471815|471814|471813|471812|471811|471810|471809|471808|471807|471806|471805|471804|471803|471802|471801|471800|471799|471798|471797|471796|471795|471794|471793|471792||";
            $lstUser = explode('|',$selVal);
            //dump($lstUser);
            $result = array();
            for ($k = 1; $k < count($lstUser) - 1; $k++) { 
                $addData1['uid'] = $lstUser[$k];
                $addData1['gid'] = $gid;
                $res = M('user_group') -> add($addData1);   
            }

        }
        
        if($res !== false){
            echo "添加群组成功！";
            exit();
        }else{
            echo "添加群组错误！";
            exit();
        }
        

    }

	public function test(){
        $sNumList = array('a','b','c');
        $tNumList = array('e','wdsdfsf','g');
        $arr = array('h','2i','jdsfsd');
        $opidL = array_merge($sNumList, $tNumList,$arr);
        //dump($opidL);//exit();

		$gid = 74688;
		$gtype = M('group') -> where(array('gid'=>$gid)) -> field('type') -> find();
        $gtype = $gtype['type'];
        echo $gtype;
        echo M("group") -> getLastSql();
        exit;
		//echo 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		$opid = session("LogOpid");
		$data = M("user")-> where("manid=".$opid)->select();
		$goods_category = $this->convert_arr_key($data, 'uid');
		dump($goods_category);
	}
	function convert_arr_key($arr, $key_name)
	{
		$arr2 = array();
		foreach($arr as $key => $val){
			$arr2[$val[$key_name]] = $val;        
		}
		return $arr2;
	}

    //tyt_new device_add()中的修改max 的其中一种方式
    /*$manidRow = M("admain")->where(array("opid"=>$manid))->field("manid,maxuser,level")->find();  //上级管理员信息
    $manid = $manidRow['manid'];
    $mlevel = $manidRow['level'];
    $maxuser = $manidRow['maxuser'];

    $maxdata = M("max")->where(array("opid"=>$opid))->find();
    if (!empty($maxdata)) {
        $map['quotaed'] = $maxdata['quotaed'] + 1;           
        $res = M("max")->where(array("opid"=>$opid))->save($map);
    }else{
        $maxuerRow = M("admain")->where(array("opid"=>$opid))->field("maxuser")->find();
        $map['opid'] = $opid;   
        $map['quota'] = $maxuerRow['maxuser']; //总额度
        $map['quotaed'] = $addCnt;  //添加的账号个数
        $map['manid'] = $iManid;   
        if ($mlevel !== 0) {      //如果上一级已经为1级域管理员
            $map['fmanid'] = $iManid;          
        }else{                    //如果上一级仍为0级管理员
            $map['fmanid'] = $manid;      //上一级的上一级的opid
        }           
        $res = M("max")->where(array("opid"=>$opid))->add($map);
    }*/

		
    //echo M('user_status')->getLastSql();

    public function index()
    {
        $count = Db::query("select count(id) as count from tb_role where is_delete =0");
        $tot = $count[0]['count']; //统计数据总条数
        // dump($tot);exit;
        $limit = 5; //每页显示多少条 
        $total = intval(ceil($tot/$limit)); //进一取整 计算多少页
        $paging = array();
        for ($i=0; $i <=$total; $i++) { 
            $paging[$i] = $i;
        }                              //页码
        $page = isset($_GET['page'])?$_GET['page']:"";
        if(empty($page)){
            $page = 1;
        } //前台传递过来的页码 
        $offset = ($page-1)*$limit; //偏移量
        $res = Db::query("select id,name,descr,status,is_delete from  tb_role limit $offset,$limit"); //SQL
        // dump($res);exit;
        if(request()->isAjax()){  //如果是AJAX请求的分页
 
            $this->assign('res',$res);
            $this->assign('title','权限管理');
            return $this->fetch('Role/indexAjax');
            exit;
        }
        //非Ajax请求
        $this->assign('paging',$paging);
        $this->assign('res',$res);
        $this->assign('title','权限管理');
        return view();
    }

    function dsfw(){
        //constraint foreign references
    }

}
?>

<?php if (!empty($_GET['p'])) {echo $_GET['p'];}else{echo 1;}?>
<?php empty($_GET['p']) ? $_GET['p']: 1;?>


<!-- dtts.html -->

<!-- <div class="inline pull-right page">
                                <div style="font:12px simsun, san-serif;">&nbsp;共<b> 3 </b>条记录 &nbsp;&nbsp;本页 <b>3</b> 条 &nbsp;&nbsp;本页从 <b>1-3</b> 条 &nbsp;&nbsp;<b>1/1</b> 页 &nbsp;&nbsp;<b></b>
                                </div>                            
                            </div> -->

                            <!-- <if condition="($list eq '') ">
                                 <if condition="($serType neq '') ">
                                     <p class="lp mt1"><a href ="__URL__/dtts?subOpid={$ropid}"> </a>&nbsp;&nbsp;&nbsp;
                                     无记录</p>
                                 <elseif condition="($iLevel lt $NowLevel) "/>
                                     <if condition="($NowLevel eq 3) OR ($NowLevel eq 0)">
                                         <p class="lp mt1"><a href ="__URL__/dtts?subOpid={$_SESSION['LogOpid']}"><<返回 </a>&nbsp;&nbsp;&nbsp;您未添加子账户</p>
                                     <else />
                                         <p class="lp mt1"><a href ="__URL__/dtts?subOpid={$manid}"><<返回 </a>&nbsp;&nbsp;&nbsp;
                                         您未添加子账户1</p>    
                                     </if>
                                 <else />
                                     <p class="lp mt1"></p>
                                 </if>
                             </if>  -->