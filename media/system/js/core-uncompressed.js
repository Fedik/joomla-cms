/**
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// Only define the Joomla namespace if not defined.
var Joomla = window.Joomla || {};

!(function(window){
	'use strict';

    Joomla.editors = {};
    // An object to hold each editor instance on page
    Joomla.editors.instances = {};

/**
 * Generic submit form
 */
Joomla.submitform = function(task, form) {
    if (typeof(form) === 'undefined') {
        form = document.getElementById('adminForm');
    }

    if (typeof(task) !== 'undefined' && task !== "") {
        form.task.value = task;
    }

    // Submit the form.
    if (typeof form.onsubmit == 'function') {
        form.onsubmit();
    }
    if (typeof form.fireEvent == "function") {
        form.fireEvent('onsubmit');
    }
    form.submit();
};

/**
 * Default function. Usually would be overriden by the component
 */
Joomla.submitbutton = function(pressbutton) {
    Joomla.submitform(pressbutton);
}

/**
 * Custom behavior for JavaScript I18N in Joomla! 1.6
 *
 * Allows you to call Joomla.JText._() to get a translated JavaScript string pushed in with JText::script() in Joomla.
 */
Joomla.JText = {
    strings: {},
    '_': function(key, def) {
        return typeof this.strings[key.toUpperCase()] !== 'undefined' ? this.strings[key.toUpperCase()] : def;
    },
    load: function(object) {
        for (var key in object) {
            this.strings[key.toUpperCase()] = object[key];
        }
        return this;
    }
};

/**
 * Method to replace all request tokens on the page with a new one.
 */
Joomla.replaceTokens = function(n) {
    var els = document.getElementsByTagName('input'), i;
    for (i = 0; i < els.length; i++) {
        if ((els[i].type == 'hidden') && (els[i].name.length == 32) && els[i].value == '1') {
            els[i].name = n;
        }
    }
};

/**
 * USED IN: administrator/components/com_banners/views/client/tmpl/default.php
 *
 * Verifies if the string is in a valid email format
 *
 * @param string
 * @return boolean
 */
