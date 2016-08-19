<?php

namespace App\ScriptLoader;

use Nette\Application\UI\Control;
use Nette\Caching\Cache;
use Nette\Utils\Strings;

/**
 * Description of ScriptLoader
 *
 * @author Vsek
 */
class ScriptLoader extends Control{
    
    private $usedModule = false;
    private $ignoreModule = array('Front');
    
    public function setModule($module){
        $this->usedModule = $module;
    }
    
    public function getPostfix($isMobile){
        $return = '';
        if($this->usedModule && !in_array($this->usedModule, $this->ignoreModule)){
            $return .= '_' . Strings::lower($this->usedModule);
        }
        if($isMobile){
            $return .= '_mobile';
        }
        return $return;
    }
    
    public function renderCssCritical($type = null, $isMobile = false){

        
        $config = $this->getPresenter()->context->parameters['scriptLoader']['css' . $this->getPostfix($isMobile)];

        if(is_null($type)){ $type = 'critical'; }

        if(isset($config[$type])){
            if(!$this->getPresenter()->context->parameters['scriptLoader']['enable']){
                if(!is_null($config[$type])){
                    foreach($config[$type] as $css){
                        echo '<link rel="stylesheet" media="screen,projection,tv" href="/' . $css . '">';
                    }
                }
            }else{

                
                $cache = new Cache($this->getPresenter()->storage, 'scriptLoader');
                $cssFile = $cache->load('css-' . $type . $this->getPostfix($isMobile));
                if(is_null($cssFile)){
                    //zminimalizuju
                    $cssFile = '';
                    $cssFiles = array();
                    if(!is_null($config[$type])){
                        foreach($config[$type] as $css){
                            $cssFile .= \CssMin::minify(file_get_contents($this->getPresenter()->context->parameters['wwwDir'] . '/' . $css));
                            $cssFiles[] = $this->getPresenter()->context->parameters['wwwDir'] . '/' . $css;
                        }
                    }

                    $cache->save('css-' . $type . $this->getPostfix($isMobile), $cssFile, array(
                        Cache::FILES => $cssFiles,
                    ));
                }

                echo('<style>' . $cssFile . '</style>');
            }
        }
    }
    
    public function renderCss($critical = false, $type = null, $isMobile = false){
        if($critical){
            $this->renderCssCritical($type, $isMobile);
        }else{
        
            $config = $this->getPresenter()->context->parameters['scriptLoader']['css' . $this->getPostfix($isMobile)];
            
            if(!$this->getPresenter()->context->parameters['scriptLoader']['enable']){
                if(!is_null($config['default'])){
                    foreach($config['default'] as $css){
                        echo '<link rel="stylesheet" media="screen,projection,tv" href="/' . $css . '">';
                    }
                }
            }else{

                $cache = new Cache($this->getPresenter()->storage, 'scriptLoader');
                if(is_null($cache->load('css' . $this->getPostfix($isMobile)))){
                    //zminimalizuju
                    $cssFile = '';
                    $cssFiles = array();
                    if(!is_null($config['default'])){
                        foreach($config['default'] as $css){
                            $cssFile .= \CssMin::minify(file_get_contents($this->getPresenter()->context->parameters['wwwDir'] . '/' . $css));
                            $cssFiles[] = $this->getPresenter()->context->parameters['wwwDir'] . '/' . $css;
                        }
                    }

                    $cache->save('css' . $this->getPostfix($isMobile), true, array(
                        Cache::FILES => $cssFiles,
                    ));

                    file_put_contents($this->getPresenter()->context->parameters['wwwDir'] . '/css/css'  . $this->getPostfix($isMobile) . '.css', $cssFile);
                }
                
                echo('<script>
          var cb = function() {
            var l = document.createElement(\'link\'); l.rel = \'stylesheet\';
            l.href = \'/css/css'  . $this->getPostfix($isMobile) . '.css\';
            var h = document.getElementsByTagName(\'head\')[0]; h.parentNode.insertBefore(l, h);
          };
          var raf = requestAnimationFrame || mozRequestAnimationFrame ||
              webkitRequestAnimationFrame || msRequestAnimationFrame;
          if (raf) raf(cb);
          else window.addEventListener(\'load\', cb);
        </script>');

                //echo '<link rel="stylesheet" media="screen,projection,tv" href="/css/css.css">';
            }
        }
    }
    
    public function renderJsCritical(){

        $config = $this->getPresenter()->context->parameters['scriptLoader']['js' . $this->getPostfix(false)];
        
        if(!$this->getPresenter()->context->parameters['scriptLoader']['enable']){
            foreach($config['critical'] as $js){
                echo '<script src="/' . $js . '"></script>';
            }
        }else{
        
            $cache = new Cache($this->getPresenter()->storage, 'scriptLoader');
            $jsFile = $cache->load('javascript-critical' . $this->getPostfix(false));
            if(is_null($jsFile)){
                //zminimalizuju
                $jsFile = '';
                $jsFiles = array();
                foreach($config['critical'] as $js){
                    $jsFile .= Minifier::minify(file_get_contents($this->getPresenter()->context->parameters['wwwDir'] . '/' . $js), array('flaggedComments' => false));
                    $jsFiles[] = $this->getPresenter()->context->parameters['wwwDir'] . '/' . $js;
                }
                
                $cache->save('javascript-critical' . $this->getPostfix(false), $jsFile, array(
                    Cache::FILES => $jsFiles,
                ));
            }

            echo '<script type="text/javascript">' . $jsFile . '</script>';
        }
    }
    
    public function renderJs($isMobile = false){
        $config = $this->getPresenter()->context->parameters['scriptLoader']['js' . $this->getPostfix($isMobile)];
        
        if(!$this->getPresenter()->context->parameters['scriptLoader']['enable']){
            if(!is_null($config)){
                foreach($config as $js){
                    echo '<script src="/' . $js . '"></script>';
                }
            }
        }else{
            $cache = new Cache($this->getPresenter()->storage, 'scriptLoader');
            if(is_null($cache->load('javascript' . $this->getPostfix($isMobile)))){
                //zminimalizuju
                $jsFile = '';
                $jsFiles = array();
                if(!is_null($config)){
                    foreach($config as $js){
                        $jsFile .= \JShrink\Minifier::minify(file_get_contents($this->getPresenter()->context->parameters['wwwDir'] . '/' . $js), array('flaggedComments' => false));
                        $jsFiles[] = $this->getPresenter()->context->parameters['wwwDir'] . '/' . $js;
                    }
                }
                
                $cache->save('javascript' . $this->getPostfix($isMobile), true, array(
                    Cache::FILES => $jsFiles,
                ));

                file_put_contents($this->getPresenter()->context->parameters['wwwDir'] . '/js/js' . $this->getPostfix($isMobile) . '.js', $jsFile);
            }

            /*echo('<script>
                var cb = function() {
                  var l = document.createElement(\'script\');
                  l.src = \'/js/js.js\';
                  var h = document.getElementsByTagName(\'head\')[0]; h.parentNode.insertBefore(l, h);
                };
                var raf = requestAnimationFrame || mozRequestAnimationFrame ||
                    webkitRequestAnimationFrame || msRequestAnimationFrame;
                if (raf) raf(cb);
                else window.addEventListener(\'load\', cb);
              </script>');*/
            
            echo '<script src="/js/js' . $this->getPostfix($isMobile) . '.js" defer></script>';
        }
    }
}
