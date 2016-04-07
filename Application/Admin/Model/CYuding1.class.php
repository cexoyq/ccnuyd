<?php
import('Admin.Model.CYuding');
interface IShowYuding{
	function listcd();
	function listsj();
	function scsz1();
	function scsz2();
	function ajaxpost($arrdata,$cgid);
}
class CShowYuding extends CYuding{
	//返回任意一个场馆的ID号，用于在首页显示预订
	private function getcgid(){
			$cg=M('changdi');
			$sqlstr['shanchu']=0;
			$sqlstr['shangji_id']=0;
			$cgList=$cg->where($sqlstr)->find();
			//dump($cgList);
			if($cgList){
				//echo $cgList['id'];
				return $cgList['id'];
			}else{
				return null;
			}
	}//返回任意一个场馆的ID号，用于在首页显示预订

	//取出场馆ID对应的所有场地
	public function arr_listcd($cdId=0){
		if($cdId == 0){
			$cdId=$this->getcgid();
		}
		$cd=M('changdi');
		$sqlstr['shanchu']=0;
		$sqlstr['shangji_id']=$cdId;
		$cdList=$cd->where($sqlstr)->order('paixu ASC')->select();
		//echo "cdid:" . $cdId;
		//dump($cdList);
		if($cdList){
			return $cdList;
		}else{
			return null;
		}
	}//取出场馆ID对应的所有场地

	//列出时间表的时间点
	public function arr_listsj(){
		$sj=M('Shijian');
		$sjList=$sj->where('shanchu=0 or shanchu=null')->select();
		if(!$sjList){
			return null;
		}
		return $sjList;
	}//列出时间表的时间点
	
	public function scsz1(){
		$xqlist=array(
		array("id"=>1,"name"=>"星期一"),
		array("id"=>2,"name"=>"星期二"),
		array("id"=>3,"name"=>"星期三"),
		array("id"=>4,"name"=>"星期四"),
		array("id"=>5,"name"=>"星期五"),
		array("id"=>6,"name"=>"星期六"),
		array("id"=>0,"name"=>"星期天")	//PHP的DATETIME函数的星期日是0
		);
		//dump($xqlist);
		$sjlist=$this->listsj();
		$tlist[]=array(array("name"=>"时间"),$xqlist);
		foreach($sjlist as $k=>$value){
			$tlist[]=array(array("name"=>$value['name'],"id"=>$value['id']),$xqlist);
		}
		//dump($tlist);
		return $tlist;
	}
	
	public function scsz2(){
		$tlist=array();
		$sjlist=$this->listsj();
		$cdlist=$this->listcd();
		//dump($sjlist);
		//dump($cdlist);
		$th=array("name"=>"场地时间","id"=>-1);
		$tlist[]=array($th,$sjlist);
		//dump($sjlist);
		foreach($cdlist as $k=>$value){
			//echo "k['name']:" . $cdlist[$k]['name'] . "</br>";
			$tlist[]=array($value,$sjlist);
		}
		//dump($tlist);
		return $tlist;
	}
	
	private function ret_sj($shijian_id){
		$db=M('shijian');
		$sqlstr["id"]=$shijian_id;
		$ret=$db->where($sqlstr)->getField("name");
		return urlencode($ret);
	}
	private function ret_cd($changdi_id){
		$db=M('changdi');
		$sqlstr["id"]=$changdi_id;
		$ret=$db->where($sqlstr)->getField("name");
		return urlencode($ret);
	}
	
	private function retarr_xq_riqi($cgid,$kstime,$jstime,$xqid,$shijian_id,$ajaxpost_ret){
	//将星期几的ID，在开始时间戳与结束时间戳 段内转换成这个时间段内的日期数组并含预订的时间ID
		date_default_timezone_set(PRC);//一定要注意，JS里的时间戳是毫秒，而PHP的时间戳是秒
		$bujin=24*60*60*1000;
		//echo "mktime:" . mktime() . "\n<br />";
		//echo "kstime:" . $kstime . "; " . date('Y-m-d',$kstime/1000) . ";  jstime:" . $jstime . "; " . date('Y-m-d',$jstime/1000) . "\n<br />";
		//echo "$kstime 星期：" . date('w',$kstime/1000) . "\n<br />";
		$arr_ret=$ajaxpost_ret;
		$s_user=I('session.user');
		$user_id=$s_user['id'];
		$user_name=$s_user['user'];
		$user_name1=urlencode($user_name);	//中文字符进行URL编码，否则JS收到后会乱码
		$cz_user_id=$s_user['id'];
		for ($x=$kstime; $x<=$jstime; $x=$x+$bujin) {
			$y=date('w',$x/1000);
			if($y == $xqid){
				$t_riqi=date('Y-m-d',$x/1000);
				//echo "$x 星期：" . $y . "; 日期：" . $t_riqi . "\n<br />";
				
				$changdi_id=$this->ret_yudingchangdi_id($cgid,$t_riqi,$shijian_id,$user_name,$user_id,$cz_user_id);
				$shijian_name=$this->ret_sj($shijian_id);
				$changdi_name=$this->ret_cd($changdi_id);
				
				$arr_ret[]=array("user_name"=>$user_name1,"user_id"=>$user_id,"cz_user_id"=>$cz_user_id,"riqi"=>$t_riqi,"shijian_id"=>$shijian_id,"changdi_id"=>$changdi_id,"shijian_name"=>$shijian_name,"changdi_name"=>$changdi_name);
			}
		}
		//dump($arr_ret);
		return $arr_ret;
	}
	private function ret_yudingchangdi_id($cgid,$riqi,$shijian_id,$user_name,$user_id,$cz_user_id){
	//预订（调用的MYSQL自定义函数），并返回预订的某天某个时间段的空闲场地ID，如果没有空闲了，则返回0
		$Model = new \Think\Model(); // 实例化一个model对象 没有对应任何数据表
		$ret=NULL;
		//调用存储过程
		$sqlstr="call getid($cgid,$shijian_id,$user_id,'$user_name','$riqi',$cz_user_id,@out1);";
		//echo "sqlstr:" . $sqlstr . "\n";
		//取OUT值
		$Model->execute($sqlstr);
		$ret=$Model->query("select @out1");
		//echo "sqlstr:" . $sqlstr . ";ret:" . $ret[0]["@out1"] . ";\n<br />";
		//$ret=$Model->query("select count(*) from ccnu1_yuding");
		//dump($ret);
		return $ret[0]["@out1"];
	}
	
	public function ajaxpost($arrdata,$cgid){
	  //dump($arrdata);
	  $ajaxpost_ret=array();
	  $kstime=NULL;		//开始日期的时间戳
	  $jstime=NULL;		//结束日期的时间戳
	  $xqid=NULL;
	  $shijian_id=NULL;
	  $arr1['riqi']=NULL;
	  $arr1['shijian_id']=NULL;
	  $arr1['user_id']=NULL;
	  $arr1['user']=NULL;
	  $arr1['changdi_id']=NULL;
	  $arr1['cz_user_id']=NULL;
	  foreach($arrdata as $k=>$v){
		if($k == 0){
			$kstime=$v[0];
			$jstime=$v[1];
			//echo "kstime:" . $kstime . ";  jstime:" . $jstime . "\n<br />";
		}else{
			$xqid=$v[1];
			$shijian_id=$v[2];
			$ajaxpost_ret=$this->retarr_xq_riqi($cgid,$kstime,$jstime,$xqid,$shijian_id,$ajaxpost_ret);
		}
	  }
	  return $ajaxpost_ret;
	}
}//class CShowYuding