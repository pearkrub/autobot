<?php 
    require_once('./vendor/autoload.php');
    //Namespace 
    use \LINE\LINEBot\HTTPClient\CurlHTTPClient; 
    use \LINE\LINEBot; 
    use \LINE\LINEBot\MessageBuilder\TextMessageBuilder; 
    use \LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
    use \LINE\LINEBot\MessageBuilder\StickerMessageBuilder;

    $channel_token = 'OnTQMCBIsw6hEL6IxCU04LMTqy8jQTdI2TXZhK1xk9+3h4+ZDPb3fSaWsl/ZdNjmmiTzwC8T0SqhZu/vbxsMkAaeT0xj4zptjkkgNXI23CZFIUVi/xGwuVDd3RbztCT5HCn84Lsk5/QREA2p+xkROQdB04t89/1O/w1cDnyilFU='; 
    $channel_secret = '328217598dac9a7d3a70a173e319fbe6'; 

    $httpClient = new CurlHTTPClient($channel_token); 
    $bot = new LINEBot($httpClient, array('channelSecret' => $channel_secret));

    // Get message from Line API 
    $content = file_get_contents('php://input'); 
    $events = json_decode($content, true); 
    if (!is_null($events['events'])) { 
        // Loop through each event 
        foreach ($events['events'] as $event) { 
            // Line API send a lot of event type, we interested in message only. 
            if ($event['type'] == 'message') { 
                // Get replyToken 
                $replyToken = $event['replyToken']; 

                switch($event['message']['type']) { 
                    
                    case 'text': 
                        // Reply message 
                        $ask = $event['message']['text']; 
                        switch(strtolower($ask)) { 
                            case 'm': 
                                $respMessage = 'What sup man. Go away!'; 
                                break; 
                            case 'f': 
                                $respMessage = 'Love you lady.'; 
                                break; 
                            default: 
                                $respMessage = 'What is your sex? M or F'; 
                                break;      
                        }
                        
                        break; 

                    case 'image':
                        $messageID = $event['message']['id']; 
                        $respMessage = 'Hello, your image ID is '. $messageID; 
                        $originalContentUrl = 'https://cdn.shopify.com/s/files/1/1217/6360/products/Shinkansen_Tokaido_ShinFuji_001_1e44e709-ea47-41ac-91e4-89b2b5eb193a_grande.jpg?v=1489641827';
                        $previewImageUrl = 'https://cdn.shopify.com/s/files/1/1217/6360/products/Shinkansen_Tokaido_ShinFuji_001_1e44e709-ea47-41ac-91e4-89b2b5eb193a_grande.jpg?v=1489641827';

                        $textMessageBuilder = new ImageMessageBuilder($originalContentUrl, $previewImageUrl); 
                        $response = $bot->replyMessage($replyToken, $textMessageBuilder);
                        break; 
                        
                    case 'sticker': 
                        $messageID = $event['message']['packageId']; 
                        // Reply message 
                        $respMessage = 'Hello, your Sticker Package ID is '. $messageID;
                        $packageId = 1;
                        $stickerId = 410;
                        
                        $textMessageBuilder = new StickerMessageBuilder($packageId, $stickerId); 
                        error_log(json_encode($textMessageBuilder));
                        $response = $bot->replyMessage($replyToken, $textMessageBuilder); 
                        break;

                    case 'video': 
                        $messageID = $event['message']['id']; // Create video file on server. 
                        $fileID = $event['message']['id']; 
                        $response = $bot->getMessageContent($fileID); 
                        $fileName = 'linebot.mp4'; 
                        $file = fopen($fileName, 'w'); 
                        fwrite($file, $response->getRawBody()); // Reply message
                        $respMessage = 'Hello, your video ID is '. $messageID; 
                        break;

                    case 'audio': 
                        $messageID = $event['message']['id']; 
                        // Create audio file on server. 
                        $fileID = $event['message']['id']; 
                        $response = $bot->getMessageContent($fileID); 
                        $fileName = 'linebot.m4a'; 
                        $file = fopen($fileName, 'w'); 
                        fwrite($file, $response->getRawBody()); 
                        // Reply message 
                        $respMessage = 'Hello, your audio ID is '. 
                        $messageID; 
                        break;
                        case 'location': $address = $event['message']['address']; // Reply message $respMessage = 'Hello, your address is '. $address; break;
                    default: 
                        $respMessage = 'Please send image only'; 
                        break;
                } 
                $textMessageBuilder = new TextMessageBuilder($respMessage); 
                $response = $bot->replyMessage($replyToken, $textMessageBuilder); 
            } 
        } 
    }
    echo 'OK';
?>
