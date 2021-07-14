<?php
    function route_user_signup($db) {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            if(
                $_POST["id"] !== '' && 
                $_POST["pw"] !== '' && 
                $_POST["repeat"] !== ''
            ) {
                if(preg_match("/[^a-zA-Z0-9ㄱ-힣]/", $_POST["id"])) {
                    echo get_render(
                        get_lang('error'), 
                        get_lang('id_check_error')
                    );
                } else {
                    if($_POST["pw"] !== $_POST["repeat"]) {
                        echo get_render(
                            get_lang('error'), 
                            get_lang('pw_check_error')
                        );
                    } else {
                        $sql_do = $db -> prepare(
                            'select set_data from m_user_set '.
                            'where user_name = ? and set_name = "password"'
                        );
                        $sql_do -> bindParam(1, $_POST["id"]);
                        $sql_do = $sql_do -> execute();
                        $sql_end = $sql_do -> fetchArray();
                        if($sql_end) {
                            echo get_render(
                                get_lang('error'), 
                                get_lang('exist_id_error')
                            );
                        } else {
                            $sql_do = $db -> query(
                                'select set_data from m_user_set '.
                                'where set_name = "password" limit 1'
                            );
                            $sql_end = $sql_do -> fetchArray();
                            if(!$sql_end) {
                                $acl = 'owner';
                            } else {
                                $acl = 'normal';
                            }

                            $sql_do = $db -> prepare(
                                'insert into m_user_set (user_name, set_name, set_data) '.
                                'values (?, "password", ?)'
                            );
                            $sql_do -> bindParam(1, $_POST["id"]);
                            $sql_do -> bindParam(2, hash("sha256", $_POST["pw"]));
                            $sql_do -> execute();
                            
                            $sql_do = $db -> prepare(
                                'insert into m_user_set (user_name, set_name, set_data) '.
                                'values (?, "acl", ?)'
                            );
                            $sql_do -> bindParam(1, $_POST["id"]);
                            $sql_do -> bindParam(2, $acl);
                            $sql_do -> execute();
                            
                            $db -> exec('commit');

                            do_redirect('?v=u_login');
                        }
                    }
                }
            } else {
                echo get_render(
                    get_lang('error'), 
                    get_lang('input_all_error')
                );
            }
        } else {            
            $data = "
                <form method=\"post\">
                    ".get_lang('id')."
                    <hr class=\"main_hr\"> 
                    <input name=\"id\">
                    <hr class=\"main_hr\"> 
                    ".get_lang('pw')."
                    <hr class=\"main_hr\"> 
                    <input type=\"password\" name=\"pw\">
                    <hr class=\"main_hr\"> 
                    ".get_lang('pw_check')."
                    <hr class=\"main_hr\"> 
                    <input type=\"password\" name=\"repeat\">
                    <hr class=\"main_hr\"> 
                    <button type=\"submit\">".get_lang('send')."</buttom>
                </form>
            ";

            echo get_render(
                get_lang('signup'), 
                $data, 
                [[get_lang('return'), "?v=u_main"]]
            );
        }
    }
?>