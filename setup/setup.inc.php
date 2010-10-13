<?php
/**
 * EGroupware Knowledgebase - Setup
 *
 * Started off as a port of phpBrain - http://vrotvrot.com/phpBrain/ but quickly became a full rewrite
 *
 * @link http://www.egroupware.org
 * @author Alejandro Pedraza <alpeb(at)users.sourceforge.net>
 * @package phpbrain
 * @subpackage setup
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

/* Basic information about this app */
$setup_info['phpbrain']['name']      = 'phpbrain';
$setup_info['phpbrain']['title']     = 'Knowledge Base';
$setup_info['phpbrain']['version']   = '1.8';
$setup_info['phpbrain']['app_order'] = 25;
$setup_info['phpbrain']['enable']    = 1;
$setup_info['phpbrain']['index']     = 'phpbrain.uikb.index';

$setup_info['phpbrain']['author'] = 'Alejandro Pedraza';
$setup_info['phpbrain']['note']   = 'Knowledge Base repository';
$setup_info['phpbrain']['license']  = 'GPL';
$setup_info['phpbrain']['description'] = 'Searchable Knowledge Base.';

/* The hooks this app includes, needed for hooks registration */
$setup_info['phpbrain']['hooks'][] = 'about';
$setup_info['phpbrain']['hooks'][] = 'admin';
$setup_info['phpbrain']['hooks'][] = 'add_def_pref';
$setup_info['phpbrain']['hooks'][] = 'config';
$setup_info['phpbrain']['hooks'][] = 'config_validate';
$setup_info['phpbrain']['hooks'][] = 'preferences';
$setup_info['phpbrain']['hooks'][] = 'settings';
$setup_info['phpbrain']['hooks'][] = 'sidebox_menu';
$setup_info['phpbrain']['hooks'][] = 'deleteaccount';
$setup_info['phpbrain']['hooks']['search_link'] = 'phpbrain.bokb.search_link';
$setup_info['phpbrain']['hooks']['delete_category'] = 'phpbrain.bokb.delete_category';

$setup_info['phpbrain']['tables'][] = 'egw_kb_articles';
$setup_info['phpbrain']['tables'][] = 'egw_kb_comment';
$setup_info['phpbrain']['tables'][] = 'egw_kb_questions';
$setup_info['phpbrain']['tables'][] = 'egw_kb_ratings';
$setup_info['phpbrain']['tables'][] = 'egw_kb_related_art';
$setup_info['phpbrain']['tables'][] = 'egw_kb_search';
$setup_info['phpbrain']['tables'][] = 'egw_kb_urls';

/* Dependencies for this app to work */
$setup_info['phpbrain']['depends'][] = array(
	'appname' => 'phpgwapi',
	'versions' => Array('1.7','1.8','1.9')
);



