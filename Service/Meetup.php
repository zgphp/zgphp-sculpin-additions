<?php

namespace Zgphp\Sculpin\Bundle\ZgphpSculpinAdditionsBundle\Service;

use Sculpin\Core\Sculpin;
use Sculpin\Core\Event\SourceSetEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Meetup implements EventSubscriberInterface
{
    protected $configuration;

    public function __construct(\Dflydev\DotAccessConfiguration\Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public static function getSubscribedEvents() {
        return array(
            Sculpin::EVENT_BEFORE_RUN => array('beforeRun', 100),
        );
    }

    public function beforeRun(SourceSetEvent $sourceSetEvent)
    {
        $key = $this->configuration->get('meetup_api_key');

        $query = [
            "sign" => true,
            "key" => $key,
            "group_urlname" => "ZgPHP-meetup",
            "status" => "upcoming",
            "order" => "time",
            "visibility" => "public"
        ];

        $url = "https://api.meetup.com/2/events";
        $url .= "?" . http_build_query($query);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $json = curl_exec($ch);
        curl_close($ch);

        if ($json === false) {
            throw new \ErrorException("Failed fetching data");
        }

        $data = json_decode($json);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \ErrorException("Failed decoding json data:" . json_last_error_msg());
        }

        if (!empty($data->problem)) {
            throw new \ErrorException($data->problem . ". " . $data->details);
        }

        if (empty($data->results)) {
            throw new \ErrorException("No pending meetups found.");
        }

        $meetup = $data->results[0];

        $time = date("d.m.Y @ H:i", $meetup->time / 1000);
        $address = urlencode($meetup->venue->address_1);
        $city = urlencode($meetup->venue->city);
        $lat = $meetup->venue->lat;
        $lon = $meetup->venue->lon;
        $mapURL = "https://www.google.com/maps/place/$address,+$city,+Croatia/@$lat,$lon,17z";

        $time = date("d.m.Y @ H:i", $meetup->time / 1000);

        $this->configuration->set('meetup', $meetup->name);
        $this->configuration->set('meetup_time', $time);
        $this->configuration->set('meetup_map_url', $mapURL);
        $this->configuration->set('meetup_venue_name', $meetup->venue->name);
        $this->configuration->set('meetup_venue_address', $meetup->venue->address_1);
        $this->configuration->set('meetup_event_url', $meetup->event_url);
    }
}