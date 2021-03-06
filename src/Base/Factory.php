<?php namespace SDK\Base;

use SDK\Base\Exceptions\BaseException;
use Illuminate\Contracts\Cache\Repository;
use SDK\Base\Exceptions\ArgumentsException;

class Factory
{
    /**
     * Mapping between
     * @var string
     */
    protected $actions_namespace;

    /**
     * @var array
     */
    protected $actions = [];

    /**
     * @var ApiContext
     */
    private $context;

    /**
     * @var Repository
     */
    private $cache;

    /**
     * itTaxiAPI constructor.
     *
     * @param ApiContext $context
     * @param Repository $cache
     */
    public function __construct(ApiContext $context, Repository $cache)
    {
        $this->context = $context;
        $this->cache = $cache;
    }


    /**
     * @param string $entity
     * @param int $statusCode
     * @param array $responseBody
     *
     * @return \SDK\Base\Action
     */
    public function fake($entity, $statusCode, array $responseBody)
    {
        $restEntity = $this->getActionClass($entity);

        return $restEntity::fake($this->context, $this->cache, $statusCode, json_encode($responseBody));
    }

    /**
     * @param $entity
     *
     * @return \SDK\Base\Action
     */
    public function make($entity)
    {
        $restEntity = $this->getActionClass($entity);

        return new $restEntity($this->context, $this->cache);
    }

    /**
     * @param $entity
     *
     * @return mixed
     * @throws ArgumentsException
     */
    protected function getActionClass($entity)
    {
        self::validateAction($entity);

        return $this->actions_namespace . '\\' . $this->actions[$entity];
    }

    /**
     * @param $entity
     *
     * @throws BaseException
     */
    private function validateAction($entity)
    {
        if (!in_array($entity, array_keys($this->actions)))
            throw new BaseException('Factory error, action not instantiable');
    }
}