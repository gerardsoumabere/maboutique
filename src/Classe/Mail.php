<?php

namespace App\Classe;

use Mailjet\Client;
use Mailjet\Resources;

class Mail
{
    private string $api_key = 'c912f1552654457053f2aef55eb4d67a';
    private string $api_key_secret = '2fcb430e7bed28560837c2f1fe1126c0';

    public function send($to_email,$to_name,$subject,$content)
    {
        $mj = new Client($this->api_key,$this->api_key_secret,true, ['version' => 'v3.1']);
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "soumabere@gmail.com",
                        'Name' => "Maboutique"
                    ],
                    'To' => [
                        [
                            'Email' => $to_email,
                            'Name' => $to_name
                        ]
                    ],
                    'TemplateID' => 3487997,
                    'TemplateLanguage' => true,
                    'Subject' => $subject,
                    'Variables' => [
                        'content' => $content,
                    ]
                ]
            ]
        ];
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        $response->success() ;

    }
}
