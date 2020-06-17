<?php
namespace Admin\Controller;
use Think\Controller;
use \Org\Net\Http;
use Think\AjaxPage;

class IndexController extends BaseController
{

    public function test()
    {
        $b = $this->back_url;
        dump($b);
        $dl = $this->de_li_url;
        dump($dl);


        $url = $_SERVER['SCRIPT_NAME']; //根目录文件夹部分
        dump($url);
        //var_dump(strrpos($url, 'x', -1));
        dump(substr_count($url, '/'));

        $ser = $_SERVER;
        dump($ser);
    }

}









    
    



    
    
