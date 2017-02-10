<?php
/**********************************
 * 放置公共函数区
 **********************************/
 function debug($string){
 	$now = date('Y-m-d H:i:s');
 	try{
 		$file = fopen('./debug.log', 'a+');
 		$string = $now.': '.$string."\r\n";
 		fwrite($file, $string);
 		fclose($file);
 	}catch (Exception $e) {
 		return false;
    }

 }