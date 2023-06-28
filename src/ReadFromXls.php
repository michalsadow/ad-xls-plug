<?php

declare(strict_types=1);

namespace Przeslijmi\AgileDataXlsPlug;

use Przeslijmi\AgileData\Exceptions\Operations\MapColumnsDestinationReuseException;
use Przeslijmi\AgileData\Exceptions\Operations\ReadFromMsOffice\CacheFileUriInaccessible;
use Przeslijmi\AgileData\Exceptions\Operations\ReadFromMsOffice\XlsxFileExtensionWrongException;
use Przeslijmi\AgileData\Exceptions\Operations\ReadFromMsOffice\XlsxFileReadingFopException;
use Przeslijmi\AgileData\Exceptions\Operations\ReadFromMsOffice\XlsxFileUriDonoexException;
use Przeslijmi\AgileData\Operations\OperationsInterface as MyInterface;
use Przeslijmi\AgileData\Operations\OperationsParent as MyParent;
use Przeslijmi\AgileData\Steps\Helpers\DataTypes;
use stdClass;
use Throwable;

/**
 * Operation that reads data from old XLS files.
 */
class ReadFromXls extends MyParent implements MyInterface
{

    /**
     * Operation key.
     *
     * @var string
     */
    protected static $opKey = 'c7JMT7Qb';

    /**
     * Only those fields are accepted for this operation.
     *
     * @var array
     */
    public static $operationFields = [
        'fileUriDrive',
        'fileUriDir',
        'fileUri',
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
        $locSta = 'Przeslijmi.AgileDataXlsPlug.ReadFromXls.';

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
        $fields = [];
        $loc    = $_ENV['LOCALE'];
        $locSta = 'Przeslijmi.AgileDataXlsPlug.ReadFromXls.fields.';

        // Convert multi field aggregation into form records.
        $mapColumnsRecords = self::packMultiFieldsIntoRecord($step, 'mapColumns', [
            'sourceColumn' => '',
            'destinationProp' => '',
            'dataType' => 'txt',
        ]);

        // Add fields.
        $fields[] = [
            'type' => 'fileChooser',
            'limitToExtension' => 'xls',
            'id' => 'fileUri',
            'value' => ( $step->fileUri ?? null ),
            'valueMore' => self::defineFileChooserValue(( $step->fileUri ?? '' )),
            'name' => $loc->get($locSta . 'fileUri.name'),
            'desc' => $loc->get($locSta . 'fileUri.desc'),
            'maxlength' => 500,
            'group' => $loc->get('Przeslijmi.AgileData.tabs.operation'),
            'htmlData' => [
                'xls-cache-uri-recalc' => 'cacheUri,fileUri,sheetName,dataRef',
            ],
        ];
        $fields[] = [
            'type' => 'text',
            'id' => 'sheetName',
            'value' => ( $step->sheetName ?? null ),
            'name' => $loc->get($locSta . 'sheetName.name'),
            'desc' => $loc->get($locSta . 'sheetName.desc'),
            'maxlength' => 500,
            'group' => $loc->get('Przeslijmi.AgileData.tabs.operation'),
            'htmlData' => [
                'xls-cache-uri-recalc' => 'cacheUri,fileUri,sheetName,dataRef',
            ],
        ];
        $fields[] = [
            'type' => 'text',
            'id' => 'dataRef',
            'value' => ( $step->dataRef ?? null ),
            'name' => $loc->get($locSta . 'dataRef.name'),
            'desc' => $loc->get($locSta . 'dataRef.desc'),
            'maxlength' => 500,
            'group' => $loc->get('Przeslijmi.AgileData.tabs.operation'),
            'htmlData' => [
                'xls-cache-uri-recalc' => 'cacheUri,fileUri,sheetName,dataRef',
            ],
        ];
        $fields[] = [
            'type' => 'multi',
            'id' => 'mapColumns',
            'allowAdding' => true,
            'allowDeleting' => true,
            'allowReorder' => true,
            'name' => $loc->get($locSta . 'mapColumns.name'),
            'desc' => $loc->get($locSta . 'mapColumns.desc'),
            'subFields' => [
                [
                    'name' => $loc->get($locSta . 'mapColumns.sourceColumn.name'),
                    'type' => 'select',
                    'id' => 'mapColumns_sourceColumn',
                    'options' => self::getColumnsLettersOptions(),
                ],
                [
                    'name' => $loc->get($locSta . 'mapColumns.destinationProp.name'),
                    'type' => 'text',
                    'id' => 'mapColumns_destinationProp',
                ],
                [
                    'name' => $loc->get($locSta . 'mapColumns.dataType.name'),
                    'type' => 'select',
                    'id' => 'mapColumns_dataType',
                    'options' => DataTypes::getLocale(),
                ],
            ],
            'values' => $mapColumnsRecords,
            'group' => $loc->get('Przeslijmi.AgileData.tabs.operation'),
        ];
        $fields[] = [
            'type' => 'text',
            'id' => 'cacheUri',
            'value' => ( $step->cacheUri ?? null ),
            'name' => $loc->get($locSta . 'cacheUri.name'),
            'desc' => $loc->get($locSta . 'cacheUri.desc'),
            'maxlength' => 500,
            'group' => $loc->get('Przeslijmi.AgileData.tabs.advanced'),
        ];

        return $fields;
    }

