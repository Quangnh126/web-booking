<?php


namespace App\Enums;


class Constant
{
//    STATUS CODE
    const SUCCESS_CODE              = 200;
    const FALSE_CODE                = false;
    const CREATED_CODE              = 201;
    const ACCEPTED_CODE             = 202;
    const NO_CONTENT_CODE           = 204;
    const BAD_REQUEST_CODE          = 400;
    const UNAUTHORIZED_CODE         = 401;
    const FORBIDDEN_CODE            = 403;
    const NOT_FOUND_CODE            = 404;
    const METHOD_NOT_ALLOWED_CODE   = 405;
    const INTERNAL_SV_ERROR_CODE    = 500;

    const DISTANCE_MAP_NOT_FOUND    = 'NOT_FOUND';

// ORDERING
    const ORDER_BY                  = 100;

// PATH
    const PATH_PROFILE              = 'profile';
    const PATH_CATEGORY             = 'category';
    const PATH_ROOM                 = 'room';
    const PATH_APP                  = 'app';
}
