원본 소스의 배포는 http://whiteBBS.net 외에는 하지 않도록 해주세요.
그리고 소스를 수정하여 프로그램이 개선되었다면 rezina@rezina.co.kr에 
알려 주시고 그 수정본을 이곳에 올릴 수 있도록 도와 주세요.

화이트보드 설치는 INSTALL.TXT 을 읽어주시기 바랍니다.
프로그램의 변경사항은 HISTORY.TXT를 읽어주시기 바랍니다.
추가될 사항은 TODO.TXT를 읽어주시기 바랍니다.

************************************************************************
** 2.1.2 이하버젼 문서입니다. : 오래됐음.
************************************************************************
///////////////////
// 업그레이드 방법
///////////////////
1. filelist.txt파일에 있는 파일이 존재하는지 확인해 보세요.
2. 화이트보드를 처음 사용하시는 분은 설치 방법으로 가세요.
3. 업그레이드 판을 다운로드 받으셔서 파일을 하나도 빠짐없이 올려 주세요.
4. 업그레이드의 경우 새로 추가한 스킨은 함께 배포하지 않았습니다.

///////////////////
//설치 방법
///////////////////
1. 압축파일을 푼다.

2. 관리자 암호를 바꾸어 준다. 
	conf/admin.php 파일을 메모장으로 열면 다음의 내용이 나온다..
	$C_admin_id = "whiteboard" ; -> 관리자 아이디
	$C_admin_password = "white" ; -> 관리자 암호
	""안을 원하는 내용으로 바꾸어 준다. 
	
3. ftp로 계정에 올린다.

4. 권한설청을 바꾼다.
	각 디렉토리와 파일의 권한(permission)을 다음과 같이 바꾸어준다.
	conf : 777
	data : 777
	skin : 777

5. admin메뉴를 실행시킨다.
	http://자기계정/whiteboard/admin/
    자세한 admin의 사용법은 whitebbs.net에 와서 확인한다. 

6. 프로그램의 수정사항 및 기능 설명은 history.txt를 읽어보세요.

7. 앞으로 추가할 사항은 todo.txt에 넣어 놓았습니다. 

8. 참조란을 꼬옥 읽어주세요.

9. 추가되어서 문서에 포함하지 못한 내용은 추후 http://whiteBBS.net에 올려
놓도록 하겠습니다.


///////////
// 참조
///////////
WhiteBoard 2.1.0
	history.txt파일을 참조해주세요.

WhiteBoard 2.0.5 
	기본으로 배포하는 스킨은 wb_로 시작하도록 변경하였습니다.


WhiteBoard 2.0 pre
..........................................................
1. 사용하시는 분들과 특히  스킨제작하시는 분들 께
..........................................................
새버젼과 관련해서 스킨 변수가 많이 바뀌었습니다.
함께 배포되는 기본스킨을 열어서 바뀐점들을 확인해주시고요...
WhiteBoard 2.0 pre 버젼은 너무 많은 기능 추가로 문서작업 마무리를 못했습니다. 
추후 WhiteBBS.net에 올려 놓겠습니다.


version 1.3.0이전 버젼인 경우 고쳐주세요.
..........................................................
1. 댓글을 달고 현재 페이지로 돌아가도록 스킨 수정하는 방법
..........................................................
댓글이 있는 스킨의 (default, wide 같은)
list.html에서 다음 부분을 찾아서 2줄을 추가하면 된다.

 <!-- 현재 페이지를 유지하기 위해 아래 2줄을 추가한다. -->
 <input type=hidden name=tot_page value='<?=$tot_page?>'>
 <input type=hidden name=cur_page value='<?=$cur_page?>'>
 <!-- 추가 끝 -->

*list.html의 일부분
-------------------
 <!-- 하단부분 -->
 <tr>
 <td width=10 height=41 valign=top><img src=skin/<?=$C_skin?>/images/7.gif></td>

<td width=260 height=41 background=skin/<?=$C_skin?/images/8.gif align=center valign=bottom nowrap>
       <?=$reply_hide_start?>
        <form action='<?=$WRITE_URL?>' name='reply_frm' method=post>
        <input type=hidden name=mode value='reply' >
        <input type=hidden name=board_group value='<?=$Row['board_group']?>' >

        <!-- 현재 페이지를 유지하기 위해 아래 2줄을 추가한다. -->
        <input type=hidden name=tot_page value='<?=$tot_page?>'>
        <input type=hidden name=cur_page value='<?=$cur_page?>'>
        <!-- 추가 끝 -->

..........................................................
2. 댓글에 스킨 적용하는 방법
..........................................................
1] 1.4.x 버전인 경우 
  1) 1.4.x 스킨에 들어있는reply_list.html 스킨디렉토리에 복사합니다.
  2) list.html 파일에 아래 줄을 추가 합니다.
     추가내용: <?=$Row['reply_list']?>
 
2] 1.3.x 이전 버전인 경우 
  1) 1.3.0 스킨에 들어있는reply_list.html 스킨디렉토리에 복사합니다.
     직접 만들경우 reply_list.html파일의 내용은list.html파일과 같은 방법으로 제작하면 됩니다.
  2) list.html 파일에 아래 줄을 추가합니다.
     추가해줄 내용: <? reply_list( $C_data, $Row['board_group'] ;?> 


  list.html파일의 일부
  --------------------
         <!-- 내용 보여 주기 -->
    <tr>
        <td width=10 height=159 background=skin/<?=$C_skin?>/images/4.gif></td> 
        <td width=260 height=159>
            <div id="<?=$Row['board_group']?>">
            <?=$Row['comment']?>
            <br>
			<!-- 댓글 기능을 위해 새로 추가한 내용 -->
			<?=$Row[reply_list?>
        
  2) 관리자로 댓글 지우기를 하실 수 있습니다.
  3) skin디렉토리에 reply_list.html 파일이 존재하지 않으면 이전버젼 방식대로 댓글이 나옵니다.

