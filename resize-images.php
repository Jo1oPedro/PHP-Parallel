<?php

use Alura\Threads\Student\InMemoryStudentRepository;

require 'vendor/autoload.php';
use parallel\Runtime;

$repository = new InMemoryStudentRepository();
$studentList = $repository->all();

$studentChunks = array_chunk($studentList, ceil(count($studentList) / 4));

$runTimes = [];

foreach($studentChunks as $key => $studentChunk) {

    $runTimes[$key] = new Runtime(__DIR__ . '/vendor/autoload.php');
    $runTimes[$key]->run(function (array $students) {
        foreach($students as $student) {
            echo 'Resizing ' . $student->fullName() . ' profile picture' . PHP_EOL;

            $profilePicturePath = $student->profilePicturePath();
            [$width, $height] = getimagesize($profilePicturePath);

            $ratio = $height / $width;

            $newWidth = 200;
            $newHeight = 200 * $ratio;

            $sourceImage = imagecreatefromjpeg($profilePicturePath);
            $destinationImage = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($destinationImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            imagejpeg($destinationImage, __DIR__ . '/storage/resized/' . basename($profilePicturePath));
            echo 'Finished resizing '.  $student->fullName() . ' profile picture' . PHP_EOL;
        }
    }, [$studentChunk]);
    //resizeTo200PixelsWidth($student->profilePicturePath());

}

foreach($runTimes as $runTime) {
    $runTime->close();
}

echo 'Finalizando Thread principal' . PHP_EOL;

function resizeTo200PixelsWidth($imagePath)
{
    [$width, $height] = getimagesize($imagePath);

    $ratio = $height / $width;

    $newWidth = 200;
    $newHeight = 200 * $ratio;

    $sourceImage = imagecreatefromjpeg($imagePath);
    $destinationImage = imagecreatetruecolor($newWidth, $newHeight);
    imagecopyresampled($destinationImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    imagejpeg($destinationImage, __DIR__ . '/storage/resized/' . basename($imagePath));
}
