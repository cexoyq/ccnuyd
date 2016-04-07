<?php
/*
import('Admin.Model.CUser');
$cUser=new \CUser();
public function users_get_list();
public function password_chk($user_id,$password);
public function password_update($user_id,$password_new);
*/
class CUser{
	
	public function users_get_list(){
	//取得用户列表
		$db=M('user');
		$ret=$db->where()->select();
		//dump($ret);
		return $ret;
	}
	
	public function password_chk($user_id,$password){
	//检查密码是否正确
		$db = M('User');
		$sqlstr['id']=$user_id;
		$sqlstr['password']=md5($password);
		//echo "id:" . $user_id . ",md5(password);" . md5($password) . "</br>";
		$res = $db->where($sqlstr)->select();
		//echo $db->getlastSql(); 
		//dump($res);
		if($res){
			//密码验证正确
			echo "密码正确！";
			return 1;
		}else{
			//密码验证错误
			return 0;
		}
		return 0;
	}

	public function password_update($user_id,$password_new){
	//更改密码
		$db=M('user');
		$sqlstr['id']=$user_id;
		$data['password']=md5($password_new);
		$ret=$db->where($sqlstr)->save($data);
		if($ret){
			echo "成功修改密码！";
		}else{
			echo "修改密码不成功！";
		}
		return $ret;
	}
}//class CUser