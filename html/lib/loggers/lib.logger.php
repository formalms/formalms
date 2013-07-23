<?php defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
|   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
|   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
\ ======================================================================== */

class Log {

	/**
	 * Here we will store all the log entries
	 * @var array
	 */
	public static $maxSourceLines = 20;
	
	protected static $_start_time = false;
	
	private static $_arr_log = array();
	
	private function __construct() {}

	public static function add($str) {
		
		self::$_arr_log[] = $str;
	}
	
	public static function reset() {
		
		self::$_arr_log = array();
	}
	
	public static function start() {
		
		self::$_start_time = microtime(true);
	}
	
	public static function time() {
		
		if(self::$_start_time !== false) return number_format( (microtime(true) - self::$_start_time), 4);
		else return 'Nan';
	}
	
	public static function get_log() {
		
		return self::$_arr_log;
	}

	public static function debug() {

		self::bp();
	}
	
	/**
	 * Transform the log entries into a string
	 * @param string $separator the glue fot the entries
	 * @return string
	 */
	protected static function stringify($separator) {
		
		return implode($separator, self::$_arr_log);
	}

	/**
	 * Return all the current log entries in a simple text string format 
	 * @return string
	 */
	public static function get() {
		
		return self::stringify("\n");
	}
	
	/**
	 * Return the current log entries in an html format 
	 * @return string
	 */
	public static function html() {
		
		return self::stringify('<br />');
	}
	
	/**
	 * Print all the logs, the following code is from yii Framework, changed in order to work for forma
	 * @param filter $str 
	 */
	public static function bp($filter = false) {

		
		$trace = debug_backtrace();
		array_shift($trace);
		$last = array_shift($trace);
		
		$traceString = '';
		foreach($trace as $i=>$t)
		{
			if(!isset($t['file']))
				$t['file']='unknown';
			if(!isset($t['line']))
				$t['line']=0;
			if(!isset($t['function']))
				$t['function']='unknown';
			$traceString.="#$i {$t['file']}({$t['line']}): <b>";
			if(isset($t['object']) && is_object($t['object']))
				$traceString.=get_class($t['object']).'->';
			$traceString.="{$t['function']}</b>(".implode(', ', $t['args']).")\n";
		}
		
		echo '<!doctype html>
		<html>
		<head>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
			<title>Forma</title>
			<style type="text/css">
			/*<![CDATA[*/
			body {font-family:"Verdana";font-weight:normal;color:black;background-color:white;}
			h1 { font-family:"Verdana";font-weight:normal;font-size:18pt;color:red }
			h2 { font-family:"Verdana";font-weight:normal;font-size:14pt;color:maroon }
			h3 {font-family:"Verdana";font-weight:bold;font-size:11pt}
			p {font-family:"Verdana";font-size:9pt;}
			pre {font-family:"Lucida Console";font-size:10pt;}
			.source{font-family:"Lucida Console";font-weight:normal;background-color:#ffffee;padding:2px;}
			.logtrace{font-family:"Lucida Console";font-weight:normal;background-color:#ffffee;padding:2px;line-height:1.31em;}
			.error {background-color: #ffeeee;}
			/*]]>*/
			</style>
			</head>
			<body>';
		echo '<h3>Source File</h3><p>'.$last['file']." ({$last['line']})".'</p><div class="source"><pre>';
		$source = Log::getSourceLines($last['file'], $last['line']);
		if(empty($source))
			echo 'No source code available.';
		else {
			foreach($source as $line=>$code) {
				
				if($line !== $last['line'])
					echo sprintf("%05d: %s",$line,str_replace("\t",'    ',$code));
				else {
					echo "<div class=\"error\">";
					echo sprintf("%05d: %s",$line,str_replace("\t",'    ',$code));
					echo "</div>";
				}
			}
		}
		echo '</pre></div><!-- end of source -->';
		echo '<h3>Stack Trace</h3><div class="callstack"><pre>'.$traceString.'</pre></div><!-- end of callstack -->';	
		echo '<h3>Log Trace</h3><div class="logtrace"><pre>'.self::html().'</pre></div>'
			.'</body></html>';
		die();
	}
	
	/**
	 * This method is from Yii framework
	 * Returns the source lines around the error line.
	 * At most {@link maxSourceLines} lines will be returned.
	 * @param string source file path
	 * @param integer the error line number
	 * @return array source lines around the error line, indxed by line numbers
	 */
	public function getSourceLines($file, $line) {
		
		// determine the max number of lines to display
		$maxLines = self::$maxSourceLines;
		if($maxLines < 1) $maxLines = 1;
		else if($maxLines > 100) $maxLines = 100;

		$line--; // adjust line number to 0-based from 1-based
		if($line < 0 || ($lines = @file($file)) === false || ( $lineCount = count($lines)) <= $line)
			return array();

		$halfLines = (int)($maxLines / 2);
		$beginLine = $line - $halfLines > 0 ? $line - $halfLines : 0;
		$endLine = $line + $halfLines < $lineCount ? $line + $halfLines : $lineCount - 1;

		$sourceLines = array();
		for($i = $beginLine; $i <= $endLine; ++$i)
			$sourceLines[$i+1] = $lines[$i];
		return $sourceLines;
	}
	
}

?>