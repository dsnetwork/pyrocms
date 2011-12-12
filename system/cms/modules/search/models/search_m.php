<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * This is the search module for PyroCMS
 *
 * It indexes content on your site as it is browsed by visitors
 *
 * @author 		Jerel Unruh - PyroCMS Dev Team
 * @package 	PyroCMS
 * @subpackage 	Search Module
 */
class Search_m extends MY_Model {

	public function __construct()
	{
		$this->_table = 'search';
		
		parent::__construct();
	}
	
	/**
	 * Index Content
	 *
	 * This adds the current output to the search table
	 *
	 * @param	string	$title	 The template title used in <title></title>
	 * @param	string	$content The content that will be output in the theme with {{ template:body }}
	 */
	public function index($title, $content)
	{
		$skip = array('system', 'addons', '404', 'search');
		
		// There's some pages we don't want to index.
		if ( /*! $this->current_user AND*/ ! in_array($this->uri->segment(1), $skip))
		{
			// strip all scripts, styles, html entities, and html tags
			$result = preg_replace('/(<(style|script)[^>]*>)(.*?)(<\/\2>)|(<[^<]+?>)|(&#?\w+?;)/ms', ' ', $content);
			// remove all duplicate white space
			$text = preg_replace('/(\s|&nbsp;)+/', ' ', $result);
			// hash the page content
			$content_hash = md5($text);
	
			$stored = $this->select('hash')
				->get_by('uri', $this->uri->uri_string());
	
			// we've previously indexed this page. Has it changed?
			if ($stored AND ($stored->hash !== $content_hash))
			{
				// out with the old and in with the new
				$this->update_by('hash',
								 $stored->hash,
								 array('uri' => $this->uri->uri_string(),
									   'module' => $this->module,
									   'title' => $title,
									   'output' => $text,
									   'hash' => $content_hash,
									   'updated_on' => now()
									   )
								 );			
			}
			elseif ( ! $stored)
			{
				// save it in the search index
				$this->insert(array('uri' => $this->uri->uri_string(),
											  'module' => $this->module,
											  'title' => $title,
											  'output' => $text,
											  'hash' => $content_hash,
											  'indexed_on' => now(),
											  'updated_on' => now()
											  )
							  );
			}
		}
	}
	
	public function search($term, $offset = 0, $limit = 0)
	{
		$term = $this->db->escape($term);
		$term = $this->security->xss_clean($term);

		$sql = "SELECT uri, title, output
					FROM ".$this->db->dbprefix('search')."
					WHERE MATCH (title, output) AGAINST (?) > 0
					LIMIT ? OFFSET ?";

		$results = $this->db->query($sql, array($term, (int) $limit, (int) $offset))
			->result();
			
		if ($results)
		{
			foreach ($results AS &$result)
			{
				// and show the introduction to the page
				$intro = wordwrap($result->output, 300, '<br/>');
				$result->intro = substr($intro, 0, strpos($intro, '<br/>'));
			}
		}
		
		return $results;
	}
 
	public function count_search_results($term)
	{
		$term = $this->db->escape($term);
		$term = $this->security->xss_clean($term);

		$sql = "SELECT COUNT(*) AS count
					FROM ".$this->db->dbprefix('search')."
					WHERE MATCH (title, output) AGAINST (?)";

		return $this->db->query($sql, array($term))
			->row()
			->count;
	}
}
