<?php

namespace App\Controller;

use App\Service\TimetableService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class TimetableController extends AbstractController
{
    private $timetableService;

    public function __construct(TimetableService $timetableService)
    {
        $this->timetableService = $timetableService;
    }

    /**
     * @Route(
     *     "/timetable/{weeks}/{days}/{semester}/{group}",
     *     requirements={
     *         "weeks"="(\d{1,2};)*\d{1,2}",
     *         "days"="[1-7]-[1-7]",
     *         "semester"="\w+"
     *     },
     *     name="timetable"
     * )
     * @param string $weeks
     * @param string $days
     * @param string $semester
     * @param string|null $group
     *
     * @return Response
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function timetable(string $weeks, string $days, string $semester, string $group = null): Response
    {
        $semester = '#' . $semester;
        $groupNumber = null;
        $groupLetter = null;

        if ($group) {
            if (preg_match('/^(\d)?([A-Z])?$/i', $group, $matches)) {
                $groupNumber = $matches[1] ?? null;
                $groupLetter = $matches[2] ?? null;
            }
        }

        $timeTablePage = $this->timetableService->getTimetablePage($weeks, $days, $semester);
        $table = $this->timetableService->getTable($timeTablePage);
        $tableData = $this->timetableService->getDataFromTable($table);
        $this->timetableService->filterTableData($tableData, $groupNumber, $groupLetter);

        return $this->render('timetable/timetable.html.twig', [
            'tableData' => $tableData
        ]);
    }
}
