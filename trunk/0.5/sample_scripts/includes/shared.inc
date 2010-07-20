<?php
/*
 * Created on May 30, 2008
 */


require_once(dirname(__FILE__) .'/../../public_html/fckeditor/fckeditor.php');
$inputName = 'wysiwyg';
$myFilename = 'test.tmpl';



function add_editor_content($page, $inputName=NULL, $initialData=NULL) {
	if(is_null($inputName) || !strlen($inputName)) {
		$inputName = 'FCKeditor1';
	}
	if(is_null($initialData) || !strlen($initialData)) {
		$initialData = "";
	}
	ob_start(); {
		$oFCKeditor = new FCKeditor($inputName) ;
		$oFCKeditor->BasePath = '/fckeditor/' ;
		$oFCKeditor->Width="100%";
		$oFCKeditor->Height="100%";
		$oFCKeditor->Value = $initialData;
		
		//set some options for the interface..
		$oFCKeditor->ToolbarSet = 'CS';
		
		
		$oFCKeditor->Create() ;
		$myContent = ob_get_contents();
	}
	ob_end_clean();
	
	$page->add_template_var('entryEditor', $page->templateRows['entryEditor']);
	$page->add_template_var('wysiwyg_editor', $myContent);
}//end add_editor_content()


?>
