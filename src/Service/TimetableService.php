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

    public function getDataFromTable(string $tableHTML): array
    {
        $dom = (new HTML5())->loadHTML($tableHTML);
        $rows = $dom->getElementsByTagName('tr');
        $data = [];
        $currentDay = null;

        foreach ($rows as $row) {
            $columns = $row->getElementsBytagName('td');
            $rowData = [];

            foreach ($columns as $column) {
                $class = $column->getAttribute('class');

                switch ($class) {
                    case 'liste-wochentag':
                        $currentDay = $column->nodeValue;
                        break;
                    case 'liste-startzeit':
                        $rowData['startTime'] = $column->nodeValue;
                        break;
                    case 'liste-endzeit':
                        $rowData['endTime'] = $column->nodeValue;
                        break;
                    case 'liste-raum':
                        $rowData['room'] = $column->nodeValue;
                        break;
                    case 'liste-veranstaltung':
                        $rowData['event'] = $column->nodeValue;
                        break;
                    case 'liste-beginn':
                        $rowData['period'] = $column->nodeValue;
                        break;
                    case 'liste-wer':
                        $rowData['organizer'] = $column->nodeValue;
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

    public function filterTableData(array &$tableData, string $groupNumber = null, string $groupLetter = null): array
    {
        foreach ($tableData as $key => $row) {
            $event = $row['event'];

            if (preg_match('/Gr\.? ?((?:\w[+])+\w|\w-\w|\w) /', $event, $groupMatches)) {
                $groupValue = $groupMatches[1];

                if (
                    ($groupNumber && strpos($groupValue, $groupNumber) !== false) ||
                    ($groupLetter && stripos($groupValue, $groupLetter) !== false)
                ) {
                    continue;
                }

                if (preg_match('/(\w)-(\w)/i', $groupValue, $rangeMatches)) {
                    if ($rangeMatches[1] < $groupNumber && $groupNumber < $rangeMatches[2]) {
                        continue;
                    }
                }

                unset($tableData[$key]);
            }
        }

        return $tableData;
    }
}