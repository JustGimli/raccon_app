<?php

class Format
{

	public static function upgrade_box($user,$stat_id,$stata)
	{

		$cs=$stata;

		if(isset($user['stati_lvl'][$stat_id])){
			$this_stata_lvl=$user['stati_lvl'][$stat_id];
		}else{
			if(isset($cs['levels'][0])){
				$this_stata_lvl=0;
			}else{
				$this_stata_lvl=1;
			}
		}

		$next_stata_lvl = $this_stata_lvl+1;

		if($cs['for']=='tap_earn'){
			$icon_up_class='fas fa-hand-pointer';
		}elseif($cs['for']=='hour_earn'){
			$icon_up_class='fa-solid fa-coins';
		}elseif($cs['for']=='passive_earn_time_seconds'){
			$icon_up_class='fas fa-clock';
		}

		if($cs['for']=='passive_earn_time_seconds'){
			//$value_param=$value_param/60/60;
			$ttt=($cs['levels'][$next_stata_lvl]['value']-$cs['levels'][$this_stata_lvl]['value'])/60/60;
		}else{
			$ttt=($cs['levels'][$next_stata_lvl]['value']-$cs['levels'][$this_stata_lvl]['value']);
		}

		if(!isset($cs['levels'][$next_stata_lvl])){
			//$plus_param=$cs['levels'][$this_stata_lvl]['value'];
			$up_lvl_btn='<div class="upgrade-btn">Max</div>';
		}else{
			//$plus_param='+'.$cs['levels'][$next_stata_lvl]['value'];
			$up_lvl_btn='<div style="font-size: 12px;text-align: center;"><span>+'.$ttt.'</span> <i class="'.$icon_up_class.'"></i></div>';
			$up_lvl_btn.='<div style="font-size: 12px;text-align: center;padding-top: 5px;">'.$cs['levels'][$next_stata_lvl]['price'].' '.User::declension($cs['levels'][$next_stata_lvl]['price'],"монета","монеты","монет").'</div>';
			$up_lvl_btn.='<div class="upgrade-btn" id="upgrade_stati_btn-'.$stat_id.'" onclick="upgrade_stati('.$stat_id.');" style="margin-top: 7px;">Улучшить</div>';
		}

		if(!isset($cs['levels'][$this_stata_lvl]['value'])){
			$value_param=0;
		}else{
			$value_param=$cs['levels'][$this_stata_lvl]['value'];
		}


		if($cs['for']=='passive_earn_time_seconds' && $value_param!=0){

			$value_param=($cs['levels'][$next_stata_lvl]['value']-$cs['levels'][$this_stata_lvl]['value'])/60/60;
		}



		$upgrade_box='
		
	
		<div class="upgrade-container" id="upgrade_box-'.$stat_id.'">
			<div class="upgrade-block">
				<div>
					<div>'.$cs['name'].'</div>
					<div style="margin: 10px 0px 0px 0;font-size: 12px;">
						Уровень: '.$this_stata_lvl.'
					</div>
					<div class="profit-detail" style="text-align: left;justify-content: left;margin: 10px 0px 0px 0; font-size:12px">
						<span>'.$value_param.'</span>
						<i class="'.$icon_up_class.'" style="color: #fff;margin-left: 5px;"></i>
					</div>
				</div>
				<div>
					'.$up_lvl_btn.'
				</div>
			</div>
		</div>';

		return $upgrade_box;

	}


