<?php

set_time_limit(0);
error_reporting(E_ALL);
echo "<h2>Соединение TCP/IP</h2>\n";

function isIP ($str) {
    preg_match('/^([0-9]{1,3}[\.]){3}[0-9]{1,3}$/', $str,$matches);
    if (isset($matches[0]))
        return true;
    return false;
}

$json_servers = file_get_contents('servers.json');
$server_list = json_decode($json_servers, true);
$domain_zone = '';
if (isIP($_POST['info'])) {
    echo 'ok' . "<br>";
    $_POST['info'] = gethostbyaddr($_POST['info']); 
    echo $_POST['info'] . "<br>";
}
for ($i = 0; $i < strlen($_POST['info']); $i++) {
    if ($_POST['info'][$i] == '.') {
        break;
    }
    $domain_zone .= $_POST['info'][$i];
}

$domain_zone = str_replace($domain_zone . '.', "", $_POST['info']);
if (key_exists($domain_zone, $server_list)) {
    echo "Whois server: {$server_list[$domain_zone][0]} <br>";
}else {
    echo 'Не найден сервер для вашей доменной зоны';
    exit;
}

$address = gethostbyname($server_list[$domain_zone][0]);

$in = $_POST['info'] . "\r\n";

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket === false) {
    echo "Не удалось выполнить socket_create(): причина: " . socket_strerror(socket_last_error()) . "\n";
} else {
    echo "OK.\n";
}

echo "Пытаемся соединиться с '$address' на порту '43'...\n";
$result = socket_connect($socket, $address, 43);
if ($result === false) {
    echo "Не удалось выполнить socket_connect().\nПричина: ($result) " . socket_strerror(socket_last_error($socket)) . "\n";
} else {
    echo "OK.\n";
}

$out = '';

socket_write($socket, $in, strlen($in));
echo "OK.\n";

echo "Читаем ответ:\n\n";
echo "<pre>";
while (($out = socket_read($socket, 16384))) {
    echo $out;
}
echo "</pre>";

echo "Закрываем сокет...";
socket_close($socket);
echo "OK.\n\n";