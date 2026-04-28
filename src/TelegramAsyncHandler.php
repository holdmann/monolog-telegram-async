<?php
/**
 * @author: Oleg Sherbakov <holdmann@yandex.ru>
 * Date: 28.04.2036
 * Time: 16:11
 */

namespace Holdmann\Monolog;


use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\CurlMultiHandler;
use GuzzleHttp\Pool;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\Utils;
use GuzzleHttp\Promise;
use GuzzleHttp\Psr7\Request;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Monolog\LogRecord;
use Psr\Http\Message\RequestInterface;


/**
 * Sends notifications through Telegram API.
 * @see https://core.telegram.org/bots/api
 */
class TelegramAsyncHandler extends AbstractProcessingHandler
{
    /**
     * @var string Default url to send requests
     */
    private string $baseBotUrl = 'https://api.telegram.org/bot';

    /** @var string Telegram API token */
    private string $token;

    /** @var int Chat identifier */
    private int $chatId;

    private array $options = [];

    /** @var RequestInterface[] Holds requests. */
    private static array $requests = [];

    /**
     * @param string $token Telegram API token
     * @param int $chatId Chat identifier
     * @param int $level The minimum logging level at which this handler will be triggered
     * @param bool $bubble Whether the messages that are handled can bubble up the stack or not
     */
    public function __construct(string $token, int $chatId, int $level = Logger::CRITICAL, bool $bubble = true) {
        $this->token = $token;
        $this->chatId = $chatId;
        parent::__construct($level, $bubble);
    }

    /**
     * @param string $url proxy URL formatted as "http://username:password@192.168.16.1:10" OR "http://192.168.16.1:10"
     */
    public function setProxy(string $url): void
    {
        $this->options['proxy'] = $url;
    }

    /**
     * @param array $options Guzzle options
     */
    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    /**
     * @param array $options Guzzle options
     */
    public function addOptions(array $options): void
    {
        foreach ($options as $option => $value) {
            $this->options[$option] = $value;
        }
    }

    /**
     * @param string $url Telegram api url to send real request
     * @return void
     */
    public function setBaseBotUrl(string $url): void
    {
        $this->baseBotUrl = $url;
    }

    /**
     * {@inheritdoc}
     *
     * @param array $record
     */
    protected function write(LogRecord $record): void
    {
        $data = [
            'chat_id'    => $this->chatId,
            'text'       => $record['formatted'],
            //'parse_mode' => 'markdown',
        ];

        self::$requests[] = new Request(
            'POST',
            sprintf('%s%s/sendMessage', $this->baseBotUrl, $this->token),
            ['Content-Type' => 'application/json'],
            \json_encode($data)
        );
    }

    public function __destruct()
    {
        try {
            $client = new Client();
            $pool = new Pool($client, self::$requests, [
                'concurrency' => 10,
                'options' => $this->options,
            ]);
            $pool->promise()->wait();
        } catch (\Throwable $e) {}
    }
}
