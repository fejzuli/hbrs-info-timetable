<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function getHome(): Response
    {
        $semesters = [
            [ 'value' => '#SPLUS1428E2', 'name' => 'BI 1' ],
            [ 'value' => '#SPLUSF85D4A', 'name' => 'BWI 1' ]
        ];
        $groupNumbers = [ '', 1, 2, 3, 4, 5, 6, 7, 8 ];
        $groupLetters = [ '', 'A', 'B', 'C', 'D', 'E', 'F', 'G'];

        return $this->render('home/home.html.twig', [
            'semesters' => $semesters,
            'groupNumbers' => $groupNumbers,
            'groupLetters' => $groupLetters
        ]);
    }
}