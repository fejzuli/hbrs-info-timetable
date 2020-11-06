<?php


namespace App\Service;


use Masterminds\HTML5;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TimetableService
{
    private static $TIMETABLE_URL = 'https://eva2.inf.h-brs.de/stundenplan/anzeigen/';
    private static $TERM = '58093cf2610304f595ab37c08e58bcf3';
    private static $EVENT_EXTRAS_PATTERN = '/(?:\(([A-ZÀ-ÖØ-öø-ÿ ]+)\) )?(?:Gr\.? ?(alle|[A-Z1-9](?:\-?[1-9]*)(?:\+[A-Z1-9])*) )?(?:\(Raum ausser KW 46\) )?\(([VPÜ])\)$/iu';
    private static $EVENT_NAME_PATTERN_COMPLEX = '/^([^(]+) (?:\([A-Za-zÀ-ÖØ-öø-ÿ ]+\).*\([VPÜ]\)|Gr.+)$/iu';
    private static $EVENT_NAME_PATTERN_SIMPLE = '/^(.+) \([VPÜ]\)$/iu';
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Gets the timetable page.
     *
     * @param string $weeks
     * @param string $days
     * @param string $semester
     *
     * @return string The page html
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getTimetablePage(string $weeks, string $days, string $semester): string
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

    /**
     * Gets the first table element out of html.
     *
     * @param string $html
     *
     * @return string
     */
    public function getTable(string $html): string
    {
        $dom = (new HTML5())->loadHTML($html);

        $domNodes = $dom->getElementsByTagName('table');
        $table = $domNodes->item(0);

        if ($table) {
            return $table->ownerDocument->saveHTML($table);
        }

        return '';
    }

    public function parseTableData(string $tableHTML): array
    {
        $dom = (new HTML5())->loadHTML($tableHTML);
        $rows = $dom->getElementsByTagName('tr');
        $currentDay = null;
        $data = [];

        foreach ($rows as $row) {
            $columns = $row->getElementsByTagName('td');
            $rowData = [
                'day' => null,
                'startTime' => null,
                'endTime' => null,
                'room' => null,
                'eventName' => null,
                'eventUrl' => null,
                'groupName' => null,
                'groupUrl' => null,
                'period' => null,
                'organizer' => '',
                'filterOrganizer' => null,
                'eventType' => null
            ];

            foreach ($columns as $column) {
                $class = $column->getAttribute('class');
                $value = $column->nodeValue;

                switch ($class) {
                    case 'liste-wochentag':
                        $currentDay = $value;
                        break;
                    case 'liste-startzeit':
                        $rowData['startTime'] = $value;
                        break;
                    case 'liste-endzeit':
                        $rowData['endTime'] = $value;
                        break;
                    case 'liste-raum':
                        $rowData['room'] = $value;
                        break;
                    case 'liste-veranstaltung':
                        if (preg_match(self::$EVENT_EXTRAS_PATTERN, $value, $eventExtras, PREG_UNMATCHED_AS_NULL)) {
                            if ($eventExtras[1] && ($eventExtras[1] === 'Priesnitz' || $eventExtras[1] === 'Berrendorf')) {
                                $rowData['filterOrganizer'] = $eventExtras[1];
                            }

                            $rowData['groupName'] = $eventExtras[2] ?? null;
                            $rowData['eventType'] = $eventExtras[3] ?? null;
                        }
                        if (preg_match(self::$EVENT_NAME_PATTERN_COMPLEX, $value, $eventNameComplex)) {
                            $rowData['eventName'] = $eventNameComplex[1];
                        }
                        else if (preg_match(self::$EVENT_NAME_PATTERN_SIMPLE, $value, $eventNameSimple)) {
                            $rowData['eventName'] = $eventNameSimple[1];
                        }
                        else {
                            $rowData['eventName'] = $value;
                        }
                        break;
                    case 'liste-beginn':
                        $rowData['period'] = $value;
                        break;
                    case 'liste-wer':
                        $rowData['organizer'] = $value;
                        break;
                }
            }

            if ($currentDay) {
                $rowData['day'] = $currentDay;
                $data[] = $rowData;
            }
        }

        return $data;
    }

    public function filterTableData(
        array &$tableData,
        string $groupNumber = null,
        string $groupLetter = null,
        string $organizer = null
    ) {
        foreach ($tableData as $key => $row) {
            if ($organizer && $row['filterOrganizer'] && $row['filterOrganizer'] !== $organizer) {
                unset($tableData[$key]);
            }

            $groupName = $row['groupName'] ?? null;

            if ($groupName && $groupName !== 'alle') {
                if (
                    (!$groupNumber || strpos($groupName, $groupNumber) === false) &&
                    (!$groupLetter || strpos($groupName, $groupLetter) === false)
                ) {
                    if (preg_match('/(\w)-(\w)/i', $groupName, $range)) {
                        if ($range[1] < $groupNumber && $groupNumber < $range[2]) {
                            continue;
                        }
                    }

                    unset($tableData[$key]);
                }
            }
        }
    }
}