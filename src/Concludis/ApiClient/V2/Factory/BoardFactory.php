<?php

namespace Concludis\ApiClient\V2\Factory;

use Concludis\ApiClient\Resources\Board;

class BoardFactory {

    public static function createFromResponseObject(string $source_id, array $data): Board {
        /*
         {
          "id": 11,
          "name": "Holding",
          "external_id": ""
        }
        */

        return new Board([
            'source_id' => $source_id,
            'id' => $data['id'],
            'external_id' => $data['external_id'],
            'name' => $data['name'],
            'extended_props' => $data['extended_props'] ?? null
        ]);

    }

}