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
class Search_log_m extends MY_Model {

	public function __construct()
	{
		parent::__construct();
	}
	
    public function cleanup()
    {
		// keep them for a week
		$expiration = now() - 604800;

		return $this->delete_by('time <', $expiration);
    }
}