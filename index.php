﻿<?php
	$url_401='401.html';
	$backend='https://sfnd-sports.azurewebsites.net';
	if(!isset($_SERVER['PHP_AUTH_USER'])||!isset($_SERVER['PHP_AUTH_PW']))
	{
		header('WWW-Authenticate:Basic relam=请输入用户名和密码');
		http_response_code(401);
		die();
	}
	else
	{
		$curl=curl_init();
		curl_setopt($curl,CURLOPT_URL,$backend.'/auth');
		curl_setopt($curl,CURLOPT_POST,true);
		curl_setopt($curl,CURLOPT_POSTFIELDS,['username'=>$_SERVER['PHP_AUTH_USER'],'password'=>$_SERVER['PHP_AUTH_PW']]);
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
		$data=json_decode(curl_exec($curl));
		if($data)
		{
			if($data->status=='error')
			{
				if($data->error=='username or password error')
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
		const classID=<?php echo $_SERVER['PHP_AUTH_USER'];?>;
		const apiURL="https://sfnd-sports.azurewebsites.net";
		var projectList=null;
		var DOMFormattedList="";
		function getProjects(){
			$.get(apiURL+"/get_projects",function(data,status){
				if(status=="success"){
					projectList=JSON.parse(data);
					if(projectList.status!="success"){
						alert("拉取项目列表失败");
						return false;
					}
					else{
						projectList=projectList.projects;
						let s="";
						let len=projectList.length;
						for(let i=0;i<len;i++){
							s=s+'<option value="'+projectList[i].id+'">'+projectList[i].name+'</option>';
						}
						DOMFormattedList=s;
					}
				}
				else{
					alert("拉取项目列表失败");
					return false;
				}
			});
		}
		function addItem(id="",name="",project=projectList[0].id){
			let tableLine='<tr><td><div class="input-group"><input type="number" step="1" class="form-control id" placeholder="学号" value="'+id+'"></div></td><td><div class="input-group"><input type="text" class="form-control name" placeholder="姓名" value="'+name+'"></div></td><td><select class="form-select project">'+DOMFormattedList+'</select></td><td><button type="button" class="btn btn-danger" onclick="$(this).parent().parent().remove()">删除</button></td></tr>';
			$("#table").append(tableLine);
			$("#table").children("tr:last").find("select").val(project);
		}
		function getTable(){
			$.get(apiURL+"/get_class_record",{class:classID},function(data,status){
				data=JSON.parse(data);
				if(status=="success"&&data.status=="success"){
					data=data.class_record;
					let len=data.length;
					for(let i=0;i<len;i++){
						addItem(data[i].school_id,data[i].name,data[i].project);
					}
				}
				else{
					alert("加载运动员数据失败");
				}
			});
		}
		function upload(){
			let records=[];
			$("#table>tr").each(function(){
				let id=$(this).find(".id").val();
				let name=$(this).find(".name").val();
				let project=$(this).find(".project").val();
				records.push({school_id:id,name:name,project:project});
			});
			$.ajax({
				type:"post",
				url:apiURL+"/update_athletes",
				dataType:"json",
				contentType:"application/json",
				data:JSON.stringify({class:classID,athletes:records}),
				success:function(data){
					alert("提交成功")
				}
			});
		}
		window.onload=function(){
			getProjects();
			getTable();
		}
	</script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta charset="utf-8" />
    <title>体育节数据登记系统</title>
</head>
<body>
    <div class="container pt-5 mt-3">
        <table class="table">
            <thead>
                <tr>
                    <th>学号</th>
                    <th>姓名</th>
                    <th>项目</th>
					<th>
						<button type="button" class="btn btn-info" onclick="addItem()">添加</button>
					</th>
                </tr>
            </thead>
			<tbody id="table">
			</tbody>
        </table>
		<button type="button" class="btn btn-success" onclick="upload();">上传</button>
    </div>
</body>
</html>