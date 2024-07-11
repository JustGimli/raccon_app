<?php

require_once  "../config.php";
$content =json_decode(file_get_contents('php://input'),true);

$telegram_user_id=$content['telegram_user_id'];

if(!User::checkHashApp($telegram_user_id,$content['hash_app'])){
	die(1);
}

if(time()-User::getLastActiveTime($telegram_user_id)<3) die();

User::setLastActiveTime($telegram_user_id);

if(!is_int($content['lvlup'])) die(2);

if(!isset($levels[$content['lvlup']])) die(3);

if($content['lvlup']==1) die(4);

$level=$levels[$content['lvlup']];

$user = User::get($telegram_user_id);

if($user['level']+1!=$content['lvlup']) die(5);

if($user['balance']<$level['price']){

	echo json_encode([
		'status'=>'no_money'
	]);

	die();
};

User::addBalance($telegram_user_id,-$level['price']);
User::upgradeUserLevel($telegram_user_id,$content['lvlup']);

$new_user = User::get($telegram_user_id);

Clickhouse::insert("default.raccoon_events_logs",[
	'telegram_user_id'=>(int)$telegram_user_id,
	'event_type'=>(string)'level_up',
	'balance_change'=>(int)-$level['price'],
	'balance_after'=>(int)$new_user['balance'],
	'ip_address'=>(string)User::getIp(),
	'platform'=>($user['platform']=='android' || $user['platform']=='ios') ? $user['platform'] : NULL,
	'level'=>(int)$user['level'],
	'new_level'=>(int)$content['lvlup'],
	'date_reg' => (int)$user['date_reg'],
	'stati_lvl'=>(string)json_encode($user['stati_lvl'])
]);

echo json_encode([
	'status'=>'success'
]);


