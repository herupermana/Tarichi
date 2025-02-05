<?php $this->load->view('the_master/top'); ?>	
<script type="text/javascript">
	$(document).ready(function(){
			$('#loader_image').hide();
			$('#save').click(function(){
					$('#loader_image').show();
					var s_name = $('#site_name').val();
					var s_slogan = $('#site_slogan').val();
					var s_date_format = $('#site_date_format').val();
					var s_default_keywords = $('#site_default_keywords').val();
					var s_default_description = $('#site_default_description').val();
					var s_home_page_type = $('#site_home_page_type').val();
					var s_per_page_post = $('#site_per_page_post').val();
					var s_status = $('#site_status').val();
					var s_comment_moderation = $('#site_comment_moderation').val();
					var s_main_email = $('#site_main_email').val();
					var s_split_post = $('#site_split_post').val();
					var s_language = $('#site_language').val();
					$.post('<?php echo site_url('ajax_things/save_site_config'); ?>',{
										  site_name : s_name,
										  site_slogan  :s_slogan,
										  site_date_format : s_date_format,
										  site_default_keywords  : s_default_keywords,
										  site_default_description : s_default_description,
										  site_home_page_type : s_home_page_type,
										  site_per_page_post : s_per_page_post,
										  site_status : s_status,
										  site_comment_moderation : s_comment_moderation,
										  site_main_email : s_main_email,
										  site_split_post : s_split_post,
										  site_language : s_language
									   }, function(data){
										   $('#loader_image').hide();
									   	});
					
									  });
							   });
</script>


	
    
    <div class="tigaperempat formbox">
    	<div class="formboxtitle">
        	<?php echo lang('caption_site_configuration'); ?>
        </div>
        <div class="formboxitem">
            <label><?php echo lang('caption_site_name'); ?></label><br />
            <input type="text" name="site_name" id="site_name" style="width:95%;" value="<?php echo $row->site_name; ?>" />
        </div>
        <div class="formboxitem">
            <label><?php echo lang('caption_site_slogan'); ?></label><br />
            <input type="text" name="site_slogan" id="site_slogan" style="width:95%;" value="<?php echo $row->site_slogan; ?>" />
        </div>
        <div class="formboxitem">
            <label><?php echo lang('caption_date_format'); ?></label><br />
            <input type="text" name="site_date_format" id="site_date_format" value="<?php echo $row->site_date_format; ?>" />
        </div>
        <div class="formboxitem">
        <label><?php echo lang('caption_default_keywords'); ?></label><br />
        <input type="text" name="site_default_keywords" id="site_default_keywords" value="<?php echo $row->site_default_keywords; ?>" style="width:95%" /><br />
        </div>
        <div class="formboxitem">
            <label><?php echo lang('caption_default_descriptions'); ?></label><br />
            <textarea name="site_default_description" id="site_default_description" style="width:95%;"><?php echo $row->site_default_description; ?></textarea>
        </div>
   	</div>
    <div class="seperempat last formbox">
    	<div class="formboxtitle">
        	<?php echo lang('caption_extra_config'); ?>
        </div>
        <div class="formboxitem">
        	<label><?php echo lang('caption_language'); ?></label><br />
            <?php echo form_dropdown('site_language', $the_lang, $row->site_language, 'id="site_language"'); ?>
        </div>
        <div class="formboxitem">
             <label><?php echo lang('caption_home_page_type'); ?></label><br />
            <?php
                $site_home_page_type = ['1'=>'Blog Style', '2'=>'Static Page Style'];
                echo form_dropdown('site_home_page_type', $site_home_page_type, $row->site_home_page_type, 'id="site_home_page_type"');
            ?>
        </div>
        <div class="formboxitem">
            <label><?php echo lang('caption_total_post_per_page'); ?></label><br />
            <input type="text" name="site_per_page_post" id="site_per_page_post" value="<?php echo $row->site_per_page_post; ?>" />
        </div>
        <div class="formboxitem">
            <label><?php echo lang('caption_site_status'); ?></label><br />
            <?php
                $site_status = ['1'=>'Available', '2'=>'Maintenance', '3'=>'On Upgrade'];
                echo form_dropdown('site_status', $site_status, $row->site_status, 'id="site_status"');
            ?>
        </div>
        <div class="formboxitem">
            <label><?php echo lang('caption_comment_moderation'); ?></label><br />
            <?php $site_comment_moderation = ['0'=>'OFF', '1'=>'ON'];
                echo form_dropdown('site_comment_moderation', $site_comment_moderation, $row->site_comment_moderation, 'id="site_comment_moderation"');
            ?>
        </div>
        <div class="formboxitem">
            <label><?php echo lang('caption_main_email'); ?></label><br />
            <input type="text" name="site_main_email" value="<?php echo $row->site_main_email; ?>" id="site_main_email" />
        </div>
        <div class="formboxitem">
            <label><?php echo lang('caption_total_char_to_split_post'); ?></label><br />
            <input type="text" name="site_split_post" value="<?php echo $row->site_split_post; ?>" id="site_split_post" /><br />
            <span class="small">* give 0(zero) to split using TinyMCE Splitter</span>
        </div>
        <div class="formboxitem">
           <a href="javascript:void(0);" class="savebtn" id="save"><?php echo lang('caption_save'); ?></a>
        </div>
	</div>
    
    <div id="loader_image" style="position:fixed;left:45%;top:40%;width:100px;height:50px;text-align:center;" class="the_page_item">
    	 	<img src="<?php echo base_url(); ?>assets/blueprint/images/ajax_start.gif" /> <?php echo lang('caption_saving'); ?> ...
        </div>
<?php $this->load->view('the_master/footer'); ?>