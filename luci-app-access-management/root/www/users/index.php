<?php
/*
 * ------------------------------------------------------------
 * Smart Redirector for Admin, PPPoE, & Hotspot Users
 * - Detect client IP from multiple headers
 * - Match IP ranges from get function
 * - Redirect users based on IP to the right destination
 * ------------------------------------------------------------
 * Created by @Taufik ( https://t.me/taufik_n_a )
 * © 2025 AlphaWireless.net by @Taufik
 * ------------------------------------------------------------
*/

function getPPPoERange()
{
    $pppoeConfigFile = '/etc/config/pppoe';
    if (file_exists($pppoeConfigFile)) {
        $configContents = file_get_contents($pppoeConfigFile);
        if (preg_match("/option\s+localip\s+'?([0-9\.]+)'?/", $configContents, $matches)) {
            $baseIp = $matches[1];
            $parts = explode('.', $baseIp);
            if (count($parts) === 4) {
                $prefix = "{$parts[0]}.{$parts[1]}.{$parts[2]}";
                return [
                    'start' => "$prefix.2",
                    'end' => "$prefix.254"
                ];
            }
        }
    }
    return [];
}

function getHotspotRange()
{
    $hotspotConfigFile = '/etc/config/chilli';
    if (file_exists($hotspotConfigFile)) {
        $configContents = file_get_contents($hotspotConfigFile);
        if (preg_match("/option\s+uamlisten\s+'?([0-9\.]+)'?/", $configContents, $matches)) {
            $baseIp = $matches[1];
            $parts = explode('.', $baseIp);
            if (count($parts) === 4) {
                $prefix = "{$parts[0]}.{$parts[1]}.{$parts[2]}";
                return [
                    'start' => "$prefix.2",
                    'end' => "$prefix.254"
                ];
            }
        }
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

        $lanSection = preg_match_all("/config interface 'lan'(.+?)(config|$)/s", $contents, $sections);
        if ($lanSection && count($sections[1]) > 0) {
            $lanBlock = $sections[1][0];

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
            'start' => long2ip($network + 2),
            'end'   => long2ip($broadcast - 1)
        ];
    }

    return [];
}

function getUhttpdPort()
{
    $configFile = '/etc/config/uhttpd';
    $port = '8080';

    if (!file_exists($configFile)) return $port;

    $contents = file_get_contents($configFile);

    if (preg_match("/config uhttpd 'main'(.+?)(config|$)/s", $contents, $matches)) {
        $block = $matches[1];

        if (preg_match_all("/list listen_http '([^']+)'/", $block, $listenMatches)) {
            foreach ($listenMatches[1] as $entry) {
                if (strpos($entry, '[') === false && strpos($entry, ':') !== false) {
                    $parts = explode(':', $entry);
                    if (isset($parts[1]) && is_numeric($parts[1])) {
                        return $parts[1];
                    }
                }
            }
        }
    }

    return $port;
}

if (array_key_exists('HTTP_CF_CONNECTING_IP', $_SERVER)) {
    $ipUser = ip2long($_SERVER["HTTP_CF_CONNECTING_IP"]);
} else if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
    $ipUser = ip2long(explode(',', $_SERVER["HTTP_X_FORWARDED_FOR"])[0]);
} else if (array_key_exists('HTTP_CLIENT_IP', $_SERVER)) {
    $ipUser = ip2long($_SERVER["HTTP_CLIENT_IP"]);
} else if (array_key_exists('REMOTE_ADDR', $_SERVER)) {
    $ipUser = ip2long($_SERVER['REMOTE_ADDR']);
} else {
    $ipUser = 0;
}

$pppoeRange = getPPPoERange();
if (!empty($pppoeRange)) {
    $start = ip2long($pppoeRange['start']);
    $end = ip2long($pppoeRange['end']);
    if ($ipUser >= $start && $ipUser <= $end) {
        header("Location: /pppoepages");
        exit;
    }
}

$hotspotRange = getHotspotRange();
if (!empty($hotspotRange)) {
    $start = ip2long($hotspotRange['start']);
    $end = ip2long($hotspotRange['end']);
    if ($ipUser >= $start && $ipUser <= $end) {
        header("Location: http://10.10.10.1:3990");
        exit;
    }
}

$lanRange = getLanRange();
if (!empty($lanRange)) {
    $start = ip2long($lanRange['start']);
    $end = ip2long($lanRange['end']);

    if ($ipUser >= $start && $ipUser <= $end) {
        $currentHost = $_SERVER['HTTP_HOST'];
        $parsedHost = parse_url("http://$currentHost", PHP_URL_HOST);
        $parsedPort = parse_url("http://$currentHost", PHP_URL_PORT);

        $uhttpdPort = getUhttpdPort();
        if (!$parsedPort || $parsedPort == 80) {
            $newLocation = "http://$parsedHost:$uhttpdPort";
        } else {
            $newLocation = "http://$parsedHost";
        }

        header("Location: $newLocation");
        exit;
    }
}
?>