<?php
/**
 * eGroupWare Knowledgebase - storage object
 *
 * Started off as a port of phpBrain - http://vrotvrot.com/phpBrain/ but quickly became a full rewrite
 *
 * @link http://www.egroupware.org
 * @author Alejandro Pedraza <alpeb(at)users.sourceforge.net>
 * @package phpbrain
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

/**
* Data manipulation layer of the Knowledge Base. Methods to be used only  by methods in the bo class.
*
* Last Editor:	$Author$
*/
class sokb
{
	/**
	* Database object
	*
	* @access	private
	* @var		egw_db
	*/
	var $db;

	/**
	* Number of rows in result set
	*
	* @access	public
	* @var		int
	*/
	var $num_rows;

	/**
	* Number of unanswered questions in result set
	*
	* @access	public
	* @var		int
	*/
	var $num_questions;

	/**
	* Number of comments in result set
	*
	* @access	public
	* @var		int
	*/
	var $num_comments;

	/**
	* Type of LIKE SQL operator to use
	*
	* @access	private
	* @var		string
	*/
	var $like;

	/**
	* Class constructor
	*
	* @author	Alejandro Pedraza
	* @access	public
	**/
	function sokb()
	{
		$this->db	= clone($GLOBALS['egw']->db);
		$this->db->set_app('phpbrain');

		$this->like = $this->db->capabilities['case_insensitive_like'];
	}

	/**
	* Returns array of articles
	*
	* @author	Alejandro Pedraza
	* @access	public
	* @param	array	$owners			Users ids accessible by current user
	* @param	array	$categories		Categories ids
	* @param	int		$start			For pagination
	* @param	int		$upper_limit	For pagination
	* @param	srting	$sort			Sorting direction: ASC | DESC
	* @param	string	$order			Sorting field name
	* @param	mixed	$publish_filter	To filter pusblished or unpublished entries
	* @param	string	$query			Search string
	* @return	array					Articles
	*/
	function search_articles($owners, $categories, $start, $upper_limit = '', $sort, $order, $publish_filter = False, $query)
	{
		$where = array(
			'user_id' => $owners,
			'cat_id'  => !$categories ? 0 : $categories,
		);
		if ($publish_filter && $publish_filter != 'all')
		{
			$where['published'] = (int) ($publish_filter == 'published');
		}
		$fields = '*';

		if ($query)
		{
			$words = $likes = $scores = array();
			foreach (explode(' ',$query) as $word)
			{
				$scores[] = 'keyword='.$this->db->quote($word);

				if ((int)$word)
				{
					$likes[] = 'egw_kb_articles.art_id='.(int)$word;
					continue;	// numbers are only searched as article-id
				}
				foreach(array('title','topic','text') as $col)
				{
					$likes[] = $col.' '.$this->like.' '.$this->db->quote('%'.$word.'%');
				}
			}
			$score = 'SELECT sum(score) FROM egw_kb_search WHERE art_id=egw_kb_articles.art_id AND ('.implode(' OR ',$scores).')';
			$fields .= ",($score) AS pertinence";

			$where[] = "(($score) > 0 OR ".implode(' OR ',$likes).')';
		}
		$order_sql = array();
		if (preg_match('/^[a-z_0-9]+$/',$order))
		{
			$order_sql[] = $order.' '.($sort != 'DESC' ? 'ASC' : 'DESC');
		}
		if ($query)
		{
			$order_sql[] = "pertinence DESC";
		}
		if (!$order && !$query)
		{
			$order_sql[] = "modified DESC";
		}
		$order_sql = ' ORDER BY ' . implode(',', $order_sql);

		$this->db->select('egw_kb_articles','COUNT(*)',$where,__LINE__,__FILE__);
		$this->num_rows = $this->db->next_record() ? $this->db->f(0) : 0;

		$this->db->select('egw_kb_articles',$fields,$where,__LINE__,__FILE__,$start,$order_sql,False,($upper_limit?$upper_limit:0));
		$this->db->query($sql, __LINE__, __FILE__,$start,($upper_limit?$upper_limit:0));

		return $this->results_to_array($dummy);
	}

