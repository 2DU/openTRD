<?php
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);

	require_once('./view/scarlet/skin.php');

	require_once('./route/main_main.php');

	require_once('./route/board_main.php');
	require_once('./route/board_add.php');

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

    function do_file_fix($data) {
        return preg_replace('/\/index.php$/' , '', $_SERVER['PHP_SELF']).$data;
    }

    function do_redirect($data) {
        header('Location: '.$data);
    }

	function do_echo_to_js($data) {
		return '<script>console.log(\''.preg_replace('/\'/', '\\\'', $data).'\');</script>';
	}

	function do_check_admin($id = '') {
		global $db;
		
		if($id === '') {
			$id = get_id();
		} 
		
		if(!array_key_exists('id', $_SESSION)) {
			return 0;
		} else {
			$sql_do = $db -> prepare(
				'select set_data from m_user_set '.
				'where user_name = ? and set_name = "acl"'
			);
			$sql_do -> bindParam(1, $id);
			$sql_do = $sql_do -> execute();
			$sql_end = $sql_do -> fetchArray();			
			if(!$sql_end) {
				return 0;
			} else {
				if($sql_end[0] === 'normal') {
					return 0;
				} else {
					return 1;
				}
			}
		}
	}
    
    function get_lang($name) {
        global $data_lang;

        if(array_key_exists($name, $data_lang)) {
            return $data_lang[$name];
        } else {
            return $name." (M)";
        }
    }

 	function get_id() {
        if(array_key_exists('id', $_SESSION)) {
            $ip = $_SESSION["id"];
        } else {
            for($i = 0; $i < 3; $i++) {
                if(
					array_key_exists('X_REAL_IP', $_SERVER) && 
					$i < 1
				) {
                    $ip = $_SERVER['X_REAL_IP'];
                } elseif(
					array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) &&
					$i < 2
				) {
                    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                } elseif(
					array_key_exists('REMOTE_ADDR', $_SERVER) &&
					$i < 3
				) {
                    $ip = $_SERVER['REMOTE_ADDR'];
                } else {
                    $ip = '0.0.0.0';
                }

                if(is_array($ip)) {
                    $ip = $ip[0];
                }
				
				$ip = explode(',', $ip)[0];

                if($ip !== '::1' && $ip !== '127.0.0.1') {
                    break;
                }
            }
        }

        return $ip;
    }

    function init_main(
        $data_ver_now,
        $data_ver_last
    ) {
        $data_ver_now_int = (int)preg_replace(
            '/\./', 
            '', 
            $data_ver_now
        );
		
		if($data_ver_now_int < 1) {
			// b_set = ['b_id', 'set_name', 'set_data']
			// b_data = ['b_id', 'id', 'name', 'data', 'date']
			// m_user_set = ['user_name', 'set_name', 'set_data']
			// m_set = ['set_name', 'set_data', 'set_cover']
			$sql_do = $db -> prepare(
				'create table if not exists '.
				'b_set(b_id longtext, set_name longtext, set_data longtext)'
			);
			$sql_do -> execute();
		
			$sql_do = $db -> prepare(
				'create table if not exists '.
				'b_data(b_id longtext, id longtext, name longtext, data longtext, date longtext)'
			);
			$sql_do -> execute();
			
			$sql_do = $db -> prepare(
				'create table if not exists '.
				'm_user_set(user_name longtext, set_name longtext, set_data longtext)'
			);
			$sql_do -> execute();
			
			$sql_do = $db -> prepare(
				'create table if not exists '.
				'm_set(set_name longtext, set_data longtext, set_cover longtext)'
			);
			$sql_do -> execute();
			
			$db -> exec('commit');
		}
		
        file_put_contents(
            './ver_now.json',
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
		} elseif($_GET['v'] === 'u_main') {
			echo route_user_main();
		} elseif($_GET['v'] === 'u_login') {
			echo route_user_login($db);
		} elseif($_GET['v'] === 'u_signup') {
			echo route_user_register($db);
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