<?php

namespace Mitoop\Response;

enum APIResponseCode: int
{
    case SUCCESS = 0;
    case ERROR = 1;
    case UNAUTHENTICATED = -1;
}
