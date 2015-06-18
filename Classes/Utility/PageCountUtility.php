<?php
namespace Subugoe\TmplDigizeit\Utility;

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

/* +++++++++++++++++++++++++++++++++++++++++++++ */
/* +++++ MyDigiZeit/VG Wort ++++++++++++++++++++ */
/* +++++++++++++++++++++++++++++++++++++++++++++ */

define('__DZROOT__', realpath(PATH_site . '/../'));

class PageCountUtility {

	/**
	 * @var string
	 */
	protected $content;

	/**
	 * @var array
	 */
	protected $cache;

	/**
	 * @var array
	 */
	protected $POST;

	/**
	 * @var string
	 */
	protected $start;

	/**
	 * @var string
	 */
	protected $end;

	/**
	 * @var array
	 */
	protected $arrResult;

	/**
	 * @var array
	 */
	protected $arrPredecessor;

	/**
	 * @var array
	 */
	protected $downloads;

	protected $config = array(
		'counter' => '/counter/logs/',
		'cache' => '/pagecount.cache',
		'start' => '20020730',
		'strWall' => '1925',
		'ppnResolver' => 'http://resolver.sub.uni-goettingen.de/purl/?',
		'metsResolver' => 'http://www.digizeitschriften.de/dms/metsresolver/?PPN=',
		'solrPhpsUrl' => 'http://localhost:8080/digizeit2/collection1/select/?wt=phps',
		'arrSerFields' => array('structrun'),
		'digizeitonly' => '((acl:free OR acl:gesamtabo) AND NOT(acl:(dipfbbfberlin OR ubfrankfurt OR ubheidelberg OR ubtuebingen OR ubweimar OR zbwkieldigire OR ubmannheim OR sbbberlin))) ',
	);

