<?php

require_once  "../config.php";
$content =json_decode(file_get_contents('php://input'),true);

$telegram_user_id=$content['telegram_user_id'];

if(!User::checkHashApp($telegram_user_id,$content['hash_app'])){
	die(1);
}

if(time()-User::getLastActiveTime($telegram_user_id)<3) die();

User::setLastActiveTime($telegram_user_id);

$task_id = $content['task_id'];

$user_tasks = Tasks::getAllTasksByUser($telegram_user_id);

if(isset($user_tasks[$task_id]) && $user_tasks[$task_id]['status']==2) {
	echo json_encode([
		'status'=>'already'
	]);

	die();
}

User::addBalance($telegram_user_id,$tasks_config[$task_id]['reward']);

if(isset($user_tasks[$task_id]) && $user_tasks[$task_id]['status']==1){
	Tasks::completeTask($telegram_user_id,$task_id);

}else{
	Tasks::createTask($telegram_user_id,$task_id,$tasks_config[$task_id],2);
}

$user=User::get($telegram_user_id);
//$balance = $user['balance']+$user_tasks[$task_id]['reward'];

echo json_encode([
	'status'=>'success',
	'balance'=>$user['balance'],
]);



/*
if(!Tasks::checkAllow($telegram_user_id,$task_id)){
	echo json_encode([
		'status'=>'already'
	]);

	die();
}
*/


