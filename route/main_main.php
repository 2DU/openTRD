<?php
	function route_main_main($db) {
		$data = '';
		
		$sql_do = $db -> query(
			'select b_id, set_data from b_set '.
			'where set_name = "list"'
		);
		while($sql_end = $sql_do -> fetchArray()) {
			$data = $data.''.
				'<li>'.
					'<a href="?v=b_main&b_id='.$sql_end[0].'">'.$sql_end[0].'. '.$sql_end[1].'</a></a>'.
				'</li>'.
			'';
		}
		
		$tool = [];
		if(do_check_admin() === 1) {
			 $tool = [[get_lang('add_board'), '?v=b_add']];
		}
		
		if($data !== '') {
			$data = '<ul>'.$data.'</ul>';
		}
		
		return get_render(
			get_lang('main_page'), 
			$data,
			$tool
		);
	}
?>