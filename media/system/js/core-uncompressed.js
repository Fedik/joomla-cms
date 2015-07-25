/**
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// Only define the Joomla namespace if not defined.
Joomla = window.Joomla || {};

// Only define editors if not defined
Joomla.editors = Joomla.editors || {};

// An object to hold each editor instance on page, only define if not defined.
Joomla.editors.instances = Joomla.editors.instances || {};

(function( Joomla, document ) {
	"use strict";

	/**
	 * Generic submit form
	 */
	Joomla.submitform = function(task, form, validate) {
		if (!form) {
			form = document.getElementById('adminForm');
		}

		if (task) {
			form.task.value = task;
		}

		// Toggle HTML5 validation
		form.noValidate = !validate;

		// Submit the form.
		// Create the input type="submit"
		var button = document.createElement('input');
		button.style.display = 'none';
		button.type = 'submit';

		// Append it and click it
		form.appendChild(button).click();

		// If "submit" was prevented, make sure we don't get a build up of buttons
		form.removeChild(button);
	};

	/**
	 * Default function. Usually would be overriden by the component
	 */
	Joomla.submitbutton = function( pressbutton ) {
		Joomla.submitform( pressbutton );
	};

	/**
	 * Custom behavior for JavaScript I18N in Joomla! 1.6
	 *
	 * Allows you to call Joomla.JText._() to get a translated JavaScript string pushed in with JText::script() in Joomla.
	 */
	Joomla.JText = {
		strings: {},
		'_': function( key, def ) {
			return typeof this.strings[ key.toUpperCase() ] !== 'undefined' ? this.strings[ key.toUpperCase() ] : def;
		},
		load: function( object ) {
			for ( var key in object ) {
				if (!object.hasOwnProperty(key)) continue;
				this.strings[ key.toUpperCase() ] = object[ key ];
			}
			return this;
		}
	};

	/**
	 * Method to replace all request tokens on the page with a new one.
	 * Used in Joomla Installation
	 */
	Joomla.replaceTokens = function( newToken ) {
		if (!/^[0-9A-F]{32}$/i.test(newToken)) { return; }

		var els = document.getElementsByTagName( 'input' ),
			i, el, n;

		for ( i = 0, n = els.length; i < n; i++ ) {
			el = els[i];

			if ( el.type == 'hidden' && el.value == '1' && el.name.length == 32 ) {
				el.name = newToken;
			}
		}
	};

	/**
	 * USED IN: administrator/components/com_banners/views/client/tmpl/default.php
	 * Actually, probably not used anywhere. Can we deprecate in favor of <input type="email">?
	 *
	 * Verifies if the string is in a valid email format
	 *
	 * @param string
	 * @return boolean
	 */
	Joomla.isEmail = function( text ) {
		var regex = /^[\w.!#$%&‚Äô*+\/=?^`{|}~-]+@[a-z0-9-]+(?:\.[a-z0-9-]{2,})+$/i;
		return regex.test( text );
	};

	/**
	 * USED IN: all list forms.
	 *
	 * Toggles the check state of a group of boxes
	 *
	 * Checkboxes must have an id attribute in the form cb0, cb1...
	 *
	 * @param   mixed   The number of box to 'check', for a checkbox element
	 * @param   string  An alternative field name
	 */
	Joomla.checkAll = function( checkbox, stub ) {
		if (!checkbox.form) return false;

		stub = stub ? stub : 'cb';

		var c = 0,
			i, e, n;

		for ( i = 0, n = checkbox.form.elements.length; i < n; i++ ) {
			e = checkbox.form.elements[ i ];

			if ( e.type == checkbox.type && e.id.indexOf( stub ) === 0 ) {
				e.checked = checkbox.checked;
				c += e.checked ? 1 : 0;
			}
		}

		if ( checkbox.form.boxchecked ) {
			checkbox.form.boxchecked.value = c;
		}

		return true;
	};

	/**
	 * Render messages send via JSON
	 * Used by some javascripts such as validate.js
	 *
	 * @param   object  messages    JavaScript object containing the messages to render. Example:
	 *                              var messages = {
	 *                              	"message": ["Message one", "Message two"],
	 *                              	"error": ["Error one", "Error two"]
	 *                              };
	 * @return  void
	 */
	Joomla.renderMessages = function( messages ) {
		Joomla.removeMessages();

		var messageContainer = document.getElementById( 'system-message-container' ),
			type, typeMessages, messagesBox, title, titleWrapper, i, messageWrapper;

		for ( type in messages ) {
			if ( !messages.hasOwnProperty( type ) ) { continue; }
			// Array of messages of this type
			typeMessages = messages[ type ];

			// Create the alert box
			messagesBox = document.createElement( 'div' );
			messagesBox.className = 'alert alert-' + type;

			// Title
			title = Joomla.JText._( type );

			// Skip titles with untranslated strings
			if ( typeof title != 'undefined' ) {
				titleWrapper = document.createElement( 'h4' );
				titleWrapper.className = 'alert-heading';
				titleWrapper.innerHTML = Joomla.JText._( type );

				messagesBox.appendChild( titleWrapper );
			}

			// Add messages to the message box
			for ( i = typeMessages.length - 1; i >= 0; i-- ) {
				messageWrapper = document.createElement( 'p' );
				messageWrapper.innerHTML = typeMessages[ i ];
				messagesBox.appendChild( messageWrapper );
			}

			messageContainer.appendChild( messagesBox );
		}
	};


	/**
	 * Remove messages
	 *
	 * @return  void
	 */
	Joomla.removeMessages = function() {
		var messageContainer = document.getElementById( 'system-message-container' );

		// Empty container with a while for Chrome performance issues
		while ( messageContainer.firstChild ) messageContainer.removeChild( messageContainer.firstChild );

		// Fix Chrome bug not updating element height
		messageContainer.style.display = 'none';
		messageContainer.offsetHeight;
		messageContainer.style.display = '';
	};

	/**
	 * USED IN: administrator/components/com_cache/views/cache/tmpl/default.php
	 * administrator/components/com_installer/views/discover/tmpl/default_item.php
	 * administrator/components/com_installer/views/update/tmpl/default_item.php
	 * administrator/components/com_languages/helpers/html/languages.php
	 * libraries/joomla/html/html/grid.php
	 *
	 * @param isitchecked
	 * @param form
	 * @return
	 */
	Joomla.isChecked = function( isitchecked, form ) {
		if ( typeof form  === 'undefined' ) {
			form = document.getElementById( 'adminForm' );
		}

		form.boxchecked.value += isitchecked ? 1 : -1;

		// If we don't have a checkall-toggle, done.
		if ( !form.elements[ 'checkall-toggle' ] ) return;

		// Toggle main toggle checkbox depending on checkbox selection
		var c = true,
			i, e, n;

		for ( i = 0, n = form.elements.length; i < n; i++ ) {
			e = form.elements[ i ];

			if ( e.type == 'checkbox' && e.name != 'checkall-toggle' && !e.checked ) {
				c = false;
				break;
			}
		}

		form.elements[ 'checkall-toggle' ].checked = c;
	};

	/**
	 * USED IN: libraries/joomla/html/toolbar/button/help.php
	 *
	 * Pops up a new window in the middle of the screen
	 */
	Joomla.popupWindow = function( mypage, myname, w, h, scroll ) {
		var winl = ( screen.width - w ) / 2,
			wint = ( screen.height - h ) / 2,
			winprops = 'height=' + h +
				',width=' + w +
				',top=' + wint +
				',left=' + winl +
				',scrollbars=' + scroll +
				',resizable';

		window.open( mypage, myname, winprops )
			.window.focus();
	};

	/**
	 * USED IN: libraries/joomla/html/html/grid.php
	 * In other words, on any reorderable table
	 */
	Joomla.tableOrdering = function( order, dir, task, form ) {
		if ( typeof form  === 'undefined' ) {
			form = document.getElementById( 'adminForm' );
		}

		form.filter_order.value = order;
		form.filter_order_Dir.value = dir;
		Joomla.submitform( task, form );
	};

	/**
	 * USED IN: administrator/components/com_modules/views/module/tmpl/default.php
	 *
	 * Writes a dynamically generated list
	 *
	 * @param string
	 *          The parameters to insert into the <select> tag
	 * @param array
	 *          A javascript array of list options in the form [key,value,text]
	 * @param string
	 *          The key to display for the initial state of the list
	 * @param string
	 *          The original key that was selected
	 * @param string
	 *          The original item value that was selected
	 */
	window.writeDynaList = function ( selectParams, source, key, orig_key, orig_val ) {
		var html = '<select ' + selectParams + '>',
			hasSelection = key == orig_key,
			i = 0,
			selected, x, item;

		for ( x in source ) {
			if (!source.hasOwnProperty(x)) { continue; }

			item = source[ x ];

			if ( item[ 0 ] != key ) { continue; }

			selected = '';

			if ( ( hasSelection && orig_val == item[ 1 ] ) || ( !hasSelection && i === 0 ) ) {
				selected = 'selected="selected"';
			}

			html += '<option value="' + item[ 1 ] + '" ' + selected + '>' + item[ 2 ] + '</option>';

			i++;
		}
		html += '</select>';

		document.writeln( html );
	};

	/**
	 * USED IN: administrator/components/com_content/views/article/view.html.php
	 * actually, probably not used anywhere.
	 *
	 * Changes a dynamically generated list
	 *
	 * @param string
	 *          The name of the list to change
	 * @param array
	 *          A javascript array of list options in the form [key,value,text]
	 * @param string
	 *          The key to display
	 * @param string
	 *          The original key that was selected
	 * @param string
	 *          The original item value that was selected
	 */
	window.changeDynaList = function ( listname, source, key, orig_key, orig_val ) {
		var list = document.adminForm[ listname ],
			hasSelection = key == orig_key,
			i, x, item, opt;

		// empty the list
		while ( list.firstChild ) list.removeChild( list.firstChild );

		i = 0;

		for ( x in source ) {
			if (!source.hasOwnProperty(x)) { continue; }

			item = source[x];

			if ( item[ 0 ] != key ) { continue; }

			opt = new Option();
			opt.value = item[ 1 ];
			opt.text = item[ 2 ];

			if ( ( hasSelection && orig_val == opt.value ) || (!hasSelection && i === 0) ) {
				opt.selected = true;
			}

			list.options[ i++ ] = opt;
		}

		list.length = i;
	};

	/**
	 * USED IN: administrator/components/com_menus/views/menus/tmpl/default.php
	 * Probably not used at all
	 *
	 * @param radioObj
	 * @return
	 */
	// return the value of the radio button that is checked
	// return an empty string if none are checked, or
	// there are no radio buttons
	window.radioGetCheckedValue = function ( radioObj ) {
		if ( !radioObj ) { return ''; }

		var n = radioObj.length,
			i;

		if ( n === undefined ) {
			return radioObj.checked ? radioObj.value : '';
		}

		for ( i = 0; i < n; i++ ) {
			if ( radioObj[ i ].checked ) {
				return radioObj[ i ].value;
			}
		}

		return '';
	};

	/**
	 * USED IN: administrator/components/com_users/views/mail/tmpl/default.php
	 * Let's get rid of this and kill it
	 *
	 * @param frmName
	 * @param srcListName
	 * @return
	 */
	window.getSelectedValue = function ( frmName, srcListName ) {
		var srcList = document[ frmName ][ srcListName ],
			i = srcList.selectedIndex;

		if ( i !== null && i > -1 ) {
			return srcList.options[ i ].value;
		} else {
			return null;
		}
	};

	/**
	 * USED IN: all over :)
	 *
	 * @param id
	 * @param task
	 * @return
	 */
	window.listItemTask = function ( id, task ) {
		var f = document.adminForm,
			i = 0, cbx,
			cb = f[ id ];

		if ( !cb ) return false;

		while ( true ) {
			cbx = f[ 'cb' + i ];

			if ( !cbx ) break;

			cbx.checked = false;

			i++;
		}

		cb.checked = true;
		f.boxchecked.value = 1;
		window.submitform( task );

		return false;
	};

	/**
	 * Default function. Usually would be overriden by the component
	 *
	 * @deprecated  12.1 This function will be removed in a future version. Use Joomla.submitbutton() instead.
	 */
	window.submitbutton = function ( pressbutton ) {
		Joomla.submitbutton( pressbutton );
	};

	/**
	 * Submit the admin form
	 *
	 * @deprecated  12.1 This function will be removed in a future version. Use Joomla.submitform() instead.
	 */
	window.submitform = function ( pressbutton ) {
		Joomla.submitform(pressbutton);
	};

	// needed for Table Column ordering
	/**
	 * USED IN: libraries/joomla/html/html/grid.php
	 * There's a better way to do this now, can we try to kill it?
	 */
	window.saveorder = function ( n, task ) {
		window.checkAll_button( n, task );
	};

	/**
	 * Checks all the boxes unless one is missing then it assumes it's checked out.
	 * Weird. Probably only used by ^saveorder
	 *
	 * @param   integer  n     The total number of checkboxes expected
	 * @param   string   task  The task to perform
	 *
	 * @return  void
	 */
	window.checkAll_button = function ( n, task ) {
		task = task ? task : 'saveorder';

		var j, box;

		for ( j = 0; j <= n; j++ ) {
			box = document.adminForm[ 'cb' + j ];

			if ( box ) {
				box.checked = true;
			} else {
				alert( "You cannot change the order of items, as an item in the list is `Checked Out`" );
				return;
			}
		}

		Joomla.submitform( task );
	};

	/**
     * Extend Objects
     */
    Joomla.extend = Joomla.extend || function(destination, source) {
    	for(var p in source) {
    		destination[p] = source[p];
    	}
    	return destination;
    };

    /**
     * Joomla options storage
     */
    Joomla.optionsStorage = {};

    /**
     * Return options for given key
     */
    Joomla.getOptions = function(key) {
    	return Joomla.optionsStorage[key] || null;
    };

    /**
     * Domready listener
     * Based on https://github.com/dperini/ContentLoaded by Diego Perini
     */
    Joomla.domReady = Joomla.domReady || function (callback) {
    	var done = false, top = true,
    		root = document.documentElement,
    		modern = document.addEventListener;

    	var init = function(e) {
    		if (e.type === 'readystatechange' && document.readyState !== 'complete') {
    			return;
    		}
    		Joomla.removeListener(e.type, init, e.type === 'load' ? window : document);
    		if (!done) {
    			callback.call(window, e.type || e);
    			done = true
    		}
    	};

    	var poll = function() {
    		try { root.doScroll('left'); } catch(e) { setTimeout(poll, 50); return; }
    		init('poll');
    	};

    	if (document.readyState === 'complete') {
    		// DOM are ready since a years! call the callback
    		callback.call(window, 'lazyload');
    	}
    	else {
    		// IE trick
    		if (!modern && root.doScroll) {
    			try { top = !window.frameElement; } catch(e) { }
    			if (top) poll();
    		}
    		// Listen when DOM will become ready
    		Joomla.addListener('DOMContentLoaded', init, document);
    		Joomla.addListener('readystatechange', init, document);
    		Joomla.addListener('load', init, window);
    	}
    };

    /**
     * Register the event listener
     * @param string  event    Event name
     * @param method  callback Callback function
     * @param element element  Add listener to element, default is window
     */
    Joomla.addListener = Joomla.addListener || function(event, callback, element) {
    	var element = element || window,
    		modern = document.addEventListener,
    		method = modern ? 'addEventListener' : 'attachEvent',
    		event  = modern ? event : 'on' + event;

    	// Add event listener,
    	element[method](event, callback);
    };

    /**
     * Unregister the event listener
     * @param string  event    Event name
     * @param method  callback Callback function
     * @param element element  DOM object, default is window,
     */
    Joomla.removeListener = Joomla.removeListener || function(event, callback, element){
    	var element = element || window,
        	modern = document.removeEventListener,
        	method = modern ? 'removeEventListener' : 'detachEvent',
        	event  = modern ? event : 'on' + event;

    	// Remove event listener,
    	element[method](event, callback);
    };

}( Joomla, document ));


