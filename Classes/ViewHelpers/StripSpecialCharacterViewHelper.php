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
     * AUTHOR Veit Briken on 01.04.2019                                       *                                 
     *                                                                        */

     
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A view helper for changing the file names of the publications according to the method used from the dfki.de Importer Script
 * which can be found here: \dfki_importer\src\Entity\SysFile.php
 * in Function:  public static function stripWhitespaces($file)
 *
 */
class StripSpecialCharacterViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper
{
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('file', 'string', 'Filename mit Sonderzeichen');
    }
    /**
     * @param string $file Filename mit Sonderzeichen
     * @author Veit Briken (veit.briken@dfki.de)
     *
     * @return string
     */
    public function render()
    {
        $file = $this->arguments['file'] ?? '';

        return str_replace([' ','&','?','#'], ['_','_','_',''], $file);
    }
}

?>
