<?php

declare(strict_types=1);

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Chevere;

use OuterIterator;
use LogicException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Api provides tools to create and retrieve the App Api.
 */
class Api
{
    /** @var string Prefix used for endpoints without a defined resource (/endpoint) */
    const METHOD_ROOT_PREFIX = '_';

    /** @var array HTTP methods accepted by this filter [HTTP_METHOD,] */
    const ACCEPT_METHODS = Route::HTTP_METHODS;

    protected $pathIdentifier;

    /** @var array Route mapping [route => [http_method => Controller]]] */
    protected $routesMap;

    /** @var array Maps [endpoint => (array) resource [regex =>, description =>,]] (for wildcard routes) */
    protected $resourcesMap;

    /* @var array Maps [Controller => ControllerInspect] */
    protected $controllersMap;

    /** @var OuterIterator */
    protected $recursiveIterator;

    /* @var array Endpoint API properties */
    protected $api;

    /** @var string Target API directory (absolute) */
    protected $directory;

    /** @var Router The injected Router, needed to add Routes to the injector instance */
    protected $router;

    /** @var array Public exposed APIs groupped by basePath [basePath => [api],] */
    protected $apis;

    /** @var string The API basepath, like 'api' */
    protected $basePath;

    /** @var array Contains ['/api/route/algo' => [id, 'route/algo']] */
    protected $routeKeys;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Automatically finds controllers in the given path and generate the API route binding.
     *
     * @param string $pathIdentifier path identifier representing the dir containing API controllers (src/Api/)
     */
    public function register(string $pathIdentifier)
    {
        $this->pathIdentifier = Utils\Str::rtail($pathIdentifier, '/');
        $this->handleDuplicates();
        $this->directory = Path::fromHandle($this->pathIdentifier);
        $this->handleMissingDirectory();

        /* @var string The API directory (relative path) */
        // $directoryRelative = Path::relative($this->directory, App::APP);

        $this->routesMap = [];
        $this->resourcesMap = [];
        $this->controllersMap = [];
        $this->api = [];

        // Iterate the $this->directory filtering accepted filenames and folders
        $iterator = new RecursiveDirectoryIterator($this->directory, RecursiveDirectoryIterator::SKIP_DOTS);
        $filter = (new ApiFilterIterator($iterator))
            ->generateAcceptedFilenames(static::ACCEPT_METHODS, static::METHOD_ROOT_PREFIX);
        $this->recursiveIterator = new RecursiveIteratorIterator($filter);

        $this->processRecursiveIteration();

        $this->processRoutesMap();

        $apiRoute = Route::bind('/'.$this->basePath)
            ->setMethod('HEAD', Controllers\ApiHead::class)
            ->setMethod('OPTIONS', Controllers\ApiOptions::class)
            ->setMethod('GET', Controllers\ApiGet::class)
            ->setId($this->basePath);
        $this->getRouter()->addRoute($apiRoute, $this->basePath);
        $this->addApis($this->basePath, $this->api);
        $this->addRouteKeys($this->basePath);
    }

    protected function handleDuplicates(): void
    {
        if (isset($this->apis[$this->pathIdentifier])) {
            throw new LogicException(
                (string)
                    (new Message('Path identified by %s has been already bound.'))
                        ->code('%s', $this->pathIdentifier)
            );
        }
    }

    protected function handleMissingDirectory(): void
    {
        if (!File::exists($this->directory)) {
            throw new LogicException(
                (string)
                    (new Message("Directory %s doesn't exists."))
                        ->code('%s', $this->directory)
            );
        }
    }

    protected function processRecursiveIteration(): void
    {
        foreach ($this->recursiveIterator as $filename) {
            $filepathAbsolute = Utils\Str::forwardSlashes((string) $filename);
            $className = $this->getClassNameFromFilepath($filepathAbsolute);
            $inspected = new ControllerInspect($className);
            $this->controllersMap[$className] = $inspected;
            $pathComponent = $inspected->pathComponent;
            if ($inspected->useResource) {
                $this->resourcesMap[$pathComponent] = $inspected->resourcesFromString;
                /*
                 * For relationships we need to create the /endpoint/{id}/relationships/relation URLs.
                 * @see https://jsonapi.org/recommendations/
                 */
                if ($inspected->isRelatedResource) {
                    $this->routesMap[$inspected->relationshipPathComponent]['GET'] = $inspected->relationship;
                }
            }
            $this->routesMap[$pathComponent][$inspected->httpMethod] = $inspected->className;
        }
        $this->basePath = explode('/', $pathComponent)[0];
        ksort($this->routesMap);
    }

    protected function processRoutesMap(): void
    {
        foreach ($this->routesMap as $pathComponent => $httpMethods) {
            $endpointApi = [];
            $apiEndpoint = new ApiEndpoint($httpMethods);
            /** @var string Full qualified route key for $pathComponent like /api/users/{user} */
            $endpointRouteKey = Utils\Str::ltail($pathComponent, '/');
            $route = Route::bind($endpointRouteKey)->setId($pathComponent)->setMethods($apiEndpoint->getHttpMethods());
            // Define Route wildcard "where" if needed
            $resource = $this->resourcesMap[$pathComponent] ?? null;
            if (isset($resource)) {
                foreach ($resource as $wildcardKey => $resourceMeta) {
                    $route->setWhere($wildcardKey, $resourceMeta['regex']);
                }
                $apiEndpoint->setResource($resource);
            }
            $this->getRouter()->addRoute($route, $this->basePath);
            $this->api[$pathComponent] = $apiEndpoint->toArray();
        }
        ksort($this->api);
    }

    protected function addApis(string $basePath, array $api): void
    {
        $this->apis[$basePath] = $api;
    }

    protected function addRouteKeys(string $basePath): void
    {
        $this->routeKeys['/'.$basePath] = [$basePath];
    }

    /**
     * Returns the namespaced class name for the specified filepath.
     *
     * @param string $filepath the class filepath
     *
     * @return string the class name detected according autoloading standard (PSR-4)
     */
    protected function getClassNameFromFilepath(string $filepath): string
    {
        $filepathRelative = Path::relative($filepath);
        $filepathNoExt = Utils\Str::replaceLast('.php', null, $filepathRelative);
        $filepathReplaceNS = Utils\Str::replaceFirst(App\PATH.'src/', APP_NS_HANDLE, $filepathNoExt);

        return str_replace('/', '\\', $filepathReplaceNS);
    }

    public function getRouter(): Router
    {
        return $this->router;
    }

    public function getEndpoint(string $key): ?array
    {
        $routeKey = $this->routeKeys[$key] ?? null;
        if (isset($routeKey)) {
            $api = $this->get($routeKey[0]);
            if (isset($routeKey[1])) {
                return $api[$routeKey[1]];
            } else {
                return $api ?? null;
            }
        }

        return null;
    }

    /**
     * Gets the API key of the alleged endpoint key.
     */
    public function getEndpointApiKey(string $key): ?string
    {
        $routeKey = $this->routeKeys[$key] ?? null;
        if (isset($routeKey)) {
            return $routeKey[0];
        }

        return null;
    }

    /**
     * Get a exposed API array.
     *
     * @param string $key the API key (base pathName)
     */
    public function get(string $key = 'api'): ?array
    {
        return $this->apis[$key] ?? null;
    }

    public function getRouteKeys(): ?array
    {
        return $this->routeKeys ?? null;
    }
}
