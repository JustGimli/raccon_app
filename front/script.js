function showScorePopup(x, y) {
  // Массив цветов
  const colors = [
    "#0000FF",
    "#1E90FF",
    "#4169E1",
    "#4682B4",
    "#6495ED",
    "#00BFFF",
    "#800080",
    "#8B008B",
    "#9400D3",
    "#9932CC",
    "#BA55D3",
    "#DA70D6",
    "#DDA0DD",
    "#EE82EE",
    "#FF00FF",
  ];

  // Генерация случайного индекса
  const randomIndex = Math.floor(Math.random() * colors.length);

  const popup = document.createElement("div");
  popup.classList.add("sco re-popup");
  popup.innerText = "+" + tap_earn;
  y = y - 70;
  x = x + 5;
  popup.style.left = `${x}px`;
  popup.style.top = `${y}px`;

  popup.style.color = colors[randomIndex];
  document.body.appendChild(popup);

  setTimeout(() => {
    popup.remove();
  }, 1500);
}

function showLowEnergy(x, y) {
  const popup = document.createElement("div");
  popup.classList.add("low_energy-popup");
  popup.innerText = "Мало энергии";
  y = y - 70;
  x = x + 5;
  popup.style.left = `${x}px`;
  popup.style.top = `${y}px`;
  document.body.appendChild(popup);

  setTimeout(() => {
    popup.remove();
  }, 1500);
}

function sendScoreToServer() {
  var temp_selector = tap_count_selector;

  if (tap_count[temp_selector] != 0) {
    if (isRequestInProgress) return;
    isRequestInProgress = true;

    if (tap_count_selector == 1) {
      tap_count_selector = 0;
    } else {
      tap_count_selector = 1;
    }

    var xhr = new XMLHttpRequest();
    var url =
      "https://tg.appenot.com/back/save_score.php?time=" + new Date().getTime();

    xhr.open("POST", url, true);
    xhr.setRequestHeader("Content-Type", "application/json");

    xhr.onload = function () {
      if (xhr.status === 200) {
        tap_count[temp_selector] = 0;
        lastSentScore = score;
      } else {
        console.log("Ошибка при выполнении запроса. Статус:", xhr.status);
      }

      isRequestInProgress = false;
    };

    xhr.onerror = function () {
      isRequestInProgress = false;
    };

    xhr.send(
      JSON.stringify({
        score: score,
        energy: energy,
        tap_count: tap_count[temp_selector],
        sound_box_status: sound_box_status,
        telegram_user_id: telegram_user_id,
        hash_app: hash_app,
      }),
    );
  }
}

function copyLink() {
  var copyText = document.getElementById("invite-link");
  copyText.select();
  copyText.setSelectionRange(0, 99999); // Для мобильных устройств
  document.execCommand("copy");
}

function handleClick(event) {
  if (energy < tap_earn) {
    showLowEnergy(event.touches[0].clientX, event.touches[0].clientY);
    return;
  }

  //navigator.vibrate(20);

  score = parseInt(score) + tap_earn;
  document.getElementById("score").innerText = score;
  tap_count[tap_count_selector]++;

  energy -= tap_earn; // Уменьшаем энергию на количество начисленных очков
  document.getElementById("energy").innerText = energy;

  updateLevelBar();

  const frameIncrement = 2;
  currentFrame = currentFrame + frameIncrement;

  if (currentFrame + frameIncrement >= totalFrames) {
    currentFrame = 0;
  }

  if (!isPlaying) {
    isPlaying = true;
    var segments = [currentFrame, currentFrame + frameIncrement];
    animation.playSegments(segments, true);

    // Установить тайм-аут для сброса флага isPlaying после окончания анимации
    var segmentDuration =
      ((segments[1] - segments[0]) / animation.frameRate) * 1000;
    setTimeout(function () {
      isPlaying = false;
    }, segmentDuration);
  }

  //animation.playSegments([currentFrame, currentFrame + frameIncrement], true);

  // Воспроизводим звук клика

  // Показать очки в месте клика
  showScorePopup(event.touches[0].clientX, event.touches[0].clientY);
}

// Обновляем уровень игрока
function updateLevelBar() {
  const levelPercentage = (score / maxScore) * 100;
  document.getElementById("level-bar").style.width =
    `${Math.min(levelPercentage, 100)}%`; // Ограничиваем ширину до 100%
}

function regenerateEnergy() {
  if (energy < maxEnergy) {
    energy = Math.min(energy + energyRegenerationRate, maxEnergy);
    document.getElementById("energy").innerText = energy;
  }
}

