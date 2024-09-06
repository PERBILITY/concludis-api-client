<?php


namespace Concludis\ApiClient\V2;


use Concludis\ApiClient\Config\Baseconfig;
use Concludis\ApiClient\V2\Client\Client;
use Concludis\ApiClient\V2\Endpoints\ApplicationDataprivacyGetEndpoint;
use Concludis\ApiClient\V2\Endpoints\ApplicationSetupGetEndpoint;
use Concludis\ApiClient\V2\Endpoints\BoardsGetEndpoint;
use Concludis\ApiClient\V2\Endpoints\CompaniesCompanyGetEndpoint;
use Concludis\ApiClient\V2\Endpoints\CompaniesCompanyPatchEndpoint;
use Concludis\ApiClient\V2\Endpoints\CompaniesCompanyPostEndpoint;
use Concludis\ApiClient\V2\Endpoints\CompaniesGetEndpoint;
use Exception;
use RuntimeException;

class Api {

    /**
     * @var Client
     */
    private Client $client;

    /**
     * Api constructor.
     * @param string $source_id
     * @throws Exception
     */
    public function __construct(string $source_id) {

        $source = Baseconfig::getSourceById($source_id);

        if ($source === null) {
            throw new RuntimeException('Source not found.');
        }

        $client = $source->client();

        if (!$client instanceof Client) {
            throw new RuntimeException('Your source is not a V2-Source, so the client is not a V2-Client. Please check your source.');
        }

        $this->client = $client;
    }


    public function CompaniesGetEndpoint(): CompaniesGetEndpoint {
        return new CompaniesGetEndpoint($this->client);
    }

    public function CompaniesCompanyGetEndpoint(): CompaniesCompanyGetEndpoint {
        return new CompaniesCompanyGetEndpoint($this->client);
    }

    public function CompaniesCompanyPostEndpoint(): CompaniesCompanyPostEndpoint {
        return new CompaniesCompanyPostEndpoint($this->client);
    }

    public function CompaniesCompanyPatchEndpoint(): CompaniesCompanyPatchEndpoint {
        return new CompaniesCompanyPatchEndpoint($this->client);
    }
    public function BoardsGetEndpoint(): BoardsGetEndpoint {
        return new BoardsGetEndpoint($this->client);
    }

    public function ApplicationSetupGetEndpoint(): ApplicationSetupGetEndpoint {
        return new ApplicationSetupGetEndpoint($this->client);
    }
    public function ApplicationDataprivacyGetEndpoint(): ApplicationDataprivacyGetEndpoint {
        return new ApplicationDataprivacyGetEndpoint($this->client);
    }

}