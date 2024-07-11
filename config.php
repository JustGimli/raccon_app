<?php
require_once __DIR__ . "/vendor/autoload.php";
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

header("Content-type: text/html; charset=utf-8");

$start_execute_time = time();

define("SECRETHASH", "dscijnerv8287Hhuc(&ubcviwjf&3jjeourevlskcmvtd");
define("BOTROKEN", "7448266480:AAGdUsaVFYbMstx_9mmgzzrccLtFRZkwGPk");
define("TELEGRAM_SECRET_WEBHOOK_TOKEN", "sdcq37sdclwekldJKBkd3jdssdcwekl3");
//https://api.telegram.org/bot7448266480:AAGdUsaVFYbMstx_9mmgzzrccLtFRZkwGPk/setWebhook?url=https://racoonbot.appiz.xyz/listner.php&max_connections=40&secret_token=sdcq37sdclwekldJKBkd3jdssdcwekl3

define("BONUS_FOR_FRIENDS", 50000);

define("CACHE_TIMEOUT_USER", 50000);
define("CACHE_TIMEOUT_USER_STATI", 50000);
define("CACHE_USER_STATI_PREFIX_NAME", "racoon_st_");
define("CACHE_USER_PREFIX_NAME", "racoon_user4_");

$config_evryday = [
    1 => [
        "name_day" => "День 1",
        "reward" => 500,
        "reward_text" => "500 монет",
    ],
    2 => [
        "name_day" => "День 2",
        "reward" => 1000,
        "reward_text" => "1 000 монет",
    ],
    3 => [
        "name_day" => "День 3",
        "reward" => 2500,
        "reward_text" => "2 500 монет",
    ],
    4 => [
        "name_day" => "День 4",
        "reward" => 5000,
        "reward_text" => "5 000 монет",
    ],
    5 => [
        "name_day" => "День 5",
        "reward" => 15000,
        "reward_text" => "15 000 монет",
    ],
    6 => [
        "name_day" => "День 6",
        "reward" => 25000,
        "reward_text" => "25 000 монет",
    ],
    7 => [
        "name_day" => "День 7",
        "reward" => 100000,
        "reward_text" => "100 000 монет",
    ],
    8 => [
        "name_day" => "День 8",
        "reward" => 500000,
        "reward_text" => "500 000 монет",
    ],
    9 => [
        "name_day" => "День 9",
        "reward" => 1000000,
        "reward_text" => "1 000 000 монет",
    ],
    10 => [
        "name_day" => "День 10",
        "reward" => 5000000,
        "reward_text" => "5 000 000 монет",
    ],
];

$levels = [
    1 => [
        "base_tap_earn" => 1,
        "base_hour_earn" => 0,
        "base_max_energy" => 1500,
        "base_energy_reg_rate" => 1,
        "loto_ticket" => 1,
        "circle_luck" => 1,
    ],
    2 => [
        "price" => 5000,
        "base_tap_earn" => 2,
        "base_hour_earn" => 100,
        "base_max_energy" => 2250,
        "base_energy_reg_rate" => 2,
        "loto_ticket" => 2,
        "circle_luck" => 1,
    ],
    3 => [
        "price" => 25000,
        "base_tap_earn" => 3,
        "base_hour_earn" => 500,
        "base_max_energy" => 3000,
        "base_energy_reg_rate" => 3,
        "loto_ticket" => 3,
        "circle_luck" => 2,
    ],
    4 => [
        "price" => 100000,
        "base_tap_earn" => 4,
        "base_hour_earn" => 2000,
        "base_max_energy" => 3750,
        "base_energy_reg_rate" => 4,
        "loto_ticket" => 4,
        "circle_luck" => 2,
    ],
    5 => [
        "price" => 1000000,
        "base_tap_earn" => 5,
        "base_hour_earn" => 7000,
        "base_max_energy" => 4500,
        "base_energy_reg_rate" => 5,
        "loto_ticket" => 5,
        "circle_luck" => 3,
    ],
    6 => [
        "price" => 5000000,
        "base_tap_earn" => 6,
        "base_hour_earn" => 20000,
        "base_max_energy" => 5250,
        "base_energy_reg_rate" => 6,
        "loto_ticket" => 6,
        "circle_luck" => 3,
    ],
    7 => [
        "price" => 10000000,
        "base_tap_earn" => 7,
        "base_hour_earn" => 50000,
        "base_max_energy" => 6000,
        "base_energy_reg_rate" => 7,
        "loto_ticket" => 7,
        "circle_luck" => 4,
    ],
    8 => [
        "price" => 50000000,
        "base_tap_earn" => 8,
        "base_hour_earn" => 150000,
        "base_max_energy" => 6750,
        "base_energy_reg_rate" => 8,
        "loto_ticket" => 8,
        "circle_luck" => 4,
    ],
    9 => [
        "price" => 200000000,
        "base_tap_earn" => 9,
        "base_hour_earn" => 400000,
        "base_max_energy" => 7500,
        "base_energy_reg_rate" => 9,
        "loto_ticket" => 9,
        "circle_luck" => 5,
    ],
    10 => [
        "price" => 1000000000,
        "base_tap_earn" => 10,
        "base_hour_earn" => 1000000,
        "base_max_energy" => 8250,
        "base_energy_reg_rate" => 10,
        "loto_ticket" => 10,
        "circle_luck" => 5,
    ],
];

