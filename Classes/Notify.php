<?php

class Notify
{

	public static function add($telegram_user_id, $event_notify,$text,$keyboards,$image)
	{
		global $db;

		$add_notify = $db->prepare("INSERT INTO notify (telegram_user_id, event_notify, text_notify, keyboards_notify, image_notify, time_add, status) VALUES (:telegram_user_id, :event_notify, :text_notify, :keyboards_notify, :image_notify, :time_add, :status)");

		return $add_notify->execute([
			'telegram_user_id' => $telegram_user_id,
			'event_notify' => $event_notify,
			'text_notify' => $text,
			'keyboards_notify' => json_encode($keyboards),
			'image_notify' => $image,
			'time_add' => time(),
			'status' => 0,
		]);

	}

	public static function changeStatus($notify_id,$new_status)
	{
		global $db;

		$update_notify = $db->prepare("UPDATE notify set status=:status WHERE id=:id");

		$update_notify->execute([
			'id' => $notify_id,
			'status' => $new_status,
		]);

		return true;

	}
}