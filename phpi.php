<?php
	// php interpreter
	
	// created by richard chaffer, 2012-2014
	// released under the mit licence (mit). see the full text at the end of this file
	
  // WARNING // // // // // // // // // // // // // // // // // // // // // // // // // //
  //
	//  DO _NOT_ UPLOAD THIS TO _ANY_ PRODUCTION SYSTEM
	//  IT IS _NOT_ RECOMMENDED TO UPLOAD THIS TO EXTERNALLY-ACCESSIBLE SERVERS
	//  IT IS RECOMMENDED THAT THIS UTILITY BE USED _ONLY_ ON LOCALLY-HOSTED PLATFORMS
	//
	//  This utility uses the PHP eval() function to process commands in an unmoderated
	//  manner. This could be used to modify environmental settings on your PHP platform,
	//  and could easily be used by malicious individuals to access and manipulate files
	//  stored on it EVEN OUTSIDE THE DOCUMENT ROOT.
	//
	//  The author accepts no liability for failure to heed the above warnings.
	//
	// WARNING // // // // // // // // // // // // // // // // // // // // // // // // // //
	
	// this iteration: 1.1.2
	
	// changelog:-
	//   ...
	//   i1.0.0		01-12-12	first fully working state ("One-shot PHP Interpreter")
	//   i1.0.1		19-12-12	implemented fatal error catcher (onShutdown)
	//   i1.0.2		04-04-13  added $().ospiTraceback()
	//   i1.0.3		06-04-13  changed name to "PHP Interpreter"
	//   i1.0.4		03-06-13	changed session name to prevent interference with shared hosts
	//   i1.1.0   29-05-14  recoded to non-jquery javascript, reduced to single-file, renamed files to phpi
	//   i1.1.1   28-10-14  added base64 embedded pngs to #x
	//   i1.1.2   02-11-14  changed licence from cc-by-sa4.0 to mit, verified working in modern browsers
	//   -- DEPLOYED TO GITHUB, as i1.1.2, 02-11-14 --
	
	// pending enhancements:-
	//   1  test in non-chrome browsers
	//   2  implement extended history (ctrl-up, ctrl-down)
	//   3  deploy to github
	
	// function definitions
	
	if(!empty($_REQUEST)){
		session_name('PHPI');
		session_start();
		
		if(function_exists('xdebug_disable')) xdebug_disable();	// xdebug messes up error reporting
		
		function getLast($args){
			// nothing expected in $args[] at present
			// returns last command
			return array('last'=>$_SESSION['last']);
		}

		function executePHP($args){
			// executes php code and returns a json list of the items that need to be added to the interpreter buffer (history)
			ob_start();								// start logging output
			ini_set('display_errors', '0');			// suppress error reporting
			$return = array();
			$_SESSION['last'] = $args['eval'];
			eval($args['eval']);
			$error = error_get_last();
			if(!is_null($error)){
				$trace = debug_backtrace();
			} else {
				$trace = null;
			}
			$highlight = highlight_string('<?php '.$args['eval'].' ?>', true);
			$return['input'] = '<span class="r_pmt">&gt;&gt;&gt;</span>'.$highlight;	// using &nbsp for the meantime - to change to margin/list-image
			$ob = ob_get_clean();
			ini_restore('display_errors');
			$return['output'] = htmlspecialchars($ob);		// output uses margin now
			$return['error'] = array('error'=>$error, 'trace'=>$trace);
			return $return;
		}
		
		// fatal error handler
		function catchFatalErrors(){
			$ob = ob_get_clean();
			ini_restore('display_errors');
			echo($ob);
	/*		$error = error_get_last();
			if($error !== null){
				$elems = '';
				foreach($error as $k=>$v){
					$elems .= '['.$k.'] => "'.$v.'", ';
				}
				$return = array('error'=>array('error'=>$elems, 'trace'=>null), 'input'=>'FATAL ERROR', 'output'=>'FATAL ERROR');
				echo(json_encode($return));
			}
	*/	}
		register_shutdown_function('catchFatalErrors');
		
		// core activity handler
		if(function_exists($_POST['fn'])){
			$P = $_POST;
			$output = $P['fn']($P);
		}
		echo(json_encode($output));
	} else {
		echo <<<EOS
<!DOCTYPE html>
<html>
	<head>
		<title>PHP Interpreter</title>
		<style type="text/css">
			* { font-family: Consolas, mono; }
			html, body { height: 100%; }
			body { font-size: 10pt; margin: 0; background-color: rgb(200,200,200); background-image: linear-gradient(to top, rgba(0,0,0,0.3), rgba(0,0,0,0) 50%); background-image: -webkit-linear-gradient(bottom, rgba(0,0,0,0.3), rgba(0,0,0,0) 50%); background-image: -moz-linear-gradient(bottom, rgba(0,0,0,0.3), rgba(0,0,0,0) 50%); background-image: -ms-linear-gradient(bottom, rgba(0,0,0,0.3), rgba(0,0,0,0) 50%); background-image: -o-linear-gradient(bottom, rgba(0,0,0,0.3), rgba(0,0,0,0) 50%); overflow-y: scroll;}
			div#container { background-color: rgb(250,250,250); background-image: linear-gradient(to bottom, rgba(0,0,0,0.05), rgba(0,0,0,0) 5.5em); background-image: -webkit-linear-gradient(top, rgba(0,0,0,0.05), rgba(0,0,0,0) 5.5em); background-image: -moz-linear-gradient(top, rgba(0,0,0,0.05), rgba(0,0,0,0) 5.5em); background-image: -ms-linear-gradient(top, rgba(0,0,0,0.05), rgba(0,0,0,0) 5.5em); background-image: -o-linear-gradient(top, rgba(0,0,0,0.05), rgba(0,0,0,0) 5.5em); box-shadow: 0 0 15px rgb(128,128,128); -moz-box-shadow: 0 0 15px rgb(128,128,128); -webkit-box-shadow: 0 0 15px rgb(128,128,128); }
			h1 { padding-top: 1em; margin-top: 0;margin-left: 2em; font-family: Consolas, mono; font-size: 10pt; color: rgb(50,50,50); }
			h1 span.feint { color: rgb(206,206,206); font-weight: normal; }
			h2 { margin-left: 2em; font-family: Consolas, mono; font-size: 10pt; color: rgb(200,0,0); }
			div#prompt { margin: 0; }
			div#x { font-family: Consolas, mono; color: rgb(128,128,128); width: 1ex; display: block; float: left; padding: 0 0.5ex 0 0.5ex; display: inline-block; cursor: pointer; }
			div#x:hover { color: rgb(200,0,0); }
			ul#r { margin: 0; padding: 0; width: 100%; }
			ul#r li { margin: 0; padding: 2px 0; list-style-type: none; }
			ul#r li.output { white-space: pre; }
			ul#r li.c_err { margin-left: 2em; }
			ul.trace { padding: 0; margin: 0; }
			label { margin: 0; padding: 0; display: block; float: left; }
			#c { margin: 0; padding: 0; width: 0px; display: block; float: left; border: none; background-color: transparent; font-family: Consolas, mono; font-size: 10pt; }
			textarea#c { height: 2.4em; }
			input:focus, textarea:focus { outline: none; }
			div#c_box { margin: 2px 0 0 0; padding: 0; width: 100%; background-color: white; box-shadow: 0 0 5px rgba(0,0,0,0.25); -moz-box-shadow: 0 0 5px rgba(0,0,0,0.5); -webkit-box-shadow: 0 0 5px rgba(0,0,0,0.5); }
			.c_pmt, .r_pmt { font-weight: bold; color: rgb(128,128,255); }
			.c_pmt { color: rgb(128,128,255); }
			.c_fnc, .r_fnc { color: #007700; }
			.c_att, .r_att { color: #0000bb; }
			.c_str, .r_str { color: #dd0000; }
			.c_com, .r_com { color: #ff8000; }
			.c_err, .r_err { color: #808080; }
			.c_err strong, .r_err strong { color: #606060; }
			.clearfix { clear: both; }
		</style>
		<script type="text/javascript">
			// these will be required globally
			var errortypes = {
				1:'E_ERROR',
				2:'E_WARNING',
				4:'E_PARSE',
				8:'E_NOTICE',
				16:'E_CORE_ERROR',
				32:'E_CORE_WARNING',
				64:'E_COMPILE_ERROR',
				128:'E_COMPILE_WARNING',
				256:'E_USER_ERROR',
				512:'E_USER_WARNING',
				1024:'E_USER_NOTICE',
				2048:'E_STRICT',
				4096:'E_RECOVERABLE_ERROR',
				8192:'E_DEPRECATED',
				16384:'E_USER_DEPRECATED',
				32767:'E_ALL',
			};
			var multilinetab = '    ';							          // the tab character
			var multilinetabce = '&nbsp;&nbsp;&nbsp;&nbsp;';	// the html character entity tab character
			var defaultlineheight = 1.2;                      // the default line height (will vary across browsers)
			var x_arrowup = '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH3gocDAMR/BitLAAAAB1pVFh0Q29tbWVudAAAAAAAQ3JlYXRlZCB3aXRoIEdJTVBkLmUHAAABHUlEQVQ4y53TvyuFcRTH8ZdfdQ3Ij0sySFIGWWQQWUyULDLZbBZl8jObP+DaZDcphdG/YGO7mySD6BbXfe71GJDn0ePpuqdO5/T9nDrv7/l+DymWJ5OnLq2mPk0ssVliVi12SesN4TUPBzT8m6CDjTIqZCdY+Ksu8X6ntPXzFDl6viC7Q1AVQTdbZUS8bZqlqgiOaB/h8VsIf2LhmK4cpVSCAXbKPlmDCMU7LXMspxIckB3lISqE8fz1hI4cxUSCwa/uUYJKfBbNM6wkEuzTM879X8/1TRJSPKczx0uMYJjdX5OPeeUnZiZZjRFs0zvBXbW/NCQ4o/OQQj2MsJfWPYGmaYo1qFunb5Lb/+5KSHBOd+MQi29c1bJwY8x/AOXyYbfGX3b4AAAAAElFTkSuQmCC" alt="arrow-up.png" width="10" height="10">';
			var x_arrowdn = '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH3gocDAQFqYPvlgAAAB1pVFh0Q29tbWVudAAAAAAAQ3JlYXRlZCB3aXRoIEdJTVBkLmUHAAABQUlEQVQ4y53TsUpcURAG4G9dohALwSZgZ+87WIZUok8gSRGsooJV+oBBDIqQJqxm0UpEi+CSvIOFj2CzuwkkbBLNYu7eey2yB4/XvQtxYJiZMzP/zDBnKp9YmGbZA6jNbuUlE6t8w2hwVJBHeqC84KszVT3jep4/4zzN+86sL4OeRW9B/mBzkaMR+MiHHn97KOO0YJ+yAVU453qOX495lkfV80InQe/w7gVHMBLm26fWozusi8AN3oa8GOCyyVpWmDvtc7A7bK3TvgcAu+wldBMk0expZDdYj3PuAJxw1WIlrhxzh61NWqUAUKOecDloC595U4y/B9Cg22I1KyT/ZHv734cbDgAH7Cf8TqP9fxlQvRSg38Wr9Lb6znu+/tehzDJ2zPcT8iWelMVVyxwXpLO0M5qvOXzItZrh0XMmh8XcAH/pl7mZtX2GAAAAAElFTkSuQmCC" alt="arrow-dn.png" width="10" height="10">';
			var computedlineheight;
			var inputplaceholder = '// Type your PHP expression here, then press Enter. Press the Up key to prefill the previous command';
			var textareaplaceholder = '// Type your PHP expression here, then press Ctrl+Enter. Press Ctrl+Up to prefill the previous command';
			
			document.addEventListener("DOMContentLoaded", function(){
				var c = document.getElementById('c');		// the console
				var r = document.getElementById('r');		// the response
				var x = document.getElementById('x');		// the expander control
								
				c.focus();
				
				computedlineheight = getLineHeight(c);
				
				c.addEventListener('keyup', function(event){
					if((c.tagName.toLowerCase() == 'input'
					  && event.keyCode == 38)
					|| (c.tagName.toLowerCase() == 'textarea'
					  && event.keyCode == 38
					  && event.ctrlKey)){
						// gets the last command on [up] or [ctrl+up]
						ajaxRequest({
							type: 'POST',
							url: 'phpi.php',
							data:'fn=getLast',
							dataType:'json',
							success:function(result){
                if(c.tagName.toLowerCase() == 'input'){
                  // replace newlines with spaces
                  var last = result.last.replace(/\\n/g, ' ');
                } else {
                  var last = result.last;
                }
								c.value = last;
							},
							fail:function(jqXHR, textStatus){
								var error = document.createElement('li');
								error.createAttribute('class');
								error.setAttribute('class', 'c_err');
								error.innerText = 'Error - the Ajax call failed with status: '+String(textStatus);
							}
						});
					}
					if(c.tagName.toLowerCase() == 'textarea'){
						// any keypress in the textarea that could change the content
						resizeTextarea();
					}
				});
				
				c.addEventListener('keypress', function(event){
					if((c.tagName.toLowerCase() == 'input'
					  && event.keyCode == 13)
					|| (c.tagName.toLowerCase() == 'textarea'
					  && ( event.keyCode == 10 || event.keyCode == 13 )
					  && event.ctrlKey)){
						// submit
						submitCommand();
					}
				});
				
				c.addEventListener('keydown', function(event){
					if(c.tagName.toLowerCase() == 'textarea'
					&& event.keyCode == 9){
						event.preventDefault();
						var caretpos = c.selectionStart;
						console.info(caretpos);
						var value = c.value;
						value = value.substring(0, caretpos) + multilinetab + value.substring(caretpos);
						c.value = value;
						c.selectionStart = c.selectionEnd = caretpos + multilinetab.length;
					}
				});
				
				resizeInput();
			
				x.addEventListener('click', function(event){
					console.warn(c);
					var value = c.value;
					var attrs = c.attributes;
					attrs = Array.prototype.slice.call(attrs);		// to convert to array
					
					if(c.tagName.toLowerCase() == 'input'){
						// remove the input and insert the textarea
						var new_c = document.createElement('textarea');
						attrs.forEach(function(item){
							var new_attr = document.createAttribute(item.name);
							new_attr.nodeValue = item.value;
							new_c.setAttributeNode(new_attr);
						});
						new_c.removeAttribute('type');
						new_c.setAttribute('placeholder', textareaplaceholder);
						c.parentNode.insertBefore(new_c, c);
						c.parentNode.removeChild(c);                // remove the original c
						x.innerHTML = x_arrowup;
						resizeTextarea();                           // to ensure the correct size
					} else {
						// remove the textarea and insert the input
						value.replace(/\\n/g, ' ');					        // collapse to one line
						var new_c = document.createElement('input');
						attrs.forEach(function(item){
							var new_attr = document.createAttribute(item.name);
							new_attr.nodeValue = item.value;
							new_c.setAttributeNode(new_attr);
						});
						var new_attr = document.createAttribute('type');
						new_attr.nodeValue = 'text';
						new_c.setAttributeNode(new_attr);
						new_c.setAttribute('placeholder', inputplaceholder);
						new_c.style.height = String(defaultlineheight)+'em';
						c.parentNode.insertBefore(new_c, c);
						c.parentNode.removeChild(c);                // remove the original c
						x.innerHTML = x_arrowdn;
						resizeInput();                              // to ensure the correct size
					}
					c = document.getElementById('c');			        // retarget #c for continuation
					c.value = value;
					
					// respawn the listeners for #c
					c.addEventListener('keyup', function(event){
						if((c.tagName.toLowerCase() == 'input'
						  && event.keyCode == 38)
						|| (c.tagName.toLowerCase() == 'textarea'
						  && event.keyCode == 38
						  && event.ctrlKey)){
							// gets the last command on [up]
							ajaxRequest({
								type: 'POST',
								url: 'phpi.php',
								data:'fn=getLast',
								dataType:'json',
								success:function(result){
									c.value = result.last;
								},
								fail:function(jqXHR, textStatus){
									var error = document.createElement('li');
									error.createAttribute('class');
									error.setAttribute('class', 'c_err');
									error.innerText = 'Error - the Ajax call failed with status: '+String(textStatus);
								}
							});
						}
						if(c.tagName.toLowerCase() == 'textarea'){
							// any keypress in the textarea that could change the content
							resizeTextarea();
						}
					});
					c.addEventListener('keypress', function(event){
						if((c.tagName.toLowerCase() == 'input'
						  && event.keyCode == 13)
						|| (c.tagName.toLowerCase() == 'textarea'
						  && ( event.keyCode == 10 || event.keyCode == 13 )
						  && event.ctrlKey)){
							// submit
							submitCommand();
						}
					});
					c.addEventListener('keydown', function(event){
						if(c.tagName.toLowerCase() == 'textarea'
						&& event.keyCode == 9){
							event.preventDefault();
							var caretpos = c.selectionStart;
							console.info(caretpos);
							var value = c.value;
							value = value.substring(0, caretpos) + multilinetab + value.substring(caretpos);
							c.value = value;
							c.selectionStart = c.selectionEnd = caretpos + multilinetab.length;
						} 
					});
				});
				
				// DOMContentLoaded ends here
			});
			
			window.onresize = function(event){
				resizeInput();
			}
			
			function resizeInput(){
				// resizes input or textarea horizontally only
				var w_width = Math.floor(document.body.clientWidth);  // was window.innerWidth
				var l_width = Math.floor(c.previousSibling.offsetWidth);
				var x_width = Math.floor(x.offsetWidth);
				c.style.width = String(w_width - l_width - x_width - 1)+'px';
			}
			
			function resizeTextarea(){
				// resizes textarea vertically
				var lines = (( c.value.match(/\\n/g) || [] ).length + 2) * defaultlineheight;	// 2 because we want to always have one blank trailing line
				c.style.height = String(lines)+'em';
//				var lines = (( c.value.match(/\\n/g) || [] ).length + 2) * computedlineheight;	// 2 because we want to always have one blank trailing line
//				c.style.height = String(lines)+'px';
			}
			
			function submitCommand(){
				// submit the command to the processor
				var cvalue = String(c.value);
				ajaxRequest({
					type:'POST',
					url:'phpi.php',
					data: jsonToUrl({"fn":"executePHP","eval":cvalue}, true),
					dataType:'json',
					success:function(result){
						var i = result.input;
						var o = result.output;
						var li = document.createElement('li');
						if(c.tagName.toLowerCase() == 'textarea'){
							i = i.split('<br />');
							for(line in i){
								if(line != 0){
//  						&& line != (i.length-1)){
									i[line] = multilinetabce + i[line];
								}
							}
							console.log(i);
							i = i.join('<br />');
						}
						li.innerHTML = i;
						r.appendChild(li);
						if(o.length > 0){
							var li = document.createElement('li');
							li.innerHTML = String(o);
							var attr = document.createAttribute('class');
							attr.nodeValue = 'output';
							li.setAttributeNode(attr);
							r.appendChild(li);
						}
						if(result.error.error != null){
							// echo error too
							console.warn('PHP provided the following error details:-');
              console.warn(result.error);
							var li = document.createElement('li');
							li.innerHTML = '<strong>'+errortypes[result.error.error.type]+'</strong> error with message <strong>'+result.error.error.message+'</strong> was returned by PHP at line <strong>'+result.error.error.line+'</strong> in file <strong>'+result.error.error.file+'</strong>'
							var attr = document.createAttribute('class');
							attr.nodeValue = 'c_err';
							li.setAttributeNode(attr);
							r.appendChild(li);
							if(result.error.trace != null){
								var trace = result.error.trace;
								var li = document.createElement('li');
								li.innerText = 'Traceback:-';
								var attr = document.createAttribute('class');
								attr.nodeValue = 'c_err';
								li.setAttributeNode(attr);
								r.appendChild(li);
								var tbul_main = document.createElement('ul');
								var attr_main = document.createAttribute('class');
								attr_main.nodeValue = 'trace';
								tbul_main.setAttributeNode(attr_main);
								if(trace != null){
									for(var index in trace[0]){
										if(index == 'args'){
											// is an object in an array
											var tbli = document.createElement('li');
											var tbliattr = document.createAttribute('class');
											tbliattr.nodeValue = 'c_err';
											tbli.setAttributeNode(tbliattr);
											tbli.innerText = 'args:-';
											tbul_main.appendChild(tbli);
											
											var tbul = document.createElement('ul');
											var tbulattr = document.createAttribute('class');
											tbulattr.nodeValue = 'c_err';
											tbul.setAttributeNode(tbulattr);
											for(var argindex in trace[0][index][0]){
												var tbulli = document.createElement('li');
												var tbulliattr = document.createAttribute('class');
												tbulliattr.nodeValue = 'c_err';
												tbulli.setAttributeNode(tbulliattr);
												tbulli.innerHTML = String(argindex) + ': <strong>' + trace[0][index][0][argindex] + '</strong>';
												tbul.appendChild(tbulli);
											}
											var tbli = document.createElement('li');
											var tbliattr = document.createAttribute('class');
											tbliattr.nodeValue = 'c_err';
											tbli.setAttributeNode(tbliattr);
											tbli.appendChild(tbul);
											tbul_main.appendChild(tbli);
										} else {
											var tbli = document.createElement('li');
											var tbliattr = document.createAttribute('class');
											tbliattr.nodeValue = 'c_err';
											tbli.setAttributeNode(tbliattr);
											tbli.innerHTML = String(index) + ': <strong>' + trace[0][index] + '</strong>';
											tbul_main.appendChild(tbli);
										}
									}
								}
								r.appendChild(tbul_main);
							}
						}
						c.value = '';
						
						resizeInput();
						var body = document.body;
						var html = document.documentElement;
						var documentheight = Math.max(
							body.scrollHeight,
							body.offsetHeight,
							html.clientHeight,
							html.scrollHeight,
							html.offsetHeight
						);
						if(documentheight > window.innerHeight){
							html.scrollTop = documentheight;    // for ie
              body.scrollTop = documentheight;    // for other browsers
						}
					},
					fail:function(xhr, ts){
						console.log('fail');
						console.log(xhr);
						if(ts == 500){
							if(xhr.responseText != ''){
								// xhr.responseText should be valid JSON from catchFatalError()
								var error = JSON.parse(xhr.responseText);
								var li = document.createElement('li');
								li.innerHTML = String(error.input);
								r.appendChild(li);
								var li = document.createElement('li');
								var attr = document.createAttribute('class');
								attr.nodeValue = 'c_err';
								li.setAttributeNode(attr);
								li.innerHTML = '<strong>'+errortypes[error.error.error.type]+'</strong> error with message <strong>'+error.error.error.message+'</strong> was returned by PHP at line <strong>'+error.error.error.line+'</strong> in file <strong>'+error.error.error.file+'</strong>';
								r.appendChild(li);
								if(error.error.trace != null){
									var trace = error.error.trace;
									var li = document.createElement('li');
									var attr = document.createAttribute('class');
									attr.nodeValue = 'c_err';
									li.setAttributeNode(attr);
									li.innerText = 'Traceback:-';
									r.appendChild(li);
									
									var trace = error.error.trace;
									var tbul_main = document.createElement('ul');
									var attr_main = document.createAttribute('class');
									attr_main.nodeValue = 'trace';
									tbul_main.setAttributeNode(attr_main);
									if(trace != null){
										for(var index in trace[0]){
											if(index == 'args'){
												// is an object in an array
												var tbli = document.createElement('li');
												var tbliattr = document.createAttribute('class');
												tbliattr.nodeValue = 'c_err';
												tbli.setAttributeNode(tbliattr);
												tbli.innerText = 'args:-';
												tbul_main.appendChild(tbli);
												
												var tbul = document.createElement('ul');
												var tbulattr = document.createAttribute('class');
												tbulattr.nodeValue = 'c_err';
												tbul.setAttributeNode(tbulattr);
												for(var argindex in trace[0][index][0]){
													var tbulli = document.createElement('li');
													var tbulliattr = document.createAttribute('class');
													tbulliattr.nodeValue = 'c_err';
													tbulli.setAttributeNode(tbulliattr);
													tbulli.innerHTML = String(argindex) + ': <strong>' + trace[0][index][0][argindex] + '</strong>';
													tbul.appendChild(tbulli);
												}
												var tbli = document.createElement('li');
												var tbliattr = document.createAttribute('class');
												tbliattr.nodeValue = 'c_err';
												tbli.setAttributeNode(tbliattr);
												tbli.appendChild(tbul);
												tbul_main.appendChild(tbli);
											} else {
												var tbli = document.createElement('li');
												var tbliattr = document.createAttribute('class');
												tbliattr.nodeValue = 'c_err';
												tbli.setAttributeNode(tbliattr);
												tbli.innerHTML = String(index) + ': <strong>' + trace[0][index] + '</strong>';
												tbul_main.appendChild(tbli);
											}
										}
									}
									r.appendChild(tbul_main);
								}
							} else {
								var li = document.createElement('li');
								li.innerHTML = '<span class="r_pmt">&gt;&gt;&gt;</span>&nbsp;&lt;?php&nbsp;' + String(c.value) + ' ?&gt;';
								r.appendChild(li);
								var li = document.createElement('li');
								var attr = document.createAttribute('class');
								attr.nodeValue = 'c_err';
								li.setAttributeNode(attr);
								li.innerText = 'The server returned error 500 - this usually indicates a fatal error in PHP. Check your code for syntax errors and retry.';
								r.appendChild(li);
							}
						
              resizeInput();
							var body = document.body;
							var html = document.documentElement;
							var documentheight = Math.max(
								body.scrollHeight,
								body.offsetHeight,
								html.clientHeight,
								html.scrollHeight,
								html.offsetHeight
							);
							if(documentheight > window.innerHeight){
                html.scrollTop = documentheight;    // for ie
                body.scrollTop = documentheight;    // for other browsers
							}
						} else {
							var li = document.createElement('li');
							li.innerHTML = '<span class="r_pmt">&gt;&gt;&gt;</span>&nbsp;&lt;?php&nbsp;<li>&lt;?php '+$('input#c').val()+' ?&gt;';
							r.appendChild(li);
							var li = document.createElement('li');
							var attr = document.createAttribute('class');
							attr.nodeValue = 'c_err';
							li.setAttributeNode(attr);
							li.innerText = 'Error - the Ajax call failed with status: '+ts+'. It\'s unlikely this was caused by your PHP statement.';
							r.appendChild(li);
							
              resizeInput();
							var body = document.body;
							var html = document.documentElement;
							var documentheight = Math.max(
								body.scrollHeight,
								body.offsetHeight,
								html.clientHeight,
								html.scrollHeight,
								html.offsetHeight
							);
							if(documentheight > window.innerHeight){
                html.scrollTop = documentheight;    // for ie
                body.scrollTop = documentheight;    // for other browsers
							}
						}
					},
					error:function(x,e){ /// <-- should e not be error?
						console.log('error');
						// if returning 500 status, most likely fatal PHP error
						if(x.status == 500){
							if(x.responseText != ''){
								// x.responseText should be valid JSON from catchFatalError()
								var error = JSON.parse(x.responseText);
								var li = document.createElement('li');
								li.innerH = String(error.input);
								r.appendChild(li);
								var li = document.createElement('li');
								var attr = document.createAttribute('class');
								attr.nodeValue = 'c_err';
								li.setAttributeNode(attr);
								li.innerHTML = '<strong>'+errortypes[error.error.error.type]+'</strong> error with message <strong>'+error.error.error.message+'</strong> was returned by PHP at line <strong>'+error.error.error.line+'</strong> in file <strong>'+error.error.error.file+'</strong>';
								r.appendChild(li);
								if(error.error.trace != null){
									var trace = error.error.trace;
									var li = document.createElement('li');
									var attr = document.createAttribute('class');
									attr.nodeValue = 'c_err';
									li.setAttributeNode(attr);
									li.innerText = 'Traceback:-';
									r.appendChild(li);
									
									var trace = error.error.trace;
									var tbul_main = document.createElement('ul');
									var attr_main = document.createAttribute('class');
									attr_main.nodeValue = 'trace';
									tbul_main.setAttributeNode(attr_main);
									if(trace != null){
										for(var index in trace[0]){
											if(index == 'args'){
												// is an object in an array
												var tbli = document.createElement('li');
												var tbliattr = document.createAttribute('class');
												tbliattr.nodeValue = 'c_err';
												tbli.setAttributeNode(tbliattr);
												tbli.innerText = 'args:-';
												tbul_main.appendChild(tbli);
												
												var tbul = document.createElement('ul');
												var tbulattr = document.createAttribute('class');
												tbulattr.nodeValue = 'c_err';
												tbul.setAttributeNode(tbliattr);
												for(var argindex in trace[0][index][0]){
													var tbulli = document.createElement('li');
													var tbulliattr = document.createAttribute('class');
													tbulliattr.nodeValue = 'c_err';
													tbulli.setAttributeNode(tbulliattr);
													tbulli.innerHTML = String(argindex) + ': <strong>' + trace[0][index][0][argindex] + '</strong>';
													tbul.appendChild(tbulli);
												}
												var tbli = document.createElement('li');
												var tbliattr = document.createAttribute('class');
												tbliattr.nodeValue = 'c_err';
												tbli.setAttributeNode(tbliattr);
												tbli.appendChild(tbul);
												tbul_main.appendChild(tbli);
											} else {
												var tbli = document.createElement('li');
												var tbliattr = document.createAttribute('class');
												tbliattr.nodeValue = 'c_err';
												tbli.setAttributeNode(tbliattr);
												tbli.innerHTML = String(index) + ': <strong>' + trace[0][index] + '</strong>';
												tbul_main.appendChild(tbli);
											}
										}
									}
									r.appendChild(tbul_main);
								}
							} else {
								var li = document.createElement('li');
								li.innerHTML = '<span class="r_pmt">&gt;&gt;&gt;</span>&nbsp;&lt;?php&nbsp;' + String(c.value) + ' ?&gt;';
								r.appendChild(li);
								var li = document.createElement('li');
								var attr = document.createAttribute('class');
								attr.nodeValue = 'c_err';
								li.setAttributeNode(attr);
								li.innerText = 'The server returned error 500 - this usually indicates a fatal error in PHP. Check your code for syntax errors and retry.';
								r.appendChild(li);
							}
						
              resizeInput();
							var body = document.body;
							var html = document.documentElement;
							var documentheight = Math.max(
								body.scrollHeight,
								body.offsetHeight,
								html.clientHeight,
								html.scrollHeight,
								html.offsetHeight
							);
							if(documentheight > window.innerHeight){
                html.scrollTop = documentheight;    // for ie
                body.scrollTop = documentheight;    // for other browsers
							}
						}
					}
				});
			}
			
			function ajaxRequest(args){
				if(typeof args.type != 'string') args.type = "GET";
				if(typeof args.success != 'function') args.success = function(result){ 
					console.warn('XMLHTTPRequest Success, but no success handler specified'); 
					console.warn(result); 
				}
				if(typeof args.fail != 'function') args.fail = function(xhr, ts){
					console.warn('XMLHTTPRequest Failure (Status '+ts+'), but no failure handler specified');
					console.warn(xhr);
				}
				if(typeof args.async != 'boolean') args.async = true;
				if(typeof args.data != 'string') args.data = null;
				
				var xhr;
				if(window.XMLHttpRequest){
					xhr = new XMLHttpRequest();
				} else if(window.ActiveXObject) {
					xhr = new ActiveXObject("Microsoft.XMLHTTP");
				}
				xhr.onreadystatechange = function(){
					if(xhr.readyState === 4){
						var ts = xhr.status;
						if(ts === 200){
							if(typeof args.dataType == 'string' && args.dataType == 'json'){
								var result = JSON.parse(xhr.responseText);
							} else {
								var result = xhr.responseText;
							}
							args.success(result);
						} else {
							args.fail(xhr, ts);
						}
					}
				}
				xhr.open(args.type, args.url, args.async);
				if(args.type.toUpperCase() == "POST"){
					xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
				}
				xhr.send(args.data);
			}
			
			function jsonToUrl(json, noquery){
				// parses a json object into a url query string
				if(typeof noquery != 'boolean') noquery = true;
				var toinclude = [];
				for(var key in json){
					if(json.hasOwnProperty(key)){
						var thisvar = encodeURIComponent(key);
						thisvar += '=';
						thisvar += encodeURIComponent(json[key]);
						toinclude.push(thisvar);
					}
				}
				var prefix = noquery ? '' : '?';
				var url = prefix + toinclude.join('&');
				return url;
			}
			
			function parseCSS(value, tolerate){
				// extracts the numbers and units from a css value
				// only works for single values, where units follow the numbers
				// when tolerate == true, non-[value][unit] return value
				if(value == "") return { value:0, tolerated:true };
				var parsed = value.match(/([0-9.]+)([a-z%]+)/);
				if(parsed == null) return { value:value, tolerated:true };
				if(parsed.length > 1){
					// both queries returned values
					return {
						value:Number(parsed[1]),
						units:parsed[2],
					};
				} else {
					// only one parsable value
					if(Number(parsed[0]) == NaN){
						// return it in textual form
						return {
							value:parsed[0],
						};
					} else {
						// return it in numeric form (as we know it will numerise)
						return {
							value:Number(parsed[0]),
						};
					}
				}
			}
			
			function getLineHeight(element){
				// gets the line-height of the specified element in px
				// element should be a dom object
				
				// getComputedStyle provides line-height in px for all formats but 'normal'
				
				var CHROME_NORMAL_LINEHEIGHT = 1.3;	// this is an estimated catch-all lineheight for chrome
				
				if(!element.hasOwnProperty('nodeType')) return false;
				
				var lineheight = getComputedStyle(element).lineHeight;
				lineheight = parseCSS(lineheight);		// lineheight will either be 'normal' or px value
				if(typeof lineheight.units == 'px'){
					// the lineheight was provided in px
					lineheight = lineheight.value;
				} else {
					// not provided in px, so make it use px by applying a line-height
					var origlineheight = lineheight;
					element.style.lineHeight = CHROME_NORMAL_LINEHEIGHT;
					lineheight = getComputedStyle(element).lineHeight;
					lineheight = parseCSS(lineheight);
					lineheight = lineheight.value;
					element.style.lineheight = origlineheight;    // restore the original lineheight
				}
				return lineheight;
			}
		</script>
	</head>
	<body>
		<div id="container">
			<h1>PHP Interpreter <span class="feint">Copyright (C) 2014 Richard Chaffer</span></h1>
			<h2>Not for deployment on production systems!</h2>
			<div id="prompt">
				<ul id="r" name="r">
<!--				<li><span class="r_pmt">&nbsp;&nbsp;&nbsp;&nbsp;</span><span class="r_com">Please type your PHP below. The up key can be used to prefill the commandline with the last command.</span></li> -->
				</ul>
				<div id="c_box">
					<label for="c"><span class="c_pmt">&gt;&gt;&gt;&nbsp;</span></label><input type="text" id="c" name="c" placeholder="// Type your PHP expression here. Press the up key to prefill the previous command" autofocus><div id="x" title="Click to allow multiline events"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH3gocDAQFqYPvlgAAAB1pVFh0Q29tbWVudAAAAAAAQ3JlYXRlZCB3aXRoIEdJTVBkLmUHAAABQUlEQVQ4y53TsUpcURAG4G9dohALwSZgZ+87WIZUok8gSRGsooJV+oBBDIqQJqxm0UpEi+CSvIOFj2CzuwkkbBLNYu7eey2yB4/XvQtxYJiZMzP/zDBnKp9YmGbZA6jNbuUlE6t8w2hwVJBHeqC84KszVT3jep4/4zzN+86sL4OeRW9B/mBzkaMR+MiHHn97KOO0YJ+yAVU453qOX495lkfV80InQe/w7gVHMBLm26fWozusi8AN3oa8GOCyyVpWmDvtc7A7bK3TvgcAu+wldBMk0expZDdYj3PuAJxw1WIlrhxzh61NWqUAUKOecDloC595U4y/B9Cg22I1KyT/ZHv734cbDgAH7Cf8TqP9fxlQvRSg38Wr9Lb6znu+/tehzDJ2zPcT8iWelMVVyxwXpLO0M5qvOXzItZrh0XMmh8XcAH/pl7mZtX2GAAAAAElFTkSuQmCC" alt="arrow-dn.png" width="10" height="10"></div>
					<div class="clearfix"></div>
				</div>
			</div>
		</div>
	</body>
</html>

EOS;
	}
	
/*
	The MIT License (MIT)

  Copyright (c) 2014 Richard Chaffer

  Permission is hereby granted, free of charge, to any person obtaining a copy of
  this software and associated documentation files (the "Software"), to deal in
  the Software without restriction, including without limitation the rights to
  use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
  the Software, and to permit persons to whom the Software is furnished to do so,
  subject to the following conditions:

  The above copyright notice and this permission notice shall be included in all
  copies or substantial portions of the Software.

  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
  FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
  COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
  IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
  CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/
?>
