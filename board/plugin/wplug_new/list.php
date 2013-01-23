<?php
$_plugin_var_save = "" ; //플러그인에서 기존변수를 저장 처리후 복구 하도록
$new_time = 12 ; 
$_view_time = (time() - $Row[timestamp]) / 3600 ;         
if( $new_time >= $_view_time) 
{
	$Row["subject"] .= " <img src=./plugin/$_plugin/images/new.gif border=0 align=absmiddle>";    
}
?>
