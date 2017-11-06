<?php 
//添加CMS分类
function update_post_term($post_id,$term_id){
		update_object_term($post_id,$term_id,'posts');
}
//删除CMS分类管理
function delete_post_term($post_id,$term_id){
		delete_object_term($post_id,$term_id,'posts');
}