<?php
namespace Admin\Model;
use Think\Model;
class AdmainModel extends Model {
	protected $_validate = array(
        array('opname','require','管理员名称不能为空！'), //默认情况下用正则进行验证
        array('opname','','管理员名称已经存在,请从新添加！',0,'unique',1), // 在新增的时候验证字段是否唯一
        array('password','require','密码不能为空！'), // 在新增的时候验证字段是否必须
	);
	public function login($name,$pass){
		$map['opname'] = $name; 
		$row = $this-> where($map) -> find();
		if ($row['password'] == $pass) {
				$_SESSION['LogUser'] = $row['opname'];
                $_SESSION['LogOpid'] = $row['opid'];
                $_SESSION["LogLevel"] =  $row['level'];
                $_SESSION["Manid"] =  $row['manid'];
                $_SESSION["MaxUser"] =  $row['maxuser'];
                /*session("LogUser",$lrow['opname']);
                session("LogOpid",$lrow['opid']);
                session("LogLevel",$lrow['level']);
                session("Manid",$lrow['manid']);
                session("MaxUser",$lrow['maxuser']);*/
                $lrow = M("admain") -> where(array('opid'=> $row['manid'])) -> field('level') -> find();
                if ($lrow) {
                    session("upLevel",$lrow['level']);
                    //$_SESSION["upLevel"] = $lrow['level'];
                }
				return true;
		}else{
			return false;
		}
	}

	public function islogin(){
		return  (isset($_SESSION['LogOpid'] ) && $_SESSION['LogOpid']>0 );
	}

	//二级管理登录验证
	public function sublogin($name,$pass){
        $map['opname'] = $name; 
        $row = D("admain")-> where($map) -> find();
        if ($row['password'] == $pass) {
                $_SESSION['subLogUser'] = $row['opname'];
                $_SESSION['subLogOpid'] = $row['opid'];
                $_SESSION["subLogLevel"] =  $row['level'];
                $_SESSION["manid"] =  $row['manid'];

                return true;
        }else{
            return false;
        }
    }

    public function issublogin(){
		return  (isset($_SESSION['subLogOpid']) && $_SESSION['subLogOpid']>0 );
	}


}