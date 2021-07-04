<?php
    function route_board_main($db) {
        $b_name = do_check_b_id_exist(1);
        if($b_name === 0) {
            do_redirect('?v=main');
        } else {
            $data = '';
            $data_content = [];
            
            $sql_do = $db -> prepare(
                'select id, data_name, data from b_data '.
                'where b_id = ? and to_id = "1" '.
                'order by id + 0 desc'
            );
            $sql_do -> bindParam(1, $_GET['b_id']);
            $sql_do = $sql_do -> execute();
            while($sql_end = $sql_do -> fetchArray()) {
                $data_content[$sql_end[0]][$sql_end[1]] = $sql_end[2];
            }
            
            foreach($data_content as $key => $value) {
                // 임시
                $user = array_key_exists('user', $value) ? $value['user'] : '';
                $data = $data.''.
                    '<li>'.
                        $value['date'].' | '.
                        $key.'. '.
                        '<a href="?v=p_read&b_id='.$_GET['b_id'].'&p_id='.$key.'">'.
                            do_html_change($value['title']).
                        '</a> | '.
                        $user.
                    '</li>'.
                '';
            }

            if($data !== '') {
                $data = '<ul>'.$data.'</ul>';
            }

            $tool = [];
            if(do_check_acl() === 1) {
                $tool = [[
                    get_lang('add_post'), 
                    '?v=p_add&b_id='.$_GET['b_id']
                ]];
            }

            echo get_render(
                $b_name,
                $data,
                $tool
            );
        }
    }
?>