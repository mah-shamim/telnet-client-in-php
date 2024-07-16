<?php

class TelnetClient {
    private $host;
    private $port;
    private $user;
    private $password;
    private $socket;

    public function __construct($host, $port, $user, $password) {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->password = $password;
    }

    public function connect() {
        $this->socket = @stream_socket_client("tcp://{$this->host}:{$this->port}", $errno, $errstr, 30);

        if (!$this->socket) {
            throw new Exception("Unable to connect to $this->host on port $this->port: $errstr ($errno)");
        }

        $this->login();
    }

    private function login() {
        $auth = base64_encode("{$this->user}:{$this->password}");
        $loginCommand = "AUTH LOGIN $auth\r\n";
        fwrite($this->socket, $loginCommand);
        $this->getResponse();
    }

    public function sendRequest($headers) {
        $headerString = "";
        foreach ($headers as $key => $value) {
            $headerString .= "$key: $value\r\n";
        }
        fwrite($this->socket, $headerString);
        return $this->getResponse();
    }

    private function getResponse() {
        $response = '';
        while (!feof($this->socket)) {
            $response .= fgets($this->socket, 128);
        }
        return $response;
    }

    public function disconnect() {
        if ($this->socket) {
            fclose($this->socket);
        }
    }
}

// Usage example:
$host = 'your_host';
$port = 23; // Telnet default port
$user = 'your_username';
$password = 'your_password';
$headers = [
    'bankid' => 'your_bankid',
    'agent' => 'your_agent',
    'token' => 'your_token',
];

try {
    $telnetClient = new TelnetClient($host, $port, $user, $password);
    $telnetClient->connect();
    $response = $telnetClient->sendRequest($headers);
    echo "Response: \n$response\n";
    $telnetClient->disconnect();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
