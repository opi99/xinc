<?php
/**
 * Repository to manage all registered Plugins
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
require_once 'Xinc/Plugin/Task/Exception.php';
require_once 'Xinc/Plugin/Iterator.php';
require_once 'Xinc/Gui/Widget/Repository.php';

class Xinc_Plugin_Repository
{

    private static $_instance;
    
    /**
     * @var Xinc_Plugin_Task_Interface[]
     */
    private $_definedTasks=array();
    private $_plugins=array();
    /**
     * Get an instance of the Plugin Repository
     *
     * @return Xinc_Plugin_Repository
     */
    public static function getInstance()
    {
        if (!Xinc_Plugin_Repository::$_instance) {
            Xinc_Plugin_Repository::$_instance = new Xinc_Plugin_Repository();
        }
        return Xinc_Plugin_Repository::$_instance;
    }
    public function registerPlugin(Xinc_Plugin_Interface &$plugin)
    {
        if (!$plugin->validate()) {
            Xinc_Logger::getInstance()->error('cannot load plugin '
                                             .$plugin->getClassname());
                                             
            return false;
        }
        $tasks=$plugin->getTaskDefinitions();
        
        $task=null;
        foreach ($tasks as $task) {
            
            $fullTaskName = $task->getName();
            $parentTasks  = $task->getAllowedParentElements(); // should return the tasks! not the string
            if (count($parentTasks)>0) {
                $this->_registerTaskDependencies($plugin, $task, $parentTasks);
            } else {
                
                $fullTaskName = strtolower($fullTaskName);
                
                if (isset($this->_definedTasks[$fullTaskName])) {
                        throw new Xinc_Plugin_Task_Exception();
                }
                $this->_definedTasks[$fullTaskName] = array( //"filename" => $task->getFilename(),
                                                                'classname'=> $task->getClassname(),
                                                                'plugin'   => array( //"filename" => $plugin->getFilename(),
                                                                                     'classname'=> $plugin->getClassname()
                                                                                   )
                                                               );
                    // register default classname as task
                $classNameTask = strtolower($task->getClassname());
                if (isset($this->_definedTasks[$classNameTask])) {
                    throw new Xinc_Plugin_Task_Exception();
                }
                $this->_definedTasks[$classNameTask] = array( //"filename" => $task->getFilename(),
                                                                'classname'=> $task->getClassname(),
                                                                'plugin'   => array( //"filename" => $plugin->getFilename(),
                                                                                     'classname'=> $plugin->getClassname()
                                                                                   )
                                                               );
            }
        }
        $widgets = $plugin->getGuiWidgets();
        foreach ($widgets as $widget) {
            Xinc_Gui_Widget_Repository::getInstance()->registerWidget($widget);
        }
        
        $this->_plugins[] = $plugin;
    }
    
    private function _registerTaskDependencies(Xinc_Plugin_Interface $plugin,
                                               Xinc_Plugin_Task_Interface $task,
                                               $parentTasks)
    {    
        $fullTaskNames = array();
        foreach ($parentTasks as $parentTask) {
            if ($parentTask instanceof Xinc_Plugin_Task_Interface ) {
                $fullTaskNames[] = $parentTask->getName() . '/' . $task->getName();
                $fullTaskNames[] = $parentTask->getClassname() . '/' . $task->getClassname();
            }
        }
        foreach ($fullTaskNames as $fullTaskName) {
            $fullTaskName = strtolower($fullTaskName);
            
            if (isset($this->_definedTasks[$fullTaskName])) {
                throw new Xinc_Plugin_Task_Exception();
            }
            $this->_definedTasks[$fullTaskName] = array( //"filename" => $task->getFilename(),
                                                            'classname'=> $task->getClassname(),
                                                            'plugin'   => array( //"filename" => $plugin->getFilename(),
                                                                                 'classname'=> $plugin->getClassname()
                                                                               )
                                                           );
        }
    }

    public function &getTask($taskname, $parentElement = null)
    {
        if ($parentElement!=null) {
            $taskname2  = $parentElement . '/' . $taskname;
        }
        //echo $taskname.' - '.$taskname2."\n";
        var_dump($this->_definedTasks);
        if (isset($this->_definedTasks[$taskname2])) {
            $taskData = $this->_definedTasks[$taskname2];
        } else if (isset($this->_definedTasks[$taskname])) {
            $taskData = $this->_definedTasks[$taskname];
        } else {
            throw new Xinc_Plugin_Task_Exception('undefined task '.$taskname);
        }
       
        //require_once($taskData['filename']);
        if ( !isset($this->_plugins[$taskData['plugin']['classname']]) ) {
            //require_once($taskData['plugin']['filename']);
            $plugin=new $taskData['plugin']['classname'];
            $this->_plugins[$taskData['plugin']['classname']]=&$plugin;

        } else {
            $plugin=$this->_plugins[$taskData['plugin']['classname']];
        }
         
        $className = $taskData['classname'];
        $object = new $className($plugin);
        return $object;
    }
    
    /**
     * Returns Plugin Iterator
     *
     * @return Xinc_Iterator
     */
    public function getPlugins()
    {
        return new Xinc_Plugin_Iterator($this->_plugins);
    }

}