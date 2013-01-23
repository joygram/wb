<?
$C_base_dir   = "/home/there/whitebbs/wb"; //관리자도구에 표시된[기본 디렉토리]
$C_auth_perm  = "7555" ;  //현재 페이지의 접근권한
$counter_data = "test1" ; //사용할 카운터 이름
$counter_view_only = "" ; //카운트는 하지않고 디스플레이만 할경우 yes
include("$C_base_dir/lib/whitebbs.inc.php") ;
?> 
<html>
<body>
COUNTER EXAMPLE 
<?=$Row_c["counter"]?>
</body>
</html>
