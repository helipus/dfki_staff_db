<?php
namespace DFKI\DfkiStaffDb\Domain\Repository;

    /***************************************************************
     *  Copyright notice
     *
     *  (c) 2012 Dipl.-Inf. Veit Briken <veit.briken@dfki.de>, DFKI GmbH
     *
     *  All rights reserved
     *
     *  This script is part of the TYPO3 project. The TYPO3 project is
     *  free software; you can redistribute it and/or modify
     *  it under the terms of the GNU General Public License as published by
     *  the Free Software Foundation; either version 3 of the License, or
     *  (at your option) any later version.
     *
     *  The GNU General Public License can be found at
     *  http://www.gnu.org/copyleft/gpl.html.
     *
     *  This script is distributed in the hope that it will be useful,
     *  but WITHOUT ANY WARRANTY; without even the implied warranty of
     *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     *  GNU General Public License for more details.
     *
     *  This copyright notice MUST APPEAR in all copies of the script!
     ***************************************************************/

/**
 *
 *
 * @package dfki_staff_db
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */

class StaffRepository extends AbstractRepository
{
    /**
     * getPublications
     *
     * @param string $uid Mitarbeiterkürzel
     * @return array
     */
    Public Function getPublications($uid = NULL)
    {
        $sql = '
            SELECT p.*, a.id AS authorid
            FROM publications p
            INNER JOIN authors a ON p.id = a.pubid
            WHERE
            a.uid LIKE "' . $uid . '"  AND
            p.approved = 1
            GROUP BY a.pubid
            ORDER BY p.year DESC, p.month DESC
        ';

        $array = array();
        if ($result = $this->db->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $row['authors'] = $this->getAuthors($row['id'], $row['externalauthors']);
                $row['editors'] = $this->getEditors($row['id'], $row['externaleditors']);
                $array[] = $row;
            }

            return $array;
        }

        return null;

    }


    Private Function getAuthors($pubid, $externalauthorsDB)
    {
        $authors = [];
        $authors_names = '';

        //Externe Autoren String in ein Multiarray zerlegen [[[Name][Position]][[Name][Position]]...]
        if ($externalauthorsDB !== '') {
            $authors = explode('|', $externalauthorsDB);
            foreach ($authors as $key => $val) {
                $authors[$key] = explode(':', $val);
            }
        }

        //DFKI Autoren ermitteln und real namen holen:
        $sql = '
            SELECT
            DISTINCT s.pub_name, s.pub_firstname, a.pos, a.name AS staff_name
            FROM authors a
            JOIN staff s ON a.uid = s.uid
            WHERE
              a.pubid = ' . $pubid . ' AND a.iseditor = 0
            ORDER BY a.pos ASC
        ';


        if ($result = $this->db->query($sql)) {

            while ($row2 = $result->fetch_assoc()) {
                if ($row2['staff_name'] !== '') {
                    $author_name = $row2['staff_name'];
                } else {
                    $author_name = $row2['pub_firstname'] . ' ' . $row2['pub_name'];
                }

                $authors[] = [$author_name, $row2['pos']];
            }

            if (!empty($authors)) {
                // Obtain a list of columns
                foreach ($authors as $key => $data) {
                    $author[$key] = $data[0];
                    $pos[$key] = $data[1];
                }
                // Sort the data with volume descending, edition ascending
                // Add $data as the last parameter, to sort by the common key
                if (!empty($author)) {
                    array_multisort($pos, SORT_NUMERIC, $author, SORT_ASC, $authors);
                }

                // Namen aus dem Multi-Array in einen geordneten String kopieren:
                $authors_names = array_map(function ($item) {
                    return $item[0];
                }, $authors);
                $authors_names = implode(', ', $authors_names);
            }

            return $authors_names;
        }

        return '';
    }

    Private Function getEditors($pubid, $externaleditorsDB)
    {
        $editors = [];
        $editors_names = '';

        //Externe Autoren String in ein Multiarray zerlegen [[[Name][Position]][[Name][Position]]...]
        if ($externaleditorsDB !== '') {
            $editors = explode('|', $externaleditorsDB);
            foreach ($editors as $key => $val) {
                $editors[$key] = explode(':', $val);
            }
        }

        //DFKI Autoren ermitteln und real namen holen:
        $sql = '
            SELECT
            DISTINCT s.pub_name, s.pub_firstname, a.pos, a.name AS staff_name
            FROM authors a
            JOIN staff s ON a.uid = s.uid
            WHERE
              a.pubid = ' . $pubid . ' AND a.iseditor = 1
              ORDER BY a.pos ASC
        ';

        if ($result = $this->db->query($sql)) {

            while ($row2 = $result->fetch_assoc()) {
                if ($row2['staff_name'] !== '') {
                    $editor_name = $row2['staff_name'];
                } else {
                    $editor_name = $row2['pub_firstname'] . ' ' . $row2['pub_name'];
                }

                $editors[] = [$editor_name, $row2['pos']];
            }

            if (!empty($editors)) {
                // Obtain a list of columns
                foreach ($editors as $key => $data) {
                    $editor[$key] = $data[0];
                    $pos[$key] = $data[1];
                }
                // Sort the data with volume descending, edition ascending
                // Add $data as the last parameter, to sort by the common key
                if (!empty($editor)) {
                    array_multisort($pos, SORT_NUMERIC, $editor, SORT_ASC, $editors);
                }

                // Namen aus dem Multi-Array in einen geordneten String kopieren:
                $editors_names = array_map(function ($item) {
                    return $item[0];
                }, $editors);
                $editors_names = implode(', ', $editors_names);
            }

            return $editors_names;
        }

        return '';
    }

