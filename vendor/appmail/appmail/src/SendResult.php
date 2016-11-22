<?php
  namespace AppMail;

  class SendResult {

    private $recipients;

    function __construct($client, $result) {
      $this->client = $client;
      $this->result = $result;
    }

    function recipients() {
      if($this->recipients != null) {
        return $this->recipients;
      } else {
        $this->recipients = array();
        foreach ($this->result->messages as $key => $value) {
          $this->recipients[strtolower($key)] = new Message($this->client, $value);
        }
        return $this->recipients;
      }
    }

    function size() {
      return count($this->recipients());
    }

  }

?>
