<?php

namespace Liip\ImagineBundle\Imagine\Data\Loader;

interface LoaderInterface
{
    /**
     * @param string $path
     *
     * @return Imagine\Image\ImageInterface
     */
    function find($path);

    /**
     * @param string $path
     *
     * @return string
     */
    function getCacheTag($path);

    /**
     * @param string $path
     *
     * @return \DateTime
     */
    function getLastModified($path);
}