$config_stati = [
    1 => [
        "name" => "⌚️ Часы для енота",
        "for" => "hour_earn",
        "levels" => [
            0 => ["price" => 0, "value" => 0],
            1 => ["price" => 500, "value" => 40],
            2 => ["price" => 950, "value" => 97],
            3 => ["price" => 1805, "value" => 177],
            4 => ["price" => 3430, "value" => 292],
            5 => ["price" => 6516, "value" => 455],
            6 => ["price" => 12380, "value" => 686],
            7 => ["price" => 23523, "value" => 1014],
            8 => ["price" => 44694, "value" => 1479],
            9 => ["price" => 84918, "value" => 2140],
            10 => ["price" => 161344, "value" => 3079],
            11 => ["price" => 306553, "value" => 4413],
            12 => ["price" => 582451, "value" => 6306],
            13 => ["price" => 1106657, "value" => 8995],
            14 => ["price" => 2102649, "value" => 12812],
            15 => ["price" => 3995033, "value" => 18234],
            16 => ["price" => 7590564, "value" => 25932],
            17 => ["price" => 14422071, "value" => 36863],
            18 => ["price" => 27401934, "value" => 52386],
            19 => ["price" => 52063675, "value" => 74428],
            20 => ["price" => 98920983, "value" => 105727],
        ],
    ],
    2 => [
        "name" => "🕶 Очки для енота",
        "for" => "hour_earn",
        "levels" => [
            0 => ["price" => 0, "value" => 0],
            1 => ["price" => 600, "value" => 52],
            2 => ["price" => 1062, "value" => 125],
            3 => ["price" => 1880, "value" => 227],
            4 => ["price" => 3327, "value" => 369],
            5 => ["price" => 5889, "value" => 569],
            6 => ["price" => 10424, "value" => 849],
            7 => ["price" => 18450, "value" => 1240],
            8 => ["price" => 32656, "value" => 1789],
            9 => ["price" => 57801, "value" => 2556],
            10 => ["price" => 102308, "value" => 3630],
            11 => ["price" => 181086, "value" => 5134],
            12 => ["price" => 320522, "value" => 7240],
            13 => ["price" => 567323, "value" => 10188],
            14 => ["price" => 1004162, "value" => 14316],
            15 => ["price" => 1777366, "value" => 20094],
            16 => ["price" => 3145939, "value" => 28183],
            17 => ["price" => 5568311, "value" => 39509],
            18 => ["price" => 9855911, "value" => 55364],
            19 => ["price" => 17444963, "value" => 77562],
            20 => ["price" => 30877584, "value" => 108639],
        ],
    ],
    3 => [
        "name" => "🎧 Наушники для енота",
        "for" => "hour_earn",
        "levels" => [
            0 => ["price" => 0, "value" => 0],
            1 => ["price" => 750, "value" => 75],
            2 => ["price" => 1350, "value" => 176],
            3 => ["price" => 2430, "value" => 313],
            4 => ["price" => 4374, "value" => 497],
            5 => ["price" => 7873, "value" => 747],
            6 => ["price" => 14172, "value" => 1083],
            7 => ["price" => 25509, "value" => 1537],
            8 => ["price" => 45917, "value" => 2150],
            9 => ["price" => 82650, "value" => 2977],
            10 => ["price" => 148769, "value" => 4094],
            11 => ["price" => 267785, "value" => 5602],
            12 => ["price" => 482013, "value" => 7638],
            13 => ["price" => 867624, "value" => 10386],
            14 => ["price" => 1561722, "value" => 14097],
            15 => ["price" => 2811100, "value" => 19105],
            16 => ["price" => 5059980, "value" => 25867],
            17 => ["price" => 9107965, "value" => 34996],
            18 => ["price" => 16394337, "value" => 47319],
            19 => ["price" => 29509806, "value" => 63956],
            20 => ["price" => 53117651, "value" => 86416],
        ],
    ],
    4 => [
        "name" => "🧢 Кепка для енота",
        "for" => "hour_earn",
        "levels" => [
            0 => ["price" => 0, "value" => 0],
            1 => ["price" => 1000, "value" => 98],
            2 => ["price" => 1650, "value" => 221],
            3 => ["price" => 2723, "value" => 374],
            4 => ["price" => 4492, "value" => 565],
            5 => ["price" => 7412, "value" => 804],
            6 => ["price" => 12230, "value" => 1103],
            7 => ["price" => 20179, "value" => 1477],
            8 => ["price" => 33296, "value" => 1945],
            9 => ["price" => 54938, "value" => 2529],
            10 => ["price" => 90647, "value" => 3259],
            11 => ["price" => 149568, "value" => 4171],
            12 => ["price" => 246788, "value" => 5312],
            13 => ["price" => 407200, "value" => 6738],
            14 => ["price" => 671879, "value" => 8521],
            15 => ["price" => 1108601, "value" => 10749],
            16 => ["price" => 1829191, "value" => 13535],
            17 => ["price" => 3018166, "value" => 17016],
            18 => ["price" => 4979974, "value" => 21368],
            19 => ["price" => 8216957, "value" => 26808],
            20 => ["price" => 13557978, "value" => 33609],
        ],
    ],
    5 => [
        "name" => "🐾 Скоростные Лапки",
        "for" => "tap_earn",
        "levels" => [
            0 => ["price" => 0, "value" => 0],
            1 => ["price" => 1650, "value" => 1],
            2 => ["price" => 2723, "value" => 2],
            3 => ["price" => 4492, "value" => 4],
            4 => ["price" => 7412, "value" => 6],
            5 => ["price" => 12230, "value" => 8],
            6 => ["price" => 20179, "value" => 10],
            7 => ["price" => 33296, "value" => 12],
            8 => ["price" => 54938, "value" => 14],
            9 => ["price" => 90647, "value" => 16],
            10 => ["price" => 149568, "value" => 18],
        ],
    ],
    6 => [
        "name" => "🚲 Велосипед для енота",
        "for" => "hour_earn",
        "levels" => [
            0 => ["price" => 0, "value" => 0],
            1 => ["price" => 505, "value" => 80], // +1%
            2 => ["price" => 935, "value" => 194], // -1.58%
            3 => ["price" => 1840, "value" => 354], // +1.94%
            4 => ["price" => 3370, "value" => 584], // -1.75%
            5 => ["price" => 6620, "value" => 910], // +1.60%
            6 => ["price" => 12150, "value" => 1372], // -1.86%
            7 => ["price" => 23990, "value" => 2028], // +1.98%
            8 => ["price" => 43890, "value" => 2958], // -1.80%
            9 => ["price" => 86530, "value" => 4280], // +1.90%
            10 => ["price" => 158450, "value" => 6158], // -1.79%
            11 => ["price" => 312380, "value" => 8826], // +1.90%
            12 => ["price" => 571740, "value" => 12612], // -1.84%
            13 => ["price" => 1128280, "value" => 17990], // +1.95%
            14 => ["price" => 2060600, "value" => 25624], // -2.00%
            15 => ["price" => 4070930, "value" => 36468], // +1.90%
            16 => ["price" => 7446380, "value" => 51864], // -1.90%
            17 => ["price" => 14695800, "value" => 73726], // +1.90%
            18 => ["price" => 26880300, "value" => 104772], // -1.90%
            19 => ["price" => 53079700, "value" => 105726], // +1.95%
            20 => ["price" => 97040500, "value" => 105727], // -1.90%
        ],
    ],
    7 => [
        "name" => "🎒 Рюкзак для енота",
        "for" => "passive_earn_time_seconds",
        "levels" => [
            0 => ["price" => 0, "value" => 0],
            1 => ["price" => 50000, "value" => 3600],
            2 => ["price" => 500000, "value" => 7200],
            3 => ["price" => 5000000, "value" => 10800],
        ],
    ],
];