	public function main() {
                ini_set('memory_limit', '2048M');

		$this->config['cache'] = sys_get_temp_dir() . $this->config['cache'];
		$this->config['counter'] = realpath(__DZROOT__ . $this->config['counter']);

		if (!is_array($this->cache)) {
			$str = file_get_contents($this->config['cache']);
			if ($str) {
				$this->cache = json_decode($str, true);
			}

		}
		if (!is_array($this->cache)) {
			$this->cache = array();
		}

		$this->config['end'] = date("Y-m-d", time()) . 'T' . date("H:i:s", time()) . 'Z';

		$this->POST = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST();

		$this->content .= '<table>';
		$this->content .= '<tr><td colspan="6">&nbsp;</td></tr>';

		$this->content .= '<form action="" method="post">';

		$this->content .= '<tr><td>Start:&nbsp;</td><td>';
		$this->getDateForm('start');
		$this->content .= '</td><td>Ende:&nbsp;</td><td>';
		$this->getDateForm('end');
		$this->content .= '</td><td colspan="2"></td></tr>';

		$this->content .= '<tr><td colspan="6">&nbsp;</td></tr>';

		$this->content .= '<tr><td valign="top">Kollektion:&nbsp;</td><td valign="top">';
		$this->getCollectionForm();

		$this->content .= '</td><td valign="top">Lizenz:&nbsp;</td><td valign="top">';
		$this->getLicenseForm();
		$this->content .= '</td></tr>';

		$this->content .= '<tr><td colspan="6">&nbsp;</td></tr>';

		$this->content .= '<tr><td colspan="3">&nbsp;</td>';
		$this->content .= '<td colspan="3" valign="center" align="center">';
		$this->content .= '<input type="submit" name="submit" value="absenden und warten!"/>';
		$this->content .= '</td></tr>';
		$this->content .= '</form>';


		$this->content .= '<tr><td colspan="6">&nbsp;</td></tr>';

		$this->content .= '</table>';

		//Formular wurde abgeschickt
		if (isset($this->POST['submit'])) {
			$arrQuery = array();
			//collections
			$arrCol = array();
			if (!in_array(0, $this->POST['collect'])) {
				foreach ($this->POST['collect'] as $collect) {
					$arrCol[] = 'dc:"' . $collect . '"';
				}
				$arrQuery[] = '(' . implode(' OR ', $arrCol) . ')';
			}

			//Licenses
			$arrAcl = array();
			if (!in_array('all', $this->POST['license'])) {
				foreach ($this->POST['license'] as $license) {
					if ($license == 'digizeitonly') {
						$arrAcl = array();
						$arrAcl[] = $this->config[$license];
						break;
					}
					$arrAcl[] = 'acl:"' . $license . '"';
				}
				$arrQuery[] = '(' . implode(' OR ', $arrAcl) . ')';
			}

			$this->start = $this->POST['start']['year'][0] . '-' . $this->POST['start']['month'][0] . '-' . '01T00:00:00Z';
			$lastdayofmonth = date("t", mktime(0, 0, 0, intval($this->POST['end']['month'][0]), 1, $this->POST['end']['year'][0]));
			$this->end = $this->POST['end']['year'][0] . '-' . $this->POST['end']['month'][0] . '-' . $lastdayofmonth . 'T23:59:59Z';

			// prepare volumes
			$volumeQuery = 'iswork:1 AND dateindexed:[' . $this->start . ' TO ' . $this->end . ']';

			if (count($arrQuery)) {
				$q = implode(' AND ', $arrQuery) . ' AND ' . $volumeQuery;
			} else {
				$q = $volumeQuery;
			}
			$arrParams = array(
				'q' => urlencode($q),
				'start' => 0,
				'rows' => 99999,
				'sort' => 'currentnosort+asc'
			);
			$arrVolumeSolr = $this->getSolrResult($arrParams);
			// end prepare volumes


			//get all periodicals from start!
			$periodicalQuery = 'docstrct:periodical';

			if (count($arrQuery)) {
				$q = implode(' AND ', $arrQuery) . ' AND ' . $periodicalQuery;
			} else {
				$q = $periodicalQuery;
			}

			$arrParams = array(
				'q' => urlencode($q),
				'start' => 0,
				'rows' => 9999,
				'sort' => 'bytitle+asc'
			);
			$arrPeriodicalSolr = $this->getSolrResult($arrParams);


			// seperating main journals from predecessors
			$this->arrResult = array();
			$this->arrPredecessor = array();
			foreach ($arrPeriodicalSolr['response']['docs'] as $periodical) {
				if (isset($periodical['suc'])) {
					$this->arrPredecessor[$periodical['pid']] = $periodical;
					$this->arrPredecessor[$periodical['pid']]['PAGES'] = 0;
				} else {
					$this->arrResult[$periodical['pid']] = $periodical;
					$this->arrResult[$periodical['pid']]['PAGES'] = 0;
				}
			}

			// add volumes to journals
			foreach ($arrVolumeSolr['response']['docs'] as $volume) {
				$this->getInfo($volume);
				if (isset($this->arrPredecessor[$volume['structrun'][0]['pid']])) {
					$this->arrPredecessor[$volume['structrun'][0]['pid']]['volumes'][] = $volume;
				}
				if (isset($this->arrResult[$volume['structrun'][0]['pid']])) {
					$this->arrResult[$volume['structrun'][0]['pid']]['volumes'][] = $volume;
				}
			}


			// add info to predecessors
			foreach ($this->arrPredecessor as $pid => $periodical) {
				$this->getInfo($this->arrPredecessor[$pid]);
			}

			// add info and predecessors to journals
			foreach ($this->arrResult as $pid => $periodical) {
				if (isset($periodical['pre'])) {
					foreach ($periodical['pre'] as $_pid) {
						$this->getPredecessor($pid, $_pid);
					}
				}
				$this->getInfo($this->arrResult[$pid]);
			}
			// end periodicals

			// get downloads from counter
			$this->downloads = array();
			$this->getDownloads($this->POST['start']['year'][0] . $this->POST['start']['month'][0], $this->POST['end']['year'][0] . $this->POST['end']['month'][0]);

			//output
			$count = 0;
			$arrLines = array();
			//legend
			$arrLines[] = "\t" . 'DigiZeitschriften: ' . "\t" . $this->POST['start']['month'][0] . '/' . $this->POST['start']['year'][0] . ' bis ' . $this->POST['end']['month'][0] . '/' . $this->POST['end']['year'][0];
			$arrLines[] = "\n\n\n";
			$arrLines[] = "\t\t\t\t\t" . 'Importierte Seiten / Bände: ' . "\t\t\t\t" . 'Band Importe:' . "\t\n";
			$arrLines[] = 'Anzahl Zss.' . "\t" . 'Titel inkl. Vorgänger' . "\t" . 'Persistent URL' . "\t" . 'Verlag.' . "\t" . 'Erscheiniungsverlauf.' . "\t" . 'vor 1926' . "\t" . 'Bände' . "\t" . 'nach 1926' . "\t" . 'Bände' . "\t" . 'erster' . "\t" . 'letzter' . "\t" . 'Downloads';
			$arrLines[] = "\n";
			foreach ($this->arrResult as $periodical) {
				if (in_array('digizeitonly', $this->POST['license'])) {
                                        $periodical['acl'] = array_unique($periodical['acl']);
					foreach ($periodical['acl'] as $key => $license) {
						$periodical['acl'][$key] = strtolower($license);
					}
					if (!in_array('gesamtabo', $periodical['acl'])) {
						continue;
					}
				}
				$count++;
				$periodical['linenumber'] = $count;
				$arrLines[] = $this->getLine($periodical);
				if ($periodical['PREDECESSOR']) {
					foreach ($periodical['PREDECESSOR'] as $_periodical) {
						$periodical['linemumber'] = '';
                                                $arrLines[] = "\n";
						$arrLines[] = $this->getLine($_periodical);
					}
                                        $arrLines[] = "\n";
				} else {
                                    $arrLines[] = "\n";                                    
                                }
				$arrLines[] = "\n";
			}
			header('Content-type: text/csv; charset=UTF-8');
			header('Content-Disposition: inline; filename="' . date('Y-m-d', time()) . '_dz_statistik_' . $this->POST['start']['year'][0] . $this->POST['start']['month'][0] . '-' . $this->POST['end']['year'][0] . $this->POST['end']['month'][0] . '.csv"');
			print_r(implode('', $arrLines));
			exit();

		}

	}

