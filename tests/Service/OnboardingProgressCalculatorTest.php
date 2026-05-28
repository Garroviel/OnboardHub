<?php

namespace App\Tests\Service;

use App\Entity\Onboarding;
use App\Entity\OnboardingSoftware;
use App\Entity\OnboardingTask;
use App\Entity\OnboardingTraining;
use App\Enum\SoftwareStatus;
use App\Enum\TaskStatus;
use App\Enum\TrainingStatus;
use App\Service\OnboardingProgressCalculator;
use PHPUnit\Framework\TestCase;

final class OnboardingProgressCalculatorTest extends TestCase
{
    public function testProgressIsZeroWhenNoItemsExist(): void
    {
        $onboarding = new Onboarding();

        $calculator = new OnboardingProgressCalculator();

        $progress = $calculator->recalculate($onboarding);

        self::assertSame(0, $progress);
        self::assertSame(0, $onboarding->getProgressPercent());
    }

    public function testProgressIsCalculatedAcrossTasksTrainingsAndSoftware(): void
    {
        $onboarding = new Onboarding();

        $task = new OnboardingTask();
        $task->setStatus(TaskStatus::DONE);
        $onboarding->addTask($task);

        $training = new OnboardingTraining();
        $training->setStatus(TrainingStatus::TODO);
        $onboarding->addTraining($training);

        $software = new OnboardingSoftware();
        $software->setStatus(SoftwareStatus::DONE);
        $onboarding->addSoftwareItem($software);

        $calculator = new OnboardingProgressCalculator();

        $progress = $calculator->recalculate($onboarding);

        self::assertSame(67, $progress);
        self::assertSame(67, $onboarding->getProgressPercent());
    }
}