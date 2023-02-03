<?php
require_once __DIR__ . '/config.php';

/** 
 * 1. for all records in list id 151, utm_campaign_spirits = rsm_spirits
 * 2. if utm_source = adwords then replace "adwords" with rsm_82
 * 3. if utm_campaign contains 'spirits' (not case sensitive) then set utm_source_spirits = utm_source
 * 4. if utm_campaign contains 'spirits' (not case sensitive) then copy the createdate timestamp to optin_date_spirits
 * 5. if utm_campaign contains 'spirits' (not case sensitive) and [utm_medium = 'paidsocial'] then set [utm_medium_spirits = mp] & [utm_content_spirits = cso]
 * 6. if utm_campaign contains 'spirits' (not case sensitive) and [utm_medium = 'aff'] then set [utm_medium_spirits = ma]
 * 7. if utm_campaign contains 'spirits' (not case sensitive) and [utm_medium = 'ppc'] then set [utm_medium_spirits = mp]
 * 8. if utm_campaign contains "pMax" or "PMAX" then utm_content_spirits=cp
 * 
 */

$list_id = 151;

$properties = "&property=email&property=firstname&property=lastname&property=phone";
$properties .= '&property=createdate';
$properties .= '&property=utm_campaign';
$properties .= '&property=utm_medium';
$properties .= '&property=utm_source';
$properties .= '&property=utm_campaign_spirits';
$properties .= '&property=utm_content_spirits';
$properties .= '&property=utm_medium_spirits';
$properties .= '&property=utm_source_spirits';
$properties .= '&property=createdate_spirits';
$properties .= '&property=optin_date_spirits';

$offset = 0;

do {
    $response = doCurl('contacts/v1/lists/' . $list_id . '/contacts/all?count=100&formSubmissionMode=newest' . $properties . '&vidOffset=' . $offset);

    $rows = [];
    $rows2 = [];
    foreach ($response['contacts'] as $contact) {
        $contact_props = $contact['properties'];

        $utm_campaign = (string) $contact_props['utm_campaign']['value'];
        $utm_source = (string) $contact_props['utm_source']['value'];
        $utm_medium = (string) $contact_props['utm_medium']['value'];

        $utm_campaign_spirits = (string) $contact_props['utm_campaign_spirits']['value'];
        $utm_source_spirits = (string) $contact_props['utm_source_spirits']['value'];
        $utm_medium_spirits = (string) $contact_props['utm_medium_spirits']['value'];
        $createdate_spirits = (string) $contact_props['createdate_spirits']['value'];
        $optin_date_spirits = (string) $contact_props['optin_date_spirits']['value'];
        $create_date = (string) $contact_props['createdate']['value'];

        echo "\n------------UPDATE CONTACT------------------\n";

        // 1.
        $params = [
            'properties' => [
                ['property' => 'utm_campaign_spirits', 'value' => 'rsm_spirits'],
            ]
        ];

        // 2.
        if (strtolower($utm_source) == 'adwords') {
            $params['properties'][] = ['property' => 'utm_source', 'value' => 'rsm_82'];
        }

        if (stripos($utm_campaign, 'spirits')) {
            // 3.
            $params['properties'][] = ['property' => 'utm_source_spirits', 'value' => $utm_source];

            // 4.
            $params['properties'][] = ['property' => 'optin_date_spirits', 'value' => date('Y-m-d H:i:s', $create_date)];

            // 5.
            if (strtolower($utm_medium) == 'paidsocial') {
                $params['properties'][] = ['property' => 'utm_medium_spirits', 'value' => 'mp'];
                $params['properties'][] = ['property' => 'utm_content_spirits', 'value' => 'cso'];
            }

            // 6.
            if (strtolower($utm_medium) == 'aff') {
                $params['properties'][] = ['property' => 'utm_medium_spirits', 'value' => 'ma'];
            }

            // 7.
            if (strtolower($utm_medium) == 'ppc') {
                $params['properties'][] = ['property' => 'utm_medium_spirits', 'value' => 'mp'];
            }

            // 8.
            if (stripos($utm_campaign, 'pmax')) {
                $params['properties'][] = ['property' => 'utm_content_spirits', 'value' => 'cp'];
            }
        }

        $contact_props['vid'] = $contact['vid'];
        logg($contact_props);
        logg($params);

        $contact_response = doCurl('contacts/v1/contact/vid/' . $contact['vid'] . '/profile', $params);
    }

    $offset = $response['vid-offset'];

    echo "\n------------------------------\n";

} while ($response['has-more']);