	public static function levels_bloks($levels)
	{

		$levels_box='';

		foreach($levels as $level_key=> $l){

			if($level_key==1) $stars='⭐';
			elseif($level_key==2) $stars='⭐⭐';
			elseif($level_key==3) $stars='⭐⭐⭐';
			elseif($level_key==4) $stars='⭐⭐⭐⭐';
			elseif($level_key==5) $stars='🌟';
			elseif($level_key==6) $stars='🌟⭐';
			elseif($level_key==7) $stars='🌟⭐⭐';
			elseif($level_key==8) $stars='🌟⭐⭐⭐';
			elseif($level_key==9) $stars='🌟⭐⭐⭐⭐';
			elseif($level_key==10) $stars='🌟🌟';
			$levels_box.='
                    <div class="level-block lvl'.$level_key.'">
					    <h3 class="level-your-lvl">Ваш уровень</h3>
					    <h2>Уровень '.$level_key.'</h2>
					    <div style="margin-bottom: 15px;">'.$stars.'</div>
					    <div class="level-detail">
						    <i class="fa-solid fa-hand-pointer"></i>
						    <span>Доход за 1 тап: '.$l['base_tap_earn'].' </span>
					    </div>
					    <div class="level-detail">
						    <i class="fa-solid fa-coins"></i>	
						    <span>Доход за 1 час: '.$l['base_hour_earn'].' </span>
					    </div>
					    <div class="level-detail">
						    <i class="fa-solid fa-bolt"></i>
						    <span>Максимальная энергия: '.$l['base_max_energy'].'</span>
					    </div> 
					    <div class="level-detail">
						    <i class="fa-solid fa-charging-station"></i>
						    <span>Регенерация энергии в 1 сек: '.$l['base_energy_reg_rate'].'</span>
					    </div>
					    <div class="level-detail">
						    <i class="fas fa-ticket-alt"></i>
						    <span>Лотерейные билеты: '.$l['loto_ticket'].'</span>
					    </div>
					    <div class="level-detail">
						    <i class="fa-solid fa-spinner"></i>
						    <span>Колесо удачи: '.$l['circle_luck'].'</span>
					    </div>
					    <div class="level-update-block" style="display: none;">
						    <div class="level-detail-price">
							    '.$l['price'].' монет
						    </div>
						    <h3 class="upgrade-btn" id="upgrade_lvl_btn-'.$level_key.'" onclick="upgrade_level('.$level_key.');" style="text-align: center;">
							    Перейти
						    </h3>
					    </div>
				    </div>';
		}

		return $levels_box;

	}

	public static function stati_bloks($user,$user_stati)
	{

		if(isset($user['username']) && $user['username']!='') $username=$user['username'];
		elseif(isset($user['first_name']) && $user['first_name']!='') $username=$user['first_name'];
		else $username='Аноним';

		if($user['level']==1) $stars='⭐';
		elseif($user['level']==2) $stars='⭐⭐';
		elseif($user['level']==3) $stars='⭐⭐⭐';
		elseif($user['level']==4) $stars='⭐⭐⭐⭐';
		elseif($user['level']==5) $stars='🌟';
		elseif($user['level']==6) $stars='🌟⭐';
		elseif($user['level']==7) $stars='🌟⭐⭐';
		elseif($user['level']==8) $stars='🌟⭐⭐⭐';
		elseif($user['level']==9) $stars='🌟⭐⭐⭐⭐';
		elseif($user['level']==10) $stars='🌟🌟';

		$stati_box='
                    <div class="stati-block" style="border: none;">
					    <h3 class="stati-your-lvl" style="display: block;margin-top: 0px;margin-bottom: 40px;">
					        <div style="font-size: 22px;">'.$username.' ('.$user['level'].' ур.) <div style="margin-top: 5px;">'.$stars.'</div></div>
					    </h3>
					    <div class="stati-detail">						  
						    <i class="fa-solid fa-hand-pointer"></i>
						    <span>Доход за 1 тап: <span class="tap_earn">'.$user_stati['tap_earn'].'</span> </span>
					    </div>
					    <div class="stati-detail">
					        <i class="fa-solid fa-coins"></i>						    
						    <span>Доход за 1 час: <span class="hour_earn">'.$user_stati['hour_earn'].'</span> </span>
					    </div>
					    <div class="stati-detail">
						    <i class="fas fa-clock"></i>
						    <span>Максимальное время дохода: '.($user_stati['passive_earn_time_seconds']/60/60).'ч.</span>
					    </div>
					    <div class="stati-detail">
						    <i class="fa-solid fa-bolt"></i>
						    <span>Максимальная энергия: '.$user_stati['max_energy'].'</span>
					    </div>
					    <div class="stati-detail">
						    <i class="fa-solid fa-charging-station"></i>
						    <span>Регенерация энергии в 1 сек: '.$user_stati['energy_regeneration_rate'].'</span>
					    </div>					    
					    <div class="stati-detail">
						    <i class="fas fa-ticket-alt"></i>
						    <span>Лотерейные билеты: '.$user_stati['loto_ticket'].'</span>
					    </div>
					    <div class="stati-detail">
						    <i class="fa-solid fa-spinner"></i>
						    <span>Колесо удачи: '.$user_stati['circle_luck'].'</span>
					    </div>		
					    <div class="stati-detail">
						    <i class="fa-solid fa-user-group"></i>
						    <span>Доход за друга: '.$user_stati['friend_earn'].'</span>
					    </div>					   
				    </div>';


		return $stati_box;

	}

