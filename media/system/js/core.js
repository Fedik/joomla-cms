/**
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */// Only define the Joomla namespace if not defined.
function writeDynaList(e,t,n,r,i){var s="\n	<select "+e+">",o=0;for(x in t){if(t[x][0]==n){var u="";if(r==n&&i==t[x][1]||o==0&&r!=n)u='selected="selected"';s+='\n		<option value="'+t[x][1]+'" '+u+">"+t[x][2]+"</option>"}o++}s+="\n	</select>",document.writeln(s)}function changeDynaList(e,t,n,r,s){var o=document.adminForm[e];for(i in o.options.length)o.options[i]=null;i=0;for(x in t)if(t[x][0]==n){opt=new Option,opt.value=t[x][1],opt.text=t[x][2];if(r==n&&s==opt.value||i==0)opt.selected=!0;o.options[i++]=opt}o.length=i}function radioGetCheckedValue(e){if(!e)return"";var t=e.length;if(t==undefined)return e.checked?e.value:"";for(var n=0;n<t;n++)if(e[n].checked)return e[n].value;return""}function getSelectedValue(e,t){var n=document[e],r=n[t];return i=r.selectedIndex,i!=null&&i>-1?r.options[i].value:null}function listItemTask(e,t){var n=document.adminForm,r=n[e];if(r){for(var i=0;!0;i++){var s=n["cb"+i];if(!s)break;s.checked=!1}r.checked=!0,n.boxchecked.value=1,submitbutton(t)}return!1}function submitbutton(e){submitform(e)}function submitform(e){e&&(document.adminForm.task.value=e),typeof document.adminForm.onsubmit=="function"&&document.adminForm.onsubmit(),typeof document.adminForm.fireEvent=="function"&&document.adminForm.fireEvent("submit"),document.adminForm.submit()}function saveorder(e,t){checkAll_button(e,t)}function checkAll_button(e,t){t||(t="saveorder");for(var n=0;n<=e;n++){var r=document.adminForm["cb"+n];if(!r){alert("You cannot change the order of items, as an item in the list is `Checked Out`");return}r.checked==0&&(r.checked=!0)}submitform(t)}if(typeof Joomla=="undefined")var Joomla={};Joomla.editors={},Joomla.editors.instances={},Joomla.submitform=function(e,t){typeof t=="undefined"&&(t=document.getElementById("adminForm")),typeof e!="undefined"&&(t.task.value=e),typeof t.onsubmit=="function"&&t.onsubmit(),typeof t.fireEvent=="function"&&t.fireEvent("submit"),t.submit()},Joomla.submitbutton=function(e){Joomla.submitform(e)},Joomla.JText={strings:{},_:function(e,t){return typeof this.strings[e.toUpperCase()]!="undefined"?this.strings[e.toUpperCase()]:t},load:function(e){for(var t in e)this.strings[t.toUpperCase()]=e[t];return this}},Joomla.replaceTokens=function(e){var t=document.getElementsByTagName("input");for(var n=0;n<t.length;n++)t[n].type=="hidden"&&t[n].name.length==32&&t[n].value=="1"&&(t[n].name=e)},Joomla.isEmail=function(e){var t=new RegExp("^[\\w-_.]*[\\w-_.]@[\\w].+[\\w]+[\\w]$");return t.test(e)},Joomla.checkAll=function(e,t){t||(t="cb");if(e.form){var n=0;for(var r=0,i=e.form.elements.length;r<i;r++){var s=e.form.elements[r];s.type==e.type&&(t&&s.id.indexOf(t)==0||!t)&&(s.checked=e.checked,n+=s.checked==1?1:0)}return e.form.boxchecked&&(e.form.boxchecked.value=n),!0}return!1},Joomla.renderMessages=function(e){Joomla.removeMessages();var t=document.id("system-message-container");Object.each(e,function(e,n){var r=new Element("div",{id:"system-message","class":"alert alert-"+n});r.inject(t);var i=new Element("h4",{"class":"alert-heading",html:Joomla.JText._(n)});i.inject(r);var s=new Element("div");Array.each(e,function(e,t,n){var r=new Element("p",{html:e});r.inject(s)},this),s.inject(r)},this)},Joomla.removeMessages=function(){var e=$$("#system-message-container > *");e.destroy()},Joomla.isChecked=function(e,t){typeof t=="undefined"&&(t=document.getElementById("adminForm")),e==1?t.boxchecked.value++:t.boxchecked.value--},Joomla.popupWindow=function(e,t,n,r,i){var s=(screen.width-n)/2,o=(screen.height-r)/2,u="height="+r+",width="+n+",top="+o+",left="+s+",scrollbars="+i+",resizable",a=window.open(e,t,u);a.window.focus()},Joomla.tableOrdering=function(e,t,n,r){typeof r=="undefined"&&(r=document.getElementById("adminForm")),r.filter_order.value=e,r.filter_order_Dir.value=t,Joomla.submitform(n,r)},Joomla.extend=function(e,t){for(var n in t)e[n]=t[n];return e},Function.prototype.bind||(Function.prototype.bind=function(e){var t=this,n=Array.prototype.slice.call(arguments,1);return function(){return t.apply(e,n)}}),Array.prototype.indexOf||(Array.prototype.indexOf=function(e){"use strict";if(this==null)throw new TypeError;var t=Object(this),n=t.length>>>0;if(n===0)return-1;var r=0;arguments.length>1&&(r=Number(arguments[1]),r!=r?r=0:r!=0&&r!=Infinity&&r!=-Infinity&&(r=(r>0||-1)*Math.floor(Math.abs(r))));if(r>=n)return-1;var i=r>=0?r:Math.max(n-Math.abs(r),0);for(;i<n;i++)if(i in t&&t[i]===e)return i;return-1}),Joomla.optionsStorage={},Joomla.eventsStorage={},Joomla.readyCalled=!1,Joomla.initLoadCalled=!1,Joomla.DOMContentLoaded=function(e){var t=!1,n=!0,r=window,i=r.document,s=i.documentElement,o=i.addEventListener?"addEventListener":"attachEvent",u=i.addEventListener?"removeEventListener":"detachEvent",a=i.addEventListener?"":"on",f=function(n){if(n.type=="readystatechange"&&i.readyState!="complete")return;(n.type=="load"?r:i)[u](a+n.type,f,!1),!t&&(t=!0)&&(e.call(r,n.type||n),Joomla.readyCalled=!0)},l=function(){try{s.doScroll("left")}catch(e){setTimeout(l,50);return}f("poll")};if(i.readyState=="complete")e.call(r,"lazy"),Joomla.readyCalled=!0;else{if(i.createEventObject&&s.doScroll){try{n=!r.frameElement}catch(c){}n&&l()}i[o](a+"DOMContentLoaded",f,!1),i[o](a+"readystatechange",f,!1),r[o](a+"load",f,!1)}},Joomla.addListener=function(e,t,n){return n=n||window,e==="domready"?Joomla.initLoadCalled||Joomla.DOMContentLoaded(t):n.addEventListener?n.addEventListener(e,t):n.attachEvent&&n.attachEvent("on"+e,t),Joomla},Joomla.removeListener=function(e,t,n){return n=n||window,e!=="domready"&&(n.removeEventListener?n.removeEventListener(e,t):n.detachEvent&&n.detachEvent("on"+e,t)),Joomla},Joomla.addEvent=function(e,t){var n=e.split("."),r=n[0];!Joomla.initLoadCalled&&n[1]!=="jinit"&&(r==="load"||r==="domready")&&Joomla.addEvent(r+".jinit",t),r==="domready"&&n[1]!=="jinit"&&(n=e.replace(r,"load").split("."),r="load");if(!Joomla.eventsStorage[r]){var i=r==="load"||r==="domready"?r+".jinit":r,s=Joomla.fireEvent.bind(window,i,document);Joomla.addListener(r,s)}var o=Joomla.eventsStorage;for(var u=0;u<n.length;u++)o[n[u]]=o[n[u]]||{cb:[]},o[n[u]].cb.indexOf(t)===-1&&o[n[u]].cb.push(t),o=o[n[u]];return Joomla},Joomla.removeEvent=function(e,t){var n=e.split("."),r=n[0];r==="domready"&&Joomla.removeEvent(e.replace(r,"load"),t);var i=Joomla.eventsStorage;for(var s=0;s<n.length;s++){i=i[n[s]]||{cb:[]};var o=i.cb.indexOf(t);o!==-1&&(delete i.cb[o],i.cb.splice(o,1))}return Joomla},Joomla.fireEvent=function(e,t){var n=e.split("."),r=n[0],i=Joomla.eventsStorage;arguments[1]=t||document;for(var s=0;s<n.length;s++)i=i[n[s]]||{cb:[]};for(var s=0;s<i.cb.length;s++)try{i.cb[s].apply(window,arguments)}catch(o){window.console&&(console.log(o),console.log(o.stack))}return n[1]==="jinit"&&delete Joomla.eventsStorage[r].jinit,r==="domready"&&delete Joomla.eventsStorage[r],r==="load"&&!Joomla.initLoadCalled&&(Joomla.initLoadCalled=Joomla.readyCalled=!0),Joomla};