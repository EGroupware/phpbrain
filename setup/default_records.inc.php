<?php
/**
 * EGroupware Knowledgebase - Setup
 *
 * @link http://www.egroupware.org
 * @package phpbrain
 * @subpackage setup
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

// give Default group rights for phpbrain
$defaultgroup = $GLOBALS['egw_setup']->add_account('Default','Default','Group',False,False);
$GLOBALS['egw_setup']->add_acl('phpbrain','run',$defaultgroup);
