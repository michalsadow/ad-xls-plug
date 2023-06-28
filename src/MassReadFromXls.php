<?php

declare(strict_types=1);

namespace Przeslijmi\AgileDataXlsPlug;

use Przeslijmi\AgileData\Exceptions\Operations\MapColumnsDestinationReuseException;
use Przeslijmi\AgileData\Exceptions\Operations\ReadFromMsOffice\XlsxDirUriDonoexException;
use Przeslijmi\AgileData\Operations\OperationsInterface as MyInterface;
use Przeslijmi\AgileDataXlsPlug\ReadFromXls as MyParent;
use Przeslijmi\AgileData\Steps\Helpers\DataTypes;
use stdClass;

/**
 * Operation that reads data from old XLS files.
 */
class MassReadFromXls extends MyParent implements MyInterface
{

    /**
     * Operation key.
     *
     * @var string
     */
    protected static $opKey = 'RZstzr1x';

    /**
     * Only those fields are accepted for this operation.
     *
     * @var array
     */
    public static $operationFields = [
        'dirUriDrive',
        'dirUri',
        'sheetName',
        'dataRef',
        'mapColumns_sourceColumn_*',
        'mapColumns_destinationProp_*',
        'mapColumns_dataType_*',
        'cacheUri',
    ];

    /**
     * Get info (mainly name and category of this operation).
     *
     * @return stdClass
     */
    public static function getInfo(): stdClass
    {

        // Lvd.
        $locSta = 'Przeslijmi.AgileDataXlsPlug.MassReadFromXls.';

        // Lvd.
        $result             = new stdClass();
        $result->name       = $_ENV['LOCALE']->get($locSta . 'title');
        $result->vendor     = 'Przeslijmi\AgileDataXlsPlug';
        $result->class      = self::class;
        $result->depr       = false;
        $result->category   = 100;
        $result->sourceName = $_ENV['LOCALE']->get($locSta . 'sourceName');

        return $result;
    }

    /**
     * Deliver fields to edit settings of this operation.
     *
     * @param string        $taskId Id of task in which edited step is present.
     * @param stdClass|null $step   Opt. Only when editing step (when creating it is null).
     *
     * @return array
     *
     * @phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter
     */
    public static function getStepFormFields(string $taskId, ?stdClass $step = null): array
    {

        // Lvd.
        $fields = parent::getStepFormFields(...func_get_args());
        $loc    = $_ENV['LOCALE'];
        $locSta = 'Przeslijmi.AgileDataXlsPlug.MassReadFromXls.fields.';

        // Prepare field dirChooser.
        $dirChooser = [
            'type' => 'dirChooser',
            'id' => 'dirUri',
            'value' => ( $step->dirUri ?? null ),
            'valueMore' => self::defineFileChooserValue(( $step->dirUri ?? '' )),
            'name' => $loc->get($locSta . 'dirUri.name'),
            'desc' => $loc->get($locSta . 'dirUri.desc'),
            'maxlength' => 255,
            'group' => $loc->get('Przeslijmi.AgileData.tabs.operation'),
            'htmlData' => [
                'xls-cache-uri-recalc' => 'cacheUri,dirUri,sheetName,dataRef',
            ],
        ];

        // Correct fields.
        foreach ($fields as $fieldId => $field) {

            // Replace fileChooser field with dirChooser field.
            if ($field['type'] === 'fileChooser') {
                $fields[$fieldId] = $dirChooser;
            }

            // Replace fileUri with dirUri in xls-cache-uri-recalc.
            if (isset($field['htmlData']['xls-cache-uri-recalc']) === true) {
                $fields[$fieldId]['htmlData']['xls-cache-uri-recalc'] = str_replace(
                    'fileUri',
                    'dirUri',
                    $field['htmlData']['xls-cache-uri-recalc']
                );
            }
        }

        return $fields;
    }

    /**
     * Validates plug definition.
     *
     * @throws XlsxDirUriDonoexException When XLSX file does not exist.
     * @throws MapColumnsDestinationReuseException When destination is reused.
     * @return void
     */
    public function validate(): void
    {

        // Test nodes.
        $this->testNodes($this->getStepPathInPlug(), $this->getStep(), [
            'dirUri' => '!string',
            'sheetName' => '!string',
            'dataRef' => [ '!regex', [ '/^[A-Z]{1,3}[0-9]{1,5}\:[A-Z]{1,3}[0-9]{1,5}$/' ] ],
            'cacheUri' => '!string',
            'mapColumns' => '!array',
        ]);

        // Test each map columns definition.
        foreach ($this->getStep()->mapColumns as $colKey => $colDef) {
            $this->testNodes($this->getStepPathInPlug() . '@mapColumns*' . $colKey, $colDef, [
                'sourceColumn' => [ '!stringEnum', array_keys(self::getColumnsLettersOptions()) ],
                'destinationProp' => '!propName',
                'dataType' => [ '!stringEnum', DataTypes::get() ],
            ]);
        }

        // Test if dir uri exists.
        if (file_exists($this->getStep()->dirUri) === false) {
            throw new XlsxDirUriDonoexException([
                $this->getTaskId(),
                $this->getStepId(),
                $this->getStep()->dirUri,
            ]);
        }

        // You cannot write twice to the same destination.
        $destColumns = array_column($this->getStep()->mapColumns, 'destinationProp');
        if (count($destColumns) !== count(array_unique($destColumns))) {
            throw new MapColumnsDestinationReuseException([
                $this->getTaskId(),
                $this->getStepId(),
            ]);
        }
    }
}
