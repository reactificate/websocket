# Websocket
Reactificate websocket wrapper around [react/http](https://reactphp.org/http).

## Installation
```
composer require reactificate/websocket
```

## Usage

- Creating websocket server [server.php]
```php

use React\EventLoop\Factory;
use React\Http\Server;
use Reactificate\Websocket\Middleware;
use Reactificate\Websocket\Prebuilt\Servers\ChatServer;

require 'vendor/autoload.php';

$loop = Factory::create();

$wsServers = Middleware::create(new ChatServer());

$socket = new \React\Socket\Server(8080, $loop);
$server = new Server($loop, ...$wsServers);
$server->listen($socket);

$loop->run();
```

- Download [socket.js](https://github.com/reactificate/reactificate/blob/master/public/socket.js) and include it in the code below

- Chat webpage [index.html]
```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chat</title>
    <script src="socket.js"></script>
</head>
<body style="margin: 0 20px">
<div style="margin: 10px 0">
    <input type="text" placeholder="Your username" id="username">
</div>
<div id="messages"></div>

<form id="formSendMessage">
    <textarea style="margin-top: 10px" name="message" id="message" cols="30" rows="5"
              placeholder="Write your message..."></textarea>
    <div>
        <button type="submit">Send</button>
    </div>
</form>
<script>
    const websocket = new Reactificate.WSClient('ws://127.0.0.1:8080/ws/chat');
    const elMessages = document.getElementById('messages');
    const inputUsername = document.getElementById('username');
    const textareaMessage = document.getElementById('message');

    websocket.onOpen(() => console.log('Connection established'))
    websocket.onClose(() => console.log('Connection closed'))
    websocket.onError((error) => console.log(error));

    websocket.onMessage(function (payload) {
        let message = JSON.parse(payload.data)
        if ('chat.message' === message.command) {
            let divMessage = document.createElement('div');
            let username = '<b>' + message.data.username + ': </b>';
            divMessage.innerHTML = '<br/>' + username + message.data.message;
            elMessages.append(divMessage)
        }
    });

    document.getElementById('formSendMessage').onsubmit = function (event) {
        event.preventDefault();

        websocket.send('chat.message', {
            username: inputUsername.value,
            message: textareaMessage.value
        }).then(() => {
            textareaMessage.value = '';
        });
    };
</script>
</body>
</html>
```

- Start server
```bash
php server.php
```

- Run **index.html** in your browser and test