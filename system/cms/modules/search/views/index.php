<div class="search-container">

	<h2><?php echo lang('search.search'); ?></h2>
	
	<?php echo form_open(site_url('search')) ?>
	
		<div class="search">
			<?php echo form_label(lang('search.term').': ', 'search_term'); ?>
			<?php echo form_input('search_term', set_value('search_term', $search_term)); ?>
			<?php echo form_submit('submit', lang('search.search')); ?>
		</div>

	<?php echo form_close(); ?>
	
	<?php if (isset($results)): ?>
		<div class="search-results-count">
			<?php echo sprintf(lang('search.count'), $search_term, $result_count); ?>
		</div>

		<ul class="search-results">
			<?php foreach ($results AS $result): ?>
				<li>
					<div class="title">
						<strong><?php echo lang('search.title').':</strong> '.$result->title; ?>
					</div>
					<div class="link">
						<strong><?php echo lang('search.link').':</strong> '.anchor($result->uri, site_url($result->uri)); ?>
					</div>
					<div class="intro">
						<strong><?php echo lang('search.intro').':</strong> '.$result->intro; ?>...
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
		
		<?php echo $pagination['links']; ?>
	<?php endif; ?>
</div>