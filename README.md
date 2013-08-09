Ratp SDK
========

This repository contains a small library to consume the RATP REST web service
(huhu)

Usage

```php

$api = new Lyrixx\Rapt\Api();

$stops = array(
    array('line' => '138', 'stop' => 'General Leclerc-Victor Hugo', 'type' => Lyrixx\Rapt\::TYPE_BUS),
);

$stops = $api->getStops($stops);

$stop[0]->getName(); // 'General Leclerc-Victor Hugo'
$stop[0]->getLine(); // '138'
$stop[0]->getType(); // 'metro'

$direction = $stop[0]->getDirection('Saint-Gratien RER');
foreach($direction->getSchedules() => $time) {
    echo $time; // '11 mn';
}
```
