<?php
/*
 * ------------------------------------------------------------------
 * Smart Redirector for Admin, PPPoE users, & Hotspot users
 * - Detect client IP from multiple headers
 * - Detect request from multiple server
 * - Match IP ranges from get function
 * - Redirect users based on IP and URL to the right destination
 * ------------------------------------------------------------------
 * License : (GPL-2.0) GNU General Public License Version 2
 * Created & developed by @Taufik <https://t.me/taufik_n_a>
 * © 2025 AlphaNetwork by @Taufik
 * ------------------------------------------------------------------
*/

function getUserIp($mode = 'long')
{
    $candidates = [
        'HTTP_CF_CONNECTING_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_CLIENT_IP',
        'REMOTE_ADDR'
    ];

    foreach ($candidates as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = trim(explode(',', $_SERVER[$key])[0]);

            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                if ($mode === 'ip') return $ip;
                return ip2long($ip);
            }
        }
    }

    return ($mode === 'ip') ? '0.0.0.0' : 0;
}

function getAcmtConfig($searchConf)
{
    $conf = '/tmp/run/acmt.conf';
    $conf_results = '';

    if (file_exists($conf)) {
        $config = file_get_contents($conf);
        if (preg_match('/^' . preg_quote($searchConf, '/') . '="([^"]+)"/m', $config, $m)) {
            $conf_results = $m[1];
        }
    }

    return $conf_results;
}

function getConfigValue($key, $default = '')
{
    $val = getAcmtConfig($key);
    if (!empty($val)) return $val;

    $uciKey = strtolower($key);
    $uciVal = trim(shell_exec("uci get acmt.main." . escapeshellarg($uciKey) . " 2>/dev/null"));
    if (!empty($uciVal)) return $uciVal;

    return $default;
}

function getIpMac($action, $input)
{
    $db_server = getConfigValue('DB_SERVER', '127.0.0.1');
    $db_user   = getConfigValue('DB_USER', 'radmon');
    $db_pass   = getConfigValue('DB_PASS', 'radmon');
    $db_name   = getConfigValue('DB_NAME', 'radmon');

    $conn = new mysqli($db_server, $db_user, $db_pass, $db_name);

    $sql = "";
    $output = "";
    if ($conn->connect_error) {
        return $output;
    }

    if ($action === "getIP") {
        $sql = "SELECT framedipaddress FROM radacct WHERE acctstoptime IS NULL AND REPLACE(callingstationid, '-', ':') = REPLACE(?, '-', ':') ORDER BY radacctid DESC LIMIT 1";
    } elseif ($action === "getMAC") {
        $sql = "SELECT callingstationid FROM radacct WHERE acctstoptime IS NULL AND framedipaddress = ? ORDER BY radacctid DESC LIMIT 1";
    } else {
        return $output;
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $input);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if ($action === "getIP") {
            $output = $row['framedipaddress'];
        } elseif ($action === "getMAC") {
            $output = $row['callingstationid'];
        } else {
            return $output;
        }
    }

    $stmt->close();
    $conn->close();
    return $output;
}

function getWListedIp()
{
    $conf_enabled = getConfigValue('IP_WL_ENABLED', '');
    $conf_output  = getConfigValue('IP_WHITELIST', '');

    if ($conf_enabled === "1" && !empty($conf_output)) {
        $ipList = array_filter(array_map('trim', explode(",", $conf_output)));
        $userIp = getUserIp('long');

        foreach ($ipList as $ip) {
            if (!empty($ip) && $userIp === ip2long($ip)) {
                return true;
            }
        }
    }

    return false;
}

function getWListedMac()
{
    $conf_enabled = getConfigValue('MAC_WL_ENABLED', '');
    $conf_output  = getConfigValue('MAC_WHITELIST', '');

    if ($conf_enabled === "1" && !empty($conf_output)) {
        $macList = array_filter(array_map('trim', explode(",", $conf_output)));
        $userIp  = getUserIp('long');

        foreach ($macList as $mac) {
            $ip = getIpMac("getIP", $mac);
            if (!empty($ip) && $userIp === ip2long($ip)) {
                return true;
            }
        }
    }

    return false;
}

function getAcmtStatus()
{
    $output = [];
    $exitCode = 1;
    exec("/usr/bin/acmt status", $output, $exitCode);
    return $exitCode === 0;
}

function getUhttpdPort()
{
    $conf = '/etc/config/uhttpd';
    $fallbackPorts = '8080';

    if (!file_exists($conf)) return $fallbackPorts;

    $config = file_get_contents($conf);
    if (preg_match("/config uhttpd 'main'(.+?)(config|$)/s", $config, $matches)) {
        if (preg_match("/list listen_http '([^']+):(\d+)'/", $matches[1], $m)) {
            return $m[2];
        } elseif (preg_match("/option listen_http '([^']+):(\d+)'/", $matches[1], $m)) {
            return $m[2];
        } else {
            return $fallbackPorts;
        }
    }

    return $fallbackPorts;
}

