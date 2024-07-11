<?php

class TelegramApi
{

	private static $base_url = "https://api.telegram.org/bot".BOTROKEN."/";

	public static function getMe()
	{

		return self::APICall("getMe");

	}

	public static function setFileLog($data) {
		$fh = fopen('log.txt', 'a') or die('can\'t open file');
		((is_array($data)) || (is_object($data))) ? fwrite($fh, print_r($data, TRUE)."\n") : fwrite($fh, $data . "\n");
		fclose($fh);
	}

	public static function setWebhook($params)
	{

		return self::APICall("setWebhook", $params);

	}

	public static function getUpdates($offset)
	{
		$params = [
			'offset' => $offset,
		];

		return self::APICall("getUpdates", $params);

	}

	public static function sendPhoto($chat_id,$img_url,$caption,$keyboard)
	{
		$params = [
			'chat_id' => $chat_id,
			'photo' => $img_url,
			'caption' => $caption,
			'parse_mode' => 'HTML',
			//'protect_content' => true,
		];

		if($keyboard){
			$params['reply_markup'] = json_encode(array('inline_keyboard' => $keyboard, 'resize_keyboard' => true,'one_time_keyboard'=>true));

		}

		return self::APICall("sendPhoto", $params);

	}


	public static function sendDocument($chat_id,$file_url)
	{
		$params = [
			'chat_id' => $chat_id,
		];

		return self::ApiCallSendFile("sendDocument", $params,$file_url);

	}

	public static function answerCallbackQuery($callback_query_id,$text,$is_alert=false)
	{
		$params = [
			'callback_query_id' => $callback_query_id,
			'text' => $text,
			//'show_alert' => true,
			//'protect_content' => true,
		];

		if($is_alert){
			$params['show_alert']=true;
		}

		return self::APICall("answerCallbackQuery", $params);

	}

	public static function sendMessage($chat_id,$text,$keyboard=false,$keyboard_inline=false)
	{
		$params = [
			'chat_id' => $chat_id,
			'text' => $text,
			'parse_mode' => 'HTML',
			'disable_web_page_preview' => true,
		];

		if($keyboard){
			if($keyboard_inline) {
				$params['reply_markup'] = json_encode(array('inline_keyboard' => $keyboard, 'resize_keyboard' => true,'one_time_keyboard'=>true));
			}else{
				$params['reply_markup'] = json_encode(array('keyboard' => $keyboard, 'resize_keyboard' => true));
			}
		}

		return self::APICall("sendMessage", $params);

	}

	public static function editMessageText($chat_id,$message_id,$text,$keyboard=false,$keyboard_inline=false)
	{
		$params = [
			'chat_id' => $chat_id,
			'message_id' => $message_id,
			'text' => $text,
			'parse_mode' => 'HTML',
			'disable_web_page_preview' => true,
		];

		if($keyboard){
			if($keyboard_inline) {
				$params['reply_markup'] = json_encode(array('inline_keyboard' => $keyboard, 'resize_keyboard' => true,'one_time_keyboard'=>true));
			}else{
				$params['reply_markup'] = json_encode(array('keyboard' => $keyboard, 'resize_keyboard' => true));
			}
		}

		return self::APICall("editMessageText", $params);

	}

	public static function editMessageCaption($chat_id,$message_id,$text,$keyboard=false,$keyboard_inline=false)
	{
		$params = [
			'chat_id' => $chat_id,
			'message_id' => $message_id,
			'caption' => $text,
			'parse_mode' => 'HTML',
			'disable_web_page_preview' => true,
		];

		if($keyboard){
			if($keyboard_inline) {
				$params['reply_markup'] = json_encode(array('inline_keyboard' => $keyboard, 'resize_keyboard' => true,'one_time_keyboard'=>true));
			}else{
				$params['reply_markup'] = json_encode(array('keyboard' => $keyboard, 'resize_keyboard' => true));
			}
		}

		return self::APICall("editMessageCaption", $params);

	}

	public static function editMessageMedia($chat_id,$message_id,$caption,$media,$keyboard=false,$keyboard_inline=false)
	{

		$media=[
			'type'=>'photo',
			'media'=>$media,
			'caption'=>$caption,
			'parse_mode'=>'HTML'
		];

		$params = [
			'chat_id' => $chat_id,
			'message_id' => $message_id,
			'media' => json_encode($media)
		];

		if($keyboard){
			if($keyboard_inline) {
				$params['reply_markup'] = json_encode(array('inline_keyboard' => $keyboard, 'resize_keyboard' => true,'one_time_keyboard'=>true));
			}else{
				$params['reply_markup'] = json_encode(array('keyboard' => $keyboard, 'resize_keyboard' => true));
			}
		}

		return self::APICall("editMessageMedia", $params);

	}

