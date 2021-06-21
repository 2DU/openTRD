<?php
	function route_post_read($db) {
		$b_name = do_check_b_id_exist(1);
		if($b_name === 0) {
			do_redirect('?v=main');
		} elseif(!array_key_exists('p_id', $_GET)) {
			do_redirect('?v=main');
		} else {
			$data_content = [];
			
			$sql_do = $db -> prepare(
				'select data_name, data from b_data '.
				'where b_id = ? and id = ? and to_id = "1"'
			);
			$sql_do -> bindParam(1, $_GET['b_id']);
			$sql_do -> bindParam(2, $_GET['p_id']);
			$sql_do = $sql_do -> execute();
			while($sql_end = $sql_do -> fetchArray()) {
				$data_content[$sql_end[0]] = $sql_end[1];
			}
			
			$title = do_html_change($data_content['title']).' ('.$b_name.')';
			// 임시
			$user = array_key_exists('user', $data_content) ? $data_content['user'] : '';
			$data = "
					".$data_content['date']." | ".$user."
					<hr class=\"main_hr\">
					".do_html_change($data_content['data'])."
			";
			
			if($data_content !== []) {				
				echo get_render(
					$title,
					$data,
					[[
						get_lang('return'),
						'?v=b_main&b_id='.$_GET['b_id']
					]]
				);
			} else {
				do_redirect('?v=main');
			}
		}
	}
?>