<?php

/*
	CREATE TABLE iserial.logs
	(
		date Date DEFAULT toDate(time),
		time DateTime,
		initiator String,
		duration UInt32,
		type Enum8('php' = 1, 'api' = 2, 'memcache' = 3),
		result Enum8('event' = 1, 'error' = 2),
		user_id UInt32,
		actor String,
		data String,
		error_code UInt32
	)
	ENGINE = MergeTree()
	PARTITION BY date
	ORDER BY (intHash32(user_id), user_id)
	SAMPLE BY intHash32(user_id)
	SETTINGS index_granularity = 8192
 */

class Clickhouse
{

	const CLICKHOUSE_SERVER = 'http://54.36.177.204';
	const CLICKHOUSE_PORT = 8124;

	const TABLE_LOGS = 'iserial.logs';

	public static function insert($table, $row)
	{

		$row['time'] = time();
		$table = $table . '(' . implode(',', array_keys($row)) . ')';

		$values = [];
		foreach ($row as $v) {
			$values[] = self::normalizeValue($v);
		}

		$query = '(' . implode(',', $values) . ')';

		return self::call($table, $query);
	}

	private static function normalizeValue($v)
	{
		if (is_int($v) || is_float($v)) {
			return $v;
		}

		return "'" . addslashes($v) . "'";
	}

	public static function call($table, $query)
	{

		//global $db;

		$url = self::CLICKHOUSE_SERVER . ":" . self::CLICKHOUSE_PORT . "?table=" . $table.'&debug=1';

		$start_execute_ch_ms=microtime(true);

		$ch = curl_init();

		$headers = [
			'Content-Type: application/x-www-form-urlencoded',
			'Host: 127.0.0.1',
		];

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		//curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$query);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);


		$res = curl_exec($ch);
		$info = curl_getinfo($ch);
		//print_r();
		//print_r(curl_error($ch));

		curl_close($ch);

		//$insert_ch_logs = $db->prepare('INSERT INTO ch_logs (duration,data,time_add,curl_info) VALUES (:duration,:data,:time_add,:curl_info)');

		/*$insert_ch_logs->execute([
			'duration' =>Clickhouse::formatm(microtime(true)-$start_execute_ch_ms),
			'data' =>$query,
			'time_add' =>time(),
			'curl_info' =>json_encode($info)
		]);*/

		//print_r($test);
		return $info;



		//$url = $clickhouse_server . ":" . $clickhouse_port . "?table=" . $table;

		//$res = json_decode(file_get_contents($url, $use_include_path = false, $context), true);

		if (!$res || empty($res['http_code']) || ($res['http_code'] === 200)) {
			return false;
		}
		return true;
	}

	public static function formatm($microtime){
		return number_format($microtime,3,'','');
	}
}