    /**
     * Prevalidator is optional in operation class and converts step if it is needed.
     *
     * @param stdClass $step Original step.
     *
     * @return stdClass Converted step.
     */
    public function preValidation(stdClass $step): stdClass
    {

        // Unpack mapColumns.
        if (isset($step->mapColumns) === false) {
            $step = $this->unpackMultiFieldsToRecords($step, 'mapColumns');
        }

        // Add human name.
        if (empty($step->humanName) === true && empty($step->fileUri) === false) {
            $step->humanName = self::getInfo()->name . ' (' . $step->fileUri . ')';
        }

        return $step;
    }

    /**
     * Validates plug definition.
     *
     * @throws XlsxFileUriDonoexException When XLSX file does not exist.
     * @throws XlsxFileExtensionWrongException When XLSX file is defined without .xlsx extension.
     * @throws CacheFileUriInaccessible When creation of cache file uri failed.
     * @throws MapColumnsDestinationReuseException When destination is reused.
     * @return void
     */
    public function validate(): void
    {

        // Test nodes.
        $this->testNodes($this->getStepPathInPlug(), $this->getStep(), [
            'fileUri' => '!string',
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

        // Test if file uri exists.
        if (file_exists($this->getStep()->fileUri) === false) {
            throw new XlsxFileUriDonoexException([
                $this->getTaskId(),
                $this->getStepId(),
                $this->getStep()->fileUri,
            ]);
        }

        // If file has proper extension.
        if (strtolower(substr($this->getStep()->fileUri, -4)) !== '.xls') {
            throw new XlsxFileExtensionWrongException([
                $this->getTaskId(),
                $this->getStepId(),
                $this->getStep()->fileUri,
            ]);
        }

        // Try to write to cache uri to check if that is possible.
        try {
            file_put_contents($this->getStep()->cacheUri, 'TEST');
            unlink($this->getStep()->cacheUri);
        } catch (Throwable $thr) {
            throw new CacheFileUriInaccessible([
                $this->getTaskId(),
                $this->getStepId(),
                $this->getStep()->cacheUri,
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

    /**
     * Reads data from XLSX file into task memory.
     *
     * @return void
     */
    public function perform(): void
    {

        // Lvd.
        $data  = [];
        $rowId = 0;

        // Get list of uris.
        if (isset($this->getStep()->fileUri) === true) {
            $fileUris = [ $this->getStep()->fileUri ];
        } elseif (isset($this->getStep()->dirUri) === true) {
            $fileUris = $this->findFilesInDir($this->getStep()->dirUri, '*.xls');
        }

        // Work on every URI.
        foreach ($fileUris as $fileUri) {

            // Lvd.
            $preData = $this->readFile($fileUri);

            // Do mapping fields is expected.
            if (( $maps = ( $this->getStep()->mapColumns ?? null ) ) !== null) {
                foreach ($preData as $key => $preRecord) {

                    // Create new record.
                    ++$rowId;
                    $record = $this->createEmptyRecord($rowId);

                    // Fill with mapped fields.
                    foreach ($maps as $map) {

                        // Lvd.
                        $value = null;

                        // Find value.
                        if (isset($preRecord->{$map->sourceColumn}) === true) {
                            $value = $preRecord->{$map->sourceColumn};
                        }

                        // Add data type - if neccessary.
                        $value = ( ( $value === null ) ? null : DataTypes::conv($value, $map->dataType) );

                        // Set value.
                        $record->properties->{$map->destinationProp} = $value;
                    }

                    // Save record.
                    $data[$rowId] = $record;
                }//end foreach
            }//end if

            // Free memory.
            $preData = [];
            unset($preData);
        }//end foreach

        // Prepare dataTypes.
        $dataTypes = array_combine(
            array_column($this->getStep()->mapColumns, 'destinationProp'),
            array_column($this->getStep()->mapColumns, 'dataType'),
        );

        // Save new records.
        $this->getCallingTask()->replaceRecords($data, $dataTypes);
    }

    /**
     * Delivers simple list of props that is available after this operation finishes work.
     *
     * @param array $inputProps Properties available in previous operation.
     *
     * @return array[]
     */
    public function getPropsAvailableAfter(array $inputProps): array
    {

        // Clean all previous props.
        $this->setProps($inputProps);
        $this->deleteProps($inputProps);

        // Add props from param.
        $this->addParamAsProp($this->getTask());

        // Add columns.
        foreach ($this->getStep()->mapColumns as $map) {
            $this->addProp($map->destinationProp, $map->dataType);
        }

        return $this->availableProps;
    }

    /**
     * Reads file - and creates cache if cache does not exists.
     *
     * @param string $fileUri File to be read.
     *
     * @throws XlsxFileReadingFopException When reading failed.
     * @return array
     */
    private function readFile(string $fileUri): array
    {

        // Get file modification time to check if cache is up to date.
        $xlsxModTime = date('Y-m-d-H-i-s', filemtime($fileUri));
        $cacheUri    = str_replace('[data]', '[' . $xlsxModTime . ']', $this->getStep()->cacheUri);

        // Make correction for dir uri.
        if (isset($this->getStep()->dirUri) === true) {
            $fileNameNoExt = mb_substr($fileUri, ( mb_strrpos($fileUri, '/') + 1 ));
            $fileNameNoExt = mb_substr($fileNameNoExt, 0, -5);
            $cacheUri      = str_replace('[file_name_no_ext]', $fileNameNoExt, $cacheUri);
        }

        // If cache uri is present - read it - otherwise make it.
        if (file_exists($cacheUri) === false) {

            // Lvd.
            $command  = $_ENV['PRZESLIJMI_AGILEDATA_CLI_PHP_CALL'];
            $command .= ' cli.php xlsFileToJson -s "%s" -d "%s" -ss "%s" -ssr "%s"';
            $command  = sprintf(
                $command,
                $fileUri,
                $cacheUri,
                $this->getStep()->sheetName,
                $this->getStep()->dataRef
            );

            // Call to create JSON cache out of XLSX.
            $resultOfCommand = shell_exec($command);
        }

        // If file still cache is not present - something went wrong.
        if (file_exists($cacheUri) === false) {
            throw new XlsxFileReadingFopException([
                $this->getTaskId(),
                $this->getStepId(),
                $fileUri,
                $cacheUri,
                $command,
                $resultOfCommand,
            ]);
        }

        // Read in cache file and create empy array for final data.
        return (array) json_decode(file_get_contents($cacheUri));
    }

    /**
     * Delivers table with column letters A, B, C, etc. till AZ for SELECT OPTION's.
     *
     * @return array
     */
    protected static function getColumnsLettersOptions(): array
    {

        // Lvd.
        $options = [];
        $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $num     = 0;

        // Prepare one-char variant.
        foreach (str_split($letters) as $letter) {
            $options[$letter] = $letter . ' (#' . ( ++$num ) . ')';
        }

        // Prepare two-char variant but only for some.
        foreach ([ 'A', 'B', 'C', 'D', 'E' ] as $first) {
            foreach (str_split($letters) as $second) {
                $options[( $first . $second )] = $first . $second . ' (#' . ( ++$num ) . ')';
            }
        }

        return $options;
    }
}
