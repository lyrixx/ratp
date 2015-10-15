Ratp SDK
========

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/ca1d2a32-febd-4e8d-96cd-a17f0aaf6bf1/mini.png)](https://insight.sensiolabs.com/projects/ca1d2a32-febd-4e8d-96cd-a17f0aaf6bf1)
[![Build Status](https://travis-ci.org/lyrixx/ratp.png?branch=master)](https://travis-ci.org/lyrixx/ratp)

This repository contains a small library to consume the RATP REST web service
(huhu)

Usage

```php

$api = new Lyrixx\Ratp\Api();

$stop = array(
    array('line' => '138', 'stop' => 'General Leclerc-Victor Hugo', 'type' => $api::TYPE_BUS),
);

$stops = $api->getStops($stop);

$stops[0]->getName(); // 'General Leclerc-Victor Hugo'
$stops[0]->getLine(); // '138'
$stops[0]->getType(); // 'metro'

$direction = $stops[0]->getDirection('Ermont-Eaubonne RER');
foreach($direction->getSchedule() as $time) {
    echo $time; // '11 mn';
}
```
