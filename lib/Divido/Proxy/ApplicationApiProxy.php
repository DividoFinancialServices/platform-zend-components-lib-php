<?php

namespace Divido\Proxy;

use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;

class ApplicationApiProxy
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @param ClientInterface $client
     * @param LoggerInterface $logger
     * @param EntityManagerInterface $entityManager
     */
    public function  __construct(
        ClientInterface $client,
        LoggerInterface $logger,
        EntityManagerInterface $entityManager
    ) {
        $this->client = $client;
        $this->logger = $logger;
        $this->entityManager = $entityManager;
    }

    /**
     * @param \Lender|null $lender
     *
     * @return bool
     */
    public function supports(?\Lender $lender): bool
    {
        if (null === $lender) {
            return false;
        }

        return $lender->getSettings()['supports_v2'] ?? false;
    }

    /**
     * @param \ApplicationActivation $activation
     *
     * @return bool
     */
    public function activate(\ApplicationActivation $activation): bool
    {
        try {
            $this->proxyEvent('activation', $activation->getId(), $activation->getApplication()->getId());
        } catch (\Exception $exception) {
            return false;
        }

        $this->entityManager->refresh($activation);

        return in_array($activation->getStatus(), ['ACTIVATED', 'AWAITING-ACTIVATION']);
    }

    /**
     * @param \ApplicationCancellation $cancellation
     *
     * @return bool
     */
    public function cancel(\ApplicationCancellation $cancellation): bool
    {
        try {
            $this->proxyEvent('cancellation', $cancellation->getId(), $cancellation->getApplication()->getId());
        } catch (\Exception $exception) {
            return false;
        }

        $this->entityManager->refresh($cancellation);

        return in_array($cancellation->getStatus(), ['CANCELLED', 'PENDING']);
    }

    /**
     * @param \ApplicationRefund $refund
     *
     * @return bool
     */
    public function refund(\ApplicationRefund $refund): bool
    {
        try {
            $this->proxyEvent('refund', $refund->getId(), $refund->getApplication()->getId());
        } catch (\Exception $exception) {
            return false;
        }

        $this->entityManager->refresh($refund);

        return in_array($refund->getStatus(), ['REFUNDED', 'PENDING']);
    }

    /**
     * @param string $eventType
     * @param string $eventId
     * @param string $applicationId
     *
     * @throws \Exception
     */
    private function proxyEvent(string $eventType, string $eventId, string $applicationId): void
    {
        $payload = [
            'data' => [
                'event' => $eventType,
                'data' => [
                    "application_{$eventType}_id" => $eventId,
                ]
            ]
        ];

        $context = ['event' => $eventType, "{$eventType}_id" => $eventId, 'application_id' => $applicationId];
        $this->logger->info("proxied application-api {$eventType} event", $context);
        $request = new Request('POST', '/event', [], json_encode($payload));

        try {
            $this->client->sendRequest($request);
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage(), $context);

            throw $exception;
        }
    }
}