function upgrade_level(lvlup) {
  if (isRequestInProgress) return;
  isRequestInProgress = true;

  var onclick_content = document
    .getElementById("upgrade_lvl_btn-" + lvlup)
    .getAttribute("onclick");
  document
    .getElementById("upgrade_lvl_btn-" + lvlup)
    .setAttribute("onclick", "");
  var text_btn_content = document.getElementById(
    "upgrade_lvl_btn-" + lvlup,
  ).textContent;
  document.getElementById("upgrade_lvl_btn-" + lvlup).textContent =
    "Загрузка...";

  var xhr = new XMLHttpRequest();
  var url =
    "https://tg.appenot.com/back/upgrade_level.php?time=" +
    new Date().getTime();

  xhr.open("POST", url, true);
  xhr.setRequestHeader("Content-Type", "application/json");

  xhr.onload = function () {
    if (xhr.status === 200) {
      if (xhr.responseText != 0) {
        var response = JSON.parse(xhr.responseText);

        if (response.status == "success") {
          openModal("modal-upgrade-level-success");
        } else if (response.status == "no_money") {
          openModal("modal-upgrade-level-nomoney");
        } else {
          console.log(xhr.responseText);
        }
      }
    }

    isRequestInProgress = false;
    document
      .getElementById("upgrade_lvl_btn-" + lvlup)
      .setAttribute("onclick", onclick_content);
    document.getElementById("upgrade_lvl_btn-" + lvlup).textContent =
      text_btn_content;
  };

  xhr.onerror = function () {
    isRequestInProgress = false;
    document
      .getElementById("upgrade_lvl_btn-" + lvlup)
      .setAttribute("onclick", onclick_content);
    document.getElementById("upgrade_lvl_btn-" + lvlup).textContent =
      text_btn_content;
  };

  xhr.send(
    JSON.stringify({
      lvlup: lvlup,
      telegram_user_id: telegram_user_id,
      hash_app: hash_app,
    }),
  );
}

function upgrade_stati(stat_id) {
  if (isRequestInProgress) return;
  isRequestInProgress = true;

  var onclick_content = document
    .getElementById("upgrade_stati_btn-" + stat_id)
    .getAttribute("onclick");
  document
    .getElementById("upgrade_stati_btn-" + stat_id)
    .setAttribute("onclick", "");
  var text_btn_content = document.getElementById(
    "upgrade_stati_btn-" + stat_id,
  ).textContent;
  document.getElementById("upgrade_stati_btn-" + stat_id).textContent =
    "Загрузка...";

  var xhr = new XMLHttpRequest();
  var url =
    "https://tg.appenot.com/back/upgrade_stati.php?time=" +
    new Date().getTime();

  xhr.open("POST", url, true);
  xhr.setRequestHeader("Content-Type", "application/json");

  xhr.onload = function () {
    if (xhr.status === 200) {
      // openModal('modal-upgrade-stati-nomoney');
      // document.getElementById('msg').textContent = xhr.responseText;
      if (xhr.responseText != 0) {
        var response = JSON.parse(xhr.responseText);

        if (response.status == "success") {
          openModal("modal-upgrade-stati-success");

          document.getElementById(`upgrade_box-${response.stat_id}`).outerHTML =
            response.upgrade_box;

          document.getElementById("score").textContent = response.balance;
          score = response.balance;

          if (response.for == "tap_earn") {
            var elements = document.getElementsByClassName("tap_earn");
            Array.from(elements).forEach((element) => {
              element.textContent = response.user_stati.tap_earn;
            });

            elements = document.getElementsByClassName("tap_earn_coin_name");
            Array.from(elements).forEach((element) => {
              element.textContent = response.user_stati.tap_earn_coin_name;
            });

            tap_earn = response.user_stati.tap_earn;
          }

          if (response.for == "hour_earn") {
            var elements = document.getElementsByClassName("hour_earn");
            Array.from(elements).forEach((element) => {
              element.textContent = response.user_stati.hour_earn;
            });

            elements = document.getElementsByClassName("hour_earn_coin_name");
            Array.from(elements).forEach((element) => {
              element.textContent = response.user_stati.hour_earn_coin_name;
            });

            hour_earn = response.user_stati.hour_earn;
          }

          if (response.for == "max_energy") {
            maxEnergy = response.max_energy;
            document.getElementById("max_energy").textContent =
              response.max_energy;
          }

          if (response.for == "energy_regeneration_rate") {
            energyRegenerationRate = response.energy_regeneration_rate;
          }
        } else if (response.status == "no_money") {
          openModal("modal-upgrade-stati-nomoney");
        } else {
          console.log(xhr.responseText);
        }
      }
    }

    isRequestInProgress = false;
    document
      .getElementById("upgrade_stati_btn-" + stat_id)
      .setAttribute("onclick", onclick_content);
    document.getElementById("upgrade_stati_btn-" + stat_id).textContent =
      text_btn_content;
  };

  xhr.onerror = function () {
    isRequestInProgress = false;
    document
      .getElementById("upgrade_stati_btn-" + stat_id)
      .setAttribute("onclick", onclick_content);
    document.getElementById("upgrade_stati_btn-" + stat_id).textContent =
      text_btn_content;
  };

  xhr.send(
    JSON.stringify({
      stat_id: stat_id,
      telegram_user_id: telegram_user_id,
      hash_app: hash_app,
    }),
  );
}