	public static function deleteKeyboard($chat_id,$message_id)
	{
		$params = [
			'chat_id' => $chat_id,
			'message_id' => $message_id,
		];

		$params['reply_markup'] = null;


		return self::APICall("editMessageReplyMarkup", $params);

	}

	private static function ApiCall($method,$params=[])
	{

		$options = array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPGET => true,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => false,
		);

		$url=self::$base_url.$method.'?'.http_build_query($params);

		$ch = curl_init($url);
		curl_setopt_array($ch,$options);
		$content = curl_exec($ch);
		$curl_info = curl_getinfo($ch);
		curl_close( $ch );

		$result['curl_info']=$curl_info;
		$result['params']=$params;



		//print_r($content);

		if ($content) {

			$result['content']=json_decode($content, true);

			if(isset($result['content']['ok']) && $result['content']['ok']){
				$result['status']=true;
			}else{
				$result['status']=false;
			}

			if(isset($result['content']['error_code']) && $result['content']['error_code']==400 && mb_strpos($result['content']['description'], 'exactly the same as a current content')){
				TelegramApi::answerCallbackQuery(CALLBACK_QUERY_ID,'There are no updates');
			}

		}else{
			$result['status']=false;
		}

		$status_answer=0;
		if(isset($result['content']['error_code'])) {
			$status_answer=$result['content']['error_code'];
		}elseif(isset($result['curl_info']['http_code'])){
			$status_answer=$result['curl_info']['http_code'];
		}

		global $start_execute_time_ms;


		//CREATE TABLE default.cosmobot_listner_request (date Date DEFAULT toDate(time), time DateTime, telegram_user_id UInt32, request_id String, bot_type String, duration UInt32, telegram_answer String, status_answer UInt32 ) ENGINE = MergeTree() ORDER BY date PARTITION BY date SETTINGS index_granularity = 8192

		//self::wrapError($method,$params,$content,$curl_info);

		return $result;
	}

	private static function ApiCallSendFile($method,$params=[],$filelink=false) {

		$url=self::$base_url.$method.'?'.http_build_query($params);

		$options = [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPGET => true,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_HTTPHEADER=>[
				"Content-Type:multipart/form-data"
			],
			CURLOPT_INFILESIZE=>filesize($filelink),
			CURLOPT_POSTFIELDS=>[
				"chat_id"=>$params['chat_id'],
				"document"     => new CURLFile(realpath($filelink))
			]
		];


		$ch = curl_init($url);
		curl_setopt_array($ch,$options);
		$content = curl_exec($ch);
		$curl_info = curl_getinfo($ch);
		curl_close( $ch );

		$result['curl_info']=$curl_info;
		$result['params']=$params;

		//print_r($content);

		if ($content) {

			$result['content']=json_decode($content, true);

			if(isset($result['content']['ok']) && $result['content']['ok']){
				$result['status']=true;
			}else{
				$result['status']=false;
			}

			if(isset($result['content']['error_code']) && $result['content']['error_code']==400 && mb_strpos($result['content']['description'], 'exactly the same as a current content')){
				TelegramApi::answerCallbackQuery(CALLBACK_QUERY_ID,'There are no updates');
			}

		}else{
			$result['status']=false;
		}

		return $result;
	}

	/*private static function ApiCallPhoto($method,$params=[])
	{

		$options = array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPGET => true,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_HTTPHEADER => ["Content-Type:multipart/form-data"],
			CURLOPT_INFILESIZE => filesize($p['imageurl']),
			CURLOPT_POSTFIELDS => [
				"photo"     => new CURLFile(realpath("{$p['imageurl']}")),
				"caption"     => "{$p['caption']}",
				"disable_notification" => true,
			],
		);

		$url=self::$base_url.$method.'?'.http_build_query($params);
		$url=self::$base_url.$method.';

		$ch = curl_init($url);
		curl_setopt_array($ch,$options);
		$content = curl_exec($ch);
		$curl_info = curl_getinfo($ch);
		curl_close( $ch );

		$result['curl_info']=$curl_info;
		$result['params']=$params;

		//print_r($content);

		if ($content) {

			$result['content']=json_decode($content, true);

			if(isset($result['content']['ok']) && $result['content']['ok']){
				$result['status']=true;
			}else{
				$result['status']=false;
			}

		}else{
			$result['status']=false;
		}

		//self::wrapError($method,$params,$content,$curl_info);

		return $result;
	}*/
}