<?php
  namespace AppMail;

  class SendMessage {

    private $client;
    public $attributes = array();

    function __construct($client) {
      $this->client = $client;
      $this->attributes['to'] = array();
      $this->attributes['cc'] = array();
      $this->attributes['bcc'] = array();
      $this->attributes['headers'] = array();
      $this->attributes['attachments'] = array();
    }

    function to($address) {
      array_push($this->attributes['to'], $address);
    }

    function cc($address) {
      array_push($this->attributes['cc'], $address);
    }

    function bcc($address) {
      array_push($this->attributes['bcc'], $address);
    }

    function from($address) {
      $this->attributes['from'] = $address;
    }

    function sender($address) {
      $this->attributes['sender'] = $address;
    }

    function subject($subject) {
      $this->attributes['subject'] = $subject;
    }

    function tag($tag) {
      $this->attributes['tag'] = $tag;
    }

    function replyTo($replyTo) {
      $this->attributes['reply_to'] = $replyTo;
    }

    function plainBody($content) {
      $this->attributes['plain_body'] = $content;
    }

    function htmlBody($content) {
      $this->attributes['html_body'] = $content;
    }

    function header($key, $value) {
      $this->attributes['headers'][$key] = $value;
    }

    function attach($filename, $content_type, $data) {
      $attachment = array();
      $attachment['name'] = $filename;
      $attachment['content_type'] = $content_type;
      $attachment['data'] = base64_encode($data);
      array_push($this->attributes['attachments'], $attachment);
    }


    function send() {
      $result = $this->client->makeRequest("send", "message", $this->attributes);
      return new SendResult($this->client, $result);
    }

  }

?>
