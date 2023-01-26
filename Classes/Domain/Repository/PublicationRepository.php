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

class PublicationRepository extends AbstractRepository
{


    /**
     * getPublication
     *
     * @param string $id Publikationsid
     * @return array
     */
    Public Function getPublication($id = NULL)
    {
        $sql = '
      SELECT p.*, a.id AS authorid, pl.name AS file_name, pl.file_id AS file_id, pl.permissions
      FROM publications p
      JOIN authors a ON p.id = a.pubid
      LEFT JOIN publicationlinks pl ON p.id = pl.pubid
      WHERE
        p.id LIKE "' . $id . '"  AND
        p.approved = 1
    ';

        if ($result = $this->db->query($sql)) {

            while ($row = $result->fetch_assoc()) {
                $row['authors'] = $this->getAuthors($id, $row['externalauthors']);
                $row['editors'] = $this->getEditors($id, $row['externaleditors']);
                $array[] = $row;
            }
            return $array[0];
        }

        return null;
    }
    
    
    /**
     * getFilesForPublication
     *
     * @param string $id Publikationsid
     * @return array
     */
    Public Function getFilesForPublication($id = NULL)
    {
      $sql = '
        SELECT pl.*
        FROM publicationlinks pl            
        WHERE
          pl.pubid LIKE "' . $id . '"  AND
          pl.permissions = "all" AND
          pl.intern = 1
        ';

        if ($result = $this->db->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $array[] = $row;
            }
            return $array;
        }
        return null;
    }
    
    /**
     * getLinksForPublication
     *
     * @param string $id Publikationsid
     * @return array
     */
    Public Function getLinksForPublication($id = NULL)
    {
      $sql = '
        SELECT pl.*
        FROM publicationlinks pl            
        WHERE
          pl.pubid LIKE "' . $id . '"  AND
          pl.permissions = "all" AND
          pl.intern = 0
        ';

        if ($result = $this->db->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $array[] = $row;
            }
            return $array;
        }
        return null;
    }    
    
    

    /**
     * getPublications
     *
     * @param null $limit
     * @return array
     */
    Public Function getPublications($limit = NULL)
    {
        $sql = '
      SELECT DISTINCT p.*
      FROM  publications p
      INNER JOIN authors a ON p.id = a.pubid
      WHERE
        a.orgaunit IN ("22") AND
        p.approved = 1
      ORDER BY
        p.year DESC,
        p.month DESC
    ';

    if ($limit > 0 ) {
        $sql .= ' LIMIT '.$limit. ' ';
    }

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


    /**
     * getPublications for project
     *
     * @param string $mydfkiProjectId ProjektID in myDFKI Datenbank
     * @return array
     */
    Public Function getProjectPublications($mydfkiProjectId)
    {
        $sql = '
      SELECT DISTINCT p.*
      FROM  publications p
      INNER JOIN authors a ON p.id = a.pubid
      INNER JOIN projectpublications pp ON p.id = pp.publication
      WHERE
        a.orgaunit IN ("22") AND 
        pp.project LIKE "'.$mydfkiProjectId.'" AND
        p.approved = 1 
      ORDER BY
        p.year DESC,
        p.month DESC
    ';

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
        if ($externalauthorsDB != '') {
            $authors = explode('|', $externalauthorsDB);
            foreach ($authors as $key => $val) {
                $authors[$key] = explode(':', $val);
            }
        }

        //DFKI Autoren ermitteln und real namen holen:
        $sql = '
      SELECT DISTINCT s.pub_name, s.pub_firstname, a.pos, a.name AS staff_name
      FROM authors a
      INNER JOIN staff s ON a.uid = s.uid
      WHERE
        a.pubid = ' . $pubid . ' AND a.iseditor = 0
      ORDER BY a.pos ASC
    ';

        if ($result = $this->db->query($sql)) {

            while ($row2 = $result->fetch_assoc()) {
                if ($row2['staff_name'] != '') {
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
        if ($externaleditorsDB != '') {
            $editors = explode('|', $externaleditorsDB);
            foreach ($editors as $key => $val) {
                $editors[$key] = explode(':', $val);
            }
        }

        //DFKI Autoren ermitteln und real namen holen:
        $sql = '
      SELECT DISTINCT s.pub_name, s.pub_firstname, a.pos, a.name AS staff_name
      FROM authors a
      INNEr JOIN staff s ON a.uid = s.uid
      WHERE
        a.pubid = ' . $pubid . ' AND a.iseditor = 1
        ORDER BY a.pos ASC
    ';

        if ($result = $this->db->query($sql)) {
            while ($row2 = $result->fetch_assoc()) {
                if ($row2['staff_name'] != '') {
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
}

?>
