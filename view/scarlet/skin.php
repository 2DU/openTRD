<?php
    function get_render($title, $data, $menu = []) {
        $menu_data = "";
        for($i = 0; array_key_exists($i, $menu); $i++) {
            if($i !== 0) {
                $menu_data = $menu_data." | ";
            }

            $menu_data = $menu_data."<a href=\"".$menu[$i][1]."\">".$menu[$i][0]."</a>";
        }

        $skin_data = "
            <!DOCTYPE html>
            <html>
                <head>    
                    <meta charset=\"utf-8\">
                    <title>".$title."</title>
                    <link rel=\"stylesheet\" href=\"".do_file_fix("/view/scarlet/css/main.css?ver=2")."\">
                    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
                </head>
                <body>
                    <header>
                        <span class=\"give_margin\"></span>
                        <a href=\"?v=main\">".get_lang('main')."</a> | 
						<a href=\"?v=u_main\">".get_lang('user')."</a>
                    </header>
                    <section>
                        <div id=\"title\"><h1>".$title."</h1></div>
                        <div id=\"tool\">".$menu_data."</div>
                        <div id=\"data\">
                            ".$data."
                        </div>
                    </section>
                    <footer>

                    </footer>
                </body>
            </html>
        ";

        return $skin_data;
    }