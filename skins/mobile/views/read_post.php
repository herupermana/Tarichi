<?php $this->load->view($site_skin.'/top'); ?>
<script type="text/javascript" src="<?php echo $base_site_url; ?>jquery/jquery-1.5.min.js"></script>

<script type="text/javascript" src="<?php echo $base_site_url; ?>jquery/jquery.scrollTo.js"></script>

    <div class="the_post span-16">
        
        <h3><?php echo $sp_title; ?></h3>
        <div class="post_info span-16">
            <span class="post_date"><?php echo $sp_date; ?></span><br />
            <span class="post_tags">Tags: <?php echo $sp_tags; ?></span><br />
            <a href="javascript:void(0);" style="text-decoration:none;" class="go_to_comment" title="Lihat Komentar"><span class="post_comment"><?php echo $sp_totalcomment; ?> Comments</span></a>
        </div>
        <?php echo $sp_content; ?>
    
     	<!--- comments here -->
        <div class="total_comment span-8">
            <?php echo $sp_totalcomment; ?> Komentar
        </div>
        <div class="give_comment span-8 last">
            <a href="#comment_box" class="give_combtn">Tinggalkan Komentar</a>
        </div>
 		<?php if ($sp_totalcomment != 0) { ?>
        <div class="the_comment span-16" id="comment_list">
		<?php $i = 0;
            foreach ($comments as $the_comments) {
                $i++; ?> 
        	<div class="comment_box" id="comment_<?php echo $the_comments['id']; ?>">
                <div class="comment_title span-16" >
                    <div class="span-13">Dari : <?php echo $the_comments['sender']; ?> | <span class="small"> <?php echo $the_comments['date']; ?></span> |
                    <a href="javascript:void(0);" class="balas_comment" title="<?php echo $the_comments['id']; ?>">Balas</a></div>
                </div>
                <div style="float:left;">
               
                    <img src="<?php echo $this->template_model->get_gravatar($the_comments['email']); ?>" height="60" width="60" style="padding:3px;border-radius:3px;float:left;padding-right:10px;" />
					<?php echo $the_comments['content']; ?>
                </div>
                <div style="clear:both;">
					<?php if ($the_comments['subcomment'] != 0) { ?>
                        <?php foreach ($the_comments['child'] as $childcomment) { ?>
                           <div class="reply_comment" id="comment_<?php echo $childcomment['id']; ?>"> 
						   		<div class="span-12">
                                	<span>
                                    	balasan oleh <?php echo $childcomment['sender']; ?> @ <?php echo $childcomment['date']; ?>
                                        <br />
										<?php echo $childcomment['content']; ?>
                                    </span>
                                </div>
                                <div class="span-2 last" style="text-align:right">
                                	 <img src="<?php echo $this->template_model->get_gravatar($childcomment['email']); ?>" height="30" width="30" style="margin:10px;padding:1px;background:#666;border-radius:3px;" />
                                </div>
                           </div>
                        <?php } ?>
                    <?php } ?>
                </div>
                
           	</div>
        <?php
            } ?>
        </div>
        <?php } ?>
        <?php if ($sp_comment == 0) { ?>
        <script type="text/javascript">
			$(document).ready(function(){
				$('.go_to_comment').click(function(){
					$.scrollTo('#comment_list',500);
				});
				$('.balas_comment').click(function(){
					var comment_id=$(this).attr('title');
					$('#reply_for').fadeIn();
					$('#reply_for').html('<span class="success" style="display:block;">* Membalas Comment | <a href="javascript:void(0);" class="batal_balas" title="'+ comment_id +'">Batalkan</a></span>');
					$.scrollTo('#comment_box',500);
					$('#parent_comment_id').val(comment_id);
				});
				
				$('.batal_balas').live('click',function(){
					$('.parent_comment_id').val('0');
					$.scrollTo('#comment_'+$(this).attr('title'),500);
					$('#reply_for').fadeOut();
					
				});
				
				$('#comment_content').keyup(function(){
					var charnya=$(this).val();
					var totalchar=charnya.length;
					var sisachar=500-totalchar;
					$('.total_char').html(sisachar);
				});
				
				$('form').submit(function(){
					var caphid=$('#captchatexthid').val();
					var cap=$('#captchatext').val();	
					if(cap==caphid)
					{
						
						return true
					}
					else
					{
						alert('wrong char received');
						var jqxhr = $.getJSON("<?php echo site_url('skin_engine/read_post/refresh_captcha'); ?>", function(data) {
							
  							$('#captchatexthid').val(data.word);
							$('#capimage').attr('src','<?php echo base_url(); ?>captcha/'+data.time+'.jpg');
							$('#captchatext').val('');
						});
						return false;
					}
					
				});
				
			});
		</script>
        <div class="the_comment span-16" id="comment_box">
        	<div class="span-16" id="reply_for" style="background:#093;padding:10px;display:none;float:left;">
            	
            </div>
			<?php echo form_open('skin_engine/read_post/send_comment/'.$sp_id); ?>
            
                <div class="span-5" style="clear:both;">
                    <label>Nama</label><br />
                    <input type="text" name="comment_name" id="comment_name" />
                    <input type="hidden" name="parent_comment_id" id="parent_comment_id" value="0" />
                    <br />
                    <label>Email</label><br />
                    <input type="text" name="comment_email" id="comment_email" /><br />
                    <label>Website</label><br />
                    <input type="text" name="comment_website" id="comment_website" /><br />
                    <label>Tuliskan</label><img src="<?php echo base_url(); ?>captcha/<?php echo $captcha['time']; ?>.jpg" id="capimage" /><br />
                    <input type="hidden" name="captchatexthid" id="captchatexthid" value="<?php echo $captcha['word']; ?>" />
                    <input type="text" name="captchatext" id="captchatext" />
                    <input type="hidden" name="uristring" id="uristring" value="<?php echo $this->uri->uri_string(); ?>" />
                   
                </div>
                <div class="span-11 last">
                	yang boleh : &lt;a&gt;
                    <textarea name="comment_content" id="comment_content" style="width:97%;"></textarea>
                    Karakter tersisa <span class="total_char">500</span>
                     <input type="submit" value="Kirim" name="kirim" id="kirim" />
                </div>
            <?php echo form_close(); ?>
        </div>
        <?php } ?>
        <!--- end of comments -->
        
    </div>
    

    

<?php $this->load->view($site_skin.'/footer'); ?>