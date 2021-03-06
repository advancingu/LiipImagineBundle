<?php

namespace Liip\ImagineBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;

class ImagineController
{
    /**
     * @var DataManager
     */
    protected $dataManager;

    /**
     * @var FilterManager
     */
    protected $filterManager;

    /**
     * @var CacheManager
     */
    protected $cacheManager;

    /**
     * Constructor
     *
     * @param DataManager $dataManager
     * @param FilterManager $filterManager
     * @param CacheManager $cacheManager
     */
    public function __construct(DataManager $dataManager, FilterManager $filterManager, CacheManager $cacheManager)
    {
        $this->dataManager = $dataManager;
        $this->filterManager = $filterManager;
        $this->cacheManager = $cacheManager;
    }

    /**
     * This action applies a given filter to a given image,
     * optionally saves the image and
     * outputs it to the browser at the same time
     *
     * @param Request $request
     * @param string $path
     * @param string $filter
     *
     * @return Response
     */
    public function filterAction(Request $request, $path, $filter)
    {
        $targetPath = $this->cacheManager->resolve($request, $path, $filter);
        if ($targetPath instanceof Response) {
            return $targetPath;
        }
        
        $imageCacheTag = $this->dataManager->getCacheTag($filter, $path);
        $filterVersion = $this->filterManager->getFilterVersion($filter);
        
        $eTag = $imageCacheTag.$filterVersion;
        
        $response = new Response();
        $response->setETag($eTag);
        
        if ($response->isNotModified($request))
        {
            // return the 304 Response immediately
            return $response;
        }

        $image = $this->dataManager->find($filter, $path);
        $response = $this->filterManager->get($request, $filter, $image, $path);

        $response->setETag($eTag);
        
        if ($targetPath) {
            $response = $this->cacheManager->store($response, $targetPath, $filter);
        }

        return $response;
    }
}
