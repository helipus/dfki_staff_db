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
class FileExistsViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('file', 'file', 'zu prüfende Datei');
    }

    /**
     * @param file $file zur prüfende Datei
     * @author Veit Briken (veit.briken@dfki.de)
     *
     * @return int
     */
    public function render()
    {
        $file = $this->arguments['file'];
        if (!$file || substr($file, -1) == '/') {
            return 0;
        }
        $filePath = GeneralUtility::getFileAbsFileName($file);
        if (file_exists($filePath) && filesize($filePath) > 0) {
            return 1;
        }
        return 0;
    }
}

?>
