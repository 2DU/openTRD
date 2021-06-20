<?php
	function route_board_main($db) {
		if(!array_key_exists('b_id', $_GET)) {
			do_redirect('?v=main');
		} else {
			$sql_do = $db -> prepare(
				'select set_data from b_set '.
				'where set_name = "list" and b_id = ?'
			);
			$sql_do -> bindParam(1, $_GET['b_id']);
			$sql_do = $sql_do -> execute();
			$sql_end = $sql_do -> fetchArray();
			if($sql_end) {
				echo get_render(
					$sql_end[0],
					''
				);
			} else {
				do_redirect('?v=main');
			}
		}
	}
?>