<?php
	/**************************************************************************\
	* phpGroupWare - KnowledgeBase                                             *
	* http://www.phpgroupware.org                                              *
	* Written by Dave Hall [skwashd AT phpgroupware DOT org]                   *
	* ------------------------------------------------------------------------ *
	* Started off as a port of phpBrain - http://vrotvrot.com/phpBrain/		 *
	*  but quickly became a full rewrite										 *
	* ------------------------------------------------------------------------ *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/
	
	class sokb
	{
		var $db;
		
		function sokb()
		{
			$this->db = $GLOBALS['phpgw']->db;
		}

		function delete_answer($faq_ids)
		{
			$i=0;
			foreach($faq_ids as $key => $val)
			{
				$this->db->query("DELETE FROM phpgw_kb_faq WHERE faq_id = $key");
				$i++;
			}
			return $i;
		}//end set_active_answer

		function delete_question($question_ids)
		{
			if(is_array($question_ids))
			{
				$i=0;
  			foreach($question_ids as $key => $val)
  			{
  				$this->db->query("DELETE FROM phpgw_kb_questions WHERE question_id = $key");
  				$i++;
  			}//end foreach(q_id)
			}
			elseif(is_int($question_ids))
			{
				$this->db->query("DELETE FROM phpgw_kb_questions WHERE question_id = $question_ids");
				$i = 1;
			}//end is_type
			return $i;
		}//end set_active_answer

		function get_stats()
		{	
			$stats = array();
    	/* how many faqs*/
    	$this->db->query('SELECT COUNT(*) FROM phpgw_kb_faq WHERE published = 1 AND is_faq = 0', __LINE__, __FILE__);
    	$this->db->next_record();
    	$stats['num_faqs'] = $this->db->f(0);
    
    	/* how many tutorials? */
    	$this->db->query('SELECT COUNT(*) FROM phpgw_kb_faq WHERE published = 1 AND is_faq = 1', __LINE__, __FILE__);
    	$this->db->next_record();
    	$stats['num_tutes'] = $this->db->f(0);
    
    	/* how many open questions? */
    	$this->db->query('SELECT COUNT(*) FROM phpgw_kb_questions WHERE pending = 0', __LINE__, __FILE__);
			$this->db->next_record();
    	$stats['num_open'] = $this->db->f(0);
			
			return $stats;
		}
		
		function get_latest()
		{
    	/* latest questions */
    	$this->db->limit_query('SELECT * FROM phpgw_kb_questions WHERE pending = 0 ORDER BY question_id DESC', 0, __LINE__, __FILE__, 3);

			$questions = array();
    	while($this->db->next_record())
			{
    		$questions[$this->db->f('question_id')] = $this->db->f('question', true);
    	}
			return $questions;
  	}//end get latest
		
		function get_faq_list($cat_id = '', $unpublished = false)
		{
    	$where  = ((strlen($cat_id) != 0) ? "cat_id = $cat_id " : '');
			$where .= ((strlen($where) > 0) ? 'AND ' : '');
			$where .= ($unpublished ? 'published = 0' : 'published = 1'); 
			$this->db->query("SELECT * FROM phpgw_kb_faq WHERE $where", __LINE__, __FILE__);
			while($this->db->next_record())
			{
				$faqs[$this->db->f('faq_id')] = array('title' 	=> $this->db->f('title', true),
            								'text'		=> substr($this->db->f('text', true),0,50) . ' ...',
            								'modified'	=> $this->db->f('modified'),
            								'views'		=> $this->db->f('views'),
            								'votes'		=> $this->db->f('votes'),
            								'total'		=> $this->db->f('total')
												);
			}
			return $faqs;
		}
		
		function get_item($faq_id)
		{
			$this->db->query("SELECT * FROM phpgw_kb_faq WHERE faq_id = $faq_id", __LINE__, __FILE__);
			if($this->db->next_record())
			{
				$item = array('faq_id'		=> $this->db->f('faq_id'),
							'title'			=> $this->db->f('title', true),
							'text'			=> $this->db->f('text', true),
							'cat_id'		=> $this->db->f('cat_id', true),
							'published'		=> $this->db->f('published'),
							'keywords'		=> $this->db->f('keywords', true),
							'user_id'		=> $this->db->f('user_id'),
							'views'			=> $this->db->f('views'),
							'modified'		=> $this->db->f('modified'),
							'type'			=> $this->db->f('type'),
							'url'			=> $this->db->f('url', true),
							'votes'			=> $this->db->f('votes'),
							'total'			=> $this->db->f('total')
							);

				$this->set_view($this->db->f('faq_id'));
			}
			return $item;
		}
		
		function get_comments($faq_id)
		{
			$this->db->query("SELECT * FROM phpgw_kb_comment WHERE faq_id = $faq_id", __LINE__, __FILE__);
			while($this->db->next_record())
			{
				$comment[$this->db->f('comment_id')] = array('user_id'	=> $this->db->f('user_id'),
                  								'comment_text'			=> $this->db->f('comment', true),
                  								'entered'			=> $this->db->f('entered')
													);
 			}
			return $comment;
		}

    function get_count($cat_id)
    {
    	$this->db->query("SELECT COUNT(*) FROM phpgw_kb_faq WHERE cat_id = $cat_id AND published = 1", __LINE__, __FILE__);
			if($this->db->next_record())
			{
				return $this->db->f(0); 
			}
			else
			{
				return 0;
			}
    }//end get count
		
		function get_pending()
		{
			$this->db->query('SELECT faq_id, text FROM phpgw_kb_faq WHERE published = 0');
			while($this->db->next_record())
			{
				$faq[$this->db->f('faq_id')] = $this->db->f('text', true); 
			}
			return $faq;
		}//end get pending

		
		function get_search_results($search, $show = null)
		{
			switch (trim($GLOBALS['phpgw_info']['server']['db_type']))
			{
				case 'mysql':
					$ver = explode('-', mysql_get_server_info());
					$ver = $ver[0];
					if($GLOBALS['phpgw']->common->cmp_version_long($ver, '3.23.23') <= 1)
					{
						return $this->search_mysql($search, $show);
					}
					else
					{
						return $this->search_ansisql($search, $show);
					}
					break;
				//case 'pgsql': - //future use
				//case 'mssql': - //future use
				default:
					return $this->search_ansisql($search, $show);
			}//end case db
				
		}

		function get_questions($pending = false)
		{
			$where = ($pending ? 'pending = 1' : 'pending = 0');
			$this->db->query("SELECT * FROM phpgw_kb_questions WHERE $where", __LINE__, __FILE__);
			while($this->db->next_record())
			{
				$open_q[$this->db->f('question_id')] = $this->db->f('question', true); 
			}
			return $open_q;
		}
		
		function save($faq_id, $faq, $admin)
		{
			if(is_int($faq_id) && is_array($faq))
			{
				if($faq_id)//is new?
				{
					$sql  =  'UPDATE phpgw_kb_faq';
					$sql .= ' SET cat_id = ' . $faq['cat_id'] . ',';
					$sql .= ' title = "' . $this->db->db_addslashes($faq['title']) . '",';
					$sql .= ' keyword = "' . $this->db->db_addslashes($faq['keyword']) . '",';
					$sql .= ' text = "' . $this->db->db_addslashes($faq['text']) . '",';
					$sql .= ' modified = ' . time() .',';
					$sql .= ' user_id = ' . $faq['user_id'] .',';
					$sql .= ' published = ' . ($admin ? 1 : 0) . ', ';
					$sql .= ' is_faq = ' . $faq['is_faq'];
					$sql .= " WHERE faq_id = $faq_id";
					$this->db->query($sql);
					if($this->db->affected_rows() == 1)
					{
						return $faq_id;
					}
					else//some went wrong
					{
						return false;
					} 
				}
				else//must be new
				{
					$sql  = 'INSERT INTO phpgw_kb_faq (title, text, cat_id, published, keywords, user_id, views, modified, is_faq, url) ';
					$sql .= "VALUES('" . $this->db->db_addslashes($faq['title']) . "', ";
					$sql .= "'" . $this->db->db_addslashes($faq['text']) . "', ";
					$sql .= $faq['cat_id'] . ", ";
					$sql .= "1, '" . $this->db->db_addslashes($faq['keywords']) . "',";
					$sql .= $faq['user_id'] . ', ';
					$sql .= '0, ' . time() . ',  ' . $faq['is_faq'] . ", '')";//url is empty for now
					$this->db->query($sql);
					return $this->db->get_last_insert_id('phpgw_kb_faq', 'faq_id');
				}//end is new
			}//end if is valid
		}//end save
		
		function set_active_answer($faq_ids)
		{
			$i=0;
			foreach($faq_ids as $key => $val)
			{
				$this->db->query("UPDATE phpgw_kb_faqs SET published = 1 WHERE faq_id = $key");
				$i++;
			}
			return $i;
		}//end set_active_answer
		
		//generic 
		function search_ansisql($search, $show)
		{
			$select  = 'SELECT * FROM phpgw_kb_faq ';
			$select .= 'WHERE published = 1 ';
			if(is_int($show))
			{
				$select .= "AND is_faq = $show ";
			}
			$search_words = expode(' ', $search);
			$cycle = 0;
			foreach($search_words as $id => $word)
			{
				if($cycle)
				{
					$title .= "OR title LIKE '%" . $this->db->db_addslashes($word) . "%' ";
					$keywords .= "OR keywords LIKE '%" . $this->db->db_addslashes($word) . "%' ";
					$text .= "OR text LIKE '%" . $this->db->db_addslashes($word) . "%' ";
				}
				else
				{
					$title .= "(title LIKE '%" . $this->db->db_addslashes($word) . "%' ";
					$keywords .= "(keywords LIKE '%" . $this->db->db_addslashes($word) . "%' ";
					$text .= "(text LIKE '%" . $this->db->db_addslashes($word) . "%' ";
				}
			}
			$title .= ") ";
			$keywords .= ") ";
			$text .= ") ";
			
			$sql = $select . 'AND' . $title . 'OR' . $keywords . 'OR' . $text;
			$this->db->query($sql);
			while($this->db->next_record())
			{
				$rows[$this->db->f('faq_id')] = $this->db->Record;
				$rows[$this->db->f('faq_id')]['score'] = 0.00;
			}
			return $rows;
		}//end search ansisql

		function search_mysql($search)
		{
			$sql  = 'SELECT *, ';
			$sql .= "MATCH text,keywords,title AGAINST('" . addslashes($search) ."') AS score ";
			$sql .= 'FROM phpgw_kb_faq ';
			$sql .= 'WHERE published = 1 ';
			if(is_int($show))
			{
				$sql .= "AND is_faq = $show ";
			}
			//$sql .= 'HAVING (score > 0) '; //- this isn't working properly afaik
			$sql .= 'ORDER BY score DESC';
			$this->db->query($sql);
			while($this->db->next_record())
			{
				$rows[$this->db->f('faq_id')] = $this->db->Record;
			}
			return $rows;
		}//end search mysql
		
		function set_rating($faq_id, $rating)
		{
			$this->db->query("UPDATE phpgw_kb_faq "
							."SET votes=votes+1, total=total+$rating "
							."WHERE faq_id=$faq_id",__LINE__, __FILE__
							);
		}//end set rating
		
		function set_question($question, $admin)
		{
			$sql  = 'INSERT INTO phpgw_kb_questions(question, pending) ';
			$sql .= 'VALUES("' . $this->db->db_addslashes($question) .'", ';
			$sql .= ($admin ? 0 : 1) .')';
			$this->db->query($sql, __LINE__, __FILE__);
			if($this->db->get_last_insert_id('phpgw_kb_questions', ' question_id'))//worked
			{
				return true;
			}
			else//must have failed
			{
				return false;
			}//end if worked
		}//end set question

		function set_view($faq_id)
		{
			$this->db->query("UPDATE phpgw_kb_faq "
							."SET views=views+1 "
							."WHERE faq_id=$faq_id",__LINE__, __FILE__
							);
		}
		
	}
?>