	protected function getLine($periodical) {
		$column = array();
		$column[0] = $periodical['linenumber'];
		$column[1] = trim($periodical['title'][0]);
		$column[2] = $this->config['ppnResolver'] . trim($periodical['pid']);
		$column[3] = trim($periodical['COPYRIGHT']);
		$column[4] = trim($periodical['DATERUN']);
		$column[5] = 0;
		$column[6] = 0;
		$column[7] = 0;
		$column[8] = 0;
		foreach ($periodical['volumes'] as $volume) {
			if ($volume['yearpublish'] <= $this->config['strWall']) {
				$column[5] += $volume['PAGES'];
				$column[6]++;
			} else {
				$column[7] += $volume['PAGES'];
				$column[8]++;
			}
		}
		$column[9] = trim($periodical['FIRSTIMPORT']);
		$column[10] = trim($periodical['LASTIMPORT']);
		$column[11] = trim($this->downloads[$periodical['pid']]);
		return implode("\t", $column);
	}

	protected function getPredecessor($pid, $_pid) {
		if (isset($this->arrPredecessor[$_pid])) {
			$this->arrResult[$pid]['PREDECESSOR'][$_pid] = $this->arrPredecessor[$_pid];
			if ($this->arrPredecessor[$_pid]['PRE']) {
				foreach ($this->arrPredecessor[$_pid]['PRE'] as $PPNPID) {
					$this->getPredecessor($pid, $PID);
				}
			}
		}
	}

