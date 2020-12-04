<?php

declare(strict_types=1);

namespace Divido\BatchProcessor;

class NordeaFinlandProcessor
{
    /**
     * @var \PDO
     */
    private $connection;

    /**
     * @var \Lenders_NordeaFinland
     */
    private $lenderApp;

    public function __construct(\PDO $connection, \Lenders_NordeaFinland $lenderApp)
    {
        $this->connection = $connection;
        $this->lenderApp = $lenderApp;
    }

    /**
     * Automatically activate ready applications with a current or past delivery date
     */
    public function processScheduledActivations(): void
    {
        $statement = $this->connection->prepare(
            "SELECT `application`.`id`
                FROM `application`
                INNER JOIN `application_submission` 
                    ON (`application_submission`.`id` = `application`.`application_submission_id`)
                INNER JOIN `lender` ON (`application_submission`.`lender_id` = `lender`.`id`)
                WHERE `application`.`status` = :status
                AND `lender`.`app_name` = :lender
                AND DATE(`metadata`->>'$.delivery_date') <= :today"
        );

        $statement->execute([
            ':status' => 'READY',
            ':lender' => 'NordeaFinland',
            ':today' => date('Y-m-d'),
        ]);

        $activations = $statement->fetchAll();

        if (count($activations) > 0) {
            $this->lenderApp->handleActivations($activations);
        }
    }
}
