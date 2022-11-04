<?php
/**
 * Reference Field - Parse and do stuff on HTML DOM elements
 */

namespace simplicateca\referencefield\services;

use Craft;
use \craft\elements\Entry;
use craft\base\Component;

use simplicateca\referencefield\ReferenceField;

use Masterminds\HTML5;
use Symfony\Component\DomCrawler\Crawler;

class DomManipulator extends Component
{

    public function entryTypeAsReference( $htmlPageParts ) {

        $details = $htmlPageParts->variables['details'] ?? '';

        if( strstr( $details, 'id="entryType"' ) ) {
            $parts = $this->extractEntryTypeField( $details );

            if( !empty( $parts['field'] ) ) {
                $htmlPageParts->variables['details'] = $parts['html'];

                $typeId = $this->selectedEntryType( $parts['field'] );

                if( $typeId ) {
                    $type = collect( Craft::$app->sections->allEntryTypes ?? [] )
                    ->whereIn('id', $typeId)->first();

                    $typeIds = collect( Craft::$app->sections->allEntryTypes ?? [] )
                        ->mapWithKeys(function ($item, $key) {
                            return [$item->handle.'|'.$item->section->handle => $item->id];
                        })->toArray();

                    $references = ReferenceField::$instance->referenceFile->parse(
                        ReferenceField::$instance->getSettings()->entryTypeReferences
                    );

                    $types = collect( $references )
                                ->whereIn('section', $type->section->handle )
                                ->toArray();

                    foreach( $types AS &$t ) {
                        $t['id'] = $typeIds[$t['type'].'|'.$type->section->handle] ?? null;
                    }

                    $parts['field'] = $this->formatEntryTypeField( $parts['field'], $types );
                }

                $htmlPageParts->variables['contentNotice'] = $htmlPageParts->variables['contentNotice'] . $parts['field'];
            }
        }

        return $htmlPageParts;
    }



    public function extractEntryTypeField( $html ) {

        $crawler = $this->getCrawler( $html );
        $field   = '';

        if( $crawler->filter('body') && $crawler->filter('body')->children() ) {
            $html = '';
            $first = true;
            foreach( $crawler->filter('body')->children() AS $node ) {
                if( $first && $node->getAttribute('class') == 'meta' ) {
                    
                    $meta = new Crawler($node);

                    $html .= "<div class='meta'>";

                    foreach( $meta->children() AS $metaChild ) {
                        if( $metaChild->getAttribute('id') == 'entryType-field' ) {
                            $field = $metaChild->ownerDocument->saveHTML($metaChild);
                        } else {
                            $html .= $metaChild->ownerDocument->saveHTML($metaChild);
                        }
                    }

                    $html .= "</div>";

                } else {
                    $html .= $node->ownerDocument->saveHTML($node);
                }
            }
        }

        return [ 'field' => $field, 'html' => $html ];
    }

    private function getCrawler( $htmlString ) {
        $libxmlUseInternalErrors = \libxml_use_internal_errors(true);
        $content = \mb_convert_encoding($htmlString, 'HTML-ENTITIES', Craft::$app->getView()->getTwig()->getCharset());
        $doc = new \DOMDocument();
        $doc->loadHTML("<html><body>$content</body></html>", LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        \libxml_use_internal_errors($libxmlUseInternalErrors);
        return new Crawler($doc);
    }

    public function formatEntryTypeField( $field = null, $types = [] ) {

        if( $field && !empty($types) ) {

            $field = "<div class='referenceField'>$field</div>";
            $refs  = '<div class="referenceField__note referenceField__note--entryType">';

            foreach( $types AS $type ) {
                $id        = $type['reference']['id']        ?? $type['id']        ?? '';
                $note      = $type['reference']['note']      ?? $type['note']      ?? '';
                $image     = $type['reference']['image']     ?? $type['image']     ?? '';
                $html      = $type['reference']['html']      ?? $type['html']      ?? '';
                $video     = $type['reference']['video']     ?? $type['video']     ?? '';
                $title     = $type['reference']['title']     ?? $type['title']     ?? '';
                $moreUrl   = $type['reference']['moreUrl']   ?? $type['moreUrl']   ?? '#';
                $moreLabel = $type['reference']['moreLabel'] ?? $type['moreLabel'] ?? '';
                $refs .= "<div
                    data-value='$id'
                    data-image='$image'
                    data-video='$video'
                    data-html='$html'
                    data-title='$title'
                    data-note='$note'
                    data-more-url='$moreUrl'
                    data-more-label='$moreLabel'
                    class='note' style='display: none;'>

                    <div class='icon'>&#8627;</div>
                    <div class='reftext'>$note";
                
                if( !empty($image) || !empty($video) || !empty($html) ) {
                    $refs .= " <a href='$moreUrl'>See reference →</a>";
                }
                
                $refs .= "</div></div>";
            }

            $refs .= "</div>";
 
            $field = preg_replace( "/<\/div>\s*<\/div>\s*$/", "$refs</div></div>", $field );
            $field = str_replace( "<select", "<select class='referenceField'", $field );
        }

        return $field ?? '';
    }

    public function selectedEntryType( $field ) {

        $crawler  = $this->getCrawler( $field );
        $children = $crawler->filter('body select')->children() ?? [];

        foreach( $children AS $node ) {
            if( $node->getAttribute('selected') ) {
                return $node->getAttribute('value');
            }
        }

        return $children[0]->getAttribute('value') ?? null;
    }   
}