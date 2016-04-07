<?php
namespace Admin\Controller;
use Think\Controller;
class LoginController extends Controller {

    public function login(){
        $this->assign('result',$result);
        $this->display();
    }
	public function doLogin(){
		$user = $_POST['user'];
        $password = $_POST['password'];
        $DBuser = M('user');
        $res = $DBuser->where("user='" . $user . "' AND password='" . md5($password) . "'")->find();
        if ($res) {
            session('user', array(
                'id' => $res['id'],
                //'user' => $res['user'],
				'admin' => $res['admin'],
				'user' => $res['name']
			));
			//$this->assign('jumpUrl', .'/Index/index');
			$this->success('登陆OK!',U('Index/index'));
        } else {
		session(null);
			//echo "please close the page!</br>";
			//echo "<script type='text/javascript'>";
			//echo "window.close();";
			/*echo "</script>";*/
           	$this->error('Auth Error!',U('Login/login'));
        }
        }

	public function ccnu1(){
	//与门户网站对接
		header("Content-Type:text/html; charset=utf-8");
		
		$post_xm=iconv("gb2312","utf-8",I('post.xm'));
		$post1_xm=iconv("gb2312","utf-8",$_REQUEST["xm"]);
		$post_zjh=I('post.zjh');
		//dump(I('post.'));
		echo "</br> post.xm:$post_xm,post.zjh:$post_zjh,post1.xm:$post1_xm</br>";
		
		$zjh=I('get.zjh');
		$xm=iconv("gb2312","utf-8",$_REQUEST["xm"]);
		//dump(I('get.'));
		//echo "</br> xm:$xm,zjh:$zjh</br>";
		if($post1_xm && $post_zjh){
			$user=array('id'=>$post_zjh,'user'=>$post1_xm,'admin'=>0);
			if($post_zjh == '2006980764'){$user['admin']=1;}
			//金老师的ID号则敷以管理员权限
			session('user',$user);
			$this->success('auth ok!',U('/Admin/index'));
		}else{
			echo "认证失败！请关闭页面后重试！";
		}

	}
}

