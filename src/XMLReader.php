<?php

namespace Cauditor;

use XMLReader as OriginalXMLReader;

/**
 * @author Matthias Mullie <cauditor@mullie.eu>
 * @copyright Copyright (c) 2016, Matthias Mullie. All rights reserved.
 * @license LICENSE MIT
 */
class XMLReader extends OriginalXMLReader
{
    /**
     * This is a blend of XMLReader's read & next, in that it accepts a node
     * name to search for, but it doesn't assume the node if on the same level
     * in DOM hierarchy as where you're currently at: it won't skip children &
     * it will cross parents (unless.
     *
     * @param string $name   Name of the node we're looking for.
     * @param string $parent Name of parent node(s) to stay inside of.
     *
     * @return bool
     */
    public function readNext($name, $parent = null)
    {
        while ($this->read()) {
            // next node
            if ($this->localName === $name && $this->nodeType === self::ELEMENT) {
                return true;
            }

            // stop processing at and of this node
            if ($this->localName === $parent && $this->nodeType === self::END_ELEMENT) {
                return false;
            }
        }

        return false;
    }
}
