<?php

//AUTOLOAD
spl_autoload_register( function ( $class ) {
    $arquivo = __DIR__ . '/src/' .str_replace('\\', '/', $class). '.php';

    if(is_file($arquivo)){
        require $arquivo;
    }
});
