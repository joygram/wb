<?php
$_plugin_var_save = "" ; //�÷����ο��� ���������� ���� ó���� ���� �ϵ���
$new_time = 12 ; 
$_view_time = (time() - $Row[timestamp]) / 3600 ;         
if( $new_time >= $_view_time) 
{
	$Row["subject"] .= " <img src=./plugin/$_plugin/images/new.gif border=0 align=absmiddle>";    
}
?>
