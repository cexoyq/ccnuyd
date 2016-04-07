<?php
namespace Admin\Controller;
use Think\Controller;
import('Admin.Model.CMsg');
import('Admin.Model.CPlYuding');
import('Admin.Model.CLogin');
import('Admin.Model.CUser');
import('Admin.Model.CChangdi');
import('Admin.Model.CComm');
import('Admin.Model.CYuding');
import('Admin.Model.CNewYuding');

class IndexController extends CmController {
	public function thtml(){
		//初始化页面的菜单及框架
		date_default_timezone_set('Etc/GMT-8');     //这里设置了时区
		$serverDate=date("Y-m-d H:i:s");					//取得当前服务器时间
		$loginUser=I('session.user');				//用户登陆后的session
		//echo "user:" . $loginUser['user'];
		//echo "id:" . $loginUser['id']; 
		//echo "admin:" . $loginUser['admin'] . '</br>';
		if (!$loginUser)//用户登陆限制，已在基类CmController中作了限制
		{
			return null;
		}
		//echo "是管理员，读取相应的权限的菜单";
		$db=M("Menu");
		$menuList=$db->where('quanxian<=' . $loginUser['admin'])->order('paixu ASC')->getField('id,name,url,zicaidan');
		if(!$menuList){
			return null;
		}
		//dump($menuList);
		foreach($menuList as $key=>$value){
			if($value['zicaidan']){
				//echo $value['name'] . "有子菜单！</br>";
				$menuList[$key]['zicaidan']=get_all_cgid();
			}
		}
		$cgId=get_cgid();
		//dump($menuList);
		//session(null);
		//$this->error('没有登陆！！',U('Login/login'));
		$this->assign('cgid',$cgId);
		$this->assign('loginuser',$loginUser);
		$this->assign('serverdate',$serverDate);
		$this->assign('menulist',$menuList);
	}

    public function index(){
		$this->thtml();//初始化页面的菜单及框架
		//import('Admin.Model.CMsg');
		$showmsg=new \CMsg();
		$str_tmp=I('session.user');
		$user_id=$str_tmp['id'];
		$msgtable=$showmsg->get_msg_table($user_id);
		//dump($msgtable);
		$this->assign('msgtable',$msgtable);
        $this->display('index','utf8','text/html');
		//$this->yuding();
	}
	
	public function chakanjilu(){
	/*查看记录*/
		$this->thtml();
		$this->display();
	}	
	
//__URL__/yuding预订管理
	public function yuding(){
	/*单个预订*/
		$this->thtml();
		$cComm=new \CComm();
		$admin=get_user_admin();
		$username=get_user_name();
		if(I('get.cgid')){
			$cgId=I('get.cgid');
		}else{
			$cgId=$cComm->listcgid();//取任意一个场馆的ID号
		}
		if(I('get.selectdate')){
			$selectDate=I('get.selectdate');
		}else{
			$selectDate=date("Y-m-d");
		}
		//echo "POST参数：$cgid";
		date_default_timezone_set('Etc/GMT-8');     //这里设置了时区
		//$serverDate=date("Y-m-d H:i:s");			//取得当前服务器时间,js Date对像不能识别此格式
		$serverDate=date("m d,Y H:i:s");			//取得当前服务器时间,Jannuary 1, 1998 20:13:15
		$sjList=list_sj();
		$tlists=$cComm->scsz($cgId,$selectDate);
		
		//dump($tlists);	//最重要的数据表了，包含场地表、时间表（含预订）、用户
		$this->assign('username',$username);		//输入用户名，用于判断预订用户是否能取消
		$this->assign('serverdate',$serverDate);
		$this->assign('cgid',$cgId);
		$this->assign('admin',$admin);
		$this->assign('selectdate',$selectDate);
		$this->assign('shijianlist',$sjList);		//输出时间点到模板
		$this->assign('tlists',$tlists);					//输出场地、及场地的预订时间表
		//调用模板显示页面
		$this->display('yuding','utf8','text/html');
	}
/*end 预订管理*/
	
/*start 批量预订*/
	public function plyuding(){
	//批量预订
		$this->thtml();
		$cPlyd=new \CPlYuding();
		$tlist=$cPlyd->scsz1();
		/*start 检查场馆ID是否正确，是否在数据库中有此ID号*/
		$cgId=ret_get_cgid();
		if($cgId == -1){
			$this->error('场馆的ID号输入错误!',U('Index/Index'));
		}
		/*end 检查场馆ID是否正确，是否在数据库中有此ID号*/
		$arrCD=list_cd($cgId);
		//dump($arrCD);
		//dump($tlist);
		$this->assign('cg_id',$cgId);	//输出场馆ID
		$this->assign('arrCD',$arrCD);	//输出场地列表数组
		$this->assign('tlist',$tlist);
		$this->display('plyuding','utf8','text/html');
	}
/*end 批量预订结束*/

