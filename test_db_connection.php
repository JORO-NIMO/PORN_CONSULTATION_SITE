<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Try different connection methods
$configs = [
    'pdo_mysql' => [
        'dsn' => 'mysql:host=127.0.0.1;dbname=mysql',
        'user' => 'root',
        'pass' => ''
    ],
    'pdo_mysql_no_db' => [
        'dsn' => 'mysql:host=127.0.0.1',
        'user' => 'root',
        'pass' => ''
    ],
    'mysqli' => [
        'host' => '127.0.0.1',
        'user' => 'root',
        'pass' => '',
        'db' => 'mysql'
    ]
];

// Test PDO MySQL
echo "<h2>Testing PDO MySQL Connection:</h2>";
try {
    $pdo = new PDO($configs['pdo_mysql']['dsn'], $configs['pdo_mysql']['user'], $configs['pdo_mysql']['pass']);
    echo "✓ Successfully connected to MySQL using PDO<br>";
    
    // List databases
    $stmt = $pdo->query('SHOW DATABASES');
    echo "<h3>Available databases:</h3><ul>";
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        echo "<li>" . htmlspecialchars($row[0]) . "</li>";
    }
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "✗ PDO Connection failed: " . htmlspecialchars($e->getMessage()) . "<br>";
}

// Test MySQLi
echo "<h2>Testing MySQLi Connection:</h2>";
try {
    $mysqli = new mysqli(
        $configs['mysqli']['host'],
        $configs['mysqli']['user'],
        $configs['mysqli']['pass'],
        $configs['mysqli']['db']
    );
    
    if ($mysqli->connect_error) {
        throw new Exception("Connection failed: " . $mysqli->connect_error);
    }
    
    echo "✓ Successfully connected to MySQL using MySQLi<br>";
    echo "MySQL Server version: " . $mysqli->server_version . "<br>";
    
} catch (Exception $e) {
    echo "✗ MySQLi Connection failed: " . htmlspecialchars($e->getMessage()) . "<br>";
}

// Check PHP MySQL extensions
echo "<h2>PHP MySQL Extensions:</h2>";
$extensions = [
    'pdo',
    'pdo_mysql',
    'mysqli'
];

echo "<ul>";
foreach ($extensions as $ext) {
    echo "<li>" . $ext . ": " . (extension_loaded($ext) ? '✓ Loaded' : '✗ Not loaded') . "</li>";
}
echo "</ul>";

// Check if MySQL is running
echo "<h2>Checking MySQL Service Status:</h2>";
$output = [];
$return_var = 0;
exec('sc query mysql', $output, $return_var);

if ($return_var === 0) {
    echo "<pre>" . htmlspecialchars(implode("\n", $output)) . "</pre>";
} else {
    echo "<p>Could not check MySQL service status. The service might not be installed or running.</p>";
}

// Check if port 3306 is listening
echo "<h2>Checking Port 3306:</h2>";
$port = 3306;
$connection = @fsockopen('127.0.0.1', $port, $errno, $errstr, 5);

if (is_resource($connection)) {
    echo "✓ Port $port is open and accepting connections<br>";
    fclose($connection);
} else {
    echo "✗ Could not connect to port $port: $errstr ($errno)<br>";
}

// Check php.ini settings
echo "<h2>PHP Configuration:</h2>";
$settings = [
    'display_errors',
    'error_reporting',
    'mysql.default_socket',
    'pdo_mysql.default_socket'
];

echo "<ul>";
foreach ($settings as $setting) {
    echo "<li>" . $setting . ": " . htmlspecialchars(ini_get($setting)) . "</li>";
}
echo "</ul>";

// Check XAMPP MySQL default credentials
echo "<h2>XAMPP MySQL Default Credentials:</h2>";
echo "<ul>";
echo "<li>Username: root</li>";
echo "<li>Password: (empty)</li>";
echo "<li>Host: 127.0.0.1</li>";
echo "<li>Port: 3306</li>";
echo "</ul>";

// Check if we can connect without a password
echo "<h2>Trying to connect without password:</h2>";
try {
    $pdo = new PDO('mysql:host=127.0.0.1', 'root', '');
    echo "✓ Successfully connected without password<br>";
} catch (PDOException $e) {
    echo "✗ Connection without password failed: " . htmlspecialchars($e->getMessage()) . "<br>";
}

// Check if we can connect with default XAMPP socket
echo "<h2>Trying to connect with default socket:</h2>";
$sockets = [
    'C:/xampp/mysql/mysql.sock',
    'C:/xampp/mysql/data/mysql.sock',
    '/tmp/mysql.sock',
    '/var/run/mysqld/mysqld.sock'
];

foreach ($sockets as $socket) {
    if (file_exists($socket)) {
        try {
            $pdo = new PDO("mysql:host=localhost;unix_socket=$socket", 'root', '');
            echo "✓ Successfully connected using socket: $socket<br>";
            break;
        } catch (PDOException $e) {
            echo "✗ Connection failed with socket $socket: " . htmlspecialchars($e->getMessage()) . "<br>";
        }
    } else {
        echo "Socket not found: $socket<br>";
    }
}

// Check if we can connect to MySQL on non-standard port
echo "<h2>Trying alternative ports:</h2>";
$ports = [3306, 3307, 3308, 3309];
foreach ($ports as $port) {
    try {
        $pdo = new PDO("mysql:host=127.0.0.1;port=$port", 'root', '');
        echo "✓ Successfully connected to port $port<br>";
    } catch (PDOException $e) {
        echo "✗ Connection failed on port $port: " . htmlspecialchars($e->getMessage()) . "<br>";
    }
}

// Check if we can connect using MySQLi
echo "<h2>Testing MySQLi connection:</h2>";
try {
    $mysqli = new mysqli('127.0.0.1', 'root', '');
    if ($mysqli->connect_error) {
        throw new Exception($mysqli->connect_error, $mysqli->connect_errno);
    }
    echo "✓ Successfully connected using MySQLi<br>";
    echo "MySQL Server version: " . $mysqli->server_info . "<br>";
    $mysqli->close();
} catch (Exception $e) {
    echo "✗ MySQLi connection failed: " . htmlspecialchars($e->getMessage()) . "<br>";
}

// Try to create a test database
echo "<h2>Testing database creation:</h2>";
try {
    $pdo = new PDO('mysql:host=127.0.0.1', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS test_connection");
    echo "✓ Successfully created test database<br>";
    $pdo->exec("DROP DATABASE IF EXISTS test_connection");
} catch (PDOException $e) {
    echo "✗ Database creation failed: " . htmlspecialchars($e->getMessage()) . "<br>";
}
?>

<h2>Next Steps:</h2>
<ol>
    <li>Make sure MySQL service is running in XAMPP Control Panel</li>
    <li>Check if port 3306 is not blocked by firewall</li>
    <li>Verify that the MySQL root password is indeed empty</li>
    <li>Try connecting using MySQL Workbench or phpMyAdmin to verify credentials</li>
    <li>Check XAMPP error logs at C:\xampp\mysql\data\*.err</li>
</ol>