function getRangeFromPrefix($baseIp)
{
    $parts = explode('.', $baseIp);
    if (count($parts) === 4) {
        $prefix = "{$parts[0]}.{$parts[1]}.{$parts[2]}";
        return ['start' => ip2long("$prefix.2"), 'end' => ip2long("$prefix.254")];
    }
    return [];
}

function getPPPoERange()
{
    $conf = '/etc/config/pppoe';
    if (file_exists($conf) && preg_match("/option\s+localip\s+'?([\d\.]+)'?/", file_get_contents($conf), $m)) {
        return getRangeFromPrefix($m[1]);
    }
    return [];
}

function getHotspotRange()
{
    $conf = '/etc/config/chilli';
    if (file_exists($conf) && preg_match("/option\s+uamlisten\s+'?([\d\.]+)'?/", file_get_contents($conf), $m)) {
        return getRangeFromPrefix($m[1]);
    }
    return [];
}

function getLanRange()
{
    $networkFile = '/etc/config/network';
    $ipaddr = '';
    $netmask = '';

    if (file_exists($networkFile)) {
        $contents = file_get_contents($networkFile);

        if (preg_match("/config interface 'lan'(.+?)(config|$)/s", $contents, $sections)) {
            $lanBlock = $sections[1];

            if (preg_match("/option ipaddr '([0-9\.]+)'/", $lanBlock, $ipMatch)) {
                $ipaddr = $ipMatch[1];
            }

            if (preg_match("/option netmask '([0-9\.]+)'/", $lanBlock, $maskMatch)) {
                $netmask = $maskMatch[1];
            }
        }
    }

    if ($ipaddr && $netmask) {
        $ipLong = ip2long($ipaddr);
        $maskLong = ip2long($netmask);
        $network = $ipLong & $maskLong;
        $broadcast = $network | (~$maskLong & 0xFFFFFFFF);

        return [
            'start' => $network + 2,
            'end'   => $broadcast - 1
        ];
    }

    return [];
}

function cidrToRange($ip, $cidr, $func)
{
    if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) return null;

    if (!is_numeric($cidr) || $cidr < 0 || $cidr > 32) return null;

    $ipLong = ip2long($ip);
    $mask = -1 << (32 - intval($cidr));
    $network = $ipLong & $mask;
    $broadcast = $network | (~$mask & 0xFFFFFFFF);

    if ($network <= 0 || $broadcast >= 0xFFFFFFFF) {
        return null;
    }

    if ($func === "ZT") return ['start' => $network + 1, 'end' => $broadcast - 1];
    elseif ($func === "TS") return ['start' => $network - 1, 'end' => $broadcast + 1];
    else return null;
}

function getZerotierRange()
{
    $ranges = [];

    $exec = shell_exec("zerotier-cli listnetworks 2>/dev/null");
    if ($exec) {
        foreach (explode("\n", $exec) as $line) {
            if (preg_match("/\s([\d\.]+)\/(\d+)/", $line, $m)) {
                $range = cidrToRange($m[1], $m[2], "ZT");
                if ($range) $ranges[] = $range;
            }
        }
    }

    return $ranges;
}

function getTailscaleRange()
{
    $ranges = [];

    $which = trim(shell_exec("which tailscale 2>/dev/null"));
    if (empty($which)) return $ranges;

    $serviceStatus = trim(shell_exec("/etc/init.d/tailscale status 2>/dev/null"));
    if ($serviceStatus !== "running") return $ranges;

    $exec = shell_exec("tailscale status --json 2>/dev/null");
    if (!$exec) return $ranges;

    $json = json_decode($exec, true);
    if (!$json) return $ranges;

    $addRanges = function($cidrs) use (&$ranges) {
        foreach ($cidrs as $cidr) {
            if (strpos($cidr, '/') !== false) {
                list($ip, $prefix) = explode('/', $cidr);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                    $range = cidrToRange($ip, $prefix, "TS");
                    if ($range) $ranges[] = $range;
                }
            }
        }
    };

    if (!empty($json['Self']['AllowedIPs']))
        $addRanges($json['Self']['AllowedIPs']);

    if (!empty($json['Peer'])) {
        foreach ($json['Peer'] as $peer) {
            if (!empty($peer['AllowedIPs']))
                $addRanges($peer['AllowedIPs']);
        }
    }

    return $ranges;
}

function substrStrlen($uri, $strings)
{
    if (substr($uri, -strlen($strings)) === $strings) return true;
    return false;
}

function getSegmentsAfterPPP(string $path): array {
    if (strpos($path, '/ppp') !== 0) return [];
    $after = substr($path, 4);
    $after = ltrim($after, '/');
    return $after === '' ? [] : explode('/', $after);
}

function redirectToMainPort($host, $ports, $uri)
{
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: http://{$host}:{$ports}{$uri}");
    exit;
}