	protected function getInfo(&$arr) {
		if (!isset($this->cache[$arr['pid']]['cachemodified']) OR $this->cache[$arr['pid']]['cachemodified'] < $arr['datemodified']) {
			unset($this->cache[$arr['pid']]);

			$dom = new \DOMDocument('1.0', 'UTF-8');
			$test = $dom->load($this->config['metsResolver'] . trim($arr['pid']));
			if (!$test) {
				return false;
			}
			$xpath = new \DOMXpath($dom);
			$this->setNSprefix($xpath);

			//copyright
			$nodeList = $xpath->evaluate('/mets:mets/mets:dmdSec/mets:mdWrap[@MDTYPE="MODS"]/mets:xmlData/mods:mods/mods:accessCondition[@type="copyright"]');
			if ($nodeList->length) {
				$arr['COPYRIGHT'] = trim($nodeList->item(0)->nodeValue);
				$this->cache[$arr['pid']]['COPYRIGHT'] = $arr['COPYRIGHT'];
			}

			//scanned pages
			if (strtolower($arr['docstrct']) == 'periodicalvolume') {
				$nodeList = $xpath->evaluate('/mets:mets/mets:structMap[@TYPE="PHYSICAL"]/mets:div/mets:div');
				if ($nodeList->length) {
					$arr['PAGES'] = $nodeList->length;
					$this->cache[$arr['pid']]['PAGES'] = $arr['PAGES'];
				}
			}

			//first- / last Import
			if (strtolower($arr['docstrct']) == 'periodical') {
				$arrParams = array(
					'q' => urlencode('iswork:1 AND idparentdoc:"' . $arr['pid'] . '"'),
					'start' => 0,
					'rows' => 9999,
					'sort' => 'dateindexed+asc'
				);
				$arrSolr = $this->getSolrResult($arrParams);
				if ($arrSolr['response']['docs']) {
					$arr['FIRSTIMPORT'] = $arrSolr['response']['docs'][0]['dateindexed'];
					$this->cache[$arr['pid']]['FIRSTIMPORT'] = $arr['FIRSTIMPORT'];
					$arr['LASTIMPORT'] = $arrSolr['response']['docs'][count($arrSolr['response']['docs']) - 1]['dateindexed'];
					$this->cache[$arr['pid']]['LASTIMPORT'] = $arr['LASTIMPORT'];
				}

				//date run from note
				if (!isset($arr['DATERUN'])) {
					$nodeList = $xpath->evaluate('/mets:mets/mets:dmdSec/mets:mdWrap[@MDTYPE="MODS"]/mets:xmlData/mods:mods/mods:note[@type="date/sequential designation"]');
					if ($nodeList->length) {
						$arr['DATERUN'] = trim($nodeList->item(0)->nodeValue);
						$this->cache[$arr['pid']]['DATERUN'] = $arr['DATERUN'];
					}
				}
				//date run from otherdate
				if (!isset($arr['DATERUN'])) {
					$nodeList = $xpath->evaluate('/mets:mets/mets:dmdSec/mets:mdWrap[@MDTYPE="MODS"]/mets:xmlData/mods:mods/mods:originInfo/mods:dateOther');
					if ($nodeList->length) {
						$arr['DATERUN'] = trim($nodeList->item(0)->nodeValue);
						$this->cache[$arr['pid']]['DATERUN'] = $arr['DATERUN'];
					}
				}
				//date run from dateIssued start / end
				if (!isset($arr['DATERUN'])) {
					$nodeList = $xpath->evaluate('/mets:mets/mets:dmdSec/mets:mdWrap[@MDTYPE="MODS"]/mets:xmlData/mods:mods/mods:originInfo/mods:dateIssued[@point="start"]');
					if ($nodeList->length) {
						$arr['DATERUN'] = trim($nodeList->item(0)->nodeValue) . ' - ';
					}
					$nodeList = $xpath->evaluate('/mets:mets/mets:dmdSec/mets:mdWrap[@MDTYPE="MODS"]/mets:xmlData/mods:mods/mods:originInfo/mods:dateIssued[@point="end"]');
					if ($nodeList->length) {
						if ($arr['DATERUN']) {
							$arr['DATERUN'] .= trim($nodeList->item(0)->nodeValue);
						} else {
							$arr['DATERUN'] .= ' - ' . trim($nodeList->item(0)->nodeValue);
						}
					}
				}
				//date run from dateIssued
				if (!isset($arr['DATERUN'])) {
					$nodeList = $xpath->evaluate('/mets:mets/mets:dmdSec/mets:mdWrap[@MDTYPE="MODS"]/mets:xmlData/mods:mods/mods:originInfo/mods:dateIssued');
					if ($nodeList->length) {
						$arr['DATERUN'] = trim($nodeList->item(0)->nodeValue) . ' - ';
						$this->cache[$arr['PPN']]['DATERUN'] = $arr['DATERUN'];
					}
				}

				$this->cache[$arr['pid']]['DATERUN'] = $arr['DATERUN'];

			}

			$this->updateCache($arr['pid']);
		} else {
			if ($this->cache[$arr['pid']]) {
				foreach ($this->cache[$arr['pid']] as $key => $val) {
					$arr[$key] = $val;
				}
			}
		}

		if (is_array($arr['volumes'])) {
			foreach ($arr['volumes'] as $volume) {
				$arr['PAGES'] += $volume['PAGES'];
			}
			foreach ($arr['PREDECESSOR'] as $pid => $journal) {
				foreach ($journal['volumes'] as $volume) {
					$arr['PREDECESSOR'][$pid]['PAGES'] += $volume['PAGES'];
				}
			}
		}
	}


