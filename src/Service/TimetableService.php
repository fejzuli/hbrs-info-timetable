<?php


namespace App\Service;


use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TimetableService
{
    private static $TIMETABLE_URL = 'https://eva2.inf.h-brs.de/stundenplan/anzeigen/';
    private static $TERM = '58093cf2610304f595ab37c08e58bcf3';
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $weeks
     * @param string $days
     * @param string $semester
     *
     * @return string
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getTimetableHtml(string $weeks, string $days, string $semester): string
    {
        $response = $this->client->request(
            'GET',
            self::$TIMETABLE_URL,
            [
                'query' => [
                    'weeks' => $weeks,
                    'days' => $days,
                    'mode' => 'table',
                    'identifier_semester' => $semester,
                    'show_semester' => '',
                    'identifier_dozent' => '',
                    'term' => self::$TERM
                ]
            ]
        );

        return $response->getContent();
    }
}