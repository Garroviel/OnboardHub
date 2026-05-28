<?php

namespace App\Service;

use App\Entity\Onboarding;
use App\Enum\SoftwareStatus;
use App\Enum\TaskStatus;
use App\Enum\TrainingStatus;

class OnboardingProgressCalculator
{
    public function recalculate(Onboarding $onboarding): int
    {
        $total = 0;
        $completed = 0;

        foreach ($onboarding->getTasks() as $task) {
            ++$total;

            if ($task->getStatus() === TaskStatus::DONE) {
                ++$completed;
            }
        }

        foreach ($onboarding->getTrainings() as $training) {
            ++$total;

            if ($training->getStatus() === TrainingStatus::DONE) {
                ++$completed;
            }
        }

        foreach ($onboarding->getSoftwareItems() as $softwareItem) {
            ++$total;

            if ($softwareItem->getStatus() === SoftwareStatus::DONE) {
                ++$completed;
            }
        }

        if ($total === 0) {
            $onboarding->setProgressPercent(0);

            return 0;
        }

        $progress = (int) round(($completed / $total) * 100);

        $onboarding->setProgressPercent($progress);

        return $progress;
    }
}