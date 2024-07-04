<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Typesense\Client;

class TestController extends AbstractController
{
    #[Route('/test', name: 'app_test')]
    public function index(): Response
    {

        $client = new Client(
            [
                'api_key'         => '8UmysiQRwjLjHsNshptyaPXyEblmMVH9',
                'nodes'           => [
                    [
                        'host'     => 'fg6l1rmvjnyk8b4wp-1.a1.typesense.net', // For Typesense Cloud use xxx.a1.typesense.net
                        'port'     => '443',      // For Typesense Cloud use 443
                        'protocol' => 'https',      // For Typesense Cloud use https
                    ],
                ],
                'connection_timeout_seconds' => 2,
            ]
        );

        //setting up the schema
        $booksSchema = [
            'name' => 'books',
            'fields' => [
                ['name' => 'title', 'type' => 'string'],
                ['name' => 'authors', 'type' => 'string[]', 'facet' => true],
                ['name' => 'publication_year', 'type' => 'int32', 'facet' => true],
                ['name' => 'ratings_count', 'type' => 'int32'],
                ['name' => 'average_rating', 'type' => 'float']
            ],
            'default_sorting_field' => 'ratings_count'
        ];

//        $client->collections->create($booksSchema);

        //loading data into the collection
        $booksData = file_get_contents('../public/data/books.jsonl');
        $client->collections['books']->documents->import($booksData);
        $searchParameters = [
            'q'         => 'harry potter',
            'query_by'  => 'title',
            'sort_by'   => 'ratings_count:desc'
        ];

        $response=$client->collections['books']->documents->search($searchParameters);
        dd($response['hits']);


        return $this->render('test/index.html.twig', [
            'controller_name' => 'TestController',
        ]);
    }
}
