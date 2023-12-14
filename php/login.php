<?php
require_once "../assets/serverConfig.php";

//Resolves Every Code Error into Server Error
function ServerError(){
    header("Internal Server Error",true,500);
    exit("SERVER_ERROR");
}
set_exception_handler("ServerError");
error_reporting(E_ERROR | E_PARSE); //Supresses Warnings

if($_SERVER["REQUEST_METHOD"]==="GET"){
    header("Method Not Allowed",true,405);
    exit();
}

try{
    if($_POST["REQUEST"]==="ValidateSession"){
        if($redis->get($_POST["SessionToken"])){
            exit("Session_Valid");
        } else {
            exit("Session_Invalid");
        }
    }
} catch(Exception $e){}

$email=filter_var($_POST["email"],FILTER_SANITIZE_EMAIL);
$password=$_POST["password"];


function userExists($emailId) : bool{
    $tableName = $GLOBALS['table'];
    $q = $GLOBALS['sql']->prepare("SELECT * FROM {$tableName} WHERE mail=?");
    $q->bind_param("s",$emailId);
    $q->execute();
    $q->store_result();

    if($q->num_rows){
        return true;
    } else  {
        return false;
    }
}

if(!userExists($email)){
    exit("USER_NOT_EXISTS");
}

$getUserPassword = $sql->prepare("SELECT password FROM {$table} WHERE mail=?");
$getUserPassword->bind_param("s",$email);

$getUserPassword->execute();

$getUserPassword->store_result();
$getUserPassword->bind_result($userPass);
$getUserPassword->fetch();


if($password !== $userPass){
    exit("USER_ENTERED_NCP");
}


$res = $userProfile->findOne(["email"=>$email])["_id"]->__toString();

$redis->setEx($res,$redisKeyExpire,$email);
echo $res;
?>

