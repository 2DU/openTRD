<?php
    function route_post_add($db) {
        $user_id = get_id();
        if(do_check_b_id_exist() !== 1) {
            do_redirect('?v=main');
        } elseif(do_check_acl($user_id) !== 1) {
            echo get_render(
                get_lang('error'), 
                get_lang('acl_error')
            );
        } else {
            if($_SERVER['REQUEST_METHOD'] === 'POST') {
                if(
                    !array_key_exists('content', $_POST) ||
                    $_POST['content'] === ''
                ) {
                    do_redirect('?v=b_main&b_id='.$_GET['b_id']);
                } elseif(
                    !array_key_exists('title', $_POST) ||
                    $_POST['title'] === ''
                ) {
                    do_redirect('?v=b_main&b_id='.$_GET['b_id']);
                } else {
                    $sql_do = $db -> prepare(
                        'select id from b_data '.
                        'where b_id = ? and data_name = "title" '.
                        'order by id + 0 desc limit 1'
                    );
                    $sql_do -> bindParam(1, $_GET['b_id']);
                    $sql_do = $sql_do -> execute();
                    $sql_end = $sql_do -> fetchArray();
                    if($sql_end) {
                        $last_id = (int)$sql_end[0];
                    } else {
                        $last_id = 0;
                    }
                    
                    $last_id = (string)($last_id + 1);

                    $sql_do = $db -> prepare(
                        'insert into b_data (b_id, id, to_id, data_name, data) '.
                        'values (?, ?, "1", "title", ?)'
                    );
                    $sql_do -> bindParam(1, $_GET['b_id']);
                    $sql_do -> bindParam(2, $last_id);
                    $sql_do -> bindParam(3, $_POST['title']);
                    $sql_do -> execute();
                    
                    $sql_do = $db -> prepare(
                        'insert into b_data (b_id, id, to_id, data_name, data) '.
                        'values (?, ?, "1", "data", ?)'
                    );
                    $sql_do -> bindParam(1, $_GET['b_id']);
                    $sql_do -> bindParam(2, $last_id);
                    $sql_do -> bindParam(3, $_POST['content']);
                    $sql_do -> execute();
                    
                    $sql_do = $db -> prepare(
                        'insert into b_data (b_id, id, to_id, data_name, data) '.
                        'values (?, ?, "1", "date", ?)'
                    );
                    $sql_do -> bindParam(1, $_GET['b_id']);
                    $sql_do -> bindParam(2, $last_id);
                    $sql_do -> bindParam(3, get_date());
                    $sql_do -> execute();
                    
                    $sql_do = $db -> prepare(
                        'insert into b_data (b_id, id, to_id, data_name, data) '.
                        'values (?, ?, "1", "user", ?)'
                    );
                    $sql_do -> bindParam(1, $_GET['b_id']);
                    $sql_do -> bindParam(2, $last_id);
                    $sql_do -> bindParam(3, $user_id);
                    $sql_do -> execute();
                    
                    $db -> exec('commit');
                    
                    do_redirect('?v=p_read&b_id='.$_GET['b_id'].'&p_id='.$last_id);
                }
            } else {
                $data = "
                    <form method=\"post\">
                        <input     style=\"width: 100%;\"
                                placeholder=\"".get_lang('title')."\"
                                name=\"title\">
                        <hr class=\"main_hr\"> 
                        <textarea     style=\"width: 100%; height: 75%;\"
                                    placeholder=\"".get_lang('content')."\"
                                    name=\"content\"></textarea>
                        <hr class=\"main_hr\"> 
                        <button type=\"submit\">".get_lang('send')."</button>
                    </form>
                ";
                $tool = [[
                    get_lang('return'), 
                    '?v=b_main&b_id='.$_GET['b_id']
                ]];

                echo get_render(
                    get_lang('add_post'),
                    $data,
                    $tool
                );
            }
        }
    }
?>