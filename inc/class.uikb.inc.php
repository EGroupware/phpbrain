<?php
/**************************************************************************\
* phpGroupWare - KnowledgeBase                                             *
* http://www.phpgroupware.org                                              *
* Written by Dave Hall [skwashd AT phpgroupware.org]		           *
* ------------------------------------------------------------------------ *
* Started off as a port of phpBrain - http://vrotvrot.com/phpBrain/	   *
*  but quickly became a full rewrite					   *
* ------------------------------------------------------------------------ *
*  This program is free software; you can redistribute it and/or modify it *
*  under the terms of the GNU General Public License as published by the   *
*  Free Software Foundation; either version 2 of the License, or (at your  *
*  option) any later version.                                              *
\**************************************************************************/
	
	class uikb
	{
		var $bo;
		var $edit_vals;
		var $cats;
		var $t;
		var $theme;
		var $public_functions = array('index'			=> True,
									'add'				=> True,
									'add_comment'		=> True,
									'add_question'		=> True,
									'browse'			=> True,
									'confirm_delete'	=> True,
									'css'				=> True,
									'delete_comment'	=> True,
									'edit'				=> True,
									'maint_answer'		=> True,
									'maint_question'	=> True,
									'preview'			=> True,
									'rate'				=> True,
									'save'				=> True,
									'search'			=> True,
									'unanswered'		=> True,
									'view'				=> True,
									'help'				=> True
						);
		
		function uikb()
		{
			$this->bo	= createObject('phpbrain.bokb');
			$this->cats	= CreateObject('phpgwapi.categories');
			$this->theme	= $GLOBALS['phpgw_info']['theme'];
			$this->t	= $GLOBALS['phpgw']->template;
			$this->t->unknowns = 'remove';
		}
		
		function index()
		{
			$this->browse();
			$GLOBALS['phpgw']->common->phpgw_exit();
		}

		function add()
		{
			if(isset($_GET['question']) && isset($_GET['question_id']))
			{
				$this->edit_vals['title'] = urldecode(trim($_GET['question']));
				$this->edit_vals['question_id'] = trim($_GET['question_id']);
			}//end if question
			$this->edit_answer(True);
			$GLOBALS['phpgw']->common->phpgw_exit();
		}//end add

		function add_comment()
		{
			$comment_id = (isset($_POST['comment_id']) ? trim($_POST['comment_id']) : 0);
			$comment_data['faq_id'] = (isset($_POST['faq_id']) ? trim($_POST['faq_id']) : 0);
			$comment_data['comment'] = (isset($_POST['comment']) ? trim($_POST['comment']) : '');
			
			if($comment_id)
			{
				$link['menuaction'] = 'phpbrain.uikb.edit_comments';
			}
			else
			{
				$link['menuaction'] = 'phpbrain.uikb.view';
			}
			
			if($comment_data['faq_id'] && $comment_data['comment'])
			{
				$this->bo->set_comment($comment_id, $comment_data);
				$link['faq_id']		= $comment_data['faq_id'];
				$link['msg'] = 'comment_added';
			}
			else
			{
				$link['msg'] = 'comment_invalid';
			}
			
			header('Location: ' . $GLOBALS['phpgw']->link('/index.php',$link)); 
			$GLOBALS['phpgw']->common->phpgw_exit();
		
		}//end add comment	
		
		function add_question()
		{
			$question = (isset($_POST['comment']) ? trim($_POST['comment']) : '');
			$ok = false;
			if(strlen($question) && !$this->bo->is_anon())
			{
				$ok = $this->bo->set_question($question);
			}//if valid question and user
			
			if($ok)
			{
				$msg = 'save ok';
			}
			else
			{
				$msg = 'not added - error';
			}// if ok
			$this->unanswered($msg);
			$GLOBALS['phpgw']->common->phpgw_exit();
			 
		}//end add question

		function browse()
		{
			$this->search_banner();
			$this->t->set_file('browse', 'browse.tpl');
			$cat_id = (int) ( (isset($_GET['cat_id']) && $_GET['cat_id'] != 0) ? trim($_GET['cat_id']) : 0);
			$cat_name = (isset($_GET['cat_name']) ? trim($_GET['cat_name']) : '');
			if($cat_name)
			{
				$cat_id = $this->cats->name2id($cat_name);
			}

			$this->t->set_block('browse', 'cur_cat_name', 'ccname');
			$this->t->set_block('browse', 'cat_row', 'rows');
			$this->t->set_block('browse', 'table', 'tbl');

			if($cat_id)
			{
				$cat_name = $this->cats->id2name($cat_id);
				$this->t->set_var('cur_category_name', $cat_name);
				$this->t->set_var('up_category_url', $GLOBALS['phpgw']->link('/index.php',
											array('menuaction' => 'phpbrain.uikb.browse',
												'cat_id'	=> $this->cats->id2name($cat_id,'parent')
												)
											)
						);
				$this->t->set_var('lang_up', lang('up'));
				$this->t->parse('ccname', 'cur_cat_name');
			}
			else
			{
				$this->t->set_var('ccname', '');
			}

			$cat_data = $this->bo->get_cat_data($cat_id);
			$cells = 0;//used for cell numbers to see if row is needed
			if(is_array($cat_data))	
			{
				$this->t->set_file('cat_list', 'cat_list.tpl');
				$this->t->set_block('cat_list', 'sub_cat', 'subcats');
				$this->t->set_block('cat_list', 'cell', 'cells');

				foreach( $cat_data as $cat_key => $cat_fields)
				{
					if(is_array($cat_fields['subs']))
					{
						foreach($cat_fields['subs'] as $sub_id => $sub_vals)
						{
							$this->t->set_var('sub_cat_link',$GLOBALS['phpgw']->link('/index.php',
																array('menuaction' => 'phpbrain.uikb.browse',
																		'cat_id'	=> $sub_id)
																	)
											);
							$this->t->set_var('sub_cat_name', $sub_vals['name']);
							if($sub_vals['num_entries'])//count(entries)
							{
								$this->t->set_var('sub_cat_count', ' (' . $sub_vals['num_entries'] . ')' );
							}
							else//count == 0
							{
								$this->t->set_var('sub_cat_count', '');
							}//count(entries)
							
							$this->t->parse('subcats','sub_cat',true);
						}//end foreach(subcats)	

					}
					else //!is_array(subcats)
					{
						$this->t->set_var('subcats', '');
					}//end is_array(subcats)
					
					$this->t->set_var('cat_link',$GLOBALS['phpgw']->link('/index.php',
											array('menuaction' => 'phpbrain.uikb.browse',
												'cat_id'	=> $cat_key)
															)
									);
					$this->t->set_var('cat_name', $cat_fields['name']);
					if($cat_fields['num_entries'])//count(entries)
					{
						$this->t->set_var('cat_count', ' (' . $cat_fields['num_entries'] . ')' );
					}
					else//count == 0
					{
						$this->t->set_var('cat_count', '');
					}//count(entries)

					$cells++;//increment cells - to track if row needed
					if(!($cells % 2))//if even then new row required
					{
						$this->t->parse('cells', 'cell', true);
						$this->t->parse('row', 'cells');//, true);
						$this->t->parse('rows', 'cat_row', true);
					}
					else
					{
						$this->t->parse('cells', 'cell');
					}//end if is_even(cells)
					
				}//end foreach cats

  			if($cells % 2)//do we need to create a blank cell and close the row
  			{
  				$this->t->set_var('cell', '<td>&nbsp;</td>');
					$this->t->parse('cells', 'cell', true);
					$this->t->parse('row', 'cells');//, true);
					$this->t->parse('rows', 'cat_row', true);
				}//end if is_even(cells)

				$this->t->parse('tbl','table');
			}
			else //!is_array(cats)
			{
				$this->t->set_block('browse', 'table', 'tbl');
				$this->t->set_var('tbl','');
			}// end is_array(cats)
			
			$faqs = $this->bo->get_faq_list($cat_id);
			$this->t->set_block('browse', 'cat_count', 'count');
			if(is_array($faqs))
			{
				$this->t->set_var('lang_cat_contains' , lang('%1 contains %2 items', $cat_name, count($faqs)));
				$this->t->parse('count', 'cat_count');
				$this->t->set_var('faqs', $this->summary($faqs));
			}
			else
			{
				$this->t->set_var('count', '');
				$this->t->set_var('faqs', '');
			}
			
			$this->t->pfp('out', 'browse');
		}//end browse

		function build_form($form_target, $title, $input_descr, $input_hidden=false, $allow_anon=false)
		{

			$tpl = $this->t;
			$tpl->set_file('form', 'form.tpl');
			
  		if(!$this->bo->is_anon())
  		{
  			$tpl->set_var(array('form_url'		=> $GLOBALS['phpgw']->link('/index.php', 
  											array('menuaction' => "phpbrain.uikb.$form_target")),
  					'lang_title'		=> lang($title),
  					'lang_input_descr'	=> lang($input_descr),
  					'lang_submit_val'	=> lang('add')
 					)
 				);

				$tpl->set_block('form', 'hidden_var', 'hidden_vars');
				if(is_array($input_hidden[1]))//multiple dimension array??
				{
					foreach($input_hidden as $ih_key => $ih_vals)
					{
						$tpl->set_var($ih_vals);
						$tpl->parse('hidden_vars', 'hidden_var',true);
					}
				}
				elseif(is_array($input_hidden))
				{
						$tpl->set_var($input_hidden);
						$tpl->parse('hidden_vars', 'hidden_var');
				}
				else//must be false
				{
					$tpl->set_var('hidden_vars', '');
				}//end if input_hidden
  			return $tpl->subst('form');
  		}
  		else//must be anon user
  		{
  			$not_reg  = '<a href="';
  			$not_reg .= $GLOBALS['phpgw']->link('/index.php', array('menuaction' => 'phpbrain.uikb.redirect_anon_info'));
  			$not_reg .='">' . lang('cant_post_must_register') . '</a>';
				return $not_reg;

  		}//end is_anon
		}//end build_form
		
		function edit()
		{
			$faq_id = (int) (isset($_GET['faq_id']) ? trim($_GET['faq_id']) : 0);
			$this->edit_vals = $this->bo->get_item($faq_id, false);
			$this->edit_answer(False);
			$GLOBALS['phpgw']->common->phpgw_exit();
		}//end edit
		
		function edit_answer($new)
		{
			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();

			$this->t->set_file('edit_faq', 'edit_faq.tpl');

			$this->t->set_var('add_answer_link', $GLOBALS['phpgw']->link('/index.php', 
												array('menuaction'	=> 'phpbrain.uikb.save',
													'question_id'	=> $this->edit_vals['question_id']
													)
												)
							);

			$this->t->set_var($this->edit_vals);

			$this->t->set_block('edit_faq', 'b_status', 'status');
			if($this->bo->is_admin() && isset($this->edit_vals['faq_id']))
			{
				$this->t->set_var(
					array('lang_status'	=> lang('status'),
						'check'		=> ($this->edit_vals['published'] ? 'checked' : ''),
						'lang_active_when_checked' => lang('active_when_checked')
						)
					);
				$this->t->parse('status', 'b_status');
			}
			else
			{
				$this->t->set_var('status', '');
			}

			$add_answer = ($new ? 'add_answer' : 'edit_answer');
			$lang = array('lang_add_answer'			=> lang($add_answer),
					'lang_check_before_submit'	=> lang('check_before_submit'),
					'lang_not_submit_qs_warn'	=> lang('not_submit_qs_warn'),
					'lang_inspire_by_suggestions'	=> lang('inspire_by_suggestions'),
					'lang_title'			=> lang('title'),
					'lang_keywords'			=> lang('keywords'),
					'lang_category'			=> lang('category'),
					'lang_related_url'		=> lang('related_url'),
					'lang_text'				=> lang('text'),
					'lang_reset'			=> lang('reset'),
					'lang_save'				=> lang('save'),
					'lang_back'				=> lang('back'),
					'lang_delete'			=> lang('delete')
					);
			$this->t->set_var($lang);

			$cat_options = $this->cats->formatted_list('select','all',$this->edit_vals['cat_id']);
			$this->t->set_var('cats_options', $cat_options);

			$this->t->pfp('out', 'edit_faq');
		}//end edit question

		function help()
		{
 			$GLOBALS['phpgw']->common->phpgw_header();
 			echo parse_navbar();
			echo '<h2>Coming Soon!</h2>';
			echo 'This will link to the manual for this app when completed';
			$GLOBALS['phpgw']->common->phpgw_exit();
		}//end help
		
		function maint_answer()
		{
			if(!$this->bo->is_admin())
			{
  			$GLOBALS['phpgw']->common->phpgw_header();
  			echo parse_navbar();
				echo '<h2 align="center">Coming Soon!</h2>';
				echo 'A proper manual will be added soon';
				$GLOBALS['phpgw']->common->exit();
			}
			else//must be admin
			{
				$msg = '';
				if($_POST['activate'] && (count($_POST['faq_id']) != 0))
				{
					$msg = lang('%1 faqs_activated', $this->bo->set_active_answer($_POST['faq_id']));
				}
				if($_POST['delete'] && (count($_POST['faq_id']) != 0))
				{
					$msg = lang('%1 faqs_deleted', $this->bo->delete_answer($_POST['faq_id']));
				}
  			$GLOBALS['phpgw']->common->phpgw_header();
  			echo parse_navbar();
  			$this->t->set_file('admin_maint', 'admin_maint.tpl');
  			$this->t->set_block('admin_maint', 'pending_list', 'pending_items');
  			$this->t->set_block('admin_maint', 'pending_block', 'p_block');
  			$this->t->set_var('admin_url', $GLOBALS['phpgw']->link('/admin/index.php'));
  			$this->t->set_var('lang_return_to_admin', lang('return_to_admin'));
				$this->t->set_var('msg', ((strlen($msg) !=0) ? $msg : '&nbsp;'));

				$faqs = $this->bo->get_faq_list('', true);				
  			if(is_array($faqs))
  			{
  				$this->t->set_var(array('lang_admin_section'	=> lang('maintain_answers'),
  										'lang_explain_function'	=> lang('explain_maintain_answers'),
											'form_action'			=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=phpbrain.uikb.maint_answer')));
  
  				foreach($faqs as $key => $vals)
  				{
  					$this->t->set_var(array('id'		=> "faq_id[$key]",
  											'text'		=> $vals['text'],
												'row_bg'	=> (($row%2) ? $this->theme['row_on'] : $this->theme['row_off']),
												'extra'		=> '<a href="'.$GLOBALS['phpgw']->link('/index.php', 
																	array('menuaction' 	=> 'phpbrain.uikb.preview',
																			'faq_id'	=> $key
																			)
																		). '" target="_blank">'.lang('preview').'</a>'
  											)
  									);
  					$this->t->parse('pending_items', 'pending_list', true);
  					$row++;
   				}//end foreach(pending)
					$lang = array('lang_explain_function'	=> lang('explain_faq_admin'),
								'lang_admin_section'		=> lang('section_maint_faqs'),
								'lang_enable'				=> lang('enable'),
								'lang_delete'				=> lang('delete')
								);
					$this->t->set_var($lang);
  				$this->t->parse('p_block', 'pending_block');
  			}
  			else//no pending faqs
  			{
  				$this->t->set_var('p_block', lang('none_pending'));
  			}//end if is_array(open)
  			$this->t->pfp('out', 'admin_maint');
			}//end is admin
		}//end maint answers
		
		function maint_question()
		{
			if(!$this->bo->is_admin())
			{
				header('Location: ' . $GLOBALS['phpgw']->link('/index.php', 'menuaction=phpbrain.uikb.index'));
				$GLOBALS['phpgw']->common->exit();
			}
			else//must be admin
			{
				$msg = '';
				if($_POST['activate'] && (count($_POST['question_id']) != 0))
				{
					$msg = lang('%1 questions_activated', $this->bo->set_active_question($_POST['question_id']));
				}
				if($_POST['delete'] && (count($_POST['question_id']) != 0))
				{
					$msg = lang('%1 questions_deleted', $this->bo->delete_answer($_POST['question_id']));
				}
  			$GLOBALS['phpgw']->common->phpgw_header();
  			echo parse_navbar();
  			$this->t->set_file('admin_maint', 'admin_maint.tpl');
  			$this->t->set_block('admin_maint', 'pending_list', 'pending_items');
  			$this->t->set_block('admin_maint', 'pending_block', 'p_block');
  			$this->t->set_var('admin_url', $GLOBALS['phpgw']->link('/admin/index.php'));
  			$this->t->set_var('lang_return_to_admin', lang('return_to_admin'));
				$this->t->set_var('msg', ((strlen($msg) !=0) ? $msg : '&nbsp;'));

				$questions = $this->bo->get_questions(true);				
  			if(is_array($questions))
  			{
  				foreach($questions as $key => $val)
  				{
  					$this->t->set_var(array('id'		=> "question_id[$key]",
  											'text'		=> $val,
												'row_bg'	=> (($row%2) ? $this->theme['row_on'] : $this->theme['row_off']),
  											)
  									);
  					$this->t->parse('pending_items', 'pending_list', true);
  					$row++;
   				}//end foreach(pending)
					$lang = array('lang_explain_function'	=> lang('explain_questions_admin'),
								'lang_admin_section'		=> lang('section_maintain_questions'),
								'lang_enable'				=> lang('enable'),
								'lang_delete'				=> lang('delete'),
								'form_action'				=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=phpbrain.uikb.maint_question')
								);
					$this->t->set_var($lang);
  				$this->t->parse('p_block', 'pending_block');
  			}
  			else//no pending faqs
  			{
  				$this->t->set_var('p_block', lang('none_pending'));
  			}//end if is_array(open)
  			$this->t->pfp('out', 'admin_maint');
			}//end is admin
		}//end maint question

		function preview()
		{
			$this->view(false);
			$GLOBALS['phpgw']->common->phpgw_exit();
		}

		function rate()
		{
			$faq_id = (int) (isset($_GET['faq_id']) ? trim($_GET['faq_id']) : 0);
			$rating = (int) (isset($_GET['rating']) ? trim($_GET['rating']) : 0); 
			if( ($faq_id > 0) && ($rating > 0))
			{
				$this->bo->set_rating($faq_id, $rating);
			}
			$this->view();
			$GLOBALS['phpgw']->common->phpgw_exit();
		}//end rate
		
		function save()
		{
			$faq_id 	= (int) (isset($_POST['faq_id']) ? trim($_POST['faq_id']) : 0);
			$question_id 	= (int) (isset($_GET['question_id']) ? trim($_GET['question_id']) : 0);
			$faq['cat_id'] 	= (int) (isset($_POST['cat_id']) ? trim($_POST['cat_id']) : 0);
			$faq['title'] 	= (isset($_POST['title']) ? trim($_POST['title']) : '');
			$faq['keywords']= (isset($_POST['keywords']) ? trim($_POST['keywords']) : '');
			$faq['text'] 	= (isset($_POST['text']) ? trim($_POST['text']) : '');
			$faq['is_faq'] 	= (int) (isset($_POST['is_faq']) ? trim($_POST['is_faq']) : 0);
			$faq['url']	= (isset($_POST['url']) ? trim($_POST['url']) : '');
			$faq['published'] = (isset($_POST['published']) ? True : False);
			$faq_id = $this->bo->save($faq_id, $faq, $question_id);
			if($faq_id)
			{
  			header ('Location: ' . $GLOBALS['phpgw']->link('/index.php', 
  									array('menuaction'	=> 'phpbrain.uikb.view',
  										'faq_id'		=>  $faq_id,
  										'msg'			=> 'faq_saved'
  										)
  									)
  					);
  			$GLOBALS['phpgw']->common->phpgw_exit();
			}
			else
			{
				echo 'whoops!';
			}
		}
		
		function search()
		{
			$search = (isset($_GET['search']) ? trim($_GET['search'])
						: (isset($_POST['search']) ? trim($_POST['search']) : ''));

			if((isset($_POST['show']) && strlen(trim($_POST['show'])) > 0)
				|| (isset($_GET['show']) && strlen(trim($_GET['show'])) > 0))
			{
				$show = (int) (isset($_POST['show']) ? trim($_POST['show']) : trim($_GET['show']));
			}
			else
			{
				$show = null;
			}
			
			if($search)
			{
				$results = $this->bo->get_search_results($search, $show);
				if(is_array($results))
				{
					$this->search_banner($search, lang('%1 matches found', count($results)));
					echo $this->summary($results);
					
				}
				else//nothing found
				{
					$this->search_banner($search, lang('none found - revise or browse'));
				}
			}
			else
			{
				$this->browse();
				$GLOBALS['phpgw']->common->phpgw_exit();
			}
		}//end search

		function search_banner($search='', $msg='')
		{
			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();

			$this->t->set_file('search', 'search.tpl');

			$msg = (isset($_GET['msg']) ? trim($_GET['msg']) : $msg);
			$search = (isset($_GET['search']) ? trim($_GET['search']) : $search);
			$this->t->set_var('curent_search', $search);
			$this->t->set_var('message', $msg);
						
			$this->t->set_var('header_bgcolor', $this->theme['navbar_bg']);
			$this->t->set_var('lang_kb_contains', lang('kb_contains'));		
			$stats = $this->bo->get_stats();
			$open = $stats['num_open'];
			foreach($stats as $stat_name => $stat_val)
			{
				$this->t->set_var('lang_' . $stat_name, lang("$stat_name %1", $stat_val));
			}
			unset($stats);

			$this->t->set_block('search', 'current_questions', 'cqs');
			foreach($this->bo->get_latest() as $cq_key => $descr)
			{
			
  			$this->t->set_var('cq_url', $GLOBALS['phpgw']->link('/index.php', array('menuaction' 	=> 'phpbrain.uikb.unanswered',
  																					'question_id'	=> $cq_key
																						)
  																)
  							);
								
  			$this->t->set_var('cq_descr', $descr);
				$this->t->parse('cqs', 'current_questions',true);
			}
			$lang = array('lang_question'		=> lang('question'),
						'lang_current_questions'=> lang('current_questions'),
						'lang_search'			=> lang('search'),
						'lang_example'			=> lang('example'),
						'lang_show'				=> lang('show'),
						'lang_faqs_and_tutes'	=> lang('faqs_and_tutes'),
						'lang_faqs'				=> lang('faqs'),
						'lang_tutorials'		=> lang('tutorials'),
						'lang_add_answer'		=> lang('add_answer'),
						'lang_add_q'			=> lang('add_question (%1 open)', $open),
						'lang_browse'			=> lang('browse'),
						'lang_help'				=> lang('help')
						);
						
			$this->t->set_var($lang);

			$this->t->set_var('search_url', $GLOBALS['phpgw']->link('/index.php', 
														array('menuaction' => 'phpbrain.uikb.search')));

			$this->t->set_var('link_add_answer', $GLOBALS['phpgw']->link('/index.php', 
														array('menuaction' => 'phpbrain.uikb.add')));

			$this->t->set_var('link_browse', $GLOBALS['phpgw']->link('/index.php', 
														array('menuaction' => 'phpbrain.uikb.browse')));

			$this->t->set_var('link_open_qs', $GLOBALS['phpgw']->link('/index.php', 
														array('menuaction' => 'phpbrain.uikb.unanswered')));

			$this->t->set_var('link_help', $GLOBALS['phpgw']->link('/index.php', 
														array('menuaction' => 'phpbrain.uikb.help')));
			
			$this->t->set_block('search','admin', 'admins');
			if($this->bo->is_admin())
			{
				$this->t->parse('admins','admin');				
			}
			else
			{
				$this->t->set_var('admins', '');
			}
			
			$this->t->pfp('out', 'search');
		}

		function summary($summaries)
		{
			if(is_array($summaries))
			{
				$t = $this->t;
				$t->set_file('faq_sum', 'faq_sum.tpl');
				$t->set_block('faq_sum', 'summary', 'summaries');
				foreach($summaries as $faq_id => $faq_vals)
				{
					$t->set_var('title',$faq_vals['title']);
					$t->set_var('text',strlen($faq_vals['text']) < 150 ? $faq_vals['text'] :
						substr($faq_vals['text'],0,strpos($faq_vals['text'],' ',150)).'...');
					$t->set_var('faq_url', $GLOBALS['phpgw']->link('/index.php', array(
						'menuaction' => 'phpbrain.uikb.view',
						'faq_id' => $faq_id
					)));
					$t->set_var('lang_score', lang('score %1', $faq_vals['score']));
					$t->set_var('lang_last_mod', lang('last_mod %1', $faq_vals['last_mod']));
					$t->set_var('lang_views', lang('views %1', $faq_vals['views']));
					$t->set_var('lang_rating', lang('rating %1', $faq_vals['vote_avg']));
					$t->set_var('lang_votes', lang('votes %1', $faq_vals['votes']));
					$t->parse('summaries', 'summary', true);
				}//end foreach summary
				return $t->subst('faq_sum');
			}
			else
			{
				return '';
			}
		}//end summaries


		function unanswered($msg = '')
		{
			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();
			$this->t->set_file('unanswered', 'unanswered.tpl');
			$this->t->set_block('unanswered', 'open_list', 'open_ones');
			$this->t->set_block('unanswered', 'open_block', 'o_block');
			$this->t->set_var('index_url', $GLOBALS['phpgw']->link('/index.php', 
											array('menuaction' => 'phpbrain.uikb.index')
											)
								);
			$this->t->set_var('lang_return_to_index', lang('return_to_index'));
			$this->t->set_var('msg', ((strlen($msg) !=0) ? lang($msg) : '&nbsp;'));

			$open_qs = $this->bo->get_questions(false);
			if(is_array($open_qs))
			{
				$this->t->set_var(array('lang_cur_open_qs'	=> lang('cur_open_qs'),
										'lang_know_contrib'	=> lang('know_contrib')));

				if($this->bo->is_anon())
				{
					$lang_opt = lang('register');
					$link_opt = $GLOBALS['phpgw']->link('/index.php', 
						array('menuaction' => 'phpbrain.uikb.redirect_anon_info'));
				}
				else//must be registered
				{
					$lang_opt = lang('answer');
					$link_opt = $GLOBALS['phpgw']->link('/index.php', 
						array('menuaction' => 'phpbrain.uikb.add'));
				}//end is anon
				
				foreach($open_qs as $id => $question)
				{
					$this->t->set_var(array('question_id'	=> $id,
											'question_text'	=> $question,
											'lang_option'	=> $lang_opt,
											'link_option'	=> "$link_opt&question=" . urlencode($question) . '&question_id=' . $id,
											'row_bg'		=> (($row%2) ? $this->theme['row_on'] : $this->theme['row_off'])
											)
									);
					$this->t->parse('open_ones', 'open_list', true);
					$row++;
 				}//end foreach(question)
				$this->t->parse('o_block', 'open_block');
			}
			else//no open questions
			{
				$this->t->set_var('o_block', lang('none_unanswered'));
			}//end if is_array(open)
  		$this->t->set_var('question_form', $this->build_form('add_question', 'add_question', 'question'));

			$this->t->pfp('out', 'unanswered');
		}//end show unanswered

		function view($header = true)
		{
			if($header)
			{
  				$GLOBALS['phpgw']->common->phpgw_header();
  				echo parse_navbar();
			}
			else
			{
				echo "<html>\n<head>\n";
				echo "<title>\n\t";
				echo $GLOBALS['phpgw_info']['server']['site_title'] .' ['. lang('phpbrain') . "]\n";
				echo "</title>\n";
				echo "<style type=\"text/css\">\n<!--";
				echo $this->css();
				echo "\n-->\n</style></head>\n<body>";
			}
			
			$faq_id = (isset($_GET['faq_id']) ? trim($_GET['faq_id']) : 0);
			$search = (isset($_GET['search']) ? trim($_GET['search']) : '');
			$msg = (isset($_GET['msg']) ? trim($_GET['msg']) : '');
			
			$item = $this->bo->get_item($faq_id);
			if(is_array($item) && $faq_id)
			{
  				$this->t->set_file('showitem', 'showitem.tpl');
				$lang = array('msg'		=> ($msg ? lang($msg) : ''),
					'lang_submitted_by'	=> lang('submitted_by'),
					'lang_views'		=> lang('views'),
					'lang_rating'		=> lang('rating'),
					'lang_title'		=> lang('title'),
					'lang_related_url'	=> lang('related_url'),
					'lang_text'		=> lang('text'),
					'lang_poor'		=> lang('poor'),
					'lang_excellent'	=> lang('excellent'),
					'lang_rate_why_explain'	=> lang('improve_by_rate'),
					'lang_comments'		=> lang('comments')
					);

				if($search)//was the user seaching?
				{
					$this->t->set_var('return_url', $GLOBALS['phpgw']->link('/index.php', 
						array('menuaction'	=> 'phpbrain.uikb.search',
							'search'		=> $search
							)
						)
					);
					$lang['return_msg'] = lang('return_to_search %1', $search); 
				}
				elseif(!$header)
				{
					$this->t->set_var('return_url', 'javascript:window.close();');
					$lang['return_msg'] = lang('close window');
				}
				else//no - they used the cat navigation to get here
				{
					$this->t->set_var('return_url', $GLOBALS['phpgw']->link('/index.php', 
						array('menuaction'	=> 'phpbrain.uikb.browse',
							'cat_id'		=> $item['cat_id']
							)
						)
					);
					$lang['return_msg'] = lang('return_to_cats %1', $this->cats->id2name($item['cat_id'])); 
				}//end if search
				
				$item['text'] = nl2br($item['text']);

				if($item['url'])
				{
					$item['rel_link'] = '<a href="' . $item['url'] .'" target="_blank">' . $item['url'] . '</a>';
				}
				else
				{
					$item['rel_link'] = lang('none');
				}
				
				$this->t->set_block('showitem', 'click_rating', 'click_ratings');
				$this->t->set_block('showitem', 'b_rate', 'b_rating');
				$this->t->set_block('showitem', 'b_no_rate', 'b_no_rating');
				if(!@$this->bo->rated[$faq_id])
				{
					$rate_url = $GLOBALS['phpgw']->link('/index.php',
                                                array('menuaction'      => 'phpbrain.uikb.rate',
                                                        'faq_id'                => $faq_id
                                                        )
                                                );

					for($i=1; $i<=5; $i++)
					{
						$this->t->set_var('rate_link', "$rate_url&rating=$i");
						$this->t->set_var('rate_val', $i);
						$this->t->parse('click_ratings', 'click_rating',true);
                                	}
					
					$this->t->parse('b_rating', 'b_rate', True);
					$this->t->set_var('b_no_rating', '');
				}
				elseif(isset($_GET['rating']))
				{
					$this->t->set_var('lang_rate_msg', lang('thanks_4_rating'));
					$this->t->set_var('b_rating', '');
					$this->t->parse('b_no_rating', 'b_no_rate', True);
				}
				else
				{
					$this->t->set_var('lang_rate_msg', lang('already_rated'));
					$this->t->set_var('b_rating', '');
					$this->t->parse('b_no_rating', 'b_no_rate', True);
				}
				
				$this->t->set_block('showitem', 'cmnt', 'cmnts');
				if(is_array($item['comments']))
				{
					$row = 0;//row counter
					foreach($item['comments'] as $comment_key => $comment_vals)
					{
						if($row % 2)//is even?
						{
							$comment_vals['comment_bg'] = $this->theme['row_on'];
						}
						else//must be odd
						{
							$comment_vals['comment_bg'] = $this->theme['row_off'];
						}//end if row == even
						
						$comment_vals['comment_text'] = nl2br($comment_vals['comment_text']);

						$this->t->set_var($comment_vals);
						$this->t->parse('cmnts', 'cmnt',true);
						$row++; //increment row counter
						
					}//end foreach(comments)
				}
				else//no comments
				{
					$this->t->set_var('comment_bg', $this->theme['row_on']);
					$this->t->set_var('comments', '<tr align="center"><td>' . lang('no comments') . '</td></tr>');
				}//end if is_array(comments)
				
				$this->t->set_var('comment_form', $this->build_form('add_comment', 'add_comments', 'comment', 
										array('hidden_name' =>'faq_id',
											 'hidden_val' => $faq_id
											 )
										)
							);
				$this->t->set_block('showitem', 'admin_option', 'admin_options');
				if($this->bo->is_admin())
				{
					$this->t->set_var('admin_url', $GLOBALS['phpgw']->link('/index.php',
										array('menuaction'	=> 'phpbrain.uikb.edit',
											'faq_id'	=> $item['faq_id']
										)
									)
							);
					$this->t->set_var('lang_admin_text', lang('edit_faq'));
					$this->t->parse('admin_options', 'admin_option', true);
				}
				else
				{
					$this->t->set_var('admin_options', '');
				}
	
				$this->t->set_var($lang);
				$this->t->set_var($item);
				
				$this->t->pfp('out', 'showitem');
			}
			else//invalid faq_id
			{
				echo lang('invalid faq request - or something is NQR');
			}//end is_array(item)
		}//end get_item

		function css()
		{
			return 'th   {  font-family: '.$this->theme['font'].'; font-size: 10pt; font-weight: bold; background-color: #D3DCE3;} '. "\n".
				'td   {  font-family: '.$this->theme['font'].'; font-size: 10pt;} '. "\n".
				'p {  font-family: '.$this->theme['font'].'; font-size: 10pt} '. "\n".
				'li {  font-family: '.$this->theme['font'].'; font-size: 10pt} '. "\n".
				'h1   {  font-family: '.$this->theme['font'].'; font-size: 16pt; font-weight: bold} '. "\n".
				'h2   {  font-family: '.$this->theme['font'].'; font-size: 13pt; font-weight: bold} '. "\n".
				'A:link    {  font-family: '.$this->theme['font'].'; text-decoration: none; '.$this->theme['link'].'} '. "\n".
				'A:visited {  font-family: '.$this->theme['font'].'; text-decoration: none; color: '.$this->theme['link'].' } '. "\n".
				'A:hover   {  font-family: '.$this->theme['font'].'; text-decoration: underline; color: '.$this->theme['alink'].'} '. "\n".
				'A.small:link    {  font-family: '.$this->theme['font'].'; font-size: 8pt; text-decoration: none; color: '.$this->theme['link'].'} '. "\n".
				'A.small:visited {  font-family: '.$this->theme['font'].'; font-size: 8pt; text-decoration: none; color: '.$this->theme['vlink'].'} '. "\n".
				'A.small:hover   {  font-family: '.$this->theme['font'].'; font-size: 8pt; text-decoration: underline; color: '.$this->theme['alink'].'} '. "\n".
				'.nav {  font-family: '.$this->theme['font'].'; background-color: ' . $this->theme['bg10'] . ';} ' . "\n".
				'.search   {  font-family: '.$this->theme['font']. '; color: ' . $this->theme['navbar_text'] . '; background-color: '.$this->theme['navbar_bg'] . '; font-size: 9pt; border: 1px solid ' . $this->theme['bg_color'] . ';} '. "\n".
				'.navbg { font-family: '.$this->theme['font'].'; color: '.$this->theme['navbar_text'] .'; background-color: '.$this->theme['navbar_bg'] . ';} '. "\n".
				'a.contrlink { font-family: '.$this->theme['font'].'; color: '.$this->theme['navbar_text'] .'; text-decoration: none;} '. "\n".
				'a.contrlink:hover, a.stats:active { font-family: '.$this->theme['font'].'; color: '.$this->theme['navbar_text'] .'; text-decoration: underline;}' . "\n".
				'.faq_info {  font-family: '.$this->theme['font'].'; color:' . $this->theme['navbar_bg'] . '; font-size: 8pt} ' . "\n" .
				'hr {background-color: ' . $this->theme['navbar_bg'] . '; border-width: 0px; heght: 2px;} ' . "\n" .
				'input, textarea {color:' . $this->theme['bg_text']. '; background-color:' .  $this->theme['bg_color'] . '; font-family: '.$this->theme['font']. '; font-size: 9pt; border: 1px solid ' . $this->theme['bg_text'] . ';} '. "\n".
				'';
		}
	}	
