<?php defined('BASEPATH') or exit('No direct script access allowed');

class Module_Search extends Module {

	public $version = '1.0';

	public function info()
	{
		return array(
			'name' => array(
				'en' => 'Search'
			),
			'description' => array(
				'en' => 'This is a site wide search module that indexes all public pages including add-on modules'
			),
			'frontend' => TRUE,
			'backend' => FALSE,
		);
	}

	public function install()
	{
		$this->dbforge->drop_table('search');

		$search = "
			CREATE TABLE ".$this->db->dbprefix('search')."
			(
			  uri VARCHAR(255) NOT NULL DEFAULT '',
			  module VARCHAR(50) NOT NULL DEFAULT '',
			  title VARCHAR(255) NOT NULL DEFAULT '',
			  output TEXT NOT NULL,
			  hash VARCHAR(40) NOT NULL DEFAULT '',
			  indexed_on int(11) NOT NULL DEFAULT 0,
			  updated_on int(11) NOT NULL DEFAULT 0,
			  PRIMARY KEY (uri),
			  KEY `hash` (`hash`),
			  FULLTEXT (title, output)
			) ENGINE=MyISAM COLLATE=utf8_unicode_ci;
		";
		
		$search_active = array(
			'slug' => 'search_active',
			'title' => 'Activate Search',
			'description' => 'Would you like to index the content of your website for site wide search?',
			'`default`' => '1',
			'`value`' => '1',
			'type' => 'select',
			'`options`' => '1=Yes|0=No',
			'is_required' => 1,
			'is_gui' => 1,
			'module' => 'search'
		);
		
		if ($this->db->query($search) AND $this->db->query($search_active))
		{
			return TRUE;
		}
	}

	public function uninstall()
	{
		$this->dbforge->drop_table('search');
	}


	public function upgrade($old_version)
	{
		// Your Upgrade Logic
		return TRUE;
	}

	public function help()
	{
		// Return a string containing help info
		// You could include a file and return it here.
		return "No documentation has been added for this module.<br />Contact the module developer for assistance.";
	}
}
/* End of file details.php */