	/**
	* Returns results of advanced search
	*
	* @author	Alejandro Pedraza
	* @access	public
	* @param	array	$owners			User ids accessible by current user
	* @param	array	$cats_ids		Categories filtering
	* @param	string	$ocurrences		Field name where to search
	* @param	string	$pub_date		Articles from last 3 or 6 months, or last year
	* @param	int		$start			For pagination
	* @param	int		$num_res		For pagination
	* @param	string	$all_words		'with all the words' filtering
	* @param	string	$phrase			'exact phrase' filtering
	* @param	string	$one_word		'with at least one of the words' filtering
	* @param	string	$without_words	'without the words' filtering
	* @param	int		$cat			Don't know
	* @param	bool	$include_subs	Include subcategories when filtering by categories. Seems to not being working
	* @return	array					Articles
	* @todo		use params $cat and $include_subs
	*/
	function adv_search_articles($owners, $cats_ids, $ocurrences, $pub_date, $start, $num_res, $all_words, $phrase, $one_word, $without_words, $cat, $include_subs)
	{
		$fields= array('egw_kb_articles.art_id', 'title', 'topic', 'views', 'cat_id', 'published', 'user_id', 'created', 'modified', 'votes_1', 'votes_2',  'votes_3', 'votes_4', 'votes_5');
		$fields_str	= implode(' , ', $fields);

		// permissions filtering
		$owners	= implode(', ', $owners);
		$sql = "SELECT DISTINCT $fields_str FROM egw_kb_articles LEFT JOIN egw_kb_search ON egw_kb_articles.art_id=egw_kb_search.art_id WHERE user_id IN ($owners)";

		// categories filtering
		$cats_ids	= implode (',', $cats_ids);
		if ($cats_ids) $sql .= " AND cat_id IN ($cats_ids)";

		// date filtering
		switch ($pub_date)
		{
			case '3':
				$sql .= " AND created>" . mktime(0, 0, 0, date('n')-3);
				break;
			case '6':
				$sql .= " AND created>" . mktime(0, 0, 0, date('n')-6);
				break;
			case 'year':
				$sql .= " AND created>" . mktime(0, 0, 0, date('n')-12);
				break;
		}

		// ocurrences filtering
		switch ($ocurrences)
		{
			case 'title':
				$target_fields = array('title');
				break;
			case 'topic':
				$target_fields = array('topic');
				break;
			case 'text':
				$target_fields = array('text');
				break;
			default:
				$target_fields = array('title', 'topic', 'keyword', 'text');
				break;
		}

		// "with all the words" filtering
		$all_words = $this->db->db_addslashes($all_words);
		$all_words = strlen($all_words)? explode(' ', $all_words) : False;
		$each_field = array();
		if ($all_words)
		{
			foreach ($all_words as $word)
			{
				$each_field[] = "(" . implode(" {$this->like} '%$word%' OR ", $target_fields) . " {$this->like} '%$word%')";
			}
			if ($each_field)
			{
				$sql .= " AND " . implode(" AND ", $each_field);
			}
		}

		// "with the exact phrase" filtering
		$phrase = $this->db->db_addslashes($phrase);
		if ($phrase)
		{
			$sql .= " AND (" . implode (" {$this->like} '%$phrase%' OR ", $target_fields) . " {$this->like} '%$phrase%')";
		}

		// "With at least one of the words" filtering
		$one_word = $this->db->db_addslashes($one_word);
		$one_word = strlen($one_word)? explode(' ', $one_word) : False;
		if ($one_word)
		{
			$each_field = array();
			foreach ($one_word as $word)
			{
				$each_field[] = "(" . implode(" {$this->like} '%$word' OR ", $target_fields) . " {$this->like} '%$word%')";
			}
			$sql .= " AND (". implode (" OR ", $each_field) . ")";
		}

		// "Without the words" filtering
		$without_words = $this->db->db_addslashes($without_words);
		$without_words = strlen($without_words)? explode(' ', $without_words) : False;
		$each_field = array();
		if ($without_words)
		{
			foreach ($without_words as $word)
			{
				$each_field[] = "(" . implode(" NOT {$this->like} '%word' AND ", $target_fields) . " NOT {$this->like} '%$word%')";
			}
			$sql .= " AND " . implode(" AND ", $each_field);
		}

		// do the query
		//echo "query: $sql <br>";
		$this->db->query($sql, __LINE__, __FILE__);
		$this->num_rows = $this->db->num_rows();
		$this->db->limit_query($sql, $start, __LINE__, __FILE__, $num_res);
		return $this->results_to_array($fields);
	}

