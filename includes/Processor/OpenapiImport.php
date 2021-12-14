<?php

/**
 * Class OpenapiImport.
 *
 * @package    ApiOpenStudio
 * @subpackage Processor
 * @author     john89 (https://gitlab.com/john89)
 * @copyright  2020-2030 Naala Pty Ltd
 * @license    This Source Code Form is subject to the terms of the ApiOpenStudio Public License.
 *             If a copy of the license was not distributed with this file,
 *             You can obtain one at https://www.apiopenstudio.com/license/.
 * @link       https://www.apiopenstudio.com
 */

namespace ApiOpenStudio\Processor;

use ADOConnection;
use ApiOpenStudio\Core\ApiException;
use ApiOpenStudio\Core\Config;
use ApiOpenStudio\Core\DataContainer;
use ApiOpenStudio\Core\MonologWrapper;
use ApiOpenStudio\Core\OpenApi\OpenApiParentAbstract;
use ApiOpenStudio\Core\OpenApi\OpenApiPathAbstract;
use ApiOpenStudio\Core\ProcessorEntity;
use ApiOpenStudio\Core\Request;
use ApiOpenStudio\Core\Utilities;
use ApiOpenStudio\Db\AccountMapper;
use ApiOpenStudio\Db\ApplicationMapper;
use ApiOpenStudio\Db\ResourceMapper;
use stdClass;
use Symfony\Component\Yaml\Yaml;

/**
 * Class OpenapiImport
 *
 * Import an OpenApi document and update the documentation on existing resources or create stubs if they do not exist.
 */
class OpenapiImport extends ProcessorEntity
{
    /**
     * {@inheritDoc}
     *
     * @var array Details of the processor.
     */
    protected array $details = [
        'name' => 'OpenApi Import',
        'machineName' => 'openapi_import',
        //phpcs:ignore
        'description' => 'Import an OpenApi document and update the documentation on existing resources or create stubs if they do not exist.',
        'menu' => 'Documentation',
        'input' => [
            'openapi_document' => [
                'description' => 'The OpenApi document.',
                'cardinality' => [1, 1],
                'literalAllowed' => false,
                'limitProcessors' => [],
                'limitTypes' => ['text', 'json'],
                'limitValues' => [],
                'default' => '',
            ],
        ],
    ];

    /**
     * @var AccountMapper
     */
    protected AccountMapper $accountMapper;

    /**
     * @var ApplicationMapper
     */
    protected ApplicationMapper $applicationMapper;

    /**
     * @var ResourceMapper
     */
    protected ResourceMapper $resourceMapper;

    /**
     * @var mixed
     */
    protected $openApiParent;

    /**
     * @var mixed
     */
    protected $openApiPath;

    /**
     * OpenapiRead constructor.
     *
     * @param $meta
     * @param Request $request
     * @param ADOConnection|null $db
     * @param MonologWrapper|null $logger
     *
     * @throws ApiException
     */
    public function __construct($meta, Request &$request, ADOConnection $db = null, MonologWrapper $logger = null)
    {
        parent::__construct($meta, $request, $db, $logger);
        $this->accountMapper = new AccountMapper($db, $logger);
        $this->applicationMapper = new ApplicationMapper($db, $logger);
        $this->resourceMapper = new ResourceMapper($db, $logger);
        $settings = new Config();
        try {
            $openapiVersion = $settings->__get(['api', 'openapi_version']);
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), 2, $this->id, 400);
        }
        $openApiParentClassPath = Utilities::getOpenApiParentClassPath($settings);
        $openApiPathClassPath = Utilities::getOpenApiParentClassPath($settings);
        $this->openApiParent = new $openApiParentClassPath();
        $this->openApiPath = new $openApiPathClassPath();
    }

    /**
     * {@inheritDoc}
     *
     * @return DataContainer Result of the processor.
     *
     * @throws ApiException Exception if invalid result.
     */
    public function process(): DataContainer
    {
        parent::process();

        $openapiDocument = $this->val('openapi_document');
        if ($openapiDocument->getType() == 'json') {
            $parent = json_decode($openapiDocument->getData());
            if (empty($parent)) {
                throw new ApiException('unable to decode the JSON', 6, $this->id, 400);
            }
        } else {
            $parent = Yaml::parse($openapiDocument->getData());
            if (empty($parent)) {
                throw new ApiException('unable to decode the YAML', 6, $this->id, 400);
            }
        }
        $parent = json_decode(json_encode($parent, JSON_UNESCAPED_SLASHES));

        $paths = $parent->paths ?? new stdClass();
        $parent->paths = new stdClass();
        $this->openApiParent->import($parent);
        $this->openApiPath->import($paths);

        try {
            $accountName = $this->openApiParent->getAccount();
            $applicationName = $this->openApiParent->getApplication();
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), 6, $this->id, 400);
        }
        $account = $this->accountMapper->findByName($accountName);
        if (empty($accid = $account->getAccid())) {
            throw new ApiException("unable to find account: $accountName", 6, $this->id, 400);
        }
        $application = $this->applicationMapper->findByAccidAppname($accid, $applicationName);
        if (empty($appid = $application->getAppid())) {
            $message = "unable to find application (account: $accountName): $applicationName";
            throw new ApiException($message, 6, $this->id, 400);
        }

        // Only developers for an application can use this processor.
        $roles = Utilities::getRolesFromToken();
        $permitted = false;
        foreach ($roles as $role) {
            if ($role['appid'] == $appid && $role['role_name'] == 'Developer') {
                $permitted = true;
            }
        }
        if (!$permitted) {
            throw new ApiException('permission denied', 4, 403);
        }

        $application->setOpenapi($this->openApiParent->export());
        try {
            $this->applicationMapper->save($application);
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), 2, $this->id, 400);
        }

        $result = [];
        foreach ((array) $paths as $uri => $methods) {
            foreach ((array) $methods as $method => $body) {
                $trimmedUri = trim(preg_replace('/\/\{.*\}/', '', $uri), '/');
                $resource = $this->resourceMapper->findByAppIdMethodUri($appid, $method, $trimmedUri);
                if (empty($resource->getResid())) {
                    $resource->setUri($trimmedUri);
                    $resource->setMethod($method);
                    $resource->setAppId($appid);
                    $resource->setName($body->summary);
                    $resource->setDescription($body->description);
                    $resource->setOpenapi(json_encode([$uri => [$method => $body]], JSON_UNESCAPED_SLASHES));
                    try {
                        $this->resourceMapper->save($resource);
                    } catch (ApiException $e) {
                        throw new ApiException($e->getMessage(), 2, $this->id, 400);
                    }
                    $result['new'][] = [
                        'uri' => $trimmedUri,
                        'method' => $method,
                        'account' => $account->getName(),
                        'application' => $application->getName(),
                    ];
                } else {
                    $resource->setOpenapi(json_encode([$uri => [$method => $body]], JSON_UNESCAPED_SLASHES));
                    try {
                        $this->resourceMapper->save($resource);
                    } catch (ApiException $e) {
                        throw new ApiException($e->getMessage(), 2, $this->id, 400);
                    }
                    $result['updated'][] = [
                        'uri' => $trimmedUri,
                        'method' => $method,
                        'account' => $account->getName(),
                        'application' => $application->getName(),
                    ];
                }
            }
        }

        return new DataContainer($result, 'array');
    }
}
