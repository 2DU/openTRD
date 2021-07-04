<?php
    function route_user_logout() {
         if(array_key_exists('id', $_SESSION)) {
            unset($_SESSION["id"]);

            do_redirect('?v=u_main');
        } else {
            do_redirect('?v=u_login');
        }
    }
?>