<?php
/**
 * Atom Protocol support
 *
 * @author  Anton Boritskiy <anton.boritskiy@sitewards.com>
 * @license MIT
 */

namespace Mekras\OData\Client\Element;

use Mekras\Atom\Element\Link as BaseLink;
use Mekras\Atom\Element\Feed;
use Mekras\Atom\Node;

class InlineFeedLink extends BaseLink
{
    /**
     * Return title.
     *
     * @return Feed
     *
     * @throws \Mekras\Atom\Exception\MalformedNodeException If there is no required element.
     *
     * @since 1.0
     */
    public function getFeed()
    {
        return $this->getCachedProperty(
            'feed',
            function () {
                $element = $this->query('m:inline/atom:feed', Node::SINGLE);

                if ($element === null) {
                    return null;
                }

                /** @var Feed $this */
                return $this->getExtensions()->parseElement($this, $element);
            }
        );
    }

    /**
     * @param Feed $value
     *
     * @return $this
     */
    public function setFeed(Feed $value)
    {
        $inlineElement = $this->query('m:inline', self::SINGLE);
        if (null === $inlineElement) {
            $inlineElement = $this->getDomElement()->ownerDocument->createElement('m:inline');
            $this->getDomElement()->appendChild($inlineElement);
        }
        $currentFeed = $inlineElement->firstChild;
        if (null !== $currentFeed) {
            $inlineElement->removeChild($currentFeed);
        }

        $inlineElement->appendChild($value->getDomElement());
        $this->setCachedProperty('feed', $value);

        return $this;
    }
}
