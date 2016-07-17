<?php

namespace AppBundle\Controller;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Pool;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $urls = "aaaa";
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..'),
            'urls'     => $urls,
        ]);
    }

    /**
     * @Route("/ajaxurl", name="ajaxurl")
     */
    public function ajaxUrl(Request $request)
    {
//        $stack = new HandlerStack();
//        $stack->setHandler(new CurlHandler());
//        $stack->push(Middleware::redirect());
//        $client = new Client(['handler' => $stack]);
        $client    = new Client();
        $urlsDone  = 0;
        $urls      = $request->request->get('urls');
        $initNum   = $request->request->get('initNum');
        $urlsArray = explode(",", $urls);
        $urlsNum   = count($urlsArray);

        $onRedirect = function (    RequestInterface $request,
                                    ResponseInterface $response,
                                    UriInterface $uri) use ($urlsDone, $urlsArray, $initNum, $urlsNum){
//            global $urlsDone;
//            $urlsDone = $urlsDone + 1;
            $status   = $response->getStatusCode();
            $length   = $response->getBody()->getSize();
            echo "<p>";
            echo '*'.$initNum.'* ';
            echo "<b> Redirecting from: </b>".(string)$request->getUri()."<b> to: </b>".$uri." <b> status: </b>".$status." <b> length: </b>".$length."</p>";
            echo PHP_EOL;
//            echo 'Redirecting! ' . $request->getUri() . ' to ' . $uri . "\n";
//            $var = "RADII!!";
//            var_dump($var);
//            dump($var);
//            die();
        };

        $requests = function ($urlsArray) use ($onRedirect) {
            foreach ($urlsArray as $url) {
                yield new \GuzzleHttp\Psr7\Request('GET', $url
//                    [
//                    'allow_redirects' => [
//                        'max'             => 10,        // allow at most 10 redirects.
//                        'strict'          => true,      // use "strict" RFC compliant redirects.
//                        'referer'         => true,      // add a Referer header
//                        'protocols'       => ['https'], // only allow https URLs
//                        'on_redirect'     => $onRedirect,
//                        'track_redirects' => true,
//                    ]
//                ]
                );
            }
        };


//        $promise = $pool->promise();
//        $promise->wait();
//        $results = \GuzzleHttp\Promise\settle($promise)->wait();
//        var_dump($results);
        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'text/event-stream');

        $pool = new Pool($client, $requests($urlsArray), [
            'concurency' => 1000,
            'fulfilled'  => function ($response, $index) use ($urlsDone, $urlsArray, $initNum, $urlsNum) {
                global $urlsDone;
                sleep(1);
                if ($response->getHeaderLine('X-Guzzle-Redirect-History') != ""){
                    $urlsDone = $urlsDone + 1;
                    $status   = $response->getStatusCode();
                    $length   = $response->getBody()->getSize();
                    $redirectHistory = explode(',', $response->getHeaderLine('X-Guzzle-Redirect-History'));
                    $redirectHistory = array_slice($redirectHistory, -1);
                    $lastRedirect = array_pop($redirectHistory);

//                    echo '******';
//                    var_dump($response);
                    echo "<p>";
                    echo '*'.$initNum.'* ';
//                    echo $response->getHeaderLine('X-Guzzle-Redirect-History');
                    echo "<b> url: </b>".$lastRedirect." <b> status: </b>".$status."<b> length: </b>".$length." <b> urlsDone: </b>".$urlsDone.'/'.$urlsNum."</p>";
                    echo PHP_EOL;
                    ob_flush();
                    flush();

                }else {
//                    $array = array("url" => $urls[$index], "status"=>$response->getStatusCode(), "length"=>$response->getBody()->getSize(), "parent"=>$parent, "index"=>$index);
                    $urlsDone = $urlsDone + 1;
                    $status   = $response->getStatusCode();
                    $length   = $response->getBody()->getSize();
//                    echo " sajt: ".$urlsArray[$index]." uspesno_".$index." status: ".$status."; *".$urlsDone."*";
                    //$responseData = array("url" => $urlsArray[$index], "status" => $status, "length" => $length, "urlsDone" => $urlsDone);

                    echo "<p>";
                    echo '*'.$initNum.'* ';
                    echo $response->getHeaderLine('X-Guzzle-Redirect-History');
                    echo "<b>url: </b>".$urlsArray[$index]."<b> status: </b>".$status." <b> length: </b>".$length." <b> urlsDone: </b>".$urlsDone.'/'.$urlsNum."</p>";
                    echo PHP_EOL;
                    ob_flush();
                    flush();
//                    $response1->send();
//                    var_dump($response);
                }

            },
            'rejected'   => function ($reason, $index) use ($urlsDone, $urlsNum, $urlsArray, $initNum) {
                global $urlsDone;
//                    sleep(2);
                $urlsDone = $urlsDone + 1;
                echo "<h6>";
                echo '*'.$initNum.'* ';
                echo "url: ".$urlsArray[$index]." status: ERROR invalid URL ".$urlsDone.'/'.$urlsNum."</h6>";
                ob_flush();
                flush();
//                    $response1->send();
//                    var_dump($reason);

            },
            'options'    => [
                'allow_redirects' => [
                    'max'             => 5,
                    'on_redirect'     => $onRedirect,
                    'track_redirects' => true
                ]
            ]
        ]);

        $response->setCallback(function () {
//            ob_flush();
//            flush();
//            echo "streaming..";
//            var_dump() $results;
        });
        $promise = $pool->promise();
        $results = \GuzzleHttp\Promise\settle($promise)->wait();
        return $response;
        /*
        $response = new StreamedResponse();
        $response->setCallback(function () {
            var_dump('Hello World11');
//            ob_flush();
            flush();
            sleep(2);
            var_dump('Hello World22');
//            ob_flush();
            flush();
        });
        $response->send();
*/
//        return $response;
    }

    /**
     * @Route("/guzzletest", name="guzzletest")
     */
    public function guzzleTest()
    {
//        $client         = new Client();
//        $guzzleResponse = $client->request('GET', 'https://www.google.rs/');
//        $guzzleResponse->getStatusCode();
//        var_dump($guzzleResponse);
//        die();

        function add_response_header($header, $value)
        {
            return function (callable $handler) use ($header, $value) {
                return function (
                    RequestInterface $request,
                    array $options
                ) use ($handler, $header, $value) {
                    $promise = $handler($request, $options);
                    return $promise->then(
                        function (ResponseInterface $response) use ($header, $value) {
                            return $response->withHeader($header, $value);
                        }
                    );
                };
            };
        }

        $stack = new HandlerStack();
        $stack->setHandler(new CurlHandler());
        $stack->push(add_response_header('X-Foo', 'bar'));
        $stack->push(Middleware::redirect());
        $client         = new Client(['handler' => $stack]);
        $guzzleResponse = $client->request('GET', 'https://www.google.com/');
        $xfoo           = $guzzleResponse->getHeader('X-Foo');
        var_dump($xfoo);
        var_dump($guzzleResponse);
        die();
    }
}
