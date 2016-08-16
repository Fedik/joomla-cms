/**
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

;(function(tinyMCE, Joomla, $, window, document){
	"use strict";

	// This line is for Mootools b/c
	window.getSize = window.getSize || function(){return {x: $(window).width(), y: $(window).height()};};

	window.jInsertEditorText = function ( text, editor ) {
		tinyMCE.activeEditor.execCommand('mceInsertContent', false, text);
	};

	/**
	 * Find all TinyMCE elements and initialize TinyMCE instance for each
	 */
	Joomla.setupEditorsTinyMCE = Joomla.setupEditorsTinyMCE || function(target){
			target = target || document;
			var $editors = $(target).find('.joomla-editor-tinymce');

			for(var i = 0, l = $editors.length; i < l; i++) {
				Joomla.initializeEditorTinyMCE($editors[i]);
			}
		}

	/**
	 * Initialize TinyMCE instance
	 */
	Joomla.initializeEditorTinyMCE = Joomla.initializeEditorTinyMCE || function (element) {
			var name = element ? $(element).attr('name').replace(/\[\]|\]/g, '').split('[').pop() : 'default', // Get Editor name
				options = JSON.parse(element.getAttribute('data-options')); // Check specific options by the name

			if (element) {
				options.selector = null;
				options.target   = element;
			}

			if (options.setupCallbacString && !options.setup) {
				options.setup = new Function('editor', options.setupCallbacString);
			}

			tinyMCE.init(options);
		};

	// Init on doomready
	$(document).ready(function(){
		Joomla.setupEditorsTinyMCE();

		if (typeof window.jModalClose_no_tinyMCE === 'undefined')
		{
			window.jModalClose_no_tinyMCE = typeof(jModalClose) == 'function'  ?  jModalClose  :  false;

			jModalClose = function () {
				if (window.jModalClose_no_tinyMCE) window.jModalClose_no_tinyMCE.apply(this, arguments);
				tinyMCE.activeEditor.windowManager.close();
			};
		}

		if (typeof window.SqueezeBoxClose_no_tinyMCE === 'undefined')
		{
			if (typeof(SqueezeBox) == 'undefined')  SqueezeBox = {};
			window.SqueezeBoxClose_no_tinyMCE = typeof(SqueezeBox.close) == 'function'  ?  SqueezeBox.close  :  false;

			SqueezeBox.close = function () {
				if (window.SqueezeBoxClose_no_tinyMCE)  window.SqueezeBoxClose_no_tinyMCE.apply(this, arguments);
				tinyMCE.activeEditor.windowManager.close();
			};
		}

		// Init in subform field
		$(document).on('subform-row-add', function(event, row){
			Joomla.setupEditorsTinyMCE(row);
		})
	});

}(tinyMCE, Joomla, jQuery, window, document));