	//__URL__/changdi，特别要注意FOREACH里$KEY和$VALUE的用法
	public function changdi(){
		$this->thtml();
		$cgId=ret_get_cgid();
		if($cgId == -1){
			return 0;
		}
		$cdList=list_cd($cgId=0);
		foreach($cdList as $key => $value){
			//echo "id:" . $value['id'] . ',id:' . $cdList[$key]['id'];
			if($cgId == $value['id']){
				$lt=$cComm->listcd($value['id']);
				$cdList[$key]['cd']=$lt;
			}else{
				//unset($cdList[$key]);
			}
			//dump($cdList);
		}
		//dump($cdList);
		$this->assign('cdlist',$cdList);
		$this->display('changdi','utf8','text/html');
	}
	//__URL__/changdi_add，增加场地、场馆
	public function changdi_add(){
		$this->thtml();
		$cChangDi=new \CChangDi();
		if(I('get.shangji_id'))
		{
			$sid=I('get.shangji_id');
			$this->assign('shangji_id',$sid);
			$this->display('changdi_add','utf8','text/html');
		}
		$cdName=I('post.cdname');
		$shangJi_id=I('post.shangji_id');
		//echo "cgname:" . $cgName . "shangji_id:" . $shangJi_id;
		if(!$cdName or !is_numeric($shangJi_id)){
			return;
		}
		//检查是否已存在这个场地名称
		$ck=$cChangDi->youledata('Changdi','name',$cdName);
		if($ck == 1){
			$this->error('数据已经存在，请修改后重试!',U('Admin/Index/changdi'));
			return;
		}
		//echo "cgname:" . $cgName . "shangji_id:" . $shangJi_id;
		//写入场地数据
		$dbChangDi = D("Changdi"); // 实例化User对象
		$data['shangji_id'] = $shangJi_id;
		$data['name'] = $cdName;
		$data['shanchu'] = 0;
		$retu=$dbChangDi->add($data);
		if($retu == false){
			//写入数据不成功
			$this->error('数据保存错误，请重试!',U('Admin/Index/changdi'));
			return;
		}else{
			//写入场地数据成功后
			if($shangJi_id == 0){
				$this->success('成功增加场馆!',U('Admin/Index/changdi'));
				return;
			}else{
				$this->success('成功增加场地!',U('Admin/Index/changdi'));
				return;
			}
		}
	}
	
	//__URL__/changdi_del,删除场地、场馆，要先检查是否已经被使用，被使用了则不删除
	public function changdi_del(){
		$this->thtml();
		$cChangDi=new \CChangDi();
		$cdId=I('get.id');
		$shangji_id=I('get.shangji_id');
		//echo "I(get.id):" . $cdId;
		if(!is_numeric($cdId)){
			$this->error('数据校验有错误，请重试!',U('Admin/Index/changdi'));
			return;
		}
		//检查数据是否已经被使用了
		$ck=$cChangDi->yonglechangdidata($cdId,$shangji_id);
		if($ck == 1){
			$this->error('数据正在被使用，不能删除!',U('Admin/Index/changdi'));
			return;
		}
		//echo "cgname:" . $cgName . "shangji_id:" . $shangJi_id;
		//删除数据
		echo "开始删除数据！</br>";
		$db = M('Changdi');
		$db->delete($cdId);
		if($db){
			$this->success('成功删除数据!',U('Admin/Index/changdi'));
		}elseif($db = false){
			$this->success('SQL语句出错，没有删除数据!',U('Admin/Index/changdi'));
		}elseif($db = 0){
			$this->success('没有删除数据!',U('Admin/Index/changdi'));
		}
	}

	public function changdi_edit(){
		$this->thtml();
		//从修改页面传来的POST数据
		$cdNameNew=I('post.cdname_new');
		$cdId=I('post.cdid');
		//从列表页面传来的数据
		$cdNameOld=I('get.cdname_old');
		$changDiId=I('get.id');
		
		if($cdNameNew and $cdId){
			//POST了场地的新名称和ID，修改场地名称
			echo "收到了场地的新名称和ID，修改场地名称POST数据 id：" . $cdId . ", cdname_new:" . $cdNameNew . "</br>";
			$db=M('changdi');
			$sqlstr['id']=$cdId;
			$data['name']=$cdNameNew;
			$retu=$db->where($sqlstr)->save($data);
			if($retu = 1){
				$this->success('成功修改数据!',U('Admin/Index/changdi'));
				return;
			}elseif($retu = false){
				$this->error('修改数据失败!',U('Admin/Index/changdi'));
				return;
			}
		}
			//GET了场地的老名称及ID号，显示页面内容
			echo "GET了场地的老名称及ID号，显示页面内容,id:" . $changDiId . ",cdname_old:" . urldecode($cdNameOld) . "</br>" ;
			$this->assign("cdname_old",urldecode($cdNameOld));
			$this->assign("cdid",$changDiId);
			$this->display('changdi_edit','utf8','text/html');
	}
	
