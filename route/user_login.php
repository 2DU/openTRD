<?php
	function route_user_login($db) {
		if(array_key_exists('id', $_SESSION)) {
			do_redirect('?v=user');
		} else {
			if($_SERVER['REQUEST_METHOD'] === 'POST') {
				if(
					$_POST["id"] !== '' && 
					$_POST["pw"] !== ''
				) {
					$sql_do = $db -> prepare(
						'select set_data from m_user_set '.
						'where user_name = ? and set_name = "password"'
					);
					$sql_do -> bindParam(1, $_POST["id"]);
					$sql_do = $sql_do -> execute();
					$sql_end = $sql_do -> fetchArray();
					if($sql_end) {
						if(hash("sha256", $_POST["pw"]) === $sql_end[0]) {
							$_SESSION["id"] = $_POST["id"];

							do_redirect('?v=u_main');
						} else {
							echo get_render(
								get_lang('error'), 
								get_lang('pw_error')
							);    
						}
					} else {
						echo get_render(
							get_lang('error'), 
							get_lang('no_exist_id_error')
						);  
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

				echo get_render(
					get_lang('login'), 
					$data, 
					[[get_lang('return'), "?v=u_main"]]
				);
			}
		}
	}
?>