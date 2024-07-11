<?php

class User
{

	public static function get($telegram_user_id)
	{
		global $mem;

		if (!($user = $mem->get(CACHE_USER_PREFIX_NAME . $telegram_user_id))) {

			global $db;

			$query = $db->prepare('SELECT * FROM users WHERE telegram_user_id=:telegram_user_id');
			$query->execute(['telegram_user_id' => $telegram_user_id]);
			$user = $query->fetch(PDO::FETCH_ASSOC);

			if ($user) {

				$user['stati_lvl']=json_decode($user['stati_lvl'],true);

				$mem->set(CACHE_USER_PREFIX_NAME . $telegram_user_id, $user, CACHE_TIMEOUT_USER);
			}

		}

		//$user['stati_lvl']=json_decode($user['stati_lvl'],true);

		return $user;
	}

	public static function getLastActiveTime($telegram_user_id)
	{
		global $mem;

		if (!($last_active_time = $mem->get('racoon_la_' . $telegram_user_id))) {
			return false;
		}

		return $last_active_time;
	}

	public static function setLastActiveTime($telegram_user_id)
	{
		global $mem;

		$mem->set('racoon_la_' . $telegram_user_id, time(), 0);

		return true;
	}

	public static function getUserStati($telegram_user_id)
	{

		//global $mem;

		//if (!($stati = $mem->get(CACHE_USER_STATI_PREFIX_NAME . $telegram_user_id))) {

			global $levels,$config_stati;

			$user = self::get($telegram_user_id);

			$user_lvl=$user['level'];

			//tap_earn
			$base_tap_earn = $levels[$user_lvl]['base_tap_earn'];
			$tap_earn=$base_tap_earn;

			//hour_earn
			$base_hour_earn = $levels[$user_lvl]['base_hour_earn'];
			$hour_earn=$base_hour_earn;

			//max_energy
			$base_max_energy = $levels[$user_lvl]['base_max_energy'];
			$max_energy = $base_max_energy;

			//energy_regeneration_rate
			$base_energy_reg_rate = $levels[$user_lvl]['base_energy_reg_rate'];
			$energy_regeneration_rate = $base_energy_reg_rate;

			//loto_ticket
			$loto_ticket = $levels[$user_lvl]['loto_ticket'];

			//circle_luck
			$circle_luck = $levels[$user_lvl]['circle_luck'];

			//passive_earn_time_seconds
			$passive_earn_time_seconds=10800;

			if(is_array($user['stati_lvl'])) {
				foreach ($user['stati_lvl'] as $stat_id => $stat_level) {

					if (!isset($config_stati[$stat_id]['levels'][$stat_level])) continue;

					if ($config_stati[$stat_id]['for'] == 'tap_earn') {
						$tap_earn = $tap_earn + $config_stati[$stat_id]['levels'][$stat_level]['value'];
					} elseif ($config_stati[$stat_id]['for'] == 'hour_earn') {
						$hour_earn = $hour_earn + $config_stati[$stat_id]['levels'][$stat_level]['value'];
					} elseif ($config_stati[$stat_id]['for'] == 'max_energy') {
						$max_energy = $max_energy + $config_stati[$stat_id]['levels'][$stat_level]['value'];
					} elseif ($config_stati[$stat_id]['for'] == 'energy_regeneration_rate') {
						$energy_regeneration_rate = $energy_regeneration_rate + $config_stati[$stat_id]['levels'][$stat_level]['value'];
					} elseif ($config_stati[$stat_id]['for'] == 'loto_ticket') {
						$loto_ticket = $loto_ticket + $config_stati[$stat_id]['levels'][$stat_level]['value'];
					} elseif ($config_stati[$stat_id]['for'] == 'circle_luck') {
						$circle_luck = $circle_luck + $config_stati[$stat_id]['levels'][$stat_level]['value'];
					} elseif ($config_stati[$stat_id]['for'] == 'passive_earn_time_seconds') {
						$passive_earn_time_seconds = $passive_earn_time_seconds + $config_stati[$stat_id]['levels'][$stat_level]['value'];
					}

				}
			}

			//friend_earn
			$friend_earn = $hour_earn*24;

			if($friend_earn<50000) $friend_earn=50000;

			$stati=[
				'tap_earn'=>$tap_earn,
				'hour_earn'=>$hour_earn,
				'max_energy'=>$max_energy,
				'energy_regeneration_rate'=>$energy_regeneration_rate,
				'loto_ticket'=>$loto_ticket,
				'circle_luck'=>$circle_luck,
				'friend_earn'=>$friend_earn,
				'passive_earn_time_seconds'=>$passive_earn_time_seconds,
			];

			//$mem->set(CACHE_USER_STATI_PREFIX_NAME . $telegram_user_id, $stati, CACHE_TIMEOUT_USER_STATI);

		//}

		return $stati;

	}