$tasks_config = [
    1 => [
        "name" => "🟢 Подпишись на нашу группу",
        "btn_name" => "Подписаться",
        "reward" => 20000,
        "type" => "subscribe",
        "link" => "https://t.me/spin_raccoon",
    ],
    2 => [
        "name" => "🟢 Вступите в TON Church",
        "btn_name" => "Перейти",
        "reward" => 20000,
        "type" => "enter",
        "link" => "https://t.me/TonChurchBot/app?startapp=ref_enotvp1",
    ],
];

/*$config = [
	"database"   => [
		"bdname"     => "andreyi2_racoon",
		"bduser"     => "andreyi2_racoon",
		"bdhost"     => "localhost",
		"bdpassword" => "j6&RPiIx"
	]
];*/

$config = [
    "database" => [
        "bdname" => "andreyi2_racdev",
        "bduser" => "andreyi2_racdev",
        "bdhost" => "andreyi2.beget.tech",
        "bdpassword" => "i&Vt2NtM",
    ],
];

$db = new PDO(
    "mysql:host=" .
        $config["database"]["bdhost"] .
        ";dbname=" .
        $config["database"]["bdname"] .
        ";charset=utf8mb4",
    $config["database"]["bduser"],
    $config["database"]["bdpassword"]
);
$db->query("SET session wait_timeout=60");
// $db->query("SET GLOBAL connect_timeout=60");
// $db->query("SET GLOBAL interactive_timeout=60");

$mem = new Memcached();
$mem->addServer("localhost", 11211);

// classes autoloader
if (!isset($admin_context) && !function_exists("my_autoloader")) {
    function my_autoloader($class)
    {
        include "Classes/" . $class . ".php";
    }

    spl_autoload_register("my_autoloader");
}
