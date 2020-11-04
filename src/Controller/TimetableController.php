<?php

namespace App\Controller;

use App\Service\TimetableService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
     *     "/timetable/{weeks}/{days}/{semester}",
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
     *
     * @return Response
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function timetable(string $weeks, string $days, string $semester): Response
    {
        $semester = '#' . $semester;

        $this->timetableService->getTimetableHtml($weeks, $days, $semester);

        return new Response(
            "<html lang='de'><body>$weeks  $days $semester</body></html>"
        );
    }
}
