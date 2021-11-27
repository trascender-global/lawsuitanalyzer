<?php
    //$mysqli = new mysqli( 'localhost','urbxspau9akvb', 'pydtd83aw5hz','dbkzbyfzdt6adn'); //Producción
    //$mysqli = new mysqli( 'localhost','sp3_adminbinius', 'Happy2Grow.,','sp3_lawsuit_analyzer_form'); //SP3
    $mysqli = new mysqli( 'localhost','root', '','dbkzbyfzdt6adn');
    $mysqli->set_charset('utf8');
    
    if($mysqli -> connect_errno ):
        echo 'Error al conectarse con MySQL: ' .$mysqli->connect_error ;
    endif;
?>