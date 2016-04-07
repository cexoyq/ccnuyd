<?php
/*
import('Admin.Model.CYuding');
$cYuding=new \CYuding();

public function chk_yuding($changdi_id,$shijian_id,$riqi);
public function chk_user_yuding($yuding_user_id,$changdi_id,$shijian_id,$riqi);
public function ret_mingcheng_changdi($changdi_id);
public function ret_mingcheng_shijian($shijian_id);
public function updateData($arr_data);
*/
class CYuding{
	public function chk_yuding($changdi_id,$shijian_id,$riqi){
		//检查场地的某个时间段是否已经被预订
		$db=M('yuding');
		$sqlstr['changdi_id']=$changdi_id;
		$sqlstr['shijian_id']=$shijian_id;
		$sqlstr['riqi']=$riqi;
		$retu=$db->where($sqlstr)->getField('id');
		if($retu)
		{
			return $retu;
		}else{
			return NULL;
		}
	}//function chk_yuding
	
	public function chk_user_yuding($yuding_user_id,$changdi_id,$shijian_id,$riqi){
		//检查场地的预订是否是此用户，如果是此用户，则返回id，否则返回null
		$db=M('yuding');
		$sqlstr['user_id']=$yuding_user_id;
		$sqlstr['changdi_id']=$changdi_id;
		$sqlstr['shijian_id']=$shijian_id;
		$sqlstr['riqi']=$riqi;
		$retu=$db->where($sqlstr)->getField('id');
		if($retu){
			return $retu;
		}else{
			return NULL;
		}
	}//function chk_user_yuding
	
	public function ret_mingcheng_changdi($changdi_id){
		$db=M('changdi');
		$sqlstr['id']=$changdi_id;
		$ret=$db->where($sqlstr)->getField('name');
		return $ret;
	}//查询ID返回场地名称
	
	public function ret_mingcheng_shijian($shijian_id){
		$db=M('shijian');
		$sqlstr['id']=$shijian_id;
		$ret=$db->where($sqlstr)->getField('name');
		return $ret;
	}//查询ID返回时间名称
	