Joomla.isEmail = function(text) {
    var regex = new RegExp("^[\\w-_\.]*[\\w-_\.]\@[\\w]\.+[\\w]+[\\w]$");
    return regex.test(text);
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
Joomla.checkAll = function(checkbox, stub) {
    if (!stub) {
        stub = 'cb';
    }
    if (checkbox.form) {
        var c = 0, i, e, n;
        for (i = 0, n = checkbox.form.elements.length; i < n; i++) {
            e = checkbox.form.elements[i];
            if (e.type == checkbox.type) {
                if ((stub && e.id.indexOf(stub) == 0) || !stub) {
                    e.checked = checkbox.checked;
                    c += (e.checked == true ? 1 : 0);
                }
            }
        }
        if (checkbox.form.boxchecked) {
            checkbox.form.boxchecked.value = c;
        }
        return true;
    }
    return false;
}

/**
 * Render messages send via JSON
 *
 * @param   object  messages    JavaScript object containing the messages to render. Example:
 *                              var messages = {
 *                              	"message": ["Message one", "Message two"],
 *                              	"error": ["Error one", "Error two"]
 *                              };
 * @return  void
 */
Joomla.renderMessages = function(messages) {
	Joomla.removeMessages();

	var messageContainer = document.getElementById('system-message-container');

	for (var type in messages) {
		if (messages.hasOwnProperty(type)) {
			// Array of messages of this type
			var typeMessages = messages[type];

			// Create the alert box
			var messagesBox = document.createElement('div');
			messagesBox.className = 'alert alert-' + type;

			// Title
			var title = Joomla.JText._(type);

			// Skip titles with untranslated strings
			if (typeof title != 'undefined') {
				var titleWrapper = document.createElement('h4');
				titleWrapper.className = 'alert-heading';
				titleWrapper.innerHTML = Joomla.JText._(type);

				messagesBox.appendChild(titleWrapper)
			}

			// Add messages to the message box
			for (var i = typeMessages.length - 1; i >= 0; i--) {
				var messageWrapper = document.createElement('p');
				messageWrapper.innerHTML = typeMessages[i];
				messagesBox.appendChild(messageWrapper);
			};

			messageContainer.appendChild(messagesBox);
		}
	}
};


/**
 * Remove messages
 *
 * @return  void
 */
Joomla.removeMessages = function() {
	var messageContainer = document.getElementById('system-message-container');

	// Empty container with a while for Chrome performance issues
	while (messageContainer.firstChild) messageContainer.removeChild(messageContainer.firstChild);

	// Fix Chrome bug not updating element height
	messageContainer.style.display='none';
	messageContainer.offsetHeight;
	messageContainer.style.display='';
}

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
Joomla.isChecked = function(isitchecked, form) {
    if (typeof(form) === 'undefined') {
        form = document.getElementById('adminForm');
    }

    if (isitchecked == true) {
        form.boxchecked.value++;
    } else {
        form.boxchecked.value--;
    }

    // Toggle main toggle checkbox depending on checkbox selection
    var c = true, i, e, n;
    for (i = 0, n = form.elements.length; i < n; i++) {
        e = form.elements[i];
        if (e.type == 'checkbox') {
            if (e.name != 'checkall-toggle' && e.checked == false) {
                c = false;
                break;
            }
        }
    }
    if (form.elements['checkall-toggle']) {
        form.elements['checkall-toggle'].checked = c;
    }
}

/**
 * USED IN: libraries/joomla/html/toolbar/button/help.php
 *
 * Pops up a new window in the middle of the screen
 */
Joomla.popupWindow = function(mypage, myname, w, h, scroll) {
    var winl = (screen.width - w) / 2, wint, winprops, win;
    wint = (screen.height - h) / 2;
    winprops = 'height=' + h + ',width=' + w + ',top=' + wint + ',left=' + winl
            + ',scrollbars=' + scroll + ',resizable'
    win = window.open(mypage, myname, winprops)
    win.window.focus();
}

/**
 * USED IN: libraries/joomla/html/html/grid.php
 */
Joomla.tableOrdering = function(order, dir, task, form) {
    if (typeof(form) === 'undefined') {
        form = document.getElementById('adminForm');
    }

    form.filter_order.value = order;
    form.filter_order_Dir.value = dir;
    Joomla.submitform(task, form);
}

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
window.writeDynaList = function (selectParams, source, key, orig_key, orig_val) {
    var html = '\n  <select ' + selectParams + '>', i, selected;
    i = 0;
    for (var x in source) {
        if (source[x][0] == key) {
            selected = '';
            if ((orig_key == key && orig_val == source[x][1])
                    || (i == 0 && orig_key != key)) {
                selected = 'selected="selected"';
            }
            html += '\n     <option value="' + source[x][1] + '" ' + selected
                    + '>' + source[x][2] + '</option>';
        }
        i++;
    }
    html += '\n </select>';

    document.writeln(html);
}

/**
 * USED IN: administrator/components/com_content/views/article/view.html.php
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
window.changeDynaList = function (listname, source, key, orig_key, orig_val) {
    var list = document.adminForm[listname];

    // empty the list
    for (var i in list.options.length) {
        list.options[i] = null;
    }
    i = 0;
    for (var x in source) {
        if (source[x][0] == key) {
            opt = new Option();
            opt.value = source[x][1];
            opt.text = source[x][2];

            if ((orig_key == key && orig_val == opt.value) || i == 0) {
                opt.selected = true;
            }
            list.options[i++] = opt;
        }
    }
    list.length = i;
}

/**
 * USED IN: administrator/components/com_menus/views/menus/tmpl/default.php
 *
 * @param radioObj
 * @return
 */
// return the value of the radio button that is checked
// return an empty string if none are checked, or
// there are no radio buttons
window.radioGetCheckedValue = function (radioObj) {
    if (!radioObj) {
        return '';
    }
    var n = radioObj.length, i;
    if (n == undefined) {
        if (radioObj.checked) {
            return radioObj.value;
        } else {
            return '';
        }
    }
    for (var i = 0; i < n; i++) {
        if (radioObj[i].checked) {
            return radioObj[i].value;
        }
    }
    return '';
}

/**
 * USED IN: administrator/components/com_banners/views/banner/tmpl/default/php
 * administrator/components/com_categories/views/category/tmpl/default.php
 * administrator/components/com_categories/views/copyselect/tmpl/default.php
 * administrator/components/com_content/views/copyselect/tmpl/default.php
 * administrator/components/com_massmail/views/massmail/tmpl/default.php
 * administrator/components/com_menus/views/list/tmpl/copy.php
 * administrator/components/com_menus/views/list/tmpl/move.php
 * administrator/components/com_messages/views/message/tmpl/default_form.php
 * administrator/components/com_newsfeeds/views/newsfeed/tmpl/default.php
 * components/com_content/views/article/tmpl/form.php
 * templates/beez/html/com_content/article/form.php
 *
 * @param frmName
 * @param srcListName
 * @return
 */
window.getSelectedValue = function (frmName, srcListName) {
    var form = document[frmName],
    srcList = form[srcListName];

    var i = srcList.selectedIndex;
    if (i != null && i > -1) {
        return srcList.options[i].value;
    } else {
        return null;
    }
}

/**
 * USED IN: all over :)
 *
 * @param id
 * @param task
 * @return
 */
window.listItemTask = function (id, task) {
    var f = document.adminForm, i, cbx,
    cb = f[id];
    if (cb) {
        for (i = 0; true; i++) {
            cbx = f['cb'+i];
            if (!cbx)
                break;
            cbx.checked = false;
        } // for
        cb.checked = true;
        f.boxchecked.value = 1;
        submitbutton(task);
    }
    return false;
}

/**
 * Default function. Usually would be overriden by the component
 *
 * @deprecated  12.1 This function will be removed in a future version. Use Joomla.submitbutton() instead.
 */
window.submitbutton = function (pressbutton) {
    submitform(pressbutton);
}

/**
 * Submit the admin form
 *
 * @deprecated  12.1 This function will be removed in a future version. Use Joomla.submitform() instead.
 */
window.submitform = function (pressbutton) {
    if (pressbutton) {
        document.adminForm.task.value = pressbutton;
    }
    if (typeof document.adminForm.onsubmit == "function") {
        document.adminForm.onsubmit();
    }
    if (typeof document.adminForm.fireEvent == "function") {
        document.adminForm.fireEvent('submit');
    }
    document.adminForm.submit();
}

// needed for Table Column ordering
/**
 * USED IN: libraries/joomla/html/html/grid.php
 */
window.saveorder = function (n, task) {
    checkAll_button(n, task);
}

window.checkAll_button = function (n, task) {
    if (!task) {
        task = 'saveorder';
    }
    var j, box;
    for (j = 0; j <= n; j++) {
        box = document.adminForm['cb'+j];
        if (box) {
            if (box.checked == false) {
                box.checked = true;
            }
        } else {
            alert("You cannot change the order of items, as an item in the list is `Checked Out`");
            return;
        }
    }
    submitform(task);
}

/**
 * Extend Objects function
 */
Joomla.extend = function(destination, source) {
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
 * @param event - string, event name
 * @param method - callback function
 * @param element - add listener to element, default is window
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
 * @param event - string, event name
 * @param callback - callback function
 * @param element - remove DOM object, default is window,
 */
Joomla.removeListener = Joomla.removeListener || function(event, callback, element){
	var element = element || window,
    	modern = document.removeEventListener,
    	method = modern ? 'removeEventListener' : 'detachEvent',
    	event  = modern ? event : 'on' + event;

	// Remove event listener,
	element[method](event, callback);
};

})(this);

