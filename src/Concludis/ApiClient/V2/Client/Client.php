<?php


namespace Concludis\ApiClient\V2\Client;


use Concludis\ApiClient\Common\AbstractClient;
use Concludis\ApiClient\Common\ApiError;
use Concludis\ApiClient\Common\ApiResponse;
use Concludis\ApiClient\Common\ProjectSaveHandler;
use Concludis\ApiClient\Config\Source;
use Concludis\ApiClient\Database\PDO;
use Concludis\ApiClient\Exception\CurlException;
use Concludis\ApiClient\Exception\HttpException;
use Concludis\ApiClient\Exception\JsonException;
use Concludis\ApiClient\Exception\ApiRuntimeException;
use Concludis\ApiClient\Resources\File;
use Concludis\ApiClient\Resources\Project;
use Concludis\ApiClient\Storage\BoardRepository;
use Concludis\ApiClient\Util\ArrayUtil;
use Concludis\ApiClient\Util\CliUtil;
use Concludis\ApiClient\V2\Endpoints\ApplicationApplyPostEndpoint;
use Concludis\ApiClient\V2\Endpoints\ApplicationDataprivacyGetEndpoint;
use Concludis\ApiClient\V2\Endpoints\ApplicationSetupGetEndpoint;
use Concludis\ApiClient\V2\Endpoints\BoardsGetEndpoint;
use Concludis\ApiClient\V2\Endpoints\CandidateApplicationsGetEndpoint;
use Concludis\ApiClient\V2\Endpoints\CandidateFileDeleteEndpoint;
use Concludis\ApiClient\V2\Endpoints\CandidateFileGetEndpoint;
use Concludis\ApiClient\V2\Endpoints\CandidateFilePostEndpoint;
use Concludis\ApiClient\V2\Endpoints\CandidateHrjsonGetEndpoint;
use Concludis\ApiClient\V2\Endpoints\CandidateHrjsonPostEndpoint;
use Concludis\ApiClient\V2\Endpoints\CandidateIncomingmessagePostEndpoint;
use Concludis\ApiClient\V2\Endpoints\CandidateMessagePatchEndpoint;
use Concludis\ApiClient\V2\Endpoints\CandidateMessagesGetEndpoint;
use Concludis\ApiClient\V2\Endpoints\CandidateProfileimageDeleteEndpoint;
use Concludis\ApiClient\V2\Endpoints\CandidateProfileimagePostEndpoint;
use Concludis\ApiClient\V2\Endpoints\ProjectsGetEndpoint;
use Concludis\ApiClient\V2\Responses\ApplicationApplyPostResponse;
use Concludis\ApiClient\V2\Responses\ApplicationDataprivacyGetResponse;
use Concludis\ApiClient\V2\Responses\ApplicationSetupGetResponse;
use Concludis\ApiClient\V2\Responses\CandidateAppicationsGetResponse;
use Exception;
use RuntimeException;

class Client extends AbstractClient {

    /**
     * @var string|null
     */
    private ?string $token = null;

    /**
     * @param string $endpoint
     * @param array $data
     * @param string $method
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


        if($this->token === null) {

            $static_token = (string)($this->source->options['static_token'] ?? '');

            if(!empty($static_token)) {
                $this->token = $static_token;
            } else {
                $response = self::doAuthTokenCall($this->source);

                if(!$response->success) {
                    return $response;
                }

                $this->token = $response->data['access_token'];
            }
        }

        return self::doCall($this->source->baseurl . $endpoint, $this->token, $data, $method);
    }

    public function __destruct() {
        try {
            $this->logout();
        } catch (Exception) {
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    private function logout(): void {
        if($this->source === null) {
            return;
        }
        if($this->token !== null) {
            $endpoint = '/de_DE/user/logout';
            $data = [];
            $method = 'POST';
            self::doCall($this->source->baseurl . $endpoint, $this->token, $data, $method);
        }
    }

    /**
     * @param Source $source
     * @return ApiResponse
     */
    private static function doAuthTokenCall(Source $source): ApiResponse {

        $url = $source->baseurl . '/de_DE/auth/token';

        $ch = curl_init($url);

        $headers = [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode($source->username . ':' . $source->password)
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if(!$source->ssl_verify_peer) {
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
                'CURL request failed',
                new CurlException(curl_error($ch), $curl_errno)
            );

            curl_close($ch);

            return new ApiResponse(null, $error);
        }


        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        // the category will be 2 for 2xx, 4 for 4xx, 5 for 5xx and so on
        $httpcode_category = (int)floor($httpcode / 100);

        if ($httpcode_category !== 2) {

            $error = new ApiError(
                ApiError::CODE_HTTP_ERROR,
                'HTTP request failed',
                new HttpException('HTTP request failed', $httpcode, $response_body)
            );

            return new ApiResponse(null, $error);
        }

        try {
            $json = json_decode($response_body, true, 512, JSON_THROW_ON_ERROR);
            if(array_key_exists('errors', $json))  {
                throw new RuntimeException($json['errors'][0]['msg'] ?? '', $json['errors'][0]['code'] ?? 0);
            }
            return new ApiResponse($json);

        } catch (\JsonException $e) {
            return new ApiResponse(null, new ApiError(
                ApiError::CODE_JSON_ERROR,
                'JSON decode failed',
                new JsonException($e->getMessage(), $e->getCode(), $response_body, $e)
            ));
        } catch (RuntimeException $e) {
            return new ApiResponse(null, new ApiError(
                ApiError::CODE_API_RUNTIME_ERROR,
                'API runtime error',
                $e
            ));
        }

    }