	public function updateData($arr_data){
	//ajax的数据处理，预订及取消预订
		$retu_arr=array(
			str=>'',
			code=>'',//新增预订成功1 不成功101，取消预订成功2 不成功102，取消他人预订成功3 不成功103，已被抢订100
			id=>NULL
		);
		
		$cud=$arr_data[1];		//cud为true,则表示预订，为false 则表示取消预订
		$db=M('yuding');
		$session_user=I('session.user');
		$user_id=$session_user["id"];
		$user=$session_user["user"];
		$admin=$session_user["admin"];
		
		$yuding_id=$arr_data[0];
		if($admin == 1){$yuding_user=$arr_data[2];}else{$yuding_user=$user;}//管理员可以把预订的用户名称改成别的姓名
		$yuding_user_id=$arr_data[3];
		$changdi_id=$arr_data[4];
		$shijian_id=$arr_data[5];
		$riqi=$arr_data[6];
		$changdi_name=$this->ret_mingcheng_changdi($changdi_id);
		$shijian_name=$this->ret_mingcheng_shijian($shijian_id);
		
		$sendmsg=new \CMsg();
		$sendmsg->set_from_user($user);
		$sendmsg->set_from_user_id($user_id);
		$sendmsg->set_to_user($user);
		$sendmsg->set_to_user_id($user_id);
		$sendmsg->set_caozuo('');
		
		//查询是否已经有预订的了
		$retu_chk=$this->chk_yuding($changdi_id,$shijian_id,$riqi);
		$yd_id=$retu_chk;
		$msg1='用户：' . $yuding_user . '，时间：' . $shijian_name . '，场地：' .  $changdi_name . '，日期：'  . $riqi;
		$msg2='用户：' . $user . '，时间：' . $shijian_name . '，场地：' .  $changdi_name . '，日期：'  . $riqi;
		if($retu_chk){
			if($admin == 1){//是管理员
				//管理员则可以直接取消用户预订
				if(!empty($yuding_user_id)){
					//显示的已预订
					$db=M('yuding');
					$sqlstr=NULL;
					$sqlstr['id']=$yd_id;
					$retu_cud=$db->where($sqlstr)->delete();
					if($retu_cud == 1){
						//取消预订成功
						$retu_arr['str']=urlencode('成功取消了预订，' . $msg1);
						$retu_arr['code']=3;
						$retu_arr['id']=$yuding_id;
						
						$sendmsg->set_to_user($yuding_user);
						$sendmsg->set_to_user_id($yuding_user_id);
						$sendmsg->set_caozuo('取消预订');
						$sendmsg->set_neirong($msg1);
						$sendmsg->set_jieguo('成功');
						
					}elseif($retu_cud == false){
						$retu_arr['str']=urlencode('取消预订失败！'. $msg1);
						$retu_arr['code']=103;
						$retu_arr['id']=$yuding_id;
						$retu_arr['user']=$user;
						
						$sendmsg->set_to_user($yuding_user);
						$sendmsg->set_to_user_id($yuding_user_id);
						$sendmsg->set_caozuo('取消预订');
						$sendmsg->set_neirong($msg1);
						$sendmsg->set_jieguo('失败');					
					}
				}else{//if(!empty($yuding_user_id)){
					//没有显示已被预订，但数据库查询已有预订
					//要求用户刷新页面后再试
					if($this->chk_user_yuding($user_id,$changdi_id,$shijian_id,$riqi)){
						//预订用户与操作用户一样，则取消预订
						$db=M('yuding');
						$sqlstr=NULL;
						$sqlstr['id']=$yd_id;
						$retu_cud=$db->where($sqlstr)->delete();
						if($retu_cud){
							//取消本人预订成功
							$retu_arr['str']=urlencode('成功取消您的预订！');
							$retu_arr['code']=2;
							$retu_arr['id']=$yuding_id;
							$retu_arr['user']=$user;
							
							$sendmsg->set_to_user($user);
							$sendmsg->set_to_user_id($user_id);
							$sendmsg->set_caozuo('取消预订');
							$sendmsg->set_neirong($msg1);
							$sendmsg->set_jieguo('成功');
						
						}else{
							$retu_arr['str']=urlencode('取消您的预订不成功！');
							$retu_arr['code']=102;
							$retu_arr['id']=$yuding_id;
							$retu_arr['user']=$user;
							
							$sendmsg->set_to_user($user);
							$sendmsg->set_to_user_id($user_id);
							$sendmsg->set_caozuo('取消预订');
							$sendmsg->set_neirong($msg1);
							$sendmsg->set_jieguo('失败');
							
						}
					}else{
						//管理员不能取消没有显示预订的已抢预订
						$retu_arr['str']=urlencode('此地场已被预订，请刷新页面后再试！');
						$retu_arr['code']=100;
						$retu_arr['id']=$yuding_id;
						//$retu_arr['user']=$user;//应该显示已抢注的用户名
					}/*if($this->chk_user_yuding($user_id,$changdi_id,$shijian_id,$riqi)){*/
				}//if(!empty($yuding_user_id)){
	
			}else{//if($admin == 1){//不是管理员
				//判断预订的用户是否等于这个用户
				if($this->chk_user_yuding($user_id,$changdi_id,$shijian_id,$riqi)){
					//预订用户与操作用户一样，则取消预订
					$db=M('yuding');
					$sqlstr=NULL;
					$sqlstr['id']=$yd_id;
					$retu_cud=$db->where($sqlstr)->delete();
					if($retu_cud){
						//取消本人预订成功
						$retu_arr['str']=urlencode('成功取消您的预订！');
						$retu_arr['code']=2;
						$retu_arr['id']=$yuding_id;
						$retu_arr['user']=$user;
						
						$sendmsg->set_to_user($user);
						$sendmsg->set_to_user_id($user_id);
						$sendmsg->set_caozuo('取消预订');
						$sendmsg->set_neirong($msg1);
						$sendmsg->set_jieguo('成功');
							
					}else{//取消本人预订不成功
						$retu_arr['str']=urlencode('取消您的预订不成功！');
						$retu_arr['code']=102;
						$retu_arr['id']=$yuding_id;
						$retu_arr['user']=$user;
						
						$sendmsg->set_to_user($user);
						$sendmsg->set_to_user_id($user_id);
						$sendmsg->set_caozuo('取消预订');
						$sendmsg->set_neirong($msg1);
						$sendmsg->set_jieguo('成功');
					}
				}else{
					//普通用户不能取消别人的预订
					$retu_arr['str']=urlencode('已被抢订！请刷新页面后重试！');
					$retu_arr['code']=100;
					$retu_arr['id']=$yuding_id;
					//$retu_arr['user']=$user;//应该显示已抢注的用户名
				}
			}
		}else{//if($retu_chk){
			//没有已经预订的记录，则新增
			$sqldata=NULL;
			$sqldata['user']=$yuding_user;
			$sqldata['user_id']=$user_id;
			$sqldata['changdi_id']=$changdi_id;
			$sqldata['shijian_id']=$shijian_id;
			$sqldata['riqi']=$riqi;
			$sqldata['cz_user_id']=$user_id;
			$db=M('yuding');
			$retu_cud=$db->add($sqldata);
			if($retu_cud){
				$retu_arr['str']=urlencode('预订成功!' . $msg2);
				$retu_arr['code']=1;
				$retu_arr['id']=$yuding_id;
				//$retu_arr['user']=$user;
				$retu_arr['user']=$yuding_user;
				
				$sendmsg->set_to_user($user);
				$sendmsg->set_to_user_id($user_id);
				$sendmsg->set_caozuo('预订');
				$sendmsg->set_neirong($msg2);
				$sendmsg->set_jieguo('成功');
						
	
			}else{
				$retu_arr['str']=urlencode('预订失败！' . $msg2);
				$retu_arr['code']=101;
				$retu_arr['id']=$yuding_id;
				$retu_arr['user']=$user;
				
				$sendmsg->set_to_user($user);
				$sendmsg->set_to_user_id($user_id);
				$sendmsg->set_caozuo('预订');
				$sendmsg->set_neirong($msg2);
				$sendmsg->set_jieguo('成功');
			}
		}
		$sendmsg->save();
		return $retu_arr;
	}//function updateData
}//class CShowYuding