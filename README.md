This plugin makes it possible to add and edit pages, posts and custom post types.<br>

== Description ==
This plugin adds the possibility to edit and create simple content on the frontend.<br>
For some end-users the block editor can be overwhelming, this plugin adds a more simple alternative.
Just add the "Frontend Posting Block" to add the functionality.<br>
An overview of the posts created by the current user can be displayed using the "User Posts" block.<br>
If anyone without publish rights addd or editd a post, it will be stored as pending, allowing the content to be reviewed before publishing.<br>
An overview of pending content can be shown using the "Pending Posts" block.<br>
This plugin also adds a custom post status: archived. Meaning a post is not visible but still kept for reference
	

== Hooks ==
# FILTERS
- apply_filters('sim_frontend_content_edit_rights', false, $postCategory)	
- apply_filters('post-edit-button', $buttonHtml, $post, $content);
- apply_filters( "content_template", $baseTemplate, 'content' );
- apply_filters('sim-frontend-content-js', array('sim_fileupload_script', 'sim_forms_script'));
- apply_filters('sim_attachment_preview', $image, $this->postId);
- apply_filters('sim-frontend-buttons', ob_get_clean(), $this);
- apply_filters('sim_frontend_content_edit_rights', $this->editRight, $this->postCategory);
- apply_filters('sim_post_content', $postContent);
- apply_filters('sim_frontend_content_validation', '', $this);
- apply_filters('sim_media_gallery_download_url', $url, $id);
- apply_filters('sim_media_gallery_download_filename', '', $type, $id);