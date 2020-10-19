<?php

declare(strict_types=1);

namespace happybe\openapi\discord;

use pocketmine\scheduler\AsyncTask;

/**
 * Class SendWebhookMessageAsyncTask
 * @package happybe\openapi\discord
 */
class SendWebhookMessageAsyncTask extends AsyncTask {

    /** @var string $url */
    public $url;
    /** @var string $title */
    public $title;
    /** @var string $message */
    public $message;
    /** @var bool $addTime */
    public $addTime;

    /**
     * SendWebhookMessageAsyncTask constructor.
     * @param string $url
     * @param string $title
     * @param string $message
     * @param bool $addTime
     */
    public function __construct(string $url, string $title, string $message, bool $addTime = false) {
        $this->url = $url;
        $this->title = $title;
        $this->message = $message;
        $this->addTime = $addTime;
    }

    public function onRun() {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $this->url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode([
            "embeds" => [[
                "title" => $this->title,
                "description" => $this->message,
                "timestamp" => date("c", strtotime("now"))
            ]]
        ]));

        curl_exec($curl);
        curl_close($curl);
    }
}