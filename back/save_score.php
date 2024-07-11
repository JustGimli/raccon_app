<?php

require_once  "../config.php";
$content =json_decode(file_get_contents('php://input'),true);

$telegram_user_id=$content['telegram_user_id'];

if(!User::checkHashApp($telegram_user_id,$content['hash_app'])){
	die();
}

if($content['tap_count']>100){
	die();
}

if(time()-User::getLastActiveTime($telegram_user_id)<3) die();

User::setLastActiveTime($telegram_user_id);

$user_stati = User::getUserStati($telegram_user_id);
$tap_earn=$user_stati['tap_earn'];

if($content['sound_box_status']==true){
	$tap_earn=$tap_earn+1;
}

$current_energy = User::getCurrentEnergy($telegram_user_id,$user_stati['energy_regeneration_rate'],$user_stati['max_energy']);
if(!$current_energy) $current_energy=$user_stati['max_energy'];

$score = $tap_earn*$content['tap_count'];

//if($score>$content['energy']) $score=$content['energy']-$score;
if($score>$current_energy) $score=$current_energy;

$user=User::get($telegram_user_id);

Clickhouse::insert("default.raccoon_events_logs",[
	'telegram_user_id'=>(int)$telegram_user_id,
	'event_type'=>(string)'save_score',
	'tap_count'=>(int)$content['tap_count'],
	'balance_change'=>(int)$score,
	'balance_after'=>(int)$user['balance']+$tap_earn,
	'energy'=>(int)$content['energy'],
	'ip_address'=>(string)User::getIp(),
	'platform'=>'android',
	'level'=>(int)$user['level'],
	'date_reg' => (int)$user['date_reg'],
	'stati_lvl'=>(string)json_encode($user['stati_lvl']),
	'max_energy'=>(int)$user_stati['max_energy'],
	'energy_regeneration_rate'=>(int)$user_stati['energy_regeneration_rate'],
	'back_energy'=>(int)$current_energy,
]);

$bu=[
	'last_time'=>time(),
	'energy'=>$content['energy'],
];

$mem->set('racoon_bu_' . $telegram_user_id,$bu,10000);


User::addBalance($content['telegram_user_id'],$score);

