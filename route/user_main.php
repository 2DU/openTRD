<?php
	function route_user_main() {
		$u_id = get_id();
        $u_auth = get_auth(get_id());
        if($u_auth === 0) {
            $u_auth = get_lang('ip');
        }

		$data = "
			<ul>
				<li>".get_lang('id')." : ".$u_id."</li>
                <li>".get_lang('auth')." : ".$u_auth."</li>
				<hr class=\"main_hr\">
				<li><a href=\"?v=u_signup\">".get_lang('signup')."</a></li>
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