<?php

use PHPUnit\Framework\TestCase;

use CloudflareBypass\RequestMethod\CFStream;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

class GuzzleHttpTest extends TestCase
{

    /**
     * Url to test
     *
     * @var string
     */
    protected $url = "https://coinkite.com";
    
    /**
     * Test 503 (without bypass)
     *
     * @return void
     */
    public function test503()
    {
        $stream_cf_wrapper = new CFStream(array(
            'cache'         => true,  // Caching now enabled by default; stores clearance tokens in Cache folder
            'max_attempts'  => 5      // Max attempts to try and get CF clearance
        ));

        $agent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36";

        $client = new Client();

        $response = $client->request('GET', $this->url, [
            'headers' => [
                'User-Agent' => "$agent",
            ],
            'http_errors' => false,
            // 'debug' => true
        ]);

        $this->assertEquals(503, $response->getStatusCode());
    }

    /**
     * Test 200 (with bypass)
     *
     * @return void
     */
    public function test200()
    {
        $stream_cf_wrapper = new CFStream(array(
            'cache'         => true,  // Caching now enabled by default; stores clearance tokens in Cache folder
            'max_attempts'  => 5      // Max attempts to try and get CF clearance
        ));

        $agent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36";

        $opts = array(
            'http' => array(
                'method' => "GET",
                'header' => "User-Agent:$agent"
            )
        );

        $stream = $stream_cf_wrapper->create($this->url, $opts);
        $client = new Client();

        $cookieJar = CookieJar::fromArray($stream->getCookiesOriginal(), parse_url($this->url)['host']);

        $response = $client->request('GET', $this->url, [
            'headers' => [
                'User-Agent' => "$agent",
            ],
            'cookies' => $cookieJar,
            // 'debug' => true
        ]);

        $this->assertEquals(200, $response->getStatusCode());
    }
}
