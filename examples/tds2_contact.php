<?php

    session_start();
    
    require_once __DIR__ . '/../vendor/autoload.php';
    use Acquired\AcquiredCommon;

    if($_SERVER['REQUEST_METHOD'] == 'POST'){

        $content = file_get_contents('php://input');
        $util = new AcquiredCommon();

        $util->clog("Received from ACS: ".$content);
        
    }

?>