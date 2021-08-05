<?php

declare(strict_types=1);

namespace Divido\Nordea\AuthCode;

use Application;
use ApplicationSubmission;
use Doctrine\ORM\EntityManagerInterface;

class DatabaseStorage implements AuthCodeStorage
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function get(Application $application): AuthCode
    {
        $data = $application->getLenderData('access_key') ?? [];

        return new AuthCode(
            $data['key'] ?? null,
            $data['expire_at'] ?? time() - 3600
        );
    }

    public function persist(Application $application, AuthCode $authCode): void
    {
        $submission = $this->getOrCreateSubmission($application);
        $submission->setLenderData(
            [
                'access_key' => [
                    'key' => $authCode->getValue(),
                    'expire_at' => $authCode->getExpiry(),
                ]
            ]
        );

        $this->entityManager->persist($submission);
        $this->entityManager->flush();
    }

    public function revoke(Application $application): void
    {
        $this->persist($application, new AuthCode(null, time() - 3600));
    }

    private function getOrCreateSubmission(Application $application): ApplicationSubmission
    {
        $submission = $application->getSubmission();

        if (null === $submission) {
            $submission = new ApplicationSubmission();
            $submission->setApplication($application);
            $submission->setLender($application->getLender());
            $submission->setStatus('UNSUBMITTED');
            $application->setSubmission($submission);

            $this->entityManager->persist($application);
        }

        return $submission;
    }
}
