<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
	function _initialize(){
        header('Content-Type:text/html;charset=utf-8');//告诉浏览器这个文件时一个html文件，编码是utf-8
    }
    public function index(){
		//取出其中一个场馆的ID值
		$cgid=$this->listcgid();
		//取得POST或GET参数值
		if(I('get.changguangid')){
			$selectChangGuangId=I('get.changguangid');
		}else{
			$selectChangGuangId=$cgid;
		}
		if(I('get.selectdate')){
			$selectDate=I('get.selectdate');
		}else{
			$selectDate=date("Y-m-d");
		}
		//echo "POST参数：$selectChangGuangId";
		date_default_timezone_set('Etc/GMT-8');     //这里设置了时区
		$serverDate=date("Y-m-d H:i:s");			//取得当前服务器时间
		$sjList=$this->listsj();
		$tlists=$this->scsz($selectChangGuangId,$selectDate);
		$cgList=$this->listcg();
		//dump($tlist);
		$this->assign("serverdate",$serverDate);
		$this->assign('shijianlist',$sjList);		//输出时间点到模板
		$this->assign('cglist',$cgList);
		$this->assign('tlists',$tlists);					//输出场地、及场地的预订时间表
		//调用模板显示页面
		$this->display('index','utf8','text/html');
    }

//列出所有场馆
	public function listcg(){
		$cg=M('changdi');
		$sqlstr['shanchu']=0;
		$sqlstr['shangji_id']=0;
		$cgList=$cg->where($sqlstr)->select();
		//dump($cgList);
		if($cgList){
			return $cgList;
		}else{
			return null;
		}
	}//列出所有场馆
	//列出时间表的时间点
	public function listsj(){
		$sj=M('Shijian');
		$sjList=$sj->where('shanchu=0 or shanchu=null')->select();
		if(!$sjList){
			return null;
		}
		return $sjList;
	}
	
	//生成有场地行及时间列的空白数据tlist数组，用于输出预订数据表
	public function scsz($selectChangGuangId,$selectDate){
		$tlist=array();
		//$yday=getdate(strtotime($selectDate));
		//$yday['yday']++;  //mysql的dayofyear从1-366,php的getdate的yday从0-365，造成差一天，需要自加1
		$yday=$selectDate;
		//读取预订时间点列表
		$shiJianList=$this->listsj();
		//dump($shiJianList);
		//返回场地
		$changDi=M('changdi');
		$sqlstr['shangji_id']=$selectChangGuangId;
		$changDiList=$changDi->where($sqlstr)->order('paixu ASC')->getField('id,name');
		//dump($changDiList);
		if(!$changDiList){
			return null;
		}/*enc if*/
		foreach ($changDiList as $key => $value){ //遍历每个场地,场地id:$key,场地名称：$value或$changDiList[$k]
			//echo "场地ID：$key,场地名称：$value, </br>";
			$tlist[]=array('id'=>$key,'changdi'=>$value,'row'=>$shiJianList);
		}/*end foreach*/ 
		//dump($tlist);
		$retu=$this->set1($tlist,$yday);
		return $retu;	
	}//生成有场地行及时间列的空白数据tlist数组，用于输出预订数据表
	
	//给tlist数组增加值
	public function set1($tlist,$yday){
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
				}
			}
		}
		//dump($tlist);
		return $tlist;
	}//给tlist数组增加值
	
	//取场地的单个时间的预订值，查询出本时间点的值及用户名称
	public function get1($cdid,$sjid,$yday){//cdid场地ID号，sjId时间段ID号，riqi预订的日期
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
			return $retu=array("user"=>$yuDingList[0]['user'],"value"=>1);
		}else{
			return $retu=array("user"=>null,"value"=>0);
		}
	}//取场地的单个时间的预订值，查询出本时间点的值及用户名称

//取出其中一个场馆的ID号，用于在首页显示预订
	public function listcgid(){
		$cg=M('changdi');
		$sqlstr['shanchu']=0;
		$sqlstr['shangji_id']=0;
		$cgList=$cg->where($sqlstr)->find();
		//dump($cgList);
		if($cgList){
			return $cgList['id'];
		}else{
			return null;
		}
	}//列出所有场馆
}
