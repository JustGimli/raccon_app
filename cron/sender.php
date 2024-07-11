<?php

require_once  "../config.php";

global $db;

$query = $db->prepare('SELECT * FROM notify WHERE status=0 LIMIT 0,1000');
$query->execute([]);

$notifies = $query->fetchALL(PDO::FETCH_ASSOC);

foreach($notifies as $n){
	Notify::changeStatus($n['id'],1);
}


foreach($notifies as $n){

	$keyboard=false;
	if($n['keyboards_notify']!=NULL) $keyboard=json_decode($n['keyboards_notify'],true);

	if($n['image_notify']!=NULL){
		$result = TelegramApi::sendPhoto($n['telegram_user_id'],$n['image_notify'],$n['text_notify'],$keyboard);
	}else{
		$result = TelegramApi::sendMessage($n['telegram_user_id'],$n['text_notify'],$keyboard,true);
	}

	Notify::changeStatus($n['id'],2);

}
