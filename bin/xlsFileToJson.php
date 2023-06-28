<?php

// Bootstrap.
$settings = require('.env.php');
chdir($settings['PRZESLIJMI_AGILEDATA_OPERATIONS_READFROMXLS_READER_URI']);
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

try {

    // Read from `$argv` array nicely into `$_ENV` table.
    readArgv($argv);

    // Start reader.
    require 'samples/Header.php';

    // Get spreasheet.
    $spreadsheet = IOFactory::load($_ENV['sourceUri']);
    $sheet       = $spreadsheet->getSheetByName($_ENV['sourceSheet']);
    $data        = [];

    // Recalc refs.
    preg_match('/^([A-Z]{1,3})([0-9]{1,5})(\:)([A-Z]{1,3})([0-9]{1,5})$/', $_ENV['sourceSheetRefs'], $refs);

    // Get data - if first row and last row are identical - read whole sheet.
    // If they differ - read only given range.
    if ((int) $refs[2] === (int) $refs[5]) {

        // Get one row - as columns specimen.
        $oneRowRefs = ( $refs[1] . $refs[2] . ':' . $refs[4] . $refs[2] );
        $oneRow     = array_values($sheet->rangeToArray($oneRowRefs, null, false, false, true));

        if (empty($oneRow) === false) {

            // Get one rows keys.
            $oneRowKeys = array_keys($oneRow[0]);
            $countKeys  = count($oneRowKeys);
            $oneRowKeys = array_combine($oneRowKeys, array_fill(0, $countKeys, null));

            // Get whole data.
            $data = $sheet->toArray(null, true, false, true);

            // Cut unneder rows.
            foreach ($data as $rowId => $row) {

                // Cut unneded row.
                if ($rowId < $refs[2]) {
                    unset($data[$rowId]);
                    continue;
                }

                // If this row remains - cut unneded columns.
                $data[$rowId] = array_intersect_key($row, $oneRowKeys);
            }
        }

    } else {
        $data = $sheet->rangeToArray($_ENV['sourceSheetRefs'], null, true, false, true);
    }

    // Convert data to json.
    $data = json_encode(
        $data,
        ( JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION | JSON_INVALID_UTF8_SUBSTITUTE )
    );

    // Save to JSON file.
    file_put_contents($_ENV['destinationUri'], $data);

} catch (\Throwable $thr) {

}

/**
 * Reads from `$argv` array nicely into `$_ENV` table.
 *
 * @param array $argv Argv as sent in commandline.
 *
 * @return void
 */
function readArgv($argv) : void {

    // Params.
    $paramsMap = [
        '-s' => 'sourceUri',
        '-d' => 'destinationUri',
        '-st' => 'sourceTable',
        '-ss' => 'sourceSheet',
        '-ssr' => 'sourceSheetRefs',
    ];

    // First one is php file uri.
    $argvs = ( ( count($argv) - 1 ) / 2 );

    // Take out keys and values.
    for ($a = 1; $a <= $argvs; ++$a) {

        $key   = $argv[(( $a * 2 ) - 1 )];
        $value = $argv[( $a * 2 )];

        $_ENV[( $paramsMap[$key] ?? $key )] = $value;
    }
}
