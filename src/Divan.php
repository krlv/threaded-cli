<?php
namespace Threaded;

class Divan
{
    /**
     * @var \GuzzleHttp\Client
     */
    private $http;

    public function __construct(string $host, string $port, string $user, string $password)
    {
        $dsn = sprintf('http://%s:%s@%s:%d', urlencode($user), urlencode($password), $host, $port);
        $config = [
            'base_uri' => $dsn,
            'headers'  => [
                'Content-Type' => 'application/json',
            ],
        ];

        $this->http = new \GuzzleHttp\Client($config);
    }

    public function getAllDatabases(array $params = []): array
    {
        try {
            $response = $this->http->get('/_all_dbs', $params);
            return json_decode($response->getBody(), true);
        } catch (\Exception $exception) {
            return ['exception' => $exception->getMessage()];
        }
    }

    public function createDatabase(string $db, array $params = []): array
    {
        try {
            $response = $this->http->put(sprintf('/%s', $db), $params);
            return json_decode($response->getBody(), true);
        } catch (\Exception $exception) {
            return ['exception' => $exception->getMessage()];
        }
    }
}
