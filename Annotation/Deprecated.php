<?php
declare(strict_types=1);

namespace Shopping\ApiTKDeprecationBundle\Annotation;

use DateTime;

/**
 * Class Deprecated
 *
 * @package Shopping\ApiTKDeprecationBundle\Annotation
 * @Annotation
 */
class Deprecated
{
    /**
     * @var DateTime|null
     */
    private $removedAfter;

    /**
     * @var DateTime|null
     */
    private $since;

    /**
     * @var string|null
     */
    private $description;

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
            $this->removedAfter = isset($options['removedAfter']) ? new DateTime($options['removedAfter']) : null;
            $this->since = isset($options['since']) ? new DateTime($options['since']) : null;
            $this->description = $options['description'] ?? null;
            $this->hideInDoc = $options['hideInDoc'] ?? false;
        }
    }

    /**
     * @return DateTime|null
     */
    public function getRemovedAfter(): ?DateTime
    {
        return $this->removedAfter;
    }

    /**
     * @return bool
     */
    public function isHiddenInDoc(): bool
    {
        return $this->hideInDoc;
    }

    /**
     * @return DateTime|null
     */
    public function getSince(): ?DateTime
    {
        return $this->since;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }
}
