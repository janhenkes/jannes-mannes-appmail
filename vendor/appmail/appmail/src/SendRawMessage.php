<?php
  namespace AppMail;

  class SendRawMessage {

    private $client;
    public $attributes = array();

    function __construct($client) {
      $this->client = $client;
      $this->attributes['rcpt_to'] = array();
    }

    function mailFrom($address) {
      $this->attributes['mail_from'] = $address;
    }

    function rcptTo($address) {
      array_push($this->attributes['rcpt_to'], $address);
    }

    function data($data) {
      $this->attributes['data'] = base64_encode($data);
    }

    function send() {
      $result = $this->client->makeRequest("send", "raw", $this->attributes);
      return new SendResult($this->client, $result);
    }

  }

?>
