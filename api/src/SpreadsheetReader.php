<?php
declare(strict_types=1);

namespace Avolle\Banebok;

use Avolle\Banebok\Api\Maatch;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * Class SpreadsheetReader
 */
class SpreadsheetReader
{
    /**
     * Options for the spreadsheet reader
     *
     * - hasHeaders - bool: Whether the spreadsheet has headers, indicating that data reading should start at row two
     * - headers - array: Maps the different Match entity properties to columns in the spreadsheet
     *
     * @var array
     */
    protected array $options = [
        'hasHeaders' => true,
        'headers' => [
            'date' => 'B',
            'day' => 'C',
            'time' => 'D',
            'homeTeam' => 'E',
            'result' => 'F',
            'awayTeam' => 'G',
            'pitch' => 'H',
            'tournament' => 'I',
            'matchId' => 'J',
            'playType' => 'K',
        ],
    ];

    /**
     * Filename where Spreadsheet contents are stored.
     *
     * @var string
     */
    protected string $filename;

    /**
     * Spreadsheet instance
     *
     * @var \PhpOffice\PhpSpreadsheet\Spreadsheet
     */
    private Spreadsheet $spreadsheet;

    /**
     * An array of Match entities
     *
     * @var \Avolle\Banebok\Api\Maatch[]
     */
    private array $matches;

    /**
     * SpreadsheetReader constructor.
     *
     * @param string $filename Filename to read spreadsheet from
     * @param array $options Options to use in the reader
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \Exception
     */
    public function __construct(string $body, array $options = [])
    {
        $filename = $this->saveToTmpFile($body);
        $this->options = $options + $this->options;
        $reader = new Xlsx();
        $reader->setReadDataOnly(true);

        $this->spreadsheet = $reader->load(TMP . $filename);

        $this->matches = $this->compileMatches();
    }

    /**
     * Save string body to a temporary Excel spreadsheet, since the library does not support loading by string.
     *
     * @param string $body Excel string body
     * @return string Saved filename
     * @throws \Exception
     */
    protected function saveToTmpFile(string $body): string
    {
        $filename = randomString() . '.xlsx';
        file_put_contents(TMP . $filename, $body);

        return $this->filename = $filename;
    }

    /**
     * Read matches from the spreadsheet and compile them into an array of Match entities
     *
     * @return array
     */
    private function compileMatches(): array
    {
        $firstRow = $this->firstRow();
        $lastRow = $this->lastRow();

        $matches = [];

        $spreadsheet = $this->spreadsheet->getActiveSheet();

        for ($row = $firstRow; $row <= $lastRow; $row++) {
            $dateValue = Date::excelToDateTimeObject(
                $spreadsheet->getCell($this->cell($this->options['headers']['date'], $row))->getValue()
            );
            $matches[] = new Maatch(
                $dateValue,
                $spreadsheet->getCell($this->cell($this->options['headers']['day'], $row))->getValue(),
                $spreadsheet->getCell($this->cell($this->options['headers']['time'], $row))->getValue(),
                $spreadsheet->getCell($this->cell($this->options['headers']['homeTeam'], $row))->getValue(),
                $spreadsheet->getCell($this->cell($this->options['headers']['result'], $row))->getValue(),
                $spreadsheet->getCell($this->cell($this->options['headers']['awayTeam'], $row))->getValue(),
                $spreadsheet->getCell($this->cell($this->options['headers']['pitch'], $row))->getValue(),
                $spreadsheet->getCell($this->cell($this->options['headers']['tournament'], $row))->getValue(),
                $spreadsheet->getCell($this->cell($this->options['headers']['playType'], $row))->getValue(),
                $spreadsheet->getCell($this->cell($this->options['headers']['matchId'], $row))->getValue(),
            );
        }

        unlink(TMP . $this->filename);

        return $matches;
    }

    /**
     * Evaluate which row contains data, so the spreadsheet can start reading from that row
     *
     * @return int
     */
    private function firstRow(): int
    {
        if ($this->options['hasHeaders']) {
            return 2;
        }

        return 1;
    }

    /**
     * Evaluate which row is the last to contain data
     *
     * @return int
     */
    private function lastRow(): int
    {
        return $this->spreadsheet->getActiveSheet()->getHighestDataRow();
    }

    /**
     * Create a cell string to be read, compiled from the input row and column
     *
     * @param string $column Column of cell
     * @param int $row Row of cell
     * @return string
     */
    private function cell(string $column, int $row): string
    {
        return sprintf("%s%s", $column, $row);
    }

    /**
     * Returns the compiled array of Match entities
     *
     * @return \Avolle\Banebok\Api\Maatch[]
     */
    public function getMatches(): array
    {
        return $this->matches;
    }
}
