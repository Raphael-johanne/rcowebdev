<?php

namespace Drupal\download_link\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * download_link controller.
 */
class DownloadController extends ControllerBase {

  /**
  * overview
  *
  * $fileName File name
  */
  public function download($fileName) {

    // File lives in /files/downloads.
    $uri_prefix = 'public://downloads/';

    $uri = $uri_prefix . $fileName;

    $this->do($uri);

    return new Response("Hello World !");

    /*
    $headers = [
      'X-Sendfile' => $uri,
      'Content-Type' => 'application/octet-stream',
      'Content-Description' => 'File Download',
      'Content-Disposition' => 'attachment; filename=' . $fileName
    ];

    // Return and trigger file donwload.
    return new BinaryFileResponse($uri, 200, $headers, true );
    */

  
  }

  private function do($file) {
 
    //$this->sendHeaders($file, 'video/webm');
    $chunkSize = 1024 * 1024;
    die('bob5');
    $handle = fopen($file, 'r');
    var_dump($handle);
    die('bob4');
    while (!feof($handle))
    {
        $buffer = fread($handle, $chunkSize);
        echo $buffer;
        ob_flush();
        flush();
    }
    fclose($handle);
  }

  private function sendHeaders($file, $type) {
      header('Pragma: public');
      header('Expires: 0');
      header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
      header('Cache-Control: private', false);
      header('Content-Transfer-Encoding: binary');
      header('Content-Disposition: attachment; filename="'.'Film'.'";');
      header('Content-Type: ' . $type);
      header('Content-Length: ' . filesize($file));
  }
}
