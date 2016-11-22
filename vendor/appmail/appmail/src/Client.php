<?php
  namespace AppMail;

  class Client {

    private $host = 'api.appmail.io';

    function __construct($serverKey, $host = null) {
      $this->serverKey = $serverKey;
      if($host !== null) {
        $this->host = $host;
      }
    }

    function makeRequest($controller, $action, $parameters) {
      $url = "https://" . $this->host . "/api/v1/" . $controller . "/" . $action;
      // Headers
      $headers = array();
      $headers['x-server-api-key'] = $this->serverKey;
      $headers['content-type'] = 'application/json';
      // Make the body
      $json = json_encode($parameters);
      // Make the request
      $response = \Requests::post($url, $headers, $json);
      if($response->status_code === 200) {
        $json = json_decode($response->body);
        if($json->status == 'success') {
          return $json->data;
        } else {
          throw new Error("[" . $json->data->code . "] " . $json->data->message);
        }
      } else {
        throw new Error("Couldn't send message to API");
      }
    }

  }

?>
