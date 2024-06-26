<?php

namespace Concludis\ApiClient\V2\Endpoints;

use Concludis\ApiClient\Common\AbstractEndpoint;
use Concludis\ApiClient\V2\Client\Client;
use Concludis\ApiClient\V2\Responses\ProjectsGetResponse;
use Concludis\ApiClient\V2\Responses\ProjectsProjectGetResponse;
use Exception;

class ProjectsGetEndpoint extends AbstractEndpoint {

    private const FILTER_TYPE_PAGINATION = 'pagination';
    public const FILTER_TYPE_PUBLISHED = 'published';
    public const FILTER_TYPE_BOARDS = 'boards';

    public const PUBLISHED_ALL = 0;
    public const PUBLISHED_PUBLIC_OR_INTERNAL = 1;
    public const PUBLISHED_PUBLIC = 2;
    public const PUBLISHED_INTERNAL = 3;

    private string $locale = 'de_DE';

    private array $filter = [];

    public function __construct(Client $client) {
        parent::__construct($client);
    }

    public function locale(string $locale): void {
        $this->locale = $locale;
    }


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
     * @param int $project_id
     * @return ProjectsProjectGetResponse
     * @throws Exception
     */
    public function getProject(int $project_id): ProjectsProjectGetResponse {

        $url_params = [
            '{locale}' => $this->locale
        ];

        $endpoint = '/{locale}/projects/project';


        $endpoint = strtr($endpoint, $url_params);

        $data = [
            'id' => $project_id,
            'published' => self::PUBLISHED_PUBLIC_OR_INTERNAL,
            'show' => ['locations', 'jobad_structured', 'ba_extended', 'indeed_extended', 'i18n']
        ];

        $response = $this->client->call($endpoint, $data, 'GET');

        return new ProjectsProjectGetResponse($this, $response);
    }


    /**
     * @return ProjectsGetResponse
     * @throws Exception
     */
    public function call(): ProjectsGetResponse {

        $url_params = [
            '{locale}' => $this->locale
        ];

        $endpoint = '/{locale}/projects';

        $endpoint = strtr($endpoint, $url_params);

        $data = [
            'show' => ['locations', 'jobad_structured', 'ba_extended', 'indeed_extended', 'i18n']
        ];

        if(array_key_exists(self::FILTER_TYPE_PAGINATION, $this->filter)) {
            $data['page'] = $this->filter[self::FILTER_TYPE_PAGINATION]['page'];
            $data['ipp'] = $this->filter[self::FILTER_TYPE_PAGINATION]['ipp'];
        }
        if(array_key_exists(self::FILTER_TYPE_PUBLISHED, $this->filter)) {
            $data['published'] = (int)$this->filter[self::FILTER_TYPE_PUBLISHED];
        }
        if(array_key_exists(self::FILTER_TYPE_BOARDS, $this->filter)) {
            $data['boards'] = (array)$this->filter[self::FILTER_TYPE_BOARDS];
        }

        $response = $this->client->call($endpoint, $data, 'GET');

        return new ProjectsGetResponse($this, $response);

    }

}