function getVariable($variableName)
{
    $variableSelect = null;

    if (empty($variableName)) return null;
    elseif ($variableName === "ip") $variableSelect = getUserIp('long');
    elseif ($variableName === "ports") $variableSelect = getUhttpdPort();
    elseif ($variableName === "lanRanges") $variableSelect = getLanRange();
    elseif ($variableName === "pppoeRanges") $variableSelect = getPPPoERange();
    elseif ($variableName === "hotspotRanges") $variableSelect = getHotspotRange();
    elseif ($variableName === "zerotierRanges") $variableSelect = getZerotierRange();
    elseif ($variableName === "tailscaleRanges") $variableSelect = getTailscaleRange();
    elseif ($variableName === "host") $variableSelect = $_SERVER['HTTP_HOST'];
    elseif ($variableName === "uri") $variableSelect  = $_SERVER['REQUEST_URI'] ?? '/';
    elseif ($variableName === "docRoot") $variableSelect = $_SERVER['DOCUMENT_ROOT'];
    elseif ($variableName === "path") $variableSelect = $_SERVER['DOCUMENT_ROOT'] . $_SERVER['REQUEST_URI'];
    else return null;

    return $variableSelect;
}

if (getVariable("ip") <= 0) {
    header("HTTP/1.1 400 Bad Request");
    echo "<h1>Bad Request</h1>";
    echo "You IP address invalid on this server.";
    exit;
} elseif (substrStrlen(getVariable("uri"), "/img/logo/radmon-logo.png") && !empty($_SERVER['REDIRECT_STATUS'])) {
    $file = __DIR__ . "/../RadMonv2/img/logo/radmon-logo.png";
    $status = intval($_SERVER['REDIRECT_STATUS']);

    if (file_exists($file) && $status === 404) {
        $mime = mime_content_type($file);
        header("Content-Type: $mime");
        header("Content-Length: " . filesize($file));
        readfile($file);
        exit;
    } else {
        if (!empty(getVariable("lanRanges")) && getVariable("ip") >= getVariable("lanRanges")['start'] && getVariable("ip") <= getVariable("lanRanges")['end']) {
            redirectToMainPort(getVariable("host"), getVariable("ports"), getVariable("uri"));
            exit;
        }
    }
} elseif (getAcmtStatus() && getWListedIp()) {
    redirectToMainPort(getVariable("host"), getVariable("ports"), getVariable("uri"));
    exit;
} elseif (getAcmtStatus() && getWListedMac()) {
    redirectToMainPort(getVariable("host"), getVariable("ports"), getVariable("uri"));
    exit;
} elseif (!empty(getVariable("pppoeRanges")) && getVariable("ip") >= getVariable("pppoeRanges")['start'] && getVariable("ip") <= getVariable("pppoeRanges")['end']) {
    $ip = getUserIp('ip');
    $mac = getIpMac('getMAC', $ip);
    $parsed = parse_url(getVariable("uri"));
    $path = $parsed['path'] ?? '/';
    $pathNormalized = rtrim($path, '/');

    $dir = "pppoepages";
    $routes = [
        'home'    => $dir . "/home.php",
        'package' => $dir . "/package.php",
        'faq'     => $dir . "/faq.php",
        'contact' => $dir . "/contact.php",
        'message' => $dir . "/message.php",
    ];

    if (!empty($_SERVER['REDIRECT_STATUS']) && intval($_SERVER['REDIRECT_STATUS']) === 404) {
        $segments = getSegmentsAfterPPP($path);

        if (empty($segments)) {
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: /ppp/home");
            exit;
        }

        $first = $segments[0];
        $extra = array_slice($segments, 1);

        if (isset($routes[$first]) && file_exists($routes[$first]) && empty($extra)) {
            include $routes[$first];
            exit;
        }

        header("HTTP/1.1 301 Moved Permanently");
        header("Location: /ppp/home");
        exit;

    } else {
        if ($path === '/' || $path === '') {
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: /ppp/home");
            exit;
        }
    }
} elseif (!empty(getVariable("hotspotRanges")) && getVariable("ip") >= getVariable("hotspotRanges")['start'] && getVariable("ip") <= getVariable("hotspotRanges")['end']) {
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: http://10.10.10.1:3990");
    exit;
} elseif (!empty(getVariable("lanRanges")) && getVariable("ip") >= getVariable("lanRanges")['start'] && getVariable("ip") <= getVariable("lanRanges")['end']) {
    redirectToMainPort(getVariable("host"), getVariable("ports"), getVariable("uri"));
    exit;
} else {
    foreach (getVariable("zerotierRanges") as $range) {
        if (getVariable("ip") >= $range['start'] && getVariable("ip") <= $range['end']) {
            redirectToMainPort(getVariable("host"), getVariable("ports"), getVariable("uri"));
            exit;
        }
    }

    foreach (getVariable("tailscaleRanges") as $range) {
        if (getVariable("ip") >= $range['start'] && getVariable("ip") <= $range['end']) {
            redirectToMainPort(getVariable("host"), getVariable("ports"), getVariable("uri"));
            exit;
        }
    }
}

header("HTTP/1.1 403 Forbidden");
echo "<h1>Forbidden</h1>";
echo "You don't have permission to access " . htmlspecialchars(getVariable("uri"), ENT_QUOTES, 'UTF-8') . " on this server.";

?>
