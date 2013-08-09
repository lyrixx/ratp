<?php

namespace Lyrixx\Ratp;

use Goutte\Client;

class Api
{
    CONST TYPE_METRO = 'metro';
    CONST TYPE_BUS   = 'bus';

    private $client;
    private $entrypoint;

    public function __construct(Client $client = null, $entrypoint = null)
    {
        $this->client = $client ?: new Client();
        $this->entrypoint = $entrypoint ?: 'http://wap.ratp.fr/siv/schedule';
        $this->client->setServerParameter('HTTP_USER_AGENT', 'Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:19.0) Gecko/20100101 Firefox/19.0');
    }

    public function getStops(array $stopsData)
    {
        $stops = array();

        foreach ($this->buildQueries($stopsData) as $stopData) {
            $id = sprintf('%s.%s', $stopData['line'], $stopData['stop']);

            $this->client->request('GET', $stopData['query']);

            try {
                $name = $this->client->getCrawler()->filter('.bwhite')->eq(1)->text();
            } catch (\InvalidArgumentException $e) {
                continue;
            }

            if (!isset($stops[$id])) {
                $stops[$id] = $stop = new Stop($stopData['line'], $name, $stopData['type']);
            } else {
                $stops[$id] = $stop;
            }

            foreach ($this->client->getCrawler()->filter('.bg1, .bg3') as $child) {
                // Hack to remove all service messages
                if ("\n" !== $child->nodeValue[0]) {
                    continue;
                }

                // Remove some space and ">"
                if (!$directionName = trim($child->nodeValue, "\n > ")) {
                    continue;
                }

                if (!$time = trim($child->nextSibling->nodeValue)) {
                    continue;
                }

                if (!$stop->hasDirection($directionName)) {
                    $stop->addDirection($directionName);
                }
                $stop->getDirection($directionName)->addWaitingTime($time);
            }

            if (!$stop->hasDirections()) {
                unset($stops[$id]);
            }
        }

        return array_values($stops);
    }

    private function buildQueries(array $stops = array())
    {
        $stopsTmp = array();
        foreach ($stops as $stop) {
            if (static::TYPE_METRO ==  $stop['type']) {
                $query = $this->buildUrl($stop['type'], $stop['line'], $stop['stop'], 'A');
                $stopsTmp[] = array_replace($stop, array('query' => $query));
                $query = $this->buildUrl($stop['type'], $stop['line'], $stop['stop'], 'R');
                $stopsTmp[] = array_replace($stop, array('query' => $query));
            } else {
                $query = $this->buildUrl($stop['type'], $stop['line'], $stop['stop']);
                $stopsTmp[] = array_replace($stop, array('query' => $query));
            }
        }

        return $stopsTmp;
    }

    private function buildUrl($type, $line, $stop, $dir = null)
    {
        $params = array(
            'service' => 'next',
            'reseau' => $type,
            'lineid' => strtoupper($type[0]) .$line,
            'stationname' => $stop,
        );

        if ($dir) {
            $params['directionsens'] = strtoupper($dir);
        }

        return sprintf('%s?%s', $this->entrypoint, http_build_query($params));
    }
}
