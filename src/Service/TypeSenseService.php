<?php

namespace App\Service;

use Typesense\Client;

class TypeSenseService
{
    private $client;
    public function __construct(string $apiKey,string $host,string $port,string $protocol,int $connectionTimeout){

        $this->client=new Client(
            [
                'api_key'         => $apiKey,
                'nodes'           => [
                    [
                        'host'     => $host,
                        'port'     => $port,
                        'protocol' => $protocol,
                    ],
                ],
                'connection_timeout_seconds' =>$connectionTimeout,
            ]
        );
    }

    public function getClient():Client{
        return $this->client;
    }


    public function search(
        string $collectionName,
        string $keywords,
        array $filters

    ){
        $filterQueries = [];
        foreach ($filters as $key => $value) {
            if (!empty($value)) {
                $filterQueries[] = $key . ':' . $value;
            }
        }
        $searchParameters=[
            'q'=>$keywords,
            'query_by'=>'name,category,embedding',
            'vector_query'=> 'embedding:([], distance_threshold:0.8)',
            'filter_by' => implode(' && ', $filterQueries)
        ];
        $response=$this->client->collections[$collectionName]->documents->search($searchParameters);
        return $response['hits'];
    }

}