    /**
     * @param $url
     * @param $token
     * @param $data
     * @param string $method
     * @return ApiResponse
     */
    private static function doCall($url, $token, $data, string $method = 'GET'): ApiResponse {

        $_url = $url;
        if ($method === 'GET') {
            $_url .= '?' . http_build_query($data);
        }

        $headers = array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        );


        $ch = curl_init($_url);

        $proxy = getenv('HTTPS_PROXY');
        if(is_string($proxy) && !empty($proxy)) {
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            if (!empty($data)) {
                try {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_THROW_ON_ERROR));
                } catch (\JsonException) {
                }
            }
        }
        if ($method === 'PATCH') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
            if (!empty($data)) {
                try {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_THROW_ON_ERROR));
                } catch (\JsonException) {
                }
            }
        }
        if ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            if (!empty($data)) {
                try {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_THROW_ON_ERROR));
                } catch (\JsonException) {
                }
            }
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response_body = curl_exec($ch);

        $curl_errno = curl_errno($ch);
        if($curl_errno) {
            $error = new ApiError(
                ApiError::CODE_CURL_ERROR,
                'CURL request failed',
                new CurlException(curl_error($ch), $curl_errno)
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
                'HTTP request failed',
                new HttpException('HTTP request failed', $httpcode, $response_body)
            );

            return new ApiResponse(null, $error);
        }

        if($httpcode === 204) {
            return new ApiResponse(null);
        }

        try {
            $json = json_decode($response_body, true, 512, JSON_THROW_ON_ERROR);

            if(array_key_exists('errors', $json)) {

                $error = new ApiError(
                    ApiError::CODE_API_RUNTIME_ERROR,
                    'Runtime error in response: ' . json_encode($json['errors'], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT),
                    new ApiRuntimeException('Runtime error in response', ApiError::CODE_API_RUNTIME_ERROR, (array)$json['errors'])
                );

                return new ApiResponse(null, $error);
            }

            return new ApiResponse($json);

        } catch (\JsonException $e) {

            return new ApiResponse(null, new ApiError(
                ApiError::CODE_JSON_ERROR,
                'JSON decode failed',
                new JsonException($e->getMessage(), $e->getCode(), $response_body, $e)
            ));
        }
    }

    /**
     * @param $url
     * @param $token
     * @param $data
     * @param string $method
     * @return string
     * @throws \JsonException
     */
    private static function visualize($url, $token, $data, string $method = 'GET'): string {

        if ($method === 'GET') {
            $url .= '?' . http_build_query($data);
        }

        $host = parse_url($url, PHP_URL_HOST);
        $path = parse_url($url, PHP_URL_PATH);
        $query = parse_url($url, PHP_URL_QUERY);

        $headers = array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        );

        $content = '';
        if ($method === 'POST') {
            $content = json_encode($data, JSON_THROW_ON_ERROR);
        }
        if ($method === 'PATCH') {
            $content = json_encode($data, JSON_THROW_ON_ERROR);
        }
        if ($method === 'DELETE') {
            $content = json_encode($data, JSON_THROW_ON_ERROR);
        }

        $out = [];
        $out[] = $method . ' ' . $path . (!empty($query) ? '?' . $query : '') . ' HTTP/1.1';
        $out[] = 'Host: ' . $host;
        foreach ($headers as $header) {
            $out[] = $header;
        }
        $out[] = '';

        if (!empty($content)) {
            $out[] = $content;
        }

        return implode(PHP_EOL, $out);
    }

    /**
     * @param int $project_id
     * @return Project
     * @throws Exception
     */
    public function fetchProject(int $project_id): Project {

        $pe = new ProjectsGetEndpoint($this);

        $response = $pe->getProject($project_id);

        $err = $response->response()->error;

        if($err !== null) {
            $project_not_found = ($err->exception->suberrors[0]['subcode'] ?? null) === 404;

            if(!$project_not_found) {
                throw $err->exception;
            }
        }

        if($response->project === null) {
            throw new RuntimeException('Project not found', 404);
        }

        return $response->project;
    }

    public function pullProject(Source $source, int $project_id, ProjectSaveHandler $saveHandler, bool $cli): void {

        $pdo = PDO::getInstance();

        $update_datetime = date('Y-m-d H:i:s');

        if($cli) {
            CliUtil::output('');
            CliUtil::output('Pulling project...');
        }


        $filter_boards = [];
        if(array_key_exists('boards', $source->filters)){
            $filter_boards = ArrayUtil::toIntArray((array)$source->filters['boards']);
        }

        try {

            $pdo->beginTransaction();

            $pe = new ProjectsGetEndpoint($this);

            $response = $pe->getProject($project_id);

            $should_delete = false;
            $err = $response->response()->error;
            if($err !== null) {
                $project_not_found = ($err->exception->suberrors[0]['subcode'] ?? null) === 404;

                if(!$project_not_found) {
                    throw $err->exception;
                }
                $should_delete = true;
            }

            if(!$should_delete) {
                $p = $response->project;

                if(!$p->is_published_public && !$p->is_published_internal) {
                    $should_delete = true;
                } else if(!empty($filter_boards) && !$p->hasBoards($filter_boards)) {
                    $should_delete = true;
                } else {
                    $p->lastupdate = $update_datetime;
                    $saveHandler->saveProject($p);
                }
            }

            if($should_delete) {
                $saveHandler->deleteProject($source->id, $project_id);
            }

            $pdo->commit();

        } catch (Exception $e) {
            $pdo->rollback();
            if($cli) {
                $this->cliHandleException($e);
            }
        }
    }

    public function pullProjects(Source $source, ProjectSaveHandler $saveHandler, bool $cli): void {

        $pdo = PDO::getInstance();

        $update_datetime = date('Y-m-d H:i:s');

        if($cli) {
            CliUtil::output('');
            CliUtil::output('Pulling projects...');
        }

        try {

            $pdo->beginTransaction();

            $pe = new ProjectsGetEndpoint($this);

            $filter_boards = [];
            if(array_key_exists('boards', $source->filters)){
                $filter_boards = ArrayUtil::toIntArray((array)$source->filters['boards']);
            }

            $k = 0;
            $items_per_page = 25;
            $page = 0;
            $count = -1;

            $pe->addFilter(ProjectsGetEndpoint::FILTER_TYPE_PUBLISHED, ProjectsGetEndpoint::PUBLISHED_PUBLIC_OR_INTERNAL);

            if(!empty($filter_boards)) {
                $pe->addFilter(ProjectsGetEndpoint::FILTER_TYPE_BOARDS, $filter_boards);
            }

            while($page <= 0 || ($page * $items_per_page) < $count) {

                $page++;

                $response = $pe
                    ->paginate($page, $items_per_page)
                    ->call()
                ;

                $err = $response->response()->error;
                if($err !== null) {
                    throw $err->exception;
                }

                $count = $response->count;

                if($cli && $page === 1) {
                    CliUtil::output('count..........: ' . $count);
                    CliUtil::output('items-per-call.: ' . $items_per_page);
                    CliUtil::showStatus(0, $count);
                }

                foreach($response->projects as $p) {
                    $p->lastupdate = $update_datetime;

                    if($cli) {
                        $k++;
                        CliUtil::showStatus($k, $count);
                    }
                    $saveHandler->saveProject($p);
                }

            }

            if($cli) {
                CliUtil::output('purgeDeprecatedProjects...');
            }
            $saveHandler->purgeDeprecatedProjects($source->id, $update_datetime);

            if($cli) {
                CliUtil::output('commit...');
            }
            $pdo->commit();

            if($cli) {
                CliUtil::output('done...');
            }
        } catch (Exception $e) {
            $pdo->rollback();
            if($cli) {
                $this->cliHandleException($e);
            }
        }
    }

    public function pullBoards(Source $source, bool $cli): void {

        $pdo = PDO::getInstance();

        if($cli) {
            CliUtil::output('');
            CliUtil::output('Pulling boards...');
        }
        try {

            $current_ids = [];
            $pdo->beginTransaction();

            $be = new BoardsGetEndpoint($this);

            $k = 0;
            $items_per_page = 25;
            $page = 0;
            $count = -1;

            while($page <= 0 || ($page * $items_per_page) < $count) {

                $page++;

                $response = $be
                    ->paginate($page, $items_per_page)
                    ->call()
                ;

                $err = $response->response()->error;
                if($err !== null) {
                    throw $err->exception;
                }

                $count = $response->count;

                if($cli && $page === 1) {
                    CliUtil::output('count..........: ' . $count);
                    CliUtil::output('items-per-call.: ' . $items_per_page);
                    CliUtil::showStatus(0, $count);
                }

                foreach($response->boards as $b) {

                    if($cli) {
                        $k++;
                        CliUtil::showStatus($k, $count);
                    }

                    $b->save();
                    $current_ids[] = $b->id;
                }
            }

            BoardRepository::purgeBySource($source->id, $current_ids);

            $pdo->commit();

        } catch (Exception $e) {
            $pdo->rollback();
            if($cli) {
                $this->cliHandleException($e);
            }
        }
    }

    private function cliHandleException(Exception $e): void {
        CliUtil::output('');
        CliUtil::output('uups, someting went wrong: ' . $e->getMessage());

        if($e instanceof ApiRuntimeException) {
            foreach($e->suberrors as $suberror) {
                try {
                    $msg = (is_string($suberror) ? $suberror : json_encode($suberror, JSON_THROW_ON_ERROR));
                    CliUtil::output($msg);
                } catch (\JsonException) {
                }
            }
        }
        CliUtil::output('');

        // print_r($e);
    }

    /**
     * push an application to ATS
     *
     * @param int $project_id
     * @param array $location_ids
     * @param int $source_id
     * @param bool $is_internal
     * @param array $candidate
     * @param array $options
     * @return ApplicationApplyPostResponse
     */
    public function pushApplication(int $project_id, array $location_ids, int $source_id, bool $is_internal, array $candidate, array $options): ApplicationApplyPostResponse {

        $endpoint = new ApplicationApplyPostEndpoint($this);
        $endpoint->addParam(ApplicationApplyPostEndpoint::PARAM_KEY_PROJECT_ID, $project_id);
        $endpoint->addParam(ApplicationApplyPostEndpoint::PARAM_KEY_LOCATION_IDS, $location_ids);
        $endpoint->addParam(ApplicationApplyPostEndpoint::PARAM_KEY_SOURCE_ID, $source_id);
        $endpoint->addParam(ApplicationApplyPostEndpoint::PARAM_KEY_IS_INTERNAL, $is_internal);
        $endpoint->addParam(ApplicationApplyPostEndpoint::PARAM_KEY_CANDIDATE, $candidate);
        $endpoint->addParam(ApplicationApplyPostEndpoint::PARAM_KEY_OPTIONS, $options);

        return $endpoint->call();
    }

    /**
     * fetch the full application setup required for creating a custom application form that respects all ATS user settings.
     *
     * @param int $project_id
     * @param bool $is_internal
     * @param int $jobboard_id
     * @param string|null $locale
     * @return ApplicationSetupGetResponse
     */
    public function fetchApplicationSetup(int $project_id, bool $is_internal, int $jobboard_id = 0, ?string $locale = null): ApplicationSetupGetResponse {

        $endpoint = new ApplicationSetupGetEndpoint($this);
        if($locale !== null) {
            $endpoint->setLocale($locale);
        }
        $endpoint->addParam(ApplicationSetupGetEndpoint::PARAM_KEY_PROJECT_ID, $project_id);
        $endpoint->addParam(ApplicationSetupGetEndpoint::PARAM_KEY_IS_INTERNAL, $is_internal);
        $endpoint->addParam(ApplicationSetupGetEndpoint::PARAM_KEY_JOBBOARD_ID, $jobboard_id);

        return $endpoint->call();
    }

    public function fetchDataPrivacyStatement(int $project_id, array $location_ids, ?string $locale = null): ApplicationDataprivacyGetResponse {

        $endpoint = new ApplicationDataprivacyGetEndpoint($this);
        if($locale !== null) {
            $endpoint->setLocale($locale);
        }
        $endpoint->addParam(ApplicationDataprivacyGetEndpoint::PARAM_KEY_PROJECT_ID, $project_id);
        $endpoint->addParam(ApplicationDataprivacyGetEndpoint::PARAM_KEY_LOCATION_IDS, $location_ids);

        return $endpoint->call();
    }

    public function fetchCandidateApplications(int $candidate_id): CandidateAppicationsGetResponse  {

        $endpoint = new CandidateApplicationsGetEndpoint($this);
        $endpoint->addParam(CandidateApplicationsGetEndpoint::PARAM_KEY_CANDIDATE_ID, $candidate_id);
        return $endpoint->call();
    }

    /**
     * @param int $candidate_id
     * @param int $file_id
     * @param array|null $meta
     * @return void
     */
    public function deleteCandidateFile(int $candidate_id, int $file_id, ?array $meta = null): void {

        $endpoint = new CandidateFileDeleteEndpoint($this);
        $endpoint->addParam(CandidateFileDeleteEndpoint::PARAM_KEY_CANDIDATE_ID, $candidate_id);
        $endpoint->addParam(CandidateFileDeleteEndpoint::PARAM_KEY_FILE_ID, $file_id);
        if(!empty($meta)) {
            $endpoint->addParam(CandidateFileDeleteEndpoint::PARAM_KEY_FILE_META, $meta);
        }
        $response = $endpoint->call();

        $err = $response->error();
        if($err !== null) {
            throw $err->exception;
        }
    }

    /**
     * @param int $candidate_id
     * @param int $file_id
     * @return File
     * @throws Exception
     */
    public function getCandidateFile(int $candidate_id, int $file_id): File  {
        $endpoint = new CandidateFileGetEndpoint($this);
        $endpoint->addParam(CandidateFileGetEndpoint::PARAM_KEY_CANDIDATE_ID, $candidate_id);
        $endpoint->addParam(CandidateFileGetEndpoint::PARAM_KEY_FILE_ID, $file_id);
        $response = $endpoint->call();

        $err = $response->error();
        if($err !== null) {
            throw $err->exception;
        }

        if($response->file === null) {
            throw new RuntimeException('File not found');
        }

        return $response->file;
    }

    /**
     * @param int $candidate_id
     * @param File $file
     * @param array|null $meta
     * @return File
     * @throws Exception
     */
    public function postCandidateFile(int $candidate_id, File $file, ?array $meta = null): File {
        if(empty($file->content)) {
            throw new RuntimeException('File content is empty');
        }
        if(empty($file->name)) {
            throw new RuntimeException('File name is empty');
        }
        $endpoint = new CandidateFilePostEndpoint($this);
        $endpoint->addParam(CandidateFilePostEndpoint::PARAM_KEY_CANDIDATE_ID, $candidate_id);
        $endpoint->addParam(CandidateFilePostEndpoint::PARAM_KEY_FILE_NAME, $file->name);
        $endpoint->addParam(CandidateFilePostEndpoint::PARAM_KEY_FILE_TYPE, $file->local_file_type);
        $endpoint->addParam(CandidateFilePostEndpoint::PARAM_KEY_FILE_CONTENT, $file->content);
        if(!empty($meta)) {
            $endpoint->addParam(CandidateFilePostEndpoint::PARAM_KEY_FILE_META, $meta);
        }
        $response = $endpoint->call();

        $err = $response->error();
        if($err !== null) {
            throw $err->exception;
        }

        if($response->file === null) {
            throw new RuntimeException('Something went wrong on postCandidateFile');
        }

        return $response->file;
    }

    /**
     * @param int $candidate_id
     * @return array
     * @throws Exception
     */
    public function getCandidateHrjson(int $candidate_id): array {
        $endpoint = new CandidateHrjsonGetEndpoint($this);
        $endpoint->addParam(CandidateHrjsonGetEndpoint::PARAM_KEY_CANDIDATE_ID, $candidate_id);
        $response = $endpoint->call();

        $err = $response->error();
        if($err !== null) {
            throw $err->exception;
        }

        if($response->candidate === null) {
            throw new RuntimeException('Cannot read candidate from ATS');
        }

        return $response->candidate;
    }

    /**
     * @param array $candidate
     * @return void
     * @throws Exception
     */
    public function postCandidateHrjson(array $candidate): void {

        $endpoint = new CandidateHrjsonPostEndpoint($this);
        $endpoint->addParam(CandidateHrjsonPostEndpoint::PARAM_KEY_CANDIDATE, $candidate);
        $response = $endpoint->call();

        $err = $response->error();
        if($err !== null) {
            throw $err->exception;
        }
    }

    /**
     * @param int $candidate_id
     * @param string $message
     * @return void
     * @throws Exception
     */
    public function postCandidateIncomingmessage(int $candidate_id, string $message): void {
        $endpoint = new CandidateIncomingmessagePostEndpoint($this);
        $endpoint->addParam(CandidateIncomingmessagePostEndpoint::PARAM_KEY_CANDIDATE_ID, $candidate_id);
        $endpoint->addParam(CandidateIncomingmessagePostEndpoint::PARAM_KEY_MESSAGE, $message);
        $response = $endpoint->call();

        $err = $response->error();
        if($err !== null) {
            throw $err->exception;
        }
    }

    /**
     * @param int $candidate_id
     * @param int $message_id
     * @param string $action
     * @return void
     * @throws Exception
     */
    public function patchCandidateMessage(int $candidate_id, int $message_id, string $action): void {
        $endpoint = new CandidateMessagePatchEndpoint($this);
        $endpoint->addParam(CandidateMessagePatchEndpoint::PARAM_KEY_CANDIDATE_ID, $candidate_id);
        $endpoint->addParam(CandidateMessagePatchEndpoint::PARAM_KEY_MESSAGE_ID, $message_id);
        $endpoint->addParam(CandidateMessagePatchEndpoint::PARAM_KEY_ACTION, $action);
        $response = $endpoint->call();

        $err = $response->error();
        if($err !== null) {
            throw $err->exception;
        }
    }

    /**
     * @param int $candidate_id
     * @return array
     * @throws Exception
     */
    public function getCandidateMessages(int $candidate_id): array {
        $endpoint = new CandidateMessagesGetEndpoint($this);
        $endpoint->addParam(CandidateMessagesGetEndpoint::PARAM_KEY_CANDIDATE_ID, $candidate_id);
        $response = $endpoint->call();

        $err = $response->error();
        if($err !== null) {
            throw $err->exception;
        }

        if($response->messages === null) {
            throw new RuntimeException('Cannot read messages from ATS');
        }
        return $response->messages;
    }

    /**
     * @param int $candidate_id
     * @return void
     * @throws Exception
     */
    public function deleteCandidateProfileimage(int $candidate_id): void {
        $endpoint = new CandidateProfileimageDeleteEndpoint($this);
        $endpoint->addParam(CandidateProfileimageDeleteEndpoint::PARAM_KEY_CANDIDATE_ID, $candidate_id);
        $response = $endpoint->call();

        $err = $response->error();
        if($err !== null) {
            throw $err->exception;
        }
    }

    public function postCandidateProfileimage(int $candidate_id, File $file): File {
        $endpoint = new CandidateProfileimagePostEndpoint($this);
        $endpoint->addParam(CandidateProfileimagePostEndpoint::PARAM_KEY_CANDIDATE_ID, $candidate_id);
        $endpoint->addParam(CandidateProfileimagePostEndpoint::PARAM_KEY_FILE_MIME, $file->mime_type);
        $endpoint->addParam(CandidateProfileimagePostEndpoint::PARAM_KEY_FILE_CONTENT, $file->content);
        $response = $endpoint->call();

        $err = $response->error();
        if($err !== null) {
            throw $err->exception;
        }

        if($response->file === null) {
            throw new RuntimeException('Something went wrong on postCandidateProfileimage');
        }

        return $response->file;
    }
}