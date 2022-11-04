<?php

namespace simplicateca\referencefield\models;

use craft\base\Model;

class Settings extends Model
{
    public $entryTypeReferences = '_sections/entrytypes.json';
    public $fileTypes = ['.json'];
    
    public function rules() : array
    {
        return [
            [['entryTypeReferences', 'fileTypes'], 'required'],
        ];
    }
}