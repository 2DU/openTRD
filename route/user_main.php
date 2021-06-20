<?php
	function route_user_main() {
		$u_id = get_id();

		$data = "
			<ul>
				<li>".get_lang('login_state')." : ".$u_id."</li>
				<br>
				<li><a href=\"?v=u_singup\">".get_lang('singup')."</a></li>
				<li><a href=\"?v=u_login\">".get_lang('login')."</a></li>
				<li><a href=\"?v=u_logout\">".get_lang('logout')."</a></li>
			</ul>
		";

		echo get_render(
			get_lang('user_page'), 
			$data
		);
	}
?>