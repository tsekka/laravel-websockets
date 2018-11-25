<?php

namespace BeyondCode\LaravelWebSockets\LaravelEcho\Pusher;

use Ratchet\ConnectionInterface;
use BeyondCode\LaravelWebSockets\LaravelEcho\Pusher\Channels\ChannelManager;
use stdClass;

class Dashboard
{
    const LOG_CHANNEL_PREFIX = 'private-websockets-dashboard-';

    const TYPE_DISCONNECTION = 'disconnection';

    const TYPE_CONNECTION = 'connection';

    const TYPE_VACATED = 'vacated';

    const TYPE_OCCUPIED = 'occupied';

    const TYPE_SUBSCRIBED = 'subscribed';

    const TYPE_CLIENT_MESSAGE = 'client_message';

    const TYPE_API_MESSAGE = 'api_message';

    public static function connection(ConnectionInterface $connection)
    {
        /** @var \GuzzleHttp\Psr7\Request $request */
        $request = $connection->httpRequest;

        self::log($connection->client->appId, self::TYPE_CONNECTION, [
            'details' => "Origin: {$request->getUri()->getScheme()}://{$request->getUri()->getHost()}",
            'socketId' => $connection->socketId,
        ]);
    }

    public static function disconnection(ConnectionInterface $connection)
    {
        self::log($connection->client->appId, self::TYPE_DISCONNECTION, [
            'socketId' => $connection->socketId
        ]);
    }

    public static function vacated(ConnectionInterface $connection, string $channelId)
    {
        self::log($connection->client->appId, self::TYPE_VACATED, [
            'details' => "Channel: {$channelId}"
        ]);
    }

    public static function occupied(ConnectionInterface $connection, string $channelId)
    {
        self::log($connection->client->appId, self::TYPE_OCCUPIED, [
            'details' => "Channel: {$channelId}"
        ]);
    }

    public static function subscribed(ConnectionInterface $connection, string $channelId)
    {
        self::log($connection->client->appId, self::TYPE_SUBSCRIBED, [
            'socketId' => $connection->socketId,
            'details' => "Channel: {$channelId}"
        ]);
    }

    public static function clientMessage(ConnectionInterface $connection, stdClass $payload)
    {
        self::log($connection->client->appId, self::TYPE_CLIENT_MESSAGE, [
            'details' => "Channel: {$payload->channel}, Event: {$payload->event}",
            'socketId' => $connection->socketId,
            'data' => json_encode($payload)
        ]);
    }

    public static function apiMessage($appId, string $channel, string $event, string $payload)
    {
        self::log($appId, self::TYPE_API_MESSAGE, [
            'details' => "Channel: {$channel}, Event: {$event}",
            'data' => $payload
        ]);
    }

    public static function log($appId, string $type, array $attributes = [])
    {
        $channelId = self::LOG_CHANNEL_PREFIX . $type;

        $channel = app(ChannelManager::class)->find($appId, $channelId);

        optional($channel)->broadcast([
            'event' => 'log_message',
            'channel' => $channelId,
            'data' => [
                'type' => $type,
                'time' => strftime("%H:%M:%S")
            ] + $attributes
        ]);
    }

}