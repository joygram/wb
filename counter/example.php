<?
$C_base_dir   = "/home/there/whitebbs/wb"; //�����ڵ����� ǥ�õ�[�⺻ ���丮]
$C_auth_perm  = "7555" ;  //���� �������� ���ٱ���
$counter_data = "test1" ; //����� ī���� �̸�
$counter_view_only = "" ; //ī��Ʈ�� �����ʰ� ���÷��̸� �Ұ�� yes
include("$C_base_dir/lib/whitebbs.inc.php") ;
?> 
<html>
<body>
COUNTER EXAMPLE 
<?=$Row_c["counter"]?>
</body>
</html>
