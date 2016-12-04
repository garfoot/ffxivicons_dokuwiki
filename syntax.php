<?php

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

class syntax_plugin_ffxivicons extends DokuWiki_Syntax_Plugin {
    function getType(){
        return 'substition';
    }

    function getSort(){
        return 300;
    }

    function connectTo($mode){
        $this->Lexer->addSpecialPattern('\{\{fficon>[^}]*\}\}', $mode, 'plugin_ffxivicons');
    }

    function handle($match, $state, $pos, Doku_Handler $handler){
        // Strip the markup
        $match = substr($match, 9, -2);

        $data = array();
        $data['name'] = strtolower(trim($match));

        if(ffxiv_icon_cache::getInstance()->hasIcon($data['name'])){
            $data['path'] = ffxiv_icon_cache::getInstance()->getIcon($data['name']);
        }

        return $data;
    }

    function render($mode, Doku_Renderer $R, $data){
        if($mode == 'xhtml'){
            if(array_key_exists('path', $data)){
                $R->doc .= '<img src="/wiki/lib/plugins/ffxivicons' . $data['path'] . '" />';
            }else{
                $R->doc .= htmlentities("Skill: " . $data['name']);
            }

            return true;
        }

        return false;
    }
}

class ffxiv_icon_cache{
    private static $instance;
    private $icons = array();
    
    public static function getInstance(){
        if(is_null(self::$instance)){
            self::$instance = new self();
        }
        
        return self::$instance;
    }

    protected function __construct(){
        $base = realpath(dirname(__FILE__));
        $this->scanDir = $base;
        $this->processIcons("$base/icons");
    }

    public function addIcon($name, $path){
        $this->icons[strtolower($name)] = $path;
    }

    public function hasIcon($name){
        return array_key_exists(strtolower($name), $this->icons);
    }

    public function getIcon($name){
        return $this->icons[strtolower($name)];
    }
    
    private function processIcons($dir){
        $items = scandir($dir);
        
        foreach($items as $item){
            if($item == '.' || $item == '..'){
                // Current or parent
                continue;
            }
            
            $fullPath = "$dir/$item";
            
            if(is_dir($fullPath)){
                $this->processIcons($fullPath);
            }else{
                $filename = strtolower(basename(str_replace('_', ' ', $item), '.png'));
                
                if(array_key_exists($filename, $this->icons)){
                    // Already processed an icon with this name
                    continue;
                }
                
                $fullPath = substr($fullPath, strlen($this->scanDir));
                
                $this->icons[$filename] = $fullPath;
            }
        }
    }
}


?>