	/**
	* Fetches results from database and returns array of articles
	*
	* @author	Alejandro Pedraza
	* @access 	private
	* @param	array	$fields	Which fields to fetch
	* @return	array	Articles
	*/
	function results_to_array($fields)
	{
		$articles = array();
		while(($article = $this->db->row(true)))
		{
			$article['username'] = $GLOBALS['egw']->common->grab_owner_name($article['user_id']);
			$article['total_votes'] = $article['votes_1'] + $article['votes_2'] + $article['votes_3'] + $article['votes_4'] + $article['votes_5'];
			if ($article['total_votes'])
			{
				$article['average_votes'] = (1*$article['votes_1'] + 2*$article['votes_2'] + 3*$article['votes_3'] + 4*$article['votes_4'] + 5*$article['votes_5']) / ($article['total_votes']);
			}
			else
			{
				$article['average_votes'] = 0;	// avoid division by zero
			}
			$articles[] = $article;
		}
		return $articles;
	}

	/**
	* Upgrades the keywords in the egw_kb_search table
	*
	* @author	Ralf Becker
	* @access	public
	* @param	int		$art_id	Article ID
	* @param	string	$words			all Keyword(s)
	* @param	mixed	$upgrade_key	Whether to give more or less score to $word
	* @return	void
	*/
	function update_keywords($art_id, $words, $upgrade_key)
	{
		$words = array_diff(explode(' ',$words),array(''));

		// delete all existing and NOT longer mentioned keywords
		$this->db->delete('egw_kb_search',!$words ? array('art_id' => $art_id) :
			$this->db->expression('egw_kb_search',array('art_id' => $art_id),' AND NOT ',array('keyword'=>$words)),
			__LINE__,__FILE__);

		foreach($words as $word)
		{
			$this->update_keyword($art_id,$word,$upgrade_key);
		}
	}

	/**
	* Upgrades egw_kb_search table given user input
	*
	* @author	Alejandro Pedraza
	* @access	public
	* @param	int		$art_id	Article ID
	* @param	string	$word			Keyword
	* @param	mixed	$upgrade_key	Whether to give more or less score to $word
	* @return	void
	*/
	function update_keyword($art_id, $word, $upgrade_key)
	{
		$word = $this->db->db_addslashes(substr($word, 0, 30));

		// retrieve current score
		$sql = "SELECT score FROM egw_kb_search WHERE keyword='$word' AND art_id=$art_id";
		$this->db->query($sql, __LINE__, __FILE__);
		$keyword_exists = $this->db->next_record();
		if ($keyword_exists && $upgrade_key != 'same')
		{
			// upgrade score
			$old_score = $this->db->f('score');
			$new_score = $upgrade_key ? $old_score + 1 : $old_score - 1;
			$sql = "UPDATE egw_kb_search SET score=$new_score WHERE keyword='$word' AND art_id=$art_id";
			$this->db->query($sql, __LINE__, __FILE__);
		}
		elseif (!$keyword_exists || $upgrade_key != 'same')
		{
			// create new entry for word
			$sql = "INSERT INTO egw_kb_search (keyword, art_id, score) VALUES('$word', $art_id, 1)";
			$this->db->query($sql, __LINE__, __FILE__);
		}
	}

	/**
	* Returns unanswered questions
	*
	* @author	Alejandro Pedraza
	* @access	public
	* @param	array	$owners			User ids accessible by current user
	* @param	array	$categories		Categories ids
	* @param	int		$start			For pagination
	* @param	int		$upper_limit	For pagination
	* @param	srting	$sort			Sorting direction: ASC | DESC
	* @param	string	$order			Sorting field name
	* @param	mixed	$publish_filter	To filter pusblished or unpublished entries
	* @param	string	$query			Search string
	* @return	array					Questions
	*/
	function unanswered_questions($owners, $categories, $start, $upper_limit='', $sort, $order, $publish_filter=False, $query)
	{
		$fields = array('question_id', 'user_id', 'summary', 'details', 'cat_id', 'creation', 'published');
		$fields_str = implode(', ', $fields);
		$owners = implode(', ', $owners);
		$sql = "SELECT $fields_str FROM egw_kb_questions WHERE user_id IN ($owners)";
		if ($publish_filter && $publish_filter!='all')
		{
			($publish_filter == 'published')? $publish_filter = 1 : $publish_filter = 0;
			$sql .= " AND published=$publish_filter";
		}
		if (!$categories)
		{
			$sql .= " AND cat_id = 0";
		}
		else
		{
			$categories = implode(",", $categories);
			$sql .= " AND cat_id IN(" . $categories . ")";
		}
		if ($query)
		{
			$query = $this->db->db_addslashes($query);
			$words = explode(' ', $query);
			$sql .= " AND (summary {$this->like} '%" . implode("%' OR summary {$this->like} '%", $words) . "%' OR details {$this->like} '%" . implode("%' OR details {$this->like} '%", $words) . "%')";
		}
		if ($order)
		{
			$sql .= " ORDER BY $order $sort";
		}
		//echo "sql: $sql <br><br>";
		$this->db->query($sql, __LINE__, __FILE__);
		$this->num_rows = $this->db->num_rows();
		$this->num_questions = $this->num_rows;
		$this->db->limit_query($sql, $start, __LINE__, __FILE__, $upper_limit);
		$questions = array();
		for ($i=0; $this->db->next_record(); $i++)
		{
			foreach ($fields as $field)
			{
				$questions[$i][$field] = $this->db->f($field);
			}
			$username = $GLOBALS['egw']->accounts->get_account_name($questions[$i]['user_id'], $lid, $fname, $lname);
			$questions[$i]['username'] = $fname . ' ' . $lname;
		}
		return $questions;
	}

