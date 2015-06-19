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

// wird in typo3conf/realurl_conf.php verwendet
class DrwikiRealurlHelper {

    /**
     * @var string
     */
    protected $spaceReplacement = '_';

    public function main($params, $ref) {
        if ($params['decodeAlias']) {
                return $this->alias2id($params['value']);
        } else {
                return $this->id2alias($params['value']);
        }
    }

    protected function id2alias($value) {
        return str_replace(' ', $this->spaceReplacement, $value);
    }

    protected function alias2id($value) {
        return str_replace($this->spaceReplacement, ' ', $value);
    }
}
