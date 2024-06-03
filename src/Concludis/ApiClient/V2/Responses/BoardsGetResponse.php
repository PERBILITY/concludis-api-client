<?php

namespace Concludis\ApiClient\V2\Responses;

use Concludis\ApiClient\Common\ApiResponse;
use Concludis\ApiClient\Common\EndpointResponse;
use Concludis\ApiClient\Resources\Board;
use Concludis\ApiClient\V2\Endpoints\BoardsGetEndpoint;
use Concludis\ApiClient\V2\Factory\BoardFactory;

class BoardsGetResponse extends EndpointResponse {

    /**
     * @var Board[]|null
     */
    public ?array $boards = null;

    /**
     * @var int
     */
    public int $count = -1;

    /**
     * @var int
     */
    public int $page = -1;

    /**
     * @var int
     */
    public int $items_per_page = -1;

    /**
     * GetCompaniesResponse constructor.
     * @param BoardsGetEndpoint $endpoint
     * @param ApiResponse $response
     */
    public function __construct(BoardsGetEndpoint $endpoint, ApiResponse $response) {
        parent::__construct($endpoint, $response);

        if($response->success) {
            $source_id = $this->endpoint->client()->source()->id;
            $this->count = (int)$response->data['count'];
            $this->page = (int)$response->data['page'];
            $this->items_per_page = (int)$response->data['items_per_page'];

            $boards = $response->data['boards'];
            $this->boards = [];
            foreach($boards as $board) {
                $this->boards[] = BoardFactory::createFromResponseObject($source_id, $board);
            }
        }
    }
}