	/**
	* Saves a new or edited article
	*
	* @author	Alejandro Pedraza
	* @access	public
	* @param	array	$contents	article contents
	* @param	bool	$is_new		True if it's a new article, False if its an edition
	* @param	bool	$publish	True if the article is to be published without revision
	* @return	mixed				article id or False if failure
	**/
	function save_article($contents, $is_new, $publish = False)
	{
		$current_time = time();
		if ($is_new)
		{
			($publish)? $publish = 1 : $publish = 0;
			$q_id = $contents['answering_question']? $contents['answering_question'] : 0;
			$sql = "INSERT INTO egw_kb_articles (q_id, title, topic, text, cat_id, published, user_id, created, modified, modified_user_id, votes_1, votes_2, votes_3, votes_4, votes_5) VALUES ("
					. "$q_id, '"
					. $this->db->db_addslashes($contents['title']) . "', '"
					. $this->db->db_addslashes($contents['topic']) . "', '"
					. $this->db->db_addslashes($contents['text']) . "', "
					. (int) $contents['cat_id'] . ", "
					. $publish . ", "
					. $GLOBALS['egw_info']['user']['account_id'] . ", "
					. $current_time . ", " . $current_time . ", "
					. $GLOBALS['egw_info']['user']['account_id'] . ", "
					. " 0, 0, 0, 0, 0)";
			$this->db->query($sql, __LINE__, __FILE__);
			$article_id = $this->db->get_last_insert_id('egw_kb_articles', 'art_id');

			// update table egw_kb_search with keywords. Even if no keywords were introduced, generate an entry
			$this->update_keywords($article_id, $contents['keywords'], 'same');

			// if publication is automatic and the article answers a question, delete the question
			if ($publish && $contents['answering_question'])
			{
				$sql = "DELETE FROM egw_kb_questions WHERE question_id=$q_id";
				$this->db->query($sql, __LINE__, __FILE__);
			}

			return $article_id;
		}
		else
		{
			$sql = "UPDATE egw_kb_articles SET "
					." title='" . $this->db->db_addslashes($contents['title'])
					."', topic='" . $this->db->db_addslashes($contents['topic'])
					."', text='" . $this->db->db_addslashes($contents['text'])
					."', cat_id='" . (int)($contents['cat_id'])
					."', modified=" . $current_time
					.", modified_user_id=" . $GLOBALS['egw_info']['user']['account_id']
					." WHERE art_id=" . $contents['editing_article_id'];
			$this->db->query($sql, __LINE__, __FILE__);
			$queries_ok = false;
			if ($this->db->affected_rows()) $queries_ok = true;

			// update keywords
			$this->update_keywords($contents['editing_article_id'],$contents['keywords'], True, False);

			if ($queries_ok)
			{
				return $contents['editing_article_id'];
			}
			else
			{
				return False;
			}
		}
	}

	/**
	* Changes article owner when user is deleted
	*
	* @author	Alejandro Pedraza
	* @access	public
	* @param	int	$owner		actual owner
	* @param	int	$new_owner	new owner
	* @return	void
	**/
	function change_articles_owner($owner, $new_owner)
	{
		$sql = "UPDATE egw_kb_articles SET user_id='$new_owner' WHERE user_id='$owner'";
		$this->db->query($sql, __LINE__, __FILE__);
	}

	/**
	* Deletes article
	*
	* @author	Alejandro Pedraza
	* @access	public
	* @param	int		$art_id		article id
	* @return	bool	1 on success, 0 on failure
	**/
	function delete_article($art_id)
	{
		$sql = "DELETE FROM egw_kb_articles WHERE art_id=$art_id";
		if (!$this->db->query($sql, __LINE__, __FILE__)) return 0;
		return 1;
	}

