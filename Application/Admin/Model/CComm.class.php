<?php
/*
import('Admin.Model.CComm');
$cComm=new \CComm();

scsz()函数用于生成单个预订的网页数组，它调用set1,set1调用get1
public function scsz($selectChangGuangId,$selectDate);
public function set1($tlist,$yday);
public function get1($cdid,$sjid,$yday);

*/
class CComm{
	public function scsz($selectChangGuangId,$selectDate){
	//生成有场地行及时间列的空白数据tlist数组，用于输出预订数据
		$tlist=array();
		//$yday=getdate(strtotime($selectDate));
		//$yday['yday']++;  //mysql的dayofyear从1-366,php的getdate的yday从0-365，造成差一天，需要自加1
		$yday=$selectDate;
		//读取预订时间点列表
		$shiJianList=list_sj();
		//dump($shiJianList);
		//取得已选场馆的场地列表
		$changDiList=list_cd($selectChangGuangId);
		//遍历每个场地,,给数组赋值
		foreach ($changDiList as $key => $value){
			//echo "场地ID：$key,场地名称：$value, </br>";
			$tlist[]=array('id'=>$value['id'],'changdi'=>$value['name'],'row'=>$shiJianList);
		}/*end foreach*/ 
		//dump($tlist);
		$retu=$this->set1($tlist,$yday);
		return $retu;	
	}//生成有场地行及时间列的空白数据tlist数组，用于输出预订数据表
	
	public function set1($tlist,$yday){
	//给tlist数组增加值
		if(!$tlist){return null;}
		if(!$yday){return null;}
		foreach($tlist as $key1 => $value1){
			$cdid=$value1['id'];
			//echo "cdid:" . $cdid . "</br>";
			foreach($value1['row'] as $key2=>$value2){
				$sjid=$tlist[$key1]['row'][$key2]['id'];
				//echo "TP:" . $tlist[$key1]['row'][$key2]['name'] . "</br>";
				//echo "sjid:" . $sjid . "</br>";
				
				if($retu=$this->get1($cdid,$sjid,$yday)){
					//$retu=$get1($cdid,$sjid,$riqi);
					$tlist[$key1]['row'][$key2]['value']=$retu['value'];
					$tlist[$key1]['row'][$key2]['user']=$retu['user'];
					$tlist[$key1]['row'][$key2]['user_id']=$retu['user_id'];
				}
			}
		}
		//dump($tlist);
		return $tlist;
	}//给tlist数组增加值
	
	public function get1($cdid,$sjid,$yday){
	/*取场地的单个时间的预订值，查询出本时间点的值及用户名称
	cdid场地ID号，sjId时间段ID号，riqi预订的日期*/
		$yday=getdate(strtotime($yday));
		//echo "yday:" . $yday['yday'];
		$yday['yday']++;  //mysql的dayofyear从1-366,php的getdate的yday从0-365，造成差一天，需要自加1
		//echo "yday:" . $yday['yday'];
		$rizi=$yday['yday'];
		$yuDing=M('Yuding');
		$sqlstr['changdi_id']=$cdid;
		$sqlstr['shijian_id']=$sjid;
		$sqlstr['riqi']=$yday;
		$yuDingList=$yuDing->where('changdi_id=' . $cdid . ' and shijian_id=' . $sjid . ' and DAYOFYEAR(riqi)=' . $rizi)->select();
		//dump($yuDingList);
		if($yuDingList){
			return $retu=array("user"=>$yuDingList[0]['user'],"user_id"=>$yuDingList[0]['user_id'],"value"=>1);
		}else{
			return $retu=array("user"=>null,"user_id"=>null,"value"=>0);
		}
	}
}//class CComm