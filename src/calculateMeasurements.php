<?php

use parallel\{Runtime, Channel};

$startTime = hrtime()[0];
$file = './measurements.txt';
$numThreads = 16;
$chunkSize = 1000000;

$fileSize = filesize($file);
$features = [];

$chunkPerThread = ceil($fileSize / $numThreads);

for ($i = 0; $i < $numThreads; $i++) {
    $runtime = new Runtime();

    $start = $i * $chunkPerThread;
    $end = ($i + 1) * $chunkPerThread;

    if ($i === $numThreads - 1) {
        $end = $fileSize;
    }

    $features[] = $runtime->run(function($file, $start, $end, $chunkSize) {
        $stations = [];
        $fileHandle = fopen($file, 'r');

        fseek($fileHandle, $start);

        $buffer = '';
        while (ftell($fileHandle) < $end && !feof($fileHandle)) {

            $data = fread($fileHandle, $chunkSize);
            $buffer .= $data;

            $lines = explode("\n", $buffer);

            for ($i = 0; $i < count($lines) - 1; $i++) {
                $line = $lines[$i];
                $pos = strpos($line, ';');

                $station = substr($line, 0, $pos);
                $temperature = (int) substr($line, $pos + 1);

                if (!isset($stations[$station])) {
                    $stations[$station] = [
                        'min' => $temperature,
                        'max' => $temperature,
                        'count' => 1,
                        'sum' => $temperature
                    ];
                } else {
                    $stations[$station]['count']++;
                    $stations[$station]['sum'] += $temperature;
                    if ($temperature < $stations[$station]['min']) {
                        $stations[$station]['min'] = $temperature;
                    }
                    if ($temperature > $stations[$station]['max']) {
                        $stations[$station]['max'] = $temperature;
                    }
                }
            }


            $buffer = $lines[count($lines) - 1];
        }

        fclose($fileHandle);

        return $stations;
    }, [$file, $start, $end, $chunkSize]);
}

$results = [];
for ($i = 0; $i < $numThreads; $i++) {
    $results[] = $features[$i]->value();
}

$finalStations = [];
foreach ($results as $threadResult) {
    foreach ($threadResult as $station => $data) {
        if (!isset($finalStations[$station])) {
            $finalStations[$station] = $data;
        } else {
            $finalStations[$station]['count'] += $data['count'];
            $finalStations[$station]['sum'] += $data['sum'];
            if ($data['min'] < $finalStations[$station]['min']) {
                $finalStations[$station]['min'] = $data['min'];
            }
            if ($data['max'] > $finalStations[$station]['max']) {
                $finalStations[$station]['max'] = $data['max'];
            }
        }
    }
}
ksort($finalStations);
foreach ($finalStations as $station => $data) {
    $average = $data['sum'] / $data['count'];
    echo "$station/{$data['min']}/$average/{$data['max']}\n";
}

$end = hrtime()[0] - $startTime;
echo "Time in sec: $end\n";