	/**
	* Deletes question
	*
	* @author	Alejandro Pedraza
	* @access	public
	* @param	int		$q_id		Question id
	* @return	bool	1 on success, 0 on failure
	**/
	function delete_question($q_id)
	{
		$sql = "DELETE FROM egw_kb_questions WHERE question_id=$q_id";
		if (!$this->db->query($sql, __LINE__, __FILE__)) return 0;
		return 1;
	}

	/**
	* Returns latest articles entered
	*
	* @author	Alejandro Pedraza
	* @access	public
	* @param	int	$parent_cat	Category id
	* @return	array	Articles
	*/
	function get_latest_articles($parent_cat)
	{
		$sql = "SELECT art_id, title, topic, text, modified, votes_1, votes_2, votes_3, votes_4, votes_5 FROM egw_kb_articles";
		$this->db->query($sql, __LINE__, __FILE__);

		$articles = array();
		while ($this->db->next_record())
		{
			$rating = 1*$this->db->f('votes_1') + 2*$this->db->f('votes_2') + 3*$this->db->f('votes_3') + 4*$this->db->f('votes_4') + 5*$this->db->f('votes_5');
			$articles[$this->db->f('art_id')] = array(
				'title'		=> $this->db->f('title'),
				'topic'		=> $this->db->f('topic'),
				'text'		=> $this->db->f('text'),
				'modified'	=> $this->db->f('modified'),
				'rating'	=> $rating
			);
		}

		return $articles;
	}

	/**
	* Returns article
	*
	* @author	Alejandro Pedraza
	* @access	public
	* @param	int		$art_id		article id
	* @return	array	Article
	**/
	function get_article($art_id)
	{
		$fields = array('art_id', 'title', 'topic', 'text', 'views', 'cat_id', 'published', 'user_id', 'created', 'modified', 'modified_user_id', 'votes_1', 'votes_2', 'votes_3', 'votes_4', 'votes_5');
		$fields_str = implode(", ", $fields);

		$sql =	"SELECT $fields_str FROM egw_kb_articles WHERE art_id=$art_id";
		//echo "sql: $sql <br>";
		$this->db->query($sql, __LINE__, __FILE__);
		$article = array();
		if (!$this->db->next_record()) return 0;
		foreach ($fields as $field)
		{
			$article[$field] = $this->db->f($field);
		}

		// get article's attached urls
		$this->db->query("SELECT art_url, art_url_title FROM egw_kb_urls WHERE art_id=$art_id", __LINE__, __FILE__);
		$article['urls'] = array();
		$i = 0;
		while ($this->db->next_record())
		{
			$article['urls'][$i]['link'] = $this->db->f('art_url');
			$article['urls'][$i]['title'] = $this->db->f('art_url_title');
			$i++;
		}

		// get article's keywords
		$this->db->query("SELECT keyword FROM egw_kb_search WHERE art_id=$art_id", __LINE__, __FILE__);
		$article['keywords'] = array();
		while ($this->db->next_record())
		{
			$article['keywords'][] = $this->db->f('keyword');
		}
		$article['keywords'] = implode(' ', $article['keywords']);

		// normalize vote frequence to the range 0 - 40
		$votes = array();
		$article['total_votes'] = $article['votes_1'] + $article['votes_2'] + $article['votes_3'] + $article['votes_4'] + $article['votes_5'];
		if ($article['total_votes'])
		{
			$article['average_votes'] = ($article['votes_1'] + 2*$article['votes_2'] + 3*$article['votes_3'] + 4*$article['votes_4'] + 5*$article['votes_5']) / $article['total_votes'];
		}
		else
		{
			$article['average_votes'] = 0;
		}

		return $article;
	}

	/**
	* Returns all articles ids from a given owner
	*
	* @author	Alejandro Pedraza
	* @access	public
	* @param	int		$owner		owner id
	* @return	array	Articles ids
	**/
	function get_articles_ids($owner)
	{
		$sql = "SELECT art_id FROM egw_kb_articles WHERE user_id=$owner";
		$this->db->query($sql, __LINE__, __FILE__);
		$articles_ids = array();
		while ($this->db->next_record())
		{
			$articles_ids[] = $this->db->f('art_id');
		}
		return $articles_ids;
	}

	/**
	* Increments the view count of a published article
	*
	* @author	Alejandro Pedraza
	* @param	int	$art_id			article id
	* @param	int	$current_count	current view count
	* @return	void
	**/
	function register_view($art_id, $current_count)
	{
		$current_count ++;
		$sql = "UPDATE egw_kb_articles SET views=$current_count WHERE art_id=$art_id";
		$this->db->query($sql, __LINE__, __FILE__);
	}

