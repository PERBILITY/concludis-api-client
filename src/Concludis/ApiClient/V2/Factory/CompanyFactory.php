<?php


namespace Concludis\ApiClient\V2\Factory;


use Concludis\ApiClient\Resources\Company;
use Concludis\ApiClient\Resources\Element;

class CompanyFactory {

    public static function createFromResponseObject(string $source_id, array $data): Company {
        /*
         *
         {
            "id": 1,
            "display_name": "Sample GmbH, Frankfurter Straße 561, DE-51145 Köln", // TODO
            "parent_id": 0,
            "name": "Sample GmbH",
            "address": "Frankfurter Straße 561",
            "city": "Köln",
            "postal_code": "51145",
            "country_code": "DE",
            "commercialregister": "",
            "external_id": "sample-1",
            "edu_auth": false,
            "phone_number": "",
            "invoice_email": "",
            "website_url": "",
            "career_site_url": "",
            "xing_profile_url": "",
            "logo_image_url": "",
            "industry_id": 68,
            "industry_name": "Software",
            "gh_contact_id": 0,
            "ba_account_id": "",
            "ba_contact_id": 0,
            "email_signature_id": 0,
            'kununu' => NULL,
            "dataprivacy_statement": {
              "dp_officer": {
                "id": 1,
                "display_name": "Support, concludis"
              },
              "dp_concact": null,
              "dp_inspecting_authority": {
                "id": 7,
                "name": "Hessen",
                "description": "Der Hessische Datenschutzbeauftragte\r\nProf. Dr. Michael Ronellenfitsch\r\n\r\nGustav-Stresemann-Ring 1\r\n65189 Wiesbaden\r\n\r\nTelefon: 06 11/140 80\r\nTelefax: 06 11/14 08-900 oder 901\r\n\r\nE-Mail: poststelle@datenschutz.hessen.de \r\nHomepage: http://www.datenschutz.hessen.de"
              },
              "dp_resposible_company": {
                "id": 1,
                "display_name": "Sample GmbH, Frankfurter Straße 561, DE-51145 Köln"
              },
              "dp_text": {
                "text": "<p>Das ist wunderschön.</p>\n<p>@@dp.primary.officer@@</p>"
              },
              "valid": true
            },
            "assigned_locations": [ // TODO
              {
                "id": 3,
                "display_name": "test"
              },
              {
                "id": 1,
                "display_name": "Concludis Zentrale, Frankfurter Straße 561, DE-51147 Köln"
              },
              {
                "id": 5,
                "display_name": "Felix Home, DE-51147 Köln"
              }
            ]
          }
         */

        return new Company([
            'source_id' => $source_id,
            'id' => $data['id'],
            'parent_id' => $data['parent_id'],
            'external_id' => $data['external_id'],
            'name' => $data['name'],
            'industry' => new Element([
                'source_id' => $source_id,
                'id' => (int)($data['industry_id'] ?? 0),
                'name' => $data['industry_name'] ?? ''
            ]),

            'url_company_site' => $data['website_url'] ?? '',
            'url_career_site' => $data['career_site_url'] ?? '',
            'xing_profile_url' => $data['xing_profile_url'] ?? '',
            'linkedin_profile_url' => $data['linkedin_profile_url'] ?? '',
            'linkedin_reference' => $data['linkedin_reference'] ?? '',

            'facebook' => $data['facebook'] ?? '',
            'whatsapp' => $data['whatsapp'] ?? '',
            'twitter' => $data['twitter'] ?? '',
            'instagram' => $data['instagram'] ?? '',
            'youtube' => $data['youtube'] ?? '',
            'tiktok' => $data['tiktok'] ?? '',

            'kununu' => $data['kununu'] ?? null,

            'url_logo' => $data['logo_image_url'] ?? '',
            'background_color' => $data['bg_color'] ?? '',
            'headline_color' => $data['headline_color'] ?? '',
            'address' => $data['address'] ?? '',
            'postal_code' => $data['postal_code'] ?? '',
            'locality' => $data['city'] ?? '',
            'country_code' => $data['country_code'] ?? '',
            'commercialregister' => $data['commercialregister'] ?? '',
            'edu_auth' => $data['edu_auth'] ?? false,
            'phone_number' => $data['phone_number'] ?? '',
            'invoice_email' => $data['invoice_email'] ?? '',
            'gh_contact_id' => (int)($data['gh_contact_id'] ?? 0),
            'ba_account_id' => $data['ba_account_id'] ?? '',
            'ba_contact_id' => (int)($data['ba_contact_id'] ?? 0),
            'dp_contact_id' => (int)($data['dp_contact_id'] ?? 0),
            'dp_officer_id' => (int)($data['dp_officer_id'] ?? 0),
            'dp_responsible_company_id' => (int)($data['dp_responsible_company_id'] ?? 0),
            'dp_inspecting_authority_id' => (int)($data['dp_inspecting_authority_id'] ?? 0),
            'email_signature_id' => (int)($data['email_signature_id'] ?? 0),
            'dataprivacy_statement' => $data['dataprivacy_statement'] ?? null,
            'assigned_locations' => $data['assigned_locations'] ?? null,
        ]);

    }
}