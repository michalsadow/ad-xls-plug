<?php

namespace Przeslijmi\AgileDataXlsPlug\CliApp;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Przeslijmi\AgileData\CliApp;
use Przeslijmi\AgileData\Tools\JsonSettings as Json;

/**
 * Converts XLS file into JSON files to make time consuming operations in different thread.
 */
class XlsToJsonCliApp extends CliApp
{

    /**
     * Aliasses for arguments.
     *
     * @var string
     */
    protected $aliasses = [
        's' => 'sourceUri',
        'd' => 'destinationUri',
        'ss' => 'sourceSheet',
        'ssr' => 'sourceSheetRefs',
    ];

    /**
     * Data read from file.
     *
     * @var array
     */
    private $data = [];

    /**
     * Performs operation.
     *
     * @return void
     */
    public function work(): void
    {

        // Get spreadsheet.
        $spreadsheet = IOFactory::load($this->args['sourceUri']);
        $sheet       = $spreadsheet->getSheetByName($this->args['sourceSheet']);

        // Recalc refs.
        preg_match('/^([A-Z]{1,3})([0-9]{1,5})(\:)([A-Z]{1,3})([0-9]{1,5})$/', $this->args['sourceSheetRefs'], $refs);

        // Get data.
        $this->getDataFromSheet($sheet, $refs);

        // Save to JSON file.
        file_put_contents(
            $this->args['destinationUri'],
            json_encode($this->data, Json::stdWrite())
        );
    }

    /**
     * Delivers data if it is inside sheet.
     *
     * @param Worksheet $sheet XLSX sheet element.
     * @param array     $refs  Array from `sourceSheetRefs` param regex.
     *
     * @return void
     */
    private function getDataFromSheet(Worksheet $sheet, array $refs): void
    {

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
                $this->data = $sheet->toArray(null, true, false, true);

                // Cut unneder rows.
                foreach ($this->data as $rowId => $row) {

                    // Cut unneded row.
                    if ($rowId < $refs[2]) {
                        unset($this->data[$rowId]);
                        continue;
                    }

                    // If this row remains - cut unneded columns.
                    $this->data[$rowId] = array_intersect_key($row, $oneRowKeys);
                }
            }//end if

        } else {
            $this->data = $sheet->rangeToArray($this->args['sourceSheetRefs'], null, true, false, true);
        }//end if
    }
}