	/**
	* Returns article's comments
	*
	* @author	Alejandro Pedraza
	* @param	int		$art_id		article id
	* @param	int		$limit		Number of comments to return
	* @return	array				Comments
	*/
	function get_comments($art_id, $limit)
	{
		$fields = array('comment_id', 'user_id', 'kb_comment', 'entered', 'art_id', 'published');
		$fields_str = implode(", ", $fields);
		$sql = "SELECT " . $fields_str . " FROM egw_kb_comment WHERE art_id=$art_id ORDER BY entered DESC";
		$this->db->query($sql, __LINE__, __FILE__);
		$this->num_comments = $this->db->num_rows();
		if ($limit)
		{
			$this->db->limit_query($sql, 0, __LINE__, __FILE__, $limit);
		}
		$comments = array();
		for ($i=0; $this->db->next_record(); $i++)
		{
			foreach ($fields as $field)
			{
				$comments[$i][$field] = $this->db->f($field);
			}
			$GLOBALS['egw']->accounts->get_account_name($comments[$i]['user_id'], $lid, $fname, $lname);
			$comments[$i]['username'] = $fname . ' ' . $lname;
		}
		return $comments;
	}

	/**
	* Delete article's comments
	*
	* @author	Alejandro Pedraza
	* @access	public
	* @param	int		$art_id		article id
	* @return	void
	*/
	function delete_comments($art_id)
	{
		$sql = "DELETE FROM egw_kb_comment WHERE art_id=$art_id";
		$this->db->query($sql, __LINE__, __FILE__);
	}

	/**
	* Delete article's ratings
	*
	* @author	Alejandro Pedraza
	* @access	public
	* @param	int		$art_id		article id
	* @return	void
	*/
	function delete_ratings($art_id)
	{
		$sql = "DELETE FROM egw_kb_ratings WHERE art_id=$art_id";
		$this->db->query($sql, __LINE__, __FILE__);
	}

	/**
	* Delete article's urls
	*
	* @author	Alejandro Pedraza
	* @access	public
	* @param	int		$art_id		article id
	* @return	void
	*/
	function delete_urls($art_id)
	{
		$sql = "DELETE FROM egw_kb_urls WHERE art_id=$art_id";
		$this->db->query($sql, __LINE__, __FILE__);
	}

	/**
	* Returns an article related comments
	*
	* @author	Alejandro Pedraza
	* @access	public
	* @param	int		$art_id	Article id
	* @param	array	$owners	Accessible owners to current user
	* @return	array	IDs and titles of articles
	*/
	function get_related_articles($art_id, $owners)
	{
		$owners = implode(', ', $owners);
		$sql = "SELECT egw_kb_articles.art_id, egw_kb_articles.title FROM egw_kb_related_art, egw_kb_articles WHERE egw_kb_related_art.related_art_id=egw_kb_articles.art_id AND egw_kb_related_art.art_id=$art_id AND egw_kb_articles.user_id IN ($owners)";
		$this->db->query($sql, __LINE__, __FILE__);
		$related = array();
		while ($this->db->next_record())
		{
			$related[] = array('art_id' => $this->db->f('art_id'), 'title' => $this->db->f('title'));
		}
		return $related;
	}

	/**
	* Tells if the current user has already rated the article
	*
	* @author	Alejandro Pedraza
	* @access	public
	* @param	int		$art_id		article id
	* @return	bool				1 if he has, 0 if not
	**/
	function user_has_voted($art_id)
	{
		$sql = "SELECT * FROM egw_kb_ratings WHERE user_id=" . $GLOBALS['egw_info']['user']['account_id'] . " AND art_id=$art_id";
		$this->db->query($sql, __LINE__, __FILE__);
		if ($this->db->next_record()) return 1;
		return 0;
	}

	/**
	* Stores new comment
	*
	* @author	Alejandro Pedraza
	* @access	public
	* @param	string	$comment	comment text
	* @param	int		$art_id		article id
	* @param	bool	$publish	True if comment is to be published, False if not
	* @return	bool				1 on success, 0 on failure
	**/
	function add_comment($comment, $art_id, $publish)
	{
		$comment = $this->db->db_addslashes($comment);
		($publish)? $publish = 1 : $publish = 0;
		$sql = "INSERT INTO egw_kb_comment (user_id, kb_comment, entered, art_id, published) VALUES("
				. $GLOBALS['egw_info']['user']['account_id'] . ", '$comment', " . time() . ", $art_id, $publish)";
		$this->db->query($sql, __LINE__, __FILE__);
		if (!$this->db->affected_rows()) return 0;
		return 1;
	}

