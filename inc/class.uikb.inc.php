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
									'delete_faq' => True,
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
			if (isset($_POST['cancel']))
			{
				header('Location: ' . $GLOBALS['phpgw']->link('/index.php', 'menuaction=phpbrain.uikb.index'));
				$GLOBALS['phpgw']->common->exit();
			}
			
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
				$link['msg'] = 'comment added';
			}
			else
			{
				$link['msg'] = 'comment invalid';
			}
			
			header('Location: ' . $GLOBALS['phpgw']->link('/index.php',$link)); 
			$GLOBALS['phpgw']->common->phpgw_exit();
		
		}//end add comment	
		
		function add_question()
		{
			$question = (isset($_POST['comment']) ? trim($_POST['comment']) : '');
			
			if (isset($_POST['cancel']))
			{
				header('Location: ' . $GLOBALS['phpgw']->link('/index.php', 'menuaction=phpbrain.uikb.index'));
				$GLOBALS['phpgw']->common->exit();
			}
			
			$ok = false;
			if(strlen($question) && !$this->bo->is_anon())
			{
				$ok = $this->bo->set_question($question);
			}//if valid question and user
			
			if($ok)
			{
				if ($this->bo->is_admin())
				{
					$msg='Question added to database';
				}
				else
				{
					$msg = 'Question added. It will appear on the database after revision of the administrator';
				}
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
			$cat_id = ( (isset($_POST['cat_id']) && $_POST['cat_id'] != 0) ? trim($_POST['cat_id']) : '');
			$msg = ( isset($_GET['msg'])  ? lang(trim($_GET['msg'])) : '');
			$search = (isset($_GET['query']) ? trim($_GET['query'])
						: (isset($_POST['query']) ? trim($_POST['query']) : ''));
			$this->start = ( isset($_POST['start'])  ? trim($_POST['start']) : 0);
			$_POST['filter'] = ( isset($_POST['filter']) ? trim($_POST['filter']) : 'Answered');
			$filter = $_POST['filter'];
			
			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();
			
			$this->t->set_file('browse', 'browse.tpl');
			
			$this->t->set_block('browse','phpbrain_header');
			$this->t->set_block('browse','column');
			$this->t->set_block('browse','row');
			$this->t->set_block('browse','phpbrain_footer');
			
			$this->t->set_var('th_bg',$GLOBALS['phpgw_info']['theme']['th_bg']);
			
			$not_empty=TRUE;
			if($search)
			{	
				$faqs = $this->bo->get_search_results($search);
				if(is_array($faqs))
				{
					$msg=lang('%1 matches found', count($faqs));
				}
				else//nothing found
				{
					$msg=lang('none found - revise or browse');
					$not_empty=FALSE;
				}
			}
			elseif ($filter == 'Answered')
			{
				$faqs = $this->bo->get_faq_list($cat_id,$this->start);
				$total_records = $this->bo->get_count($cat_id);
				$cols='<td height="21"><font size="-1" face="Arial, Helvetica, sans-serif">'.lang('Question').'</font></td>';
				$cols.='<td height="21"><font size="-1" face="Arial, Helvetica, sans-serif">'.lang('Answer').'</font></td>';
				$cols.='<td height="21"><font size="-1" face="Arial, Helvetica, sans-serif">'.lang('Modified').'</font></td>';
				$cols.='<td height="21"><font size="-1" face="Arial, Helvetica, sans-serif">'.lang('Avg. score').'</font></td>';
				$cols.='<td height="21"><font size="-1" face="Arial, Helvetica, sans-serif">'.lang('Actions').'</font></td>';
				$this->t->set_var('cols',$cols);
			}
			elseif ($filter == 'Open')
			{
				$q_unanswered = $this->bo->get_questions(FALSE,$this->start);
				$total_records = $this->bo->get_count_unanswered();
				$cols='<td height="21"><font size="-1" face="Arial, Helvetica, sans-serif">'.lang('Question').'</font></td>';
				$this->t->set_var('cols',$cols);
			}
			
			$this->t->set_var('message',$msg);
			
			$GLOBALS['phpgw']->nextmatchs = CreateObject('phpgwapi.nextmatchs');
			
			$GLOBALS['phpgw']->template->set_var('searchreturn','');
			
			$this->sort=1;
						
			$search_filter = $GLOBALS['phpgw']->nextmatchs->show_tpl(
				'/index.php',																												// $sn
				$this->start, 																												// $localstart
				$total_records,																											// $total
				'&menuaction=phpbrain.uikb.index&cat_id=' . $cat_id,													// $extra
				'97%',																														// $twidth
				$GLOBALS['phpgw_info']['theme']['th_bg'],																	// $bgtheme
				1,																																// $search_obj
				array(array('Answered',lang('Answered')),array('Open',lang('Open'))),							// $filter_obj
				1,																																// $showsearch
				0,																																// $yours
				$cat_id,																													// $cat_id
				'cat_id'																														// $cat_field
			);
			$this->t->set_var('search_filter',$search_filter);
			
			if ($not_empty)
			{
				$lang_showing = $GLOBALS['phpgw']->nextmatchs->show_hits($total_records,$this->start);
			}
			else
			{
				$lang_showing="";
			}
			
			$this->t->set_var('lang_showing', $lang_showing);
			$this->t->pparse('out','phpbrain_header');
			
			/* Show the entries */
			if ($filter=='Answered' && $not_empty && isset($faqs))
			{
				foreach ($faqs as $faq_id => $faq)
				{
					$actions = '<a href="'
						. $GLOBALS['phpgw']->link('/index.php',array(
						'menuaction' => 'phpbrain.uikb.view',
						'faq_id'      => $faq_id
					))
					. '"><img src="'
					. $GLOBALS['phpgw']->common->image('addressbook','view')
					. '" border="0" title="'.lang('View').'"></a> ';
					
					if ($this->bo->is_admin())
					{
						$actions .= '<a href="'
							. $GLOBALS['phpgw']->link('/index.php',array(
							'menuaction' => 'phpbrain.uikb.edit',
							'faq_id'      => $faq_id
						))
						. '"><img src="'
						. $GLOBALS['phpgw']->common->image('addressbook','edit')
						. '" border="0" title="'.lang('Edit').'"></a> '
						
						.'<a href="'
							. $GLOBALS['phpgw']->link('/index.php',array(
							'menuaction' => 'phpbrain.uikb.delete_faq',
							'faq_id'      => $faq_id
						))
						. '"><img src="'
						. $GLOBALS['phpgw']->common->image('addressbook','delete')
						. '" border="0" title="'.lang('Delete').'"></a> ';
					}
					
					$this->t->set_var('columns','');
					$tr_color = $GLOBALS['phpgw']->nextmatchs->alternate_row_color($tr_color);
					$this->t->set_var('row_tr_color',$tr_color);
					
					$this->t->set_var('col_data',$faq['title']);
					$this->t->parse('columns','column',True);
					$this->t->set_var('col_data',$faq['text']);
					$this->t->parse('columns','column',True);
					$this->t->set_var('col_data',date('d-M-Y', $faq["modified"]));
					$this->t->parse('columns','column',True);
					$this->t->set_var('col_data', '<center>' . $faq['vote_avg'] . '</center>');
					$this->t->parse('columns','column',True);
					$this->t->set_var('col_data', '<center>' . $actions . '</center>');
					$this->t->parse('columns','column',True);
					$this->t->parse('rows','row',True);
					$this->t->pparse('out','row');
				}
			}
			elseif ($filter=='Open' && $not_empty && isset($q_unanswered))
			{
				foreach ($q_unanswered as $qu_id => $q_content)
				{
					$this->t->set_var('columns','');
					$tr_color = $GLOBALS['phpgw']->nextmatchs->alternate_row_color($tr_color);
					$this->t->set_var('row_tr_color',$tr_color);
					
					$faq_url='<a href=';
					$faq_url.=$GLOBALS['phpgw']->link('/index.php',array('menuaction' => 'phpbrain.uikb.add', 'question' => $q_content, 'question_id' => $qu_id));
					$faq_url.='>';
					
					$this->t->set_var('col_data',$faq_url.$q_content.'</a>');
					$this->t->parse('columns','column',True);
					$this->t->parse('rows','row',True);
					$this->t->pparse('out','row');
				}
			}
			
			$this->t->pparse('out','phpbrain_footer');			
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
  					'lang_submit_val'	=> lang('add'),
  					'lang_submit_cancel' => lang('cancel')
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
		
		function delete_faq()
		{
			$faq_id = (isset($_GET['faq_id']) ? trim($_GET['faq_id']) : '');
			$this->bo->delete_answer($faq_id);
			header('Location: ' . $GLOBALS['phpgw']->link('/index.php', array('menuaction' => 'phpbrain.uikb.index','msg' => 'FAQ removed successfully')));
		}
		
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
			$add_answer = ($new ? 'add_answer' : 'edit_answer');
			$GLOBALS['phpgw_info']['flags']['app_header'] = lang($GLOBALS['phpgw_info']['flags']['currentapp']) . ' - ' . lang($add_answer);
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

			$this->t->set_var(array(
				'tr_on' => $GLOBALS['phpgw_info']['theme']['row_on'],
				'tr_off' =>$GLOBALS['phpgw_info']['theme']['row_off']
				)
			);
			
			$lang = array(
					'lang_check_before_submit'	=> lang('check_before_submit'),
					'lang_not_submit_qs_warn'	=> lang('not_submit_qs_warn'),
					'lang_inspire_by_suggestions'	=> lang('inspire_by_suggestions'),
					'lang_title'			=> lang('Question'),
					'lang_keywords'			=> lang('keywords'),
					'lang_category'			=> lang('category'),
					'lang_related_url'		=> lang('related_url'),
					'lang_text'				=> lang('text'),
					'lang_submit_cancel'			=> lang('Cancel'),
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
  			$GLOBALS['phpgw_info']['flags']['app_header'] = lang($GLOBALS['phpgw_info']['flags']['currentapp']) . ' - ' . lang('Maintain Answers');
  			echo parse_navbar();
  			
  			$this->t->set_file('admin_maint', 'admin_maint.tpl');
  			$this->t->set_block('admin_maint', 'pending_list', 'pending_items');
  			$this->t->set_block('admin_maint', 'pending_block', 'p_block');
  			$this->t->set_var('admin_url', $GLOBALS['phpgw']->link('/admin/index.php'));
  			$this->t->set_var('lang_return_to_admin', lang('return_to_admin'));
			$this->t->set_var('msg', ((strlen($msg) !=0) ? $msg : '&nbsp;'));

			$faqs = $this->bo->get_faq_list('', 0, true);				
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
  			$GLOBALS['phpgw_info']['flags']['app_header'] = lang($GLOBALS['phpgw_info']['flags']['currentapp']) . ' - ' . lang('Maintain Questions');
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
			if (isset($_POST['cancel']))
			{
				header('Location: ' . $GLOBALS['phpgw']->link('/index.php', 'menuaction=phpbrain.uikb.index'));
				$GLOBALS['phpgw']->common->exit();
			}
			
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
  									array('menuaction'	=> 'phpbrain.uikb.index',
  										'faq_id'		=>  $faq_id,
  										'msg'			=> ($this->bo->is_admin() ? 'Answer added to database' : 'Answer saved. Wait for aproval from the administrator to appear on database' )
  										)
  									)
  					);
  			$GLOBALS['phpgw']->common->phpgw_exit();
			}
		}
		
		function unanswered($msg = '')
		{
			$GLOBALS['phpgw']->common->phpgw_header();
			$GLOBALS['phpgw_info']['flags']['app_header'] = lang($GLOBALS['phpgw_info']['flags']['currentapp']) . ' - ' . lang('Add Question');
			echo parse_navbar();
			$this->t->set_file('unanswered', 'unanswered.tpl');
			$this->t->set_block('unanswered', 'open_list', 'open_ones');
			$this->t->set_block('unanswered', 'open_block', 'o_block');
			$this->t->set_var('th_bg', $GLOBALS['phpgw_info']['theme']['th_bg']);
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
				//echo "<style type=\"text/css\">\n<!--";
				//echo $this->css();
				//echo "\n-->\n</style>
				echo "</head>\n<body>";
			}
			
			$faq_id = (isset($_GET['faq_id']) ? trim($_GET['faq_id']) : 0);
			$search = (isset($_GET['search']) ? trim($_GET['search']) : '');
			$msg = (isset($_GET['msg']) ? trim($_GET['msg']) : '');
			
			$item = $this->bo->get_item($faq_id);
			if(is_array($item) && $faq_id)
			{
  				$this->t->set_file('showitem', 'showitem.tpl');
  				$this->t->set_var(array(
  					'tr_on' => $GLOBALS['phpgw_info']['theme']['row_on'],
					'tr_off' => $GLOBALS['phpgw_info']['theme']['row_off']
					)
				);
  				
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
				$this->t->set_block('comment_form', 'admin_option', 'admin_options');
				if($this->bo->is_admin())
				{
					$temp_link=$GLOBALS['phpgw']->link('/index.php', array('menuaction'	=> 'phpbrain.uikb.edit', 'faq_id'	=> $item['faq_id']));
					$this->t->set_var('edit_button', '<input type=button value="'
									. lang('edit_faq')
									.'" onclick=window.location.href="' . $temp_link . '">');
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
/*			return 'th   {  font-family: '.$this->theme['font'].'; font-size: 10pt; font-weight: bold; background-color: #D3DCE3;} '. "\n".
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
				'';*/
		}
	}	
