<?php

class Tasks
{

	public static function checkAllow($telegram_user_id,$task_id)
	{
		global $db;

		$query = $db->prepare('SELECT count(*) as cc FROM tasks WHERE telegram_user_id=:telegram_user_id AND task_id=:task_id');
		$query->execute([
			'telegram_user_id' => $telegram_user_id,
			'task_id' => $task_id,
		]);

		$tasks = $query->fetch(PDO::FETCH_ASSOC);

		if ($tasks && $tasks['cc']==0) return true;

		return false;

	}

	public static function getAllTasksByUser($telegram_user_id)
	{
		global $db;

		$query = $db->prepare('SELECT * FROM tasks WHERE telegram_user_id=:telegram_user_id');
		$query->execute([
			'telegram_user_id' => $telegram_user_id
		]);

		$temp_tasks = $query->fetchALL(PDO::FETCH_ASSOC);

		if(!$temp_tasks || count($temp_tasks)==0) return [];

		foreach($temp_tasks as $tt){
			$tasks[$tt['task_id']]=$tt;
		}

		return $tasks;

	}

	public static function completeTask($telegram_user_id,$task_id)
	{
		global $db;

		$query = $db->prepare('UPDATE tasks SET status=:status, time_end=:time_end WHERE telegram_user_id=:telegram_user_id AND task_id=:task_id');
		$query->execute([
			'telegram_user_id' => $telegram_user_id,
			'task_id' => $task_id,
			'status' => 2,
			'time_end' => time(),
		]);

		return true;


	}

	public static function createTask($telegram_user_id,$task_id,$task,$status)
	{

		global $db;

		$create_user = $db->prepare("INSERT INTO tasks (telegram_user_id, task_id, time_start, time_end, status, reward) VALUES (:telegram_user_id, :task_id, :time_start, :time_end, :status, :reward)");

		return $create_user->execute([
			'telegram_user_id' => $telegram_user_id,
			'task_id' => $task_id,
			'time_start' => time(),
			'time_end' => NULL,
			'status' => $status,
			'reward' => $task['reward'],
		]);



	}
}