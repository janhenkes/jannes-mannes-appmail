# AppMail for PHP

This library helps you send e-mails through [AppMail](https://appmail.io) from PHP 5 upwards.

## Installation

Add this library to your composer.json file.

```javascript
{
  "require":{
    "appmail/appmail":">=1.0"
  },
  "autoload": {
    "psr-4": {"AppMail\\": "library/"}
  }
}
```

## Usage

Sending an email is very simple. Just follow the example below. Before you can begin, you'll
need to login to our web interface and generate a new API credential.

```php
// Create a new AppMail client using the server key you generate in our web interface
$client = new AppMail\Client("xxx");

// Create a new message
$message = new AppMail\SendMessage($client);

// Add some recipients
$message->to("john@example.com");
$message->to("mary@example.com");
$message->cc("mike@example.com");
$message->bcc("secret@awesomeapp.com");

// Specify who the message should be from. This must be from a verified domain
// on your mail server.
$message->from("test@test.appmail.io");

// Set the subejct
$message->subject("Hi there!");

// Set the content for the e-mail
$message->plainBody("Hello world!");
$message->htmlBody("<p>Hello world!</p>");

// Add any custom headers
$message->header('X-PHP-Test', 'value');

// Attach any files
$message->attach("textmessage.txt", "text/plain", "Hello world!");

// Send the message and get the result
$result = $message->send();

// Loop through each of the recipients to get the message ID
foreach ($result->recipients() as $email => $message) {
  $email;                  # => The e-mail address of the recipient
  $message->id();          # => Returns the message ID
  $message->token();       # => Returns the message's token
}
```
