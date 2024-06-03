<?php

namespace Concludis\ApiClient\V2\Factory;

use Concludis\ApiClient\Resources\Person;

class PersonFactory {

    public static function createFromResponseObject(string $source_id, array $data): Person {
        /*
         {
            "id": 43,
            "external_id": "",
            "gender": "f",
            "title": "",
            "firstname": "Yo",
            "lastname": "Dies",
            "email": "aa@concludis.de",
            "phone": "",
            "profile_image": "https://concludis.test/assets/concludis/img/user.png",
            "position": "",
            "division": "",
            "department": "",
            "organisation": "",
            "social_media": {
                "whatsapp_profil": "",
                "xing": "",
                "linkedin": ""
            }
        }
        */
        return new Person([
            'source_id' => $source_id,
            'id' => (int)($data['id'] ?? 0),
            'external_id' => $data['external_id'] ?? '',
            'gender' => $data['gender'] ?? '',
            'title' => $data['title'] ?? '',
            'firstname' => $data['firstname'] ?? '',
            'lastname' => $data['lastname'] ?? '',
            'profile_image' => $data['profile_image'] ?? '',
            'position' => $data['position'] ?? '',
            'department' => $data['department'] ?? '',
            'division' => $data['division'] ?? '',
            'organisation' => $data['organisation'] ?? '',
            'email' => $data['email'] ?? '',
            'phone' => $data['phone'] ?? '',
            'social_media' => (array)($data['social_media'] ?? [])
        ]);

    }

}