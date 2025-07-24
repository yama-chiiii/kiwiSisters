<?php
$memoryFragments = ["enaga","kijima","takamori", "shirasagi"];
$correctOrder = ["enaga"];

$isRecovered = ($memoryFragments === $correctOrder);

if ($isRecovered) {
    echo "あの日の記憶を取り戻す";
} else {
    echo "もう何も思い出せない…";
}

