<?php

/**
 * (c) 2013 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\Workflow;

interface TokenInterface
{
    /**
     * Set the data
     *
     * @param string $key
     * @param mixed $data
     * @return $this
     */
    function setData($key, $data);

    /**
     * Return a single data item by key or all of the data
     *
     * @param $key
     * @return mixed
     */
    function getData($key = null);

    /**
     * Unset the data
     *
     * @param $key
     * @return $this
     */
    function unsetData($key);

    /**
     * Set the location
     *
     * @param string $location
     * @return $this
     */
    function setLocation($location);

    /**
     * Return the location
     *
     * @return NodeInterface
     */
    function getLocation();
}