	/**
	* Adds link to article
	*
	* @author	Alejandro Pedraza
	* @access	public
	* @param	string	$url		Url
	* @param	string	$title		Url title
	* @param	int		$art_id		article id
	* @return	bool				1 on success, 0 on failure
	*/
	function add_link($url, $title, $art_id)
	{
		$sql = "INSERT INTO egw_kb_urls (art_id, art_url, art_url_title) VALUES ($art_id, '$url', '$title')";
		$this->db->query($sql, __LINE__, __FILE__);
		if (!$this->db->affected_rows()) return 0;
		return 1;
	}

	/**
	* Publishes article, and resets creation and modification date
	*
	* @author	Alejandro Pedraza
	* @access	public
	* @param	int		$art_id		article id
	* @return	int					Numbers of lines affected (should be 1, if not there's an error)
	**/
	function publish_article($art_id)
	{
		$sql = "UPDATE egw_kb_articles SET published=1, created=". time() . ", modified=" . time() . " WHERE art_id=$art_id";
		$this->db->query($sql, __LINE__, __FILE__);

		// check if the article answers a question, and if so, delete it
		$sql = "SELECT q_id FROM egw_kb_articles WHERE art_id=$art_id";
		$this->db->query($sql, __LINE__, __FILE__);
		if ($this->db->next_record())
		{
			$sql = "DELETE FROM egw_kb_questions WHERE question_id=".$this->db->f('q_id');
			$this->db->query($sql, __LINE__, __FILE__);
		}

		return True;
	}

	/**
	* Publishes question
	*
	* @author	Alejandro Pedraza
	* @access	public
	* @param	int		$q_id		Question id
	* @return	int					Numbers of lines affected (should be 1, if not there's an error)
	**/
	function publish_question($q_id)
	{
		$sql = "UPDATE egw_kb_questions SET published=1 WHERE question_id=$q_id";
		$this->db->query($sql, __LINE__, __FILE__);
		return ($this->db->affected_rows());
	}

	/**
	* Publishes article comment
	*
	* @author	Alejandro Pedraza
	* @access	public
	* @param	int	$art_id		Article ID
	* @param	int $comment_id	Comment ID
	* @return	int				Numbers of lines affected (should be 1, if not there's an error)
	*/
	function publish_comment($art_id, $comment_id)
	{
		$sql = "UPDATE egw_kb_comment SET published=1 WHERE art_id=$art_id AND comment_id=$comment_id";
		$this->db->query($sql, __LINE__, __FILE__);
		return ($this->db->affected_rows());
	}

	/**
	* Deletes article comment
	*
	* @author	Alejandro Pedraza
	* @access	public
	* @param	int	$art_id		Article ID
	* @param	int $comment_id	Comment ID
	* @return	int				Numbers of lines affected (should be 1, if not there's an error)
	*/
	function delete_comment($art_id, $comment_id)
	{
		$sql = "DELETE FROM egw_kb_comment WHERE art_id=$art_id AND comment_id=$comment_id";
		$this->db->query($sql, __LINE__, __FILE__);
		return ($this->db->affected_rows());
	}

	/**
	* Deletes article comment
	*
	* @author	Alejandro Pedraza
	* @access	public
	* @param	int	$art_id			Article ID
	* @param	int $delete_link	Link ID
	* @return	bool				1 on success, 0 on failure
	*/
	function delete_link($art_id, $delete_link)
	{
		$delete_link = $this->db->db_addslashes($delete_link);
		$sql = "DELETE FROM egw_kb_urls WHERE art_id=$art_id AND art_url='$delete_link'";
		$this->db->query($sql, __LINE__, __FILE__);
		if (!$this->db->affected_rows()) return 0;
		return 1;
	}

	/**
	* Increments vote_x in table
	*
	* @author	Alejandro Pedraza
	* @access	public
	* @param	int	$art_id			Article id
	* @param 	int	$rating			Rating between 1 and 5
	* @param	int	$current_rating	Number of current votes in that rating
	* @return	bool				1 on success, 0 on failure
	**/
	function add_vote($art_id, $rating, $current_rating)
	{
		$new_rating = $current_rating + 1;
		$sql = "UPDATE egw_kb_articles SET votes_" . $rating . "=$new_rating WHERE art_id=$art_id";
		$this->db->query($sql, __LINE__, __FILE__);
		if (!$this->db->affected_rows()) return 0;
		return 1;
	}

