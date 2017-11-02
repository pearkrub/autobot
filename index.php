<?php 
require_once('./vendor/autoload.php');
//Namespace 
use \LINE\LINEBot\HTTPClient\CurlHTTPClient; 
use \LINE\LINEBot; 
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder; 
$channel_token = 'OnTQMCBIsw6hEL6IxCU04LMTqy8jQTdI2TXZhK1xk9+3h4+ZDPb3fSaWsl/ZdNjmmiTzwC8T0SqhZu/vbxsMkAaeT0xj4zptjkkgNXI23CZFIUVi/xGwuVDd3RbztCT5HCn84Lsk5/QREA2p+xkROQdB04t89/1O/w1cDnyilFU='; 
$channel_secret = '328217598dac9a7d3a70a173e319fbe6'; 

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
                    $respMessage = 'Hello, your message is '. $event['message']['text']; 
                    
                    break; 

                case 'image':
                    $messageID = $event['message']['id']; 
                    $respMessage = 'Hello, your image ID is '. $messageID; 
                    break; 
                    
                case 'sticker': 
                    $messageID = $event['message']['packageId']; 
                    // Reply message 
                    $respMessage = 'Hello, your Sticker Package ID is '. $messageID; 
                    break;

                case 'video': 
                    $messageID = $event['message']['id']; // Create video file on server. 
                    $fileID = $event['message']['id']; 
                    $httpClient = new CurlHTTPClient($channel_token); 
                    $bot = new LINEBot($httpClient, array('channelSecret' => $channel_secret));
                    $response = $bot->getMessageContent($fileID); 
                    $fileName = 'linebot.mp4'; 
                    $file = fopen($fileName, 'w'); 
                    fwrite($file, $response->getRawBody()); // Reply message
                    $respMessage = 'Hello, your video ID is '. $messageID; 
                    break;

                default: 
                    $respMessage = 'Please send image only'; 
                    break;
            } 
            $httpClient = new CurlHTTPClient($channel_token); 
            $bot = new LINEBot($httpClient, array('channelSecret' => $channel_secret));
            $textMessageBuilder = new TextMessageBuilder($respMessage); 
            $response = $bot->replyMessage($replyToken, $textMessageBuilder); 
        } 
    } 
}
echo 'OK';
?>
