<?php

namespace Concludis\ApiClient\V2\Endpoints;

use Concludis\ApiClient\Common\AbstractEndpoint;
use Concludis\ApiClient\V2\Responses\BoardsGetResponse;

class BoardsGetEndpoint extends AbstractEndpoint {

    private const FILTER_TYPE_PAGINATION = 'pagination';

    private array $filter = [];


    public function paginate(int $page, int $items_per_page = 5): self {
        $this->filter[self::FILTER_TYPE_PAGINATION] = [
            'page' => $page,
            'ipp' => $items_per_page
        ];
        return $this;
    }

    public function addFilter(string $filter_type,  $value): self {

        $this->filter[$filter_type] = $value;

        return $this;
    }


    /**
     * @return BoardsGetResponse
     */
    public function call(): BoardsGetResponse {

        $url_params = [
            '{locale}' => $this->locale
        ];

        $endpoint = '/{locale}/boards';

        $endpoint = strtr($endpoint, $url_params);

        $data = [
            'show' => ['extended_props']
        ];

        if(array_key_exists(self::FILTER_TYPE_PAGINATION, $this->filter)) {
            $data['page'] = $this->filter[self::FILTER_TYPE_PAGINATION]['page'];
            $data['ipp'] = $this->filter[self::FILTER_TYPE_PAGINATION]['ipp'];
        }

        $response = $this->client->call($endpoint, $data, 'GET');

        return new BoardsGetResponse($this, $response);

    }
}