// Открываем модальное окно при клике на кнопку
function openModal(id) {
  document.getElementById(`${id}`).style.display = "block";
  window.addEventListener("click", (event) => {
    if (event.target == document.getElementById(`${id}`)) {
      document.getElementById(`${id}`).style.display = "none";
      if (id == "modal-upgrade-level-success") {
        location.reload();
      }
    }
  });
  document.getElementById(`${id}`).addEventListener("click", () => {
    document.getElementById(`${id}`).style.display = "none";

    if (id == "modal-upgrade-level-success") {
      location.reload();
    }
  });
}

function checkTask(task_id) {
  if (isRequestInProgress) return;
  isRequestInProgress = true;

  var onclick_content = document
    .getElementById("task-btn-check_" + task_id)
    .getAttribute("onclick");
  document
    .getElementById("task-btn-check_" + task_id)
    .setAttribute("onclick", "");
  var text_btn_content = document.getElementById(
    "task-btn-check_" + task_id,
  ).textContent;
  document.getElementById("task-btn-check_" + task_id).textContent =
    "Загрузка...";

  var xhr = new XMLHttpRequest();
  var url =
    "https://tg.appenot.com/back/check_task.php?time=" + new Date().getTime();

  xhr.open("POST", url, true);
  xhr.setRequestHeader("Content-Type", "application/json");

  xhr.onload = function () {
    if (xhr.status === 200) {
      if (xhr.responseText != 0) {
        var response = JSON.parse(xhr.responseText);

        if (response.status == "success") {
          openModal("modal-chech_task-success");

          document.getElementById("score").textContent = response.balance;
          score = response.balance;
          document.getElementById(`task_block_${task_id}`).style.display =
            "none";
        } else {
          console.log(xhr.responseText);
        }
      }
    }

    isRequestInProgress = false;
    document
      .getElementById("task-btn-check_" + task_id)
      .setAttribute("onclick", onclick_content);
    document.getElementById("task-btn-check_" + task_id).textContent =
      text_btn_content;
  };

  xhr.onerror = function () {
    isRequestInProgress = false;
    document
      .getElementById("task-btn-check_" + task_id)
      .setAttribute("onclick", onclick_content);
    document.getElementById("task-btn-check_" + task_id).textContent =
      text_btn_content;
  };

  xhr.send(
    JSON.stringify({
      task_id: task_id,
      telegram_user_id: telegram_user_id,
      hash_app: hash_app,
    }),
  );
}

function taskAction(task_id) {
  document.getElementById(`task-btn-action_${task_id}`).style.display = "none";
  document.getElementById(`task-btn-check_${task_id}`).style.display = "block";
}

function getEveryDay(day_id) {
  if (isRequestInProgress) return;
  isRequestInProgress = true;

  var onclick_content = document
    .getElementById("everyday_btn_block_" + day_id)
    .getAttribute("onclick");
  document
    .getElementById("everyday_btn_block_" + day_id)
    .setAttribute("onclick", "");
  var text_btn_content = document.getElementById(
    "everyday_btn_block_" + day_id,
  ).textContent;
  document.getElementById("everyday_btn_block_" + day_id).textContent =
    "Загрузка...";

  var xhr = new XMLHttpRequest();
  var url =
    "https://tg.appenot.com/back/get_everyday.php?time=" + new Date().getTime();

  xhr.open("POST", url, true);
  xhr.setRequestHeader("Content-Type", "application/json");

  xhr.onload = function () {
    if (xhr.status === 200) {
      if (xhr.responseText != 0) {
        var response = JSON.parse(xhr.responseText);

        if (response.status == "success") {
          openModal("modal-get-every-day-success");

          document.getElementById("score").textContent = response.balance;
          score = response.balance;
          document.getElementById(`everyday_btn_block_${day_id}`).outerHTML =
            '<div style="width:20%; box-shadow: none;padding: 8px 15px;"><i class="fa-solid fa-circle-check" style="color: #4CAF50;"></i></div>';
        } else {
          console.log(xhr.responseText);
        }
      }
    }

    isRequestInProgress = false;
    document
      .getElementById("everyday_btn_block_" + day_id)
      .setAttribute("onclick", onclick_content);
    document.getElementById("everyday_btn_block_" + day_id).textContent =
      text_btn_content;
  };

  xhr.onerror = function () {
    isRequestInProgress = false;
    document
      .getElementById("everyday_btn_block_" + day_id)
      .setAttribute("onclick", onclick_content);
    document.getElementById("everyday_btn_block_" + day_id).textContent =
      text_btn_content;
  };

  xhr.send(
    JSON.stringify({ telegram_user_id: telegram_user_id, hash_app: hash_app }),
  );
}

function showTab(tabId) {
  // Скрыть все табы
  var tabContents = document.getElementsByClassName("tab-content");
  for (var i = 0; i < tabContents.length; i++) {
    tabContents[i].classList.remove("active");
  }

  // Отобразить выбранный таб
  var tab = document.getElementById(tabId);
  tab.classList.add("active");

  // Установить активный класс для выбранной кнопки
  var tabs = document.getElementsByClassName("tab");
  for (var i = 0; i < tabs.length; i++) {
    tabs[i].classList.remove("active");
  }
  var activeTab = document.querySelector("[data-tab='" + tabId + "']");
  activeTab.classList.add("active");
}
