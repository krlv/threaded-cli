<?php
namespace Threaded\Couch;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Threaded\Couch\Exception\NotFoundException;
use Threaded\Couch\Exception\RuntimeException;

class Client
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
        return $this->request('GET', '/_all_dbs', $params);
    }

    public function createDatabase(string $db, array $params = []): array
    {
        return $this->request('PUT', sprintf('/%s', $db), $params);
    }

    public function getDatabase(string $db, array $params = []): array
    {
        return $this->request('GET', sprintf('/%s', $db), $params);
    }

    public function createDocument(string $db, array $doc, array $params = [])
    {
        $params['json'] = $doc;
        return $this->request('POST', sprintf('/%s', $db), $params);
    }

    public function bulkDocuments(string $db, array $docs, array $params = [])
    {
        $params['json'] = ['docs' => $docs];
        return $this->request('POST', sprintf('/%s/_bulk_docs', $db), $params);
    }

    public function createDesignDocument(string $db, string $name, array $ddoc, array $params = []): array
    {
        $params['json'] = $ddoc;
        return $this->request('PUT', sprintf('/%s/_design/%s', $db, $name), $params);
    }

    public function getAllDocuments(string $db, array $params = []): array
    {
        return $this->request('GET', sprintf('/%s/_all_docs', $db), $params);
    }

    public function getAllDocumentsByKeys(string $db, array $keys, array $params = []): array
    {
        $params['json'] = $keys;
        return $this->request('POST', sprintf('/%s/_all_docs', $db), $params);
    }

    public function findDocuments(string $db, array $criteria, array $params = []): array
    {
        $params['json'] = $criteria;
        return $this->request('POST', sprintf('/%s/_find', $db), $params);
    }

    private function request(string $method, string $uri, array $options): array
    {
        try {
            $response = $this->http->request($method, $uri, $options);
            return json_decode($response->getBody(), true);
        } catch (ClientException $exception) {
            throw new NotFoundException(
                $exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        } catch (ServerException $exception) {
            throw new RuntimeException(
                $exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        }
    }
}
