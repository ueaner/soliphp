<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli;

/**
 * Collection
 */
class Collection implements \ArrayAccess, \Countable, \Iterator
{
    protected $data;

    /**
     * Collection constructor.
     *
     * @param array $data
     */
    final public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Checks if the element is present in the Collection
     *
     * @param string|int $offset
     * @return bool
     */
    final public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * Returns an index in the Collection
     *
     * @param string|int $offset
     * @return mixed|null
     */
    final public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->data[$offset] : null;
    }

    /**
     * Sets an element in the Collection
     *
     * @param string|int $offset
     * @param $value
     */
    final public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    /**
     * Unsets an element in the Collection
     *
     * @param string|int $offset
     */
    final public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            unset($this->data[$offset]);
        }
    }

    /**
     * Checks how many elements are in the register
     */
    final public function count()
    {
        return count($this->data);
    }

    /**
     * Moves cursor to next row in the Collection
     */
    final public function next()
    {
        next($this->data);
    }

    /**
     * Gets pointer number of active row in the Collection
     */
    final public function key()
    {
        return key($this->data);
    }

    /**
     * Rewinds the Collection cursor to its beginning
     */
    final public function rewind()
    {
        reset($this->data);
    }

    /**
     * Checks if the iterator is valid
     */
    public function valid()
    {
        return key($this->data) !== null;
    }

    /**
     * Obtains the current value in the internal iterator
     */
    public function current()
    {
        return current($this->data);
    }

    final public function isEmpty()
    {
        return empty($this->data);
    }

    final public function toArray()
    {
        return is_array($this->data) ? $this->data : [];
    }
}
