<?php
declare(encoding = 'utf-8');
/**
 * Xinc - Continuous Integration.
 * Iterator over an array of SimpleXMLElement objects
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Config
 * @author    Arno Schneider <username@example.org>
 * @copyright 2007 Arno Schneider, Barcelona
 * @license   http://www.gnu.org/copyleft/lgpl.html GNU/LGPL, see license.php
 *            This file is part of Xinc.
 *            Xinc is free software; you can redistribute it and/or modify
 *            it under the terms of the GNU Lesser General Public License as
 *            published by the Free Software Foundation; either version 2.1 of
 *            the License, or (at your option) any later version.
 *
 *            Xinc is distributed in the hope that it will be useful,
 *            but WITHOUT ANY WARRANTY; without even the implied warranty of
 *            MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *            GNU Lesser General Public License for more details.
 *
 *            You should have received a copy of the GNU Lesser General Public
 *            License along with Xinc, write to the Free Software Foundation,
 *            Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * @link      http://xincplus.sourceforge.net
 */

require_once 'Xinc/Iterator.php';
require_once 'Xinc/Config/Exception/InvalidElement.php';


class Xinc_Config_Element_Iterator extends Xinc_Iterator
{
  
    /**
     *
     * @param SimpleXMLElement[] $array
     *
     * @throws Xinc_Config_Exception_InvalidElement
     */
    public function __construct(array $array = array())
    {
        foreach ($array as $xmlElement) {
            if (!$xmlElement instanceof SimpleXMLElement ) {
                throw new Xinc_Config_Exception_InvalidElement();
            }
            
        }
        
        parent::__construct($array);
    }
  
}