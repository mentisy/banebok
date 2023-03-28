<?php
declare(strict_types=1);

namespace Avolle\Banebok\Api;

use Avolle\Banebok\Exception\FotballApiException;
use Avolle\Banebok\SpreadsheetReader;
use Cake\Chronos\Chronos;
use Cake\Http\Client;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Matches class
 *
 * Responsible for fetching and converting matches from the Fotball.no API and convert to an array of Maatch entities.
 */
class Matches
{
    /**
     * URL template for fetching matches from Fotball.no API
     *
     * Following string needs replacing:
     * - stadium: The stadium ID (integer) for your stadium
     * - date_from: The date to start fetching matches
     * - date_To: The date to stop fetching matches
     */
    protected const FOTBALL_URL_TEMPLATE = 'https://www.fotball.no/footballapi/Calendar/DownloadArenaExcelCalendar?stadiumId={stadium}&fromDate={date_from}&toDate={date_to}';

    /**
     * HTTP Client for calling API
     *
     * @var \Cake\Http\Client
     */
    protected Client $client;

    /**
     * Configuration
     * - cache: Cache the results
     * - cacheLifetime: How long to store the cached results (in seconds).
     * - deleteCacheFirst: Delete the cache before checking. Used for debugging purposes.
     *
     * @var array
     */
    protected array $config = [
        'cache' => true,
        'cacheLifetime' => 3600,
        'deleteCacheFirst' => false,
    ];

    /**
     * Constructor
     *
     * @param array $config Configuration. Merges with config property.
     */
    public function __construct(array $config = [])
    {
        $this->client = new Client();
        $this->config = array_merge_recursive($this->config, $config);
    }

    /**
     * Get matches from API and convert to Maatch entities.
     *
     * @param int $stadiumId Stadium ID to fetch matches for
     * @param \Cake\Chronos\Chronos $from Date to get matches from
     * @param \Cake\Chronos\Chronos $to Date to get matches to
     * @return \Avolle\Banebok\Api\Maatch[]
     * @throws \Avolle\Banebok\Exception\FotballApiException
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getMatchesForStadium(int $stadiumId, Chronos $from, Chronos $to): array
    {
        $spreadsheetString = $this->getMatchesFromApi($stadiumId, $from, $to);

        return $this->convertToEntities($spreadsheetString);
    }

    /**
     * Get the matches from API. Returns an Excel file's content.
     * This method will either call the cached response if configured,
     * or if not, it will get the data from the API.
     *
     * @param int $stadiumId Stadium ID to fetch matches for
     * @param \Cake\Chronos\Chronos $from Date to get matches from
     * @param \Cake\Chronos\Chronos $to Date to get matches to
     * @return string Excel file contents
     * @throws \Avolle\Banebok\Exception\FotballApiException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getMatchesFromApi(int $stadiumId, Chronos $from, Chronos $to): string
    {
        $dates = [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ];
        if (!$this->config['cache']) {
            return $this->_getMatchesFromApi($stadiumId, $from, $to);
        }

        $cache = new FilesystemAdapter();
        $key = "matches-{$dates['from']}-{$dates['to']}";
        if ($this->config['deleteCacheFirst']) {
            $cache->deleteItem($key);
        }

        return $cache->get($key, function (ItemInterface $item) use ($stadiumId, $from, $to) {
            $item->expiresAfter($this->config['cacheLifetime']);

            return $this->_getMatchesFromApi($stadiumId, $from, $to);
        });
    }

    /**
     * Get matches from the actual API.
     *
     * @param int $stadiumId Stadium ID to fetch matches for
     * @param \Cake\Chronos\Chronos $from Date to get matches from
     * @param \Cake\Chronos\Chronos $to Date to get matches to
     * @return string
     * @throws \Avolle\Banebok\Exception\FotballApiException
     */
    protected function _getMatchesFromApi(int $stadiumId, Chronos $from, Chronos $to): string
    {
        $dates = [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ];
        $url = str_replace(
            ['{stadium}', '{date_from}', '{date_to}'],
            [$stadiumId, $dates['from'], $dates['to']],
            static::FOTBALL_URL_TEMPLATE,
        );
        $log = new Logger('Info', [new StreamHandler(LOGS . 'info.log', Level::Info)]);
        $log->info("Getting from API for date ${dates['from']} to ${dates['to']}");

        $res = $this->client->get($url);
        if (!$res->isOk()) {
            throw new FotballApiException('Could not retrieve matches from Fotball.no. Status code . ' . $res->getStatusCode());
        }
        $body = $res->getStringBody();
        if (empty($body)) {
            throw new FotballApiException('Returned string from Fotball.no was empty. Expected Excel spreadsheet');
        }

        return $body;
    }

    /**
     * Convert excel string to Maatch entities
     *
     * @param string $body Excel file body
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    protected function convertToEntities(string $body): array
    {
        $reader = new SpreadsheetReader($body);

        return $reader->getMatches();
    }
}
