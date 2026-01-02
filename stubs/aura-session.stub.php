<?php declare(strict_types=1);

namespace Aura\Session;

class Segment
{
    /**
     * Remove a key from the segment, or remove the entire segment from the session.
     *
     * Aura\Session has an incorrect PHPDoc in upstream source that says `@param null $key`,
     * but the implementation accepts string keys.
     *
     * @param string|null $key
     */
    public function remove(string|null $key = null): void
    {
    }
}
