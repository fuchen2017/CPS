/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// %REMOVE_START%
	// The configuration options below are needed when running CKEditor from source files.
	config.plugins = 'dialogui,dialog,about,a11yhelp,dialogadvtab,basicstyles,bidi,blockquote,clipboard,button,panelbutton,panel,floatpanel,colorbutton,colordialog,templates,menu,contextmenu,div,resize,toolbar,elementspath,enterkey,entities,popup,filebrowser,find,fakeobjects,flash,floatingspace,listblock,richcombo,font,forms,format,horizontalrule,htmlwriter,iframe,wysiwygarea,image,indent,indentblock,indentlist,smiley,justify,link,list,liststyle,magicline,maximize,newpage,pagebreak,pastetext,pastefromword,preview,print,removeformat,save,selectall,showblocks,showborders,sourcearea,specialchar,menubutton,scayt,stylescombo,tab,table,tabletools,undo,wsc';
	config.skin = 'moonocolor';
	// %REMOVE_END%

	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	/*
		判斷網頁是在前端或是後端，若在前端須補上後端的位置	
	*/
	var l_href = $(location).attr('href');
	var url_dsc ="bcontroller/";
	if(l_href.search("bcontroller")!='-1'){
		url_dsc ="";
	}
	
	config.filebrowserBrowseUrl = url_dsc+'js/ckfinder/ckfinder.html';
	config.filebrowserImageBrowseUrl = url_dsc+'js/ckfinder/ckfinder.html?Type=Images';
	config.filebrowserFlashBrowseUrl = url_dsc+'js/ckfinder/ckfinder.html?Type=Flash';
	config.filebrowserUploadUrl = url_dsc+'js/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files'; //可上傳一般檔案
	config.filebrowserImageUploadUrl = url_dsc+'js/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images';//可上傳圖檔
	config.filebrowserFlashUploadUrl = url_dsc+'js/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash';//可上傳Flash檔案
	
};
