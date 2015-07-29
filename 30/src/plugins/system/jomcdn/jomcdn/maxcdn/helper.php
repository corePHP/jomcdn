<?php

/**
 * @version		$Id: helper.php 1 2009-10-27 20:56:04Z rafael $
 * @package		jomCDN
 * @copyright	Copyright (C) 2010 'corePHP' / corephp.com. All rights reserved.
 * @license		GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die;
/**
 * Plugin that replaces media urls with CDN urls
 */
class MAXCDNHelper
{
	function __construct(&$params)
	{
		$this->params = $params;
		$this->params->pass = 0;

		$hascdn = preg_replace(array('#^.*\://#', '#/$#'), '', $this->params->cdn);

		// return if cdn field has no value
		if (!$hascdn) {
			return;
		}


		$this->params->sets = array();

		$nr_of_sets = 1;
		for ($i = 1; $i <= $nr_of_sets; $i++) {
			$this->params->sets[] = $this->initParams($i);
		}

		foreach ($this->params->sets as $i => $set) {
			if (empty($set) || empty($set->searches)) {
				unset($this->params->sets[$i]);
			}
		}

		if (empty($this->params->sets)) {
			return;
		}

		$this->params->pass = 1;
	}

	function initParams($set = '')
	{
		if ($set > 1) {
			$set = '_' . (int) $set;
		} else {
			$set = '';
		}
		$p = new stdClass;

		if ($set && (!isset($this->params->{'use_extra' . $set}) || !$this->params->{'use_extra' . $set})) {
			return $p;
		}

		$p->enable_in_scripts = $this->params->{'enable_in_scripts' . $set};


		$p->cdn = preg_replace('#/$#', '', $this->params->{'cdn' . $set});
		$p->ignorefiles = explode(',', str_replace(array('\n', ' '), array(',', ''), $this->params->{'ignorefiles' . $set}));

		$root = preg_replace(array('#^/#', '#/$#'), '', $this->params->{'root' . $set}) . '/';
		$root = preg_quote(preg_replace('#^/#', '', $root), '#');

		$filetypes = str_replace('-', ',', implode(',', $this->params->{'filetypes' . $set}));
		$filetypes = explode(',', $filetypes);
		$extratypes = preg_replace('#\s#', '', $this->params->{'extratypes' . $set});

		if ($extratypes) {
			$filetypes = array_merge($filetypes, explode(',', $extratypes));
		}

		$filetypes = array_unique(array_diff($filetypes, array('', 'x')));

		$p->searches = array();
		$p->js_searches = array();
		if (!empty($filetypes)) {
			$filetypes = implode('|', $filetypes);
			$attribs = 'href=|src=|longdesc=|@import|name="movie" value=|property="og:image" content=';
			$attribs = str_replace(array('"', '=', ' '), array('["\']?', '\s*=\s*', '\s+'), $attribs);

			// Domain url or root path
			$url = preg_quote(str_replace('https://', 'http://', JURI::root()), '#');
			$url .= '|' . preg_quote(str_replace('http://', '//', JURI::root()), '#');
			if (JURI::root(1)) {
				$url .= '|' . preg_quote(JURI::root(1) . '/', '#');
			}

			$urls = array();

			// Absolute path
			$urls[] = '(?:' . $url . ')' . $root . '([^\?QUOTES]+\.(?:' . $filetypes . ')(?:\?[^QUOTES]*)?)';
			// Relative path
			$urls[] = 'LSLASH' . $root . '([a-z0-9-_]+/[^\?QUOTES]+\.(?:' . $filetypes . ')(?:\?[^QUOTES]*)?)';
			// Relative path - file in root
			$urls[] = 'LSLASH' . $root . '([a-z0-9-_]+[^\?\/QUOTES]+\.(?:' . $filetypes . ')(?:\?[^QUOTES]*)?)';

			self::getSearches($p, $attribs, $urls);
		}

		$p->cdns = explode(',', $p->cdn);
		foreach ($p->cdns as $i => $cdn) {
			$cdn = preg_replace('#^.*\://#', '', trim($cdn));
			$p->cdns[$i] = $cdn;
		}

		return $p;
	}

