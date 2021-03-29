<?php
    require_once('./view/scarlet/index.php');
    $ver = 2;

    $conn = new PDO('sqlite:data.db');
    session_start();

    function file_fix($url) {
        return preg_replace('/\/index.php$/' , '', $_SERVER['PHP_SELF']).$url;
    }

    function start_init() {
        global $conn;

        $sql = $conn -> prepare('create table if not exists b_list(name longtext, type longtext)');
        $sql -> execute([]);

        $sql = $conn -> prepare('create table if not exists b_data(list longtext, name longtext, data longtext, num longtext, id longtext, date longtext)');
        $sql -> execute([]);

        $sql = $conn -> prepare('create table if not exists user(name longtext, pw longtext, acl longtext)');
        $sql -> execute([]);

        $sql = $conn -> prepare('create table if not exists setting(name longtext, data longtext, cover longtext)');
        $sql -> execute([]);
    }

    function start_update($ver) {
        global $conn;

        $sql = $conn -> prepare('alter table b_list add column num longtext default ""');
        $sql -> execute([]);
    }

    function redirect($data) {
        header('Location: '.$data);
    }

    function check_admin($id = NULL) {
        global $conn;

        if($id === NULL) {
            $id = array_key_exists('id', $_SESSION) ? $_SESSION["id"] : NULL;
        }

        if($id) {
            $sql = $conn -> prepare('select acl from user where name = ?');
            $sql -> execute([$id]);
            $sql_data = $sql -> fetchAll();
            if($sql_data && $sql_data[0]['acl'] !== 'normal') {
                return 1;
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    function get_id() {
        if($_SESSION["id"]) {
            $ip = $_SESSION["id"];
        } else {
            $i = 0;
            while($i != 2) {
                if($_SERVER['X_REAL_IP'] && $i === 0) {
                    $ip = $_SERVER['X_REAL_IP'];
                } elseif($_SERVER['HTTP_X_FORWARDED_FOR']) {
                    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                } elseif($_SERVER['REMOTE_ADDR']) {
                    $ip = $_SERVER['REMOTE_ADDR'];
                } else {
                    $ip = '0.0.0.0';
                }

                if(is_array($ip)) {
                    $ip = $ip[0];
                }

                if($ip !== '::1' && $ip !== '127.0.0.1') {
                    break;
                } else {
                    $i += 1;
                }
            }
        }

        return $ip;
    }

    $lang_file = json_decode(file_get_contents('./lang/ko-KR.json'), TRUE);
    function get_lang($name) {
        global $lang_file;

        if($lang_file[$name]) {
            return $lang_file[$name];
        } else {
            return $name." (M)";
        }
    }

    start_init();
    
    $sql = $conn -> prepare('select data from setting where name = "ver"');
    $sql -> execute([]);
    $sql_data = $sql -> fetchAll();
    if(!$sql_data) {
        start_update(1);
        
        $sql = $conn -> prepare('insert into setting (name, data, cover) values ("ver", ?, "")');
        $sql -> execute([$ver]);
    } else {
        if((int)$sql_data[0]["data"] !== $ver) {
            start_update((int)$sql_data[0]["data"]);
            
            $sql = $conn -> prepare('update setting set data = ? where name = "ver"');
            $sql -> execute([$ver]);
        }
    }

    if(array_key_exists('v', $_GET)) {
        if($_GET['v'] === 'main') {
            $data = ''; 

            $sql = $conn -> prepare('select name, num from b_list');
            $sql -> execute([]);
            $sql_data = $sql -> fetchAll();
            
            $i = 0;
            while(array_key_exists($i, $sql_data)) {
                if($data === '') {
                    $data = '<ul>';
                }

                $data = $data.'<li><a href="?v=into_b&b_id='.$sql_data[$i]["num"].'">'.$sql_data[$i]["name"].'</a></li>';

                $i += 1;
            }

            if($data !== '') {
                $data = $data.'</ul>';
            }

            if(check_admin()) {
                $tool = [[get_lang('add_board'), '?v=add_b']];
            } else {
                $tool = [];
            }

            echo load_render(get_lang('main_page'), $data, $tool);
        } elseif($_GET['v'] === 'add_b') {
            if(check_admin()) {
                if($_SERVER['REQUEST_METHOD'] === 'POST') {
                    if($_POST["b_name"] !== '') {
                        $sql = $conn -> prepare('select num from b_list order by num + 0 desc');
                        $sql -> execute([]);
                        $sql_data = $sql -> fetchAll();
                        if($sql_data) {
                            $num = (int)$sql_data[0]['num'] + 1;
                        } else {
                            $num = 1;
                        }

                        $sql = $conn -> prepare('insert into b_list (name, type, num) values (?, ?, ?)');
                        $sql -> execute([$_POST["b_name"], $_POST["b_type"], $num]);

                        echo redirect('?v=main');
                    } else {
                        echo load_render(get_lang('error'), get_lang('input_error'));
                    }
                } else {
                    $data = "
                        <form method=\"post\">
                            ".get_lang('name')."
                            <br>
                            <input name=\"b_name\">
                            <br>
                            <br>
                            ".get_lang('type')."
                            <br>
                            <select name=\"b_type\">
                                <option value=\"thread\">".get_lang('thread')."</option>
                            </select>
                            <br>
                            <br>
                            <button type=\"submit\">".get_lang('add')."</buttom>
                        </form>
                    ";

                    echo load_render(get_lang('add_board'), $data, [[get_lang('return'), "?v=main"]]);
                }
            } else {
                echo load_render(get_lang('error'), get_lang('acl_error'));
            }
        } elseif($_GET['v'] === 'into_b') {
            if($_GET['b_id']) {
                $data = ''; 

                $sql = $conn -> prepare('select name, num, id, date from b_data where list = ?');
                $sql -> execute([$_GET['b_id']]);
                $sql_data = $sql -> fetchAll();
                
                $i = 0;
                while($sql_data[$i]) {
                    if($data === '') {
                        $data = '<ul>';
                    }

                    $data = $data."
                        <li>
                            <a href=\"?v=read_b&b_id=".$_GET['b_id']."&g_num=".$sql_data[$i]["num"]."\">".$sql_data[$i]["name"]."</a> |
                             ".$sql_data[$i]["id"]." |
                             ".$sql_data[$i]["date"]."
                        </li>
                    ";

                    $i += 1;
                }

                if($data !== '') {
                    $data = $data.'</ul>';
                }

                echo load_render(get_lang('board'), $data, $tool);
            } else {
                http_response_code(404);
                echo redirect('?v=main');    
            }
        } elseif($_GET['v'] === 'user') {
            $state = get_id();
            if($_SESSION["id"]) {                
                $sql = $conn -> prepare('select acl from user where name = ?');
                $sql -> execute([$state]);
                $sql_data = $sql -> fetchAll();
                if($sql_data) {
                    $state = $state.' ('.$sql_data[0]['acl'].')';
                }
            }

            $data = "
                <ul>
                    <li>".get_lang('login_state')." : ".$state."</li>
                    <br>
                    <li><a href=\"?v=register\">".get_lang('register')."</a></li>
                    <li><a href=\"?v=login\">".get_lang('login')."</a></li>
                    <li><a href=\"?v=logout\">".get_lang('logout')."</a></li>
                </ul>
            ";

            echo load_render(get_lang('user_page'), $data);
        } elseif($_GET['v'] === 'register') {
            if($_SERVER['REQUEST_METHOD'] === 'POST') {
                if($_POST["id"] !== '' && $_POST["pw"] !== '' && $_POST["repeat"] !== '') {
                    if(!preg_match("/^[a-zA-Z0-9ㄱ-힣]+$/", $_POST["id"])) {
                        echo load_render(get_lang('error'), get_lang('id_check_error'));
                    } else {
                        if($_POST["pw"] !== $_POST["repeat"]) {
                            echo load_render(get_lang('error'), get_lang('pw_check_error'));
                        } else {
                            $sql = $conn -> prepare('select name from user where name = ?');
                            $sql -> execute([$_POST["id"]]);
                            $sql_data = $sql -> fetchAll();
                            if($sql_data) {
                                echo load_render(get_lang('error'), get_lang('exist_id_error'));
                            } else {
                                $sql = $conn -> prepare('select name from user limit 1');
                                $sql -> execute([]);
                                $sql_data = $sql -> fetchAll();
                                if(!$sql_data) {
                                    $acl = 'owner';
                                } else {
                                    $acl = 'normal';
                                }

                                $sql = $conn -> prepare('insert into user (name, pw, acl) values (?, ?, ?)');
                                $sql -> execute([$_POST["id"], hash("sha256", $_POST["pw"]), $acl]);

                                echo redirect('?v=login');
                            }
                        }
                    }
                } else {
                    echo load_render(get_lang('error'), get_lang('input_all_error'));
                }
            } else {
                $data = "
                    <form method=\"post\">
                        ".get_lang('id')."
                        <br>
                        <input name=\"id\">
                        <br>
                        <br>
                        ".get_lang('pw')."
                        <br>
                        <input type=\"password\" name=\"pw\">
                        <br>
                        <br>
                        ".get_lang('pw_check')."
                        <br>
                        <input type=\"password\" name=\"repeat\">
                        <br>
                        <br>
                        <button type=\"submit\">".get_lang('send')."</buttom>
                    </form>
                ";

                echo load_render(get_lang('register'), $data, [[get_lang('return'), "?v=user"]]);
            }    
        } elseif($_GET["v"] === 'login') {
            if($_SESSION["id"]) {
                echo redirect('?v=user');
            } else {
                if($_SERVER['REQUEST_METHOD'] === 'POST') {
                    if($_POST["id"] !== '' && $_POST["pw"] !== '') {
                        $sql = $conn -> prepare('select pw from user where name = ?');
                        $sql -> execute([$_POST["id"]]);
                        $sql_data = $sql -> fetchAll();
                        if($sql_data) {
                            if(hash("sha256", $_POST["pw"]) === $sql_data[0]["pw"]) {
                                $_SESSION["id"] = $_POST["id"];

                                echo redirect('?v=user');
                            } else {
                                echo load_render(get_lang('error'), get_lang('pw_error'));    
                            }
                        } else {
                            echo load_render(get_lang('error'), get_lang('no_exist_id_error'));  
                        }
                    } else {
                        echo load_render(get_lang('error'), get_lang('input_all_error'));
                    }
                } else {
                    $data = "
                        <form method=\"post\">
                            ".get_lang('id')."
                            <br>
                            <input name=\"id\">
                            <br>
                            <br>
                            ".get_lang('pw')."
                            <br>
                            <input type=\"password\" name=\"pw\">
                            <br>
                            <br>
                            <button type=\"submit\">".get_lang('send')."</buttom>
                        </form>
                    ";

                    echo load_render(get_lang('login'), $data, [[get_lang('return'), "?v=user"]]);
                }
            }
        } elseif($_GET["v"] === 'logout') {
            if($_SESSION["id"]) {
                $_SESSION["id"] = NULL;

                echo redirect('?v=user');
            } else {
                echo redirect('?v=login');
            }
        } else {
            http_response_code(404);
            echo redirect('?v=main');    
        }
    } else {
        http_response_code(404);
        echo redirect('?v=main');
    }