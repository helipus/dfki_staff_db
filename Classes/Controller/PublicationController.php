<?php
namespace DFKI\DfkiStaffDb\Controller;
use TYPO3\CMS\Extbase\Annotation as Extbase;

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
class PublicationController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    /**
     * publicationRepository
     *
     * @var \DFKI\DfkiStaffDb\Domain\Repository\PublicationRepository
     * @Extbase\Inject
     */
    protected $publicationRepository;

    /**
     * action list
     *
     * @return void
     */
    public function listAction()
    {
        $publications = $this->publicationRepository->getPublications();
        $this->view->assign('publications', $publications);

    }

    /**
     * action latest
     *
     * @return void
     */
    public function latestAction()
    {
        // Fetch limit from Flexform, if value is set
        $limit = -1; //DEFAULT-VALUE
        if(isset($this->settings['ff']['limit'])) {
            if((int) $this->settings['ff']['limit'] > 0) {
                $limit = (int) $this->settings['ff']['limit'];
            }
        }    
    
        $publications = $this->publicationRepository->getPublications($limit);
        $this->view->assign('publications', $publications);                   

    }


    /**
     * action show
     *
     * @param string $id
     * @return void
     */
    public function showAction($id)
    {

        $publication = $this->publicationRepository->getPublication($id);        
        $publicationFiles = $this->publicationRepository->getFilesForPublication($id);
        $publicationLinks = $this->publicationRepository->getLinksForPublication($id);        
        
        $this->view->assignMultiple(
            [
                'publication' => $publication,
                'publicationFiles' => $publicationFiles,
                'publicationLinks' => $publicationLinks
            ]
        );        
        
    }

}

?>