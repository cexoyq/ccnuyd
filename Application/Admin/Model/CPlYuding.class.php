<?php
/*
import('Admin.Model.CPlYuding');
$cPlYuding=new \CPlYuding();

public function scsz1();
private function insert_yuding($arr);
public function ajax_ret_plyuding($arrdata);
*/
class CPlYuding{
	public function scsz1(){
		$xqlist=array(
		array("id"=>1,"name"=>"星期一"),
		array("id"=>2,"name"=>"星期二"),
		array("id"=>3,"name"=>"星期三"),
		array("id"=>4,"name"=>"星期四"),
		array("id"=>5,"name"=>"星期五"),
		array("id"=>6,"name"=>"星期六"),
		array("id"=>0,"name"=>"星期天")	//PHP的DATETIME函数的星期日是0，javascript的星期日是0
		);
		//dump($xqlist);
		$sjlist=list_sj();
		$tlist[]=array(array("name"=>"时间"),$xqlist);
		foreach($sjlist as $k=>$value){
			$tlist[]=array(array("name"=>$value['name'],"id"=>$value['id']),$xqlist);
		}
		//dump($tlist);
		return $tlist;
	}
	
	private function insert_yuding($arr){
	/*将单条预订的数据调用存储过程保存，
	在存储过程中判断用户选择的场地是否已被使用，
	如果已被使用则更换成下一个可用场地进
	行预订；如果没有被使用则预订此场地*/
		$Model = new \Think\Model(); // 实例化一个model对象 没有对应任何数据表
		$ret=NULL;
		/*start 先锁住表，然后查询并添加数据，再解锁*/
		//$sqlstr="LOCK TABLES ccnu1_yuding WRITE;";
		//$Model->execute($sqlstr);
		file_put_contents('/tmp/1.log',
		"5\t sqlstr:" . $sqlstr . "\n",FILE_APPEND);
		/*end 先锁住表，然后查询并添加数据，再解锁*/
		/*调用存储过程
		IN `@riqi` date,IN `@changdi_id` integer(11),
		IN `@shijian_id` integer(11),IN `@user_id` integer(11),
		IN `@user_name` varchar(16),IN `@cz_user_id` integer(11),
		IN `@cg_id` integer(11),`@out1` integer(11)*/
		$sqlstr="call proc_plyuding(
			'{$arr['riqi']}',{$arr['changdi_id']},
			{$arr['shijian_id']},{$arr['user_id']},'{$arr['user']}',
			{$arr['cz_user_id']},{$arr['cgid']},@out1);";
		file_put_contents('/tmp/1.log',
		"5\t sqlstr:" . $sqlstr . "\n",FILE_APPEND);
		$Model->execute($sqlstr);
		$ret=$Model->query("select @out1");//取存储过程的OUT值
		/*解除表锁定*/
		//$sqlstr="UNLOCK TABLES;";
		//$Model->execute($sqlstr);
		/*END 解除表锁定*/
		
		file_put_contents('/tmp/1.log',
		"6\t ret:" . $ret[0]["@out1"] . ";\n",FILE_APPEND);
		return $ret[0]["@out1"];
	}
	
	public function ajax_ret_plyuding($arrdata){
	  //dump($arrdata);
	  $ajaxpost_ret=array();
	  $arr_tmp=array();
	  file_put_contents('/tmp/1.log',"2\t ajax_ret_plyuding:arrdata-" . $arrdata . "\n",FILE_APPEND);
	  foreach($arrdata as $k=>$v){
		  $arr_tmp=NULL;
		if($k == 0){
			$arr1["cgid"]=$v[0];		//场馆ID号
			$arr1["cz_user_id"]=$v[1];//操作的用户的ID
			$arr1["user_id"]=$v[1];//预订的用户ID
			$arr1["user"]=$v[2];//预订的用户名称
			file_put_contents('/tmp/1.log',"3\t k=0:\t0:".$v[0]."\t1:".$v[1]."\t2:".$v[2]. "\n",FILE_APPEND);
		}else{
			$arr1["riqi"]=$v[1];		//日期
			$arr1["changdi_id"]=$v[2];	//用户预选的场地ID
			$arr1["shijian_id"]=$v[3];	//时间ID
			file_put_contents('/tmp/1.log',"4\t body:\t0:".$v[0]."\t1:".$v[1]."\t2:".$v[2]."\t3:".$v[3]. "\n",FILE_APPEND);
			$ret=$this->insert_yuding($arr1);	//调用函数，判断后插入数据
			/*$ret返回类型：0表示用户选择的场地预订成功，-1表示预订不成功，大于1表示不是使用用户选择的场地预订成功*/
			/*生成数组的一行的ajax的返回数据*/
			$arr_tmp["ddid"]=$v[0];
			$arr_tmp["tdid"]=$v[4];
			$arr_tmp["ret"]=$ret;	
			$ajaxpost_ret[]=$arr_tmp;
			/*end 生成数组一行的返回数据*/
			file_put_contents('/tmp/1.log',"7\t insert_yuding ret:" . $ret . "\n",FILE_APPEND);
			file_put_contents('/tmp/1.log',"8\t ajaxpost_ret:" . $ajaxpost_ret . "\n",FILE_APPEND);
		}/*end if*/
	  }/*end foreach*/
	  return $ajaxpost_ret;
	}
}//class CShowYuding