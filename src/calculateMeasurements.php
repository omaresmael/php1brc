<?php

$file = fopen('./measurements.txt', 'r');
$stations = [];
$start = hrtime()[0];

while ($data = fgetcsv(stream: $file, separator: ';',escape: '\\')) {
    $station = $data[0];
    $temperature = $data[1];
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
fclose($file);
ksort($stations);
foreach ($stations as $station => $data) {
    $mean = $data['sum'] / $data['count'];
    echo "Station: $station\n";
    echo "Min: {$data['min']}\n";
    echo "Max: {$data['max']}\n";
    echo "Mean: $mean\n";
    echo "\n";
}
$end = $start = hrtime()[0] - $start;
echo "Time in sec: $end\n";