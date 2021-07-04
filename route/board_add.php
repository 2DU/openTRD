<?php
	function route_board_add($db) {
		if(!do_check_admin() === 1) {
			echo get_render(
				get_lang('error'), 
				get_lang('acl_error')
			);
		} else {
			if($_SERVER['REQUEST_METHOD'] === 'POST') {
				if($_POST["b_name"] !== '') {
					$sql_do = $db -> query(
						'select b_id from b_set '.
						'where set_name = "list" order by b_id + 0 desc'
					);
					$sql_end = $sql_do -> fetchArray();
					if($sql_end) {
						$num = (string)((int)$sql_end[0] + 1);
					} else {
						$num = '1';
					}

					$sql_do = $db -> prepare(
						'insert into b_set (b_id, set_name, set_data) values (?, "list", ?)'
					);
					$sql_do -> bindParam(1, $num);
					$sql_do -> bindParam(2, $_POST["b_name"]);
					$sql_do -> execute();
					
					$db -> exec('commit');
					
					do_redirect('?v=main');
				} else {
					echo get_render(
						get_lang('error'), 
						get_lang('input_error')
					);
				}
			} else {
				$data = "
					<form method=\"post\">
						".get_lang('name')."
						<hr class=\"main_hr\">
						<input name=\"b_name\">
						<hr class=\"main_hr\">
						".get_lang('type')."
						<hr class=\"main_hr\">
						<select name=\"b_type\">
							<option value=\"thread\">".get_lang('thread')."</option>
						</select>
						<hr class=\"main_hr\">
						<button type=\"submit\">".get_lang('add')."</buttom>
					</form>
				";

				echo get_render(
					get_lang('add_board'), 
					$data, 
					[[get_lang('return'), "?v=main"]]
				);
			}
		}
	}
?>