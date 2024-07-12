<?php

require_once "../config.php";

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$tester = true;
$tasks = false;
$tasks_box = false;

if (
    $tester &&
    $_SERVER["HTTP_REFERER"] != "https://tg.appenot.com/front/?tester"
) {
    $content = json_decode(file_get_contents("php://input"), true);

    $platform = urldecode($content["platform"]);
    $version_bot = urldecode($content["version"]);

    $content = urldecode($content["data"]);

    $content = explode("&", $content);
    //print_r(json_encode($content));

    $arr = [];
    foreach ($content as $c) {
        $c = explode("=", $c);
        $arr[$c[0]] = $c[1];
    }

    $status = check_hash(BOTROKEN, $arr);

    if (!$status) {
        die(0);
    }

    $user_init = json_decode(urldecode($arr["user"]), true);

    $telegram_user_id = $user_init["id"];

    $start_params = false;
    if (isset($arr["start_param"])) {
        $start_params_temp = $arr["start_param"];
        $start_params_temp = explode("-", $start_params_temp);
        $start_params = [];

        foreach ($start_params_temp as $t) {
            $t = explode("_", $t);
            $start_params[$t[0]] = $t[1];
        }
    }
} else {
    $telegram_user_id = 162875028;
    $platform = "test";
    $version_bot = "6.0";
}

$start_page = false;
if (isset($start_params["d"])) {
    $start_page = $start_params["d"];
}

//if($telegram_user_id==162875028) User::clearCache(162875028);

$user = User::get($telegram_user_id);

if (!$user) {
    $ref_id = false;

    if (isset($start_params["f"])) {
        $ref_id = User::DecodeIdHash($start_params["f"]);
        if (!is_numeric($ref_id)) {
            $ref_id = false;
        }
    }

    if (isset($start_params["s"])) {
        $user_target_source = $start_params["s"];
    }

    User::create(
        $telegram_user_id,
        [
            "username" =>
                isset($user_init["username"]) &&
                trim($user_init["username"]) != ""
                    ? trim($user_init["username"])
                    : null,
            "first_name" =>
                isset($user_init["first_name"]) &&
                trim($user_init["first_name"]) != ""
                    ? trim($user_init["first_name"])
                    : null,
            "last_name" =>
                isset($user_init["last_name"]) &&
                trim($user_init["last_name"]) != ""
                    ? trim($user_init["last_name"])
                    : null,
            "is_bot" => isset($user_init["is_bot"]) ? $user_init["is_bot"] : 0,
            "language_code" => isset($user_init["language_code"])
                ? $user_init["language_code"]
                : null,
            "is_premium" => isset($user_init["is_premium"])
                ? $user_init["is_premium"]
                : 0,
            "platform" => $platform,
            "version" => $version_bot,
        ],
        $ref_id,
        $user_target_source
    );

    if ($ref_id) {
        User::addBalance($telegram_user_id, BONUS_FOR_FRIENDS);
        $ref_user_stati = User::getUserStati($ref_id);
        User::addBalance($ref_id, $ref_user_stati["friend_earn"]);
        User::updateCountRef($ref_id);
        $friend_bonus = BONUS_FOR_FRIENDS;
    }

    $user = User::get($telegram_user_id);
}

$last_time_active = $user["last_time_active"];
User::setUserLastTimeActive($telegram_user_id);

$timestamp_2day_ago = time() - 172800;
$endOfDay_2DayAgo = mktime(
    23,
    59,
    59,
    date("n", $timestamp_2day_ago),
    date("j", $timestamp_2day_ago),
    date("Y", $timestamp_2day_ago)
);

if (
    $user["number_active_day"] > 0 &&
    $endOfDay_2DayAgo > $user["last_get_day_reward"]
) {
    User::resetDailyReward($telegram_user_id);
    $user["number_active_day"] = 0;
}

//$last_active_time = User::getLastActiveTime($telegram_user_id);
//User::setLastActiveTime($telegram_user_id);

$user_stati = User::getUserStati($telegram_user_id);

//текущая не потраченная энергия пользователя
$current_energy = User::getCurrentEnergy(
    $telegram_user_id,
    $user_stati["energy_regeneration_rate"],
    $user_stati["max_energy"]
);
if (!$current_energy) {
    $current_energy = $user_stati["max_energy"];
}

