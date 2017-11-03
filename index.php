<?php 
    require_once('./vendor/autoload.php');
    //Namespace 
    use \LINE\LINEBot\HTTPClient\CurlHTTPClient; 
    use \LINE\LINEBot; 
    use \LINE\LINEBot\MessageBuilder\TextMessageBuilder; 
    use \LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
    use \LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
    use \LINE\LINEBot\MessageBuilder\VideoMessageBuilder;
    use \LINE\LINEBot\MessageBuilder\LocationMessageBuilder;

    $channel_token = 'OnTQMCBIsw6hEL6IxCU04LMTqy8jQTdI2TXZhK1xk9+3h4+ZDPb3fSaWsl/ZdNjmmiTzwC8T0SqhZu/vbxsMkAaeT0xj4zptjkkgNXI23CZFIUVi/xGwuVDd3RbztCT5HCn84Lsk5/QREA2p+xkROQdB04t89/1O/w1cDnyilFU='; 
    $channel_secret = '328217598dac9a7d3a70a173e319fbe6'; 

    $httpClient = new CurlHTTPClient($channel_token); 
    $bot = new LINEBot($httpClient, array('channelSecret' => $channel_secret));

    // Get message from Line API 
    $content = file_get_contents('php://input'); 
    $events = json_decode($content, true); 
    error_log(json_encode($events));
    if (!is_null($events['events'])) { 
        // Loop through each event 
        foreach ($events['events'] as $event) { 
            // Line API send a lot of event type, we interested in message only. 
            if ($event['type'] == 'message' && $event['message']['type'] == 'text') {
                $replyToken = $event['replyToken']; 
                // Split message then keep it in database. 
                $appointments = explode(',', $event['message']['text']); 
                try{
                    $host = 'ec2-174-129-224-33.compute-1.amazonaws.com'; 
                    $dbname = 'd3306tqdi77npn'; 
                    $user = 'yzmadrqtxhfoqh';
                    $pass = 'e193b0d3401586d017c1f5541686cb6bd9019c986d09e4c1ef87e2558dae8713'; 
                    $connection = new PDO("pgsql:host=$host;dbname=$dbname", $user, $pass); 
                    $params = array( 'time' => $appointments[0], 'content' => $appointments[1], ); 
                    $statement = $connection->prepare("INSERT INTO appointments (time, content) VALUES (:time, :content)"); 
                    $result = $statement->execute($params); 
                    $respMessage = 'Your appointment has saved.'; 
                } catch(Exception $e) {
                    error_log($e->getMessage()); 
                }
                $textMessageBuilder = new TextMessageBuilder($respMessage); 
                $response = $bot->replyMessage($replyToken, $textMessageBuilder);
            }
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
                        $originalContentUrl = 'https://www.select2web.com.com/the-fuji.mp4'; 
                        $previewImageUrl = 'https://www.select2web.com.com/the-fuji.jpg'; 

                        $httpClient = new CurlHTTPClient($channel_token); 
                        $bot = new LINEBot($httpClient, array('channelSecret' => $channel_secret)); 
                        $textMessageBuilder = new VideoMessageBuilder($originalContentUrl, $previewImageUrl); 
                        $response = $bot->replyMessage($replyToken, $textMessageBuilder);
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

                    case 'location': 
                        $address = $event['message']['address']; // Reply message 
                        $respMessage = 'Hello, your address is '. $address; 
                        $title = $event['message']['title'];; 
                        $latitude = $event['message']['latitude']; 
                        $longitude = $event['message']['longitude'];
                        $textMessageBuilder = new LocationMessageBuilder($title, $address, $latitude, $longitude); 
                        $response = $bot->replyMessage($replyToken, $textMessageBuilder);
                        break;

                    case 'file': 
                        $messageID = $event['message']['id']; 
                        $fileName = $event['message']['fileName']; // Reply message 
                        $respMessage = 'Hello, your file ID is '. $messageID . ' and file name is '. $fileName; 
                        break;
                    
                    default: 
                        $respMessage = 'Please send image only'; 
                        break;
                } 
                $textMessageBuilder = new TextMessageBuilder($respMessage); 
                $response = $bot->replyMessage($replyToken, $textMessageBuilder); 
            }

            if ($event['type'] == 'follow') { 
                // Get replyToken 
                $replyToken = $event['replyToken']; 
                // Greeting 
                $respMessage = 'Thanks you. I try to be your best friend.'; 
                $httpClient = new CurlHTTPClient($channel_token); 
                $bot = new LINEBot($httpClient, array('channelSecret' => $channel_secret)); 
                $textMessageBuilder = new TextMessageBuilder($respMessage); 
                $response = $bot->replyMessage($replyToken, $textMessageBuilder); 
            }
            if ($event['type'] == 'join') { 
                // Get replyToken 
                $replyToken = $event['replyToken']; 
                // Greeting 
                $respMessage = 'Hi guys, I am Praibool.Robot. You can ask me everything.'; 
                $httpClient = new CurlHTTPClient($channel_token); 
                $bot = new LINEBot($httpClient, array('channelSecret' => $channel_secret)); 
                $textMessageBuilder = new TextMessageBuilder($respMessage); 
                $response = $bot->replyMessage($replyToken, $textMessageBuilder); 
            }
        } 
    }
    echo 'OK';
?>
<div class="line-it-button" data-lang="th" data-type="share-d" data-url="https://praibool-autobot.herokuapp.com" style="display: none;"></div>
 <script src="https://d.line-scdn.net/r/web/social-plugin/js/thirdparty/loader.min.js" async="async" defer="defer"></script>
 <script type="text/javascript">LineIt.loadButton();</script>