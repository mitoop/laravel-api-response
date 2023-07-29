<?php

namespace Mitoop\Http\Resources;

trait ResourceTrait
{
    public function withMessage($message): ResourceCollection
    {
        $this->with['message'] = $message;

        return $this;
    }

    protected function encoding($response)
    {
        return $response->setEncodingOptions(JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
    }
}
