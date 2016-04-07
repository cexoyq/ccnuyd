<?php
namespace Admin\Controller;
use Think\Controller;
class CmController extends Controller {
	public function _initialize(){//使用自动化，验证用户是否登陆
		if(session('user.id')){
			//echo "已经设置了用户ID";
		}
		else
		{
			$this->error('请登陆！',U('Login/login'));
			$this->redirect("Login/login");
			//用户只能通过学校门户网站认证后登陆，不允许通过LOGIN登陆了
			session(null);
			//echo "please close the page!</br>";
			//echo "<script type='text/javascript'>";
			//echo "window.close();";
			//echo "</script>";
		}
    }
	public function logout(){
		session(null);
		echo "please close the page!</br>";
                echo "<script type='text/javascript'>";
                echo "window.close();";
                echo "</script>";
		//$this->success('成功注销用户登陆信息!',U('Index/index'));
	}
}
