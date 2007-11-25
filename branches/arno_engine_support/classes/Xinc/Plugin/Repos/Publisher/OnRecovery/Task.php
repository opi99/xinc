<?php
/**
 * This interface represents a publishing mechanism to publish build results
 * 
 * @package Xinc.Plugin
 * @author Arno Schneider
 * @version 2.0
 * @copyright 2007 David Ellis, One Degree Square
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
require_once 'Xinc/Plugin/Repos/Publisher/AbstractTask.php';

class Xinc_Plugin_Repos_Publisher_OnRecovery_Task extends Xinc_Plugin_Repos_Publisher_AbstractTask
{
   
    public function getName()
    {
        return 'onrecovery';
    }
    public function validateTask()
    {
        
        foreach ( $this->_subtasks as $task ) {
            if ( !in_array('Xinc_Plugin_Repos_Publisher_AbstractTask', class_parents($task)) ) {
                return false;
            }
                
        }
        return true;
    }
    public function publish(Xinc_Build_Interface &$build)
    {
        /**
         * We only process on success. 
         * Failed builds are not processed by this publisher
         */
        if ($build->getStatus() != Xinc_Build_Interface::PASSED ) return;
        /**
         * Only if we recovered from a previous failed build cycle
         */
        $build->info('Last Build status: '.$build->getLastBuild()->getStatus());
        if ($build->getLastBuild()->getStatus() != 0) return;
        
        $published = false;
        $build->info('Publishing with OnRecovery Publishers');
        foreach ($this->_subtasks as $task) {
            $published = true;
            $build->info('Publishing with OnRecovery Publisher: ' . get_class($task));
            $task->publish($build);
            if ($build->getStatus() != Xinc_Build_Interface::PASSED) {
                $build->error('Error while publishing on Recovery. OnRecovery-Publish-Process stopped');
                break;
            }
        }
        if (!$published) {
            $build->info('No Publishers registered for OnRecovery');
        }
    }
}