<?php

use Elasticsearch\Client;

/**
 * An adapter to be used with ElasticSearch
 * Can be plugged in Zend_Paginator to use all of its functionality.
 *
 * @author Jonas Hässel
 */
class Divido_Paginator_Adapter_Elastic implements Zend_Paginator_Adapter_Interface
{
	protected $client;
	protected $query;

	public function __construct(Client $client, array $query)
	{
        $this->client = $client;
		$this->query = $query;
	}

	public function getItems($offset, $itemCountPerPage)
	{
        $query = $this->query;

        $query['from'] = $offset;
        $query['size'] = $itemCountPerPage;

        $result = $this->client->search($query);

        $items = [];
        if ($result['hits']['total'] > 0) {
            foreach ($result['hits']['hits'] as $hit) {
                $data = $hit['_source'];
                $data['id'] = $hit['_id'];
                $items[] = $data;
            }
        }

        return $items;

	}

	public function count()
	{
        $query = $this->query;

        if (isset($query['body']['sort'])) {
            unset($query['body']['sort']);
        }

        $result = $this->client->count($query);
        $count = $result['count'];
		return $count;
	}
}
