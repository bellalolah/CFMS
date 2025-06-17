<?php

function loadGlobalSecret(): void
{
    if (!isset($GLOBALS['superSecretKey'])) {
        $GLOBALS['superSecretKey'] = 'iEt0Dycnb4JNYHLtkVBnzfmPdISI+RXM/Utu5sAjAGfE789dTGHrclmnh4pPBGCZZusYdYWnMt9wdLIL2tLarA==';
    }
}

function getSecretKey() {
    loadGlobalSecret();
    return $GLOBALS['superSecretKey'];
}
