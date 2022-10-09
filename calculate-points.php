<?php

require_once 'vendor/autoload.php';
use parallel\Runtime;
use parallel\Channel;

$repository = new \Alura\Threads\Student\InMemoryStudentRepository();
$studentList = $repository->all();

$totalPoints = 0;
$runTimes = [];
$futures = [];
$channel = Channel::make('points');
foreach($studentList as $key => $student) {
    $activities = $repository->activitiesInADay($student);

    $runTimes[$key] = new Runtime(__DIR__ . '/vendor/autoload.php');

    $futures[] = $runTimes[$key]->run(function (array $activities, \Alura\Threads\Student\Student $student, Channel $channel) {
         $points = array_reduce(
            $activities,
            fn (int $total, \Alura\Threads\Activity\Activity $activity) => $total + $activity->points(),
            0
        );

        $channel->send($points);

        printf('%s made %d poinst today%s', $student->fullName(), $points, PHP_EOL);

        return $points;
    }, [$activities, $student, $channel]);

}

$totalPointsWithChannel = 0;
for($i = 0; $i < count($studentList); $i++) {
    $totalPointsWithChannel += $channel->recv();
}

$channel->close();

$totalPoints = array_reduce($futures, fn($totalPoints, $future) => $totalPoints += $future->value(), 0) . PHP_EOL;

printf('We had a total of %d points today%s', $totalPointsWithChannel, PHP_EOL);
printf('We had a total of %d points today%s', $totalPoints, PHP_EOL);
