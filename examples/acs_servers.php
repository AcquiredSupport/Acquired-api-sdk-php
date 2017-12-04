<?php
	
	require_once('../lib/Acquired.Helper.php');

    if($_SERVER['REQUEST_METHOD'] == 'POST' and !empty($_POST['pareq'])){

        $md = $_POST['md'];
        $url = $_POST['termurl'];
        $pares = 'pares';

        $post_data = "PaRes=".$pares."&md=".$md;

        $auth = new Auth_pub();
        $response = $auth->https_request($url,$post_data);

        $auth->clog("ACS servers have received");

        echo $response;

    }

?>