	protected function getLicenseForm() {
		$arrParams = array(
			'q' => '',
			'start' => 0,
			'rows' => 0,
			'facet' => 'on',
			'facet.field' => 'acl',
			'facet.sort' => 'lexicographic',
		);
		$arrSolr = $this->getSolrResult($arrParams);
		$arrACL = $arrSolr['facet_counts']['facet_fields']['acl'];
		$arrACL = array_merge(array('all' => 'All'), $arrACL);
		$arrACL = array_merge(array('digizeitonly' => 'VGWort'), $arrACL);

		$i = 0;
		$selected = false;
		foreach ($arrACL as $acl => $count) {
			$license[$i]['item'] = $acl;
			if ($acl == 'all' || $acl == 'digizeitonly') {
				$license[$i]['value'] = $count;
			} else {
				$license[$i]['value'] = $acl;
			}
			if (isset($this->POST['license'])) {
				if (in_array($license[$i]['item'], $this->POST['license'])) {
					$license[$i]['selected'] = 'selected="selected"';
					$selected = true;
				} else {
					$license[$i]['selected'] = '';
				}
			} else {
				$license[$i]['selected'] = '';
			}
			$i++;
		}
		if (!$selected) {
			$license[0]['selected'] = 'selected="selected"';
		}
		reset($license);
		$this->content .= '<select name="license[]" size="10" multiple>';
		foreach ($license as $val) {
			$this->content .= '<option value="' . strtolower($val['item']) . '" ' . $val['selected'] . '>' . $val['value'] . '</option>';
		}
		$this->content .= '</select>';
	}

	protected function getCollectionForm() {
		$collect[0]['item'] = 'All';
		$collect[0]['value'] = 0;
		if (isset($this->POST['collect'])) {
			if (in_array($collect[0]['value'], $this->POST['collect'])) {
				$collect[0]['selected'] = 'selected="selected"';
			} else {
				$collect[0]['selected'] = '';
			}
		} else {
			$collect[0]['selected'] = 'selected';
		}
		$i = 1;

		$arrParams = array(
			'q' => '',
			'start' => 0,
			'rows' => 0,
			'facet' => 'on',
			'facet.field' => 'dc',
			'facet.sort' => 'lexicographic',
		);
		$arrSolr = $this->getSolrResult($arrParams);
		$arrFields = $arrSolr['facet_counts']['facet_fields']['dc'];

		foreach ($arrFields as $field => $count) {
			$collect[$i]['item'] = $field;
			$collect[$i]['value'] = $field;
			if (isset($this->POST['collect'])) {
				if (in_array($collect[$i]['value'], $this->POST['collect'])) {
					$collect[$i]['selected'] = 'selected="selected"';
				} else {
					$collect[$i]['selected'] = '';
				}
			} else {
				$collect[$i]['selected'] = '';
			}
			$i++;
		}

		reset($collect);
		$this->content .= '<select name="collect[]" size="10" multiple>';
		foreach ($collect as $val) {
			$this->content .= '<option value="' . $val['value'] . '" ' . $val['selected'] . '>' . $val['item'] . '</option>';
		}
		$this->content .= '</select>';
	}

