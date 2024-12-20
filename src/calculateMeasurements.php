<?php

$file = fopen('./measurements.txt', 'r');
$stations = [];
$start = hrtime()[0];

$chunkSize = 1048576; // 1MB
$chunkNumber = 0;

while (!feof($file)) {
    $data = fread($file, $chunkSize);
    $lines = explode("\n", $data);
    foreach ($lines as $line) {
        $pos = strpos($line, ';');
        $station = substr($line, 0, $pos);
        $temperature = (int) substr($line, $pos+1, -1);
        if (!isset($stations[$station])) {
            $stations[$station]['min'] = $temperature;
            $stations[$station]['max'] = $temperature;
            $stations[$station]['count'] = 1;
            $stations[$station]['sum'] = $temperature;
            continue;
        }
        $stations[$station]['count']++;
        $stations[$station]['sum'] += $temperature;
        if ($temperature < $stations[$station]['min']) {
            $stations[$station]['min'] = $temperature;
            continue;
        }
        $stations[$station]['max'] = $temperature;
    }
}
fclose($file);
ksort($stations);
foreach ($stations as $station => $data) {
    $mean = $data['sum'] / $data['count'];
    echo "{\n";
    echo "Station: $station\n";
    echo "Min: {$data['min']}\n";
    echo "Max: {$data['max']}\n";
    echo "Mean: $mean\n";
    echo "}\n";
    echo "\n";
}
$end = $start = hrtime()[0] - $start;
echo "Time in sec: $end\n";