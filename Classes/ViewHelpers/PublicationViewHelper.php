<?php
namespace DFKI\DfkiStaffDb\ViewHelpers;

    /*                                                                        *
     * This script is part of the TYPO3 project - inspiring people to share!  *
     *                                                                        *
     * TYPO3 is free software; you can redistribute it and/or modify it under *
     * the terms of the GNU General Public License version 2 as published by  *
     * the Free Software Foundation.                                          *
     *                                                                        *
     * This script is distributed in the hope that it will be useful, but     *
     * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
     * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
     * Public License for more details.                                       *
     *                                                                        */
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;



/**
 * A view helper for setting the document title in the <title> tag.
 *
 * = Examples =
 *
 * <page.title mode="prepend" glue=" - ">{blog.name}</page.title>
 *
 * <page.title mode="replace">Something here</page.title>
 *
 * <h1><page.title mode="append" glue=" | " display="render">Title</page.title></h1>
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @deprecated
 */
class PublicationViewHelper extends AbstractTagBasedViewHelper
{

    /** @var $cObj ContentObjectRenderer */
    protected $cObj;

    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerUniversalTagAttributes();
        $this->registerArgument('pub', 'array', 'Publications');
        $this->registerArgument('link', 'string', '"render" or "none", bei render wird ein show-action Link hinzugefügt.');
    }

    /**
     * Initialize properties
     *
     */
    protected function init()
    {
        $this->cObj = GeneralUtility::makeInstance(ContentObjectRenderer::class);
    }

    /**
     * @param array $pub Publications
     * @param string $link 'render' or 'none', bei render wird ein show-action Link hinzugefügt.
     * @author Veit Briken (veit.briken@dfki.de)
     * @return string
     * @throws \InvalidArgumentException
     */
    public function render()
    {
        $pub = $this->arguments['pub'] ?? NULL;
        $link = $this->arguments['link'] ?? 'render';

        $this->init();

        $result = '';

        $monthnames = [
            '1' => 'Jan',
            '2' => 'Feb',
            '3' => 'Mar',
            '4' => 'Apr',
            '5' => 'May',
            '6' => 'Jun',
            '7' => 'Jul',
            '8' => 'Aug',
            '9' => 'Sep',
            '10' => 'Oct',
            '11' => 'Nov',
            '12' => 'Dec'
        ];


        switch ($pub['bibtextype']) {
            /*
            case "inproceedings":
              return $result;
            break;
            */
            default:
                $result .= '<h5 class="title">' . $pub['title'] . '</h5>';
                $result .= $this->printVar('<div class="authors">', $pub['authors'], '</div>');  //Autoren
                $result .= $this->printVar('<div class="editors">Editors: ', $pub['editors'], '</div>');  //Autoren
                $result .= '<div class="additional-infos">';
                $result .= $this->printVar('In ', $pub['booktitle'], ', ');
                $result .= $this->printVar('In ', $pub['journal'], ', ');
                $result .= $this->printVar('In ', $pub['chaptertitle'], ', ');
                $result .= $this->printVar('(', $pub['conference_abb'], '), ');
                $result .= $this->printVar('', $pub['cdate'], '');
                $result .= $this->printVar('', $pub['cyear'], ', ');
                $result .= $this->printVar('', $pub['ccity'], ', ');
                $result .= $this->printVar('', $pub['cstate'], ', ');
                //$result .= $this->printVar("", $pub['country'], ". ");
                //$result .= $this->printVar("", getEditors($pub), ", ");  //Editoren
                $result .= $this->printVar('', $pub['publisher'], ', ');
                $result .= $this->printVar('', $pub['howpublished'], ', ');
                $result .= $this->printVar('', $pub['edition'], ', ');
                $result .= $this->printVar('series ', $pub['series'], ', ');
                $result .= $this->printVar('volume ', $pub['volume'], ', ');
                $result .= $this->printVar('number ', $pub['number'], ', ');
                $result .= $this->printVar('chapter ', $pub['chapter'], ', ');
                $result .= $this->printVar('pages ', $pub['pages'], ', ');
                $result .= $this->printVar('', $pub['address'], ', ');
                $result .= $this->printVar('', $monthnames[$pub['month']], '/');
                $result .= $this->printVar('', $pub['year'], '. ');
                $result .= $this->printVar('', $pub['organization'], '. ');
                $result .= $this->printVar('', $pub['institution'], '. ');
                $result .= $this->printVar('', $pub['school'], '. ');
                $result .= $this->printVar('ISBN: ', $pub['isbn'], '. ');
                $result .= '</div>';
                break;
        }

        if ($link === 'render') {

            $result = $this->cObj->typoLink(
                $result,
                [
                    'parameter' => '155',
                    'additionalParams' => '&tx_dfkistaffdb_dfkistaffplugin[id]=' . $pub['id'] . '&tx_dfkistaffdb_dfkistaffplugin[action]=show&tx_dfkistaffdb_dfkistaffplugin[controller]=Publication',
                    'useCacheHash' => 1
                ]
            );
        }
        return $result;
    }


    private function printVar($pre, $var, $post)
    {
        if (strlen($var) > 0) {
            return $pre . $var . $post;
        }

        return '';
    }



}

?>