	protected function getStructForm() {
		$struct[0]['item'] = 'All';
		$struct[0]['value'] = 'periodicalvolume';
		$struct[0]['selected'] = 'selected="selected"';

		$i = 1;
		$arrParams = array(
			'q' => urlencode('docstrct:*'),
			'start' => 0,
			'rows' => 0,
			'facet' => 'on',
			'facet.field' => 'docstrct',
			'facet.sort' => 'lexicographic',
		);
		$arrSolr = $this->getSolrResult($arrParams);
		$arrFields = $arrSolr['facet_counts']['facet_fields']['docstrct'];
		foreach ($arrFields as $field => $count) {
			$struct[$i]['item'] = $field;
			$struct[$i]['value'] = $field;
			if (isset($this->POST['struct'])) {
				if (in_array($struct[$i]['value'], $this->POST['struct'])) {
					$struct[$i]['selected'] = 'selected="selected"';
				} else {
					$struct[$i]['selected'] = '';
				}
			} else {
				$struct[$i]['selected'] = '';
			}
			$i++;
		}

		reset($struct);
		$this->content .= '<select name="struct[]" size="10" multiple>';
		foreach ($struct as $val) {
			$this->content .= '<option value="' . $val['value'] . '" ' . $val['selected'] . '>' . $val['item'] . '</option>';
		}
		$this->content .= '</select>';
	}

	protected function getDateForm($name) {

		$this->content .= '<select name="' . $name . '[month][]" size="1">';
		for ($month = 1; $month <= 12; $month++) {
			if (isset($this->POST[$name]['month'])) {
				if ($this->POST[$name]['month'][0] == substr(('0' . $month), -2)) {
					$selected = 'selected="selected"';
				} else {
					$selected = '';
				}
			} else {
				if (substr($this->config[$name], 4, 2) == substr(('0' . $month), -2)) {
					$selected = 'selected="selected"';
				} else {
					$selected = '';
				}
			}
			$this->content .= '<option value="' . substr(('0' . $month), -2) . '" ' . $selected . '>' . substr(('0' . $month), -2) . '</option>';
		}
		$this->content .= '</select>';

		$this->content .= '<select name="' . $name . '[year][]" size="1">';
		for ($year = substr($this->config['start'], 0, 4); $year <= substr($this->config['end'], 0, 4); $year++) {
			if (isset($this->POST[$name]['year'])) {
				if ($this->POST[$name]['year'][0] == $year) {
					$selected = 'selected="selected"';
				} else {
					$selected = '';
				}
			} else {
				if (substr($this->config[$name], 0, 4) == $year) {
					$selected = 'selected="selected"';
				} else {
					$selected = '';
				}
			}
			$this->content .= '<option value="' . $year . '" ' . $selected . '>' . $year . '</option>';
		}
		$this->content .= '</select>';
	}

	protected function getSolrResult($arr) {
		$strSolr = '';
		foreach ($arr as $key => $val) {
			$strSolr .= '&' . $key . '=' . $val;
		}
		$arrSolr = unserialize(file_get_contents($this->config['solrPhpsUrl'] . $strSolr));
		foreach ($arrSolr['response']['docs'] as $key => $val) {
			foreach ($val as $k => $v) {
				if (in_array($k, $this->config['arrSerFields'])) {
					$arrSolr['response']['docs'][$key][$k] = $this->_unserialize($v);
				}
			}
		}
		return $arrSolr;
	}

	protected function updateCache($pid) {
		$this->cache[$pid]['cachemodified'] = date('Y-m-d', time()) . 'T' . date('H:i:s', time()) .'Z';
		file_put_contents($this->config['cache'], json_encode($this->cache));
	}

	protected function _unserialize($str) {
		$ret = json_decode($str, true);
		if (!is_array($ret)) {
			$ret = unserialize($str);
		}
		return $ret;
	}

