<?php
namespace Subugoe\TmplDigizeit\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;

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

class IpCalculatorUtility {
	protected $broadcast = '0.0.0.0';
	protected $netaddress = '';
	protected $netmask = '';
	protected $strErrMsg = '';

	/**
	 * @var array
	 */
	protected $POST;

	/**
	 * @var string
	 */
	protected $content;

	public function main() {

		$this->POST = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST();

		$ipStart = array_key_exists('ipstart', $this->POST) ? $this->POST['ipstart'] : '';
		$ipEnd = array_key_exists('ipend', $this->POST) ? $this->POST['ipend'] : '';

		$this->content .= '<table>';
		$this->content .= '<tr><td colspan="4">&nbsp;</td></tr>';

		$this->content .= '<form action="" method="post">';

		$this->content .= '<tr><td>Von IP:&nbsp;</td><td>';
		$this->content .= '<input type="text" name="ipstart" value="' . $ipStart . '"/>';;
		$this->content .= '</td><td>bis IP:&nbsp;</td><td>';
		$this->content .= '<input type="text" name="ipend" value="' . $ipEnd . '"/>';;
		$this->content .= '</td></tr>';
		$this->content .= '<tr><td colspan="4">&nbsp;</td></tr>';
		$this->content .= '<tr><td colspan="2">&nbsp;</td>';
		$this->content .= '<td colspan="2" valign="center" align="center">';
		$this->content .= '<input type="submit" name="submit" value="Netzwerk berechnen!"/>';;
		$this->content .= '</td></tr>';
		$this->content .= '</form>';;

		//Formular wurde abgeschickt
		if (isset($this->POST['submit'])) {

			$ipstart = $this->checkIP($ipStart);
			if (!$ipstart) {
				$this->content .= '<tr><td colspan="4"><b>' . $ipStart . '</b> ist keine korrekte IP-Adresse</td></tr>';
			}
			$ipend = $this->checkIP($this->POST['ipend']);
			if (!$ipend) {
				$this->content .= '<tr><td colspan="4"><b>' . $ipEnd . '</b> ist keine korrekte IP-Adresse</td></tr>';
			}
			if (base_convert($this->decTObin($ipend), 2, 16) < base_convert($this->decTObin($ipstart), 2, 16)) {
				$this->content .= '<tr><td colspan="4"><b>' . $ipEnd . '</b> ist kleiner <b>' . $ipStart . '</b></td></tr>';
			} else {

				if ($ipstart && $ipend) {
					while (base_convert($this->decTObin($ipend), 2, 16) > base_convert($this->decTObin($this->broadcast), 2, 16)) {
						$this->minMask($ipstart, $ipend);
						$this->content .= '<tr><td></td><td colspan="3">' . $this->netaddress . '/' . $this->netmask . ',</td></tr>';
						$arrIP = explode('.', $this->broadcast);
						$arrIP[3]++;
						for ($i = 3; $i > 1; $i--) {
							if ($arrIP[$i] >= 255) {
								$arrIP[$i] = 0;
								$arrIP[$i - 1]++;
							}
						}
						$ipstart = implode('.', $arrIP);
					}
				}
			}
		}

		$this->content .= '<tr><td colspan="4">&nbsp;</td></tr>';

		$this->content .= '</table>';

		return $this->content;

	}

	/**
	 * suche die kleineste mögliche Netzmaske mit der Netzadresse $ip
	 *
	 * @param string $ip
	 * @param string $end
	 * @return bool|void
	 */
	protected function minMask($ip, $end) {

		$ip = $this->checkIP($ip);
		if (!$ip) {
			$this->content .= '<tr><td colspan="4"><b>' . $ip . '</b> ist keine korrekte IP-Adresse</td></tr>';
			return false;
		}
		$arrIP = explode('.', $ip);
		if ($arrIP[3] == 1) {
			$arrIP[3] = 0;
			$ip = implode('.', $arrIP);
		}

		$bin = $this->decTObin($ip);
		$arrBits = str_split(strval($bin));
		for ($mask = 32; $mask > 0; $mask--) {
			if ($arrBits[$mask - 1]) {
				$this->netaddress = $ip;
				$this->netmask = $mask;
				$this->broadcast = substr($bin, 0, $this->netmask) . str_repeat('1', (32 - $this->netmask));
				while (base_convert((substr($this->broadcast, 0, 31) . '0'), 2, 16) > base_convert($this->decTObin($end), 2, 16) && $this->netmask < 32) {
					$this->netmask++;
					$this->broadcast = substr($bin, 0, $this->netmask) . str_repeat('1', (32 - $this->netmask));
				}
				$this->broadcast = $this->binTOdec($this->broadcast);
				return true;
			}
		}

	}

	protected function checkIP($ip) {
		$arrIP = explode('.', trim($ip));
		if (count($arrIP) < 4) {
			return false;
		}
		foreach ($arrIP as $key => $part) {
			$arrIP[$key] = intval(trim($part));
			if ($part > 255) {
				return false;
			}
		}
		return implode('.', $arrIP);
	}

	protected function decTObin($ip) {
		$ip = $this->checkIP($ip);
		if (!$ip) {
			return false;
		}
		$arrIP = explode('.', $ip);
		$bin = '';
		foreach ($arrIP as $part) {
			$bin .= substr('00000000' . decbin(trim($part)), -8);
		}
		return $bin;
	}

	protected function binTOdec($ip) {
		if (strlen(trim($ip)) < 32) {
			return false;
		}
		$arrIP = array();
		for ($i = 0; $i < 32; $i = $i + 8) {
			$tmp = bindec(substr($ip, $i, 8));
			if ($tmp > 255) {
				return false;
			}
			$arrIP[] = $tmp;
		}
		return implode('.', $arrIP);
	}

}

$ipcalc = GeneralUtility::makeInstance(\Subugoe\TmplDigizeit\Utility\IpCalculatorUtility::class);
print_r($ipcalc->main());