/**
 * Joomla Behavior
 */
(function(window, document, Joomla){
	'use strict';

	/**
	 * Private behaviors storage
	 */
	var _behaviorsStorage = {};

	var _getBehaviorsStorage = function (key) {
		if(!_behaviorsStorage[key]) {
			_behaviorsStorage[key] = [];
		}
		return _behaviorsStorage[key];
	};

	/**
	 * Behavior item object
	 */
	var JoomlaBehaviorItem = window.JoomlaBehaviorItem || function(name) {
		this.name    = name;
		this.events  = {}; // event => callback
		this.options = null;
		this.optionsRequired = false; // If true then Behavior will be executed only when options available
	};

	/**
	 * Event object
	 */
	var JoomlaEvent = window.JoomlaEvent || function(type, target){
		var event = type,
			name = '',
			i = type.indexOf('.');

		// Check for namespaced event eg. event.behaviorname
		if (i !== -1) {
			event = type.substring(0, i);
			name  = type.substring(i + 1);
		}

		this.name         = event;
		this.nameFull     = type;
		this.behaviorName = name;
		this.target       = target || document;
	};

	/**
	 * Behaviors object
	 */
	var JoomlaBehavior = window.JoomlaBehavior || function() {
		// Key for behaviors storage
		this.key = Math.random().toString(36).substr(8);

		// Init empty storage
		_getBehaviorsStorage(this.key);
	};

	/**
	 * Add new behavior
	 *
	 * @param string       name     Behavior name
	 * @param string|array event    Event(s) subscribed to
	 * @param method       callback Callback to be executed
	 * @param bool  optionsRequired If true, then Behavior will be executed only when
	 * 								options for this name available in Joomla.optionsStorage
	 *
	 * @example:
	 * 	Watch on document ready and update part of document:
	 *
	 * 		Joomla.Behavior.add('myBehavior', 'ready update', function(event){
	 * 			console.log(event.name, event.target);
	 * 		});
	 *
	 *  Or:
	 *  	Joomla.Behavior.add('myBehavior', ['ready', 'update'], function(event){
	 * 			console.log(event.name, event.target);
	 * 		});
	 *
	 *  Watch when someone request to clean up inside Target container:
	 *
	 *  	Joomla.Behavior.add('myBehavior', 'remove', function(event){
	 * 			console.log(event.name, event.target);
	 * 		});
	 *
	 */
	JoomlaBehavior.prototype.add = function (name, event, callback, optionsRequired) {
		var events  = event.toString === '[object Array]' ? event : event.split(' '),
			storage = _getBehaviorsStorage(this.key),
			behavior;

		// Check whether already exist
		for (var i = 0, l = storage.length; i < l; i++ ) {
			if(storage[i] && storage[i].name === name) {
				behavior = storage[i];
				break;
			}
		}

		// Create new if not
		if(!behavior) {
			behavior = new JoomlaBehaviorItem(name);
			behavior.optionsRequired = !!optionsRequired;
			storage.push(behavior);
		}

		// Add event => callback
		for (var i = 0, l = events.length; i < l; i++ ) {
			if (events[i]) {
				behavior.events[events[i]] = callback;
			}
		}
	};

	/**
	 * Remove new behavior
	 *
	 * @param string event Event to be removed, can be in format event.behaviorname
	 *
	 * @example:
	 * 	Unbind specific event from all behaviors:
	 * 		Joomla.Behavior.remove('update');
	 *
	 *  Unbind specific event from myBehavior:
	 * 		Joomla.Behavior.remove('update.myBehavior');
	 *
	 *  Remove myBehavior:
	 * 		Joomla.Behavior.remove('.myBehavior');
	 */
	JoomlaBehavior.prototype.remove = function (event) {
		var jevent = new JoomlaEvent(event),
			removeEvents = !!(jevent.name && !jevent.behaviorName),
			removeByName = !!(!jevent.name && jevent.behaviorName),
			storage = _getBehaviorsStorage(this.key),
			behavior;

		// Unbind specific event from all behaviors
		if (removeEvents) {
			for (var i = 0, l = storage.length; i < l; i++ ) {
				behavior = storage[i];
				if (behavior && behavior.events[jevent.name]) {
					delete behavior.events[jevent.name];
				}
			}
		}

		// Remove behavior with all events
		else if (removeByName){
			for (var i = 0, l = storage.length; i < l; i++ ) {
				behavior = storage[i];
				if(behavior && behavior.name === jevent.behaviorName) {
					//storage.splice(i, 1);
					storage[i] = null; // We cannot totally remove it as it will change storage length
					break;
				}
			}
		}

		// Unbind specific event from behavior
		else {
			for (var i = 0, l = storage.length; i < l; i++ ) {
				behavior = storage[i];
				if(behavior && behavior.name === jevent.behaviorName) {
					delete behavior.events[jevent.name];
					break;
				}
			}
		}
	};

	/**
	 * Call behaviors
	 *
	 * @param string  event   Event to be called, can be in format event.behaviorname
	 * @param element element Target DOM element
	 * @param object  options Custom options for Behavior, used only when call specific behavior, eg event.behaviorname
	 *
	 * @example:
	 * 	Notify all behaviors about DOM changes:
	 * 		Joomla.Behavior.call('update', changedContainer);
	 *
	 * 	Notify only myBehavior about DOM changes:
	 * 		Joomla.Behavior.call('update.myBehavior', changedContainer);
	 *
	 *  Notify only myBehavior about DOM changes, with custom options:
	 * 		Joomla.Behavior.call('update.myBehavior', changedContainer, options);
	 *
	 *  Request to clean up inside Target container:
	 * 		Joomla.Behavior.call('remove', container);
	 *
	 *
	 */
	JoomlaBehavior.prototype.call = function (event, element, options) {
		var jevent  = new JoomlaEvent(event, element),
			storage = _getBehaviorsStorage(this.key),
			target  = jevent.target,
			behavior,
			callback;

		for (var i = 0, l = storage.length; i < l; i++ ) {
			behavior = storage[i];
			callback = behavior && behavior.events ? behavior.events[jevent.name] : null;
			jevent.options = null;

			// Check whether we have valid behavior
			if (!callback || (jevent.behaviorName && behavior.name !== jevent.behaviorName)){
				continue;
			}

			// Check Options
			if (options && jevent.behaviorName && behavior.name === jevent.behaviorName) {
				jevent.options = options;
			}
			else {
				jevent.options = Joomla.getOptions(behavior.name);
			}

			// Do not call Behavior without options, if behavior do not want it
			if (behavior.optionsRequired && !jevent.options) {
				continue;
			}

			// Call behavior
			if (callback.call(target, jevent) === false) {
				break;
			}
		}
	};

	/**
	 * Init Behavior, and wait on DOM ready, and watch on loaded/unload
	 */
	Joomla.Behavior = Joomla.Behavior || new JoomlaBehavior;

	Joomla.domReady(function(){
		Joomla.Behavior.call('ready');
		Joomla.Behavior.remove('ready');
	});

	var _loadCallback = function(){
		Joomla.removeListener('load', _loadCallback);
		Joomla.Behavior.call('load');
		Joomla.Behavior.remove('load');
	};

	Joomla.addListener('load', _loadCallback);

	Joomla.addListener('unload', function(){
		Joomla.Behavior.call('unload');
	});

	// Make thing global
	window.JoomlaBehavior     = JoomlaBehavior;
	window.JoomlaBehaviorItem = JoomlaBehaviorItem;
	window.JoomlaEvent        = JoomlaEvent;

})(window, document, Joomla);
