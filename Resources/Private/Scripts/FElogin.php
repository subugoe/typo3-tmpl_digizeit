<?php

/* **************************************************************
 *  Copyright notice
 *
 *  (c) 2010 Niedersächsische Staats- und Universitätsbibliothek
 *  (c) 2010 Jochen Kothe (kothe@sub.uni-goettingen.de) (jk@profi-php.de)
 *  All rights reserved
 *
 *  This script is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
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
 * ************************************************************* */

class user_FElogin {
	var $cObj;// The backReference to the mother cObj object set at call time

	/**
	 * @var \TYPO3\CMS\Fluid\View\StandaloneView
	 */
	protected $view;

	/**
	 * Call it from a USER cObject with 'userFunc = user_FElogin->main'
	 */
	function main($content, $conf) {
		$this->createTemplate();
		$arrGP = array_merge($GLOBALS['_GET'], $GLOBALS['_POST']);
		$this->view->assign('storagePid', $conf['storagePid']);

		//kein Frontenduser angemeldet
		if (!$GLOBALS['TSFE']->fe_user->user || $arrGP['logintype'] == 'logout') {
			$this->view->assign('loginType', 'login');
		} else {
			if (strpos(strtolower(\TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL')), 'id=139') || strpos(strtolower(\TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL')), 'mydigizeit')) {
				$action = 'https://www.digizeitschriften.de/';
			} else {
				$action = str_replace('http://', 'https://', strtolower(\TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL')));
			}
			$this->view->assign('loginType', 'logout');
			$this->view->assign('loggedIn', $GLOBALS['TSFE']->fe_user->user['name']);
			$this->view->assign('action', $action);
			$this->view->assign('myDigiZeit', 139);
		}

		return $this->view->render();
	}

	protected function createTemplate() {
		/** @var \TYPO3\CMS\Fluid\View\StandaloneView template */
		$this->view = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
		$this->view->setFormat('html');
		$templateRootPath = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('tmpl_digizeit') . 'Resources/Private/Templates/');
		$templatePathAndFilename = $templateRootPath . 'Fe.html';
		$this->view->setTemplatePathAndFilename($templatePathAndFilename);
	}

}

?>
