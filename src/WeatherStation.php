<?php
namespace Omaresmaeel\Php1brc;
class WeatherStation
{
    public function __construct(public string $id, public float $meanTemperature) {

    }
    public function measurement() {
        $m = $this->gaussMs($this->meanTemperature);
        return round($m * 10) / 10.0;
    }

    // PHP lacks a native Gaussian/normal distribution function, so we implement the Box-Muller transform
    private function gaussMs(float $mean) {
        $u1 = mt_rand() / mt_getrandmax();
        $u2 = mt_rand() / mt_getrandmax();
        $randStdNormal = sqrt(-2 * log($u1)) * sin(2 * pi() * $u2);
        return $mean + 10 * $randStdNormal;
    }


}