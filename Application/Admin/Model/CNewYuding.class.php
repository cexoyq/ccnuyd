<?php
/*
import('Admin.Model.CNewYuding');
$cNewYuding=new \CNewYuding();


*/
class CNewYuding{
	public function get_yuding_date_all($arr_data){
	//根据日期参数返回用户的预订列表
		$db = new \Think\Model();
		$v['user_id']=get_user_id();
		$v['riqi']=$arr_data[1];
		$v['cg_id']=$arr_data[0];	//场馆的ID，传入的是场馆的ID号
		$v["shanchu"]=0;					//没有删除
		/*通过子查询，查询用户在某一天的场馆下订了多少场地*/
		$sql_str="SELECT __PREFIX__yuding.id,
				__PREFIX__yuding.changdi_id,
				__PREFIX__yuding.shijian_id,
				__PREFIX__yuding.user_id,
				__PREFIX__yuding.riqi,
				__PREFIX__yuding.`user`,
				__PREFIX__yuding.cz_user_id,
				__PREFIX__yuding.yuding_type,
				__PREFIX__changdi.`name` as changdi_name,
				__PREFIX__shijian.`name` as shijian_name
				FROM __PREFIX__yuding 
				INNER JOIN __PREFIX__shijian ON 
				__PREFIX__shijian.id = __PREFIX__yuding.shijian_id
				INNER JOIN __PREFIX__changdi ON 
				__PREFIX__changdi.id = __PREFIX__yuding.changdi_id
				WHERE riqi='{$v['riqi']}' 
				AND user_id={$v['user_id']} 
				AND EXISTS (
				SELECT id FROM ccnu1_changdi 
				WHERE 
				__PREFIX__yuding.changdi_id = __PREFIX__changdi.id 
				AND	shangji_id={$v['cg_id']})";
		$ret=$db->query($sql_str);
		return $ret;
	}
	public function get_list_ydsj($arr_data){
	//根据日期、场地参数返回已预订时间点的列表
		$db = new \Think\Model();
		$v["cd_id"]=$arr_data[0];
		$v["riqi"]=$arr_data[1];
		$sql_str="SELECT id,name FROM __PREFIX__shijian
			WHERE shanchu=0	AND	EXISTS
			(SELECT id FROM __PREFIX__yuding
			WHERE __PREFIX__yuding.shijian_id=__PREFIX__shijian.id
			AND riqi='{$v['riqi']}' AND 
			__PREFIX__yuding.changdi_id={$v['cd_id']})";
		$ret=$db->query($sql_str);
		return $ret;
	}
	public function set_yuding($arr_data){
	//根据日期、场地、时间、用户ID参数进行预订
	}

}//class CNewYuding