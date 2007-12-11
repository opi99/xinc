<?php
/**
 * Api to get information about builds
 * 
 * @package Xinc.Plugin
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
require_once 'Xinc/Api/Module/Interface.php';
require_once 'Xinc/Plugin/Repos/Gui/Dashboard/Detail/Extension.php';

class Xinc_Plugin_Repos_Api_Artifacts implements Xinc_Api_Module_Interface
{
    /**
     * Enter description here...
     *
     * @var Xinc_Plugin_Interface
     */
    protected $_plugin;
    
    public function __construct(Xinc_Plugin_Interface &$plugin)
    {
        $this->_plugin = $plugin;
        
    }
    public function getName()
    {
        return 'artifacts';
    }
    public function getMethods()
    {
        return array('get', 'list');
    }
    public function processCall($methodName, $params = array())
    {

        switch ($methodName){
            case 'list':
                return $this->_getArtifacts($params);
                break;
            case 'get':
                return $this->_getArtifactFile($params);
                break;
        }
          
       
       
    }
    public function mime_content_type2($fileName)
    {
        $contentType = null;
        if(preg_match('/.*?.tar\.gz/', $fileName)) {
            return 'application/x-gzip';
        }
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME); // return mime type ala mimetype extension
            if(!$finfo) return;
            $contentType = finfo_file($finfo, $fileName);
            finfo_close($finfo);
        } else if (function_exists('mime_content_type')) {
            $contentType = mime_content_type($fileName);
        }
    
        return $contentType;
    
    }
    private function _getArtifactFile($params)
    {
        /**$projectName = $params['p'];
        $buildTime = $params['buildtime'];
        $file = $params['file'];*/
        
        $query = $_SERVER['REQUEST_URI'];
       
       preg_match("/\/(.*?)\/(.*?)\/(.*?)\/(.*?)\/(.*?)\/(.*?)\/(.*)/", $query, $matches);
       
       if (count($matches)!=8) {
           echo "Could not find artifact";
           die();
       }
       $projectName = $matches[5];
       $buildTime = $matches[6];
       $file = $matches[7];
        $project = new Xinc_Project();
        $project->setName($projectName);
        try {
            $build = Xinc_Build::unserialize($project,
                                             $buildTime,
                                             Xinc_Gui_Handler::getInstance()->getStatusDir());
           
            $statusDir = Xinc_Gui_Handler::getInstance()->getStatusDir();
            $statusDir .= DIRECTORY_SEPARATOR . $build->getStatusSubDir() . 
                          DIRECTORY_SEPARATOR . Xinc_Plugin_Repos_Artifacts::ARTIFACTS_DIR .
                          DIRECTORY_SEPARATOR;

            /**
             * Replace multiple / slashes with just one
             */
            $fileName = $statusDir.$file;
            $fileName = preg_replace('/\\' . DIRECTORY_SEPARATOR . '+/', DIRECTORY_SEPARATOR, $fileName);
            $realfile = realpath($fileName);
            if ($realfile != $fileName) {
                echo "Could not find artifact";
                die();
            } else if (file_exists($fileName) && is_file($realfile)) {
                //echo "here";
                $contentType = $this->mime_content_type2($fileName);
                if (!empty($contentType)) {
                    header("Content-Type: " . $contentType);
                }
                readfile($fileName);
                die();
            } else {
                echo "Could not find artifact";
                die();
            }
           
        } catch (Exception $e) {
            echo "Could not find any artifacts";
            die();
        }
    }

    private function _getArtifacts($params)
    {
        $projectName = isset($params['p']) ? $params['p'] : null;
        $project = new Xinc_Project();
        $project->setName($projectName);
        $buildtime = isset($params['buildtime']) ? (int)$params['buildtime'] : 0;
        $node = isset($params['node']) ? $params['node'] : '';
        $node = str_replace('source', '', $node);
        $node = str_replace(',', DIRECTORY_SEPARATOR, $node);
        $artifacts = array();
        try {
            $buildObject = Xinc_Build::unserialize($project,
                                                       $buildtime,
                                                       Xinc_Gui_Handler::getInstance()->getStatusDir());
            $artifacts = $this->_getArtifactsTree($buildObject, $node);
        } catch(Exception $e) {
            
        }
        
        
        $responseObject = new Xinc_Api_Response_Object();
        $responseObject->set($artifacts);
        return $responseObject;
    }
    
    private function _getArtifactsTree(Xinc_Build_Interface &$build, $dirname)
    {
        $projectName = $build->getProject()->getName();
        $buildTime = $build->getBuildTime();
        
        $statusDir = Xinc_Gui_Handler::getInstance()->getStatusDir();
        $statusDir .= DIRECTORY_SEPARATOR . $build->getStatusSubDir() . 
                      DIRECTORY_SEPARATOR . Xinc_Plugin_Repos_Artifacts::ARTIFACTS_DIR .
                      DIRECTORY_SEPARATOR;
        $directory = $statusDir . $dirname;
        if (!is_dir($directory)) return;
        $dh = opendir($directory);
        $items = array();
        while ($file = readdir($dh)) {
            if (!in_array($file, array('.', '..'))) {
                if (is_dir($directory . DIRECTORY_SEPARATOR . $file)) {
                    $class = 'folder';
                    $leaf = false;
                } else {
                    $class = 'file';
                    $leaf = true;
                }
                $items[] = array('text'=> $file,
                                 'id'=> addslashes(str_replace('/', '/', $dirname . DIRECTORY_SEPARATOR . $file)),
                                 'cls'=> $class, 
                                 'leaf'=> $leaf);
            }
        }
        return $items;
    }
    
   
    
    private function _getHistoryBuilds($projectName, $start, $limit=null)
    {
        $statusDir = Xinc_Gui_Handler::getInstance()->getStatusDir();
        $historyFile = $statusDir . DIRECTORY_SEPARATOR . $projectName . '.history';
        $project = new Xinc_Project();
        $project->setName($projectName);
        $buildHistoryArr = unserialize(file_get_contents($historyFile));
        $totalCount = count($buildHistoryArr);
        if ($limit==null) {
            $limit = $totalCount;
        }
        $buildHistoryArr = array_slice($buildHistoryArr, $start, $limit, true);
        
        $builds = array();
        
        foreach ($buildHistoryArr as $buildTimestamp => $buildFileName) {
            try {
                $buildObject = Xinc_Build::unserialize($project,
                                                       $buildTimestamp,
                                                       Xinc_Gui_Handler::getInstance()->getStatusDir());
                $builds[] = array('buildtime'=>$buildObject->getBuildTime(),'label'=>$buildObject->getLabel());
            } catch (Exception $e) {
                // TODO: Handle
                
            }
            
        }
        
        $builds = array_reverse($builds);
        
        $object = new stdClass();
        $object->totalcount = $totalCount;
        $object->builds = $builds;
        //return new Xinc_Build_Iterator($builds);
        return $object;
    }
}