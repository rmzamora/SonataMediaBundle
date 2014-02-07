<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Sonata\MediaBundle\Document;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;

use Sonata\MediaBundle\Model\MediaManager as AbstractMediaManager;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\Pool;

class MediaManager extends AbstractMediaManager
{
    /**
     * @var DocumentManager
     */
    protected $dm;

    /**
     * Constructor.
     *
     * @param Pool            $pool
     * @param DocumentManager $dm
     * @param string          $class
     */
    public function __construct(Pool $pool, DocumentManager $dm, $class)
    {
        parent::__construct($pool, $class);

        $this->dm = $dm;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException When entity is an invalid object
     */
    public function save($entity, $andFlush = true)
    {
        /*
         * Warning: previous method signature was : save(MediaInterface $media, $context = null, $providerName = null)
         */

        if (!$entity instanceof MediaInterface) {
            throw new \InvalidArgumentException(sprintf(
                'Entity remove must be instance of Sonata\\MediaBundle\\Model\\MediaInterface, %s given',
                is_object($entity)? get_class($entity) : gettype($entity)
            ));
        }

        // BC compatibility for $context parameter
        if ($andFlush && is_string($andFlush)) {
            $entity->setContext($andFlush);
        }

        // BC compatibility for $providerName parameter
        if (3 == func_num_args()) {
            $entity->setProviderName(func_get_arg(2));
        }

        $this->dm->persist($entity);

        if ($andFlush && is_bool($andFlush) || 3 == func_num_args()) {
            $this->dm->flush();
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException When entity is an invalid object
     */
    public function delete($entity, $andFlush = true)
    {
        if (!$entity instanceof MediaInterface) {
            throw new \InvalidArgumentException(sprintf(
                'Entity remove must be instance of Sonata\\MediaBundle\\Model\\MediaInterface, %s given',
                is_object($entity)? get_class($entity) : gettype($entity)
            ));
        }

        $this->dm->remove($entity);

        if ($andFlush) {
            $this->dm->flush();
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return \Doctrine\MongoDB\Connection
     */
    public function getConnection()
    {
        return $this->dm->getConnection();
    }

    /**
     * Get the related collection name.
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->dm->getClassMetadata($this->class)->getCollection();
    }

    /**
     * {@inheritdoc}
     *
     * @return DocumentRepository
     */
    protected function getRepository()
    {
        return $this->dm->getRepository($this->class);
    }
}
