<?php

//if(!isset($_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN']) && $_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN']!=TELEGRAM_SECRET_WEBHOOK_TOKEN) die(1);

require_once __DIR__ . "/../config.php";

$data=json_decode(file_get_contents('php://input'),true);

$is_callback = false;

if(isset($data['callback_query'])){
	$is_callback = true;
	$telegram_user_id=$data['callback_query']['from']['id'];
	$callback_query_id=$data['callback_query']['id'];
	define('CALLBACK_QUERY_ID', $callback_query_id);
	$chat_id = $data['callback_query']['message']['chat']['id'];
	$message_id =  $data['callback_query']['message']['message_id'];
	$callback_data = json_decode($data['callback_query']['data'],true);
	$from = $data['callback_query']['from'];
	if(isset($callback_data['cb_tree']) && $callback_data['cb_tree']==false) $is_callback=false;
	if(isset($callback_data['action'])){
		$is_callback=false;
		//создание пати на покупку
		//if($callback_data['action']=='apc_b_s') $callback_data['action'] = 'action_party_create_buy__sum';
	}

	$message_text='';

}else{
	$telegram_user_id=$data['message']['from']['id'];
	$chat_id = $data['message']['chat']['id'];
	$message_id =  $data['message']['message_id'];
	$from = $data['message']['from'];

	if(isset($data['message']['text'])){
		$message_text=trim($data['message']['text']);
		if($message_text=='') $message_text=false;
	}else{
		$message_text=false;
	}

}

$keyboard=[[
			['text' => '🌀 Играть', 'url' => 'https://t.me/spin_racoon_bot/play']
		]];

//$result = TelegramApi::sendMessage($chat_id,"Раскрути енота и получи ценный призы!");


$result = TelegramApi::sendMessage($chat_id,"Раскрути енота и получи ценные призы!",$keyboard,true);
