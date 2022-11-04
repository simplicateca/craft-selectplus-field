<?php
/**
 * ReferenceField - Twig Extension
 * 
 * Allows access to load Reference files from twig by filename+value
 *
 */

namespace simplicateca\referencefield\twigextensions;

use simplicateca\referencefield\ReferenceField;

use Craft;

use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;

class Functions extends AbstractExtension
{

    public function getFunctions(): array
    {
        return [            
            new TwigFunction('referenceFile',  [$this, 'referenceFile']),
        ];
    }

    public function referenceFile( string $filename = null, string $value = null )
    {
        if( !$filename || !$value ) {
            return null;
        }
        
        $references = ReferenceField::$instance->referenceFile->parse( $filename );
        $reference  = collect( $references )->whereIn('value', $value)->first();

        return $reference ?? null;
    }
}