<?php
	/**************************************************************************\
	* eGroupWare                                                               *
	* http://www.egroupware.org                                                *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	$egw_info = array();
	if(!file_exists('header.inc.php'))
	{
		Header('Location: setup/index.php');
		exit;
	}

	$GLOBALS['sessionid'] = isset($_GET['sessionid']) ? $_GET['sessionid'] : @$_COOKIE['sessionid'];
	if(!$GLOBALS['sessionid'])
	{
		Header('Location: login.php'.
			(isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING']) ?
			'?phpgw_forward='.urlencode('/index.php?'.$_SERVER['QUERY_STRING']):''));
		exit;
	}

	if(isset($_GET['hasupdates']) && $_GET['hasupdates'] == 'yes')
	{
		$hasupdates = True;
	}

	/*
		This is the menuaction driver for the multi-layered design
	*/
	if(isset($_GET['menuaction']))
	{
		list($app,$class,$method) = explode('.',@$_GET['menuaction']);
		if(! $app || ! $class || ! $method)
		{
			$invalid_data = True;
		}
	}
	else
	{
	//$egw->log->message('W-BadmenuactionVariable, menuaction missing or corrupt: %1',$menuaction);
	//$egw->log->commit();

		$app = 'home';
		$invalid_data = True;
	}

	if($app == 'phpgwapi')
	{
		$app = 'home';
		$api_requested = True;
	}

	$GLOBALS['egw_info']['flags'] = array(
		'noheader'   => True,
		'nonavbar'   => True,
		'enable_network_class'    => True,
		'enable_contacts_class'   => True,
		'enable_nextmatchs_class' => True,
		'currentapp' => $app
	);
	include('./header.inc.php');

	// 	Check if we are using windows or normal webpage
	$windowed = false;
	$tpl_info = EGW_SERVER_ROOT . '/phpgwapi/templates/' . $GLOBALS['egw_info']['user']['preferences']['common']['template_set'] . '/setup/setup.inc.php';

	if(@file_exists($tpl_info))
	{
	   include_once($tpl_info);
//	   if(isset($template_info))
//	   {
		  if($GLOBALS['egw_info']['template'][$GLOBALS['egw_info']['user']['preferences']['common']['template_set']]['windowed'])
		  {
			 $windowed = true;
		  }
//	   }
	}

	if($app == 'home' && !$api_requested && !$windowed)
	{
		if ($GLOBALS['egw_info']['server']['force_default_app'] && $GLOBALS['egw_info']['server']['force_default_app'] != 'user_choice')
		{
			$GLOBALS['egw_info']['user']['preferences']['common']['default_app'] = $GLOBALS['egw_info']['server']['force_default_app'];
		}
		if($GLOBALS['egw_info']['user']['preferences']['common']['default_app'] && !$hasupdates)
		{
			$GLOBALS['egw']->redirect_link('/'.$GLOBALS['egw_info']['user']['preferences']['common']['default_app'].'/index.php');
		}
		else
		{
			$GLOBALS['egw']->redirect_link('/home/index.php');
		}
	}

	if($windowed && $_GET['cd'] == 'yes')
	{
		$GLOBALS['egw_info']['flags'] = array(
			'noheader'   => False,
			'nonavbar'   => False,
			'enable_network_class'    => True,
			'enable_contacts_class'   => True,
			'enable_nextmatchs_class' => True,
			'currentapp' => 'eGroupWare'
		);
		$GLOBALS['egw']->common->egw_header();
		$GLOBALS['egw']->common->egw_footer();

	}
	else
	{
		if($api_requested)
		{
			$app = 'phpgwapi';
		}

		$GLOBALS[$class] =& CreateObject($app.'.'.$class);
		if((is_array($GLOBALS[$class]->public_functions) && $GLOBALS[$class]->public_functions[$method]) && ! $invalid_data)
		{
			execmethod($_GET['menuaction']);
			unset($app);
			unset($class);
			unset($method);
			unset($invalid_data);
			unset($api_requested);
		}
		else
		{
			if(!$app || !$class || !$method)
			{
				if(@is_object($GLOBALS['egw']->log))
				{
					$GLOBALS['egw']->log->message(array(
						'text' => 'W-BadmenuactionVariable, menuaction missing or corrupt: %1',
						'p1'   => $menuaction,
						'line' => __LINE__,
						'file' => __FILE__
					));
				}
			}

			if(!is_array($GLOBALS[$class]->public_functions) || ! $$GLOBALS[$class]->public_functions[$method] && $method)
			{
				if(@is_object($GLOBALS['egw']->log))
				{
					$GLOBALS['egw']->log->message(array(
						'text' => 'W-BadmenuactionVariable, attempted to access private method: %1',
						'p1'   => $method,
						'line' => __LINE__,
						'file' => __FILE__
					));
				}
			}
			if(@is_object($GLOBALS['egw']->log))
			{
				$GLOBALS['egw']->log->commit();
			}

			$GLOBALS['egw']->redirect_link('/home/index.php');
		}

		if(!isset($GLOBALS['egw_info']['nofooter']))
		{
			$GLOBALS['egw']->common->egw_footer();
		}
	}
?>
