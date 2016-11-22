<?php
  namespace AppMail;

  class Message {

    function __construct($client, $attributes) {
      $this->client = $client;
      $this->attributes = $attributes;
    }

    function id() {
      return $this->attributes->id;
    }

    function token() {
      return $this->attributes->token;
    }

  }

?>
