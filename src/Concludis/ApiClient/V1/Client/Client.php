<?php


namespace Concludis\ApiClient\V1\Client;


use Concludis\ApiClient\Common\AbstractClient;
use Concludis\ApiClient\Common\ApiError;
use Concludis\ApiClient\Common\ApiResponse;
use Concludis\ApiClient\Common\ProjectSaveHandler;
use Concludis\ApiClient\Config\Source;
use Concludis\ApiClient\Database\PDO;
use Concludis\ApiClient\Exception\CurlException;
use Concludis\ApiClient\Exception\HttpException;
use Concludis\ApiClient\Exception\JsonException;
use Concludis\ApiClient\Resources\Project;
use Concludis\ApiClient\Util\CliUtil;
use Concludis\ApiClient\V1\Endpoints\ProjectEndpoint as V1ProjectEndpoint;
use Concludis\ApiClient\V2\Responses\ApplicationApplyPostResponse;
use Exception;
use RuntimeException;

class Client extends AbstractClient {

    /**
     * @param  string  $endpoint
     * @param  array  $data
     * @param  string  $method
     * @return ApiResponse
     */
    public function call(string $endpoint, array $data, string $method): ApiResponse {

        if($this->source === null) {
            $error = new ApiError(
                ApiError::CODE_API_RUNTIME_ERROR,
                'Source not defined',
                new RuntimeException('Source not defined')
            );

            return new ApiResponse(null, $error);
        }
        $url = $this->source->baseurl . $endpoint;

        if($method === 'GET' && !empty($data)) {
            $url .= '?' . http_build_query($data);
        }
        $headers = array(
            'Content-Type: application/json',
            'Authorization: Basic '. base64_encode($this->source->username . ':' . $this->source->password)
        );

    /**
     * @param string $endpoint
     * @param array $data
     * @param string $method
     * @return ApiResponse
     */
        $ch = curl_init($url);

        $proxy = getenv('HTTPS_PROXY');
        if(is_string($proxy) && !empty($proxy)) {
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        if($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            if( !empty($data)) {
                try {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_THROW_ON_ERROR));
                } catch (\JsonException) {}
            }
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        if(!$this->source->ssl_verify_peer) {
            /**
             * @noinspection CurlSslServerSpoofingInspection
             * @noinspection UnknownInspectionInspection
             */
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        $response_body = curl_exec($ch);


        $curl_errno = curl_errno($ch);
        if($curl_errno) {
            $error = new ApiError(
                ApiError::CODE_CURL_ERROR,
                'CURL request failed for: ' . $url,
                new CurlException( curl_error($ch), $curl_errno)
            );

            curl_close($ch);

            return new ApiResponse(null, $error);
        }


        $httpcode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        // the category will be 2 for 2xx, 4 for 4xx, 5 for 5xx and so on
        $httpcode_category = (int)floor($httpcode / 100);

        if ($httpcode_category !== 2) {

            $error = new ApiError(
                ApiError::CODE_HTTP_ERROR,
                'HTTP request failed for: ' . $url,
                new HttpException('HTTP request failed', $httpcode, $response_body)
            );

            return new ApiResponse(null, $error);
        }

        if($httpcode === 204) {
            return new ApiResponse(null);
        }

        try {
            $json = json_decode($response_body, true, 512, JSON_THROW_ON_ERROR);

            if(is_int($json)) {
                $json = ['int' => $json];
            }

            return new ApiResponse($json);

        } catch (\JsonException $e) {

            $error = new ApiError(
                ApiError::CODE_JSON_ERROR,
                'JSON decode failed',
                new JsonException($e->getMessage(), $e->getCode(), $response_body)
            );
        }


        return new ApiResponse(null, $error);
    }

    /**
     * @param  Source  $source
     * @param  int  $project_id
     * @param  ProjectSaveHandler  $saveHandler
     * @param  bool  $cli
     * @return void
     * @throws Exception
     */
    public function pullProject(Source $source, int $project_id, ProjectSaveHandler $saveHandler, bool $cli): void {

        $pdo = PDO::getInstance();

        $update_datetime = date('Y-m-d H:i:s');

        try {

            $pdo->beginTransaction();

            if($cli) {
                CliUtil::output('');
                CliUtil::output('pullProject....: ' . $project_id);
                CliUtil::output('source.........: ' . $source->baseurl);
                CliUtil::output('');
            }

            $pe = new V1ProjectEndpoint($this);

            $d = $pe->byId($update_datetime, $project_id);
            if($d !== null) {
                $saveHandler->saveProject($d);
            }

            $saveHandler->purgeDeprecatedProjects($source->id, $update_datetime);

            if($cli) {
                CliUtil::output('');
                CliUtil::output('Finished ' . $source->baseurl);
                CliUtil::output('------------------------------------');
                CliUtil::output('');
            }

            $pdo->commit();

        } catch (Exception $e) {
            $pdo->rollback();
            if($cli) {
                CliUtil::output('');
                CliUtil::output('uups, API call failed: ' . $e->getMessage());
                CliUtil::output('------------------------------------');
                CliUtil::output('');
                CliUtil::output($e->getTraceAsString());
                $previous = $e->getPrevious();
                if($previous) {
                    CliUtil::output($previous->getTraceAsString());
                }
                return;
            }
        }

    }

    /**
     * @param  Source  $source
     * @param  ProjectSaveHandler  $saveHandler
     * @param  bool  $cli
     * @return void
     */
    public function pullProjects(Source $source, ProjectSaveHandler $saveHandler, bool $cli): void {

        $pdo = PDO::getInstance();

        $update_datetime = date('Y-m-d H:i:s');

        try {

            $pdo->beginTransaction();

            $pe = new V1ProjectEndpoint($this);

            $count = $pe->count($source->filters);

            $items_per_call = 25;

            if($cli) {
                CliUtil::output('');
                CliUtil::output('count..........: ' . $count);
                CliUtil::output('items-per-call.: ' . $items_per_call);
                CliUtil::output('');

                CliUtil::showStatus(0, $count);
            }

            $k = 0;

            for($i = 0; $i < $count; $i += $items_per_call){

                $data = $pe
                    ->paginate($items_per_call, $i)
                    ->listFull($update_datetime, $source->filters);

                foreach($data as $d) {

                    if($cli) {
                        $k++;
                        CliUtil::showStatus($k, $count);
                    }
                    $saveHandler->saveProject($d);
                }
            }

            $saveHandler->purgeDeprecatedProjects($source->id, $update_datetime);

            if($cli) {
                CliUtil::output('');
                CliUtil::output('Finished ' . $source->baseurl);
                CliUtil::output('------------------------------------');
                CliUtil::output('');
            }

            $pdo->commit();

        } catch (Exception $e) {
            $pdo->rollback();
            if($cli) {
                CliUtil::output('');
                CliUtil::output('uups, API call failed: ' . $e->getMessage());
                CliUtil::output('------------------------------------');
                CliUtil::output('');
                CliUtil::output($e->getTraceAsString());
                $previous = $e->getPrevious();
                if($previous) {
                    CliUtil::output($previous->getTraceAsString());
                }
                return;
            }
        }

    }

    public function pullBoards(Source $source, bool $cli): void {
        throw new RuntimeException('pullBoards not implemented!');
    }

    public function pushApplication(int $project_id, array $location_ids, int $source_id, bool $is_internal, array $candidate, array $options): ApplicationApplyPostResponse {
        throw new RuntimeException('pushApplication not implemented!');
    }

    public function fetchProject(int $project_id): Project {
        throw new RuntimeException('fetchProject not implemented!');
    }
}