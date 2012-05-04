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

if (!defined('PHPBRAIN_APP'))
{
    define('PHPBRAIN_APP','phpbrain');
}

class sokb
{
	/**
	* Database object
	*
	* @access	private
	* @var		egw_db
	*/
	static public $db;

	/**
	 * Timestaps that need to be adjusted to user-time on reading or saving
	 *
	 * @var array
	*/
	static public $timestamps = array(
		'created','modified','entered','creation',
	);
	/**
	 * Offset in secconds between user and server-time, it need to be add to a server-time to get the user-time
	 * or substracted from a user-time to get the server-time
	 *
	 * @var int
	 */
	static public $tz_offset_s;
	/**
	 * Current time as timestamp in user-time
	 *
	 * @var int
	 */
	static public $now;
	/**
	 * Start of today in user-time
	 *
	 * @var int
	 */
	static public $today;

	/**
	* Number of rows in result set
	*
	* @access	public
	* @var		int
	*/
	static public $num_rows;

	/**
	* Number of unanswered questions in result set
	*
	* @access	public
	* @var		int
	*/
	static public $num_questions;

	/**
	* Number of comments in result set
	*
	* @access	public
	* @var		int
	*/
	static public $num_comments;

	/**
	* Type of LIKE SQL operator to use
	*
	* @access	private
	* @var		string
	*/
	static public $like;

	/**
	* Class constructor
	*
	* @author	Alejandro Pedraza
	* @access	public
	**/
	function __construct()
	{
		self::$db = $GLOBALS['egw']->db;
		self::$tz_offset_s = $GLOBALS['egw']->datetime->tz_offset;
		self::$now = time() + self::$tz_offset_s;   // time() is server-time and we need a user-time
		self::$today = mktime(0,0,0,date('m',self::$now),date('d',self::$now),date('Y',self::$now));

		self::$like = self::$db->capabilities['case_insensitive_like'];
	}
	/**
	 * old class constructor, now wraps the new one
	 */
	function sokb()
	{
		self::__construct();
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
		$loclike = self::$like;
		$where = array(
			'user_id' => $owners,
			'cat_id'  => !$categories ? 0 : $categories,
		);
		if (isset($owners['fetch']) && $owners['fetch'] == 'all') unset($where['user_id']); // if we pass fetch -> all, return all entrys
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
				$scores[] = 'keyword='.self::$db->quote($word);

				if ((int)$word)
				{
					$likes[] = 'egw_kb_articles.art_id='.(int)$word;
					continue;	// numbers are only searched as article-id
				}
				if (strpos($word,"user_id=")!==false)
				{
					$word = str_replace('user_id=','',$word);
					$adduserquery[]  = 'egw_kb_articles.user_id='.(int)$word;
					continue;
				}
				foreach(array('title','topic','text') as $col)
				{
					$likes[] = $col.' '.$loclike.' '.self::$db->quote('%'.$word.'%');
				}
			}
			$score = 'SELECT sum(score) FROM egw_kb_search WHERE art_id=egw_kb_articles.art_id AND ('.implode(' OR ',$scores).')';
			$fields .= ",($score) AS pertinence";

			$where[] = "(($score) > 0 OR ".implode(' OR ',$likes).')';
			if($adduserquery) $where[] = $adduserquery;
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

		self::$db->select('egw_kb_articles','COUNT(*)',$where,__LINE__,__FILE__);
		self::$num_rows = self::$db->next_record() ? self::$db->f(0) : 0;

		self::$db->select('egw_kb_articles',$fields,$where,__LINE__,__FILE__,$start,$order_sql,False,($upper_limit?$upper_limit:0));
		//self::$db->query($sql, __LINE__, __FILE__,$start,($upper_limit?$upper_limit:0));

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
		$loclike = self::$like;
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
		$all_words = self::$db->db_addslashes($all_words);
		$all_words = strlen($all_words)? explode(' ', $all_words) : False;
		$each_field = array();
		if ($all_words)
		{
			foreach ($all_words as $word)
			{
				$each_field[] = "(" . implode(" {$loclike} '%$word%' OR ", $target_fields) . " {$loclike} '%$word%')";
			}
			if ($each_field)
			{
				$sql .= " AND " . implode(" AND ", $each_field);
			}
		}

