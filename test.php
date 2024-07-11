<?php

require_once  "config.php";

$query = $db->prepare('SELECT * FROM users');
$query->execute();
$users = $query->fetchALL(PDO::FETCH_ASSOC);

foreach($users as $u){
	User::clearCache($u);
}

CREATE TABLE raccoon_events_log
(
    date Date DEFAULT toDate(time),
    time DateTime,

event_type Enum8(
    'save_score' = 1,
    'level_up' = 2,
    'new_upgrade' = 3,
    'task' = 4,
    'everyday_task' = 5,
    'register' = 6,
    'new_ref' = 7,
    'offline_profit' = 8
),

    -- Common fields for all events

                         telegram_user_id UInt64,
    balance_change Int32,
    balance_after Int32,

    -- save score
    energy Nullable(Int32) DEFAULT NULL,
    back_energy Nullable(Int32) DEFAULT NULL,
    tap_count Nullable(Int32) DEFAULT NULL,

    -- level up
    new_level Nullable(UInt8) DEFAULT NULL,

    -- new upgrade
    stat_id Nullable(UInt16) DEFAULT NULL,
    new_stat_level Nullable(UInt16) DEFAULT NULL,
    stat_value Nullable(Float32) DEFAULT NULL,

    -- task
    task_id Nullable(UInt32) DEFAULT NULL,

    -- everyday task

-- register
    invitation_bonus Nullable(UInt32) DEFAULT NULL,
    ref_id Nullable(UInt64) DEFAULT NULL,

    -- register
    time_offline Nullable(UInt32) DEFAULT NULL,

   -- offline profit
    hour_earn Nullable(UInt32) DEFAULT NULL,

    -- Fields from MySQL table
    friends_count Nullable(UInt32) DEFAULT NULL,
    ip_address Nullable(String) DEFAULT NULL,
    is_premium Nullable(UInt8) DEFAULT NULL,
    number_active_day Nullable(UInt8) DEFAULT NULL,
    last_get_day_reward Nullable(UInt32) DEFAULT NULL,
    platform Nullable(Enum8(
    'android' = 1,
    'ios' = 2)) DEFAULT NULL,
    date_reg Nullable(DateTime) DEFAULT NULL,
    last_time_active Nullable(DateTime) DEFAULT NULL,
    level Nullable(UInt8) DEFAULT NULL,
    stati_lvl Nullable(String) DEFAULT NULL,
    max_energy Nullable(UInt16) DEFAULT NULL,
    energy_regeneration_rate Nullable(UInt16) DEFAULT NULL,
    friend_earn Nullable(UInt16) DEFAULT NULL,
    passive_earn_time_seconds Nullable(UInt16) DEFAULT NULL
)
ENGINE = MergeTree()
PARTITION BY date
ORDER BY (intHash32(telegram_user_id), telegram_user_id)
  SAMPLE BY intHash32(telegram_user_id)
  SETTINGS index_granularity = 8192
code