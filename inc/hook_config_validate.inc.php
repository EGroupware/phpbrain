<?php
/**************************************************************************\
* eGroupWare KnowledgeBase - site config validation                           *
* http://www.egroupware.org                                                *
* -------------------------------------------------                        *
* Copyright (C) 2007 RalfBecker@outdoor-training.de                        *
* --------------------------------------------                             *
*  This program is free software; you can redistribute it and/or modify it *
*  under the terms of the GNU General Public License as published by the   *
*  Free Software Foundation; either version 2 of the License, or (at your  *
*  option) any later version.                                              *
\**************************************************************************/


$GLOBALS['egw_info']['server']['found_validation_hook'] = True;

function final_validation($settings)
{	
	if ($settings['upload_dir'])
	{
		if (!is_dir($_SERVER['DOCUMENT_ROOT'].$settings['upload_dir']) || !file_exists($_SERVER['DOCUMENT_ROOT'].$settings['upload_dir'].'/.'))
		{
			$GLOBALS['config_error'] = 'Directory does not exist, is not readable by the webserver or is not relative to the document root!';
		}
	}
}
