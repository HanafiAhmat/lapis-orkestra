<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Foundation;

/**
 * The Constants class contains constant values to be used across the application.
 *
 * @link https://github.com/omniti-labs/jsend Guide on building API response.
 */
class Constants
{
    /**
     * @var string Success respond status string which is part of the response body.
     */
    public const STATUS_SUCCESS = 'success';

    /**
     * @var string Fail respond status string which is part of the response body.
     */
    public const STATUS_FAIL = 'fail';

    /**
     * @var string Error respond status string which is part of the response body.
     */
    public const STATUS_ERROR = 'error';

    /**
     * @var int OK http respond status code.
     */
    public const STATUS_CODE_OK = 200;

    /**
     * @var int New record created http respond status code.
     */
    public const STATUS_CODE_CREATED = 201;

    /**
     * @var int Record deleted http respond status code.
     */
    public const STATUS_CODE_DELETED = 204;

    /**
     * @var int Bad Request http respond status code.
     */
    public const STATUS_CODE_BAD_REQUEST = 400;

    /**
     * @var int Unauthorised http respond status code.
     *
     * The HTTP status code 401, often denoted as UNAUTHORIZED, signifies that the client lacks proper authentication credentials
     * or has provided invalid credentials. In simpler terms, the server has failed to identify the user.
     *
     * @link https://supertokens.com/blog/http-error-codes-401-vs-403#http-401-unauthorized
     */
    public const STATUS_CODE_UNAUTHORIZED = 401;

    /**
     * @var int Forbidden http respond status code.
     *
     * HTTP status code 403 also denoted as FORBIDDEN is returned when the server has successfully authenticated the user, but
     * the user is still denied access to the requested resource. This is different from a 401 error, as the user’s credentials
     * are valid, but they lack the necessary permissions to view or interact with the specific resource.
     *
     * @link https://supertokens.com/blog/http-error-codes-401-vs-403#http-403-forbidden
     */
    public const STATUS_CODE_FORBIDDEN = 403;

    /**
     * @var int Record not found http respond status code.
     */
    public const STATUS_CODE_NOT_FOUND = 404;

    /**
     * @var int Data sent validation error http respond status code.
     */
    public const STATUS_CODE_DATA_CONFLICT = 409;

    /**
     * @var int Upgrade Required http respond status code.
     */
    public const STATUS_CODE_UPGRADE_REQUIRED = 426;

    /**
     * @var int Too Many Requests http respond status code.
     */
    public const STATUS_CODE_TOO_MANY_REQUESTS = 429;

    /**
     * @var int Internal Server Error http respond status code.
     */
    public const STATUS_CODE_SERVER_ERROR = 500;

    /**
     * @var int Service Unavailable http respond status code.
     */
    public const STATUS_CODE_SERVICE_UNAVAILABLE = 503;

    // Audience keys for JWT
    public const AUDIENCE_WEB = 'web';

    public const AUDIENCE_MOBILE = 'mobile';

    public const AUDIENCE_POSTMAN = 'postman';

    // Common environment keys
    public const ENV_PRODUCTION = 'production';

    public const ENV_DEVELOPMENT = 'development';

    public const ENV_TESTING = 'testing';

    // Token Defaults
    public const ACCESS_TOKEN_EXPIRY_SECONDS = 300; // 5 minutes

    public const REFRESH_TOKEN_EXPIRY_SECONDS = 604800; // 7 days
}