	/**
	 * @param \DOMXPath $xpath
	 * @param \DOMNodeList $node
	 */
	protected function setNSprefix(&$xpath, $node = NULL) {
		if (!$node) {
			$xqueryList = $xpath->evaluate('*[1]');
			if ($xqueryList->length) {
				self::setNSprefix($xpath, $xqueryList->item(0));
			}
		}
		if (is_object($node)) {
			if ($node->prefix) {
				$xpath->registerNamespace(strtolower($node->prefix), $node->namespaceURI);
			}
			$xqueryList = $xpath->evaluate('following-sibling::*[name()!="' . $node->nodeName . '"][1]', $node);
			if ($xqueryList->length) {
				self::setNSprefix($xpath, $xqueryList->item(0));
			}
			if ($node->firstChild) {
				self::setNSprefix($xpath, $node->firstChild);
			}
			if ($node->attributes->length) {
				foreach ($node->attributes as $attribute) {
					if ($attribute->prefix && !$arrNS[strtolower($attribute->prefix)]) {
						$xpath->registerNamespace(strtolower($attribute->prefix), $attribute->namespaceURI);
					}
				}
			}
		}
		unset($xqueryList);
		unset($node);
		unset($attribute);
	}

	protected function getDownloads($start, $end) {
		$startmonth = intval(substr($start, 4, 2));
		$startyear = substr($start, 0, 4);
		$endmonth = intval(substr($end, 4, 2));
		$endyear = substr($end, 0, 4);
		$arrDate = array();

		if ($startyear < $endyear) {
			for ($month = $startmonth; $month <= 12; $month++) {
				$arrDate[$startyear . '12'][] = $month + 6;
			}
			for ($year = (intval($startyear) + 1); $year < $endyear; $year++) {
				for ($month = 1; $month <= 12; $month++) {
					$arrDate[$year . '12'][] = $month + 6;
				}
			}
			for ($month = 1; $month <= $endmonth; $month++) {
				$arrDate[$endyear . substr('0' . $endmonth, -2)][] = $month + 6;
			}
		} else {
			for ($month = $startmonth; $month <= $endmonth; $month++) {
				$arrDate[$startyear . substr('0' . $endmonth, -2)][] = $month + 6;
			}
		}

		$xml = new \DOMDocument('1.0', 'UTF-8');
		foreach ($arrDate as $date => $arrMonth) {
			$test = $xml->load($this->config['counter'] . '/' . $date . '/xml/all.xml');
			if ($test) {
				$xpath = new \DOMXpath($xml);
				// title nodes: "title text (PPN)"
				$nodeList = $xpath->evaluate('/excel_workbook/sheets/sheet[2]/rows/row/cell[@col="0"]');
				if ($nodeList->length) {
					foreach ($nodeList as $node) {
						$parent = $node->parentNode;
						$_parent = $parent->nextSibling;
						while ($_parent && $_parent->nodeType != XML_ELEMENT_NODE) {
							$_parent = $_parent->nextSibling;
						}
						$start = strrpos(trim($node->nodeValue), '(') + 1;
						$length = strrpos(trim($node->nodeValue), ')') - strrpos(trim($node->nodeValue), '(') - 1;
						$ppn = trim(substr(trim($node->nodeValue), $start, $length));
						$arrParams = array(
							'q' => urlencode('pid:"' . $ppn . '" AND docstrct:periodical'),
							'start' => 0,
							'rows' => 1,
						);
						$arrSolr = $this->getSolrResult($arrParams);
						if ($arrSolr['response']['numFound']) {
							if (!isset($this->downloads[$ppn])) {
								$this->downloads[$ppn] = 0;
							}
							if ($ppn) {
								foreach ($arrMonth as $col) {
									$cellList = $xpath->evaluate('cell[@col="' . $col . '"]', $parent);
									if ($cellList->length) {
										//$this->downloads[$ppn][$col]['pdf'] = trim($cellList->item(0)->nodeValue);
										$this->downloads[$ppn] += intval(trim($cellList->item(0)->nodeValue));
									}
									$cellList = $xpath->evaluate('cell[@col="' . $col . '"]', $_parent);
									if ($cellList->length) {
										//$this->downloads[$ppn][$col]['img'] = trim($cellList->item(0)->nodeValue);
										$this->downloads[$ppn] += intval(trim($cellList->item(0)->nodeValue));
									}
								}
							}
						}
					}
				}
			}
		}
	}

	/**
	 * @return string
	 */
	public function getContent() {
		return $this->content;
	}

}

$vgwort = new \Subugoe\TmplDigizeit\Utility\PageCountUtility;
$vgwort->main();
print_r($vgwort->getContent());
