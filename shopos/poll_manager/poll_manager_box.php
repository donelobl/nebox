<?php

  $no_color = '#999999';

	$poll_questions_array = array();

	if((int)$_POST['poll_id'] && (int)$_POST['poll_id']){

		os_db_query("UPDATE ".TABLE_POLL_ITEMS." set 
      hits = hits+1 
			WHERE item_id = '".(int)$_POST['poll_items']."' 
			AND poll_id = '".(int)$_POST['poll_id']."'");
		
		// IP Sperre und Dauer in Cookie speichern
/*    
    $set_time_query = os_db_query("SELECT start,end FROM ".TABLE_POLL." WHERE id = '".(int)$_POST['poll_id']."'");
    $set_time = os_db_fetch_array($set_time_query);
    
    $date_day         = substr($set_time['start'],8,2);
    $date_month       = substr($set_time['start'],5,2);
    $date_year        = substr($set_time['start'],0,4);
    $date_hour        = substr($set_time['start'],-8);
    $date_minute      = substr($set_time['start'],-5);
    $date_second      = substr($set_time['start'],-2); 

    $date_day_end     = substr($update['end'],8,2);
    $date_month_end   = substr($update['end'],5,2);
    $date_year_end    = substr($update['end'],0,4);
    $date_hour_end    = substr($update['end'],-8);
    $date_minute_end  = substr($update['end'],-5);
    $date_second_end  = substr($update['end'],-2);  

    $days = $date_day_end - $date_day;
    $hours = $date_hour_end - $date_hour;
*/    
		$expires_time = time() + 60 * 60 * 24 * 30; // lebensdauer
		$poll_cookie = 'mypoll_'.$_POST['poll_id'];
		
		setcookie("$poll_cookie", "Poll", $expires_time, "", "", "");	// ID
		os_redirect(os_href_link(basename($PHP_SELF).'?'.os_get_all_get_params(array ('action')), '', 'NONSSL'));
	}
  
//---------------------------------------------------------------------------------------------------------------------- QUESTIONS

		// Questions Query
		$poll_query = os_db_query("
			SELECT p.id, p.start, p.end, p.customers_groups, pd.title
			FROM ".TABLE_POLL." p, 
				".TABLE_POLL_DESCRIPTION." pd
			WHERE p.id = pd.id
			AND p.status = '1'
			AND pd.language_id = '".$_SESSION['languages_id']."'");
		
		// mehrere umfragen	
		$i = 0;
		$j = 0;

		while($poll_questions = os_db_fetch_array($poll_query)){
    
      // Prüfen der Kundengruppe
      $teilen = explode(',', $poll_questions['customers_groups']); 
      if(in_array($_SESSION['customers_status']['customers_status_id'], $teilen))
      {
        // Ablauf und Start
        if((date("Y-m-d H:i:s") < $poll_questions['end'] && $poll_questions['start'] <= date("Y-m-d H:i:s")) 
          || $poll_questions['end'] == '0000-00-00 00:00:00'){
          
          $button = '';
          if(!isset($_COOKIE['mypoll_'.$poll_questions['id']])) 
            $button = os_image_submit('button_add_quick.gif', 'Poll');
        
          $poll_questions_array[$i] = array(
            'POLL_ID'					=> os_draw_hidden_field('poll_id',$poll_questions['id']),
            'POLL_QUESTION' 	=> $poll_questions['title'],
            'POLL_BUTTON'     => $button,
            'ITEMS'						=> '');
          
          //	Answers Query
          $poll_answers = os_db_query("
            SELECT pi.item_id, pi.color, pi.hits, pid.title
            FROM ".TABLE_POLL_ITEMS." pi,
              ".TABLE_POLL_ITEMS_DESCRIPTION." pid
            WHERE pi.item_id = pid.item_id
            AND pi.poll_id = '".$poll_questions['id']."'
            AND pid.language_id = '".$_SESSION['languages_id']."'
            ORDER BY pi.position");
          
          $poll_skala_query = os_db_query("SELECT SUM(hits) AS skala FROM ".TABLE_POLL_ITEMS." WHERE poll_id = '".$poll_questions['id']."'");
          $poll_skala = os_db_fetch_array($poll_skala_query);
          
          if($poll_skala['skala'] > 0) $poll_skala_perzent = 100 / $poll_skala['skala']; // Prozent
          
          while($poll_items = os_db_fetch_array($poll_answers)){
    
            if(isset($_COOKIE['mypoll_'.$poll_questions['id']])){	// Ergebnisse anzeigen
              
              $poll_skala = 0;
              if($poll_items['hits'] > 0)
                $poll_skala = round($poll_skala_perzent * $poll_items['hits'], 0);
                     
              $poll_questions_array[$i]['ITEMS'][$j] = array(
                'ITEM_ID'						=> $poll_items['item_id'],
                'ITEM_HITS' 				=> $poll_items['hits'],
                'ITEM_COLOR' 				=> ($poll_items['color'] == '' ? $no_color : $poll_items['color']),
                'ITEM_HITS_SKALA' 	=> $poll_skala,
                'ITEM_HITS_TEXT' 	  => $poll_skala.'% - '.$poll_items['hits'].' Stimmen',
                'ITEM_TITLE' 				=> $poll_items['title']);
                
            }else{	// Antwortmöglichkeiten anzeigen
            
              $poll_questions_array[$i]['ITEMS'][$j] = array(
                'ITEM_ID'						=> $poll_items['item_id'],
                'ITEM_RADIO_FIELD' 	=> os_draw_radio_field('poll_items', $poll_items['item_id']),
                'ITEM_TITLE' 				=> $poll_items['title'],
                'ITEM_HITS_SKALA' 	=> '',
                'ITEM_HITS_TEXT' 	  => '');
          }
            $j++;	
          }
          $i++;	
          
        }// IF Ablauf
      
      } // IF Kundengruppen
		}
		// An Template übergeben
//		echo '<pre>';
//		print_r($poll_questions_array);
//		echo '</pre>';
		
    // Anzeigen wenn Umfragen vorhanden und Laufzeit nicht abgelaufen ist
    if (count($poll_questions_array) > 0)
      $box->assign('showpoll', '1');
      
		$box->assign('poll_questions', $poll_questions_array);
		
		$box->assign('FORM', os_draw_form('poll', os_href_link(basename($PHP_SELF).'?'.os_get_all_get_params(array ('action')), '', 'NONSSL')));
		$box->assign('FORM_END', '</form>');

    $box->assign('language', $_SESSION['language']);
	

?>