	public static function getCurrentEnergy($telegram_user_id,$energy_regeneration_rate,$max_energy)
	{
		global $mem;

		if(!$balance_update = $mem->get('racoon_bu_' . $telegram_user_id)) return $max_energy;

		if(!isset($balance_update['last_time']) || !isset($balance_update['energy'])) return $max_energy;

		$temp_time=time()-$balance_update['last_time'];
		$acamulate_energy=$temp_time*$energy_regeneration_rate;

		$current_energy=$acamulate_energy+$balance_update['energy'];

		if($current_energy>$max_energy) return $max_energy;

		return $current_energy;

	}

	public static function create($telegram_user_id, $from, $ref_id,$user_target_source)
	{
		global $db;

		$create_user = $db->prepare("INSERT INTO users (telegram_user_id, date_reg, username, first_name, last_name, last_time_active, balance,is_premium,is_bot,lang_code, ref_id, platform,version_bot,geoip_country,geoip_region,geoip_city,ip_address,useragent,user_source, status) VALUES (:telegram_user_id, :date_reg, :username, :first_name, :last_name, :last_time_active, :balance, :is_premium,:is_bot,:lang_code,:ref_id,:platform,:version_bot,:geoip_country,:geoip_region,:geoip_city, :ip_address, :useragent,:user_source, :status)");

		return $create_user->execute([
			'telegram_user_id' => $telegram_user_id,
			'date_reg' => time(),
			'username' => $from['username'],
			'first_name' => $from['first_name'],
			'last_name' => $from['last_name'],
			'last_time_active' => time(),
			'balance' => 0,
			'is_premium' => $from['is_premium'],
			'is_bot' => $from['is_bot'],
			'lang_code' => $from['language_code'],
			'ref_id' => ($ref_id) ? $ref_id : NULL,
			'platform' => $from['platform'],
			'version_bot' => $from['version'],
			'geoip_country' => (isset($_SERVER['GEOIP_COUNTRY_CODE']) && $_SERVER['GEOIP_COUNTRY_CODE']!='') ? $_SERVER['GEOIP_COUNTRY_CODE'] : NULL,
			'geoip_region' => (isset($_SERVER['GEOIP_REGION']) && $_SERVER['GEOIP_REGION']!='') ? $_SERVER['GEOIP_REGION'] : NULL,
			'geoip_city' => (isset($_SERVER['GEOIP_CITY']) && $_SERVER['GEOIP_CITY']!='') ? $_SERVER['GEOIP_CITY'] : NULL,
			'ip_address' => (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) ? $_SERVER['HTTP_CF_CONNECTING_IP'] : $_SERVER['REMOTE_ADDR'],
			'useragent' => (isset($_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_USER_AGENT']!='') ? $_SERVER['HTTP_USER_AGENT'] : NULL,
			'user_source' => $user_target_source,
			'status' => 1
		]);

	}

	public static function upgradeUserLevel($telegram_user_id,$lvlup)
	{
		global $db;

		$update = $db->prepare("UPDATE users SET level=:level WHERE telegram_user_id=:telegram_user_id");

		$update->execute([
			'telegram_user_id' => $telegram_user_id,
			'level' => $lvlup,
		]);

		self::clearCache($telegram_user_id);

		return true;

	}

	public static function upgradeUserStataLvl($telegram_user_id,$user_stati_lvl,$stat_id,$next_stat_lvl)
	{
		global $db,$mem;

		$user_stati_lvl[$stat_id]=$next_stat_lvl;

		$update = $db->prepare("UPDATE users SET stati_lvl=:stati_lvl WHERE telegram_user_id=:telegram_user_id");

		$update->execute([
			'telegram_user_id' => $telegram_user_id,
			'stati_lvl' => json_encode($user_stati_lvl),
		]);

		self::updateUserCache($telegram_user_id,[
			'stati_lvl'=>$user_stati_lvl
		]);

		$mem->delete(CACHE_USER_STATI_PREFIX_NAME . $telegram_user_id);

		return true;

	}

	public static function addBalance($telegram_user_id, $balance)
	{
		global $db;

		$update = $db->prepare("UPDATE users SET balance=balance+:balance WHERE telegram_user_id=:telegram_user_id");

		$update->execute([
			'telegram_user_id' => $telegram_user_id,
			'balance' => $balance,
		]);

		self::updateUserCache($telegram_user_id,[
			'balance'=>$balance
		]);


		return true;

	}

	public static function setUserLastTimeActive($telegram_user_id)
	{
		global $db;

		$update = $db->prepare("UPDATE users SET last_time_active=:last_time_active WHERE telegram_user_id=:telegram_user_id");

		$time= time();

		$update->execute([
			'telegram_user_id' => $telegram_user_id,
			'last_time_active' => $time,
		]);

		self::updateUserCache($telegram_user_id,[
			'last_time_active'=>$time
		]);


		return true;

	}

	public static function updateCountRef($telegram_user_id)
	{
		global $db;

		$query = $db->prepare('SELECT count(*) as cc FROM users WHERE ref_id=:ref_id');
		$query->execute(['ref_id' => $telegram_user_id]);
		$count_ref = $query->fetch(PDO::FETCH_ASSOC);

		if ($count_ref) {

			$count_ref = $count_ref['cc'];

			$update = $db->prepare("UPDATE users SET friends_count=:friends_count WHERE telegram_user_id=:telegram_user_id");

			$update->execute([
				'telegram_user_id' => $telegram_user_id,
				'friends_count' => $count_ref,
			]);

			self::updateUserCache($telegram_user_id,[
				'friends_count'=>$count_ref
			]);

			return true;
		}

		return false;

	}

	public static function generateHashApp($telegram_user_id)
	{
		return md5(md5($telegram_user_id.SECRETHASH).SECRETHASH);
	}

	public static function checkHashApp($telegram_user_id,$hash_app)
	{
		if($hash_app == md5(md5($telegram_user_id.SECRETHASH).SECRETHASH)) return true;

		return false;
	}

	public static function declension($number, $singular, $few, $many) {

		$number = abs($number) % 100;
		$lastDigit = $number % 10;

		if ($number > 10 && $number < 20) {
			return $many;
		}
		if ($lastDigit > 1 && $lastDigit < 5) {
			return $few;
		}
		if ($lastDigit == 1) {
			return $singular;
		}

		return $many;
	}

	public static function clearCache($telegram_user_id) {
		global $mem;

		$mem->delete(CACHE_USER_PREFIX_NAME . $telegram_user_id);
		$mem->delete(CACHE_USER_STATI_PREFIX_NAME . $telegram_user_id);
		$mem->delete('racoon_bu_' . $telegram_user_id);

	}

	public static function updateUserCache($telegram_user_id,$params) {

		global $mem;

		if (!($user = $mem->get(CACHE_USER_PREFIX_NAME . $telegram_user_id))) return false;

		foreach($params as $key => $value){

			if($key=='balance'){
				$user[$key]=$user[$key]+$value;
				continue;
			}

			$user[$key]=$value;
		}

		$mem->set(CACHE_USER_PREFIX_NAME . $telegram_user_id, $user, CACHE_TIMEOUT_USER);

		return true;

	}

	public static function GetIdHash($id)
	{

		$user_hash = base_convert($id, 10, 32);
		return $user_hash;

	}

	public static function DecodeIdHash($id_hash){

		$id = base_convert($id_hash,32,10);
		return $id;

	}

	public static function checkFloodControl($telegram_user_id){

		global $mem;

		$flood = $mem->get(CACHE_USER_PREFIX_NAME . $telegram_user_id);


		//$mem->set(CACHE_USER_PREFIX_NAME . $telegram_user_id, $user, CACHE_TIMEOUT_USER);

	}

	public static function activateDailyReward($telegram_user_id,$activate_day){

		global $db;

		$update = $db->prepare("UPDATE users SET number_active_day=:number_active_day, last_get_day_reward=:last_get_day_reward WHERE telegram_user_id=:telegram_user_id");

		$last_get_day_reward=time();

		$update->execute([
			'telegram_user_id' => $telegram_user_id,
			'number_active_day' => $activate_day,
			'last_get_day_reward' => $last_get_day_reward,
		]);

		self::updateUserCache($telegram_user_id,[
			'number_active_day'=>$activate_day,
			'last_get_day_reward'=>$last_get_day_reward,
		]);

		return true;
	}

	public static function resetDailyReward($telegram_user_id){

		global $db;

		$update = $db->prepare("UPDATE users SET number_active_day=:number_active_day, WHERE telegram_user_id=:telegram_user_id");

		$update->execute([
			'telegram_user_id' => $telegram_user_id,
			'number_active_day' => 0,
		]);

		self::updateUserCache($telegram_user_id,[
			'number_active_day'=>0,
		]);

		return true;
	}


	public static function setFloodControl($telegram_user_id){

		global $mem;

		$flood = $mem->get('flood_control_' . $telegram_user_id);

		//$flood

		//$mem->set('flood_control_' . $telegram_user_id, $user, CACHE_TIMEOUT_USER);

	}

	public static function getIp(){

		$ip=(isset($_SERVER['HTTP_CF_CONNECTING_IP'])) ? $_SERVER['HTTP_CF_CONNECTING_IP'] : $_SERVER['REMOTE_ADDR'];

		return $ip;

	}

}