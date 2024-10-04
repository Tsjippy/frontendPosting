# FILTERS
- apply_filters('sim_frontend_content_edit_rights', false, $postCategory)	
- apply_filters('post-edit-button', $buttonHtml, $post, $content);
- apply_filters( "content_template", $baseTemplate, 'content' );
- apply_filters('sim-frontend-content-js', array('sim_fileupload_script', 'sim_forms_script'));
- apply_filters('sim_attachment_preview', $image, $this->postId);
- apply_filters('sim-frontend-buttons', ob_get_clean(), $this);
- apply_filters('sim_frontend_content_edit_rights', $this->editRight, $this->postCategory);
- apply_filters('sim_frontend_posting_modals', ['attachment']);
- apply_filters('sim_post_content', $postContent);
- apply_filters('sim_frontend_content_validation', '', $this);
- apply_filters('sim_media_gallery_download_url', $url, $id);
- apply_filters('sim_media_gallery_download_filename', '', $type, $id);

# Actions
- 