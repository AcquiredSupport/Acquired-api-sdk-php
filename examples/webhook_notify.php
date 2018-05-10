<?php
    
    require_once __DIR__ . '/../vendor/autoload.php';
    use Acquired\AcquiredCommon;
    use Acquired\AcquiredConfig;

    if($_SERVER['REQUEST_METHOD'] == 'POST'){

        $content = file_get_contents('php://input');
        $util = new AcquiredCommon();
        $util->clog("Received webhook ".$content);

        $data = json_decode($content, true);
        $data["company_hash_code"] = AcquiredConfig::HASHCODE;
        $hash = $util->generateWebhookHash($data);
        // check response hash
        if($hash == $data["hash"]){

            $util->clog("check hash successful");

            // Perform actions based on the result
            
            
        }

    }

?>