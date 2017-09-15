<?php
/**
 * OData client library
 *
 * @author  Михаил Красильников <m.krasilnikov@yandex.ru>
 * @license MIT
 */
namespace Mekras\OData\Client;

use Mekras\Atom\Atom;
use Mekras\Atom\Document\Document;
use Mekras\Atom\Element\Content;
use Mekras\Atom\Element\Element;
use Mekras\Atom\Extension\DocumentExtension;
use Mekras\Atom\Extension\ElementExtension;
use Mekras\Atom\Extension\NamespaceExtension;
use Mekras\Atom\Extensions;
use Mekras\Atom\Node;
use Mekras\OData\Client\Document\EntryDocument;
use Mekras\OData\Client\Document\ErrorDocument;
use Mekras\OData\Client\Element\Entry;
use Mekras\OData\Client\Element\Properties;
use Mekras\OData\Client\Element\InlineFeedLink;

/**
 * OData extensions.
 *
 * @since 0.3
 */
class ODataExtension implements DocumentExtension, ElementExtension, NamespaceExtension
{
    /**
     * Create OData document from XML DOM document.
     *
     * @param Extensions   $extensions Extension registry.
     * @param \DOMDocument $document   Source document.
     *
     * @return Document|null
     *
     * @since 0.3
     */
    public function parseDocument(Extensions $extensions, \DOMDocument $document)
    {
        switch ($document->documentElement->namespaceURI) {
            case Atom::NS:
                switch ($document->documentElement->localName) {
                    case 'entry':
                        // Node name already checked
                        return new EntryDocument($extensions, $document);
                }
                break;

            case OData::META:
                switch ($document->documentElement->localName) {
                    case 'error':
                        // Node name already checked
                        return new ErrorDocument($extensions, $document);
                }
                break;
        }

        return null;
    }

    /**
     * Create new OData document.
     *
     * @param Extensions $extensions Extension registry.
     * @param string     $name       Element name.
     *
     * @return Document|null
     *
     * @since 0.3
     */
    public function createDocument(Extensions $extensions, $name)
    {
        switch ($name) {
            case 'atom:entry':
                // No document — no exception.
                return new EntryDocument($extensions);
        }

        return null;
    }

    /**
     * Create Atom node from XML DOM element.
     *
     * @param Node        $parent  Parent node.
     * @param \DOMElement $element DOM element.
     *
     * @return Element|null
     *
     * @since 0.3
     */
    public function parseElement(Node $parent, \DOMElement $element)
    {
        if (Atom::NS === $element->namespaceURI) {
            switch ($element->localName) {
                case 'entry':
                    // Node name already checked
                    return new Entry($parent, $element);
                case 'link':
                    // Node name already checked
                    return new InlineFeedLink($parent, $element);
            }
        } elseif (OData::META === $element->namespaceURI) {
            switch ($element->localName) {
                case 'properties':
                    /** @var Content $parent */
                    // Node name already checked
                    return new Properties($parent, $element);
                case 'link':
                    // Node name already checked
                    return new InlineFeedLink($parent, $element);
            }
        }

        return null;
    }

    /**
     * Create new Atom node.
     *
     * @param Node   $parent Parent node.
     * @param string $name   Element name.
     *
     * @return Element|null
     *
     * @throws \InvalidArgumentException If $element has invalid namespace.
     *
     * @since 0.3
     */
    public function createElement(Node $parent, $name)
    {
        switch ($name) {
            case 'atom:entry':
                return new Entry($parent);
            case 'm:properties':
                /** @var Content $parent */
                return new Properties($parent);
            case 'link':
                return new InlineFeedLink($parent);
        }

        return null;
    }

    /**
     * Return additional XML namespaces.
     *
     * @return string[] prefix => namespace.
     *
     * @since 0.3
     */
    public function getNamespaces()
    {
        return [
            'm' => OData::META,
            'd' => OData::DATA
        ];
    }
}