	////////////////////////////////////////////////////////////////////
	// onContentPrepare
	////////////////////////////////////////////////////////////////////

	function onContentPrepare(&$article, $context = '')
	{
		if (!$this->params->pass) {
			return;
		}

		// FEED
		if (JFactory::getDocument()->getType() != 'feed' && JFactory::getApplication()->input->get('option') != 'com_acymailing') {
			return;
		}


		if (isset($article->text) &&
			($context != 'com_content.category' ||
				!(JFactory::getApplication()->input->get('view') == 'category' && !JFactory::getApplication()->input->get('layout'))
			)
		) {
			$this->replace($article->text);
		}
		if (isset($article->description)) {
			$this->replace($article->description);
		}
		if (isset($article->title)) {
			$this->replace($article->title);
		}
		if (isset($article->author)) {
			if (isset($article->author->name)) {
				$this->replace($article->author->name);
			} else if (is_string($article->author)) {
				$this->replace($article->author);
			}
		}
	}


	////////////////////////////////////////////////////////////////////
	// onAfterDispatch
	////////////////////////////////////////////////////////////////////

	function onAfterDispatch()
	{
		if (!$this->params->pass) {
			return;
		}

		if (JFactory::getDocument()->getType() != 'feed' && JFactory::getApplication()->input->get('option') != 'com_acymailing') {
			return;
		}

		// FEED
		if (isset(JFactory::getDocument()->items)) {
			for ($i = 0; $i < count(JFactory::getDocument()->items); $i++) {
				$this->onContentPrepare(JFactory::getDocument()->items[$i]);
			}
		}
	}

	////////////////////////////////////////////////////////////////////
	// onAfterRender
	////////////////////////////////////////////////////////////////////
	function onAfterRender()
	{
		if (!$this->params->pass) {
			return;
		}

		// not in pdf's
		if (JFactory::getDocument()->getType() == 'pdf') {
			return;
		}

		$html = JResponse::getBody();
		$this->replace($html);
		$this->cleanLeftoverJunk($html);
		JResponse::setBody($html);
	}

	function replace(&$str)
	{

		if (is_array($str)) {
			foreach ($str as $key => $val) {
				$str[$key] = $this->replaceReturn($val);
			}
		} else {
			$string_array = $this->protectString($str);
			foreach ($string_array as $i => $string) {
				if (!($i % 2)) {
					foreach ($this->params->sets as $set) {
						$this->replaceBySet($string_array[$i], $set);
					}
				}
			}
			$str = implode('', $string_array);
		}
	}

	function replaceReturn($str)
	{
		$this->replace($str);
		return $str;
	}

	function replaceBySet(&$str, &$params)
	{
		if( $this->params->https ){
			$http = 'https://';
		} else {
			$http = 'http://';
		}

		$this->replaceBySearches($str, $params->searches, $params, $http);

		if (!(strpos($str, '<script') === false)) {
			$regex = '#<script(?:\s+type=[^>]*)?>.*?</script>#si';
			if (preg_match_all($regex, $str, $strparts, PREG_SET_ORDER) > 0) {
				foreach ($strparts as $strpart) {
					$newstr = $strpart['0'];
					if ($this->replaceBySearches($newstr, $params->js_searches, $params, $http)) {
						$str = str_replace($strpart['0'], $newstr, $str);
					}
				}
			}
		}
	}

	function replaceBySearches(&$str, &$searches, &$params, $http)
	{
		$changed = 0;
		foreach ($searches as $search) {
			if (preg_match_all($search, $str, $matches, PREG_SET_ORDER) > 0) {
				foreach ($matches as $match) {
					$pass = 1;
					$file = trim($match['3']);
					if ($file) {
						foreach ($params->ignorefiles as $ignore) {
							if ($ignore && (!(strpos($file, $ignore) === false) || !(strpos(htmlentities($file), $ignore) === false))) {
								$pass = 0;
								break;
							}
						}
						if ($pass) {
							$cdn = $this->getCDN($file, $params->cdns, $http);
							$replace = $match['1'] . $cdn . '/' . $file . $match['4'];
							$str = str_replace($match['0'], $replace, $str);
							$changed = 1;
						}
					}
				}
			}
		}
		return $changed;
	}

