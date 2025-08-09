<?php
/**
 * Malt Dashboard
 *
 * This file displays the dashboard for development environments built with Malt
 */

// Load configuration file
$configFile = __DIR__ . '/../malt.json';
$config = json_decode(file_get_contents($configFile), true);

// Get version information from dependencies (fallback)
function getVersionFromDependencies($dependencies, $serviceName) {
    foreach ($dependencies as $dep) {
        if (strpos($dep, $serviceName . '@') === 0) {
            $parts = explode('@', $dep);
            return $parts[1] ?? 'Unknown';
        }
    }
    return 'Unknown';
}

// Get service version using command line
function getServiceVersionCommand($service) {
    $versionCommands = [
        'php' => 'php -v | head -n 1',
        'mysql' => 'mysql --version',
        'redis' => 'redis-cli --version',
        'nginx' => 'nginx -v 2>&1',
        'httpd' => 'apachectl -v | head -n 1',
        'memcached' => 'memcached -h | head -n 1'
    ];

    if (!isset($versionCommands[$service])) {
        return null;
    }

    $version = null;

    // Check if exec function is available
    if (function_exists('exec') && !in_array('exec', explode(',', ini_get('disable_functions')))) {
        $output = [];
        exec($versionCommands[$service], $output, $return);
        $version = $output[0] ?? null;

        // Clean up version string
        if ($version) {
            if ($service === 'mysql') {
                // Extract "8.0.32" from "mysql  Ver 8.0.32 for macos13.0 on arm64 (Homebrew)"
                if (preg_match('/Ver\s+(\d+\.\d+\.\d+)/', $version, $matches)) {
                    $version = $matches[1];
                }
            } elseif ($service === 'php') {
                // Extract "8.4.0" from "PHP 8.4.0 (cli) (built: Mar 22 2024 18:45:11) (NTS)"
                if (preg_match('/PHP\s+(\d+\.\d+\.\d+)/', $version, $matches)) {
                    $version = $matches[1];
                }
            } elseif ($service === 'nginx') {
                // Extract "1.25.1" from "nginx version: nginx/1.25.1"
                if (preg_match('/nginx\/(\d+\.\d+\.\d+)/', $version, $matches)) {
                    $version = $matches[1];
                }
            }
        }
    }

    return $version;
}

// Get service version (try both methods)
function getServiceVersion($service, $dependencies) {
    // First, try to get version via command line
    $version = getServiceVersionCommand($service);

    // If not found, get from dependencies
    if ($version === null) {
        $version = getVersionFromDependencies($dependencies, $service);
    }

    // If still not found
    if ($version === null) {
        // Special handling for PHP
        if ($service === 'php') {
            return PHP_VERSION;
        }
        return 'Unknown';
    }

    return $version;
}

// Check if port is listening
function isPortListening($port) {
    if (function_exists('fsockopen')) {
        $connection = @fsockopen('127.0.0.1', $port, $errno, $errstr, 0.5);
        if (is_resource($connection)) {
            fclose($connection);
            return true;
        }
    }
    return false;
}

// Check service running status
function getServiceStatus($service, $port) {
    // If port is specified, check if it's listening
    if ($port) {
        return isPortListening($port) ? 'running' : 'stopped';
    }

    // Process check (fallback)
    $processPatterns = [
        'php-fpm' => 'php-fpm',
        'nginx' => 'nginx: master process',
        'httpd' => 'httpd -k start',
        'mysql' => 'mysqld',
        'redis' => 'redis-server',
        'memcached' => 'memcached'
    ];

    $pattern = $processPatterns[$service] ?? $service;

    if (function_exists('exec') && !in_array('exec', explode(',', ini_get('disable_functions')))) {
        $command = "ps aux | grep -v grep | grep " . escapeshellarg($pattern);
        exec($command, $output, $return);
        return !empty($output) ? 'running' : 'stopped';
    }

    // Assume running if detection fails
    return 'running';
}

