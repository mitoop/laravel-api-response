<?php

namespace Mitoop\Http;

enum ResponseCode: int
{
    case SUCCESS = 0;
    case ERROR = 1;
    case UNAUTHENTICATED = -1;
}
