<?php

namespace App\ScriptLoader;

/**
 * Description of IScriptLoaderFactory
 *
 * @author Vsek
 */
interface IScriptLoaderFactory {
    /**
     * @return \App\ScriptLoader\ScriptLoader
     */
    function create();
}
