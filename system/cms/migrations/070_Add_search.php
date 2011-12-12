<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_search extends CI_Migration {

	public function up()
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

	public function down()
	{
		return $this->dbforge->drop_table('search');
	}
}