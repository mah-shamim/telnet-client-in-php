<?php
class TelnetClient {
    private $host;
    private $port;
    private $timeout;
    private $socket;

    public function __construct($host, $port = 23, $timeout = 10) {
        $this->host = $host;
        $this->port = $port;
        $this->timeout = $timeout;
    }

    public function connect() {
        $this->socket = fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);

        if (!$this->socket) {
            throw new Exception("Unable to connect to Telnet server: $errstr ($errno)");
        }
    }

    public function disconnect() {
        if ($this->socket) {
            fclose($this->socket);
            $this->socket = null;
        }
    }

    public function read($length = 1024) {
        if (!$this->socket) {
            throw new Exception("No connection to Telnet server.");
        }

        $data = fread($this->socket, $length);
        return $data;
    }

    public function write($command) {
        if (!$this->socket) {
            throw new Exception("No connection to Telnet server.");
        }

        fwrite($this->socket, $command . "\r\n");
    }

    public function login($username, $password) {
        $this->write($username);
        sleep(1); // Delay to wait for the server to process the username
        $this->write($password);
    }
}

// Example usage
try {
    $telnet = new TelnetClient('your_telnet_server_host');
    $telnet->connect();
    $telnet->login('your_username', 'your_password');

    // Read the welcome message
    $welcomeMessage = $telnet->read();
    echo "Server: $welcomeMessage";

    // Write a command and read the response
    $telnet->write('your_command_here');
    $response = $telnet->read();
    echo "Response: $response";

    $telnet->disconnect();
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>
