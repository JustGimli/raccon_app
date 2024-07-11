<?php

require_once  "../config.php";

global $db;

$timestamp=time();

// Определение начала дня
$startOfDay = mktime(0, 0, 0, date('n', $timestamp), date('j', $timestamp), date('Y', $timestamp));

// Определение конца дня
$endOfDay = mktime(23, 59, 59, date('n', $timestamp), date('j', $timestamp), date('Y', $timestamp));


$query = $db->prepare('SELECT * FROM users WHERE last_get_day_reward<:startofday');
$query->execute([
	'startofday'=>$startOfDay
]);
$users = $query->fetchALL(PDO::FETCH_ASSOC);


foreach($users as $u){
	$text='<b>🗣 Не забудь получить ежедневную награду!</b>'.PHP_EOL.PHP_EOL.'До конца дня осталось ⏳ <b>меньше 1-го часа</b>, а ты еще не получил награду за вход в игру! Заходи в игру 10 дней подряд и получи 💰 <b>5 000 000 монет</b>!';

	$keyboard=[[
		['text' => '💰 Получить награду', 'url' => 'https://t.me/spin_racoon_bot/play/?startapp=d_tasks']
	]];

	$image='https://tg.appenot.com/share1.jpg';

	Notify::add($u['telegram_user_id'],'everyday_reward',$text,$keyboard,$image);

}



