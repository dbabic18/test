<?php

namespace AppBundle\Controller;

use GuzzleHttp\Client;
use GuzzleHttp\Pool;
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
        $urlsDone = 0;
        $urls      = $request->request->get('urls');
        $initNum = $request->request->get('initNum');
        $urlsArray = explode(",", $urls);
        $urlsNum = count($urlsArray);
        $client    = new Client();
        $requests  = function ($urlsArray) {
            foreach ($urlsArray as $url) {
                yield new \GuzzleHttp\Psr7\Request('GET', $url);
            }
        };


//        $promise = $pool->promise();
//        $promise->wait();
//        $results = \GuzzleHttp\Promise\settle($promise)->wait();
//        var_dump($results);
        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'text/event-stream');

        $pool    = new Pool($client, $requests($urlsArray), [
            'concurency' => 5,
            'fulfilled'  => function ($response, $index) use ($urlsDone, $urlsArray, $initNum, $urlsNum){
                global $urlsDone;
                sleep(2);
//                    $array = array("url" => $urls[$index], "status"=>$response->getStatusCode(), "length"=>$response->getBody()->getSize(), "parent"=>$parent, "index"=>$index);
                $urlsDone = $urlsDone + 1;
                $status = $response->getStatusCode();
                $length = $response->getBody()->getSize();
//                    echo " sajt: ".$urlsArray[$index]." uspesno_".$index." status: ".$status."; *".$urlsDone."*";
                //$responseData = array("url" => $urlsArray[$index], "status" => $status, "length" => $length, "urlsDone" => $urlsDone);
                echo "<h6>";
                echo '*'.$initNum.'* ';
                echo "url: ".$urlsArray[$index]."status: ".$status."length: ".$length."urlsDone: ".$urlsDone.'/'.$urlsNum."</h6>";
                echo PHP_EOL;
                ob_flush();
                flush();
//                    $response1->send();
//                    var_dump($response);

            },
            'rejected'   => function ($reason, $index) use ($urlsDone, $urlsNum, $urlsArray, $initNum){
                global $urlsDone;
//                    sleep(2);
                $urlsDone = $urlsDone + 1;
                echo "<h6>";
                echo '*'.$initNum.'* ';
                echo "url: ".$urlsArray[$index]."status: ERROR invalid URL".$urlsDone.'/'.$urlsNum."</h6>";
                ob_flush();
                flush();
//                    $response1->send();
//                    var_dump($reason);

            }
        ]);

        $response->setCallback(function () use ($client, $requests, $urlsArray, $urlsDone, $pool) {


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
        $client         = new Client();
        $guzzleResponse = $client->request('GET', 'https://www.google.rs/');
        $guzzleResponse->getStatusCode();
        var_dump($guzzleResponse);
        die();
    }
}
