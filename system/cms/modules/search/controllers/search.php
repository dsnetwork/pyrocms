<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * This is the search module for PyroCMS
 *
 * It indexes content on your site as it's browsed
 * by visitors
 *
 * @author 		Jerel Unruh - PyroCMS Dev Team
 * @package 	PyroCMS
 * @subpackage 	Search Module
 */
class Search extends Public_Controller
{

	public function __construct()
	{
		parent::__construct();
		
		$this->lang->load('search');
	}
	
	private $_validation	= array(
		array(
			'field' => 'search_term',
			'label'	=> 'lang:search.term',
			'rules'	=> 'required|xss_clean|max_length[255]'
		)
	);

	public function index($query = '', $offset = 0)
	{
		$this->load->library('form_validation');
		$this->load->helper('form');

		// do we have a query passed in the url?
		if ($query > '') $_POST['search_term'] = urldecode($query);

		$term = $this->input->post('search_term');

		$this->form_validation->set_rules($this->_validation);

		if ($this->form_validation->run())
		{
			$data->results = $this->search_m->search($term, $offset, 1);
			$data->result_count = $this->search_m->count_search_results($term);

			$data->pagination = create_pagination('search/'.urlencode($term), $data->result_count, 1, 3);
		}

		foreach ($this->_validation AS $rule)
		{
			$data->{$rule['field']} = $this->input->post($rule['field']);
		}

		$this->template->title($this->module_details['name'], lang('search.search'))
						->build('index', $data);
	}
}