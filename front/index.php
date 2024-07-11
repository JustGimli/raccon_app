<?php



function isMobileDevice($userAgent) {
	$patterns = [
		'/iPhone/i',
		'/iPod/i',
		'/iPad/i',
		'/Android/i'
	];

	foreach ($patterns as $pattern) {
		if (preg_match($pattern, $userAgent)) {
			return true;
		}
	}

	return false;
}

if(!isMobileDevice($_SERVER['HTTP_USER_AGENT'])){
	?>
	<div style="background: #000; width:100%; height:100%">
		<div style="text-align: center;color:#fff;vertical-align: middle;height: 100%;display: flex;flex-direction: column;justify-content: center;">
			Пожалуйста, используйте телефон для доступа к приложению!
		</div>
	</div>

	<?php
	die();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Раскрути енота!</title>
    <link rel="stylesheet" href="styles.css?=<?=time()?>">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
	<script src="https://telegram.org/js/telegram-web-app.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.7.13/lottie.min.js"></script>
	<script>
        let isRequestInProgress = false;
        let score = 0;
        let rotation = 0;
        let lastSentScore = 0;
        let telegram_user_id = 0;
        let sound_box_status = false;
        let hash_app = '';
        let tap_earn = 0;
        let hour_earn = 0;
        let tap_count = {};
        tap_count[0]=0;
        tap_count[1]=0;
        let tap_count_selector = 1;
        let maxScore = 0; // Максимальное количество очков для заполнения уровня
        let energy = 0;
        let maxEnergy = 0; // Максимальное количество энергии
        let energyRegenerationRate = 0; // Скорость пополнения энергии (единиц в секунду)

        let totalFrames = 71;
        let currentFrame = 0;
        let currentTime = 0;
        let isPlaying = false;

	</script>

</head>
<body style="background-color: #2d3053;">
	<div id="preloader" style="position: fixed;top: 0;left: 0;width: 100%;height: 100%;display: flex;justify-content: center;align-items: center;z-index: 9999;background-color: #242839;box-shadow: inset 0px 2px 20px #4c56d8;flex-direction: column;opacity: 1;transition: opacity 2s ease;">
		<div style="border: 10px solid #f3f3f3;border-top: 10px solid #4b0bc0;border-radius: 50%;width: 50px;height: 50px;animation: spin 2s linear infinite;"></div>
		<div style="color: #fff;margin-top: 20px;">
			Загрузка...
		</div>
	</div>
    <div class="container" style="display:none;" id="container">
	    <div class="content" style="margin-bottom:100px;height: 100%;">

		    <div class="tab-content active" id="tab1" style="height: 100%;">
			    <div class="profit-container">
				    <div class="profit-block">
					    <div>Прибыль за тап</div>
					    <div class="profit-detail">
						    <i class="fas fa-hand-pointer"></i>
						    <span><span class="tap_earn">0</span> <span class="tap_earn_coin_name"></span> </span>
					    </div>
				    </div>
				    <div class="profit-block">
					    <div>Прибыль в час</div>
					    <div class="profit-detail">
						    <i class="fa-solid fa-coins"></i>
						    <span><span class="hour_earn">0</span> <span class="hour_earn_coin_name"></span></span>
					    </div>
				    </div>
			    </div>

			    <div style="height: 60%;display: flex;
    align-content: flex-start;
    flex-direction: column;
    justify-content: center;">
				    <div class="profit-container">
					    <div class="">
						    <div style="text-decoration: underline; color: white;" id="sound-box">
							    Включить настроение 🔊
						    </div>
						    <div style="margin-top: 5px; font-size: 12px;    height: 13px;" id="sound-box-bonus"> </div>
					    </div>

				    </div>
				   <!-- <h2 style="padding-bottom: 20px">Раскрути енота 🦡</h2>-->

				    <div id="lottie-hamster" class="lottie-container box-shadow-anime-racoon"></div>
				    <p>Монеты: <span id="score"></span></p>
				    <audio id="click-sound" src="pedro3.mp3" loop></audio>
			    </div>
			    <div style="width: 90%;position:fixed;bottom: 125px;">
				    <div onclick="showTab('tab7')" style="display: flex;font-size: 13px;justify-content: space-between;">
					    <span>Подробнее-&gt;</span>
					    <span>Энергия <span id="energy">0</span>/<span id="max_energy">0</span></span>
					    <span>Уровень <span id="user_lvl">0</span>/10</span>
				    </div>
				    <div id="level-container2">
					    <div id="level-bar"></div>
				    </div>
			    </div>

			    <div id="modal_current_hour_earn_coins" class="modal">
				    <div class="modal-content">
					    <span class="close">×</span>
					    <div style="font-size: 20px;margin: 10px 0;padding: 20px;">
						    Пока вас не было вы заработали:
						    <div style="margin-top: 10px;border: 1px solid #9C27B0;padding: 10px;/* box-shadow: inset 0px 2px 20px #9C27B0; */ border-radius: 10px;">
							    <span id="current_hour_earn_coins"></span>
							    <span id="current_hour_earn_name"></span>
						    </div>
					    </div>
				    </div>
			    </div>

			    <div id="modal_bonus-from-friend" class="modal">
				    <div class="modal-content">
					    <span class="close">×</span>
					    <div style="font-size: 20px;margin: 10px 0;padding: 20px;">
						    Вы получили бонус от друга!
						    <div style="margin-top: 10px;border: 1px solid #9C27B0;padding: 10px;/* box-shadow: inset 0px 2px 20px #9C27B0; */ border-radius: 10px;">
							    <span id="bonus-from-friend"></span>
						    </div>
					    </div>
				    </div>
			    </div>
		    </div>
		    <div class="tab-content" id="tab2">
			    <!-- Содержимое для Tab 2 -->
			    <div>
				    <img src="https://icons.iconarchive.com/icons/iconarchive/incognito-animal-2/512/Racoon-icon.png" style="width: 100px;">
			    </div>
			    <h2 class="header">Пригласите друзей крутить енота!</h2>
			    <p>Зовите своих друзей в игру и получайте отличные награды!</p>
			    <div class="invite-input-container">
				    <input type="text" class="invite-input" value="" id="invite-link" readonly>
				    <button class="copy-button" onclick="copyLink()">Копировать</button>
			    </div>
			    <div id="friend_btn_share"></div>
			    <p class="reward" style="margin-top: 30px;">Ваша награда: <strong><span id="friend_earn"></span></strong> за каждого друга</p>
			    <p class="friends-count" style="margin-top: 30px;">Количество друзей: <strong><span id="friends_count">0</span></strong></p>
		    </div>
		    <div class="tab-content" id="tab3">
			    <h2>Прокачайся!</h2>
			    <div class="profit-container">
				    <div class="profit-block box-shadow-anime">
					    <div>Прибыль за тап</div>
					    <div class="profit-detail">
						    <i class="fas fa-hand-pointer"></i>
						    <span><span class="tap_earn">0</span> <span class="tap_earn_coin_name"></span> </span>
					    </div>
				    </div>
				    <div class="profit-block">
					    <div>Прибыль в час</div>
					    <div class="profit-detail">
						    <i class="fa-solid fa-coins"></i>
						    <span><span class="hour_earn">0</span> <span class="hour_earn_coin_name"></span></span>
					    </div>
				    </div>
			    </div>
			    <div class="upgrade-block" style="margin-top: 20px;border-radius: 8px;">
				    <div>
					    <div style="margin-top: 10px;">Ваш уровень: <span id="user_lvl_withno_star"></span></div>
					    <div class="profit-detail" style="text-align: left;justify-content: space-evenly;margin: 5px 0px 0px 0;display: flex;flex-direction: column;align-items: flex-start;">
						    <span id="user_lvl_star"></span>
					    </div>
				    </div>
				    <div>
					    <div class="upgrade-btn" onclick="showTab('tab7')">Прокачать</div>
				    </div>

			    </div>
			    <div onclick="showTab('tab9')" style="margin-top: 20px;text-decoration: underline;">
				    Все характеристики
			    </div>
			    <h3 style="text-align: left;margin-top: 40px;">Улучшения:</h3>
			    <div id="upgrade_box">
			    </div>

			    <div id="modal-upgrade-stati-success" class="modal">
				    <div class="modal-content">
					    <span class="close">&times;</span>
					    <p>Улучшено!</p>
				    </div>
			    </div>
			    <div id="modal-upgrade-stati-nomoney" class="modal">
				    <div class="modal-content upgrade-level-nomoney">
					    <div id="msg"></div>
					    <span class="close">&times;</span>
					    <p>Недостаточно монет!</p>
				    </div>
			    </div>

		    </div>
		    <div class="tab-content" id="tab4">
			    <!-- Содержимое для Tab 3 -->
			    <h2>Выполняй задания - получай бонусы</h2>
			    <div class="upgrade-container">
				    <div class="upgrade-block">
					    <div style="width: 50%;">
						    <div style="margin-top: 16px;">🏆 Ежедневная награда</div>
						    <div class="profit-detail" style="text-align: left;justify-content: space-evenly;margin: 15px 0px 0px 0;display: flex;flex-direction: column;align-items: flex-start;">

						    </div>
					    </div>
					    <div style="width: 42%;">
						    <div style="text-align: center;">+5 000 000 монет</div>
						    <div class="upgrade-btn"  onclick="showTab('tab10')" style="margin-top: 10px;text-align: center;">Получить</div>
					    </div>

				    </div>
				    <div class="upgrade-block">
					    <div  style="width: 50%;">
						    <div style="    margin-top: 16px;">🌀 Колесо удачи</div>
						    <div class="profit-detail" style="
    text-align: left;
    justify-content: space-evenly;
    margin: 15px 0px 0px 0;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    art;
    /* margin-top: 5px; */
">

							    <!--<span>Следующий шанс:</span><span style="
    margin-top: 5px;
">23ч 00м 40с
</span>-->
						    </div>
					    </div>
					    <div style="width: 42%;">
						    <div style="text-align:center;" class="upgrade-btn" onclick="showTab('tab8')">Крутить колесо</div>
					    </div>

				    </div>

				    <div id="tasks_box" style="width: 100%;">
				    </div>


			    </div>

			    <div id="modal-chech_task-success" class="modal">
				    <div class="modal-content">
					    <span class="close">&times;</span>
					    <p>Бонус начислен!</p>
				    </div>
			    </div>
		    </div>
		    <div class="tab-content" id="tab5">
			    <h2>Скоро!</h2>
			    <div style="font-size: 20px;">
				    Каждую неделю <u>в прямом эфире</u> разыгрываем Айфон'ы 📱, деньги 💰, Dyson 💈 и другие призы!
			    </div>
			    <div style="border-radius: 8px;padding: 20px;border: 1px solid #4c56d8;margin: 40px 0px;font-size: 20px;font-weight: 600;">
				    Ваши лотерейные билеты:<br>
				    <div style="margin-top: 10px;font-weight: 600;"> 🎫 <span id="loto_ticket_count"></span></div>
			    </div>
			    <div style="font-size: 20px;">
				    Прокачивай уровень ⬆️  и получай больше лотерейных билетов 🎫
			    </div>
			    <div style="font-size: 20px;margin-top: 20px;">
				    Больше уровень - больше шанс выиграть!
			    </div>
		    </div>
		    <div class="tab-content" id="tab6" style="text-align: initial">
			    <!-- Содержимое для Tab 6 -->
			    <h1>Внимание, геймеры!</h1>
			    <p>Готовьтесь к невероятному событию! Мы рады объявить о предстоящем <strong>Airdrop в нашей любимой игре!</strong></p>

			    <h2>Что вас ждет?</h2>
			    <ul>
				    <li><strong>Выпуск токена на биржах:</strong> Наши игровые монеты скоро станут токенами, которые можно будет обменивать на биржах.</li>
				    <li><strong>Возможность продать монеты:</strong> Заработанные вами монеты можно будет продать за реальные деньги.</li>
			    </ul>

			    <h2>Когда это произойдет?</h2>
			    <p>Точная дата пока неизвестна, поэтому следите за обновлениями! Airdrop начнется в ближайшее время.</p>

			    <h2>Как принять участие?</h2>
			    <ol>
				    <li>Крутите енота!</li>
				    <li>Выполняйте как можно больше заданий.</li>
				    <li>Зовите друзей.</li>
				    <li>Зарабатывайте монеты и получайте свои заслуженные награды!</li>
			    </ol>

			    <p><strong>Чем больше монет заработаете, тем больше денег и призов получите!</strong></p>

			    <p>Следите за новостями в игре и не забывайте делиться своими успехами в наших социальных сетях. Удачи, игроки!</p>

			    <p>С наилучшими пожеланиями,<br>Команда крути енота!</p>
		    </div>
		    <div class="tab-content" id="tab7">
			    <div class="levels-container" id="levels-box">
			    </div>
			    <div id="modal-upgrade-level-success" class="modal">
				    <div class="modal-content">
					    <span class="close">&times;</span>
					    <p>Уровень повышен!</p>
				    </div>
			    </div>
			    <div id="modal-upgrade-level-nomoney" class="modal">
				    <div class="modal-content upgrade-level-nomoney">
					    <span class="close">&times;</span>
					    <p>Недостаточно монет!</p>
				    </div>
			    </div>
		    </div>
		    <div class="tab-content" id="tab8">
			    <h2>Скоро!</h2>
			    <!--<div class="wheel-container">
				    <div id="wheel" class="wheel">
					    <img src="https://static.tildacdn.com/tild3937-3762-4835-a539-323566363739/_.png" alt="Колесо удачи">
				    </div>
			    </div>
			    <button class="spin-button" onclick="spinWheel()">Крутить колесо</button>

			    <script>
                    function spinWheel() {
                        const wheel = document.getElementById('wheel');
                        const randomDegree = Math.floor(Math.random() * 3600) + 360; // Обеспечивает достаточное количество оборотов
                        wheel.style.transform = `rotate(${randomDegree}deg)`;
                    }
			    </script>-->
		    </div>

		    <div class="tab-content" id="tab9">
			    <h2>Характеристики</h2>
			    <div class="stati-container" id="stati_box" style="display: flex;">

			    </div>
		    </div>

		    <div class="tab-content" id="tab10">
			    <div style="display: flex;justify-content: center;">
				    <img src="https://cdn3d.iconscout.com/3d/premium/thumb/reward-calendar-5598885-4687496.png" style="width: 100px;">
			    </div>
			    <h2>Ежедневная награда</h2>
			    <div style=" padding: 10px; ">
				    <div style="text-align: center; margin-top: 0;">Забирайте монеты за ежедневную игру без пропусков!</div>
				    <ul style="list-style: none; padding-left: 0;" id="evryday_box">

				    </ul>
			    </div>

			    <div id="modal-get-every-day-success" class="modal">
				    <div class="modal-content">
					    <span class="close">&times;</span>
					    <p>Награда получена!</p>
				    </div>
			    </div>
		    </div>
	    </div>

	    <div class="navigation">
		    <div class="btn"  onclick="showTab('tab1')">Крути</div>
		    <div class="btn"  onclick="showTab('tab2')">Друзья</div>
		    <div class="btn"  onclick="showTab('tab3')">Улучшения</div>
		    <div class="btn"  onclick="showTab('tab4')">Задания</div>
		    <div class="btn"  onclick="showTab('tab5')">Лото</div>
		    <div class="btn"  onclick="showTab('tab6')">Airdrop</div>
	    </div>

    </div>

</body>
<script>

    let tg;

    let animation = lottie.loadAnimation({
        container: document.getElementById('lottie-hamster'), // контейнер для анимации
        renderer: 'svg',
        loop: false,
        autoplay: false,
        path: 'racoon.json' // Замените на путь к вашему файлу Lottie JSON
    });

    isRequestInProgress = true;

    window.onload = function() {

        tg = window.Telegram.WebApp;

        if(tg.version!='6.0' && tg.isVersionAtLeast(6.9)){
            tg.requestWriteAccess();
        }


        tg.setHeaderColor('#2d3053');
       // tg.themeParams.text_color='#fff';
        //tg.setSecondaryBackgroundColor('#ffffff');
        tg.expand();

        //var outputDiv = document.getElementById('output');
        //hamster.textContent = 'Ответ от сервера: ' + tg.initData;
        var xhr = new XMLHttpRequest();
        var url = 'https://tg.appenot.com/back/init.php'; // Замените 'your_server_url' на URL вашего сервера

        // Установка параметров запроса
        xhr.open('POST', url, true);
        xhr.setRequestHeader('Content-Type', 'application/json');

        // Обработка ответа от сервера
        xhr.onload = function() {
            if (xhr.status === 200) {
                if(xhr.responseText!=0){

                    var response = JSON.parse(xhr.responseText);
                  //  document.getElementById('username').textContent = response.username;
                    document.getElementById('score').textContent = response.balance;

                    document.getElementById('levels-box').innerHTML = response.levels_box;

                    var elements = document.getElementsByClassName('tap_earn');
                    Array.from(elements).forEach(element => {
                        element.textContent = response.tap_earn;
                    });

                    elements = document.getElementsByClassName('hour_earn');
                    Array.from(elements).forEach(element => {
                        element.textContent = response.hour_earn;
                    });

                    elements = document.getElementsByClassName('tap_earn_coin_name');
                    Array.from(elements).forEach(element => {
                        element.textContent = response.tap_earn_coin_name;
                    });

                    elements = document.getElementsByClassName('hour_earn_coin_name');
                    Array.from(elements).forEach(element => {
                        element.textContent = response.hour_earn_coin_name;
                    });

                    energy=response.energy;
                    document.getElementById('energy').textContent = response.energy;
                    energyRegenerationRate=response.energy_regeneration_rate;
                    maxEnergy=response.max_energy;

                    document.getElementById('max_energy').textContent = response.max_energy;

                    document.getElementById('user_lvl').textContent = response.user_lvl;

                    elements = document.querySelectorAll(`.lvl${response.user_lvl} .level-your-lvl`);
                    Array.from(elements).forEach(element => {
                        element.style.display = 'block';
                    });

                    elements = document.querySelectorAll(`.lvl${response.user_lvl}`);
                    Array.from(elements).forEach(element => {
                        element.style.borderColor = '#c400ff';
                    });

                    if(response.user_lvl>1){

                        var n = response.user_lvl-1;

                        while (n > 0) {
                            elements = document.querySelectorAll(`.lvl${n}`);
                            Array.from(elements).forEach(element => {
                                element.style.display = 'none';
                            });
                            n--;
                        }
                    }

                    elements = document.querySelectorAll(`.lvl${parseInt(response.user_lvl)+1} .level-update-block`);
                    Array.from(elements).forEach(element => {
                        element.style.display = 'block';
                    });



                    if(response.current_hour_earn_coins>0){
                        document.getElementById('current_hour_earn_coins').textContent = response.current_hour_earn_coins;
                        document.getElementById('current_hour_earn_name').textContent = response.current_hour_earn_name;
                        openModal('modal_current_hour_earn_coins');
                    }

                    document.getElementById('upgrade_box').innerHTML = response.upgrade_box;
                    document.getElementById('stati_box').innerHTML = response.stati_box;

                    if(response.tasks_box){
                        document.getElementById('tasks_box').innerHTML = response.tasks_box;
                    }


                    document.getElementById('friend_earn').textContent = response.friend_earn;
                    document.getElementById('friends_count').textContent = response.friends_count;
                    document.getElementById('invite-link').value = response.friend_link;

                    document.getElementById('loto_ticket_count').textContent = response.loto_ticket_count;

                    if(response.friend_bonus){
                        document.getElementById('bonus-from-friend').textContent = response.friend_bonus;
                        openModal('modal_bonus-from-friend');
                    }

                    document.getElementById('loto_ticket_count').textContent = response.loto_ticket_count;

                    document.getElementById('user_lvl_withno_star').textContent = response.user_lvl;
                    document.getElementById('user_lvl_star').textContent = response.user_lvl_star;

                    document.getElementById('friend_btn_share').innerHTML = response.btn_share_friend;
                    document.getElementById('evryday_box').innerHTML = response.evryday_box;

                    if(response.start_page){
                        if(response.start_page=='tasks'){

                            setTimeout(() => {
                                showTab('tab4');
                            }, 3000);

                        }


                    }


                    maxScore=response.max_lvl_score;


                    score=response.balance;
                    tap_earn=response.tap_earn;
                    hour_earn=response.hour_earn;
                    telegram_user_id=response.telegram_user_id;
                    hash_app=response.hash_app;

                    updateLevelBar();


                }


                document.getElementById('preloader').classList.add('preloader-hidden');

                setTimeout(() => {
                    document.getElementById('preloader').style.display = 'none';
                    document.getElementById('container').style.display = 'inline-table';
                    document.body.style.overflow = 'auto';
                }, 2000); // Время должно совпадать с временем transition в CSS

            } else {
                console.log('Ошибка при выполнении запроса. Статус:', xhr.status);
            }

            isRequestInProgress = false;

        };

        xhr.onerror = function() {
            isRequestInProgress = false;
        };

        // Отправка данных на сервер
        xhr.send(JSON.stringify({data: tg.initData, platform: tg.platform, version: tg.version}));

        setInterval(regenerateEnergy, 1000);
        setInterval(sendScoreToServer, 5000);

        animation.goToAndStop(0, true);


        document.getElementById('lottie-hamster').addEventListener('touchstart', handleClick, { passive: true });

        document.getElementById('lottie-hamster').addEventListener('contextmenu', (event) => {
            event.preventDefault();
        });

        document.getElementById('lottie-hamster').addEventListener('selectstart', (event) => {
            event.preventDefault();
        });

        document.getElementById('sound-box').addEventListener('click', function playMusic() {

            const music = document.getElementById('click-sound');
            if(sound_box_status==false){
                music.play().catch(error => {
                    console.log('Autoplay prevented: ', error);
                });
                sound_box_status=true;
                tap_earn=tap_earn+1;
                document.getElementById('sound-box').innerText="Выключить 🔊";
                document.getElementById('sound-box-bonus').innerHTML="Бонус +1 <i class=\"fas fa-hand-pointer\"></i>";
            }else{
                music.pause();
                sound_box_status=false;
                tap_earn=tap_earn-1;
                document.getElementById('sound-box').innerText="Включить настроение 🔊";
                document.getElementById('sound-box-bonus').innerHTML="";


            }


        });



    }
</script>

<style>
	html,
	body {
		width: 100%;
		height: 100%;
		margin: 0;
		padding: 0;

		position: relative;
		min-height: 100vh;

	}

	.preloader-hidden {
		opacity: 0!important;
	}

	@keyframes spin {
		0% { transform: rotate(0deg); }
		100% { transform: rotate(360deg); }
	}


	#board {
		width: 100%;
		height: 100%;
		position: absolute;
		overflow: hidden;
		background-color: rgb(245, 247, 250);
		/*margin-bottom: 200px;  Высота панели навигации */

	}

	.card {
		width: 90%;
		height: 80%;
		position: absolute;
		top: 45%;
		left: 50%;
		border-radius: 1%;
		box-shadow: 0px 4px 4px 0px rgba(0, 0, 0, 0.1);
		background-color: white;
		transform: translateX(-50%) translateY(-50%) scale(0.95);
	}
	.navigation {
		position: fixed;
		bottom: 0;
		left: 0;
		width: 100%;
		/*height: 100px;  Высота панели навигации */
		background-color: #373d83;
		display: flex;
		justify-content: space-around;
		align-items: center;
		box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
		flex-wrap: wrap;
		padding: 5px 0px;
		gap: 7px;
		flex-direction: row;
		flex-wrap: wrap;
		justify-content: space-evenly;
		/*border-top: 1px solid #ccc;*/

	}

	.btn {
		display: inline-block;
		width: 100px;
		height: 40px;
		text-align: center;
		line-height: 40px;
		font-weight: bold;
		color: white;
		background-color: #2d3053;
		border-radius: 5px;
		cursor: pointer;
		border: 1px solid #131517;
	}

	.btn:hover {
		background-color: #1e2036;
	}
	.tab-content {
		display: none;
	}

	.tab-content.active {
		display: block;
	}
	.tabs {
		display: flex;
		justify-content: space-around;
		margin-bottom: 20px;
	}

	.tab {
		padding: 10px 20px;
		background-color: #f2f2f2;
		cursor: pointer;
	}

	.tab.active {
		background-color: #ccc;
	}
</style>
<script src="script.js?=<?=time()?>"></script>
<script>

</script>

</html>