/**
 * Joomla Behavior system
 */
(function(window){
	'use strict';

	var JoomlaBehavior = window.JoomlaBehavior || function() {
		// Registered behaviors
		this.behaviors = {};
	};

	var JoomlaEvent = window.JoomlaEvent || function(type, target){
		this.type = type;
		this.target = target || document;
	};

	// Make it global
	window.JoomlaBehavior = JoomlaBehavior;
	window.JoomlaEvent = JoomlaEvent;

	/**
	 * Add new behavior
	 * @param string name Behavior name
	 * @param string|array event(s) Event subscribed to
	 * @param method callback Callback to be executed
	 */
	JoomlaBehavior.prototype.add = function (name, event, callback) {
		var events = event.toString === '[object Array]' ? event : event.split(' ');

		for (var i = 0, l = events.length; i < l; i++ ) {
			var e = events[i];

			this.behaviors[e] = this.behaviors[e] || [];
			this.behaviors[e].push(callback);

		}
	};

	/**
	 * Remove new behavior
	 * @param string name Behavior name
	 * @param string event Event subscribed to
	 */
	JoomlaBehavior.prototype.remove = function (name, event) {
		var removeAll = event && !name;
		console.log('remove', name, event, removeAll)
	};

	/**
	 * Call behaviors
	 * @param string event Event to be called
	 */
	JoomlaBehavior.prototype.call = function (event, target) {
		var jevent = new JoomlaEvent(event, target),
			callbacks = this.behaviors[event] || [];

		console.log(event, jevent, this.behaviors);

		for (var i = 0, l = callbacks.length; i < l; i++ ) {
			// Do not crash the site if some behavior is buggy
			try {
				callbacks[i].apply(this, [jevent]);
			} catch (e) {
				if(window.console){ console.log(e);}
			}
		}

	};

	/**
	 * Init Behavior, and wait on DOM ready, and all loaded
	 */
	Joomla.behavior = Joomla.behavior || new JoomlaBehavior;

	Joomla.domReady(function(){
		Joomla.behavior.call('ready');
		Joomla.behavior.remove(null, 'ready');
	});

	var onLoadCallback = function(){
		Joomla.removeListener('load', onLoadCallback);
		Joomla.behavior.call('load');
		Joomla.behavior.remove(null, 'load');
	};

	Joomla.addListener('load', onLoadCallback);


})(this);

