/**
 * @license Copyright (c) 2003-2017, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	/* 22-09-2017 -add start plugins --*/
	
	//config.extraPlugins = 'uploadimage';
	//config.extraPlugins = 'uploadwidget';
	//config.extraPlugins = 'widget';
	//config.extraPlugins = 'clipboard';
	//config.extraPlugins = 'filetools';
	//config.extraPlugins= 'imgbrowse',
	//config.filebrowserImageBrowseLinkUrl = '';
	/* 22-09-2017 -add end plugins --*/
	//config.extraPlugins = 'imageuploader';
	//config.extraPlugins = 'embed';
	config.allowedContent = true;
	/* commented on 23-10-2017 config.removeButtons = 'JustifyCenter,BGColor,Maximize,ShowBlocks,Preview,Print,About,SelectAll,HiddenField,Source,NewPage,Language,JustifyBlock,JustifyLeft,JustifyRight,BidiLtr,BidiRtl,Styles,Format,Iframe'; */
	config.removeButtons = 'BGColor,Maximize,ShowBlocks,Preview,Print,About,SelectAll,HiddenField,NewPage,Language,BidiLtr,BidiRtl,Iframe';
	config.removeDialogTabs = 'image:advanced;image:Link;link:advanced;link:upload;';
	config.removePlugins = 'elementspath,save,forms,find,smiley,specialchar,templates,wsc,magicline,preview,pagebreak,magicline,flash,div,a11yhelp,about,copyformatting,magicline,liststyle,wsc'; 
	/* commented on 23-10-2017 config.removePlugins = 'elementspath,save,font,forms,find,smiley,specialchar,table,tableselection,templates,wsc,magicline,tabletools,scayt,preview,pagebreak,magicline,flash,div,a11yhelp,about,copyformatting,magicline,liststyle,wsc,blockquote'; */
};
