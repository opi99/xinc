<?php
/**
 * Build interface
 * 
 * Used by the engines to process a build
 * 
 * @package Xinc.Config
 * @author Arno Schneider
 * @version 2.0
 * @copyright 2007 Arno Schneider, Barcelona
 * @license  http://www.gnu.org/copyleft/lgpl.html GNU/LGPL, see license.php
 *    This file is part of Xinc.
 *    Xinc is free software; you can redistribute it and/or modify
 *    it under the terms of the GNU Lesser General Public License as published
 *    by the Free Software Foundation; either version 2.1 of the License, or    
 *    (at your option) any later version.
 *
 *    Xinc is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Lesser General Public License for more details.
 *
 *    You should have received a copy of the GNU Lesser General Public License
 *    along with Xinc, write to the Free Software
 *    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
interface Xinc_Build_Interface
{
    const INITIALIZED=-2;
    const FAILED=0;
    const PASSED=1;
    const STOPPED=-1;

     /** 
     * sets the project
     * and timestamp for the build
     *
     * @param Xinc_Project $project
     * @param integer $buildTimestamp
     */
    public function __construct(Xinc_Project &$project, $buildTimestamp);
    
    /**
     * Returns the last build
     * @return Xinc_Build_Interface
     */
    public function getLastBuild();
    
    /**
     * Enter description here...
     * @return integer Timestamp of build
     */
    public function getBuildTime();
    

    /**
     * 
     * @return Xinc_Project
     */
    public function getProject();
    
    /**
     * stores the build information
     *
     */
    public function serialize();
    
    /**
     * loads the build information
     *
     */
    public function unserialize();
}