<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class HomeController extends AbstractController
{
    private static $SEMESTERS = [
        [ 'value' => '#SPLUS1428E2', 'name' => 'BI 1' ],
        [ 'value' => '#SPLUSF85D4A', 'name' => 'BWI 1' ]
    ];
    private static $GROUP_NUMBERS = [ '', 1, 2, 3, 4, 5, 6, 7, 8 ];
    private static $GROUP_LETTERS = [ '', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M' ];
    private static $ORGANIZERS = [ '', 'Berrendorf', 'Priesnitz' ];

    /**
     * @Route("/", name="home")
     */
    public function getHome(): Response
    {
        return $this->render('home/home.html.twig', [
            'semesters' => self::$SEMESTERS,
            'groupNumbers' => self::$GROUP_NUMBERS,
            'groupLetters' => self::$GROUP_LETTERS,
            'organizers' => self::$ORGANIZERS
        ]);
    }
}