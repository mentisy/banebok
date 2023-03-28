<?php
declare(strict_types=1);

namespace Avolle\Banebok;

use Avolle\Banebok\Api\Matches;
use Cake\Chronos\Chronos;

/**
 * App class
 *
 * Responsible for handling requests and creating responses.
 */
class App
{
    /**
     * Request information
     *
     * @var array|array[]
     */
    protected array $request = [];

    /**
     * Configuration
     *
     * @var array|int[]|mixed
     */
    protected array $config = [
        'stadium' => 0,
    ];

    /**
     * Constructor method.
     */
    public function __construct()
    {
        $this->request = [
            'data' => $this->getStreamData(),
        ];
        $this->config = require ROOT . 'config' . DS . 'config.php';
    }

    /**
     * Run the app
     *
     * @throws \Avolle\Banebok\Exception\FotballApiException
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function run(): void
    {
        $dates = [
            'from' => Chronos::now()->startOfWeek(),
            'to' => Chronos::now()->endOfWeek(),
        ];
        if (isset($this->request['data']['from'])) {
            $dates['from'] = Chronos::parse($this->request['data']['from']);
        }
        if (isset($this->request['data']['to'])) {
            $dates['to'] = Chronos::parse($this->request['data']['to']);
        }

        $stadiumId = $this->config['stadium'];

        $matches = (new Matches())->getMatchesForStadium($stadiumId, $dates['from'], $dates['to']);

        header('Content-Type: application/json');

        echo json_encode(compact('matches', 'dates'));
    }

    /**
     * Get stream contents from request.
     *
     * @return array
     */
    protected function getStreamData(): array
    {
        $streamName = 'php://input';
        $file = fopen($streamName, 'rw');
        $stream = stream_get_contents($file);

        if (empty($stream)) {
            return [];
        }

        return json_decode($stream, true);
    }
}