		// "with the exact phrase" filtering
		$phrase = self::$db->db_addslashes($phrase);
		if ($phrase)
		{
			$sql .= " AND (" . implode (" {$loclike} '%$phrase%' OR ", $target_fields) . " {$loclike} '%$phrase%')";
		}

		// "With at least one of the words" filtering
		$one_word = self::$db->db_addslashes($one_word);
		$one_word = strlen($one_word)? explode(' ', $one_word) : False;
		if ($one_word)
		{
			$each_field = array();
			foreach ($one_word as $word)
			{
				$each_field[] = "(" . implode(" {$loclike} '%$word' OR ", $target_fields) . " {$loclike} '%$word%')";
			}
			$sql .= " AND (". implode (" OR ", $each_field) . ")";
		}

		// "Without the words" filtering
		$without_words = self::$db->db_addslashes($without_words);
		$without_words = strlen($without_words)? explode(' ', $without_words) : False;
		$each_field = array();
		if ($without_words)
		{
			foreach ($without_words as $word)
			{
				$each_field[] = "(" . implode(" NOT {$loclike} '%$word' AND ", $target_fields) . " NOT {$loclike} '%$word%')";
			}
			$sql .= " AND " . implode(" AND ", $each_field);
		}

		// do the query
		//echo "query: $sql <br>";
		self::$db->query($sql, __LINE__, __FILE__);
		self::$num_rows = self::$db->num_rows();
		self::$db->limit_query($sql, $start, __LINE__, __FILE__, $num_res);
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
		while(($article = self::$db->row(true)))
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
	* delete the keywords in the egw_kb_search table for a given article
	*
	* @author	Klaus Leithoff
	* @access	public
	* @param	int		$art_id	Article ID
	* @param	string	$words			all Keyword(s)
	* @return	void
	*/
	function delete_keywords($art_id, $words=false)
	{
		if ($words) $words = array_diff(explode(' ',$words),array(''));

		// delete all existing and NOT longer mentioned keywords
		self::$db->delete('egw_kb_search',!$words ? array('art_id' => $art_id) :
			self::$db->expression('egw_kb_search',array('art_id' => $art_id),' AND ',array('keyword'=>$words)),
			__LINE__,__FILE__);
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
	function update_keywords($art_id, $words, $upgrade_key, $cleanupkeys = false)
	{
		$old_keys = self::get_keywords($art_id);
		$words = explode(' ',$words);
		$to_delete = array_diff($old_keys, $words);
		// delete all existing and NOT longer mentioned keywords
		if ($cleanupkeys && !empty($to_delete)) self::delete_keywords($art_id, implode(' ',$to_delete));

		foreach($words as $word)
		{
			self::update_keyword($art_id,$word,$upgrade_key);
		}
	}

	/**
	* Checks if there is already an keyword in the db with the given ID
	*
	* @author	Klaus Leithoff
	* @access	public
	* @param	int		$art_id		article id
	* @param	string	$word		keyword
	* @param	int		$score		score (reference)
	* @return	int				1 if there is one, 0 if not
	**/
	function exist_keyword($article_id, $word, &$score=1)
	{
		$fields_str = "count(art_id), score";
		$where = array(
			'art_id'=>$article_id,
			'keyword'=>$word
		);
		foreach(self::$db->select('egw_kb_search',$fields_str,$where,__LINE__,__FILE__,false,' GROUP BY score ',PHPBRAIN_APP) as $row)
		{
			#error_log(__METHOD__.$row['count(art_id)'].'#' .$row['score']);
			$score = $row['score'];
			return $row['count(art_id)'];
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
		// retrieve current score
		$keyword_exists = self::exist_keyword($art_id, $word, $old_score);
		#error_log(__METHOD__.$word.'#'.$keyword_exists.'#' .$old_score.'#'.$upgrade_key);
		if ($keyword_exists )
		{
			// upgrade score
			$new_score = $upgrade_key ? $old_score + 1 : $old_score - 1;
			#error_log(__METHOD__."Update".$old_score."#".$upgrade_key);
			$where = array( 'art_id' => $art_id, 'keyword' => $word);
			$valuzes = array('score'=> $new_score);
			if (!($upgrade_key === 'same')) {
				#error_log(__METHOD__."Update".$new_score."#".$upgrade_key);
				self::$db->update('egw_kb_search',$valuzes,$where,__LINE__,__FILE__,PHPBRAIN_APP);
			}
		}
		else
		{
			// create new entry for word
			#error_log(__METHOD__."Insert");
			$where = $valuzes = array( 'art_id' => $art_id, 'keyword' => $word, 'score'=> 1);
			self::$db->insert('egw_kb_search',$valuzes,$where,__LINE__,__FILE__,PHPBRAIN_APP);
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
		$loclike = self::$like;
		$fields = array('question_id', 'user_id', 'summary', 'details', 'cat_id', 'creation', 'published');
		$fields_str = implode(', ', $fields);
		$ownerquery = " IN (".implode(', ', $owners).") ";
		if (isset($owners['fetch']) && $owners['fetch']=='all') $ownerquery = " > 0 ";
		$sql = "SELECT $fields_str FROM egw_kb_questions WHERE user_id $ownerquery";
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
			$query = self::$db->db_addslashes($query);
			$words = explode(' ', $query);
			$sql .= " AND (summary {$loclike} '%" . implode("%' OR summary {$loclike} '%", $words) . "%' OR details {$loclike} '%" . implode("%' OR details {$loclike} '%", $words) . "%')";
		}
		if ($order)
		{
			$sql .= " ORDER BY $order $sort";
		}
		//echo "sql: $sql <br><br>";
		self::$db->query($sql, __LINE__, __FILE__);
		self::$num_questions = self::$num_rows = self::$db->num_rows();
		self::$db->limit_query($sql, $start, __LINE__, __FILE__, $upper_limit);
		$questions = array();
		for ($i=0; self::$db->next_record(); $i++)
		{
			foreach ($fields as $field)
			{
				$questions[$i][$field] = self::$db->f($field);
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
		$current_time = self::$now;
		if ($is_new)
		{
			($publish)? $publish = 1 : $publish = 0;
			$q_id = $contents['answering_question']? $contents['answering_question'] : 0;
			$valuzes = array(
				'q_id'	=> $q_id,
				'title' => $contents['title'],
				'topic'	=> $contents['topic'],
				'text'	=> $contents['text'],
				'cat_id'=> (int) $contents['cat_id'],
				'published'	=> $publish,
				'user_id'	=> $GLOBALS['egw_info']['user']['account_id'],
				'created'	=> $current_time,
				'modified'	=> $current_time,
				'modified_user_id'	=> $GLOBALS['egw_info']['user']['account_id'],
				'votes_1'	=> 0,
				'votes_2'	=> 0,
				'votes_3'	=> 0,
				'votes_4'	=> 0,
				'votes_5'	=> 0,
			);
			self::$db->insert('egw_kb_articles',$valuzes,array(),__LINE__,__FILE__,PHPBRAIN_APP);
			$article_id = self::$db->get_last_insert_id('egw_kb_articles', 'art_id');
			#error_log(__METHOD__.":$article_id created");
			// update table egw_kb_search with keywords. Even if no keywords were introduced, generate an entry
			self::update_keywords($article_id, $contents['keywords'], 'same');

			// if publication is automatic and the article answers a question, delete the question
			if ($publish && $contents['answering_question'])
			{
				self::delete_question($q_id);
			}

			return $article_id;
		}
		else
		{
			$valuzes = array('title'=>$contents['title'],
					'topic'=> $contents['topic'],
					'text' => $contents['text'],
					'cat_id'=> (int)$contents['cat_id'],
					'modified' => $current_time,
					'modified_user_id' => $GLOBALS['egw_info']['user']['account_id'],
			);
			$queries_ok = false;
			$where = array('art_id'=>$contents['editing_article_id']);
			$queries_ok = self::$db->update('egw_kb_articles',$valuzes,$where,__LINE__,__FILE__,PHPBRAIN_APP);

			// update keywords, preserve keyword score
			self::update_keywords($contents['editing_article_id'],$contents['keywords'], 'same', True);

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
		$valuzes = array( 'user_id'=>$new_owner);
		$where = array('user_id'=>$owner);
		self::$db->update('egw_kb_articles',$valuzes,$where,__LINE__,__FILE__,PHPBRAIN_APP);
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
		$deleted = true;
		$where = array(
			'art_id' =>$art_id,
		);
		if ($art_id &&  !self::$db->delete('egw_kb_articles',$where,__LINE__,__FILE__,PHPBRAIN_APP)) {
			error_log(__FILE__.':'.__METHOD__,":Could not delete article No.:$art_id");
			$deleted = false;
		}
		if ($deleted) self::delete_keywords($art_id);
		return $deleted;
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
		$deleted = true;
		$where = array(
			'question_id' =>$q_id,
		);
		if ($q_id &&  !self::$db->delete('egw_kb_questions',$where,__LINE__,__FILE__,PHPBRAIN_APP)) {
			error_log(__FILE__.':'.__METHOD__,":Could not delete Question No.:$q_id");
			$deleted = false;
		}
		return $deleted;
	}

	/**
	* Returns latest articles entered
	*
	* @author	Alejandro Pedraza
	* @access	public
	* @param	int	$parent_cat	Category id
	* @return	array	Articles
	*/
	/*
 	not used anymore?
	function get_latest_articles($parent_cat)
	{
		$sql = "SELECT art_id, title, topic, text, modified, votes_1, votes_2, votes_3, votes_4, votes_5 FROM egw_kb_articles";
		self::$db->query($sql, __LINE__, __FILE__);

		$articles = array();
		while (self::$db->next_record())
		{
			$rating = 1*self::$db->f('votes_1') + 2*self::$db->f('votes_2') + 3*self::$db->f('votes_3') + 4*self::$db->f('votes_4') + 5*self::$db->f('votes_5');
			$articles[self::$db->f('art_id')] = array(
				'title'		=> self::$db->f('title'),
				'topic'		=> self::$db->f('topic'),
				'text'		=> self::$db->f('text'),
				'modified'	=> self::$db->f('modified'),
				'rating'	=> $rating
			);
		}

		return $articles;
	}
	*/

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
		$where = array('art_id'=>$art_id);
		$article=array();
		foreach(self::$db->select('egw_kb_articles',$fields_str,$where,__LINE__,__FILE__,false,'',PHPBRAIN_APP) as $row)
		{
			foreach ($fields as $field)
			{
				$article[$field] = $row[$field];
			}
		}
		// get article's attached urls
		foreach ( self::get_urls($art_id) as $url ) {
			$article['urls'][] = array('link' => $url['art_url'], 'title' =>$url['art_url_title']);
		}
		// get article's keywords
		$article['keywords'] = self::get_keywords($art_id);
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
	* Returns article's keywords
	*
	* @author	klaus Leithoff
	* @param	int		$art_id		article id
	* @return	array				keywords
	*/
	function get_keywords($art_id)
	{
		$fields_str = "keyword";
		$rv = array();
		$where = array('art_id'=>$art_id );
		foreach(self::$db->select('egw_kb_search',$fields_str,$where,__LINE__,__FILE__,false,' ORDER BY keyword ASC',PHPBRAIN_APP) as $row)
		{
			$rv[] = $row[$fields_str];
		}
		return $rv;
	}

 	/**
	* Returns article's urls
	*
	* @author	klaus Leithoff
	* @param	int		$art_id		article id
	* @return	array				URLs
	*/
	function get_urls($art_id)
	{
		$fields_str = "art_url, art_url_title";
		$rv = array();
		$where = array('art_id'=>$art_id );
		foreach(self::$db->select('egw_kb_urls',$fields_str,$where,__LINE__,__FILE__,false,' ORDER BY art_url_title ASC',PHPBRAIN_APP) as $row)
		{
			$rv[] = $row;
		}
		return $rv;
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
		$fields_str = "art_id";
		$rv = array();
		$where = array('user_id'=>$owner,);
		foreach(self::$db->select('egw_kb_articles',$fields_str,$where,__LINE__,__FILE__,false,'',PHPBRAIN_APP) as $row)
		{
			$rv[] = $row[$field_str];
		}
		return $rv;
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
		$valuzes = array(
			'views' => $current_count,
		);
		$where = array(
			'art_id' =>$art_id,
		);
		self::$db->update('egw_kb_articles',$valuzes,$where,__LINE__,__FILE__,PHPBRAIN_APP);
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
		$fields_str = "comment_id, user_id, kb_comment, entered, art_id, published";
		$comments = array();
		$where = array('art_id'=>$art_id );
		foreach(self::$db->select('egw_kb_comment',$fields_str,$where,__LINE__,__FILE__,($limit ? 0:false),' ORDER BY entered DESC',PHPBRAIN_APP,$limit) as $row)
		{
			$GLOBALS['egw']->accounts->get_account_name($row['user_id'], $lid, $fname, $lname);
			$row['username'] = $fname . ' ' . $lname;
			$comments[] = $row;
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
		if ($art_id) self::$db->delete('egw_kb_comment',array('art_id' => $art_id),__LINE__,__FILE__,PHPBRAIN_APP);
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
		if ($art_id) self::$db->delete('egw_kb_ratings',array('art_id' => $art_id),__LINE__,__FILE__,PHPBRAIN_APP);
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
		if ($art_id) self::$db->delete('egw_kb_urls',array('art_id' => $art_id),__LINE__,__FILE__,PHPBRAIN_APP);
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
		$fields_str = "egw_kb_articles.art_id, egw_kb_articles.title";
		$related = array();
		$where = array(
			'egw_kb_related_art.related_art_id=egw_kb_articles.art_id',
			'egw_kb_related_art.art_id='. $art_id,
			'egw_kb_articles.user_id in ('.$owners.')',
		);
		foreach(self::$db->select('egw_kb_articles',$fields_str,$where,__LINE__,__FILE__,false,'',PHPBRAIN_APP,0,
			", egw_kb_related_art") as $row)
		{
			$related[] = $row;
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
		$fields_str = "count(art_id)";
		$where = array('art_id'=>$article_id, 'user_id'=> $GLOBALS['egw_info']['user']['account_id']);
		foreach(self::$db->select('egw_kb_ratings',$fields_str,$where,__LINE__,__FILE__,false,'',PHPBRAIN_APP) as $row)
		{
			return $row[$fields_str];
		}
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
		($publish)? $publish = 1 : $publish = 0;
		$added = true;
		$valuzes = array(
			'user_id' => $GLOBALS['egw_info']['user']['account_id'],
			'kb_comment' => $comment,
			'entered' => self::$now,
			'art_id' => $art_id,
			'published' =>$publish
		);
		$where = $valuzes;
		unset($where['entered']);
		if (!self::$db->insert('egw_kb_comment',$valuzes,$where,__LINE__,__FILE__,PHPBRAIN_APP)) {
			error_log(__FILE__.':'.__METHOD__,":Could not add comment to Articel No.:$art_id");
			$added = false;
		}
		return $added;
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
		$added = true;
		$valuzes = array(
			'art_id' => $art_id,
			'art_url' =>$url,
			'art_url_title'=>$title,
		);
		$where = $valuzes;
		if (!self::$db->insert('egw_kb_urls',$valuzes,$where,__LINE__,__FILE__,PHPBRAIN_APP)) {
			error_log(__FILE__.':'.__METHOD__,":Could not add urls to Article No.:$art_id");
			$added = false;
		}
		return true;
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
		$updated = true;
		$valuzes = array(
			'published' => 1,
			'created'	=> self::$now,
			'modified'	=> self::$now,
		);
		$where = array(
			'art_id' =>$art_id,
		);
		if (!self::$db->update('egw_kb_articles',$valuzes,$where,__LINE__,__FILE__,PHPBRAIN_APP)) {
			error_log(__FILE__.':'.__METHOD__,":Could not update (publish) article No.:$art_id");
			$updated = false;
		}

		// check if the article answers a question, and if so, delete it
		$fields_str = "q_id";
		foreach(self::$db->select('egw_kb_articles',$fields_str,$where,__LINE__,__FILE__,false,'',PHPBRAIN_APP) as $row)
		{
			self::delete_question($row[$fields_str]);
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
		$updated = true;
		$valuzes = array(
			'published' => 1,
		);
		$where = array(
			'question_id' =>$q_id,
		);
		if (!self::$db->update('egw_kb_questions',$valuzes,$where,__LINE__,__FILE__,PHPBRAIN_APP)) {
			error_log(__FILE__.':'.__METHOD__,":Could not publish question $q_id");
			$updated = false;
		}
		return $updated;
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
		$updated = true;
		$valuzes = array(
			'published' => 1,
		);
		$where = array(
			'art_id' =>$art_id,
			'comment_id' => $comment_id,
		);
		if (!self::$db->update('egw_kb_comment',$valuzes,$where,__LINE__,__FILE__,PHPBRAIN_APP)) {
			error_log(__FILE__.':'.__METHOD__,":Could not update (publish) comment $comment_id for Articel No.:$art_id");
			$updated = false;
		}
		return $updated;
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
		$deleted = true;
		$where = array(
			'art_id' => $art_id,
			'comment_id' => $comment_id,
		);
		if (!self::$db->delete('egw_kb_comment',$where,__LINE__,__FILE__,PHPBRAIN_APP)) {
			error_log(__FILE__.':'.__METHOD__,":Could not delete comment $comment_id for article No.:$art_id");
			$deleted = false;
		}
		return $deleted;
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
		$deleted = true;
		$where = array(
			'art_id' =>$art_id,
			'art_url'=> $delete_link,
		);
		if ($art_id &&  !self::$db->delete('egw_kb_urls',$where,__LINE__,__FILE__,PHPBRAIN_APP)) {
			error_log(__FILE__.':'.__METHOD__,":Could not delete Articel No.:$art_id from egw_kb_urls");
			$deleted = false;
		}
		return $deleted;
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
		$updated = true;
		$new_rating = $current_rating + 1;
		$valuzes = array(
			'votes_'.$rating => $new_rating,
		);
		$where = array(
			'art_id' =>$art_id,
		);
		if (!self::$db->update('egw_kb_articles',$valuzes,$where,__LINE__,__FILE__,PHPBRAIN_APP)) {
			error_log(__FILE__.':'.__METHOD__,":Could not update Articel No.:$art_id with new rating");
			$updated = false;
		}
		return $updated;
	}

	/**
	* Registers that actual user has voted this article
	*
	* @author	Alejandro Pedraza
	* @access	public
	* @param	int	$art_id		article id
	* @return	bool		,	1 on success, 0 on failure
	**/
	function add_rating_user($art_id)
	{
		$added = true;
		$valuzes = array(
			'user_id' => $GLOBALS['egw_info']['user']['account_id'],
			'art_id' =>$art_id
		);
		$where = $valuzes;
		if (!self::$db->insert('egw_kb_ratings',$valuzes,$where,__LINE__,__FILE__,PHPBRAIN_APP)) {
			error_log(__FILE__.':'.__METHOD__,":Could not add voting for Articel No.:$art_id by user No.:$user_id");
			$added = false;
		}
		return $added;
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
		$fields_str = "count(art_id)";
		$where = array('art_id'=>$article_id);
		foreach(self::$db->select('egw_kb_articles',$fields_str,$where,__LINE__,__FILE__,false,'',PHPBRAIN_APP) as $row)
		{
			return $row[$fields_str];
		}
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
		$fields_str = "art_id, user_id";
		$owners = array();
		$where = array('art_id'=>(array)$articles);
		foreach(self::$db->select('egw_kb_articles',$fields_str,$where,__LINE__,__FILE__,false,'',PHPBRAIN_APP) as $row)
		{
			$owners[] = $row;
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
		$added = true;
		foreach ((array)$articles as $article)
		{
			if ($art_id == $article) {
				error_log(__FILE__.':'.__METHOD__,":Sorry, I dont relate an Articel (No.:$article) to itself");
				continue;
			}
			$added = true;
			$valuzes = array(
				'art_id' => $art_id,
				'related_art_id' =>$article
			);
			$where = $valuzes;
			if (!self::$db->insert('egw_kb_related_art',$valuzes,$where,__LINE__,__FILE__,PHPBRAIN_APP)) {
				error_log(__FILE__.':'.__METHOD__,":Could not add related Articel No.:$article to Article No.:$art_id");
				$added = false;
			}
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
		self::$db->delete('egw_kb_related_art',(array) self::$db->expression('egw_kb_related_art',
							array('art_id'=>$art_id)," $sql_operator ",array('related_art_id'=>$related_id)),__LINE__,__FILE__,PHPBRAIN_APP);
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
		if ($art_id) self::$db->delete('egw_kb_search',array('art_id' => $art_id),__LINE__,__FILE__,PHPBRAIN_APP);
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

		$question = array(
			'user_id'	=>	$GLOBALS['egw_info']['user']['account_id'],
			'summary'	=>	$data['summary'],
			'details'	=>	$data['details'],
			'cat_id'	=>	(int)$data['cat_id'],
			'creation'	=>	self::$now,
			'published'	=>	$publish,
		);

		self::$db->insert('egw_kb_questions',$question,false,__LINE__,__FILE__,PHPBRAIN_APP);
		if (!($q_id = self::$db->get_last_insert_id('egw_kb_questions','question_id')))
		{
			return false;
		}
		return 1;
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
		$fields_str = "user_id, summary, details, cat_id, creation";
		$question = array();
		$where = array('question_id'=>$q_id, 'published'=> 1);
		foreach(self::$db->select('egw_kb_questions',$fields_str,$where,__LINE__,__FILE__,false,'',PHPBRAIN_APP) as $row)
		{
			foreach($row as $key => $value) {
				$question[$key] = $value;
			}
		}
		return $question;
	}

	/**
	* Changes articles category when the old one is deleted
	*
	* @access	public
	* @param	int	$cat		actual category
	* @param	int	$new_category	new category
	**/
	function change_articles_cat($cat, $new_cat)
	{
		$current_time = self::$now;
		$valuzes = array('cat_id'=>$new_cat,
						'modified'	=> $current_time,
						'modified_user_id'	=> $GLOBALS['egw_info']['user']['account_id'],
		);
		$where = array('cat_id'=>$cat);
		self::$db->update('egw_kb_articles',$valuzes,$where,__LINE__,__FILE__,PHPBRAIN_APP);
	}

	/**
	* Changes questions category when the old one is deleted
	*
	* @access	public
	* @param	int	$cat		actual category
	* @param	int	$new_category	new category
	**/
	function change_questions_cat($cat, $new_cat)
	{
		$valuzes = array('cat_id'=>$new_cat);
		$where = array('cat_id'=>$cat);
		self::$db->update('egw_kb_questions',$valuzes,$where,__LINE__,__FILE__,PHPBRAIN_APP);
	}
}
