<?php
	session_start();
	define("backend","https://sfnd-sports.azurewebsites.net");
	if(!isset($_SERVER["PHP_AUTH_USER"])||!isset($_SERVER["PHP_AUTH_PW"]))
	{
		header("WWW-Authenticate:Basic relam=请输入用户名和密码");
		http_response_code(401);
		die();
	}
	else
	{
		$curl=curl_init();
		curl_setopt($curl,CURLOPT_URL,backend."/auth");
		curl_setopt($curl,CURLOPT_POST,true);
		curl_setopt($curl,CURLOPT_POSTFIELDS,["username"=>$_SERVER["PHP_AUTH_USER"],"password"=>$_SERVER["PHP_AUTH_PW"]]);
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
		$data=json_decode(curl_exec($curl));
		if($data)
		{
			if($data->status=="error")
			{
				if($data->error=="username or password error")
				{
					http_response_code(401);
					die();
				}
				else
				{
					http_response_code(401);
					die();
				}
			}
			else
			{
				$_SESSION["class"]=$_SERVER["PHP_AUTH_USER"];
			}
		}
		else
		{
			http_response_code(401);
			die();
		}
	}
?>
<!DOCTYPE html>
<html>
<head>
    <script src="https://cdn.staticfile.org/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://cdn.staticfile.org/twitter-bootstrap/5.1.1/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.staticfile.org/twitter-bootstrap/5.1.1/css/bootstrap.min.css">
	<script>
		"use strict";
		const classID="<?php echo $_SERVER["PHP_AUTH_USER"];?>";
		const apiURL="request.php";
		var projectList=null;
		var token=null;
		function Initialize(){
			$.get(apiURL,{request:"getToken",class:classID},function(data,status){
				if(status=="success"){
					token=data;
					$.get(apiURL,{request:"getProjects",class:classID,token:token},function(data,status){
						if(status=="success"){
							projectList=JSON.parse(data);
							token=projectList.token;
							if(projectList.status!="success"){
								alert("拉取项目列表失败");
								return false;
							}
							else{
								projectList=projectList.projects;
								getTable();
							}
						}
						else{
							alert("拉取项目列表失败");
							return false;
						}
					});
				}
			});
		}
		function addItem(id="",name="",project){
			let tableLine='<tr><td><div class="input-group"><input type="number" step="1" min="0" class="form-control id" placeholder="学号" value="'+id+'"></div></td><td><div class="input-group"><input type="text" class="form-control name" placeholder="姓名" value="'+name+'"></div></td><td><span class="project" data-project="'+project.id+'">'+project.name+'</span></td></tr>';
			$("#table").append(tableLine);
			$("#table").children("tr:last").find("select").val(project);
		}
		function getTable(){
			let records=[];
			$.get(apiURL,{request:"getRecord",class:classID,token:token},function(data,status){
				data=JSON.parse(data);
				if(status=="success"){
					token=data.token;
					if(data.status=="success"){
						data=data.class_record;
						let len=projectList.length;
						let i=0;
						for(;i<len;i++){
							records.push({id:"",name:"",project:projectList[i]});
						}
						let plen=len;
						len=data.length;
						for(i=0;i<len;i++){
							for(let n=0;n<plen;n++){
								if(records[n].project.id==data[i].project){
									records[n].id=data[i].school_id;
									records[n].name=data[i].name;
									break;
								}
							}
						}
						for(i=0;i<plen;i++){
							addItem(records[i].id,records[i].name,records[i].project);
						}
					}
					else{
						alert("加载运动员数据失败");
					}
				}
				else{
					alert("加载运动员数据失败");
				}
			});
		}
		function upload(){
			let records=[];
			let flag=true;
			let re=/^\s*$/;
			$("#table>tr").each(function(){
				let id=$(this).find(".id").val();
				let name=$(this).find(".name").val();
				let project=$(this).find(".project").attr("data-project");
				records.push({school_id:id,name:name,project:project});
				if(!re.test(id)&&(id.length<classID.length||id.substring(0,classID.length)!=classID||id.length!=classID.length+2)){
					let len=projectList.length;
					let p=undefined;
					for(let i=0;i<len;i++){
						if(projectList[i].id==project){
							p=projectList[i].name;
							break;
						}
					}
					alert(p+"项目学号填写错误");
					flag=false;
					return false;
				}
				if(!re.test(id)&&re.test(name)){
					let len=projectList.length;
					let p=undefined;
					for(let i=0;i<len;i++){
						if(projectList[i].id==project){
							p=projectList[i].name;
							break;
						}
					}
					alert(p+"项目姓名未填写");
					flag=false;
					return false;
				}
			});
			if(!flag){
				return false;
			}
			$.ajax({
				type:"post",
				url:apiURL+"?request=updateRecord&class="+classID+"&token="+token,
				data:{athletes:records},
				success:function(data){
					data=JSON.parse(data);
					token=data.token;
					if(data.status=="success"){
						alert("提交成功");
					}
					else{
						alert("提交失败");
					}
				}
			});
		}
		window.onload=function(){
			Initialize();
			let d=new Date();
			if(d.getDay()==4){
				console.warn("KFC crazy Thursday need ￥50");
			}
		}
	</script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta charset="utf-8" />
    <title>体育节数据登记系统</title>
</head>
<body>
    <div class="container pt-5 mt-3">
		<div id="tips" style="color:#DC3545;">请在对应项目前的输入框内填入运动员学号及姓名。学号格式：登录账号（即xx届+班级）+学号，如：250703。不报名可留空（4x100米为必报项目）</br>每个项目最多可以报名两位同学，请分别填写</div>
        <table class="table">
            <thead>
                <tr>
                    <th>学号</th>
                    <th>姓名</th>
                    <th>项目</th>
                </tr>
            </thead>
			<tbody id="table">
			</tbody>
        </table>
		<button type="button" class="btn btn-success" onclick="upload();">上传</button>
    </div>
</body>
</html>

