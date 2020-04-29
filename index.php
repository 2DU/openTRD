<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL & ~E_NOTICE);

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
        return "<script>window.location.href = '".$data."';</script>";
    }

    function check_admin($id = NULL) {
        global $conn;

        if($id === NULL) {
            $id = $_SESSION["id"];
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
                } else if($_SERVER['HTTP_X_FORWARDED_FOR']) {
                    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                } else if($_SERVER['REMOTE_ADDR']) {
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

    if($_GET['v']) {
        if($_GET['v'] === 'main') {
            $data = ''; 

            $sql = $conn -> prepare('select name, num from b_list');
            $sql -> execute([]);
            $sql_data = $sql -> fetchAll();
            
            $i = 0;
            while($sql_data[$i]) {
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
                $tool = [['게시판 추가', '?v=add_b']];
            } else {
                $tool = [];
            }

            echo load_render('메인 페이지', $data, $tool);
        } else if($_GET['v'] === 'add_b') {
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
                        echo load_render('오류', '필수 항목을 입력하세요.');
                    }
                } else {
                    $data = "
                        <form method=\"post\">
                            게시판 이름
                            <br>
                            <input name=\"b_name\">
                            <br>
                            <br>
                            타입
                            <br>
                            <select name=\"b_type\">
                                <option value=\"thread\">스레드</option>
                            </select>
                            <br>
                            <br>
                            <button type=\"submit\">추가</buttom>
                        </form>
                    ";

                    echo load_render('게시판 추가', $data, [["돌아가기", "?v=main"]]);
                }
            } else {
                echo load_render('오류', '권한이 부족합니다.');
            }
        } else if($_GET['v'] === 'into_b') {
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

                echo load_render('게시판', $data, $tool);
            } else {
                http_response_code(404);
                echo redirect('?v=main');    
            }
        } else if($_GET['v'] === 'user') {
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
                    <li>로그인 상태 : ".$state."</li>
                    <br>
                    <li><a href=\"?v=register\">회원가입</a></li>
                    <li><a href=\"?v=login\">로그인</a></li>
                    <li><a href=\"?v=logout\">로그아웃</a></li>
                </ul>
            ";

            echo load_render('사용자 페이지', $data);
        } else if($_GET['v'] === 'register') {
            if($_SERVER['REQUEST_METHOD'] === 'POST') {
                if($_POST["id"] !== '' && $_POST["pw"] !== '' && $_POST["repeat"] !== '') {
                    if(!preg_match("/^[a-zA-Z0-9ㄱ-힣]+$/", $_POST["id"])) {
                        echo load_render('오류', '아이디에는 알파벳, 숫자, 한글만 허용됩니다.');
                    } else {
                        if($_POST["pw"] !== $_POST["repeat"]) {
                            echo load_render('오류', '비밀번호와 비밀번호 재확인이 일치하지 않습니다.');
                        } else {
                            $sql = $conn -> prepare('select name from user where name = ?');
                            $sql -> execute([$_POST["id"]]);
                            $sql_data = $sql -> fetchAll();
                            if($sql_data) {
                                echo load_render('오류', '동일한 아이디의 사용자가 있습니다.');
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
                    echo load_render('오류', '모든 항목을 입력하세요.');
                }
            } else {
                $data = "
                    <form method=\"post\">
                        아이디
                        <br>
                        <input name=\"id\">
                        <br>
                        <br>
                        비밀번호
                        <br>
                        <input type=\"password\" name=\"pw\">
                        <br>
                        <br>
                        비밀번호 재확인
                        <br>
                        <input type=\"password\" name=\"repeat\">
                        <br>
                        <br>
                        <button type=\"submit\">가입</buttom>
                    </form>
                ";

                echo load_render('회원가입', $data, [["돌아가기", "?v=user"]]);
            }    
        } else if($_GET["v"] === 'login') {
            if($_SESSION["id"]) {
                echo load_render('오류', '이미 로그인 되어 있습니다.');
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
                                echo load_render('오류', '비밀번호가 다릅니다.');    
                            }
                        } else {
                            echo load_render('오류', '계정이 없습니다.');    
                        }
                    } else {
                        echo load_render('오류', '모든 항목을 입력하세요.');
                    }
                } else {
                    $data = "
                        <form method=\"post\">
                            아이디
                            <br>
                            <input name=\"id\">
                            <br>
                            <br>
                            비밀번호
                            <br>
                            <input type=\"password\" name=\"pw\">
                            <br>
                            <br>
                            <button type=\"submit\">로그인</buttom>
                        </form>
                    ";

                    echo load_render('로그인', $data, [["돌아가기", "?v=user"]]);
                }
            }
        } else if($_GET["v"] === 'logout') {
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
?>