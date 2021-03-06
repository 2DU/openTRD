<?php
    $db = '';

    function do_global_db_1($data_db) {
        global $db;
        
        $db = $data_db;
    }

    function do_file_fix($data) {
        return preg_replace('/\/index.php$/' , '', $_SERVER['PHP_SELF']).$data;
    }

    function do_redirect($data) {
        header('Location: '.$data);
    }

    function do_echo_to_js($data) {
        return '<script>console.log(\''.preg_replace('/\'/', '\\\'', $data).'\');</script>';
    }

    function do_html_change($data, $reverse = 0) {
        if($reverse === 0) {
            return htmlspecialchars($data);
        } else {
            return htmlspecialchars_decode($data);
        }
    }

    function do_check_xss($data) {
        if(htmlspecialchars($data) !== $data) {
            return 1;
        } else {
            return 0;
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
    
    function get_render($title, $data, $menu = []) {
        $main_head_ver = '1';
        
        $other = [];
        $other['main_head'] = '<link rel="stylesheet" href="'.do_file_fix('/view/main_css/css/main.css?ver='.$main_head_ver).'">';
        
        return skin_render($title, $data, $menu, $other);
    }

    function get_date() {
        return (string)date("Y-m-d H:i:s");
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

    function get_auth($id = '') {
        global $db;
        
        if($id === '') {
            $id = get_id();
        }
        
        if(do_check_sub($id) === 1) {
            $sql_do = $db -> prepare(
                'select set_data from m_user_set '.
                'where user_name = ? and set_name = "acl"'
            );
            $sql_do -> bindParam(1, $id);
            $sql_do = $sql_do -> execute();
            $sql_end = $sql_do -> fetchArray();
            
            return $sql_end[0];
        } else {
            return 0;
        }
    }

    function do_check_sub($id = '') {
        if($id === '') {
            $id = get_id();
        }
        
        if(!array_key_exists('id', $_SESSION)) {
            return 0;
        } else {
            return 1;
        }
    }

    function do_check_admin($id = '') {        
        if($id === '') {
            $id = get_id();
        }
        
        if(!array_key_exists('id', $_SESSION)) {
            return 0;
        } else {
            $u_auth = get_auth($id);
            if($u_auth === 0) {
                return 0;
            } else {
                if($u_auth === 'normal') {
                    return 0;
                } else {
                    return 1;
                }
            }
        }
    }

    function do_check_acl($id = '') {        
        if($id === '') {
            $id = get_id();
        }
        
        $u_auth = get_auth($id);        
        if($u_auth === 'ban') {
            return 0;
        } else {
            return 1;
        }
    }

    function do_check_b_id_exist($return_title = 0) {
        global $db;
        
        if(!array_key_exists('b_id', $_GET)) {
            return 0;
        } else {
            $sql_do = $db -> prepare(
                'select set_data from b_set '.
                'where set_name = "list" and b_id = ?'
            );
            $sql_do -> bindParam(1, $_GET['b_id']);
            $sql_do = $sql_do -> execute();
            $sql_end = $sql_do -> fetchArray();
            if(!$sql_end) {
                return 0;
            } else {
                if($return_title === 1) {
                    return $sql_end[0];
                } else {
                    return 1;
                }
            }
        }
    }
?>