	/**
	* Registers that actual user has voted this article
	*
	* @author	Alejandro Pedraza
	* @access	public
	* @param	int	$art_id		article id
	* @return	bool			1 on success, 0 on failure
	**/
	function add_rating_user($art_id)
	{
		$sql = "INSERT INTO egw_kb_ratings (user_id, art_id) VALUES(" . $GLOBALS['egw_info']['user']['account_id'] . ", $art_id)";
		$this->db->query($sql, __LINE__, __FILE__);
		if (!$this->db->affected_rows()) return 0;
		return 1;
	}

	/**
	* Checks if there is already an article in the db with the given ID
	*
	* @author	Alejandro Pedraza
	* @access	public
	* @param	int		$art_id		article id
	* @return	bool				1 if there is one, 0 if not
	**/
	function exist_articleID($article_id)
	{
		$sql = "SELECT art_id FROM egw_kb_articles WHERE art_id=" . $article_id;
		$this->db->query($sql, __LINE__, __FILE__);
		return $this->db->next_record();
	}

	/**
	* Returns ids of owners of articles
	*
	* @author	Alejandro Pedraza
	* @access	public
	* @param	array	$articles	Ids of articles
	* @return	array				Article ids and owners ids
	*/
	function owners_list($articles)
	{
		$articles = implode(', ', $articles);
		$sql = "SELECT art_id, user_id FROM egw_kb_articles WHERE art_id IN($articles)";
		$this->db->query($sql, __LINE__, __FILE__);
		$owners = array();
		while ($this->db->next_record())
		{
			$owners[] = array('art_id' => $this->db->f('art_id'), 'user_id' => $this->db->f('user_id'));
		}
		return $owners;
	}

	/**
	* Adds related article to article
	*
	* @author	Alejandro Pedraza
	* @access	public
	* @param	int		$art_id		Article id
	* @param	array	$articles	Articles id to relate to $art_id
	* @return	bool				1 on success, 0 on failure
	*/
	function add_related($art_id, $articles)
	{
		$added = False;
		foreach ($articles as $article)
		{
			$sql = "INSERT INTO egw_kb_related_art (art_id, related_art_id) VALUES($art_id, $article)";
			$this->db->query($sql, __LINE__, __FILE__);
			if ($this->db->affected_rows()) $added = True;
		}
		return $added;
	}

	/**
	* Deletes related article to article
	*
	* @author	Alejandro Pedraza
	* @access	public
	* @param	int		$art_id		Article id
	* @param	int		$related_id	Article id to delete
	* @return	void
	*/
	function delete_related($art_id, $related_id, $all = False)
	{
		$sql_operator = $all? 'OR' : 'AND';
		$sql = "DELETE FROM egw_kb_related_art WHERE art_id=$art_id $sql_operator related_art_id=$related_id";
		$this->db->query($sql, __LINE__, __FILE__);
	}

	/**
	* Deletes entry in keywords table
	*
	* @author	Alejandro Pedraza
	* @access	public
	* @param	int		$art_id		Article id
	* @return	void
	*/
	function delete_search($art_id)
	{
		$sql = "DELETE FROM egw_kb_search WHERE art_id=$art_id";
		$this->db->query($sql, __LINE__, __FILE__);
	}

	/**
	* Adds question to database
	*
	* @author	Alejandro Pedraza
	* @access	public
	* @param	array	$data		Question data
	* @param	bool	$publish	Whether to publish the question or not
	* @return	int				Numbers of lines affected (should be 1, if not there's an error)
	*/
	function add_question($data, $publish)
	{
		($publish)? $publish = 1 : $publish = 0;
		$sql = "INSERT INTO egw_kb_questions (user_id, summary, details, cat_id, creation, published) VALUES ("
				. $GLOBALS['egw_info']['user']['account_id'] . ", '"
				. $this->db->db_addslashes($data['summary']) . "', '"
				. $this->db->db_addslashes($data['details']) . "', "
				. (int)$data['cat_id'] . ", "
				. time() . ", "
				. $publish . ")";
		$this->db->query($sql, __LINE__, __FILE__);
		return $this->db->affected_rows();
	}

	/**
	* Returns question
	*
	* @author	Alejandro Pedraza
	* @access	public
	* @param	int		$q_id	Question id
	* @return	array			Question
	*/
	function get_question($q_id)
	{
		$fields = array('user_id', 'summary', 'details', 'cat_id', 'creation');
		$fields_str = implode(", ", $fields);

		$sql = "SELECT $fields_str FROM egw_kb_questions WHERE question_id=$q_id AND published=1";
		$this->db->query($sql, __LINE__, __FILE__);
		$question = array();
		while ($this->db->next_record())
		{
			foreach ($fields as $field)
			{
				$question[$field] = $this->db->f($field);
			}
		}
		return $question;
	}
}
