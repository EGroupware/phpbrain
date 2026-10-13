/**
 * EGroupware phpbrain static javascript code
 *
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package phpbrain
 * @link http://www.egroupware.org
 * @author Klaus Leithoff <kl@stylite.de>
 * @version $Id$
 */

import {EgwApp} from '../../api/js/jsapi/egw_app';
// app is an ambient global (declare global {} in egw_global.d.ts) - no import needed or possible.

/**
 * UI for phpbrain
 *
 * phpbrain has no JS of its own beyond the legacy toggleAllCheckboxes() below, so this class has
 * nothing to override - it exists only so app.classes.phpbrain is a real class: without it,
 * etemplate2.load() finds a rollup entry for phpbrain/js/app.min.js (this file) but no resulting
 * app.classes.phpbrain, assumes the chunk failed to load because of a mid-session rebuild (ticket
 * #124112) and tells the user their client is outdated - which it isn't (ticket #126081).
 */
class PhpbrainApp extends EgwApp
{
}

// Register the app with EGroupware
app.classes.phpbrain = PhpbrainApp;

// referenced via onclick="javascript:toggleAllCheckboxes();" in templates/default/maintain_articles.xet,
// so it has to stay a plain global, not a module-scoped function or class method
(<any>window).toggleAllCheckboxes = function(which?)
{
	var selected = document.getElementsByName('exec[nm][rows][selected][]');

	for (let i = 0; i < selected.length; i++)
	{
		(<HTMLInputElement>selected[i]).checked = !(<HTMLInputElement>selected[i]).checked;
	}
}
