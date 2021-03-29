<?php
    function load_render($title, $data, $menu = []) {
        $i = 0;
        $menu_data = "";
        while(array_key_exists($i, $menu)) {
            if($i !== 0) {
                $menu_data = $menu_data." | ";
            }

            $menu_data = $menu_data."<a href=\"".$menu[$i][1]."\">".$menu[$i][0]."</a>";
            
            $i += 1;
        }

        $skin_data = "
            <!DOCTYPE html>
            <html>
                <head>    
                    <meta charset=\"utf-8\">
                    <title>".$title."</title>
                    <link rel=\"stylesheet\" href=\"".file_fix("/view/scarlet/css/main.css?ver=2")."\">
                    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
                </head>
                <body>
                    <header>
                        <span class=\"give_margin\"></span>
                        <a href=\"?v=main\">메인</a> | <a href=\"?v=user\">사용자</a>
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