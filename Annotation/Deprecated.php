<?php
declare(strict_types=1);

namespace Shopping\ApiTKDeprecationBundle\Annotation;

/**
 * Class Deprecated
 *
 * @package Shopping\ApiTKDeprecationBundle\Annotation
 * @Annotation
 */
class Deprecated
{
    /**
     * @var \DateTime|null
     */
    private $removedAfter = null;

    /**
     * @var bool
     */
    private $hideInDoc = false;

    /**
     * @param null|array $options
     */
    public function __construct($options = null)
    {
        if (is_array($options)) {
            $this->removedAfter = isset($options['removedAfter']) ? new \DateTime($options['removedAfter']) : null;
            $this->hideInDoc = $options['hideInDoc'] ?? false;
        }
    }

    /**
     * @return \DateTime|null
     */
    public function getRemovedAfter(): ?\DateTime
    {
        return $this->removedAfter;
    }

    public function isHiddenInDoc(): bool
    {
        return $this->hideInDoc;
    }
}