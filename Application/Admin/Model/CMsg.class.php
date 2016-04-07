<?php
interface IMsg{
	function set_from_user_id($from_user_id);
	function set_from_user($from_user);
	function set_to_user_id($to_user_id);
	function set_to_user($to_user);
	function set_msg_time();
	function set_caozuo($caozuo);
	function set_neirong($neirong);
	function set_jieguo($jieguo);
	function save();
}
class CMsg implements IMsg{
	private $arr_data=array();
	function set_from_user_id($from_user_id){
		$this->arr_data['from_user_id']=$from_user_id;
	}
	function set_from_user($from_user){
		$this->arr_data['from_user']=$from_user;
	}
	function set_to_user_id($to_user_id){
		$this->arr_data['to_user_id']=$to_user_id;
	}
	function set_to_user($to_user){
		$this->arr_data['to_user']=$to_user;
	}
	function set_msg_time(){
		date_default_timezone_set('Etc/GMT-8');     //这里设置了时区
		$this->arr_data['msg_time']='NOW()';
	}
	function set_caozuo($caozuo){
		$this->arr_data['caozuo']=$caozuo;
	}
	function set_neirong($neirong){
		$this->arr_data['neirong']=$neirong;
	}
	function set_jieguo($jieguo){
		$this->arr_data['jieguo']=$jieguo;
	}
	function save(){
		$db=M('Msg');
		$ret=$db->add($this->arr_data);
	}
	function get_msg_table($user_id,$count=20){
		$db=M('Msg');
		$sqlstr['to_user_id']=$user_id;
		$sqlstr['from_user_id']=$user_id;
		$sqlstr['_logic'] = 'OR';
		$ret=$db->where($sqlstr)->order('id DESC')->limit($count)->select();
		return $ret;
	}
}