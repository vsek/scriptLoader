<?php

namespace App\ScriptLoader;

use Nette\Application\UI\Control;
use Nette\Caching\Cache;

/**
 * Description of ScriptLoader
 *
 * @author Vsek
 */
class ScriptLoader extends Control{
    
    private $cache;
    
    public function renderCssCritical($type = null, $isMobile = false){

        
        $config = $this->getPresenter()->context->parameters['scriptLoader']['css' . ($isMobile ? '_mobile' : '')];

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
                $cssFile = $cache->load('css-' . $type . ($isMobile ? '_mobile' : ''));
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

                    $cache->save('css-' . $type . ($isMobile ? '_mobile' : ''), $cssFile, array(
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
        
            $config = $this->getPresenter()->context->parameters['scriptLoader']['css' . ($isMobile ? '_mobile' : '')];
            
            if(!$this->getPresenter()->context->parameters['scriptLoader']['enable']){
                if(!is_null($config['default'])){
                    foreach($config['default'] as $css){
                        echo '<link rel="stylesheet" media="screen,projection,tv" href="/' . $css . '">';
                    }
                }
            }else{

                $cache = new Cache($this->getPresenter()->storage, 'scriptLoader');
                if(is_null($cache->load('css' . ($isMobile ? '_mobile' : '')))){
                    //zminimalizuju
                    $cssFile = '';
                    $cssFiles = array();
                    if(!is_null($config['default'])){
                        foreach($config['default'] as $css){
                            $cssFile .= \CssMin::minify(file_get_contents($this->getPresenter()->context->parameters['wwwDir'] . '/' . $css));
                            $cssFiles[] = $this->getPresenter()->context->parameters['wwwDir'] . '/' . $css;
                        }
                    }

                    $cache->save('css' . ($isMobile ? '_mobile' : ''), true, array(
                        Cache::FILES => $cssFiles,
                    ));

                    file_put_contents($this->getPresenter()->context->parameters['wwwDir'] . '/css/css.css', $cssFile);
                }
                
                echo('<script>
          var cb = function() {
            var l = document.createElement(\'link\'); l.rel = \'stylesheet\';
            l.href = \'/css/css.css\';
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

        $config = $this->getPresenter()->context->parameters['scriptLoader']['js'];
        
        if(!$this->getPresenter()->context->parameters['scriptLoader']['enable']){
            foreach($config['critical'] as $js){
                echo '<script src="/' . $js . '"></script>';
            }
        }else{
        
            $cache = new Cache($this->getPresenter()->storage, 'scriptLoader');
            $jsFile = $cache->load('javascript-critical');
            if(is_null($jsFile)){
                //zminimalizuju
                $jsFile = '';
                $jsFiles = array();
                foreach($config['critical'] as $js){
                    $jsFile .= Minifier::minify(file_get_contents($this->getPresenter()->context->parameters['wwwDir'] . '/' . $js), array('flaggedComments' => false));
                    $jsFiles[] = $this->getPresenter()->context->parameters['wwwDir'] . '/' . $js;
                }
                
                $cache->save('javascript-critical', $jsFile, array(
                    Cache::FILES => $jsFiles,
                ));
            }

            echo '<script type="text/javascript">' . $jsFile . '</script>';
        }
    }
    
    public function renderJs($isMobile = false){
        $config = $this->getPresenter()->context->parameters['scriptLoader']['js' . ($isMobile ? '_mobile' : '')];
        
        if(!$this->getPresenter()->context->parameters['scriptLoader']['enable']){
            if(!is_null($config)){
                foreach($config as $js){
                    echo '<script src="/' . $js . '"></script>';
                }
            }
        }else{
            $cache = new Cache($this->getPresenter()->storage, 'scriptLoader');
            if(is_null($cache->load('javascript' . ($isMobile ? '_mobile' : '')))){
                //zminimalizuju
                $jsFile = '';
                $jsFiles = array();
                if(!is_null($config)){
                    foreach($config as $js){
                        $jsFile .= \JShrink\Minifier::minify(file_get_contents($this->getPresenter()->context->parameters['wwwDir'] . '/' . $js), array('flaggedComments' => false));
                        $jsFiles[] = $this->getPresenter()->context->parameters['wwwDir'] . '/' . $js;
                    }
                }
                
                $cache->save('javascript' . ($isMobile ? '_mobile' : ''), true, array(
                    Cache::FILES => $jsFiles,
                ));

                file_put_contents($this->getPresenter()->context->parameters['wwwDir'] . '/js/js' . ($isMobile ? '_mobile' : '') . '.js', $jsFile);
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
            
            echo '<script src="/js/js' . ($isMobile ? '_mobile' : '') . '.js" defer></script>';
        }
    }
}