// Get process ID for a service
function getProcessId($serviceName, $port = null) {
    $pidCommands = [
        'php-fpm' => "ps aux | grep -v grep | grep 'php-fpm: master process' | awk '{print $2}'",
        'nginx' => "ps aux | grep -v grep | grep 'nginx: master process' | awk '{print $2}'",
        'httpd' => "ps aux | grep -v grep | grep 'httpd -k start' | head -1 | awk '{print $2}'",
        'mysql' => "ps aux | grep -v grep | grep -E 'mysqld' | grep -v mysqld_safe | awk '{print $2}'",
        'redis' => "ps aux | grep -v grep | grep 'redis-server' | awk '{print $2}'",
        'memcached' => "ps aux | grep -v grep | grep 'memcached' | awk '{print $2}'"
    ];

    if (!isset($pidCommands[$serviceName])) {
        return '---';
    }

    // Check if exec function is available
    if (function_exists('exec') && !in_array('exec', explode(',', ini_get('disable_functions')))) {
        $output = [];
        exec($pidCommands[$serviceName], $output, $return);

        if (!empty($output)) {
            return $output[0]; // First PID
        }

        // If not found by process name, check by port
        if ($port) {
            return getProcessIdByPort($port);
        }
    }

    return '---';
}

// Get process ID by port (alternative method)
function getProcessIdByPort($port) {
    if (!function_exists('exec') || in_array('exec', explode(',', ini_get('disable_functions')))) {
        return '---';
    }

    $command = "lsof -i:" . escapeshellarg($port) . " -t";
    $output = [];
    exec($command, $output, $return);

    if (!empty($output)) {
        return $output[0]; // First PID
    }

    return '---';
}

// Get PHP version and extensions information
$phpVersion = phpversion();
$phpExtensions = get_loaded_extensions();

// Project information
$projectName = $config['project_name'] ?? 'myapp';
$projectPath = dirname(__DIR__);

// Get dependencies
$dependencies = $config['dependencies'] ?? [];

// Get PHP extensions
$configuredExtensions = $config['php_extensions'] ?? [];

// Status color map
$statusColors = [
    'running' => 'success',
    'stopped' => 'danger',
    'warning' => 'warning'
];

// Build service information
$services = [];

// PHP-FPM
$phpFpmPort = $config['ports']['php'][0] ?? 9000;
$phpFpmPid = getProcessId('php-fpm', $phpFpmPort);
$services['php-fpm'] = [
    'name' => 'PHP-FPM',
    'status' => getServiceStatus('php-fpm', $phpFpmPort),
    'version' => getServiceVersion('php', $dependencies),
    'port' => $phpFpmPort,
    'process_id' => $phpFpmPid
];

// Nginx
$nginxHttpPort = $config['ports']['nginx'][0] ?? 80;
$nginxPid = getProcessId('nginx', $nginxHttpPort);
$services['nginx'] = [
    'name' => 'Nginx',
    'status' => getServiceStatus('nginx', $nginxHttpPort),
    'version' => getServiceVersion('nginx', $dependencies),
    'http_port' => $nginxHttpPort,
    'https_port' => $config['ports']['nginx'][1] ?? 443,
    'process_id' => $nginxPid
];

// Apache HTTPD
$httpdHttpPort = $config['ports']['httpd'][0] ?? 8080;
$httpdPid = getProcessId('httpd', $httpdHttpPort);
$services['httpd'] = [
    'name' => 'Apache HTTPD',
    'status' => getServiceStatus('httpd', $httpdHttpPort),
    'version' => getServiceVersion('httpd', $dependencies),
    'http_port' => $httpdHttpPort,
    'https_port' => $config['ports']['httpd'][1] ?? 8443,
    'process_id' => $httpdPid
];