	/*
	 * Searches are replaced by:
	 * '\1http(s)://' . $this->params->cdn . '/\3\4'
	 * \2 is used to reference the possible starting quote
	 */
	function getSearches(&$p, $attribs, $urls)
	{
		foreach ($urls as $url) {
			$r = '\s*' . str_replace('QUOTES', '"\'', $url) . '\s*';

			if ($p->enable_in_scripts) {
				$jsr = str_replace('LSLASH', '', $r);
				$p->js_searches[] = '#((["\']))' . $jsr . '(["\'])#i'; // "..."
			}

			$r = str_replace('LSLASH', '/?', $r);

			$p->searches[] = '#((?:' . $attribs . ')(["\']))' . $r . '(\2)#i'; // attrib="..."
			$p->searches[] = '#((?:' . $attribs . ')())' . $r . '([\s|>])#i'; // attrib=...
			$p->searches[] = '#(url\((["\']))' . $r . '(\2\))#i'; // url("...")
			// add ')' to the no quote checks
			$r = '\s*' . str_replace('QUOTES', '"\'\)', $url) . '\s*';
			$p->searches[] = '#(url\(())' . $r . '(\))#i'; // url(...)
		}
	}

	function getCDN($file, $cdns = array(''), $http)
	{
		return $http . $cdns['0'];
	}


	/**
	 * Just in case you can't figure the method name out: this cleans the left-over junk
	 */
	function cleanLeftoverJunk(&$str)
	{
		$str = str_replace(array('{nocdn}', '{/nocdn}'), '', $str);
	}

	function protectString($str)
	{
		if ($this->isEditPage()) {
			$str = preg_replace('#(<' . 'form [^>]*(id|name)="(adminForm|postform)".*?</form>)#si', '{nocdn}\1{/nocdn}', $str);
		}

		if (strpos($str, '{nocdn}') === false || strpos($str, '{/nocdn}') === false) {
			$str = str_replace(array('{nocdn}', '{/nocdn}'), '', $str);
			return array($str);
		}
		$str = str_replace(array('{nocdn}', '{/nocdn}'), '[[CDN_SPLIT]]', $str);
		return explode('[[CDN_SPLIT]]', $str);
	}

	public static function isEditPage()
	{
		$option = JFactory::getApplication()->input->get('option');
		// always return false for these components
		if (in_array($option, array('com_rsevents', 'com_rseventspro'))) {
			return 0;
		}

		$task = JFactory::getApplication()->input->get('task');
		$view = JFactory::getApplication()->input->get('view');
		if (!(strpos($task, '.') === false)) {
			$task = explode('.', $task);
			$task = array_pop($task);
		}
		if (!(strpos($view, '.') === false)) {
			$view = explode('.', $view);
			$view = array_pop($view);
		}

		return (
			in_array($task, array('edit', 'form', 'submission'))
			|| in_array($view, array('edit', 'form'))
			|| in_array(JFactory::getApplication()->input->get('do'), array('edit', 'form'))
			|| in_array(JFactory::getApplication()->input->get('layout'), array('edit', 'form', 'write'))
			|| in_array(JFactory::getApplication()->input->get('option'), array('com_contentsubmit', 'com_cckjseblod'))
			|| self::isAdmin()
		);
	}

	public static function isAdmin($block_login = 0)
	{
		$options = array('com_acymailing');
		if ($block_login) {
			$options[] = 'com_login';
		}
		return (
			JFactory::getApplication()->isAdmin()
			&& !in_array(JFactory::getApplication()->input->get('option'), $options)
			&& JFactory::getApplication()->input->get('task') != 'preview'
		);
	}

	public static function isProtectedPage($ext = '', $hastags = 0)
	{
		// return if disabled via url
		// return if current page is raw format
		// return if current page is NoNumber QuickPage
		// return if current page is a JoomFish or Josetta page
		return (
			($ext && JFactory::getApplication()->input->get('disable_' . $ext))
			|| JFactory::getApplication()->input->get('format') == 'raw'
			|| ($hastags
				&& (
					JFactory::getApplication()->input->getInt('nn_qp', 0)
					|| in_array(JFactory::getApplication()->input->get('option'), array('com_joomfishplus', 'com_josetta'))
				))
		);
	}
}