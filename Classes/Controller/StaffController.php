<?php
namespace DFKI\DfkiStaffDb\Controller;
use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Context\Context;

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
class StaffController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    /**
     * staffRepository
     *
     * @var \DFKI\DfkiStaffDb\Domain\Repository\StaffRepository
     * @Extbase\Inject
     */
    protected $staffRepository;

    /**
     * action list
     *
     * @return void
     */
    public function listAction()
    {
        $this->genericListAction();
    }

    /**
     * action list2
     *
     * @return void
     */
    public function list2Action()
    {
        $this->genericListAction();
    }

    /**
     * action thumbs
     *
     * @return void
     */
    public function thumbsAction()
    {
        $this->genericListAction();
    }

    /**
     * @param string $orgaunit
     * @return void
     */
    protected function genericListAction($orgaunit= '22')
    {
        if (isset($this->settings['ff']['orgaunit'])) {
          $orgaunit = $this->settings['ff']['orgaunit'];
        }

        $staffDirector = $this->staffRepository->getStaffDirector($orgaunit);
        $staffEmployees = $this->staffRepository->getStaffEmployees($orgaunit);
        $staffAssistants = $this->staffRepository->getStaffAssistants($orgaunit);
        $staffGuestResearchers = $this->staffRepository->getGuestResearchers($orgaunit);
        $staffInterns = $this->staffRepository->getInterns($orgaunit);
        $staffTrainees = $this->staffRepository->getTrainees($orgaunit);

        $this->view->assignMultiple(
            [
                'staffDirector' => $staffDirector,
                'staffEmployees' => $staffEmployees,
                'staffAssistants' => $staffAssistants,
                'staffGuestResearchers' => $staffGuestResearchers,
                'staffInterns' => $staffInterns,
                'staffTrainees' => $staffTrainees

            ]
        );

    }

    /**
     * action show
     *
     * @param string $uid
     * @return void
     */
    public function showAction($uid)
    {

        // Sprachumgebung einstellen:
        $lang = 'de';
        $languageAspect = GeneralUtility::makeInstance(Context::class)->getAspect('language');
        if ($languageAspect->getId()  == 1) {
            $lang = 'en';
        }

        $staff = $this->staffRepository->getSingleStaff($uid, $lang);

        //sicherstellen, dass nur zugelassene Mitarbeiterhomepages angezeigt werden:
        if (!preg_match(
            "/^(?:http:\/\/)?(?:www\.)?(?:dfki|robotik\.dfki-bremen|informatik\.uni-bremen|uni-bremen)\.de\//i",
            $staff['homepage'],
            $output_array)
        ) {
            $staff['homepage'] = '';
        }

        // Mitarbeiterseite nur anzeigen, wenn Mitarbeiter nicht "retired" ist:
        if (isset($staff['retired'])) {
            $this->view->assign('staff', $staff);

            $contactDetails = $this->staffRepository->getSingleStaffContactDetails($uid);
            $this->view->assign('contactDetails', $contactDetails);

            $publications = $this->staffRepository->getPublications($uid);
            $this->view->assign('publications', $publications);
        }

    }

}

?>
