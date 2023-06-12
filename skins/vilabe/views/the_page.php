<?php $this->load->view($site_skin.'/top'); ?>
<div id="left_content" class="span-16">
	<div class="the_post span-16">
    	<h3><?php echo $page_title; ?></h3>
        <?php echo $page_content; ?>
    </div>
</div>
<?php $this->load->view($site_skin.'/sidebar'); ?>
<?php $this->load->view($site_skin.'/footer'); ?>