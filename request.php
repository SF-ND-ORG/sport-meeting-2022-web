<?php
    define("backend","https://sfnd-sports.azurewebsites.net");
    function generateToken(){
        if(isset($_SESSION["class"])){
            $_SESSION["time"]=time();
            $_SESSION["token"]=md5($_SESSION["class"].$_SESSION["time"]);
            return $_SESSION["token"];
        }
        else
        {
            http_response_code(403);
            die();
        }
    }
    function validateToken($class,$token){
        if(isset($_SESSION["class"])&&isset($_SESSION["time"]))
        {
            $t=md5($_SESSION["class"].$_SESSION["time"]);
            if($token==$_SESSION["token"]&&$t==$_SESSION["token"])
            {
                return true;
            }
            else
            {
                http_response_code(403);
                die();
            }
        }
        else
        {
            http_response_code(403);
            die();
        }
    }
    function getRecord($class,$token)
    {
        if(validateToken($class,$token))
        {
            $curl=curl_init();
            curl_setopt($curl,CURLOPT_URL,backend."/get_class_record?class=".$class);
            curl_setopt($curl,CURLOPT_HTTPGET,true);
            curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
            $data=json_decode(curl_exec($curl));
            $data->token=generateToken($_SESSION["class"]);
            echo json_encode($data);
        }
        else
        {
            http_response_code(403);
            die();
        }
    }
    function updateRecord($class,$record,$token)
    {
        if(validateToken($class,$token))
        {
            $record=json_encode(["class"=>$class,"athletes"=>$record]);
            $curl=curl_init();
            curl_setopt($curl,CURLOPT_URL,backend."/update_athletes");
            curl_setopt($curl,CURLOPT_POST,true);
            curl_setopt($curl,CURLOPT_POSTFIELDS,$record);
            curl_setopt($curl,CURLOPT_HTTPHEADER,array("Content-Type:application/json; charset:utf-8;","Content-Length:".strlen($record)));
            curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
            $data=json_decode(curl_exec($curl));
            $data->token=generateToken();
            echo json_encode($data);
        }
        else
        {
            http_response_code(403);
            die();
        }
    }
    function getProjects($class,$token)
    {
        if(validateToken($class,$token))
        {
            $curl=curl_init();
            curl_setopt($curl,CURLOPT_URL,backend."/get_projects");
            curl_setopt($curl,CURLOPT_HTTPGET,true);
            curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
            $data=json_decode(curl_exec($curl));
            $data->token=generateToken();
            echo json_encode($data);
        }
        else
        {
            http_response_code(403);
            die();
        }
    }
    session_start();
    switch($_GET["request"])
    {
        case "getToken":
            if(isset($_GET["class"])&&$_GET["class"]==$_SESSION["class"])
            {
                echo generateToken();
                break;
            }
            else
            {
                http_response_code(403);
                die();
            }
        case "getProjects":
            if(isset($_GET["class"])&&isset($_GET["token"]))
            {
                getProjects($_GET["class"],$_GET["token"]);
                break;
            }
            else
            {
                http_response_code(403);
                die();
            }
        case "getRecord":
            if(isset($_GET["class"])&&isset($_GET["token"]))
            {
                getRecord($_GET["class"],$_GET["token"]);
                break;
            }
            else
            {
                http_response_code(403);
                die();
            }
        case "updateRecord":
            if(isset($_GET["class"])&&isset($_POST["athletes"])&&isset($_GET["token"]))
            {
                updateRecord($_GET["class"],$_POST["athletes"],$_GET["token"]);
                break;
            }
            else
            {
                http_response_code(403);
                die();
            }
        default:
            http_response_code(403);
            die();
    }
?>