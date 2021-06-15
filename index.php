<?php
    $data_lang = json_decode(
        file_get_contents('./lang/ko-KR.json'), 
        TRUE
    );

    function do_file_fix($data) {
        return preg_replace('/\/index.php$/' , '', $_SERVER['PHP_SELF']).$data;
    }

    function do_redirect($data) {
        header('Location: '.$data);
    }
    
    function get_lang($name) {
        global $data_lang;

        if($data_lang[$name]) {
            return $data_lang[$name];
        } else {
            return $name." (M)";
        }
    }

    function init_main(
        $data_ver_now,
        $data_ver_last
    ) {
        $data_ver_now_int = (int)preg_replace(
            '/\./', 
            '', 
            $data_ver_now
        );
        
        file_put_contents(
            './ver_now.json',
            json_encode($data_ver_last)
        );
    }
    
    if(file_exists('./ver_now.json')) {
        $data_ver_now = json_decode(
            file_get_contents('./ver_now.json'), 
            TRUE
        )['main'];
    } else {
        $data_ver_now = '0.0.0';
    }
    
    $data_ver_last = json_decode(
        file_get_contents('./ver_last.json'), 
        TRUE
    );
    
    if($data_ver_now !== $data_ver_last['main']) {
        init_main(
            $data_ver_now, 
            $data_ver_last
        );
    } else {
        echo "test";
    }
    
    echo $data_ver_now;
?>