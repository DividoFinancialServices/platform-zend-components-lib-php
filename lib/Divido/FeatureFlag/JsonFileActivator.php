<?php

declare(strict_types=1);

namespace Divido\FeatureFlag;

use Flagception\Activator\ArrayActivator;
use Flagception\Activator\FeatureActivatorInterface;
use Flagception\Model\Context;
use Psr\Log\LoggerInterface;

class JsonFileActivator implements FeatureActivatorInterface
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ArrayActivator|null
     */
    private $internalActivator;

    public function __construct(string $path, LoggerInterface $logger)
    {
        $this->path = $path;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'json';
    }

    /**
     * @inheritDoc
     */
    public function isActive($name, Context $context): bool
    {
        if (null === $this->internalActivator) {
            try {
                $this->internalActivator = new ArrayActivator($this->getFeatures());
            } catch (\Throwable $throwable) {
                $this->logger->warning(
                    'failed to load features',
                    [
                        'error' => $throwable->getMessage(),
                        'path' => $this->path,
                    ]
                );

                return false;
            }
        }

        return $this->internalActivator->isActive($name, $context);
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \JsonException
     */
    private function getFeatures(): array
    {
        if (!is_file($this->path)) {
            throw new \InvalidArgumentException('file is not valid');
        }

        if (!is_readable($this->path)) {
            throw new \InvalidArgumentException('file is not readable');
        }

        $json = json_decode(file_get_contents($this->path), true, 512, JSON_THROW_ON_ERROR);

        if (!isset($json['active'])) {
            throw new \InvalidArgumentException('no active features');
        }

        return $json['active'];
    }
}
