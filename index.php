<?php
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
    
    function get_lang($name) {
        global $data_lang;

        if($data_lang[$name]) {
            return $data_lang[$name];
        } else {
            return $name." (M)";
        }
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
        
        file_put_contents(
            './ver_now.json',
            json_encode($data_ver_last)
        );
		
		if($data_ver_now_int < 1) {
			// b_set = ['b_id', 'set_name', 'set_data']
			// b_data = ['b_id', 'id', 'name', 'data', 'date']
			// m_user_set = ['user_name', 'set_name', 'set_data']
			// m_set = ['set_name', 'set_data', 'set_cover']
			$sql_do = $db -> prepare(
				'create table if not exists ' +
				'b_set(b_id longtext, set_name longtext, set_data longtext)'
			);
			$sql_do -> execute();
		
			$sql_do = $db -> prepare(
				'create table if not exists ' +
				'b_data(b_id longtext, id longtext, name longtext, data longtext, date longtext)'
			);
			$sql_do -> execute();
			
			$sql_do = $db -> prepare(
				'create table if not exists ' +
				'm_user_set(user_name longtext, set_name longtext, set_data longtext)'
			);
			$sql_do -> execute();
			
			$sql_do = $db -> prepare(
				'create table if not exists ' +
				'm_set(set_name longtext, set_data longtext, set_cover longtext)'
			);
			$sql_do -> execute();
		}
    }

	/*
		$stmt = $db -> prepare('SELECT bar FROM foo WHERE id=:id');
		$stmt -> bindValue(':id', 1, SQLITE3_INTEGER);
		$result = $stmt->execute();
	*/
    
    if(file_exists('./ver_now.json')) {
        $data_ver_now = json_decode(
            file_get_contents('./ver_now.json'), 
            TRUE
        )['main'];
    } else {
        $data_ver_now = '0.0.0';
    }

    if($data_ver_now !== $data_ver_last['main']) {
        init_main(
            $data_ver_now, 
            $data_ver_last,
			$db
        );
    }
    
    echo $data_ver_now;
?>