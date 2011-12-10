<?php
/*
LICENSE: http://docs.forwardfour.com/index.php/License

Package: System Core
Dependencies: None
Known issues: None

This class will build success and alert messages.
*/

	class Message {
	/*
	 * Check to see if the message boxes should be styled using inline styles, or external stylesheets by using the constructor method.
	 * The only time inline styles should be used is when the system is displaying a fatal error which occured before the page content could be generated.
	*/
		function __construct($input) {
			$useStyleSheet = $input;
		}
		
	//Display a success message
		public function success($message) {
			if ($this->useStyleSheet == false) {
				$return = "<style>
  .ui-widget { font-family: Verdana,Arial,sans-serif; font-size: 1.1em; }
  .ui-state-highlight { border: 1px solid #fcefa1; background: #fbf9ee url(" . ROOT . "system/images/ajax_libraries/base/ui-bg_glass_55_fbf9ee_1x400.png) 50% 50% repeat-x; color: #363636; }
  .ui-corner-all { -moz-border-radius: 4px; -webkit-border-radius: 4px; }
  .ui-icon { width: 16px; height: 16px; background-image: url(" . ROOT . "system/images/ajax_libraries/base/ui-icons_222222_256x240.png); }
  .ui-icon-info { background-position: -16px -144px; }
</style>";
			} else {
				$return = "";
			}
			
			$return .= "
<section class=\"ui-widget\">
<div class=\"ui-state-highlight ui-corner-all\" style=\"margin-top: 20px; padding: 0pt 0.7em;\"> 
<p><span class=\"ui-icon ui-icon-info\" style=\"float: left; margin-right: 0.3em;\"></span>
" . $message . "</p>
</div>
</section>";
			
			echo $return;
		}
		
	//Display an error message
		public function error($message) {
			if ($this->useStyleSheet == false) {
				$return = "<style>
  .ui-widget { font-family: Verdana,Arial,sans-serif; font-size: 1.1em; }
  .ui-state-error { border: 1px solid #cd0a0a; background: #fef1ec url(" . ROOT . "system/images/ajax_libraries/base/ui-bg_glass_95_fef1ec_1x400.png) 50% bottom repeat-x; color: #cd0a0a; }
  .ui-corner-all { -moz-border-radius: 4px; -webkit-border-radius: 4px; }
  .ui-icon { width: 16px; height: 16px; background-image: url(" . ROOT . "system/images/ajax_libraries/base/ui-icons_222222_256x240.png); }
  .ui-icon-alert { background-position: 0 -144px; }
</style>";
			} else {
				$return = "";
			}
			
			$return .= "
<section class=\"ui-widget\">
<div class=\"ui-state-error ui-corner-all\" style=\"margin-top: 20px; padding: 0pt 0.7em;\"> 
<p><span class=\"ui-icon ui-icon-alert\" style=\"float: left; margin-right: 0.3em;\"></span>
" . $message . "</p>
</div>
</section>";
			
			echo $return;
		}
		
	//Generate a message based on URL parameters
		public function generate($trigger, $triggerValue, $type, $text) {
			if (isset($_GET[$trigger]) && $_GET[$trigger] == $triggerValue) {
				switch($type) {
					case "success" :
						$this->success($text);
						break;
						
					case "error" : 
						$this->error($text);
						break;
						
					default : 
						$error = debug_backtrace();
						$this->error("<strong>Warning:</strong> An invalid message type was requested on line " .  $error['0']['line']);
						break;
				}
			}
		}
	}
	
//Instantiate the "Message" class to allow the system to easily display messages to the user.
	$message = new Message(true);
?>