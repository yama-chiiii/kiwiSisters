<?php
require '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

function listFiles($dir, $exts = []) {
  $files = [];
  foreach (scandir($dir) as $file) {
    if ($file === '.' || $file === '..') continue;
    $path = "$dir/$file";
    if (is_file($path)) {
      $ext = pathinfo($file, PATHINFO_EXTENSION);
      if (empty($exts) || in_array(strtolower($ext), $exts)) {
        $files[] = '/' . ltrim($path, '/');
      }
    } elseif (is_dir($path)) {
      $files = array_merge($files, listFiles($path, $exts));
    }
  }
  return $files;
}

function loadExcelScenario($path) {
  if (!file_exists($path)) return [];

  $spreadsheet = IOFactory::load($path);
  $sheet = $spreadsheet->getActiveSheet();
  $highestRow = $sheet->getHighestRow();

  $data = [];
  for ($rowNum = 2; $rowNum <= $highestRow; $rowNum++) {
    $row = $sheet->getRowIterator($rowNum, $rowNum)->current();
    $cellIterator = $row->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(false);

    $values = [];
    foreach ($cellIterator as $cell) {
      $values[] = (string) $cell->getValue();
    }

    $data[$rowNum] = [
      'background' => $values[0] ?? '',
      'character' => $values[1] ?? '',
      'text' => $values[2] ?? '',
      'next_state' => $values[3] ?? '',
      'illustration' => $values[4] ?? '',
      'illustration2' => $values[5] ?? '',
      'illustration3' => $values[6] ?? '',
      'illustration4' => $values[7] ?? '',
      'choice1' => $values[9] ?? '',
      'choice2' => $values[10] ?? '',
      'choice3' => $values[8] ?? '',
      'jumpTarget' => $values[11] ?? '',
      'correctjumpTarget' => $values[12] ?? '',
      'incorrectjumpTarget' => $values[13] ?? '',
      'bgm' => $values[14] ?? '',
      'se' => $values[15] ?? '',
      'end' => $values[16] ?? '',
    ];
  }
  return $data;
}

header('Content-Type: application/json');

// 静的ファイル
$imgFiles = listFiles('img', ['png', 'jpg', 'jpeg', 'gif', 'webp']);
$seFiles = listFiles('se', ['mp3', 'ogg', 'wav']);
$controllerFiles = listFiles('controller', ['php']);

// Excelシナリオの中身を読み込む
$scenarioData = [
  1 => loadExcelScenario('scenario/ScenarioPlay1.xlsx'),
  2 => loadExcelScenario('scenario/ScenarioPlay2.xlsx'),
  3 => loadExcelScenario('scenario/ScenarioPlay3.xlsx'),
  4 => loadExcelScenario('scenario/ScenarioPlay4.xlsx'),
];

echo json_encode([
  'images' => $imgFiles,
  'sounds' => $seFiles,
  'controllers' => $controllerFiles,
  'scenarios' => $scenarioData, // 👈 各章の中身入り！
], JSON_UNESCAPED_UNICODE);
