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

	private $_matches = array();

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
		// they've specified only to index when a bot loads the page.
		if (Settings::get('search_index') == 'bots')
		{
			$this->load->library('user_agent');
			// it's not a bot - run!
			if ( ! $this->agent->is_robot())
			{
				return TRUE;
			}
		}

		$skip = array('system', 'addons', '404', 'search');
		
		// There's some pages we don't want to index. Restricted pages, file missing, 404...
		if ( ! $this->current_user AND ! in_array($this->uri->segment(1), $skip))
		{
			// strip all scripts, styles, html entities, and html tags
			$result = preg_replace('/(<(style|script)[^>]*>)(.*?)(<\/\2>)|(<[^<]+?>)|(&#?\w+?;)/ms', ' ', $content);
			// remove all duplicate white space
			$text = preg_replace('/(\s|&nbsp;)+/', ' ', $result);
			// hash the page content
			$content_hash = md5($text);
	
			// see if we have it indexed
			$stored = $this->select('hash')
				->get_by('uri', $this->uri->uri_string());
	
			// we've previously indexed this page but has it changed?
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
	
	/**
	 * Perform the fulltext search
	 *
	 * @param	string	$term	The search term
	 * @param	int		$offset
	 * @param	int		$limit
	 *
	 * @return	mixed
	 */
	public function search($term, $offset = 0, $limit = 0)
	{
		$sql = "SELECT uri, title, output, hash
					FROM ".$this->db->dbprefix('search')."
					WHERE MATCH (title, output) AGAINST (?) > 0
					LIMIT ?, ?
					";

		$results = $this->db->query($sql, array($term, (int) $offset, (int) $limit))
			->result();
			
		if ($results)
		{
			foreach ($results AS &$result)
			{
				// and show the introduction to the page
				$intro = wordwrap($result->output, 300, '<br/>');
				$result->intro = substr($intro, 0, strpos($intro, '<br/>'));
				
				$this->_matches[] = $result->hash;
			}

			$data = array('term' => $term,
						  'matches' => implode(',', $this->_matches),
						  'ip_address' => $this->input->ip_address(),
						  'time' => now()
						  );

			// log the search results
			if ($this->search_log_m->get_by('term', $term))
			{
				$this->search_log_m->update_by('term', $term, $data);
			}
			else
			{
				$this->search_log_m->insert($data);
			}
			
			// delete old searches
			$this->search_log_m->cleanup();
		}
		
		return $results;
	}
 
	public function count_search_results($term)
	{
		$sql = "SELECT COUNT(*) AS count
					FROM ".$this->db->dbprefix('search')."
					WHERE MATCH (title, output) AGAINST (?)";

		return $this->db->query($sql, array($term))
			->row()
			->count;
	}
}
