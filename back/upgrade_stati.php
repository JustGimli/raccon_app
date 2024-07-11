<?php

require_once  "../config.php";
$content =json_decode(file_get_contents('php://input'),true);

$telegram_user_id=$content['telegram_user_id'];
$stat_id=$content['stat_id'];

if(!User::checkHashApp($telegram_user_id,$content['hash_app'])){
	die(1);
}

if(time()-User::getLastActiveTime($telegram_user_id)<3) die();

User::setLastActiveTime($telegram_user_id);

if(!is_int($stat_id)) die(2);

if(!isset($config_stati[$stat_id])) die(3);

$user = User::get($telegram_user_id);

//находим текущий уровень статы пользователя
if(isset($user['stati_lvl'][$stat_id])){
	$current_stat_lvl = $user['stati_lvl'][$stat_id];
}else{
	if(isset($config_stati[$stat_id]['levels'][0])){
		$current_stat_lvl = 0;
	}else{
		$current_stat_lvl = 1;
	}
}

$next_stat_lvl = $current_stat_lvl+1;

//проверяем, что следующий уровень есть
if(!isset($config_stati[$stat_id]['levels'][$next_stat_lvl])) die(4);

$price_upgrade = $config_stati[$stat_id]['levels'][$next_stat_lvl]['price'];

if($user['balance']<$price_upgrade){

	echo json_encode([
		'status'=>'no_money',
		'price'=>$price_upgrade,
		'balance'=>$user['balance'],
		'current_stat_lvl'=>$current_stat_lvl,
	]);

	die();
};

if(isset($config_stati[$stat_id]['levels'][$current_stat_lvl+2])){
	$has_next_level=1;
	$price_next_level=$config_stati[$stat_id]['levels'][$current_stat_lvl+2]['price'];
}else{
	$has_next_level=0;
	$price_next_level=0;
}

User::addBalance($telegram_user_id,-$price_upgrade);
User::upgradeUserStataLvl($telegram_user_id,$user['stati_lvl'],$stat_id,$next_stat_lvl);

$new_user=User::get($telegram_user_id);
$user_stati = User::getUserStati($telegram_user_id);

$user_stati['tap_earn_coin_name']=User::declension($user_stati['tap_earn'],"монета","монеты","монет");
$user_stati['hour_earn_coin_name']=User::declension($user_stati['tap_earn'],"монета","монеты","монет");

$upgrade_box=Format::upgrade_box($new_user,$stat_id,$config_stati[$stat_id]);

Clickhouse::insert("default.raccoon_events_logs",[
	'telegram_user_id'=>(int)$telegram_user_id,
	'event_type'=>(string)'new_upgrade',
	'balance_change'=>(int)-$price_upgrade,
	'balance_after'=>(int)$user['balance'],
	'ip_address'=>(string)User::getIp(),
	'platform'=>($user['platform']=='android' || $user['platform']=='ios') ? $user['platform'] : NULL,
	'level'=>(int)$user['level'],
	'date_reg' => (int)$user['date_reg'],
	'stati_lvl'=>(string)json_encode($new_user['stati_lvl']),
	'stat_id'=>(int)$stat_id,
	'new_stat_level'=>(int)$next_stat_lvl,
	//'stat_value'=>(int)$config_stati[$stat_id]['levels'][],
]);

echo json_encode([
	'status'=>'success',
	'for'=>$config_stati[$stat_id]['for'],
	'stat_id'=>$stat_id,
	'upgrade_box'=>$upgrade_box,
	'user_stati'=>$user_stati,
	'balance'=>$new_user['balance'],
]);