	public static function get_lvl_stars($level)
	{
		if($level==1) $stars='⭐';
		elseif($level==2) $stars='⭐⭐';
		elseif($level==3) $stars='⭐⭐⭐';
		elseif($level==4) $stars='⭐⭐⭐⭐';
		elseif($level==5) $stars='🌟';
		elseif($level==6) $stars='🌟⭐';
		elseif($level==7) $stars='🌟⭐⭐';
		elseif($level==8) $stars='🌟⭐⭐⭐';
		elseif($level==9) $stars='🌟⭐⭐⭐⭐';
		elseif($level==10) $stars='🌟🌟';

		return $stars;
	}

	public static function tasks_bloks($tasks,$user_tasks)
	{

		$tasks_box='';

		$count=0;

		foreach($tasks as $task_id=> $t){

			if($user_tasks[$task_id]['status']==2) continue;
			$count++;
			$tasks_box.=' <div class="upgrade-block" id="task_block_'.$task_id.'">
							    <div style="width: 50%;">
							        <div style="margin-top: 16px;width: 70%;">'.$t['name'].'</div>
								    <div class="profit-detail" style="text-align: left;justify-content: space-evenly;margin: 15px 0px 0px 0;display: flex;flex-direction: column;align-items: flex-start;">
		
								    </div>
							    </div>
							    <div style="width: 42%;">
								    <div style="text-align: center;">+'.$t['reward'].' монет</div>
								    <div class="upgrade-btn" id="task-btn-action_'.$task_id.'" onclick="taskAction('.$task_id.'); tg.openTelegramLink(\''.$t['link'].'\');" style="margin-top: 10px;text-align:center;">'.$t['btn_name'].'</div>
								    <div class="upgrade-btn" id="task-btn-check_'.$task_id.'" onclick="checkTask('.$task_id.')" style="margin-top: 10px;display:none; text-align:center;">Проверить</div>
								</div>
						    </div>';
		}

		if($count==0) return false;

		return $tasks_box;

	}

	public static function btn_share_link($link)
	{

		$link=urlencode($link);
		$text = urlencode('
👋 Давай играть вместе!
		
🦡 Крути енота и зарабатывай монеты, повышай уровень, участвуй в бесплатных лотереях и выигрывай призы. 🚀 Получи Airdrop!

💰По моей ссылке тебя ждет бонус в 50 тысяч монет!');

		$share_btn='<div class="gradient-button"  onclick="tg.openTelegramLink(\'https://t.me/share/url?url='.$link.'&text='.$text.'\');"">Позвать друга</div>';
//$share_btn='<div class="gradient-button"  onclick="window.location.href = \'tg://msg_url?url='.$link.'&text=Давай\'">Позвать друга</div>';
//$share_btn='<div class="gradient-button"  onclick="tg.switchInlineQuery(\'text\');">Позвать друга</div>';
		return $share_btn;

	}

	public static function evryday_box($user_day,$last_get_day_reward)
	{

		global $config_evryday;

		$user_day=$user_day+1;

		$timestamp=time();
		$startOfDay = mktime(0, 0, 0, date('n', $timestamp), date('j', $timestamp), date('Y', $timestamp));
		$endOfDay = mktime(23, 59, 59, date('n', $timestamp), date('j', $timestamp), date('Y', $timestamp));

		$is_today_already=false;

		if($last_get_day_reward>$startOfDay && $endOfDay>$last_get_day_reward){
			$is_today_already=true;
		}

		$box='';

		foreach($config_evryday as $day_number => $ce){

			if($user_day>$day_number){
				//$btn_div='<div class="upgrade-btn" style="width:20%; background: linear-gradient(178deg, #4CAF50, #274f1b);box-shadow: none;">Получено</div>';
				$btn_div='<div style="width:20%; box-shadow: none;padding: 8px 15px;"><i class="fa-solid fa-circle-check" style="color: #4CAF50;"></i></div>';
			}elseif($user_day==$day_number && !$is_today_already){
				$btn_div='<div id="everyday_btn_block_'.$day_number.'" class="upgrade-btn" style="width:20%;" onclick="getEveryDay('.$day_number.')">забрать</div>';
			}else{
				$btn_div='<div style="width:20%; box-shadow: none;padding: 8px 15px;"><i class="fa-solid fa-hourglass-start" style="color: #bababa;"></i></div>';
			}

			$box.='
				<li style="border-bottom: 1px solid #373d83;padding: 10px 0;display: flex;justify-content: space-between;align-items: center;">
					<strong style="width:20%;">'.$ce['name_day'].'</strong>
					<span style="width:60%;">'.$ce['reward_text'].'</span>
					'.$btn_div.'
				</li>
			';
		}

		return $box;
	}

}