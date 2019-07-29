<?php
namespace breadhead\mailchimp\api;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

class MailchimpClient
{
    private static $mc_root;
    private $apiKey;
    private $client;

    private $response;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;

        // Setting http_errors to false since guzzle explodes for anything not 200
        $client = new Client([
            'base_uri' => $this->getUrl(),
            'auth' => ['api', $this->apiKey],
            'cookies' => true,
            'allow_redirects' => true,
            'http_errors' => false,
            "headers" => [
                "User-Agent" => "MCv3.0 / PHP",
                "Accept" => "application/json"
            ]
        ]);

        $this->client = $client;
    }

    private function getUrl(): string
    {
        $dc = $this->getDatacenter();

        return  "https://{$dc}.api.mailchimp.com/3.0/";
    }

    private function getDatacenter(): string
    {
        // Determine the Datacenter from the API Key
        $dc = trim(strstr($this->apiKey, "-"), "-");

        return $dc;
    }

    protected function setData($method, array $data = [])
    {
        // TODO: consider sanitizing incoming data?
        foreach ($data as $key => $value) {
            // Set query parameters if method is GET
            if ($method == "GET") {
                // If the value is an array convert it to a string
                if (is_array($value)) {
                    $value = implode(',', $value);
                }
                // Set the query param to an associative array
                $params['query'][$key] = $value;
            } else {
                $params['json'][$key] = $value;
            }
        }
        return $params;
    }

    public function execute($method, $url, array $data = []): ResponseInterface
    {
        $this->response = '';

        if ($data) {
            /**  @var \Psr7\Http\Message\ResponseInterface $response*/
            $response = $this->client->request($method, $url, $this->setData($method, $data));
        } else {
            /**  @var ResponseInterface $response*/
            $response = $this->client->request($method, $url);
        }

        $statusCode = $response->getStatusCode();

        $this->response = json_decode($response->getBody()->getContents());

        if ($statusCode <> 200) {
        /*
            print_r($url . $method);
            print_r($data);
            print_r($this->response);

            die('ERROR!!----');
        */
        }

        return $response;
    }

    public function getResponse()
    {
        return $this->response;
    }

    protected function getMemberHash($email_address)
    {
        return md5(strtolower($email_address));
    }

    public function optionalFields(array $optional_fields, array $provided_fields)
    {
        $data = [];
        foreach ($provided_fields as $key => $value) {
            if (in_array(strtolower($key), $optional_fields) ) {
                $data[$key] = $value;
            }
        }
        return $data;
    }

    protected function createLog($output, $overwrite = false, $file_name = "request.log", $tag = null)
    {
        $w = "a+";
        if ($overwrite) {
            $w = "w+";
        }
        $file = $file_name;
        $json_output = json_encode($output);
        $date = new \DateTime("now", new \DateTimeZone('America/New_York'));
        $time_formatted = $date->format("Y/m/d H:i:s");
        $handle = fopen($file, $w);
        $content = "Request: {$time_formatted}\n";
        if ($tag) {
            $content .= "TAGGED: {$tag}";
            $content .= "\n";
        }
        $content .= $json_output;
        $content .= "\n";
        // $content .= print_r($output, true)."\n  ----------------------------------------------------  \n";
        $content .= "\n  ----------------------------------------------------  \n";
        fwrite($handle, $content);
        fclose($handle);
    }


    public function logData($data, $tag, array $optional_settings = [])
    {
        if (isset($optional_settings["file_name"])) {
            $file_name = $optional_settings["file_name"];
        } else {
            $file_name = null;
        }

        if (isset($optional_settings["overwrite"])) {
            $overwrite = true;
        } else {
            $overwrite = false;
        }

        return $this->createLog($data, $overwrite, $file_name, $tag);
    }
}
