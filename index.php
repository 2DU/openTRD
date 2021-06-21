<?php
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);

	require_once('./view/scarlet/skin.php');

	require_once('./route/tool/func.php');

	require_once('./route/main_main.php');

	require_once('./route/board_main.php');
	require_once('./route/board_add.php');

	require_once('./route/post_add.php');

	require_once('./route/user_main.php');
	require_once('./route/user_login.php');
	require_once('./route/user_signup.php');
	require_once('./route/user_logout.php');

	session_start();

    $data_lang = json_decode(
        file_get_contents('./lang/ko-KR.json'), 
        TRUE
    );

    $data_ver_last = json_decode(
        file_get_contents('./ver_last.json'), 
        TRUE
    );
    
	$db = new SQLite3('data.db');
	do_global_db_1($db);

	function get_render($title, $data, $menu = []) {
		$main_head_ver = '1';
		
		$other = [];
		$other['main_head'] = '<link rel="stylesheet" href="'.do_file_fix('/view/main_css/css/main.css?ver='.$main_head_ver).'">';
		
		return skin_render($title, $data, $menu, $other);
	}

    function init_main(
        $data_ver_now,
        $data_ver_last
    ) {
		global $db;
		
        $data_ver_now_int = (int)preg_replace(
            '/\./', 
            '', 
            $data_ver_now
        );
		
		// b_set = ['b_id', 'set_name', 'set_data']
		// b_data = ['b_id', 'id', 'to_id', 'data_name', 'data']
		// m_user_set = ['user_name', 'set_name', 'set_data']
		// m_set = ['set_name', 'set_data', 'set_cover']
		
		if($data_ver_now_int < 1) {
			$db -> exec(
				'create table if not exists '.
				'b_set(b_id longtext, set_name longtext, set_data longtext)'
			);		
			$db -> exec(
				'create table if not exists '.
				'b_data(b_id longtext, id longtext, data_name longtext, data longtext)'
			);
			$db -> exec(
				'create table if not exists '.
				'm_user_set(user_name longtext, set_name longtext, set_data longtext)'
			);
			$db -> exec(
				'create table if not exists '.
				'm_set(set_name longtext, set_data longtext, set_cover longtext)'
			);
		}
		
        file_put_contents(
            './data/ver_now.json',
            json_encode($data_ver_last)
        );
    }

    if(file_exists('./ver_now.json')) {
        $data_ver_now = json_decode(
            file_get_contents('./ver_now.json'), 
            TRUE
        )['main'];
    } else {
        $data_ver_now = '0.0.0';
    }

	echo do_echo_to_js('Now version : '.$data_ver_now);
	echo do_echo_to_js('Last version : '.$data_ver_last['main']);
    if($data_ver_now !== $data_ver_last['main']) {
        init_main(
            $data_ver_now, 
            $data_ver_last
        );
    }

	/*
		$sql_do = $db -> prepare(
			'select set_data from m_user_set '.
			'where user_name = ? and set_name = "password"'
		);
		$sql_do -> bindParam(1, 'test');
		$sql_do = $sql_do -> execute();
		$sql_end = $sql_do -> fetchArray();
		
		$sql_do = $db -> query(
			'select set_data from m_user_set '.
			'where set_name = "password" limit 1'
		);
		$sql_end = $sql_do -> fetchArray();
	*/
    
    if(array_key_exists('v', $_GET)) {
		if($_GET['v'] === 'main') {
			echo route_main_main($db);
		} elseif($_GET['v'] === 'b_main') {
			echo route_board_main($db);
		} elseif($_GET['v'] === 'b_add') {
			echo route_board_add($db);
		} elseif($_GET['v'] === 'p_add') {
			echo route_post_add($db);
		} elseif($_GET['v'] === 'u_main') {
			echo route_user_main();
		} elseif($_GET['v'] === 'u_login') {
			echo route_user_login($db);
		} elseif($_GET['v'] === 'u_signup') {
			echo route_user_signup($db);
		} elseif($_GET['v'] === 'u_logout') {
			echo route_user_logout();
		} else {
			http_response_code(404);
			do_redirect('?v=main');
		}
	} else {
		http_response_code(404);
		do_redirect('?v=main');
	}
?>