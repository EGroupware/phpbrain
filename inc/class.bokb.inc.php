<?php
	/**************************************************************************\
	* phpGroupWare - KnowledgeBase                                             *
	* http://www.phpgroupware.org                                              *
	* Written by Dave Hall [skwashd AT phpgroupware DOT org]                   *
	* ------------------------------------------------------------------------ *
	* Started off as a port of phpBrain - http://vrotvrot.com/phpBrain/        *
	*  but quickly became a full rewrite                                       *
	* ------------------------------------------------------------------------ *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	class bokb
	{
		var $cats;
		var $rated;
		var $so;
		var $viewed;
  	
  		function bokb()
  		{
  			$this->cats = createObject('phpgwapi.categories');
				$this->rated = $GLOBALS['phpgw']->session->appsession('rated','phpbrain');
				$this->so = createObject('phpbrain.sokb');
				$this->viewed = $GLOBALS['phpgw']->session->appsession('viewed','phpbrain');
				$GLOBALS['phpgw_info']['apps']['phpkb']['config'] = $this->get_config();
  		}
		
		function get_cat_data($cat_id)
		{
			$cat_id = (int) $cat_id;
			$cats = $this->cats->return_array('all', 0, False, '', '', '', False, $cat_id);
			if(is_array($cats))
			{
  			foreach ($cats as $c_key => $c_vals)
  			{
  				$id = $c_vals['id'];
  				$return_cats[$id] = array('name'		=> $c_vals['name'],
  										'num_entries'	=> $this->so->get_count($id)
  										);
  
  				$sub_cats = $this->cats->return_array('subs', 0, False, '', '', '', False, $id);
  				if(is_array($sub_cats))
  				{
  					foreach($sub_cats as $sub_key => $sub_vals)
  					{
						$sub_id = $sub_vals['id'];
  						$return_cats[$id]['subs'][$sub_id] 
							= array('name'	=> $sub_vals['name'],
  								'num_entries'	=> $this->so->get_count($sub_id)
  								);
  					}//end foreach(subcats)
  					unset($sub_cats);
  				}//end if is_array(sub_cats)
  			}//end foreach(cats)
				return $return_cats;
			}
			else //no cats
			{
				return false;
			}//end if is_array(cats)

		}//end get_cat_data
		
		function delete_comment($comment_id)
		{
			$comment_id = (int) $comment_id;
			if($comment_id)
			{
				return $this->so->delete_comment($comment_id);
			}
			return false;
		}

		function delete_answer($answers)
		{
			return $this->so->delete_answer($answers);
		}

		
		function get_comments($faq_id)
		{
			$comments = $this->so->get_comments($faq_id);
			if(is_array($comments))
			{
				foreach($comments as $key => $vals)
				{
					$comments[$key]['comment_date'] = date('d-M-Y', $vals['entered']);
					$comments[$key]['comment_user'] = $GLOBALS['phpgw']->common->grab_owner_name($vals['user_id']);
				}//end foreach(comment)
			}//end is_array(comments)
			return $comments;
		}//end get_comments

		function get_config()
		{
			if(!is_object($GLOBALS['phpgw']->config))
			{
				$config = createObject('phpgwapi.config');
			}
			else
			{
				$config = $GLOBALS['phpgw']->config;
			}
			
			$config->read_repository();
			return $config->config_data;
			
		}//end get_config
		
		function get_faq_list($cat_id = '', $unpublished = false)
		{
			if(!$this->is_admin() && $unpublished)
			{
				$unpublished = false;
			}

			$faqs = $this->so->get_faq_list($cat_id, $unpublished);
			if(is_array($faqs))
			{
  			foreach($faqs as $faq_id => $faq_vals)
  			{
  				$faqs[$faq_id]['vote_avg'] = (($faq_vals['total'] && $faq_vals['votes'])
												? round(($faq_vals['total'] / $faq_vals['votes']),2) : 0);
  				$faqs[$faq_id]['last_mod'] = date('d-M-Y', $faqs[$faq_id]['modified']);
					$faqs[$faq_id]['score'] = '1.00'; 
					$faqs[$faq_id]['title'] = ($item['is_faq'] 
											? lang('question') . ': '. $faqs[$faq_id]['title']
											: lang('tutorial') . ': '. $faqs[$faq_id]['title']);

  			}
			}
			return $faqs;
		}//end get_faq_list

		function get_item($faq_id, $show_type = True)
		{
			$item = $this->so->get_item($faq_id, !@$this->viewed[$faq_id]);
			if(is_array($item))
			{
  				$item['last_mod']	= date('d-M-Y', $item['modified']);
				$item['username']	= $GLOBALS['phpgw']->common->grab_owner_name($item['user_id']);
  				$item['rating']		= ($item['votes'] 
								? round(($item['total']/$item['votes']),2) : 0);
				$item['comments']	= $this->get_comments($faq_id); 
				if($show_type)
				{
					$item['title'] = ($item['is_faq'] 
								? lang('faq') . ': '. $item['title']
								: lang('tutorial') . ': '. $item['title']);
				}
				$this->viewed[$faq_id] = True;
				$GLOBALS['phpgw']->session->appsession('viewed','phpbrain', $this->viewed);

			}//end if is_array(item)

			return $item;

		}//end get_item
		
		function get_latest()
		{
			return $this->so->get_latest();
		}// end get_latest
		
		function get_questions($pending = false)
		{
			if(!$this->is_admin() && $pending)
			{
				return null;
			}
			else
			{
				return $this->so->get_questions($pending);
			}
		}//end questions

		function get_search_results($search, $show)
		{
			$results = $this->so->get_search_results($search, $show);
			if(is_array($results))
			{
  			foreach($results as $id => $vals)
  			{
    				$results[$id]['vote_avg'] = (($vals['total'] && $vals['votes'])
  												? round(($vals['total'] / $vals['votes']),2) : 0);
    				$results[$id]['last_mod'] = date('d-M-Y', $vals['modified']);

						$results[$id]['title'] = ($results[$id]['is_faq'] 
												? lang('question') . ': '. $results[$id]['title']
												: lang('tutorial') . ': '. $results[$id]['title']);
  			}
			}
			return $results;
		}//end get search results
		
		function get_stats()
		{
			return $this->so->get_stats();
		}//end get_stats

		function is_admin()
		{
			return isset($GLOBALS['phpgw_info']['user']['apps']['admin']);
		}//end is_admin
		
		function is_anon()
		{
			return ($GLOBALS['phpgw_info']['apps']['phpkb']['config']['anon_user'] 
						== $GLOBALS['phpgw_info']['user']['account_id']);
		}//end is_anon

		function save($faq_id, $faq, $question_id)
		{
			if(!$GLOBALS['phpgw_info']['apps']['phpkb']['config']['alow_tags'])
			{
  				$faq['title'] = strip_tags($faq['title']);
  				$faq['keywords'] = strip_tags($faq['keywords']);
  				$faq['text'] = strip_tags($faq['text']);
			}
			$faq['user_id'] = (isset($faq['user_id']) ? $faq['user_id'] : $GLOBALS['phpgw_info']['user']['account_id']);
			$new_faq_id = $this->so->save($faq_id, $faq, $this->is_admin());
			if($new_faq_id && $question_id && !$faq_id)
			{
				$this->so->delete_question($question_id);
			}
			return $new_faq_id;
			
		}//end save
		
		function set_active_answer($faq_ids)
		{
			return $this->so->set_active_answer($faq_ids);
		}//end set active answer
		
		function set_active_question($question_ids)
		{
			return $this->so->set_active_question($question_ids);
		}//end set active question

		function set_comment($comment_id, $comment_data)
		{
			$comment_id = (int) $comment_id;
			$comment_data['faq_id']	= (int) $comment_data['faq_id'];
			$comment_data['user_id'] = $GLOBALS['phpgw_info']['user']['account_id'];
			$this->so->set_comment($comment_id, $comment_data);
		}//end set comment

		function set_question($question)
		{
			return $this->so->set_question($question, $this->is_admin());
		}//end set question
		
		function set_rating($faq_id, $rating)
		{
			if(!@$this->rated[$faq_id])//only rate if not already done so
			{
				//make sure values are within a valid range
				$rating = ($rating < 1 ? 1 : $rating);
				$rating = ($rating > 5 ? 5 : $rating);

				$this->so->set_rating($faq_id, $rating);
				$this->rated[$faq_id] = True;
				$GLOBALS['phpgw']->session->appsession('rated','phpbrain', $this->rated);
			}
		}//end set_rating
		
	}//end class bokb
	
	
