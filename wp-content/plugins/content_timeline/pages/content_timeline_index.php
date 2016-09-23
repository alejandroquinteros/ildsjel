<div class="wrap">
	<h2>Timelines
		<?php 
		global $wp_my_timeline_wpml_deafult_lang;
		global $wp_my_timeline_has_wmpl;
		global $wp_my_timeline_wpml_languages;
		if(empty($wp_my_timeline_has_wmpl)){?>
			<a href="<?php echo admin_url( "admin.php?page=contenttimeline_edit" ); ?>" class="add-new-h2">Add New</a>
			<?php 
		}else {
			

			foreach( $wp_my_timeline_wpml_languages as $k123=>$v123){
				?>
						
						<a class="add-new-h2" href="<?php echo admin_url( "admin.php?page=contenttimeline_edit&lang=".$v123['code']);?>"><?php echo __("Add new in",'my_content_timeline').' - ';echo $v123['display_name'];?></a>
						<?php 
					}
					}
			?>
	</h2>
<?php

?>


<table class="wp-list-table widefat fixed">
	<thead>
		<tr>
			<th width="5%">ID</th>
			<th width="30%">Name</th>
			<?php if(empty($wp_my_timeline_has_wmpl)){?>
			<th width="60%">Shortcode</th>
			<?php }else {?>
			<th width="40%">Shortcode</th>
			<th width="20%">Language</th>
			
			<?php }?>
			<th width="20%">Actions</th>					
		</tr>
	</thead>
	
	<tfoot>
		<tr>
			<th>ID</th>
			<th>Name</th>
			<?php if(empty($wp_my_timeline_has_wmpl)){?>
			<th>Shortcode</th>
			<?php }else {?>
			<th>Shortcode</th>
			<th>Language</th>
			<?php }?>
			<th>Actions</th>					
		</tr>
	</tfoot>
	
	<tbody>
		<?php 
			global $wpdb;
			$prefix = $wpdb->prefix;

			if(isset($_GET['action']) && $_GET['action'] == 'delete') {
				$wpdb->query('DELETE FROM '. $prefix . 'ctimelines WHERE id = '.$_GET['id']);
			}
			$timelines = $wpdb->get_results("SELECT * FROM " . $prefix . "ctimelines ORDER BY id");
			if (count($timelines) == 0) {
				echo '<tr>'.
						 '<td colspan="100%">No timelines found.</td>'.
					 '</tr>';
			} else {
				$tname;
				$my_lang_name='';
				
				foreach ($timelines as $timeline) {
					if(!empty($wp_my_timeline_has_wmpl)){
						foreach(explode('||',$timeline->settings) as $val) {
							$expl = explode('::',$val);
							if($expl[0]=='my_lang'){
								$my_lang_123=$expl[1];
								foreach( $wp_my_timeline_wpml_languages as $k123=>$v123){
									if($my_lang_123==$v123['code']){
										$my_lang_name='<td>'.$v123['display_name'].'</td>';
								break;
							}
						}
				break;
					}
				}
			}
					$tname = $timeline->name;
					if(!$tname) {
						$tname = 'Timeline #' . $timeline->id . ' (untitled)';
					}
					echo '<tr>'.
							'<td>' . $timeline->id . '</td>'.						
							'<td>' . '<a href="' . admin_url('admin.php?page=contenttimeline_edit&id=' . $timeline->id) . '" title="Edit">'.$tname.'</a>' . '</td>'.
							'<td> [content_timeline id="' . $timeline->id . '"]</td>' .$my_lang_name.				
							'<td>' . '<a href="' . admin_url('admin.php?page=contenttimeline_edit&id=' . $timeline->id) . '" title="Edit this item">Edit</a> | '.									  
								  '<a href="' . admin_url('admin.php?page=contenttimeline&action=delete&id='  . $timeline->id) . '" title="Delete this item" >Delete</a> | '.
								  '<a href="'.admin_url('admin.php?page=contenttimeline_edit&my_clone_s=' . $timeline->id).'">Clone Timeline Settings</a> | '.
								  '<a href="'.admin_url('admin.php?page=contenttimeline_edit&my_clone=' . $timeline->id).'">Clone Full Timeline</a>'.
								  
							'</td>'.														
						'</tr>';
				}
			}
		?>
		
	</tbody>		 
</table>
<div style="margin-top:20px;">

<h2>Step by step:</h2>
<ul>
	<li><h3>1. Click on "Add New button"</h3></li>
	<li><h3>2. Setup your timeline, and click Save</h3></li>
	<li><h3>3. Copy "shortcode" from table and use it in your post or page. (for adding timeline into .php parts of template use it like this "&lt;?php do_shortcode('[content_timeline id="X"]') ?&gt;" where X is id of your timeline)</h3></li>

</ul>
</div>
</div>
<?php

?>