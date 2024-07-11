<?php

require_once  "../config.php";
$content =json_decode(file_get_contents('php://input'),true);

$telegram_user_id=$content['telegram_user_id'];

if(!User::checkHashApp($telegram_user_id,$content['hash_app'])){
	die(1);
}

if(time()-User::getLastActiveTime($telegram_user_id)<3) die();

User::setLastActiveTime($telegram_user_id);


$day_id = $content['task_id'];

$user=User::get($telegram_user_id);

$timestamp=time();

// Определение начала дня
$startOfDay = mktime(0, 0, 0, date('n', $timestamp), date('j', $timestamp), date('Y', $timestamp));

// Определение конца дня
$endOfDay = mktime(23, 59, 59, date('n', $timestamp), date('j', $timestamp), date('Y', $timestamp));

//уже активировал сегодня
if($user['last_get_day_reward']>$startOfDay && $endOfDay>$user['last_get_day_reward']){
	echo json_encode([
		'status'=>'already'
	]);

	die();
}

$timestamp_2day_ago=$timestamp-172800;

$endOfDay_2DayAgo = mktime(23, 59, 59, date('n', $timestamp_2day_ago), date('j', $timestamp_2day_ago), date('Y', $timestamp_2day_ago));

if($endOfDay_2DayAgo>$user['last_get_day_reward']){
	$activate_day = 1;
}else{
	$activate_day = $user['number_active_day']+1;
}

$reward=$config_evryday[$activate_day]['reward'];

User::activateDailyReward($telegram_user_id,$activate_day);

User::addBalance($telegram_user_id,$reward);

$user=User::get($telegram_user_id);

echo json_encode([
	'status'=>'success',
	'balance'=>$user['balance'],
]);






