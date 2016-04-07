 <?php
/*
/*自定义的全局函数
/www/Application/Admin/Common/function.php

用户的session变量如下:
$loginUser=I('session.user');		//取session的用户变量值
echo "user:" . $loginUser['user'];	//用户的名称
echo "id:" . $loginUser['id']; 		//用户的ID号
echo "admin:" . $loginUser['admin'];//用户是否为管理员

function get_user_id();
function get_user_name();
function get_user_admin();
function ret_sj($shijian_id);
function ret_cd($changdi_id);


function listmenu();
function ret_get_cgid();
function get_all_cgid();
function get_cgid();
function list_cd($cgId=0);
function list_sj();..
*/
function get_user_id(){
//通过读取get取得用户的信息
	$user=I('session.user');
	if($user){
		return $user['id'];
	}
	return -1;
}
function get_user_name(){
//通过读取get取得用户的信息
	$user=I('session.user');
	if($user){
		return $user['user'];
	}
	return -1;
}
function get_user_admin(){
//通过读取get取得用户的信息
	$user=I('session.user');
	if($user){
		return $user['admin'];
	}
	return -1;
}

function ret_sj($shijian_id){
//根据时间ID返回时间名称
	$db=M('shijian');
	$sqlstr["id"]=$shijian_id;
	$ret=$db->where($sqlstr)->getField("name");
	return urlencode($ret);
}

function ret_cd($changdi_id){
//根据场地ID返回场地名称
	$db=M('changdi');
	$sqlstr["id"]=$changdi_id;
	$ret=$db->where($sqlstr)->getField("name");
	return urlencode($ret);
}

function listmenu()
//列出菜单
{
		
}

function ret_get_cgid(){
//从get参数中取出cgid号，并与数据库中的场馆id号进行对比，看是否正确，防止用户直接从url参数中输入get的cgid号
	if(I('get.cgid')){
		$cg_id=I('get.cgid');
	}else{
		$cg_id=-1;
		return -1;
	}
	$arr_cgid=get_all_cgid();
	foreach($arr_cgid as $k=>$v){
		if($v['id'] == $cg_id){return $cg_id;}
	}
	return -1;
}

function get_all_cgid(){
//取出所有场馆的数组
	$cg=M('changdi');
	$sqlstr['shanchu']=0;
	$sqlstr['shangji_id']=0;
	$cgList=$cg->where($sqlstr)->select();
	//dump($cgList);
	if($cgList){
		//echo $cgList['id'];
		//dump($cgList);
		return $cgList;
	}else{
		return null;
	}
}

function get_cgid(){
//取出其中一个场馆的ID号，用于在首页显示预订
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
}

function list_cd($cgId=0){
//取出场馆ID对应的所有场地
	if($cdId == 0){
		$cdId=ret_get_cgid();
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

function list_sj(){
//列出时间表的时间点
	$sj=M('Shijian');
	$sjList=$sj->where('shanchu=0 or shanchu=null')->select();
	if(!$sjList){
		return null;
	}
	return $sjList;
}