// MySQL
$mysqlPort = $config['ports']['mysql'][0] ?? 3306;
$mysqlPid = getProcessId('mysql', $mysqlPort);
$services['mysql'] = [
    'name' => 'MySQL',
    'status' => getServiceStatus('mysql', $mysqlPort),
    'version' => getServiceVersion('mysql', $dependencies),
    'port' => $mysqlPort,
    'process_id' => $mysqlPid
];

// Redis
$redisPort = $config['ports']['redis'][0] ?? 6379;
$redisPid = getProcessId('redis', $redisPort);
$services['redis'] = [
    'name' => 'Redis',
    'status' => getServiceStatus('redis', $redisPort),
    'version' => getServiceVersion('redis', $dependencies),
    'port' => $redisPort,
    'process_id' => $redisPid
];

// Memcached
$memcachedPort = $config['ports']['memcached'][0] ?? 11211;
$memcachedPid = getProcessId('memcached', $memcachedPort);
$services['memcached'] = [
    'name' => 'Memcached',
    'status' => getServiceStatus('memcached', $memcachedPort),
    'version' => getServiceVersion('memcached', $dependencies),
    'port' => $memcachedPort,
    'process_id' => $memcachedPid
];

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Malt Dashboard - <?php echo htmlspecialchars($projectName); ?></title>
    <style>
        :root {
            --primary: #d4a14f;
            --primary-dark: #b7873c;
            --secondary: #333;
            --light: #f5f5f5;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
        }

        body {
            background-color: var(--light);
            color: var(--secondary);
            line-height: 1.6;
        }

        code {
            background: #f0f0f0;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-size: 0.9em;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e0e0e0;
        }

        .logo {
            display: flex;
            align-items: center;
        }

        .logo img {
            height: 60px;
            margin-right: 15px;
        }

        h1 {
            color: var(--primary-dark);
            font-size: 2rem;
        }

        h2 {
            margin: 20px 0 15px;
            color: var(--primary-dark);
        }

        .project-info {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .project-name {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .project-path {
            color: #666;
            font-family: monospace;
            background: #f0f0f0;
            padding: 5px 10px;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 15px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .card-title {
            font-weight: bold;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .status {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 5px;
        }

        .running {
            background-color: var(--success);
            box-shadow: 0 0 5px var(--success);
        }

        .stopped {
            background-color: var(--danger);
        }

        .warning {
            background-color: var(--warning);
        }

        .badge {
            font-size: 0.8rem;
            padding: 3px 8px;
            border-radius: 12px;
            background: var(--light);
            color: var(--secondary);
        }

        .badge-primary {
            background: var(--primary);
            color: white;
        }

        .badge-success {
            background: var(--success);
            color: white;
        }

        .badge-warning {
            background: var(--warning);
            color: white;
        }

        .badge-danger {
            background: var(--danger);
            color: white;
        }

        ul {
            list-style: none;
        }

        li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
        }

        li:last-child {
            border-bottom: none;
        }

        .btn {
            display: inline-block;
            padding: 8px 16px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9rem;
            transition: background 0.2s;
        }

        .btn:hover {
            background: var(--primary-dark);
        }

        .btn-sm {
            padding: 4px 10px;
            font-size: 0.8rem;
        }

        .action-row {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        footer {
            text-align: center;
            padding: 20px;
            margin-top: 40px;
            border-top: 1px solid #e0e0e0;
            color: #666;
        }

        .refresh-info {
            text-align: right;
            font-size: 0.8rem;
            color: #888;
            margin-bottom: 10px;
        }

        .extension-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            padding: 5px;
        }

        .ext-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
            color: white;
        }

        .ext-badge.enabled {
            background-color: var(--success);
        }

        .ext-badge.disabled {
            background-color: #6c757d;
            opacity: 0.7;
        }
    </style>
</head>
<body>
<div class="container">
    <header>
        <div class="logo">
            <img src="https://koriym.github.io/homebrew-malt/malt.jpg" alt="Malt Logo">
            <h1>Malt Dashboard</h1>
        </div>
    </header>

    <div class="project-info">
        <div class="project-name"><?php echo htmlspecialchars($projectName); ?></div>
        <div class="project-path"><?php echo htmlspecialchars($projectPath); ?></div>
        <div>
            <span id="project-status" class="badge badge-success">Active</span>
            <?php foreach($dependencies as $dependency):
                if (strpos($dependency, '@') !== false):
                    list($name, $version) = explode('@', $dependency);
                    $class = $name === 'php' ? 'badge-primary' : '';
                    ?>
                    <span class="badge <?php echo $class; ?>"><?php echo ucfirst($name) . ' ' . $version; ?></span>
                <?php endif; endforeach; ?>
        </div>
    </div>

    <h2>Services Status</h2>
    <div class="refresh-info">Last update: <?php echo date('Y-m-d H:i:s'); ?> <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-sm">Refresh</a></div>
    <div class="grid">
        <?php foreach ($services as $serviceId => $service): ?>
            <div class="card">
                <div class="card-title">
                    <?php echo htmlspecialchars($service['name']); ?>
                    <span><span class="status <?php echo $service['status']; ?>"></span>
                    <?php echo ucfirst($service['status']); ?></span>
                </div>
                <ul>
                    <?php if (isset($service['version'])): ?>
                        <li>
                            Version
                            <span><?php echo htmlspecialchars($service['version']); ?></span>
                        </li>
                    <?php endif; ?>

                    <?php if (isset($service['port'])): ?>
                        <li>
                            Port
                            <span><?php echo htmlspecialchars($service['port']); ?></span>
                        </li>
                    <?php endif; ?>

                    <?php if (isset($service['http_port'])): ?>
                        <li>
                            HTTP Port
                            <span><?php echo htmlspecialchars($service['http_port']); ?></span>
                        </li>
                    <?php endif; ?>

                    <?php if (isset($service['https_port'])): ?>
                        <li>
                            HTTPS Port
                            <span><?php echo htmlspecialchars($service['https_port']); ?></span>
                        </li>
                    <?php endif; ?>

                    <?php if (isset($service['process_id'])): ?>
                        <li>
                            Process ID
                            <span><?php echo htmlspecialchars($service['process_id']); ?></span>
                        </li>
                    <?php endif; ?>
                </ul>
                <div class="action-row">
                    <?php if ($serviceId === 'php-fpm'): ?>
                        <a href="phpinfo.php" target="_blank" class="btn">PHP Info</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <h2>PHP Extensions</h2>
    <div class="card">
        <div class="extension-badges">
            <?php
            // Specified PHP extensions + commonly important extensions
            $importantExtensions = array_merge($configuredExtensions, [
                'mbstring', 'intl', 'pdo', 'pdo_mysql', 'json', 'curl', 'zip'
            ]);

            foreach ($importantExtensions as $ext):
                // Special check for PDO and PDO_* extensions
                if ($ext === 'pdo') {
                    $isEnabled = extension_loaded('pdo');
                } elseif (strpos($ext, 'pdo_') === 0) {
                    $driver = substr($ext, 4); // Extract "mysql" from "pdo_mysql"
                    $isEnabled = extension_loaded('pdo') && in_array($driver, PDO::getAvailableDrivers());
                } else {
                    $isEnabled = extension_loaded($ext);
                }

                $badgeClass = $isEnabled ? 'enabled' : 'disabled';
                ?>
                <span class="ext-badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($ext); ?></span>
            <?php endforeach; ?>
        </div>
    </div>

    <footer>
        Powered by Malt - The essential ingredient for your development environment
        <div style="margin-top: 10px;">
            <a href="https://koriym.github.io/homebrew-malt/index.html" target="_blank" class="btn btn-sm">Documentation</a>
            <a href="https://github.com/koriym/homebrew-malt" target="_blank" class="btn btn-sm btn-outline">GitHub</a>
        </div>
    </footer>
</div>
</body>
</html>