/*  dictionary
    language	category	keyname	value	description	description2
language	category	keyname	value	description	description2
de	employeeType	2	Gastwissenschaftler / Sonstige	 	Gast
de	employeeType	3	Leitung	DFKI	Leitung
de	employeeType	4	Hilfswissenschaftler	DFKI	Hilfswissenschaftler
de	employeeType	5	Sekretariat / Assistenz	DFKI	Sekretariat / Assistenz
de	employeeType	6	Mitarbeiter	DFKI	Mitarbeiter
de	employeeType	7	Aushilfe	DFKI	Aushilfe
de	employeeType	8	Mitarbeiter (Uni)	Universität	Mitarbeiter
de	employeeType	9	Hilfswissenschaftler (Uni)	Universität	Hilfswissenschaftler
de	employeeType	10	Aushilfe (Uni)	Universität	Aushilfe
de	employeeType	11	Auszubildender	DFKI	Auszubildender
de	employeeType	12	Auszubildender (Uni)	Universität	Auszubildender
de	employeeType	13	Freier Student (unbezahlt)	 	Freier Student
de	employeeType	14	Praktikant	DFKI	Praktikant
de	employeeType	15	Praktikant (Uni)	Universität	Praktikant
de	employeeType	16	Externer (sonstige)	 	Externer
de	employeeType	17	Sekretariat / Assistenz (Uni)	Universität	Sekretariat / Assistenz
de	employeeType	18	Praktikant (unbezahlt)	 	Praktikant
de	employeeType	19	Mitarbeiter (DFKI & Uni)	DFKI & Universität	Mitarbeiter
de	employeeType	20	Mitarbeiter (DFKI Aufstockung)	DFKI & Universität	Mitarbeiter
de	employeeType	21	Sekretariat / Assistenz (DFKI Aufstockung)	DFKI & Universität	Sekretariat / Assistenz
de	employeeType	22	Sekretariat / Assistenz (DFKI & Uni)	DFKI & Universität	Sekretariat / Assistenz
de	employeeType	23	Leitung (DFKI & Uni)	DFKI & Universität	Leitung
12 A&D
22 RIC
34 ISG
54 A&D HB
*/



    /**
     * getStaffEmployees
     *
     * @param string $orgaunit
     * @return array
     */
    Public Function getStaffEmployees($orgaunit)
    {
        $sql = '
          SELECT * FROM staff s
          LEFT OUTER JOIN orgaunitmembers oum on s.uid = oum.member
          INNER JOIN contactinfo c ON s.uid = c.uid
          WHERE
            oum.orgaunit IN ('.$orgaunit.') AND
            s.retired = 0 AND
            s.dfkiweb = 1 AND
            s.employeeTypeReal IN (3,5,6,8,17,19,20,21,22,23) AND 
            s.uid NOT LIKE "frki01"
          GROUP BY oum.member, oum.id, c.location
          ORDER BY s.name, s.firstname
        ';
        $array = array();
        if ($result = $this->db->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $array[] = $row;
            }

            if ( !empty($array) ) {
                $array = $this->checkStaffExceptions($array);
            }
            return $array;
        }

        return null;
    }

    /**
     * getStaffAssistants
     *
     * @param string $orgaunit
     * @return array
     */
    Public Function getStaffAssistants($orgaunit)
    {
        $sql = '
            SELECT * FROM staff s
            LEFT OUTER JOIN orgaunitmembers oum ON s.uid = oum.member
            JOIN contactinfo c ON s.uid = c.uid
            WHERE
                oum.orgaunit IN ('.$orgaunit.') AND
                s.retired = 0 AND
                s.dfkiweb = 1 AND
                s.employeeTypeReal IN (4,7,9,10)
            GROUP BY oum.member, oum.id, c.location
            ORDER BY s.name, s.firstname
        ';

        if ($result = $this->db->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $array[] = $row;
            }
            if ( !empty($array) ) {
                $array = $this->checkStaffExceptions($array);
            }
            return $array;
        }

        return null;
    }


     /**
     * getGuestResearchers
     *
     * @param string $orgaunit
     * @return array
     */
    Public Function getGuestResearchers($orgaunit)
    {
        $sql = '
            SELECT * FROM staff s
            LEFT OUTER JOIN orgaunitmembers oum ON s.uid = oum.member
            JOIN contactinfo c ON s.uid = c.uid
            WHERE
                oum.orgaunit IN ('.$orgaunit.') AND
                s.retired = 0 AND
                s.dfkiweb = 1 AND
                s.employeeTypeReal IN (2)
            GROUP BY oum.member, oum.id, c.location
            ORDER BY s.name, s.firstname
        ';

        if ($result = $this->db->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $array[] = $row;
            }

            if ( !empty($array) ) {
                $array = $this->checkStaffExceptions($array);
            }
            return $array;
        }

        return null;
    }



     /**
     * getInterns
     *
     * @param string $orgaunit
     * @return array
     */
    Public Function getInterns($orgaunit)
    {
        $sql = '
            SELECT * FROM staff s
            LEFT OUTER JOIN orgaunitmembers oum ON s.uid = oum.member
            JOIN contactinfo c ON s.uid = c.uid
            WHERE
                oum.orgaunit IN ('.$orgaunit.') AND
                s.retired = 0 AND
                s.dfkiweb = 1 AND
                s.employeeTypeReal IN (14,15,18)
            GROUP BY oum.member, oum.id, c.location 
            ORDER BY s.name, s.firstname
        ';

        if ($result = $this->db->query($sql)){
            while ($row = $result->fetch_assoc()) {
                $array[] = $row;
            }

            if ( !empty($array) ) {
                $array = $this->checkStaffExceptions($array);
            }
            return $array;
        }

        return null;
    }


     /**
     * getTrainees
     *
     * @param string $orgaunit
     * @return array
     */
    Public Function getTrainees($orgaunit)
    {
        $sql = '
            SELECT * FROM staff s
            LEFT OUTER JOIN orgaunitmembers oum ON s.uid = oum.member
            JOIN contactinfo c ON s.uid = c.uid
            WHERE
                oum.orgaunit IN ('.$orgaunit.') AND
                s.retired = 0 AND
                s.dfkiweb = 1 AND
                s.employeeTypeReal IN (11,12)
            GROUP BY oum.member, oum.id, c.location 
            ORDER BY s.name, s.firstname
        ';

        if ($result = $this->db->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $array[] = $row;
            }

            if ( !empty($array) ) {
                $array = $this->checkStaffExceptions($array);
            }
            return $array;
        }
        return null;
    }



    /**
     * getStaffDirector
     *
     * @param string $orgaunit
     * @return array
     */
    Public Function getStaffDirector($orgaunit)
    {
        /*
        $sql = '
          SELECT * FROM staff s
          LEFT OUTER JOIN orgaunitmembers oum on s.uid = oum.member
          INNER JOIN contactinfo c ON s.uid = c.uid
          WHERE
            oum.orgaunit IN ('.$orgaunit.') AND
            s.retired = 0 AND
            s.dfkiweb = 1 AND
            s.employeeTypeReal IN (23)
          GROUP BY oum.member
          ORDER BY s.name DESC, s.firstname
        ';
        */

        $sql = '
          SELECT * FROM staff s
          INNER JOIN contactinfo c ON s.uid = c.uid
          WHERE s.uid LIKE "frki01" AND s.retired = 0 and s.dfkiweb = 1
          ORDER BY s.name
        ';

        $result = $this->db->query($sql);

        while ($row = $result->fetch_assoc()) {
            $array[] = $row;
        }

        if ( !empty($array) ) {
            $array = $this->checkStaffExceptions($array);
        }
        return $array;
    }


    /**
     * getSingleStaff
     *
     * @param string $uid Mitarbeiterkürzel
     * @param string $lang Webseitensprache
     * @return array
     */
    Public Function getSingleStaff($uid = NULL, $lang = 'de')
    {
        $array = $array2 = [];

        $sql = '
            SELECT
                s.uid, s.name, s.firstname, s.pub_name, s.pub_firstname, s.title, s.employeeType, s.employeeTypeReal,
                s.employeeNumber, s.location, s.ou, s.mail, s.universityMail, s.skype, s.mobile, s.homepage, s.dfkiweb,
                s.retired, s.title_name_long,
                c.building, c.room, c.phone, c.fax,                
                d.description2,
                o.name AS orgaunitname 
                FROM staff s
                INNER JOIN contactinfo c ON s.uid = c.uid
                INNER JOIN dictionary d ON s.employeeTypeReal= d.keyname AND d.category = "employeeType" AND d.language = "' . $lang . '"
                LEFT OUTER JOIN orgaunits o ON s.uid= o.head AND d.language = "' . $lang . '"
                WHERE
                    s.uid = "' . $uid . '"  AND
                    s.retired = 0 AND s.dfkiweb = 1
        ';

        $result = $this->db->query($sql);
        while ($row = $result->fetch_assoc()) {
            $array[] = $row;
        }

        //Dazugehörige Orgaunits in einem Feld zusammen fassen:
        $sql2 = '
            SELECT name
            FROM orgaunits o
            JOIN orgaunitmembers om ON o.id = om.orgaunit
            WHERE
              om.member = "' . $array[0]['uid'] . '" AND
              o.language LIKE "' . $lang . '"
        ';

        $result2 = $this->db->query($sql2);

        while ($row = $result2->fetch_assoc()) {
            $array2[] = $row['name'];
        }

        if(is_array($array2) && count($array2) > 0) {
            sort($array2);
        }

        $array[0]['orgaunits'] = $array2;

        $array = $this->checkStaffExceptions($array);

        return $array[0];
    }

    /**
     * getSingleStaff
     *
     * @param string $uid Mitarbeiterkürzel
     * @return array
     */
    Public Function getSingleStaffContactDetails($uid = NULL)
    {
        $array = $array2 = [];

        $sql = '
            SELECT *  
                FROM staff s
                WHERE
                    s.uid = "' . $uid . '"  AND
                    s.retired = 0 AND s.dfkiweb = 1
                LIMIT 1
        ';

        $result = $this->db->query($sql);
        while ($row = $result->fetch_assoc()) {
            $array[] = $row;
        }
        $locationsArray = explode(', ',$array[0]['location']);
        $locations = implode('","',$locationsArray);
        $sql2 = '
            SELECT *
            FROM contactinfo c
            WHERE
              c.uid = "' . $uid . '"  AND
              c.location IN ("'.$locations.'")
            ';
        $result2 = $this->db->query($sql2);
        while ($row2 = $result2->fetch_assoc()) {
            if ($row2['location'] == 'B') {
                $row2['location'] = 'Berlin';
            }
            $contactDetails[] = $row2;
        }

        return $contactDetails;
    }

    /**
     * checkStaffExceptions  Prüft die Ausnahmen bei speziellen Mitarbeitern
     *
     * @param array $array
     * @return array
     */
    Private Function checkStaffExceptions(array $array)
    {

        $shortDFKI = 'DFKI';
        $longDFKI = 'DFKI GmbH';

        $shortUNI = 'Uni';
        $longUNI = 'Universität Bremen';

        $shortUNIOS = 'Uni';
        $longUNIOS = 'Universität Osnabrück';

        $shortDFKIUNI = 'DFKI&nbsp;&amp; Uni';
        $longDFKIUNI = 'DFKI GmbH&nbsp;&amp; Universität Bremen';

        $shortDFKIUNIOS = 'DFKI&nbsp;&amp; Uni';
        $longDFKIUNIOS = 'DFKI GmbH&nbsp;&amp; Universität Osnabrück';

        $extern = 'Extern';

        $records2Delete = [];
        foreach ($array as $key => &$staff) {  //Das &staff ist eine direkte Referenz auf die Array-Elemente und ermöglicht die direkte Manipulation.

            if (in_array($staff['employeeTypeReal'], array('8', '9', '10', '12', '15', '17'), true) ) {
                $staff['organisation'] = $shortUNI;
                $staff['organisationLong'] = $longUNI;
                if ($staff['ou']===59){  //PBR OS
                    $staff['organisation'] = $shortUNIOS;
                    $staff['organisationLong'] = $longUNIOS;
                }
            }
            elseif (in_array($staff['employeeTypeReal'], array('19', '20', '21', '22', '23'), true) ) {
                $staff['organisation'] = $shortDFKIUNI;
                $staff['organisationLong'] = $longDFKIUNI;
                if ($staff['ou']===59){  //PBR OS
                    $staff['organisation'] = $shortDFKIUNIOS;
                    $staff['organisationLong'] = $longDFKIUNIOS;
                }
            }
            else {
                $staff['organisation'] = $shortDFKI;
                $staff['organisationLong'] = $longDFKI;
            }

            if ($staff['employeeTypeReal'] === '18') {   // unbezahlte Praktikanten sind immer Organisation DFKI, sollte es andere Fälle geben, so muss eine neuer real Type eingeführt werden
                $staff['organisation'] = $shortDFKI;
                $staff['organisationLong'] = $longDFKI;
            }

            if ($staff['employeeTypeReal'] === '2') {   // unbezahlte Gastwissenschaftler sind immer Organisation DFKI, sollte es andere Fälle geben, so muss eine neuer real Type eingeführt werden
                $staff['organisation'] = $shortDFKI;
                $staff['organisationLong'] = $longDFKI;
            }

            if ($staff['employeeTypeReal'] === '16') {   // Externer (sonstige)
                $staff['organisation'] = $extern;
                $staff['organisationLong'] = $extern;
            }



            // Der 'Leiter'-Prefix beim Status wird nun nur ergänzt, wenn employeeType Leiter entspricht und eine orgaunitname gesetzt ist
            // Marc Ronthaler und Jens Mey erhalten unten eine gesonderte Def. deren Ausgabe so möglich wird.
            if ( ($staff['employeeType'] == 3 ) && (strlen($staff['orgaunitname']) > 0)) {
                $staff['orgaunitnameLeaderPrefix'] = 1;
            }

            // Doppelte Orgaunits löschen. Bsp. 'Administration & Dienste HB' ersetzt 'Administration & Dienste':
            if (isset($staff['orgaunits'])) {
            /*
                if (FALSE !== ($index = array_search('Administration & Dienste', $staff['orgaunits'], true))) {
                    unset($staff['orgaunits'][$index]);
                }
            */
                if (FALSE !== ($index = array_search('Betriebsrat', $staff['orgaunits'], true))) {
                    unset($staff['orgaunits'][$index]);
                }
            }
            //ergänzt am 22.05.2017 auf Wunsch von Frank nach Umstrukturierung Administration
            // A&D Mitarbeiter in Übersicht darstellen:
            if (!in_array($staff['employeeTypeReal'],
                array('8', '9', '10', '12', '15', '17', '19', '20', '21', '22', '23'), true)) {
                if ($staff['ou'] == 12) {
                    $staff['bereich'] = 'A&amp;D';
                }
                if ($staff['ou'] == 22) {
                    $staff['bereich'] = 'RIC';
                }
                if ($staff['ou'] == 34) {
                    $staff['bereich'] = 'ISG';
                }
                if ($staff['ou'] == 53) {
                    $staff['bereich'] = 'UK';
                }
                if ($staff['ou'] == 59) {
                    $staff['bereich'] = 'PBR';
                }
            } else {
                //AG Rabotik
                $staff['bereich'] = 'RIC';
            }

            // HIER WERDEN AUSNAHMEN DEFINIERT!!!
            switch ($staff['uid']) {
                case 'frki01' :
                    $staff['orgaunits'][] = 'Arbeitsgruppe Robotik';
                    $staff['orgaunitname'] = 'Geschäftsführender Direktor<br />Leitung Robotics Innovation Center<br />Leitung Arbeitsgruppe Robotik<br />Scientific Director of Brazilian Institute of Robotics';
                    $staff['bereich'] = 'RIC';
                    if (FALSE !== ($index = array_search('Robotic Exploration Laboratory', $staff['orgaunits'], true))) {
                        unset($staff['orgaunits'][$index]);
                    }
                    break;
                case 'sist01' :
                    $staff['employeeType'] = 3;   //Damit der Status
                    $staff['orgaunitname'] = 'special.1'; //'Stellv. Leiter Robotics Innovation Center';
                    break;
                case 'jeme01' :
                    $staff['employeeType'] = 3;   //Damit der Status
                    $staff['orgaunitname'] = 'special.1'; //'Stellv. Leiter Robotics Innovation Center';
                    break;
                case 'mawe04' :
                    $records2Delete[] = $key;
                    break;
/*                case 'thvo01' :
                    if ($staff['location'] == 'HB') {
                        $records2Delete[] = $key;
                    }
                    break;
*/
                case 'stst02' :  //Stefan Stiene
                    if ($staff['location'] == 'HB') {
                        $records2Delete[] = $key;
                    }
                    break;
                case 'safo01' :     //Focke Martínez, Santiago 
                    if ($staff['location'] == 'HB') {
                        $records2Delete[] = $key;
                    }
                    break;                                                         
            }


            // Bei Uni Mitarbeiter die Uni Mailadresse anzeigen:
            if ($staff['organisation'] == $shortUNI && $staff['universityMail'] != '') {
                $staff['mail'] = $staff['universityMail'];
            }


        }
        unset($staff); // Die Hilfsreferenz löschen

        //Array Löschbefehle durchführen:
        foreach ($records2Delete as $record) {
            unset($array[$record]);
        }

        return $array;
    }


}

?>
