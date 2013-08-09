<?php

namespace Lyrixx\Ratp\Tests;

use Goutte\Client;
use Guzzle\Http\Client as GuzzleClient;
use Guzzle\Plugin\Mock\MockPlugin;
use Lyrixx\Ratp\Api;

class ApiTest extends \PHPUnit_Framework_TestCase
{
    private $guzzleMockPlugin;
    private $api;

    public function setUp()
    {
        $this->guzzleMockPlugin = new MockPlugin();

        $guzzleClient = new GuzzleClient();
        $guzzleClient->addSubscriber($this->guzzleMockPlugin);

        $goutte = new Client();
        $goutte->setClient($guzzleClient);

        $this->api = new Api($goutte);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Parameter "line" is missing for stop #1.
     */
    public function testGetDatasWithInvalidValue()
    {
        $stops = $this->api->getStops(array(
            array('type' => Api::TYPE_METRO, 'line' => '13', 'stop' => 'Porte de Saint-Ouen'),
            array('foo' => 'bar'),
            array('type' => Api::TYPE_METRO, 'line' => '15', 'stop' => 'Porte de Saint-Ouen'),
        ));
    }

    public function testGetDatasWithMetro()
    {
        $this->guzzleMockPlugin->addResponse($this->getResponse('metro-full-A.html'));
        $this->guzzleMockPlugin->addResponse($this->getResponse('metro-full-R.html'));

        $stops = $this->api->getStops(array(array('type' => Api::TYPE_METRO, 'line' => '13', 'stop' => 'Porte de Saint-Ouen')));

        $this->assertCount(1, $stops);

        $stop = reset($stops);
        $this->assertInstanceOf('Lyrixx\Ratp\Stop', $stop);
        $this->assertSame('Porte de Saint-Ouen', $stop->getName());
        $this->assertSame('13', $stop->getLine());
        $this->assertSame('metro', $stop->getType());

        $directions = $stop->getDirections();
        $this->assertCount(2, $directions);

        $this->assertArrayHasKey('Saint-Denis-Université', $directions);
        $direction1 = $directions['Saint-Denis-Université'];
        $this->assertInstanceOf('Lyrixx\Ratp\Direction', $direction1);
        $this->assertSame('Saint-Denis-Université', $direction1->getName());
        $schedule = $direction1->getSchedule();
        $this->assertInstanceOf('Lyrixx\Ratp\Schedule', $schedule);
        $this->assertCount(4, $schedule);
        $this->assertSame(array('4 mn', '13 mn', '21 mn', '28 mn'), iterator_to_array($schedule));

        $this->assertArrayHasKey('Châtillon-Montrouge', $directions);
        $direction2 = $directions['Châtillon-Montrouge'];
        $this->assertInstanceOf('Lyrixx\Ratp\Direction', $direction2);
        $this->assertSame('Châtillon-Montrouge', $direction2->getName());
        $schedule = $direction2->getSchedule();
        $this->assertInstanceOf('Lyrixx\Ratp\Schedule', $schedule);
        $this->assertCount(4, $schedule);
        $this->assertSame(array('2 mn', '10 mn', '18 mn', '26 mn'), iterator_to_array($schedule));
    }

    public function testGetDatasWithBus()
    {
        $this->guzzleMockPlugin->addResponse($this->getResponse('bus-full.html'));

        $stops = $this->api->getStops(array(array('type' => Api::TYPE_BUS, 'line' => '138', 'stop' => 'General Leclerc-Victor Hugo')));

        $this->assertCount(1, $stops);

        $stop = reset($stops);
        $this->assertInstanceOf('Lyrixx\Ratp\Stop', $stop);
        $this->assertSame('General Leclerc-Victor Hugo', $stop->getName());
        $this->assertSame('138', $stop->getLine());
        $this->assertSame('bus', $stop->getType());

        $directions = $stop->getDirections();
        $this->assertCount(2, $directions);

        $this->assertArrayHasKey('Saint-Gratien RER', $directions);
        $direction1 = $directions['Saint-Gratien RER'];
        $this->assertInstanceOf('Lyrixx\Ratp\Direction', $direction1);
        $this->assertSame('Saint-Gratien RER', $direction1->getName());
        $schedule = $direction1->getSchedule();
        $this->assertInstanceOf('Lyrixx\Ratp\Schedule', $schedule);
        $this->assertCount(1, $schedule);
        $this->assertSame(array('12 mn'), iterator_to_array($schedule));

        $this->assertArrayHasKey('Gare d\'Ermont Eaubonne-RER', $directions);
        $direction2 = $directions['Gare d\'Ermont Eaubonne-RER'];
        $this->assertInstanceOf('Lyrixx\Ratp\Direction', $direction2);
        $this->assertSame('Gare d\'Ermont Eaubonne-RER', $direction2->getName());
        $schedule = $direction2->getSchedule();
        $this->assertInstanceOf('Lyrixx\Ratp\Schedule', $schedule);
        $this->assertCount(1, $schedule);
        $this->assertSame(array('30 mn'), iterator_to_array($schedule));
    }

    public function tearDown()
    {
        $this->guzzleMockPlugin = null;
        $this->api = null;
    }

    private function getResponse($path)
    {
        return MockPlugin::getMockFile(__DIR__.'/fixtures/'.$path);
    }
}