//доход пользователя пока его не было онлайн
if ($last_time_active && time() - 60 > $last_time_active) {
    //количество денег в секунду
    $hour_earn_in_seconds = $user_stati["hour_earn"] / 60 / 60;

    $now_time = time();

    if (
        $now_time - $last_time_active >
        $user_stati["passive_earn_time_seconds"]
    ) {
        $time_for_earn = $user_stati["passive_earn_time_seconds"];
    } else {
        $time_for_earn = $now_time - $last_time_active;
    }

    $current_hour_earn_coins = intval($time_for_earn * $hour_earn_in_seconds);

    User::addBalance($telegram_user_id, $current_hour_earn_coins);
    $user["balance"] = $user["balance"] + $current_hour_earn_coins;

    Clickhouse::insert("default.raccoon_events_logs", [
        "telegram_user_id" => (int) $telegram_user_id,
        "event_type" => (string) "offline_profit",
        "balance_change" => (int) $current_hour_earn_coins,
        "balance_after" => (int) $user["balance"],
        "ip_address" => (string) User::getIp(),
        "platform" =>
            $user["platform"] == "android" || $user["platform"] == "ios"
                ? $user["platform"]
                : null,
        "level" => (int) $user["level"],
        "back_energy" => (int) $current_energy,
        "date_reg" => (int) $user["date_reg"],
        "hour_earn" => (int) $time_for_earn,
        "stati_lvl" => (string) json_encode($user["stati_lvl"]),
    ]);
} else {
    $current_hour_earn_coins = 0;
}

if (isset($levels[$user["level"] + 1])) {
    $max_lvl_score = $levels[$user["level"] + 1]["price"];
} else {
    $max_lvl_score = 10000000000;
}

$upgrade_box = "";

foreach ($config_stati as $stat_id => $cs) {
    $upgrade_box .= Format::upgrade_box($user, $stat_id, $cs);
}

$levels_box = Format::levels_bloks($levels);
$stati_box = Format::stati_bloks($user, $user_stati);

$user_tasks = Tasks::getAllTasksByUser($telegram_user_id);

$tasks_box = Format::tasks_bloks($tasks_config, $user_tasks);

$evryday_box = Format::evryday_box(
    $user["number_active_day"],
    $user["last_get_day_reward"]
);

echo json_encode([
    "hash_app" => User::generateHashApp($telegram_user_id),
    "telegram_user_id" => $telegram_user_id,
    "user" => $user,
    "first_name" => isset($user_init["first_name"])
        ? $user_init["first_name"]
        : "Аноним",
    "balance" => $user["balance"],
    "tap_earn" => $user_stati["tap_earn"],
    "tap_earn_coin_name" => User::declension(
        $user_stati["tap_earn"],
        "монета",
        "монеты",
        "монет"
    ),
    "hour_earn" => $user_stati["hour_earn"],
    "hour_earn_coin_name" => User::declension(
        $user_stati["hour_earn"],
        "монета",
        "монеты",
        "монет"
    ),
    "current_hour_earn_coins" => $current_hour_earn_coins,
    "current_hour_earn_name" => User::declension(
        $current_hour_earn_coins,
        "монета",
        "монеты",
        "монет"
    ),
    "energy" => $current_energy,
    "max_energy" => $user_stati["max_energy"],
    "energy_regeneration_rate" => $user_stati["energy_regeneration_rate"],
    "user_lvl" => $user["level"],
    "loto_ticket_count" => $user_stati["loto_ticket"],
    "max_lvl_score" => $max_lvl_score,
    "upgrade_box" => $upgrade_box,
    "levels_box" => $levels_box,
    "stati_box" => $stati_box,
    "tasks_box" => $tasks_box,
    "evryday_box" => $evryday_box,
    "user_lvl_with_star" =>
        $user["level"] . " " . Format::get_lvl_stars($user["level"]),
    "user_lvl_star" => Format::get_lvl_stars($user["level"]),
    "btn_share_friend" => Format::btn_share_link(
        "https://t.me/spin_racoon_bot/play?startapp=f_" .
            User::GetIdHash($telegram_user_id)
    ),
    "friends_count" => $user["friends_count"],
    "friend_earn" =>
        $user_stati["friend_earn"] .
        " " .
        User::declension(
            $user_stati["friend_earn"],
            "монета",
            "монеты",
            "монет"
        ),
    "friend_link" =>
        "https://t.me/spin_racoon_bot/play?startapp=f_" .
        User::GetIdHash($telegram_user_id),
    "friend_bonus" => isset($friend_bonus)
        ? $friend_bonus .
            " " .
            User::declension($friend_bonus, "монета", "монеты", "монет")
        : false,
    "start_page" => $start_page,
]);

function check_hash($token, $arr2)
{
    $arr = $arr2;
    $check_hash = $arr["hash"];
    unset($arr["hash"]);
    foreach ($arr as $k => $v) {
        $check_arr[] = $k . "=" . $v;
    }
    @sort($check_arr);
    $string = @implode("\n", $check_arr);
    $secret_key = hex2bin(hash_hmac("sha256", $token, "WebAppData"));
    $hash = hash_hmac("sha256", $string, $secret_key);

    if (strcmp($hash, $check_hash) !== 0) {
        return false;
    }
    return true;
}