	//__URL__/user
	public function user_edit(){
		$this->thtml();
		$cUser=new \CUser();
		$password_old=I('post.password_old');
		$password_new=I('post.password_new');
		$password_confirm=I('post.password_confirm');
		$session_user = I('session.user');
		$user_id=$session_user['id'];

		if(!empty($password_new) and !empty($password_old) and !empty($password_confirm)){
			//$this->error('请输入新密码！',U('Admin/Index/user_edit'));
			if($password_new === $password_confirm){
				//$this->error('密码二次输入不匹配，请重试！',U('Admin/Index/user_edit'));
				//echo "收到POST数据user_id:$user_id,old:$password_old,new:$password_new,confirm:$password_confirm,更改密码！</br>";
				$ret=$cUser->password_chk($user_id,$password_old);
				if($ret){
					echo "密码验证成功，开始修改密码！";
					$ret=$cUser->password_update($user_id,$password_new);
					if($ret){
						echo "修改密码成功！";
						$this->logout();
						//$this->success('成功修改用户密码!',U('Admin/Index/user_edit'));
						return;
					}else{
						echo "修改密码失败！";
						//$this->display('user_edit','utf8','text/html');
						$this->error('修改密码失败!',U('Admin/Index/user_edit'));
						return;
					}
				}else{
					echo "密码验证错误！";
					//$this->display('user_edit','utf8','text/html');
					$this->error('密码验证错误，请重试!',U('Admin/Index/user_edit'));
					return;
				}
			}else{
				echo "二次输入密码不一样！";
				//$this->display('user_edit','utf8','text/html');
				$this->error('二次输入密码不一样，请重试!',U('Admin/Index/user_edit'));
				return;
			}
		}
		//核对旧密码是否正确//更改密码
		//$this->success('成功修改用户密码!',U('Admin/Index/user_edit'));
		$this->display('user_edit','utf8','text/html');
	}
	
	//__URL__/users
	public function users(){
		$this->thtml();
		$cUser=new \CUser();
		$usersList=$cUser->users_get_list();
		$this->assign('user_list',$usersList);
		$this->display('users','utf8','text/html');
	}
	
	public function users_add(){
	//__URL__/user_add
		$this->thtml();
		
		$this->assign('user_list',$usersList);
		$this->display('users_add','utf8','text/html');
	}
	
	public function cdgb(){
		$this->thtml();
		$this->display('cdgb','utf8','text/html');
	}
/*start 新的预订=====================================*/
	public function newyd(){
	//新的预订
		$this->thtml();
		$cg_id=ret_get_cgid();
		$list_cd=list_cd($cg_id);
		$list_sj=list_sj();
		$this->assign('cg_id',$cg_id);
		$this->assign('list_cd',$list_cd);
		$this->assign('list_sj',$list_sj);
		$this->display('yuding_new','utf8','text/html');
	}
	public function ajax_newyd_sel_date(){
	//AJAX 用户选择日期后，返回可预订的时间段
		$cNewYuding=new \CNewYuding();
		$ajax_arr_data=I('post.ajax_data');
		$ret=$cNewYuding->get_yuding_date_all($ajax_arr_data);
		echo json_encode($ret);
	}
	public function ajax_newyd_sel_sj(){
	//AJAX 用户选择时间段后，预订并返回预订场地资料
		$cNewYuding=new \CNewYuding();
		$ajax_arr_data=I('post.ajax_data');
		$ret=$cNewYuding->set_yuding($ajax_arr_data);
		echo json_encode($ret);
	}
	public function ajax_newyd_sel_cd(){
	//AJAX 用户选择场地后，返回已预订的时间段
		$cNewYuding=new \CNewYuding();
		$ajax_arr_data=I('post.ajax_data');
		$ret=$cNewYuding->get_list_ydsj($ajax_arr_data);
		echo json_encode($ret);
	}
/*start ajax server=================================================*/
	public function ajax_server1(){
	//用户单个预订的AJAX POST服务器
		$cYuding=new \CYuding();
		$ajax_data = I('post.aid');//获得的$aid直接就是个数组了。
		$return=$cYuding->updateData($ajax_data);
		/*$return['str']=urlencode('测试中!');	//json中文需先用urlencode编码，再在JS中用decodeURIComponent解码
		$return['code']=102;
		$return['id']=1;*/
		echo json_encode($return); //直接echo $aid的话，会显示 "Array"，所以为了方便阅读编码成了json
	}//ajax_server
	
	public function ajax_plyuding(){
	//批量预订的AJAX 服务器
		$ajax_data=I('post.arr');
		$cPlyd=new \CPlYuding();
		file_put_contents('/tmp/1.log',"1\t php ajax_data:" . $ajax_data . "\n");
		$ret=$cPlyd->ajax_ret_plyuding($ajax_data);
		//$ajax_ret=urlencode($ret);//只对中文的变量转码，没有中文不需要
		//二维数组排序
		//$ret_ajax_data = my_sort($ret,'riqi',SORT_ASC,SORT_STRING);
		echo json_encode($ret);
	}
/*end ajax server=================================================*/

}