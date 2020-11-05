# hbrs-info-timetable

Dies ist ein Werkzeug um einen individuellen Stundenplan zu erstellen.

[Webseite](https://hbrs-inf-stundenplan.herokuapp.com/)

## Vorschläge

Jegliche Vorschläge könnt ihr mir an meine Uni-Mail
`merlin.fejzuli@smail.inf.h-brs.de` schicken.
Bei Designvorschlägen bitte auch immer Bilder mit anhängen, da ich mit "rot sieht schicker aus" wenig anfangen kann.

## Mitwirken
Schön dich mit dabei zu haben. Dieses Projekt ist in PHP, Javascript, Twig, HTML und CSS geschrieben,
deshalb solltest du mit diesen Webtechnologien vertraut sein.

#### Eine kleine Einführung in das Projekt

Die Webseite wird auf dem Server gerendert, da wir zuerst den originalen Stundenplan herunterladen müssen um diesen zu verarbeiten.
Die Stundenplan wird dann aus dem HTML Dokument extrahiert und zu einem Array mit unseren Daten konvertiert.
Dieser Array wird dann gefiltert indem wir den Namen der Veranstaltung mit unseren Gruppen abgleichen.
Aus diesem Array wird dann im letzten Schritt die Tabelle gebaut und an den Client geschickt.

#### Wie geht mein code live?

- Forke dieses repo
- Mach deinen kram
- Erstelle eine Pull Request auf den Main branch
- Wenn die Pull Request approved und gemerged wurde wird die Webseite automatisch auf den neuen Stand aktualisiert

### Dokumentation

[Symfony](https://symfony.com/doc/current/index.html)
[Twig](https://twig.symfony.com/doc/2.x/)
[PHP](https://www.php.net/docs.php)
[Javascript](https://developer.mozilla.org/de/docs/Web/JavaScript/Reference)
